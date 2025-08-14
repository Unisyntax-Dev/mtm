<?php
/**
 * Handles enqueueing of public and admin scripts/styles for Mini Task Manager.
 *
 * Loads compiled assets from /assets/dist and localizes data for React apps.
 *
 * @package Mini_Task_Manager
 */

if (!defined('ABSPATH')) exit; // Prevent direct access

class MTM_Assets {

    /**
     * Attach asset enqueue hooks for front-end and admin.
     */
    public function hooks() {
        add_action('wp_enqueue_scripts',    [$this, 'enqueue_public']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin']);
    }

    /**
     * Enqueue public-facing scripts and styles.
     *
     * Loads compiled public.js/public.css from /assets/dist if they exist.
     * Passes REST API details, nonce, settings, and asset URLs to the script.
     */
    public function enqueue_public() {
        $dir = MTM_PATH . 'assets/dist/';
        $url = MTM_URL  . 'assets/dist/';

        if (file_exists($dir . 'public.js')) {
            if (file_exists($dir . 'public.css')) {
                wp_enqueue_style(
                    'mtm-public',
                    $url . 'public.css',
                    [],
                    filemtime($dir . 'public.css')
                );
            }

            wp_enqueue_script(
                'mtm-public',
                $url . 'public.js',
                [],
                filemtime($dir . 'public.js'),
                true
            );

            // Merge settings with defaults if available.
            $opt = get_option('mtm_settings', []);
            if (!is_array($opt)) $opt = [];
            if (class_exists('MTM_Settings')) {
                $opt = wp_parse_args($opt, MTM_Settings::defaults());
            }

            wp_localize_script('mtm-public', 'MTM', [
                'rest'     => esc_url_raw(untrailingslashit(rest_url('mtm/v1'))),
                'nonce'    => wp_create_nonce('wp_rest'),
                'settings' => $opt,
                'assets'   => [
                    'icons' => MTM_URL . 'assets/dist/icons/',
                ],
            ]);
        }
    }

    /**
     * Enqueue admin settings page scripts and styles.
     *
     * Only runs on the Mini Task Manager settings screen.
     *
     * @param string $hook Current admin page hook suffix.
     */
    public function enqueue_admin($hook) {

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        $screen_id = $screen ? $screen->id : (string) $hook;

        $valid_ids = ['toplevel_page_mtm', 'settings_page_mtm_settings_page'];

        if (!in_array($screen_id, $valid_ids, true) && strpos($screen_id, 'mtm') === false) {
            return;
        }

        $dir = MTM_PATH . 'assets/dist/';
        $url = MTM_URL  . 'assets/dist/';

        if (file_exists($dir . 'admin.js')) {
            if (file_exists($dir . 'admin.css')) {
                wp_enqueue_style('mtm-admin', $url . 'admin.css', [], filemtime($dir . 'admin.css'));
            }

            wp_enqueue_script('mtm-admin', $url . 'admin.js', [], filemtime($dir . 'admin.js'), true);

            wp_localize_script('mtm-admin', 'MTM', [
                'rest'  => esc_url_raw(untrailingslashit(rest_url('mtm/v1'))),
                'nonce' => wp_create_nonce('wp_rest'),
            ]);
        }
    }
}
