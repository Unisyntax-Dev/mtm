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

if ( is_multisite() ) {
    // In multisite, drop the table for each site.
    foreach ( get_sites() as $site ) {
        switch_to_blog( $site->blog_id );

        $table_name = $wpdb->prefix . 'mtm_tasks';
        $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

        restore_current_blog();
    }
} else {
    $table_name = $wpdb->prefix . 'mtm_tasks';
    $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
}
