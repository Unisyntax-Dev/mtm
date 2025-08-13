<?php
if (!defined('ABSPATH')) exit;

class MTM_I18n {
    public function load_textdomain() {
        load_plugin_textdomain('mini-task-manager', false, dirname(plugin_basename(__FILE__), 2) . '/languages/');
    }
}
