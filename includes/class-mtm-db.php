<?php
if (!defined('ABSPATH')) exit;

class MTM_DB {
    private $table;
    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'mtm_tasks';
    }

    public function get_recent($limit = 5) {
        global $wpdb;
        $limit = max(1, (int)$limit);
        $sql = $wpdb->prepare("SELECT id, title, description, created_at FROM {$this->table} ORDER BY created_at DESC, id DESC LIMIT %d", $limit);
        return $wpdb->get_results($sql, ARRAY_A);
    }

    public function insert($title, $description, $user_id = null) {
        global $wpdb;
        return $wpdb->insert(
            $this->table,
            [
                'title'       => sanitize_text_field($title),
                'description' => sanitize_textarea_field($description),
                'created_by'  => $user_id ? (int)$user_id : null,
                'created_at'  => current_time('mysql'),
            ],
            ['%s','%s','%d','%s']
        );
    }

    public function delete($id) {
        global $wpdb;
        return $wpdb->delete($this->table, ['id' => (int)$id], ['%d']);
    }
}
