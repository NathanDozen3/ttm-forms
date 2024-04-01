<?php

namespace ttm\forms;

/**
 *
 */
class Database {


	/**
	 *
	 *
	 * @param array $block
	 * @param array $attrs
	 *
	 * @return array
	 */
	private function do_block( array $block, $attrs = [] ) : array {

		// Only allow TTM blocks
		if( ! str_starts_with( $block[ 'blockName' ], 'ttm/' ) ) {
			return [];
		}

		if( isset( $block[ 'innerBlocks' ] ) && ! empty( $block[ 'innerBlocks' ] ) ) {
			foreach( $block[ 'innerBlocks' ] as $innerBlock ) {
				$attrs = array_merge( $attrs, $this->do_block( $innerBlock, $attrs ) );
			}
		}
		else if( $block[ 'blockName' ] === 'ttm/recaptcha' ) {
			$attrs[] = 'g-recaptcha-response';
		}
		else {
			$html = $block[ 'innerHTML' ] ?? '';

			$dom = new \DOMDocument();
			$dom->loadHTML($html);


			$xpath = new \DOMXPath($dom);

			$tags = $xpath->query('//input');
			foreach( $tags as $tag ) {
				$name = trim( $tag->getAttribute( 'name' ) );
				if( ! empty( $name ) ) {
					$attrs[] = $name;
				}
			}

			$tags = $xpath->query('//textarea');
			foreach( $tags as $tag ) {
				$name = trim( $tag->getAttribute( 'name' ) );
				if( ! empty( $name ) ) {
					$attrs[] = $name;
				}
			}
		}
		return array_unique( $attrs );
	}

	/**
	 *
	 *
	 * @param int $post_id
	 * @param array $fields
	 *
	 * @return array
	 */
	private function validate_form( int $post_id, array $fields ) : array {
		$the_fields = $fields;
		unset( $the_fields[ 'url' ] );
		unset( $the_fields[ 'post_id' ] );
		unset( $the_fields[ 'ttm_form' ] );
		unset( $the_fields[ 'ttm_form_ref' ] );
		$keys = array_keys( $the_fields );
		unset( $the_fields );

		$post = get_post( $post_id );
		$blocks = parse_blocks( $post->post_content );

		// Loop through all blocks in a post
		foreach( $blocks as $block ) {

			// Skip non-ttm/form blocks
			if( $block[ 'blockName' ] !== 'ttm/form' ) {
				continue;
			}

			// Get all the form fields in the ttm/form block
			$attrs = $this->do_block( $block );

			// Add to and subject fields from ttm/form block
			$attrs[ 'to' ] = is_email( $block[ 'attrs' ][ 'to' ] ?? '' );
			$attrs[ 'subject' ] = sanitize_text_field( $block[ 'attrs' ][ 'subject' ] ?? '' );

			// Block validation
			$is_block = true;
			foreach( $keys as $key ) {
				if( ! in_array( $key, $attrs ) ) {
					$is_block = false;
					break;
				}
			}
			if( $is_block ) {
				return $attrs;
			}
		}
		return [];
	}


	/**
	 *
	 *
	 * @return never
	 */
	public function process_form() {
		if(
			is_admin() ||
			empty( $_POST[ 'post_id' ] ) ||
			empty( $_POST[ 'ttm_form' ] )
		) {
			return;
		}

		$posted = $_POST;

		// Get the ID
		$post_id = (int) $posted[ 'post_id' ];
		if( isset( $posted[ 'ttm_form_ref' ] ) && ! empty( $posted[ 'ttm_form_ref' ] ) ) {
			$post_id = (int) $posted[ 'ttm_form_ref' ];
		}

		$validated_fields = [
			'fields' => [],
		];
		$fields = $this->validate_form( $post_id, $posted );

		foreach( $fields as $key => $field ) {
			if( in_array( $field, [ 'g-recaptcha-response' ] ) ) {
				continue;
			}
			if( in_array( $key, [ 'to', 'subject' ] ) ) {
				$validated_fields[ $key ] = $field;
			}
			else if( ! empty( $posted[ $field ] ) ) {
				$validated_fields[ 'fields' ][ $field ] = $posted[ $field ];
			}
		}

		// Get URL, account for terms
		$exploded = explode( '_', $posted[ 'post_id' ] );
		if( count( $exploded ) == 2 ) {
			$validated_fields[ 'url' ] = get_term_link( (int) $exploded[1], $exploded[0] );
		}
		else {
			$validated_fields[ 'url' ] = get_the_permalink( $posted[ 'post_id' ] ?? null );
		}

		$validated_fields[ 'to' ] = is_email( $validated_fields[ 'to' ] );
		$validated_fields[ 'subject' ] = sanitize_text_field( $validated_fields[ 'subject' ] );
		$validated_fields[ 'headers' ] = [ 'Content-Type: text/html; charset=UTF-8' ];

		$fields = $attrs;
		$n = 0;

		$message = '<table>';

		$dont_save = [
			'ttm_form',
			'g-recaptcha-response',
			'credit-card_number',
			'credit-card_cvv',
			'credit-card_zip'
		];

		/**
		 *
		 */
		$dont_save = apply_filters( 'ttm\forms\dont_save', $dont_save );

		foreach( $validated_fields[ 'fields' ] as $key => $value ) {
			if( in_array( $key, $dont_save ) ) {
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

		$validated_fields[ 'fields' ] = (array) apply_filters( 'ttm\forms\fields\pre_insert', $fields );
		$validated_fields[ 'fields' ] = json_encode( json_decode( json_encode( $fields ) ) );

		$validated_fields[ 'date' ] = date( 'Y-m-d H:i:s' );
		$validated_fields[ 'message' ] = $message;

		// Validate reCAPTCHA
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
				$sent = wp_mail( $validated_fields[ 'to' ], $validated_fields[ 'subject' ], $validated_fields[ 'message' ], $validated_fields[ 'headers' ] );
				if( $sent ) {
					$this->insert_record_into_table( $validated_fields[ 'date' ], $validated_fields[ 'url' ], $validated_fields[ 'fields' ] );
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
