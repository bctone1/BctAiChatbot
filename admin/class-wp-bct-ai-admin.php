<?php
if (!defined('ABSPATH'))
    exit;

/**
 * The admin-specific functionality of the plugin.
 * 
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 * 
 * @package     Wp_BCT_Ai
 * @subpackage  Wp_BCT_Ai/admin
 * @author      bctone <bct@bctone.kr>
 */

class Wp_BCT_Ai_Admin
{
    /**
     * The ID of this plugin.
     * 
     * @since       0.0.1
     * @access      private
     * @var         string      $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     * 
     * @since       0.0.1
     * @access      private
     * @var         string      $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     * 
     * @since       0.0.1
     * @param       string      $plugin_name        The name of this plugin.
     * @param       string      $version        The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        //echo $plugin_name;
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     * 
     * @since       0.0.1
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name,plugin_dir_url(__DIR__) . 'src/css/wp-bct-ai-admin.css',array(),$this->version,'all');
        wp_enqueue_style('common-css', plugin_dir_url( __DIR__ ) . 'src/css/common.css', array(), $this->version, 'all' );
        wp_enqueue_style('style-css', plugin_dir_url( __DIR__ ) . 'src/css/style.css', array(), $this->version, 'all' );
        

        wp_enqueue_style( 'jquery-ui', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css' );
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css', array(), '6.4.2', 'all');


    }



    /**
     * Register the JavaScript for the admin rea.
     * 
     * @since       0.0.1
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script('bctai-chat-script', BCTAI_PLUGIN_URL.'src/js/bctai-chat.js',null,null,true);
        wp_enqueue_script('jquery-js', plugin_dir_url( __DIR__ ) . 'src/js/jquery.js', array(), $this->version, 'all' );
        wp_enqueue_script('common-js', plugin_dir_url( __DIR__ ) . 'src/js/common.js', array(), $this->version, 'all' );
        
    }

    function bctai_load_db_value_js()
    {
        // global $post;
        // include BCTAI_PLUGIN_DIR . 'admin/views/scripts.php';
    }


    public $bctai_engine = 'gpt-3.5-turbo';
    public $bctai_max_tokens = 2000;
    public $bctai_temperature = 0;
    public $bctai_top_p = 1;
    public $bctai_best_of = 1;
    public $bctai_frequency_penalty = 0;
    public $bctai_presence_penalty = 0;
    public $bctai_stop = [];

    public function bctai_options_page()
    {

        add_menu_page(
            __('Settings', 'bctai'),
            'BCT AI Chatbot',
            'manage_options',
            'bctaichat',
            array($this, 'bctai_api_settings'),
            BCTAI_PLUGIN_URL.'public/images/bctaichatbot_left_menu_icon.png',
            6
        );

        add_submenu_page(
            'bctaichat',
            __('Settings', 'bctai'),
            __('Settings', 'bctai'),
            'manage_options',
            'bctaichat',
            array($this, 'bctai_api_settings'),
            
        );
        

        add_submenu_page(
            'bctaichat',
            __('Embeddings', 'bctai'),
            __('Embeddings', 'bctai'),
            'manage_options',
            'Embeddings',
            array($this, 'bctai_submenu_Embeddings'),
            1
        );
        add_submenu_page(
            'bctaichat',
            __('Fine-tuning', 'bctai'),
            __('Fine-tuning', 'bctai'),
            'manage_options',
            'Fine-tuning',
            array($this, 'bctai_submenu_Fine_tuning'),
            2
        );
        add_submenu_page(
            'bctaichat',
            __('Audio', 'bctai'),
            __('Audio', 'bctai'),
            'manage_options',
            'Audio',
            array($this, 'bctai_submenu_Audio'),
            3
        );
        add_submenu_page(
            'bctaichat',
            __('AI ChatBot', 'bctai'),
            __('AI ChatBot', 'bctai'),
            'manage_options',
            'AI ChatBot',
            array($this, 'bctai_submenu_AI_ChatBot'),
            4
        );
        add_submenu_page(
            'bctaichat',
            __('Statistics', 'bctai'),
            __('Statistics', 'bctai'),
            'manage_options',
            'Statistics',
            array($this, 'bctai_submenu_Statistics'),
            5
        );
        add_submenu_page(
            'bctaichat',
            __('Modules', 'bctai'),
            __('Modules', 'bctai'),
            'manage_options',
            'Modules',
            array($this, 'bctai_submenu_Modules'),
            6
        );

        // add_submenu_page(
        //     'bctaichat',
        //     __('QuestionList', 'bctai'),
        //     __('QuestionList', 'bctai'),
        //     'manage_options',
        //     'QuestionList',
        //     array($this, 'bctai_submenu_QuestionList'),
        //     7
        // );
        
    }
    


    public function bctai_api_settings()
    {
        include BCTAI_PLUGIN_DIR . 'admin/views/settings/index.php';
    }

    public function bctai_submenu_Embeddings()
    {
        include BCTAI_PLUGIN_DIR . 'admin/views/embeddings/index.php';
    }

    public function bctai_submenu_Fine_tuning()
    {
        include BCTAI_PLUGIN_DIR . 'admin/views/finetune/index.php';
    }

    public function bctai_submenu_Audio()
    {
        include BCTAI_PLUGIN_DIR . 'admin/views/audio/index.php';
    }

    public function bctai_submenu_AI_Forms()
    {
        include BCTAI_PLUGIN_DIR . 'admin/extra/bctai_forms.php';
    }

    public function bctai_submenu_PromptBase()
    {
        include BCTAI_PLUGIN_DIR . 'admin/extra/bctai_promptbase.php';
    }

    public function bctai_submenu_AI_ChatBot()
    {
        include BCTAI_PLUGIN_DIR . 'admin/extra/bctai_chatmode.php';
    }

    public function bctai_submenu_Statistics()
    {
        include BCTAI_PLUGIN_DIR . 'admin/views/chart/index.php';
    }

    public function bctai_submenu_License()
    {
        include BCTAI_PLUGIN_DIR . 'admin/views/License/index.php';
    }

    public function bctai_submenu_Modules()
    {
        include BCTAI_PLUGIN_DIR . 'admin/views/modules/index.php';
    }

    // public function bctai_submenu_QuestionList()
    // {
    //     include BCTAI_PLUGIN_DIR . 'admin/views/QuestionList.php';
    // }

}