<?php
if (!defined('ABSPATH')) exit;

class MTM_Admin_Menu {

    public function hooks() {
        add_action('admin_menu', [$this, 'register']);
    }

    public function register() {
        add_menu_page(
            __('Mini Task Manager', 'mini-task-manager'),
            __('Mini Task Manager', 'mini-task-manager'),
            'manage_options',
            'mtm',
            [$this, 'render'],
            'dashicons-list-view',
            26
        );
    }

    public function render() {
        if (!class_exists('MTM_Settings')) {
            echo '<div class="wrap"><p>' . esc_html__('Settings class missing.', 'mini-task-manager') . '</p></div>';
            return;
        }
        $settings = new MTM_Settings();
        $settings->render_settings_form();
    }
}
