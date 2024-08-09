<?php
class WP_BCT_AI_Module_Settings {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));
    }

    public function add_admin_menu() {
        add_submenu_page(
            'bctaichat',
            'BCT AI Modules',
            'Modules',
            'manage_options',
            'bct_ai_modules',
            array($this, 'settings_page')
        );
    }

    public function settings_init() {
        register_setting('bct_ai_modules_group', 'bct_ai_modules_settings');

        add_settings_section(
            'bct_ai_modules_section',
            __('BCT AI Modules Settings', 'bctai'),
            array($this, 'settings_section_callback'),
            'bct_ai_modules'
        );

        $this->add_module_field('scenarios', __('Enable Scenarios', 'bctai'));
        $this->add_module_field('stt_evaluation', __('Enable STT Evaluation', 'bctai'));
        $this->add_module_field('pdf', __('Enable PDF', 'bctai'));
    }

    private function add_module_field($module_key, $label) {
        add_settings_field(
            "enable_$module_key",
            $label,
            array($this, 'render_checkbox'),
            'bct_ai_modules',
            'bct_ai_modules_section',
            array('module_key' => $module_key)
        );
    }

    public function render_checkbox($args) {
        $module_key = $args['module_key'];
        $options = get_option('bct_ai_modules_settings');
        ?>
        <input type='checkbox' name='bct_ai_modules_settings[enable_<?php echo $module_key; ?>]' <?php checked($options["enable_$module_key"], 1); ?> value='1'>
        <?php
    }

    public function settings_section_callback() {
        echo __('Toggle the BCT AI Chatbot modules.', 'bctai');
    }

    public function settings_page() {
        ?>
        <form action='options.php' method='post'>
            <h2>BCT AI Modules Settings</h2>
            <?php
            settings_fields('bct_ai_modules_group');
            do_settings_sections('bct_ai_modules');
            submit_button();
            ?>
        </form>
        <?php
    }
}
