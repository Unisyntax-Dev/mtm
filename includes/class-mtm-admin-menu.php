<?php
/**
 * Registers the admin menu for Mini Task Manager and renders the settings page.
 *
 * @package Mini_Task_Manager
 */

if (!defined('ABSPATH')) exit; // Prevent direct access

class MTM_Admin_Menu {

    /**
     * Attach admin menu registration hook.
     */
    public function hooks() {
        add_action('admin_menu', [$this, 'register']);
    }

    /**
     * Register the top-level admin menu page.
     *
     * Capability: manage_options
     * Icon: dashicons-list-view
     * Position: 26 (just below Comments)
     */
    public function register() {
        add_menu_page(
            __('Mini Task Manager', 'mini-task-manager'), // Page title
            __('Mini Task Manager', 'mini-task-manager'), // Menu title
            'manage_options',                             // Capability required
            'mtm',                                        // Menu slug
            [$this, 'render'],                            // Callback to render page
            'dashicons-list-view',                        // Menu icon
            26                                            // Menu position
        );
    }

    /**
     * Render the admin settings page.
     *
     * Checks for the MTM_Settings class and displays the form; otherwise shows an error message.
     */
    public function render() {
        if (!class_exists('MTM_Settings')) {
            echo '<div class="wrap"><p>' .
                esc_html__('Settings class missing.', 'mini-task-manager') .
                '</p></div>';
            return;
        }
        $settings = new MTM_Settings();
        $settings->render_settings_form();
    }
}
