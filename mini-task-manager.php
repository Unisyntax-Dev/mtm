<?php
/**
 * Plugin Name: Mini Task Manager
 * Description: Shortcode and admin panel for adding/viewing tasks.
 * Version: 0.1.0
 * Author: UniSyntax
 * Text Domain: mini-task-manager
 */

if (!defined('ABSPATH')) exit; // Prevent direct access to the file

// Define constants for plugin path, URL, and version
define('MTM_PATH', plugin_dir_path(__FILE__)); // Absolute path to plugin directory
define('MTM_URL', plugin_dir_url(__FILE__));   // URL to plugin directory
define('MTM_VER', '0.1.0');                    // Plugin version

// Include the main plugin class
require_once MTM_PATH . 'includes/class-mtm-plugin.php';

// Register activation and deactivation hooks
register_activation_hook(__FILE__, ['MTM_Activator', 'activate']);     // Run on plugin activation
register_deactivation_hook(__FILE__, ['MTM_Deactivator', 'deactivate']); // Run on plugin deactivation

// Initialize the plugin after all plugins are loaded
add_action('plugins_loaded', function () {
    (new MTM_Plugin())->init(); // Create instance and run init method
});
