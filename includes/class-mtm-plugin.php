<?php
/**
 * Main plugin bootstrap: loads classes and wires hooks.
 *
 * Includes required class files and initializes feature modules
 * (assets, admin menu, settings, shortcodes, REST controller).
 *
 * @package Mini_Task_Manager
 */

if (!defined('ABSPATH')) exit; // Prevent direct access to the file

// List of include file names located under /includes
$files = [
    'class-mtm-activator.php',
    'class-mtm-deactivator.php',
    'class-mtm-assets.php',
    'class-mtm-admin-menu.php',
    'class-mtm-shortcodes.php',
    'class-mtm-rest-controller.php',
    'class-mtm-tasks-service.php',
    'class-mtm-settings.php',
];

// Require each class file if present; log a warning if missing.
foreach ($files as $file) {
    $path = MTM_PATH . 'includes/' . $file;
    if (file_exists($path)) {
        require_once $path;
    } else {
        // Using error_log keeps front-end silent while surfacing the issue in logs.
        error_log("MTM: missing include {$file}");
    }
}

/**
 * Plugin orchestrator: attaches hooks for all submodules.
 */
class MTM_Plugin
{
    /**
     * Initialize plugin modules and register their hooks.
     *
     * Each module exposes a ->hooks() method to self-register all WP actions/filters.
     * We check class existence to avoid fatals if a file failed to load.
     */
    public function init() {
        if (class_exists('MTM_Assets'))           (new MTM_Assets())->hooks();             // Enqueue public/admin assets
        if (class_exists('MTM_Admin_Menu'))       (new MTM_Admin_Menu())->hooks();         // Admin menu & settings page
        if (class_exists('MTM_Settings'))         (new MTM_Settings())->hooks();           // Register settings
        if (class_exists('MTM_Shortcodes'))       (new MTM_Shortcodes())->hooks();         // [mini_task_manager] shortcode
        if (class_exists('MTM_REST_Controller'))  (new MTM_REST_Controller())->hooks();    // REST API routes
    }
}
