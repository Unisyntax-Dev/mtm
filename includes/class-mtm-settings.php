<?php

if (!defined('ABSPATH')) exit;

class MTM_Settings {

    const OPTION = 'mtm_settings';

    public function hooks() {
        add_action('admin_init', [$this, 'register_settings']);
    }

    public static function defaults(): array {
        return [
            'items_limit'         => 5,
            'enable_delete'       => 1,
        ];
    }

    public function register_settings() {
        register_setting(
            'mtm_settings_group',
            self::OPTION,
            ['type' => 'array', 'sanitize_callback' => [$this, 'sanitize_settings']]
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
    }

    public function sanitize_settings($input): array {
        $d = self::defaults();

        $out = [];
        $out['items_limit']         = isset($input['items_limit']) ? max(1, min(20, (int)$input['items_limit'])) : $d['items_limit'];
        $out['enable_delete']       = !empty($input['enable_delete']) ? 1 : 0;

        return $out;
    }

    private function get_option(): array {
        $opt = get_option(self::OPTION, []);
        if (!is_array($opt)) $opt = [];
        return wp_parse_args($opt, self::defaults());
    }

    // --- Renderers ---

    public function field_items_limit() {
        $o = $this->get_option();
        printf(
            '<input type="number" name="%1$s[items_limit]" value="%2$d" min="1" max="20" class="small-text" />',
            esc_attr(self::OPTION),
            (int)$o['items_limit']
        );
        echo ' <span class="description">' . esc_html__('Show last N tasks (1–20).', 'mini-task-manager') . '</span>';
    }


    public function field_enable_delete() {
        $o = $this->get_option();
        printf(
            '<label><input type="checkbox" name="%1$s[enable_delete]" value="1" %2$s /> %3$s</label>',
            esc_attr(self::OPTION),
            checked(!empty($o['enable_delete']), true, false),
            esc_html__('Allow deleting tasks from the list', 'mini-task-manager')
        );
    }


    public function render_settings_form() {
        echo '<div class="wrap"><h1>' . esc_html__('Mini Task Manager', 'mini-task-manager') . '</h1>';
        echo '<form action="options.php" method="post">';
        settings_fields('mtm_settings_group');
        do_settings_sections('mtm_settings_page');
        submit_button();
        echo '</form></div>';
    }
}
