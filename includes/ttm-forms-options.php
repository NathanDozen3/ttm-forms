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
	 * Add per_page screen option.
	 *
	 * @return void
	 */
	public function add_per_page_options() : void {
		add_screen_option( 'per_page' );
	}


	/**
	 * Set per_page screen option.
	 *
	 * @param mixed $screen_option The value to save instead of the option value. Default false (to skip saving the current option).
	 * @param string $option The option name.
	 * @param int $value The option value.
	 *
	 * @return int
	 */
	public function set_per_page_option( $screen_option, $option, $value ) : int|false {

		$fields = json_encode( $_POST[ 'fields' ] );
		update_user_option( get_current_user_id(), 'toplevel_page_ttm_forms_fields', $fields );

		if( $option === TTM_FORMS_PER_PAGE_OPTIONS_NAME ) {
			return $value;
		}

		return $screen_option;
	}


	/**
	 * Enqueue the TTM Forms settings CSS.
	 *
	 * @return void
	 */
	public function enqueue_ttm_form_settings_css() {
		wp_enqueue_style( 'ttm-forms-settings', plugins_url() . '/ttm-forms/assets/css/ttm-forms-settings.css' );
	}


	/**
	 * Add screen settings checkboxes.
	 *
	 * @param string $settings
	 * @param \WP_Screen $screen
	 *
	 * @return string
	 */
	function screen_settings( string $settings, \WP_Screen $screen ) : string {
		if( 'toplevel_page_ttm-forms' !== $screen->base ) {
			return $settings;
		}

		$user_fields = get_user_option( 'toplevel_page_ttm_forms_fields' ) ?: [];
		if( is_string( $user_fields ) && json_validate( $user_fields ) ) {
			$user_fields = json_decode( $user_fields );
		}
		else {
			$user_fields = [];
		}

		global $ttm_forms_database;
		$fields = $ttm_forms_database->get_record_labels();

		$text = '<fieldset class="screen-options">' .
		'<legend>Fields</legend><div class="fields">';

		foreach( $fields as $field ) {
			$field_title = strtolower( $field );
			$field_title = str_replace(
				[ '_', '-', 'id', 'url', 'ttm' ],
				[ ' ', ' ', 'ID', 'URL', 'TTM' ],
				$field_title
			);
			$field_title = ucwords( $field_title );
			$checked = in_array( $field, $user_fields ) ? 'checked="checked"' : '';
			$t = sprintf(
				'<div class="field"><label class="field-label" for="%1$s">%2$s:</label> <input type="checkbox" id="%1$s" value="%1$s" name="fields[]" %3$s></div>',
				$field,
				$field_title,
				$checked
			);
			$text .= $t;
		}

		$text .= '</div></fieldset>';
		return $text;
	}

}
