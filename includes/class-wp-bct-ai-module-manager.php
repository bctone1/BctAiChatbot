<?php
require_once plugin_dir_path(__FILE__) . 'interface-wp-bct-ai-module.php';

class WP_BCT_AI_Module_Manager {
    private $modules = array();

    public function __construct() {
        $this->load_modules();
        $this->initialize_modules();
    }

    private function load_modules() {
        // Load all modules
        require_once plugin_dir_path(__FILE__) . 'class-wp-bct-ai-scenarios.php';
        require_once plugin_dir_path(__FILE__) . 'class-wp-bct-ai-stt-evaluation.php';
        require_once plugin_dir_path(__FILE__) . 'class-wp-bct-ai-pdf.php';

        $this->modules = array(
            'scenarios' => new WP_BCT_AI_Scenarios(),
            'stt_evaluation' => new WP_BCT_AI_STT_Evaluation(),
            'pdf' => new WP_BCT_AI_PDF(),
        );
    }

    public function initialize_modules() {
        $options = get_option('bct_ai_modules_settings');

        foreach ($this->modules as $key => $module) {
            if (isset($options["enable_$key"]) && $options["enable_$key"] == 1) {
                $module->initialize();
            }
        }
    }
}
