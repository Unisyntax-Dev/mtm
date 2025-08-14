<?php
/**
 * Handles plugin deactivation.
 *
 * Executed when the plugin is deactivated (but not uninstalled).
 * This is the place to remove scheduled events, flush rewrite rules,
 * or clean up transient data if needed.
 *
 * @package Mini_Task_Manager
 */

if (!defined('ABSPATH')) exit; // Prevent direct access to the file

class MTM_Deactivator {

    /**
     * Deactivation callback.
     *
     * Currently empty — no specific cleanup is required on deactivation.
     * Implement if you need to:
     *  - Unschedule WP-Cron events
     *  - Flush rewrite rules
     *  - Remove temporary/transient data
     */
    public static function deactivate() {
        //
    }
}
