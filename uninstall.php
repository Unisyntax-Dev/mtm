<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * This file runs only on plugin uninstall (not on deactivate).
 * Use it to remove plugin-specific data that should not persist.
 *
 * @package Mini_Task_Manager
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    // Safety guard: exit if this file is called directly.
    exit;
}

global $wpdb;

// Build the full table name with the current site's prefix.
// Note: In multisite, uninstall runs per site context.
$table_name = $wpdb->prefix . 'mtm_tasks';

// Remove the custom table created by the plugin.
// IF EXISTS prevents errors if the table is already gone.
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
