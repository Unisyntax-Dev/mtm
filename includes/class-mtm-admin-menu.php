<?php
if (!defined('ABSPATH')) exit;

class MTM_Admin_Menu {
    public function hooks() {
        add_action('admin_menu', [$this, 'register']);
    }
    public function register() {
        //
    }
}
