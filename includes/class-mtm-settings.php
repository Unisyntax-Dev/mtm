<?php
/**
 * Settings registration and rendering for Mini Task Manager.
 *
 * Registers plugin options via Settings API, provides sanitization,
 * and renders the settings page fields.
 *
 * @package Mini_Task_Manager
 */

if (!defined('ABSPATH')) exit; // Prevent direct access to the file

class MTM_Settings {

    /** Option name stored in wp_options */
    const OPTION = 'mtm_settings';

    /**
     * Hook settings registration.
     */
    public function hooks() {
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Default option values.
     */
    public static function defaults(): array {
        return [
            'items_limit'   => 5, // Show last N tasks
            'enable_delete' => 1, // Allow delete from list
            'enable_edit'   => 1, // Allow inline edit from list
        ];
    }

    /**
     * Register settings, section, and fields using Settings API.
     */
    public function register_settings() {
        register_setting(
            'mtm_settings_group',
            self::OPTION,
            [
                'type'              => 'array',
                'sanitize_callback' => [$this, 'sanitize_settings'], // Validate & sanitize input
            ]
        );

        add_settings_section(
            'mtm_main_section',
            __('Settings', 'mini-task-manager'),
            function () {
                echo '<p>' . esc_html__('Basic behavior of the tasks form and list.', 'mini-task-manager') . '</p>';
            },
            'mtm_settings_page'
        );

        // items_limit
        add_settings_field(
            'items_limit',
            __('Last tasks limit', 'mini-task-manager'),
            [$this, 'field_items_limit'],
            'mtm_settings_page',
            'mtm_main_section'
        );

        // enable_delete
        add_settings_field(
            'enable_delete',
            __('Enable delete from list', 'mini-task-manager'),
            [$this, 'field_enable_delete'],
            'mtm_settings_page',
            'mtm_main_section'
        );

        // enable_edit
        add_settings_field(
            'enable_edit',
            __('Enable edit from list', 'mini-task-manager'),
            [$this, 'field_enable_edit'],
            'mtm_settings_page',
            'mtm_main_section'
        );
    }

    /**
     * Sanitize and normalize settings array before saving.
     *
     * @param mixed $input Raw input from the settings form.
     * @return array Sanitized options.
     */
    public function sanitize_settings($input): array {
        $d = self::defaults();

        $out = [];
        // Clamp items_limit to 1–20
        $out['items_limit']   = isset($input['items_limit']) ? max(1, min(20, (int)$input['items_limit'])) : $d['items_limit'];
        $out['enable_delete'] = !empty($input['enable_delete']) ? 1 : 0;
        $out['enable_edit']   = !empty($input['enable_edit']) ? 1 : 0;

        return $out;
    }

    /**
     * Retrieve merged options (stored + defaults).
     */
    private function get_option(): array {
        $opt = get_option(self::OPTION, []);
        if (!is_array($opt)) $opt = [];
        return wp_parse_args($opt, self::defaults());
    }

    // --- Field renderers ---

    /**
     * Render the "items_limit" number field.
     */
    public function field_items_limit() {
        $o = $this->get_option();
        printf(
            '<input type="number" name="%1$s[items_limit]" value="%2$d" min="1" max="20" class="small-text" />',
            esc_attr(self::OPTION),
            (int)$o['items_limit']
        );
        echo ' <span class="description">' . esc_html__('Show last N tasks (1–20).', 'mini-task-manager') . '</span>';
    }

    /**
     * Render the "enable_delete" checkbox.
     */
    public function field_enable_delete() {
        $o = $this->get_option();
        printf(
            '<label><input type="checkbox" name="%1$s[enable_delete]" value="1" %2$s /> %3$s</label>',
            esc_attr(self::OPTION),
            checked(!empty($o['enable_delete']), true, false),
            esc_html__('Allow deleting tasks from the list', 'mini-task-manager')
        );
    }

    /**
     * Render the "enable_edit" checkbox.
     */
    public function field_enable_edit() {
        $o = $this->get_option();
        printf(
            '<label><input type="checkbox" name="%1$s[enable_edit]" value="1" %2$s /> %3$s</label>',
            esc_attr(self::OPTION),
            checked(!empty($o['enable_edit']), true, false),
            esc_html__('Allow editing tasks from the list', 'mini-task-manager')
        );
    }

    /**
     * Render the full settings page form.
     */
    public function render_settings_form() {
        echo '<div class="mtm-admin-wrap wrap"><h1>' . esc_html__('Mini Task Manager', 'mini-task-manager') . '</h1>';

        // Info block about shortcode
        echo '<div style="background: #fff; border-left: 4px solid #0073aa; padding: 12px; margin: 20px 0;">';
        echo '<p style="margin: 0;">' . esc_html__('To display the task manager on the front-end, use the shortcode:', 'mini-task-manager') . '</p>';
        echo '<code>[mini_task_manager]</code>';
        echo '</div>';

        // React admin mount point
        echo '<div id="mtm-admin-root"></div>';

        echo '<form action="options.php" method="post">';
        settings_fields('mtm_settings_group');     // Outputs nonce + option group fields
        do_settings_sections('mtm_settings_page'); // Renders sections & fields
        submit_button();
        echo '</form></div>';
    }
}
