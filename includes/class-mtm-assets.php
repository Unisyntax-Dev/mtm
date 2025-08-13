<?php
if (!defined('ABSPATH')) exit;

class MTM_Assets {
    public function hooks() {
        add_action('wp_enqueue_scripts',    [$this, 'enqueue_public']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin']);
    }

    public function enqueue_public() {
        $dir = MTM_PATH . 'assets/dist/';
        $url = MTM_URL  . 'assets/dist/';

        if (file_exists($dir . 'public.js')) {
            if (file_exists($dir . 'public.css')) {
                wp_enqueue_style('mtm-public', $url . 'public.css', [], filemtime($dir.'public.css'));
            }
            wp_enqueue_script('mtm-public', $url . 'public.js', [], filemtime($dir.'public.js'), true);
            wp_localize_script('mtm-public', 'MTM', [
                'rest'  => esc_url_raw( rest_url('mtm/v1') ),
                'nonce' => wp_create_nonce('wp_rest'),
            ]);
        }
    }

    public function enqueue_admin() {
        $dir = MTM_PATH . 'assets/dist/';
        $url = MTM_URL  . 'assets/dist/';

        if (file_exists($dir . 'admin.js')) {
            if (file_exists($dir . 'admin.css')) {
                wp_enqueue_style('mtm-admin', $url . 'admin.css', [], filemtime($dir.'admin.css'));
            }
            wp_enqueue_script('mtm-admin', $url . 'admin.js', [], filemtime($dir.'admin.js'), true);
            wp_localize_script('mtm-admin', 'MTM', [
                'rest'  => esc_url_raw( rest_url('mtm/v1') ),
                'nonce' => wp_create_nonce('wp_rest'),
            ]);
        }
    }
}
