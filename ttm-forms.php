<?php
/**
 * Plugin Name: TTM Forms
 * Plugin URI:
 * Description: Create Gutenberg-first, mobile-first, accessibility-first forms and view entries in the WordPress admin.
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Twelve Three Media
 * Author URI: https://www.digitalmarketingcompany.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ttm-forms
 */

namespace ttm\forms;

global $wpdb;
define( 'TTM_FORMS_FILE', __FILE__ );
define( 'TTM_FORMS_DIR', __DIR__ );
define( 'TTM_FORMS_BLOCKS_DIR', TTM_FORMS_DIR . '/blocks/' );
define( 'TTM_FORMS_TABLE_NAME', $wpdb->prefix . 'ttm_forms' );
define( 'TTM_FORMS_PER_PAGE_OPTIONS_NAME', 'toplevel_page_ttm_forms_per_page' );
define( 'TTM_FORMS_HONEYPOT_POST_VAR', 'url' );

require 'vendor/autoload.php';

require TTM_FORMS_DIR . '/includes/ttm-forms-akismet.php';
require TTM_FORMS_DIR . '/includes/ttm-forms-blocks.php';
require TTM_FORMS_DIR . '/includes/ttm-forms-database.php';
require TTM_FORMS_DIR . '/includes/ttm-forms-functions.php';
require TTM_FORMS_DIR . '/includes/ttm-forms-mailgun.php';
require TTM_FORMS_DIR . '/includes/ttm-forms-module.php';
require TTM_FORMS_DIR . '/includes/ttm-forms-modules.php';
require TTM_FORMS_DIR . '/includes/ttm-forms-options.php';
require TTM_FORMS_DIR . '/includes/ttm-forms-postmark.php';
require TTM_FORMS_DIR . '/includes/ttm-forms-recaptcha.php';
require TTM_FORMS_DIR . '/includes/ttm-forms-rest.php';
require TTM_FORMS_DIR . '/includes/ttm-forms-sendgrid.php';
require TTM_FORMS_DIR . '/includes/ttm-forms-turnstile.php';
require TTM_FORMS_DIR . '/includes/ttm-forms-webhooks.php';

$ttm_forms_blocks = new Blocks();
add_action( is_admin() ? 'admin_init' : 'init',  [ $ttm_forms_blocks, 'register_blocks' ], 20 );
add_action( 'enqueue_block_assets', [ $ttm_forms_blocks, 'enqueue_block_assets' ] );
add_filter( 'render_block_ttm/recaptcha', [ $ttm_forms_blocks, 'add_recaptcha_site_key' ], 10, 3 );
add_filter( 'render_block_core/block', [ $ttm_forms_blocks, 'add_hidden_field_to_ttm_form' ], 10, 3 );
add_filter( 'render_block_ttm/form', [ $ttm_forms_blocks, 'add_honeypot_to_ttm_form' ], 20, 3 );
add_filter( 'render_block_ttm/form', [ $ttm_forms_blocks, 'form_footer' ], 20, 3 );

$ttm_forms_database = new Database();
add_action( 'init', [ $ttm_forms_database, 'process_form' ], 0 );
register_activation_hook( TTM_FORMS_FILE, [ $ttm_forms_database, 'create_database_table' ] );

$ttm_forms_options = new Options();
add_action( 'admin_menu', [ $ttm_forms_options, 'add_form_menu_to_admin_menu' ] );
add_action( 'admin_init', [ $ttm_forms_options, 'register_ttm_forms_settings' ] );
add_filter( 'plugin_action_links_ttm-forms/ttm-forms.php', [ $ttm_forms_options, 'add_settings_link_to_plugins_administration_page' ] );
add_action( 'load-forms_page_ttm-forms-settings', [ $ttm_forms_options, 'enqueue_ttm_form_settings_css' ] );
add_action( 'load-toplevel_page_ttm-forms', [ $ttm_forms_options, 'enqueue_ttm_form_settings_css' ] );
add_action( 'load-toplevel_page_ttm-forms', [ $ttm_forms_options, 'add_per_page_options' ] );
add_action( 'load-toplevel_page_ttm-forms', [ $ttm_forms_options, 'enqueue_wp_api' ] );
add_filter( 'set-screen-option', [ $ttm_forms_options, 'set_per_page_option' ], 11, 3);
add_filter( 'screen_settings', [ $ttm_forms_options, 'screen_settings' ], 10, 2 );

$ttm_forms_rest = new Rest();
add_action( 'rest_api_init', [ $ttm_forms_rest, 'register_routes' ] );

$ttm_forms_sendgrid = new Sendgrid();
add_action( 'pre_wp_mail', [ $ttm_forms_sendgrid, 'pre_wp_mail' ], 10, 2 );

$ttm_forms_mailgun = new Mailgun_SMTP();
add_action( 'pre_wp_mail', [ $ttm_forms_mailgun, 'pre_wp_mail' ], 10, 2 );

$ttm_forms_postmark = new Postmark();
add_action( 'pre_wp_mail', [ $ttm_forms_postmark, 'pre_wp_mail' ], 10, 2 );

$ttm_forms_modules = new Modules();

