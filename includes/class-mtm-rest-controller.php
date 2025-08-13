<?php
if (!defined('ABSPATH')) exit;

class MTM_REST_Controller extends WP_REST_Controller {
    /** @var MTM_Tasks_Service */
    private $svc;

    public function __construct() {
        $this->namespace = 'mtm/v1';
        $this->rest_base = 'tasks';
        $this->svc = new MTM_Tasks_Service();
    }

    public function hooks() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

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
                        // ⚠️ убрали 'default', чтобы можно было fallback'нуть к настройкам
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
                'methods'  => WP_REST_Server::DELETABLE, // DELETE /tasks/{id}
                'callback' => [$this, 'delete'],
                'permission_callback' => '__return_true',
            ],
            [
                'methods'  => WP_REST_Server::EDITABLE,  // PUT/PATCH /tasks/{id}
                'callback' => [$this, 'update'],
                'permission_callback' => '__return_true',
                'args' => [
                    'title'       => ['required' => false, 'type' => 'string'],
                    'description' => ['required' => false, 'type' => 'string'],
                ],
            ],
        ]);
    }

    private function get_items_limit_from_settings(): int {
        $opt = get_option('mtm_settings', []);
        if (!is_array($opt)) $opt = [];

        // если класс настроек недоступен — подстрахуемся
        if (!class_exists('MTM_Settings')) {
            return 5;
        }

        $defaults = MTM_Settings::defaults();
        $o = wp_parse_args($opt, $defaults);

        // настройки уже санитизируются до 1–20, но продублируем
        return max(1, min(20, (int)$o['items_limit']));
    }

    public function list(WP_REST_Request $req) {
        $limit = $req->get_param('limit');
        if ($limit === null || $limit === '') {
            // если limit не передали — берём из настроек
            $limit = $this->get_items_limit_from_settings();
        } else {
            $limit = max(1, min(100, (int)$limit)); // если передали — уважаем
        }

        $items = $this->svc->list_recent($limit);
        return rest_ensure_response(['success' => true, 'items' => $items]);
    }

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

        // ⚠️ раньше тут было list_recent(5) — теперь уважаем настройку
        $limit = $this->get_items_limit_from_settings();
        $items = $this->svc->list_recent($limit);

        return new WP_REST_Response([
            'success' => true,
            'item'    => $res,
            'items'   => $items,
        ], 201);
    }

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

        // ⚠️ раньше тут было list_recent(5) — теперь уважаем настройку
        $limit = $this->get_items_limit_from_settings();
        $items = $this->svc->list_recent($limit);

        return new WP_REST_Response([
            'success' => true,
            'items'   => $items,
        ], 200);
    }

    public function update(WP_REST_Request $req) {
        $id = (int) $req['id'];
        if ($id <= 0) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Bad ID',
            ], 400);
        }

        $payload = [];
        if ($req->offsetExists('title'))       $payload['title'] = (string)$req->get_param('title');
        if ($req->offsetExists('description')) $payload['description'] = (string)$req->get_param('description');

        $res = $this->svc->update($id, $payload);
        if (is_wp_error($res)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => $res->get_error_message(),
            ], 400);
        }

        return new WP_REST_Response([
            'success' => true,
            'item'    => $res,
        ], 200);
    }
}
