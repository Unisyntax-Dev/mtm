<?php
if (!defined('ABSPATH')) exit;

class MTM_Assets {
    public function hooks() {
        add_action('wp_enqueue_scripts',    [$this, 'enqueue_public']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin']);
    }
    public function enqueue_public() {}
    public function enqueue_admin()  {}
}
