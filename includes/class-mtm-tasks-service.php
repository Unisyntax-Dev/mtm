<?php
if (!defined('ABSPATH')) exit;

class MTM_Tasks_Service {
    /** @var string */
    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'mtm_tasks';
    }

    /* =======================
     * Create
     * ======================= */
    public function create(string $title, string $description = '', ?int $user_id = null) {
        global $wpdb;

        $title = $this->sanitize_title($title);
        $description = $this->sanitize_description($description);

        if ($title === '') {
            return new WP_Error('mtm_empty_title', __('Title is required', 'mini-task-manager'));
        }

        $data = [
            'title'       => $title,
            'description' => $description,
            'created_by'  => $user_id ? (int)$user_id : null,
            'created_at'  => current_time('mysql', true), // UTC
        ];

        $formats = ['%s', '%s', '%d', '%s'];

        $ok = $wpdb->insert($this->table, $data, $formats);
        if (!$ok) {
            return new WP_Error('mtm_insert_failed', __('Failed to create task', 'mini-task-manager'));
        }

        return $this->get((int)$wpdb->insert_id);
    }

    /* =======================
     * Read (one)
     * ======================= */
    public function get(int $id) {
        global $wpdb;
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT id, title, description, created_at FROM {$this->table} WHERE id = %d", $id),
            ARRAY_A
        );
        return $row ? $this->format_row($row) : null;
    }

    /* =======================
     * Read (list recent)
     * ======================= */
    public function list_recent(int $limit = 5): array {
        global $wpdb;
        $limit = max(1, (int)$limit);

        $items = $wpdb->get_results(
            $wpdb->prepare("
                SELECT id, title, description, created_at
                FROM {$this->table}
                ORDER BY created_at DESC, id DESC
                LIMIT %d
            ", $limit),
            ARRAY_A
        );

        return array_map([$this, 'format_row'], $items ?: []);
    }

    /* =======================
     * Update
     * ======================= */
    public function update(int $id, array $fields) {
        global $wpdb;

        $data = [];
        $formats = [];

        if (isset($fields['title'])) {
            $title = $this->sanitize_title((string)$fields['title']);
            if ($title === '') {
                return new WP_Error('mtm_empty_title', __('Title is required', 'mini-task-manager'));
            }
            $data['title'] = $title;
            $formats[] = '%s';
        }

        if (isset($fields['description'])) {
            $data['description'] = $this->sanitize_description((string)$fields['description']);
            $formats[] = '%s';
        }

        if (!$data) {
            return new WP_Error('mtm_no_fields', __('Nothing to update', 'mini-task-manager'));
        }

        $ok = $wpdb->update($this->table, $data, ['id' => (int)$id], $formats, ['%d']);
        if ($ok === false) {
            return new WP_Error('mtm_update_failed', __('Failed to update task', 'mini-task-manager'));
        }

        return $this->get($id);
    }

    /* =======================
     * Delete
     * ======================= */
    public function delete(int $id) {
        global $wpdb;
        $deleted = $wpdb->delete($this->table, ['id' => (int)$id], ['%d']);
        if (!$deleted) {
            return new WP_Error('mtm_delete_failed', __('Failed to delete task', 'mini-task-manager'));
        }
        return true;
    }

    /* =======================
     * Helpers
     * ======================= */

    private function sanitize_title(string $title): string {
        $title = trim(wp_strip_all_tags($title));
        if (function_exists('mb_substr')) {
            $title = mb_substr($title, 0, 255);
        } else {
            $title = substr($title, 0, 255);
        }
        return $title;
    }

    private function sanitize_description(string $desc): string {
        // Разрешим базовую разметку
        $allowed = [
            'a'      => ['href' => true, 'title' => true, 'target' => true, 'rel' => true],
            'br'     => [],
            'em'     => [],
            'strong' => [],
            'p'      => [],
            'ul'     => [], 'ol' => [], 'li' => [],
            'code'   => [], 'pre' => [],
        ];
        $desc = wp_kses($desc, $allowed);
        // Ограничим разумную длину
        if (function_exists('mb_substr')) {
            $desc = mb_substr($desc, 0, 5000);
        } else {
            $desc = substr($desc, 0, 5000);
        }
        return $desc;
    }

    private function format_row(array $row): array {
        // Приводим формат даты к локали WP (по желанию)
        if (!empty($row['created_at'])) {
            $ts = strtotime($row['created_at'] . ' UTC');
            $row['created_at'] = $ts ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $ts) : $row['created_at'];
        }
        // Безопасный вывод делай на уровне шаблона (esc_html и т.п.)
        return [
            'id'          => (int)$row['id'],
            'title'       => $row['title'],
            'description' => $row['description'],
            'created_at'  => $row['created_at'] ?? null,
        ];
    }
}
