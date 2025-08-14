<?php
/**
 * REST API controller for Mini Task Manager.
 *
 * Exposes CRUD endpoints for tasks under /mtm/v1/tasks.
 * Uses MTM_Tasks_Service for data access and respects plugin settings.
 *
 * @package Mini_Task_Manager
 */

if (!defined('ABSPATH')) exit; // Prevent direct access

class MTM_REST_Controller extends WP_REST_Controller {

    /** @var MTM_Tasks_Service Service layer for tasks */
    private $svc;

    public function __construct() {
        $this->namespace = 'mtm/v1';
        $this->rest_base = 'tasks';
        $this->svc = new MTM_Tasks_Service();
    }

    /**
     * Attach controller hooks.
     */
    public function hooks() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST routes for tasks.
     *
     * Routes:
     * - GET    /mtm/v1/tasks?limit=...   -> list()
     * - POST   /mtm/v1/tasks             -> create()
     * - DELETE /mtm/v1/tasks/{id}        -> delete()
     * - PUT    /mtm/v1/tasks/{id}        -> update()
     * - PATCH  /mtm/v1/tasks/{id}        -> update()
     */
    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods'  => WP_REST_Server::READABLE,   // GET /tasks[?limit=...]
                'callback' => [$this, 'list'],
                'permission_callback' => '__return_true',
                'args' => [
                    'limit' => [
                        'type'     => 'integer',
                        'required' => false,
                        'minimum'  => 1,
                        'maximum'  => 100,
                    ],
                ],
            ],
            [
                'methods'  => WP_REST_Server::CREATABLE,  // POST /tasks
                'callback' => [$this, 'create'],
                'permission_callback' => '__return_true',
                'args' => [
                    'title'       => ['required' => true,  'type' => 'string'],
                    'description' => ['required' => false, 'type' => 'string'],
                ],
            ],
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>\d+)', [
            [
                'methods'  => WP_REST_Server::DELETABLE,  // DELETE /tasks/{id}
                'callback' => [$this, 'delete'],
                'permission_callback' => [$this, 'permission_delete'],
            ],
            [
                'methods'  => WP_REST_Server::EDITABLE,   // PUT/PATCH /tasks/{id}
                'callback' => [$this, 'update'],
                'permission_callback' => [$this, 'permission_edit'],
                'args' => [
                    'title'       => ['required' => false, 'type' => 'string'],
                    'description' => ['required' => false, 'type' => 'string'],
                ],
            ],
        ]);
    }

    /**
     * Read items_limit from settings with sane defaults.
     */
    private function get_items_limit_from_settings(): int {
        $opt = get_option('mtm_settings', []);
        if (!is_array($opt)) $opt = [];

        if (!class_exists('MTM_Settings')) {
            return 5;
        }

        $defaults = MTM_Settings::defaults();
        $o = wp_parse_args($opt, $defaults);

        return max(1, min(20, (int)$o['items_limit']));
    }

    /**
     * GET /tasks — return recent tasks (limit from query or settings).
     *
     * @param WP_REST_Request $req
     * @return WP_REST_Response|array
     */
    public function list(WP_REST_Request $req) {
        $limit = $req->get_param('limit');
        if ($limit === null || $limit === '') {
            $limit = $this->get_items_limit_from_settings();
        } else {
            $limit = max(1, min(100, (int)$limit));
        }

        $items = $this->svc->list_recent($limit);
        return rest_ensure_response(['success' => true, 'items' => $items]);
    }

    /**
     * POST /tasks — create a new task and return updated list.
     *
     * @param WP_REST_Request $req
     * @return WP_REST_Response
     */
    public function create(WP_REST_Request $req) {
        $title = (string) $req->get_param('title');
        $desc  = (string) ($req->get_param('description') ?? '');

        $res = $this->svc->create($title, $desc, get_current_user_id() ?: null);
        if (is_wp_error($res)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => $res->get_error_message(),
            ], 400);
        }

        $limit = $this->get_items_limit_from_settings();
        $items = $this->svc->list_recent($limit);

        return new WP_REST_Response([
            'success' => true,
            'item'    => $res,
            'items'   => $items,
        ], 201);
    }

    /**
     * Check if deleting from the list is enabled via settings.
     */
    private function is_delete_enabled(): bool {
        $opt = get_option('mtm_settings', []);
        if (!is_array($opt)) $opt = [];
        if (class_exists('MTM_Settings')) {
            $opt = wp_parse_args($opt, MTM_Settings::defaults());
        }
        return !empty($opt['enable_delete']);
    }

    /**
     * Permission callback for DELETE /tasks/{id}.
     *
     * @param WP_REST_Request $request
     * @return true|WP_Error
     */
    public function permission_delete( $request ) {
        if ( $this->is_delete_enabled() ) {
            return true;
        }
        return new WP_Error(
            'mtm_delete_disabled',
            'Deleting from the list is disabled by settings.',
            ['status' => 403]
        );
    }

    /**
     * DELETE /tasks/{id} — delete a task and return updated list.
     *
     * @param WP_REST_Request $req
     * @return WP_REST_Response
     */
    public function delete(WP_REST_Request $req) {
        $id = (int) $req['id'];
        if ($id <= 0) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Bad ID',
            ], 400);
        }

        $res = $this->svc->delete($id);
        if (is_wp_error($res) || !$res) {
            return new WP_REST_Response([
                'success' => false,
                'message' => is_wp_error($res) ? $res->get_error_message() : 'Delete failed',
            ], 500);
        }

        $limit = $this->get_items_limit_from_settings();
        $items = $this->svc->list_recent($limit);

        return new WP_REST_Response([
            'success' => true,
            'items'   => $items,
        ], 200);
    }

    /**
     * Check if editing from the list is enabled via settings.
     */
    private function is_edit_enabled(): bool {
        $opt = get_option('mtm_settings', []);
        if (!is_array($opt)) $opt = [];
        if (class_exists('MTM_Settings')) {
            $opt = wp_parse_args($opt, MTM_Settings::defaults());
        }
        return !empty($opt['enable_edit']);
    }

    /**
     * Permission callback for PUT/PATCH /tasks/{id}.
     *
     * @param WP_REST_Request $req
     * @return true|WP_Error
     */
    public function permission_edit( $req ) {
        if ( $this->is_edit_enabled() ) return true;
        return new WP_Error(
            'mtm_edit_disabled',
            'Editing from the list is disabled by settings.',
            ['status' => 403]
        );
    }

    /**
     * PUT/PATCH /tasks/{id} — update fields of an existing task.
     *
     * @param WP_REST_Request $req
     * @return WP_REST_Response
     */
    public function update(WP_REST_Request $req) {
        $id = (int) $req['id'];
        if ($id <= 0) {
            return new WP_REST_Response(['success' => false, 'message' => 'Bad ID'], 400);
        }

        $params  = (array) $req->get_json_params();
        $payload = [];
        if (array_key_exists('title', $params))       $payload['title'] = (string) $params['title'];
        if (array_key_exists('description', $params)) $payload['description'] = (string) $params['description'];

        if (!$payload) {
            return new WP_REST_Response(['success' => false, 'message' => 'Nothing to update'], 400);
        }

        $res = $this->svc->update($id, $payload);
        if (is_wp_error($res)) {
            $status = 400;
            switch ($res->get_error_code()) {
                case 'mtm_not_found':
                    $status = 404;
                    break;
                case 'mtm_update_failed':
                    $status = 500;
                    break;
            }
            return new WP_REST_Response(
                ['success' => false, 'message' => $res->get_error_message()],
                $status
            );
        }

        if ($res === null) {
            return new WP_REST_Response(['success' => false, 'message' => 'Task not found'], 404);
        }

        return new WP_REST_Response(['success' => true, 'item' => $res], 200);
    }
}
