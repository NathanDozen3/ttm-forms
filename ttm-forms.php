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
define( 'TTM_FORMS_TABLE_NAME', $wpdb->prefix . 'ttm_forms' );
define( 'TTM_FORMS_PER_PAGE_OPTIONS_NAME', 'toplevel_page_ttm_forms_per_page' );

require TTM_FORMS_DIR . '/includes/ttm-forms-blocks.php';
require TTM_FORMS_DIR . '/includes/ttm-forms-database.php';
require TTM_FORMS_DIR . '/includes/ttm-forms-functions.php';
require TTM_FORMS_DIR . '/includes/ttm-forms-options.php';
require TTM_FORMS_DIR . '/includes/ttm-forms-recaptcha.php';

$ttm_forms_blocks = new Blocks();
add_action( 'init',  [ $ttm_forms_blocks, 'register_blocks' ] );
add_action( 'enqueue_block_assets', [ $ttm_forms_blocks, 'enqueue_block_assets' ] );
add_filter( 'render_block_ttm/recaptcha', [ $ttm_forms_blocks, 'add_recaptcha_site_key' ], 10, 3 );
add_filter( 'render_block_core/block', [ $ttm_forms_blocks, 'add_hidden_field_to_ttm_form' ], 10, 3 );

$ttm_forms_database = new Database();
add_action( 'init', [ $ttm_forms_database, 'process_form' ] );
register_activation_hook( TTM_FORMS_FILE, [ $ttm_forms_database, 'create_database_table' ] );

$ttm_forms_options = new Options();
add_action( 'admin_menu', [ $ttm_forms_options, 'add_form_menu_to_admin_menu' ] );
add_action( 'admin_init', [ $ttm_forms_options, 'register_ttm_forms_settings' ] );
add_filter( 'plugin_action_links_ttm-forms/ttm-forms.php', [ $ttm_forms_options, 'add_settings_link_to_plugins_administration_page' ] );
add_action( 'load-forms_page_ttm-forms-settings', [ $ttm_forms_options, 'enqueue_ttm_form_settings_css' ] );
add_action( 'load-toplevel_page_ttm-forms', [ $ttm_forms_options, 'add_per_page_options' ] );
add_filter( 'set-screen-option', [ $ttm_forms_options, 'set_per_page_option' ], 11, 3);
