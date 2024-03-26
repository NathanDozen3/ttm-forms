<?php

namespace ttm\forms;

/**
 *
 */
class Database {

	/**
	 *
	 */
	private function process_inner_blocks( array $blocks ) : array {
		$innerBlocks = [];

		foreach( $blocks as $block ) {

			if(
				! str_starts_with( $block[ 'blockName' ], 'ttm/' ) ||
				str_starts_with( $block[ 'blockName' ], 'ttm/input-submit' )
			) {
				continue;
			}

			if( ! empty( $block[ 'innerBlocks' ] ) ) {
				$innerBlocks = array_merge( $innerBlocks, $this->process_inner_blocks( $block[ 'innerBlocks' ] ) );
			}
			else {
				$label = $block[ 'attrs' ][ 'label' ] ?? '';

				if(
					str_starts_with( $block[ 'blockName' ], 'ttm/input-hidden' ) ||
					str_starts_with( $block[ 'blockName' ], 'ttm/input-checkbox-item' ) ||
					str_starts_with( $block[ 'blockName' ], 'ttm/input-radio-item' )
				) {
					$label = $block[ 'attrs' ][ 'name' ] ?? '';
				}
				$name = strtolower( str_replace( [ ':', ' ' ], [ '', '' ], $label ) );
				$innerBlocks[] = $name;
			}
		}
		return $innerBlocks;
	}

	/**
	 *
	 */
	private function process_individual_block( array $block, array $keys, array $attrs = [] ) {
		if( $attrs !== [] ) {
			return $attrs;
		}

		if( ! str_starts_with( $block[ 'blockName' ], 'ttm/form' ) ) {
			return [];
		}

		$fields = $this->process_inner_blocks( $block[ 'innerBlocks' ] );
		sort( $fields );

		$fields = array_filter( $fields, function( $val ) {
			return ! empty( $val );
		} );

		$insert = true;
		foreach( $fields as $field ) {
			if( ! in_array( $field, $keys ) ) {
				$insert = false;
			}
		}

		if( $insert ) {
			$block[ 'attrs' ] = array_filter( $block[ 'attrs' ], function( $key ) {
				return in_array( $key, [ 'post_id', 'to', 'subject' ] );
			}, ARRAY_FILTER_USE_KEY );
			$attrs = $block[ 'attrs' ];
			return $attrs;
		}

		return [];
	}

	/**
	 *
	 */
	public function process_form() {
		if(
			empty( $_POST[ 'post_id' ] ) ||
			empty( $_POST[ 'ttm_form' ] )
		) {
			return;
		}

		$posted = $_POST;

		$exploded = explode( '_', $posted[ 'post_id' ] );
		if( count( $exploded ) == 2 ) {
			$posted[ 'url' ] = get_term_link( (int) $exploded[1], $exploded[0] );
		}
		else {
			$posted[ 'url' ] = get_the_permalink( $posted[ 'post_id' ] ?? null );
		}

		unset( $posted[ 'ttm_form' ] );

		$keys = array_keys( $posted );
		sort( $keys );

		$post_id = (int) $posted[ 'post_id' ];
		if( isset( $posted[ 'ttm_form_ref' ] ) ) {
			$post_id = (int) $posted[ 'ttm_form_ref' ];
		}

		$post = get_post( $post_id );
		$blocks = parse_blocks( $post->post_content );

		$attrs = [];
		foreach( $blocks as $block ) {
			if ( str_starts_with( $block[ 'blockName' ], 'core/block' ) ) {
				$ref = $block[ 'attrs' ][ 'ref' ];
				$reusable_block = get_post( $ref );
				$newBlocks = parse_blocks( $reusable_block->post_content );

				foreach( $newBlocks as $newBlock ) {
					$attrs = $this->process_individual_block( $newBlock, $keys, $attrs );
				}
			}
			else {
				$attrs = $this->process_individual_block( $block, $keys, $attrs );
			}
		}

		$attrs[ 'to' ] = is_email( $attrs[ 'to' ] );
		$to = is_email( $attrs[ 'to' ] );
		$attrs[ 'subject' ] = sanitize_text_field( $attrs[ 'subject' ] );
		$subject = sanitize_text_field( $attrs[ 'subject' ] );
		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];

		$fields = $attrs;
		$n = 0;

		$message = '<table>';

		foreach( $posted as $key => $value ) {
			if(
				$key === 'ttm_form' ||
				$key === 'g-recaptcha-response'
			) {
				continue;
			}

			if( is_array( $value ) ) {
				foreach( $value as $k => $v ) {
					$value[ $k ] = sanitize_text_field( $v );
				}
				$value = json_encode( $value );
			}

			$key = sanitize_text_field( $key );
			$value = sanitize_text_field( $value );
			$fields[ $key ] = $value;

			$color = $n % 2 === 0 ? '#ffffff' : '#f0f0f0';

			$message .= "<tr style='background-color:$color;'>";
			$message .= "<td style='padding:5px;'>$key</td>";
			$message .= "<td>$value</td>";
			$message .= "</tr>";
			$n++;
		}
		$message .= '</table>';
		$fields = json_encode( json_decode( json_encode( $fields ) ) );

		$date = date( 'Y-m-d H:i:s' );
		$url = $posted[ 'url' ];

		$options = get_option( 'ttm_forms' );
		$secret = $options[ 'secret-key' ];
		$response = $posted[ 'g-recaptcha-response' ];
		$remoteip = '';

		$has_recaptcha = isset( $posted[ 'g-recaptcha-response' ]);

		if( $has_recaptcha ) {
			$recaptcha = ( new Recaptcha( $secret, $response, $remoteip ) )->get();
			$body = json_decode( wp_remote_retrieve_body( $recaptcha ) );
		}

		if(
			! $has_recaptcha ||
			( $has_recaptcha && $body->success === true )
		) {

			// Validate Honeypot
			if(
				isset( $_REQUEST[ TTM_FORMS_HONEYPOT_POST_VAR ] ) &&
				empty( $_REQUEST[ TTM_FORMS_HONEYPOT_POST_VAR ] )
			) {
				$sent = wp_mail( $to, $subject, $message, $headers );
				if( $sent ) {
					$this->insert_record_into_table( $date, $url, $fields );
				}
			}
		}
		header("Location: {$_SERVER[ 'REQUEST_URI' ]}");
		die;
	}


	/**
	 *
	 *
	 * @return array
	 */
	public function get_record_labels() : array {
		global $wpdb;
		$table_name = TTM_FORMS_TABLE_NAME;
		$items = $wpdb->get_results(
			"SELECT `fields` FROM $table_name", ARRAY_A
		);
		$dont_log = [
			'date',
			'ttm_form',
			'g-recaptcha-response',
		];

		$fields = [];
		foreach( $items as $item ) {
			$fs = json_decode( $item[ 'fields' ] );
			foreach( $fs as $k => $f ) {


				if( in_array( $k, $dont_log ) ) {
					continue;
				}
				$fields[ $k ] = true;
			}
		}
		return array_keys( $fields );
	}


	/**
	 *
	 */
	private function insert_record_into_table( $date, $url, $fields ) {

		global $wpdb;

		$table_name = TTM_FORMS_TABLE_NAME;

		$wpdb->insert(
			$table_name,
			[
				'date' => $date,
				'url' => $url,
				'fields' => $fields,
			]
		);
	}

	/**
	 *
	 */
	public function create_database_table() {
		global $wpdb;

		$table_name = TTM_FORMS_TABLE_NAME;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			date datetime DEFAULT '0000-00-00' NOT NULL,
			url varchar(55) DEFAULT '' NOT NULL,
			fields text NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
