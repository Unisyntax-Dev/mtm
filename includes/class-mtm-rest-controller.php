<?php
if (!defined('ABSPATH')) exit;

class MTM_REST_Controller extends WP_REST_Controller {

    public function hooks() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes() {
        $this->namespace = 'mtm/v1';

        register_rest_route($this->namespace, '/tasks', [
            [
                'methods'  => WP_REST_Server::READABLE,   // GET /tasks
                'callback' => [$this, 'list'],
                'permission_callback' => '__return_true',
            ],
            [
                'methods'  => WP_REST_Server::CREATABLE,  // POST /tasks
                'callback' => [$this, 'create'],
                'args'     => [
                    'title'       => ['required' => true, 'type' => 'string'],
                    'description' => ['required' => false, 'type' => 'string'],
                ],
                'permission_callback' => '__return_true',
            ],
        ]);

        register_rest_route($this->namespace, '/tasks/(?P<id>\d+)', [
            [
                'methods'  => WP_REST_Server::DELETABLE,
                'callback' => [$this, 'delete'],
                'permission_callback' => '__return_true',
            ],
        ]);
    }

    public function list(WP_REST_Request $req) {
        $limit = (int) ($req->get_param('limit') ?: 5);
        $db = new MTM_DB();
        $items = $db->get_recent($limit);
        return rest_ensure_response(['success' => true, 'items' => $items]);
    }

    public function create(WP_REST_Request $req) {
        $title = (string) $req->get_param('title');
        $desc  = (string) ($req->get_param('description') ?? '');

        if (trim($title) === '') {
            return new WP_Error('mtm_empty_title', __('Title is required', 'mini-task-manager'), ['status' => 400]);
        }

        $db = new MTM_DB();
        $ok = $db->insert($title, $desc, get_current_user_id() ?: null);
        if (!$ok) {
            return new WP_Error('mtm_insert_fail', __('Insert failed', 'mini-task-manager'), ['status' => 500]);
        }

        $items = $db->get_recent(5);
        return rest_ensure_response(['success' => true, 'items' => $items]);
    }

    public function delete(WP_REST_Request $req) {
        $id = (int) $req['id'];
        if ($id <= 0) {
            return new WP_Error('mtm_bad_id', __('Bad ID', 'mini-task-manager'), ['status' => 400]);
        }

        $db = new MTM_DB();
        $ok = $db->delete($id);
        if (!$ok) {
            return new WP_Error('mtm_delete_fail', __('Delete failed', 'mini-task-manager'), ['status' => 500]);
        }

        $items = $db->get_recent(5);
        return rest_ensure_response(['success' => true, 'items' => $items]);
    }
}
