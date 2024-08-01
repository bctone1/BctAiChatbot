<?php
class WP_BCT_AI_Manager {
    public function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    private function includes() {
        require_once plugin_dir_path(__FILE__) . 'class-wp-bct-ai-openai.php';
        //require_once plugin_dir_path(__FILE__) . 'class-wp-bct-ai-chat.php';
        //require_once plugin_dir_path(__FILE__) . 'class-wp-bct-ai-embeddings.php';
        //require_once plugin_dir_path(__FILE__) . 'class-wp-bct-ai-tts.php';
        //require_once plugin_dir_path(__FILE__) . 'class-wp-bct-ai-admin.php';
        require_once plugin_dir_path(__FILE__) . 'class-wp-bct-ai-module-settings.php';
        require_once plugin_dir_path(__FILE__) . 'class-wp-bct-ai-module-manager.php';
    }

    private function init_hooks() {
        new WP_BCT_AI_OpenAI();
        new WP_BCT_AI_Chat();
        new WP_BCT_AI_Embeddings();
        new WP_BCT_AI_TTS();

        // Initialize the admin settings pages
        new WP_BCT_AI_Admin();
        new WP_BCT_AI_Module_Settings();

        // Initialize the module manager
        new WP_BCT_AI_Module_Manager();
    }
}
