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
 *
 * @return Module
 */
function register_module( string $slug, string $name, array $fields = [], string $block = '' ) : Module {
	global $ttm_forms_modules;

	$module = new Module( $slug );
	$module->name( $name );

	foreach( $fields as $field ) {
		$module->field( $field[ 'slug' ], $field[ 'label' ], $field[ 'callback' ] ?? null );
	}

	if( ! empty( $block ) ) {
		$module->block( $block );
	}

	$ttm_forms_modules->register( $module );

	return $module;
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
