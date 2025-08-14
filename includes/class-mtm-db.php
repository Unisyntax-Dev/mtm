<?php
/**
 * Low-level database helper for Mini Task Manager.
 *
 * Provides basic CRUD operations for the tasks table
 * without additional formatting or business logic.
 *
 * @package Mini_Task_Manager
 */

if (!defined('ABSPATH')) exit; // Prevent direct access to the file

class MTM_DB {

    /** @var string Full table name with WP prefix */
    private $table;

    public function __construct() {
        global $wpdb;
        // Define the fully prefixed table name
        $this->table = $wpdb->prefix . 'mtm_tasks';
    }

    /**
     * Retrieve the most recent tasks.
     *
     * @param int $limit Number of tasks to fetch (minimum 1).
     * @return array[] Array of associative arrays with keys: id, title, description, created_at.
     */
    public function get_recent($limit = 5) {
        global $wpdb;
        $limit = max(1, (int)$limit);

        $sql = $wpdb->prepare(
            "SELECT id, title, description, created_at
             FROM {$this->table}
             ORDER BY created_at DESC, id DESC
             LIMIT %d",
            $limit
        );

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Insert a new task into the table.
     *
     * @param string   $title       Task title.
     * @param string   $description Task description.
     * @param int|null $user_id     User ID of the creator (optional).
     * @return int|false Inserted row ID on success, false on failure.
     */
    public function insert($title, $description, $user_id = null) {
        global $wpdb;

        return $wpdb->insert(
            $this->table,
            [
                'title'       => sanitize_text_field($title),
                'description' => sanitize_textarea_field($description),
                'created_by'  => $user_id ? (int)$user_id : null,
                'created_at'  => current_time('mysql'), // Local WP time
            ],
            ['%s', '%s', '%d', '%s']
        );
    }

    /**
     * Delete a task by ID.
     *
     * @param int $id Task ID.
     * @return int|false Number of rows deleted, or false on failure.
     */
    public function delete($id) {
        global $wpdb;
        return $wpdb->delete($this->table, ['id' => (int)$id], ['%d']);
    }
}
