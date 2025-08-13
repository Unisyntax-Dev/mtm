<?php
if (!defined('ABSPATH')) exit;

class MTM_REST_Controller extends WP_REST_Controller {
    public function hooks() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    public function register_routes() {
      //
    }
}
