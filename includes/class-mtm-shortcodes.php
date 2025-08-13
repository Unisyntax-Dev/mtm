<?php
if (!defined('ABSPATH')) exit;

class MTM_Shortcodes {
    public function hooks() {
        add_shortcode('mini_task_manager', [$this, 'render']);
    }
    public function render() {
        return '<div id="mtm-root"></div>';
    }
}
