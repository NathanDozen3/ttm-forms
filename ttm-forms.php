<?php
/**
 * Plugin Name: TTM Forms
 * Plugin URI: 
 * Description: Another form plugin.
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 7.3
 * Author: Twelve Three Media
 * Author URI: https://www.digitalmarketingcompany.com/
 * Text Domain: ttm-forms
 */

namespace ttm\forms;

global $wpdb;
define( 'TTM_FORMS_FILE', __FILE__ );
define( 'TTM_FORMS_DIR', __DIR__ );
define( 'TTM_FORMS_TABLE_NAME', $wpdb->prefix . 'ttm_forms' );

require TTM_FORMS_DIR . '/ttm-forms-blocks.php';
$ttm_forms_blocks = new Blocks();
add_action( 'init',  [ $ttm_forms_blocks, 'register_blocks' ] );

require TTM_FORMS_DIR . '/ttm-forms-database.php';
$ttm_forms_database = new Database();
add_action( 'plugins_loaded', [ $ttm_forms_database, 'process_form' ] );
register_activation_hook( TTM_FORMS_FILE, [ $ttm_forms_database, 'create_database_table' ] );
