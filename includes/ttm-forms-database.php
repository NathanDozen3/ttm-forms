<?php

namespace ttm\forms;

/**
 *
 */
class Database {


	/**
	 * Do each block and return the attributes.
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
	 * Validate completed form.
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
			$attrs[ 'thank-you-link' ] = $block[ 'attrs' ][ 'thankYouLink' ] ?? '';

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
	 * Validate the incomplete form.
	 *
	 * @param int $post_id
	 * @param array $posted
	 *
	 * @return array
	 */
	public function process_incomplete_form( int $post_id, array $posted ) : array {

		$validated_fields = [
			'partial' => $posted[ 'partial' ],
			'fields' => [],
		];
		$partial = $posted[ 'partial' ];
		unset( $posted[ 'partial' ] );

		$fields = $this->validate_form( $post_id, $posted );

		foreach( $fields as $key => $field ) {
			if( in_array( $field, [ 'g-recaptcha-response' ] ) ) {
				continue;
			}
			if( in_array( $key, [ 'to', 'subject', 'thank-you-link' ] ) ) {
				$validated_fields[ $key ] = $field;
			}
			else if( ! empty( $posted[ $field ] ) ) {
				$validated_fields[ 'fields' ][ $field ] = $posted[ $field ];
			}
		}

		return $validated_fields;
	}


	/**
	 * Process the form.
	 *
	 * @return void
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

		$partial = $posted[ 'partial' ] ?? '';
		unset( $posted[ 'partial' ] );

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
			if( in_array( $key, [ 'to', 'subject', 'thank-you-link' ] ) ) {
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
		 * Array of values that will not get saved to the database.
		 *
		 * @param array $dont_save
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

		/**
		 * Filter fields before inserting into database.
		 *
		 * @param array $fields
		 */
		$validated_fields[ 'fields' ] = (array) apply_filters( 'ttm\forms\fields\pre_insert', $fields );
		$validated_fields[ 'fields' ] = json_encode( json_decode( json_encode( $validated_fields[ 'fields' ] ) ) );

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
					$this->maybe_insert_record_into_table( $validated_fields[ 'date' ], $validated_fields[ 'url' ], $partial, $validated_fields[ 'fields' ] );
				}
			}
		}

		$redirect = $_SERVER[ 'REQUEST_URI' ];
		if( isset( $validated_fields[ 'thank-you-link' ] ) && ! empty( $validated_fields[ 'thank-you-link' ] ) ) {
			$redirect = $validated_fields[ 'thank-you-link' ];
		}
		header("Location: {$redirect}");
		die;
	}


	/**
	 * Return all the records.
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
	 * Insert or update partial record.
	 *
	 * @param string $id
	 * @param array $params
	 *
	 * @return void
	 */
	public function update_partial_by_id( string $id, array $params ) : void {
		global $wpdb;
		$table_name = TTM_FORMS_TABLE_NAME;
		$rows = $wpdb->get_results( "SELECT * FROM $table_name WHERE `url` LIKE '$id'" );

		if( count( $rows ) === 0 ) {
			$this->insert_record_into_table( date( 'Y-m-d H:i:s' ), $id, json_encode( $params ) );
		}
		if( count( $rows ) === 1 ) {
			$row = $rows[0];
			$fields = json_encode( $params );
			$sql = "UPDATE `$table_name` SET `id` = '$row->id', `date` = '$row->date', `url` = '$row->url', `fields` = '$fields' WHERE `id` = '$row->id'";
			$wpdb->get_results( $sql );
		}
		else {
			$wpdb->get_results( "DELETE FROM $table_name WHERE `url` LIKE '$id'" );
			$this->insert_record_into_table( date( 'Y-m-d H:i:s' ), $id, json_encode( $params ) );
		}
	}


	/**
	 * Insert or update record in table based on partial string.
	 *
	 * @param string $date
	 * @param string $url
	 * @param string $partial
	 * @param string $fields
	 *
	 * @return void
	 */
	public function maybe_insert_record_into_table( string $date, string $url, string $partial, string $fields ) : void {
		global $wpdb;
		$table_name = TTM_FORMS_TABLE_NAME;
		$rows = $wpdb->get_results( "SELECT * FROM $table_name WHERE `url` LIKE '$partial'" );

		if( count( $rows ) === 0 ) {
			$this->insert_record_into_table( date( 'Y-m-d H:i:s' ), $url, json_encode( $params ) );
		}
		if( count( $rows ) === 1 ) {
			$row = $rows[0];
			$sql = "UPDATE `$table_name` SET `id` = '$row->id', `date` = '$row->date', `url` = '$url', `fields` = '$fields' WHERE `id` = '$row->id'";
			$wpdb->get_results( $sql );
		}
		else {
			$wpdb->get_results( "DELETE FROM $table_name WHERE `url` LIKE '$id'" );
			$this->insert_record_into_table( date( 'Y-m-d H:i:s' ), $url, json_encode( $params ) );
		}
	}


	/**
	 * Insert a record into the database table.
	 *
	 * @param string $date
	 * @param string $url
	 * @param string $fields
	 *
	 * @return void
	 */
	public function insert_record_into_table( string $date, string $url, string $fields ) : void {

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
	 * Create the TTM Forms database table.
	 *
	 * @return void
	 */
	public function create_database_table() : void {
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
