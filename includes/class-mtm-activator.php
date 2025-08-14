<?php
/**
 * Handles plugin activation tasks.
 *
 * Creates required database tables on activation.
 * Supports both single-site and multisite (network) activation.
 *
 * @package Mini_Task_Manager
 */

if (!defined('ABSPATH')) exit; // Prevent direct access

class MTM_Activator
{
    /**
     * Called by WordPress when the plugin is activated.
     *
     * Network activation is supported for multisite installations.
     *
     * @param bool $network_wide True if the plugin is activated network-wide in multisite.
     */
    public static function activate($network_wide = false)
    {
        if (is_multisite() && $network_wide) {
            // Create the table on each site in the network.
            $sites = get_sites(['fields' => 'ids']);
            foreach ($sites as $blog_id) {
                switch_to_blog($blog_id);
                self::create_tables();
                restore_current_blog();
            }
        } else {
            // Regular activation (single site).
            self::create_tables();
        }
    }

    /**
     * Create plugin-specific database tables.
     *
     * Uses dbDelta() to handle both creation and schema updates.
     */
    private static function create_tables()
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table_name      = $wpdb->prefix . 'mtm_tasks';
        $charset_collate = $wpdb->get_charset_collate();

        // Main task table schema
        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            description LONGTEXT NULL,
            created_by BIGINT UNSIGNED NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY created_by (created_by),
            KEY created_at (created_at)
        ) {$charset_collate};";

        // dbDelta will create or update the table as needed
        dbDelta($sql);

        // Store the schema version in options — useful for future migrations
        add_option('mtm_db_version', '1.0.0');
    }
}
