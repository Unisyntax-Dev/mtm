<?php
/**
 * Plugin Name: Mini Task Manager
 * Description: Shortcode and admin panel for adding/viewing tasks
 * Version: 0.1.0
 * Author: UniSyntax
 * Text Domain: mini-task-manager
 */

if (!defined('ABSPATH')) exit;

define('MTM_PATH', plugin_dir_path(__FILE__));
define('MTM_URL', plugin_dir_url(__FILE__));
define('MTM_VER', '0.1.0');

require_once MTM_PATH . 'includes/class-mtm-plugin.php';

register_activation_hook(__FILE__, ['MTM_Activator', 'activate']);
register_deactivation_hook(__FILE__, ['MTM_Deactivator', 'deactivate']);

add_action('plugins_loaded', function () {
    (new MTM_Plugin())->init();
});
