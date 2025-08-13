<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Mini_Task_Manager
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

$table_name = $wpdb->prefix . 'mtm_tasks';

// Remove table
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
