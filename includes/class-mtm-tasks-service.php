<?php
if (!defined('ABSPATH')) exit; // Prevent direct access to the file

class MTM_Tasks_Service {
    /** @var string Full table name including WP prefix */
    private $table;

    public function __construct() {
        global $wpdb;
        // Set the table name for tasks
        $this->table = $wpdb->prefix . 'mtm_tasks';
    }

    /* =======================
     * Create
     * ======================= */
    /**
     * Create a new task.
     *
     * @param string   $title       Task title (required)
     * @param string   $description Task description (optional)
     * @param int|null $user_id     Creator's user ID (optional)
     * @return array|WP_Error       Created task data or error object
     */
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
            'created_at'  => current_time('mysql', true), // UTC timestamp
        ];

        $formats = ['%s', '%s', '%s'];

        if ($user_id !== null) {
            $data['created_by'] = (int) $user_id;
            $formats[] = '%d';
        }

        $ok = $wpdb->insert($this->table, $data, $formats);
        if (!$ok) {
            return new WP_Error('mtm_insert_failed', __('Failed to create task', 'mini-task-manager'));
        }

        return $this->get((int)$wpdb->insert_id);
    }

    /* =======================
     * Read (single task)
     * ======================= */
    /**
     * Get a single task by ID.
     *
     * @param int $id
     * @return array|null
     */
    public function get(int $id) {
        global $wpdb;
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT id, title, description, created_at FROM {$this->table} WHERE id = %d", $id),
            ARRAY_A
        );
        return $row ? $this->format_row($row) : null;
    }

    /* =======================
     * Read (list recent tasks)
     * ======================= */
    /**
     * Get a list of the most recent tasks.
     *
     * @param int $limit
     * @return array
     */
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
    /**
     * Update an existing task.
     *
     * @param int   $id
     * @param array $fields
     * @return array|WP_Error
     */
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

        if ($ok === 0) {
            return new WP_Error('mtm_not_found', __('Task not found', 'mini-task-manager'));
        }

        return $this->get($id);
    }

    /* =======================
     * Delete
     * ======================= */
    /**
     * Delete a task by ID.
     *
     * @param int $id
     * @return bool|WP_Error
     */
    public function delete(int $id) {
        global $wpdb;
        $deleted = $wpdb->delete($this->table, ['id' => (int)$id], ['%d']);
        if ($deleted === false) {
            return new WP_Error('mtm_delete_failed', __('Failed to delete task', 'mini-task-manager'));
        }
        if ($deleted === 0) {
            return new WP_Error('mtm_not_found', __('Task not found', 'mini-task-manager'));
        }
        return true;
    }

    /* =======================
     * Helpers
     * ======================= */

    /**
     * Sanitize and trim task title.
     */
    private function sanitize_title(string $title): string {
        $title = trim(wp_strip_all_tags($title));
        if (function_exists('mb_substr')) {
            $title = mb_substr($title, 0, 255);
        } else {
            $title = substr($title, 0, 255);
        }
        return $title;
    }

    /**
     * Sanitize task description, allow basic HTML, and limit length.
     */
    private function sanitize_description(string $desc): string {
        // Allow basic formatting tags
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
        // Limit length
        if (function_exists('mb_substr')) {
            $desc = mb_substr($desc, 0, 5000);
        } else {
            $desc = substr($desc, 0, 5000);
        }
        return $desc;
    }

    /**
     * Format a task row for output, including localized date formatting.
     */
    private function format_row(array $row): array {
        // Convert UTC date to WP local format
        if (!empty($row['created_at'])) {
            $ts = strtotime($row['created_at'] . ' UTC');
            $row['created_at'] = $ts ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $ts) : $row['created_at'];
        }
        // Return formatted array
        return [
            'id'          => (int)$row['id'],
            'title'       => $row['title'],
            'description' => $row['description'],
            'created_at'  => $row['created_at'] ?? null,
        ];
    }
}
