<?php

namespace ttm\forms;

/**
 *
 */
class Rest {


	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public function register_routes() : void {
		register_rest_route( 'ttm-forms/v1', '/update/(?P<id>[0-9]+)', array(
			'methods' => 'POST',
			'callback' => function( \WP_REST_Request $data ){
				global $ttm_forms_database;

				$body = json_decode( $data->get_body() );
				$entry = [];
				foreach( $body->entry as $key => $val ) {
					if( ! empty( $key ) ) {
						$entry[ sanitize_text_field( $key ) ] = sanitize_text_field( $val );
					}
				}

				$params = $data->get_params();
				$ttm_forms_database->update_entry( $params[ 'id' ], $entry );

				return [
					'id' => $params[ 'id' ],
					'entry' => $entry,
				];
			},
			'permission_callback' => function() {
				return current_user_can( 'manage_options' );
			}
		) );

		register_rest_route( 'ttm-forms/v1', '/partial', array(
			'methods' => 'POST',
			'callback' => [ $this, 'process_partial' ],
			'permission_callback' => '__return_true',
		) );

		register_rest_route( 'ttm-forms/v1', '/webhook', array(
			'methods' => 'POST',
			'callback' => '\ttm\forms\webhook_action',
			'permission_callback' => '__return_true',
		) );
	}


	/**
	 * Return array of sanitized and validated form data.
	 *
	 * @return array
	 */
	public function process_partial( \WP_REST_Request $data ) {
		$params = [];
		foreach( $data->get_params() as $key => $param ) {
			$params[ sanitize_title( $key ) ] = sanitize_text_field( $param );
		}

		$post_id = (int) $params[ 'post_id' ];
		if( isset( $params[ 'ttm_form_ref' ] ) && ! empty( $params[ 'ttm_form_ref' ] ) ) {
			$post_id = (int) $params[ 'ttm_form_ref' ];
		}

		global $ttm_forms_database;
		$params = $ttm_forms_database->process_incomplete_form( $post_id, $params );

		$partial = $params[ 'partial' ];
		unset( $params[ 'partial' ] );
		$ttm_forms_database->update_partial_by_id( $partial, $params );
		return $params;
	}
}
