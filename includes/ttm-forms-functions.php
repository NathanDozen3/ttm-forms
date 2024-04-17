<?php

namespace ttm\forms;

/**
 * Include the partial file.
 *
 * @param string $partial
 * @param array $args
 *
 * @return void
 */
function get_partial( string $partial, array $args = [] ) : void {
	$file = TTM_FORMS_DIR . '/partials/' . $partial . '.php';
	if( ! file_exists( $file ) ) {
		wp_die( "File does not exist. <br>\n $file");
	}
	require $file;
}


/**
 * Print a checkbox field.
 *
 * @param array $args.
 *
 * @return void
 */
function render_input_checkbox( array $args ) : void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$options = get_option( 'ttm_forms' );
	$args[ 'value' ] = $options[ $args[ 'id' ] ] ?? '';
	get_partial( 'input-checkbox', $args );
}

/**
 * Print a text input field.
 *
 * @param array $args This is the description.
 *
 * @return void
 */
function render_input_text_field( array $args ) : void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$options = get_option( 'ttm_forms' );
	$args[ 'value' ] = $options[ $args[ 'id' ] ] ?? '';
	get_partial( 'input-text', $args );
}

/**
 * Print a password input field.
 *
 * @param array $args This is the description.
 *
 * @return void
 */
function render_input_password_field( array $args ) : void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$options = get_option( 'ttm_forms' );
	$args[ 'value' ] = $options[ $args[ 'id' ] ] ?? '';
	get_partial( 'input-password', $args );
}

/**
 * Print a radio input field.
 *
 * @param array $args This is the description.
 *
 * @return void
 */
function render_input_radio_field( array $args ) : void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$options = get_option( 'ttm_forms' );
	$args[ 'value' ] = $options[ $args[ 'id' ] ] ?? '';
	get_partial( 'input-radio', $args );
}

/**
 * Return whether the module is active.
 *
 * @param string $module
 *
 * @return bool
 */
function is_module_active( string $module ) : bool {
	$option = get_option( 'ttm_forms' );
	$active = false;
	if( isset( $option[ "module-$module" ] ) && $option[ "module-$module" ] === 'on' ) {
		$active = true;
	}
	return $active;
}

/**
 * Register TTM Form module.
 *
 * @param string $slug
 * @param string $name
 * @param array $fields
 * @param string $block
 * @param string $parent
 *
 * @return Module
 */
function register_module( string $slug, string $name, array $fields = [], string $block = '', string $parent = '' ) : Module {
	global $ttm_forms_modules;

	$module = new Module( $slug );
	$module->name( $name );

	foreach( $fields as $field ) {
		$module->field( $field[ 'slug' ], $field[ 'label' ], $field[ 'callback' ] ?? null, $field[ 'args' ] ?? [] );
	}

	if( ! empty( $block ) ) {
		$module->block( $block );
	}

	if( ! empty( $parent ) ) {
		$module->parent( $parent );
	}

	$ttm_forms_modules->register( $module );

	return $module;
}

/**
 * Register TTM Form module.
 *
 * @param string $slug
 * @param string $name
 * @param string $parent
 * @param array $fields
 * @param string $block
 *
 * @return Module
 */
function register_submodule( string $slug, string $name, string $parent, array $fields = [], string $block = '' ) : Module {
	return register_module( $slug, $name, $fields, $block, $parent );
}

/**
 * Return the TTM Forms option.
 *
 * @param string $name
 *
 * @return string
 */
function get_ttm_forms_options( string $name ) {
	$options = get_option( 'ttm_forms' );
	return $options[ $name ] ?? '';
}

/**
 * Trigger a webhook via HTTP POST request.
 *
 * @param string $url
 * @param array $body
 *
 * @return bool Whether the HTTP Post request responded with a 200 code.
 */
function webhook_trigger( string $url, array $body ) : bool {
	$args = [
		'body' => $body,
	];
	$response = wp_safe_remote_post( $url, $args );
	$code = wp_remote_retrieve_response_code( $response );
	return $code === 200;
}

/**
 * Process an incoming webhook.
 *
 * @param \WP_REST_Request $request
 *
 * @return bool Whether the incoming webhook was processed correctly.
 */
function webhook_action( \WP_REST_Request $request ) : bool {

	/**
	 * Filter the REST request
	 */
	return apply_filters( 'ttm\forms\process_rest_request', false, $request->get_headers(), $request->get_params() );
}

