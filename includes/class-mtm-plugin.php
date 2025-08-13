<?php

if (!defined('ABSPATH')) exit;

$files = [
    'class-mtm-activator.php',
    'class-mtm-deactivator.php',
    'class-mtm-assets.php',
    'class-mtm-admin-menu.php',
    'class-mtm-shortcodes.php',
    'class-mtm-rest-controller.php',
    'class-mtm-db.php',
    'class-mtm-tasks-service.php',
    'class-mtm-i18n.php',
    'class-mtm-settings.php',
];

foreach ($files as $file) {
    $path = MTM_PATH . 'includes/' . $file;
    if (file_exists($path)) {
        require_once $path;
    } else {
        error_log("MTM: missing include {$file}");
    }
}

class MTM_Plugin
{
    public function init() {
        if (class_exists('MTM_I18n'))          (new MTM_I18n())->load_textdomain();
        if (class_exists('MTM_Assets'))        (new MTM_Assets())->hooks();
        if (class_exists('MTM_Admin_Menu'))    (new MTM_Admin_Menu())->hooks();
        if (class_exists('MTM_Shortcodes'))    (new MTM_Shortcodes())->hooks();
        if (class_exists('MTM_REST_Controller')) (new MTM_REST_Controller())->hooks();
    }
}
