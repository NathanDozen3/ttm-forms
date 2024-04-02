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
		register_rest_route( 'ttm-forms/v1', '/partial', array(
			'methods' => 'POST',
			'callback' => [ $this, 'process_partial' ],
		) );
	}


	/**
	 * Return array of sanitized and validated form data.
	 *
	 * @return array
	 */
	public function process_partial( $data ) {
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