$ttm_forms_akismet = new Akismet();
add_filter( 'ttm\forms\fields\pre_insert', [ $ttm_forms_akismet, 'pre_insert' ] );

$ttm_forms_webhooks = new Webhooks();
add_filter( 'get_to_ping', [ $ttm_forms_webhooks, 'get_to_ping' ] );
add_action( 'pre_ping', [ $ttm_forms_webhooks, 'pre_ping' ], 10, 3 );
add_filter( 'enclosure_links', [ $ttm_forms_webhooks, 'enclosure_links' ] );
add_action( 'ttm\forms\email\sent', [ $ttm_forms_webhooks, 'process_webhooks' ] );

$ttm_forms_turnstile = new Turnstile();
add_filter( 'render_block_ttm/turnstile', [ $ttm_forms_turnstile, 'add_turnstile_site_key' ], 10, 3 );

register_module(
	slug: 'recaptcha',
	name: __( 'reCAPTCHA', 'ttm-forms' ),
	block: 'ttm-recaptcha',
	fields: [
		[
			'slug' => 'site-key',
			'label' => __( 'Site Key', 'ttm-forms' ),
			'callback' => '\ttm\forms\render_input_text_field',
		],
		[
			'slug' => 'secret-key',
			'label' => __( 'Secret Key', 'ttm-forms' ),
			'callback' => '\ttm\forms\render_input_password_field',
		],
	],
);

register_module(
	slug: 'credit-card',
	name: __( 'Credit Card', 'ttm-forms' ),
	block: 'ttm-credit-card',
);

register_module(
	slug: 'akismet',
	name: __( 'Akismet', 'ttm-forms' ),
	fields: [
		[
			'slug' => 'akismet-api-key',
			'label' => __( 'API Key', 'ttm-forms' ),
			'callback' => '\ttm\forms\render_input_password_field',
		],
	],
);

register_module(
	slug: 'webhooks',
	name: __( 'Webhooks', 'ttm-forms' ),
	fields: [
		[
			'slug' => 'webhooks-api-key',
			'label' => __( 'API Key', 'ttm-forms' ),
			'callback' => '\ttm\forms\render_input_text_field',
		],
	],
);

register_module(
	slug: 'turnstile',
	name: __( 'Turnstile', 'ttm-forms' ),
	block: 'ttm-turnstile',
	fields: [
		[
			'slug' => 'turnstile-site-key',
			'label' => __( 'Site Key', 'ttm-forms' ),
			'callback' => '\ttm\forms\render_input_text_field',
		],
		[
			'slug' => 'turnstile-secret-key',
			'label' => __( 'Secret Key', 'ttm-forms' ),
			'callback' => '\ttm\forms\render_input_password_field',
		],
	],
);

register_module(
	slug: 'smtp',
	name: __( 'SMTP', 'ttm-forms' ),
	fields: [
		[
			'slug' => 'smtp',
			'label' => __( 'SMTP Solution', 'ttm-forms' ),
			'callback' => '\ttm\forms\render_input_radio_field',
			'args' => [
				'name' => 'smtp',
				'description' => '',
				'options' => [
					'sendgrid' => 'SendGrid',
					'mailgun' => 'Mailgun',
					'postmark' => 'Postmark',
					'custom' => 'Custom',
				],
			],
		],
	],
);

register_submodule(
	slug: 'sendgrid',
	name: __( 'SendGrid', 'ttm-forms' ),
	parent: 'smtp',
	fields: [
		[
			'slug' => 'sendgrid-api-key',
			'label' => __( 'API Key', 'ttm-forms' ),
			'callback' => '\ttm\forms\render_input_password_field',
		],
		[
			'slug' => 'sendgrid-from-email',
			'label' => __( 'From Email', 'ttm-forms' ),
			'callback' => '\ttm\forms\render_input_text_field',
		],
		[
			'slug' => 'sendgrid-from-name',
			'label' => __( 'From Name', 'ttm-forms' ),
			'callback' => '\ttm\forms\render_input_text_field',
		],
	],
);

register_submodule(
	slug: 'mailgun',
	name: __( 'Mailgun', 'ttm-forms' ),
	parent: 'smtp',
	fields: [
		[
			'slug' => 'mailgun-api-key',
			'label' => __( 'API Key', 'ttm-forms' ),
			'callback' => '\ttm\forms\render_input_text_field',
		],
		[
			'slug' => 'mailgun-domain',
			'label' => __( 'Domain', 'ttm-forms' ),
			'callback' => '\ttm\forms\render_input_text_field',
		],
		[
			'slug' => 'mailgun-from-email',
			'label' => __( 'From Email', 'ttm-forms' ),
			'callback' => '\ttm\forms\render_input_text_field',
		],
	],
);

register_submodule(
	slug: 'postmark',
	name: __( 'Postmark', 'ttm-forms' ),
	parent: 'smtp',
	fields: [
		[
			'slug' => 'postmark-api-key',
			'label' => __( 'API Key', 'ttm-forms' ),
			'callback' => '\ttm\forms\render_input_text_field',
		],
		[
			'slug' => 'postmark-from-email',
			'label' => __( 'From Email', 'ttm-forms' ),
			'callback' => '\ttm\forms\render_input_text_field',
		],
	],
);
