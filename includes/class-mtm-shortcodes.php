<?php
if (!defined('ABSPATH')) exit; // Prevent direct access to the file

class MTM_Shortcodes {

    /**
     * Register shortcode hooks.
     */
    public function hooks() {
        // Register the [mini_task_manager] shortcode
        add_shortcode('mini_task_manager', [$this, 'render']);
    }

    /**
     * Render the shortcode output.
     *
     * @return string HTML container for the React app
     */
    public function render() {
        // This div will be the mount point for the public React app
        return '<div id="mtm-root"></div>';
    }
}
