<?php

namespace ttm\forms;

/**
 *
 */
class Options {

	/**
	 * Register TTM Forms settings.
	 *
	 * @return void
	 */
	public function register_ttm_forms_settings() : void {
		register_setting( 'ttm-forms-settings', 'ttm_forms' );

		add_settings_section(
			'ttm-forms-settings-section',
			__( '', 'ttm-forms' ),
			'__return_null',
			'ttm-forms-settings'
		);

		add_settings_field(
			'ttm-forms-site-key',
			__( 'Site Key', 'ttm-forms' ),
			[ $this, 'render_input_text_field' ],
			'ttm-forms-settings',
			'ttm-forms-settings-section',
			[
				'id' => 'site-key',
			]
		);

		add_settings_field(
			'ttm-forms-secret-key',
			__( 'Secret Key', 'ttm-forms' ),
			[ $this, 'render_input_password_field' ],
			'ttm-forms-settings',
			'ttm-forms-settings-section',
			[
				'id' => 'secret-key',
			]
		);
	}


	/**
	 * Add submenu page to general options menu.
	 *
	 * @return void
	 */
	public function add_form_menu_to_admin_menu() : void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_menu_page(
			__( 'TTM Forms', 'ttm-forms' ),
			__( 'Forms', 'ttm-forms' ),
			'manage_options',
			'ttm-forms',
			[ $this, 'render_options_page' ],
			'dashicons-format-aside',
			$position = 50
		);

		add_submenu_page(
			'ttm-forms',
			__( 'TTM Form Settings', 'ttm-forms' ),
			__( 'Settings', 'ttm-forms' ),
			'manage_options',
			'ttm-forms-settings',
			[ $this, 'render_settings_page' ]
		);
	}


	/**
	 * Print the TTM Forms options page.
	 *
	 * @return void
	 */
	public function render_options_page() : void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if( ! class_exists( 'WP_List_Table' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}
		require TTM_FORMS_DIR . '/includes/ttm-forms-list-table.php';

		get_partial( 'options-page' );
	}


	/**
	 * Print the TTM Forms settings page.
	 *
	 * @return void
	 */
	public function render_settings_page() : void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		get_partial( 'settings-page' );
	}


	/**
	 * Print a text area field.
	 *
	 * @param array $args This is the description.
	 *
	 * @return void
	 */
	public function render_textarea_field( array $args ) : void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$options = get_option( 'ttm_forms' );
		$args[ 'value' ] = $options[ $args[ 'id' ] ] ?? '';
		get_partial( 'textarea', $args );
	}


	/**
	 * Print a text input field.
	 *
	 * @param array $args This is the description.
	 *
	 * @return void
	 */
	public function render_input_text_field( array $args ) : void {
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
	public function render_input_password_field( array $args ) : void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$options = get_option( 'ttm_forms' );
		$args[ 'value' ] = $options[ $args[ 'id' ] ] ?? '';
		get_partial( 'input-password', $args );
	}


	/**
	 * Add settings link to plugins administration page.
	 *
	 * @param array $links An array of links for the plugin.
	 *
	 * @return array
	 */
	public function add_settings_link_to_plugins_administration_page( array $links ) : array {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $links;
		}

		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( '/wp-admin/admin.php?page=ttm-forms-settings' ) ),
			esc_html__( 'Settings', 'ttm-forms' )
		);

		array_unshift( $links, $settings_link );
		return $links;
	}


	/**
	 * Enqueue the TTM Forms settings CSS.
	 *
	 * @return void
	 */
	public function enqueue_ttm_form_settings_css() {
		wp_enqueue_style( 'ttm-forms-settings', plugins_url() . '/ttm-forms/assets/css/ttm-forms-settings.css' );
	}
}
