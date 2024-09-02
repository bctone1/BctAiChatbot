<?php
namespace BCTAI;

if (!defined('ABSPATH'))
    exit;
if (!class_exists('\\BCTAI\BCTAI_Chat')) {
    class BCTAI_Chat
    {
        private static $instance = null;

        public static function get_instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            add_shortcode('bctai_chatgpt', [$this, 'bctai_chatbox']);
            add_shortcode('bctai_chatgpt_widget', [$this, 'bctai_chatbox_widget']);
            add_action('wp_ajax_bctai_chatbox_message', array($this, 'bctai_chatbox_message'));
            add_action('wp_ajax_nopriv_bctai_chatbox_message', array($this, 'bctai_chatbox_message'));
            add_action('wp_ajax_bctai_chat_shortcode_message', array($this, 'bctai_chatbox_message'));
            add_action('wp_ajax_nopriv_bctai_chat_shortcode_message', array($this, 'bctai_chatbox_message'));

            add_action('wp_ajax_bctai_Scenario_menu', array($this, 'bctai_Scenario_menu'));
            add_action('wp_ajax_nopriv_bctai_Scenario_menu', array($this, 'bctai_Scenario_menu'));

            add_action('wp_ajax_wpaicg_fetch_google_models', [$this, 'wpaicg_fetch_google_models']);

            

            $this->create_database_tables();
        }


        

        

        public function wpaicg_fetch_google_models()
        {
            if (!current_user_can('manage_options')) {
                wp_send_json(['status' => 'error', 'msg' => esc_html__('You do not have permission for this action.', 'gpt3-ai-content-generator')]);
            }
        
            if (!wp_verify_nonce($_POST['nonce'], 'wpaicg-ajax-nonce')) {
                wp_send_json(['status' => 'error', 'msg' => esc_html__('Nonce verification failed', 'gpt3-ai-content-generator')]);
            }
        
            $api_key = get_option('wpaicg_google_model_api_key');
            if (empty($api_key)) {
                wp_send_json(['status' => 'error', 'msg' => 'Google API key is not configured. Please enter your Google API key in the settings and save it first.']);
            }
        
            $google_ai = WPAICG_Google::get_instance();
            $model_list = $google_ai->listModels();
        
            if (is_wp_error($model_list)) {
                wp_send_json(['status' => 'error', 'msg' => $model_list->get_error_message()]);
            }

            // Check if the response is an error response from the Google API
            if (isset($model_list['error'])) {
                $api_error_msg = $model_list['error']['message'];
                wp_send_json(['status' => 'error', 'msg' => $api_error_msg]);
            }
        
            update_option('wpaicg_google_model_list', $model_list);
            wp_send_json(['status' => 'success', 'msg' => 'Models updated successfully']);
        }



        public function bctai_Scenario_menu(){
            $content = $_REQUEST['content'];

            //$menu_structure_json = json_encode($content);
            
            update_option('menu_structure', $content);

            wp_send_json($content);

            

        }


        public function create_database_tables()
        {
            global $wpdb;



            $bctaiChatLogTable = $wpdb->prefix . 'bctai_chatlogs';

            if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $bctaiChatLogTable)) != $bctaiChatLogTable) {
                $charset_collate = $wpdb->get_charset_collate();
                $sql = "CREATE TABLE " . $bctaiChatLogTable . " (
                `id` mediumint(11) NOT NULL AUTO_INCREMENT,
                `log_session` VARCHAR(255) NOT NULL,
                `data` LONGTEXT NOT NULL,
                `page_title` TEXT DEFAULT NULL,
                `source` VARCHAR(255) DEFAULT NULL,
                `created_at` VARCHAR(255) NOT NULL,
                `user_id` LONGTEXT,
                PRIMARY KEY (id)
                ) $charset_collate";
                
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                $wpdb->query($sql);
            }
            
            $bctaiChatTokensTable = $wpdb->prefix . 'bctai_chattokens';
            
            if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $bctaiChatTokensTable)) != $bctaiChatTokensTable) {
                $charset_collate = $wpdb->get_charset_collate();
                $sql = "CREATE TABLE " . $bctaiChatTokensTable . " (
                `id` mediumint(11) NOT NULL AUTO_INCREMENT,
                `tokens` VARCHAR(255) DEFAULT NULL,
                `user_id` VARCHAR(255) DEFAULT NULL,
                `session_id` VARCHAR(255) DEFAULT NULL,
                `source` VARCHAR(255) DEFAULT NULL,
                `created_at` VARCHAR(255) NOT NULL,
                PRIMARY KEY  (id)
                ) $charset_collate";
                
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                $wpdb->query($sql);
            }



            $baraiChatQuestion = $wpdb ->prefix . 'bctai_question';

            
            if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $baraiChatQuestion)) != $baraiChatQuestion) {
                $charset_collate = $wpdb->get_charset_collate();
                $sql = "CREATE TABLE " . $baraiChatQuestion . " (
                `id` mediumint(11) NOT NULL AUTO_INCREMENT,
                `log_session` VARCHAR(255) NOT NULL,
                `data` LONGTEXT NOT NULL,
                `page_title` TEXT DEFAULT NULL,
                `source` VARCHAR(255) DEFAULT NULL,
                `created_at` VARCHAR(255) NOT NULL,
                `user_id` LONGTEXT,
                PRIMARY KEY (id)
                ) $charset_collate";
                
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                $wpdb->query($sql);
            }



        }

        

        public function bctai_chatgpt()
        {
            include BCTAI_PLUGIN_DIR . 'admin/extra/bctai_chatmode.php';
        }

        public function bctai_get_cookie_id()
        {
            if (!function_exists('PasswordHash')) {
                require_once ABSPATH . 'wp-includes/class-phpass.php';
            }
            if (isset($_COOKIE['bctai_chat_client_id']) && !empty($_COOKIE['bctai_chat_client_id'])) {
                return $_COOKIE['bctai_chat_client_id'];
            } else {
                $hasher = new \PasswordHash(8, false);
                $cookie_id = 't_' . substr(md5($hasher->get_random_bytes(32)), 2);

                setcookie('bctai_chat_client_id', $cookie_id, time() + 604800, COOKIEPATH, COOKIE_DOMAIN);
                return $cookie_id;
            }

        }



        public function bctai_chatbox_message()
        {
            //wp_send_json("ajax요청 성공");
            global $wpdb;
            $bctai_client_id = $this->bctai_get_cookie_id();
            $bctai_result = array(
                'status' => 'start',
                'msg' => 'startNowwww',
                'score' => 'none',
            );

            $bctai_chat_provider = get_option('bctai_chat_provider','OpenAI');
            //wp_send_json($bctai_chat_provider);
            

            switch ($bctai_chat_provider) {
                case 'Google':
                    $open_ai = WPAICG_Google::get_instance();
                    break;
                case 'Huggingface':
                    $open_ai = BCTAI_Huggingface::get_instance()->openai();
                    break;
                case 'OpenRouter':
                    $open_ai = BCTAI_OpenRouter::get_instance()->openai();
                    break;
                default:
                    $open_ai = BCTAI_OpenAI::get_instance()->openai();
                    break;
            }

            // wp_send_json($open_ai);

            
            


            if (!$open_ai) {
                $bctai_result['msg'] = 'Missing API Setting';
                wp_send_json($bctai_result);
                exit;
            }
            $bctai_save_request = false;
            $bctai_nonce = sanitize_text_field($_REQUEST['_wpnonce']);
            if (!wp_verify_nonce($bctai_nonce, 'bctai-chatbox')) {
                $bctai_result['msg'] = BCTAI_NONCE_ERROR;
            } else {
                $bctai_message = (isset($_REQUEST['message']) && !empty($_REQUEST['message']) ? sanitize_text_field($_REQUEST['message']) : '');
                $bctai_message = (isset($_REQUEST['message']) && !empty($_REQUEST['message']) ? $_REQUEST['message'] : '');
                $url = (isset($_REQUEST['url']) && !empty($_REQUEST['url']) ? sanitize_text_field($_REQUEST['url']) : '');
                $bctai_pinecone_api = get_option('bctai_pinecone_api', '');
                $bctai_pinecone_environment = get_option('bctai_pinecone_environment', '');
                $bctai_total_tokens = 0;
                $bctai_limited_tokens = false;
                $bctai_token_usage_client = 0;
                $bctai_token_limit_message = 'You have reached your token limit.';
                $bctai_limited_tokens_number = 0;
                $bctai_chat_source = 'widget';
                $bctai_chat_temperature = get_option('bctai_chat_temperature', $open_ai->temperature);
                $bctai_chat_max_tokens = get_option('bctai_chat_max_tokens', $open_ai->max_tokens);
                $bctai_chat_top_p = get_option('bctai_chat_top_p', $open_ai->top_p);
                $bctai_chat_best_of = get_option('bctai_chat_best_of', $open_ai->best_of);
                $bctai_chat_frequency_penalty = get_option('bctai_chat_frequency_penalty', $open_ai->frequency_penalty);
                $bctai_chat_presence_penalty = get_option('bctai_chat_presence_penalty', $open_ai->presence_penalty);

                $bctai_vector_db_provider = get_option('bctai_vector_db_provider', 'pinecone');
                
                if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'bctai_chat_shortcode_message') {
                    $bctai_chat_source = 'shortcode';
                }
                if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'bctai_chat_shortcode_message') {
                    $table = $wpdb->prefix . 'bctai';
                    $existingValue = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE name = %s", 'bctai_settings'), ARRAY_A);
                    $bctai_chat_shortcode_options = get_option('bctai_chat_shortcode_options', []);
                    $default_setting = array(
                        'language' => 'en',
                        'tone' => 'friendly',
                        'profession' => 'none',
                        'fontsize' => 13,
                        'fontcolor' => '#fff',
                        'AI_fontcolor' => '#fff',
                        'user_bg_color' => '#444654',
                        'ai_bg_color' => '#343541',
                        'ai_icon_url' => '',
                        'ai_icon' => 'default',
                        'use_avatar' => false,
                        'model' => 'text-davinci-003',
                        'temperature' => $existingValue['temperature'],
                        'max_tokens' => $existingValue['max_tokens'],
                        'top_p' => $existingValue['top_p'],
                        'best_of' => $existingValue['best_of'],
                        'frequency_penalty' => $existingValue['frequency_penalty'],
                        'presence_penalty' => $existingValue['presence_penalty'],
                        'ai_name' => 'AI',
                        'you' => 'You',
                        'ai_thinking' => 'AI Thinking',
                        'placeholder' => 'Type a message',
                        'welcome' => 'Hello human, I am a GPT powered AI chat bot. Ask me anything!',
                        'no_answer' => '',
                        'remember_conversation' => 'yes',
                        'conversation_cut' => 10,
                        'user_aware' => 'no',
                        'content_aware' => 'yes',
                        'embedding' => false,
                        'embedding_type' => false,
                        'embedding_top' => false,
                        'embedding_index' => '',
                        'user_limited' => false,
                        'guest_limited' => false,
                        'user_tokens' => 0,
                        'limited_message' => 'You have reached your token limit.',
                        'guest_tokens' => 0,
                        'role_limited' => false,
                        'limited_roles' => [],
                        'save_logs' => false,
                        'log_request' => false
                    );
                    $bctai_settings = shortcode_atts($default_setting, $bctai_chat_shortcode_options);

                    if (isset($_REQUEST['bctai_chat_shortcode_options']) && is_array($_REQUEST['bctai_chat_shortcode_options'])) {
                        $bctai_chat_shortcode_options = bctai_util_core()->sanitize_text_or_array_field($_REQUEST['bctai_chat_shortcode_options']);
                        $bctai_settings = shortcode_atts($bctai_settings, $bctai_chat_shortcode_options);
                    }
                    #Language, Tone and Profession
                    $bctai_chat_language = isset($bctai_settings['language']) ? $bctai_settings['language'] : 'en';
                    $bctai_chat_tone = isset($bctai_settings['tone']) ? $bctai_settings['tone'] : 'friendly';
                    $bctai_chat_proffesion2 = isset($bctai_settings['profession']) ? $bctai_settings['profession'] : 'none';
                    #Parameters
                    $bctai_ai_model = isset($bctai_settings['model']) ? $bctai_settings['model'] : 'gpt-3.5-turbo';
                    $bctai_chat_temperature = isset($bctai_settings['temperature']) && !empty($bctai_settings['temperature']) ? $bctai_settings['temperature'] : $bctai_chat_temperature;
                    $bctai_chat_max_tokens = isset($bctai_settings['max_tokens']) && !empty($bctai_settings['max_tokens']) ? $bctai_settings['max_tokens'] : $bctai_chat_max_tokens;
                    $bctai_chat_top_p = isset($bctai_settings['top_p']) && !empty($bctai_settings['top_p']) ? $bctai_settings['top_p'] : $bctai_chat_top_p;
                    $bctai_chat_best_of = isset($bctai_settings['best_of']) && !empty($bctai_settings['best_of']) ? $bctai_settings['best_of'] : $bctai_chat_best_of;
                    $bctai_chat_frequency_penalty = isset($bctai_settings['frequency_penalty']) && !empty($bctai_settings['frequency_penalty']) ? $bctai_settings['frequency_penalty'] : $bctai_chat_frequency_penalty;
                    $bctai_chat_presence_penalty = isset($bctai_settings['presence_penalty']) && !empty($bctai_settings['presence_penalty']) ? $bctai_settings['presence_penalty'] : $bctai_chat_presence_penalty;
                    #Custom Text
                    $bctai_chat_no_answer = isset($bctai_settings['no_answer']) ? $bctai_settings['no_answer'] : '';
                    $bctai_chat_no_answer = empty($bctai_chat_no_answer) ? 'I dont know2222' : $bctai_chat_no_answer;
                    #Context
                    $bctai_chat_remember_conversation = isset($bctai_settings['remember_conversation']) ? $bctai_settings['remember_conversation'] : 'yes';
                    $bctai_conversation_cut = isset($bctai_settings['conversation_cut']) ? $bctai_settings['conversation_cut'] : 10;
                    $bctai_conversation_url = 'bctai_conversation_url_shortcode';
                    $bctai_user_aware = isset($bctai_settings['user_aware']) ? $bctai_settings['user_aware'] : 'no';
                    $bctai_chat_content_aware = isset($bctai_settings['content_aware']) ? $bctai_settings['content_aware'] : 'yes';
                    $bctai_chat_embedding = isset($bctai_settings['embedding']) && $bctai_settings['embedding'] ? true : false;
                    $bctai_chat_embedding_type = isset($bctai_settings['embedding_type']) ? $bctai_settings['embedding_type'] : '';
                    $bctai_chat_embedding_top = isset($bctai_settings['embedding_top']) ? $bctai_settings['embedding_top'] : 1;
                    $bctai_chat_with_embedding = false;
                    if (isset($bctai_settings['embedding_index']) && !empty($bctai_settings['embedding_index'])) {
                        $bctai_pinecone_environment = $bctai_settings['embedding_index'];
                    }
                    #Token Handling
                    $bctai_token_limit_message = isset($bctai_settings['limited_message']) ? $bctai_settings['limited_message'] : $bctai_token_limit_message;
                    if (is_user_logged_in() && $bctai_settings['user_limited'] && $bctai_settings['user_tokens'] > 0) {
                        $bctai_limited_tokens = true;
                        $bctai_limited_tokens_number = $bctai_settings['user_tokens'];
                    }
                    #Check limit base role
                    if (is_user_logged_in() && isset($bctai_settings['role_limited']) && $bctai_settings['role_limited']) {
                        $bctai_roles = (array) wp_get_current_user()->roles;
                        $limited_current_role = 0;
                        foreach ($bctai_roles as $bctai_role) {
                            if (
                                isset($bctai_settings['limited_roles'])
                                && is_array($bctai_settings['limited_roles'])
                                && isset($bctai_settings['limited_roles'][$bctai_role])
                                && $bctai_settings['limited_roles'][$bctai_role] > $limited_current_role
                            ) {
                                $limited_current_role = $bctai_settings['limited_roles'][$bctai_role];
                            }
                        }
                        if ($limited_current_role > 0) {
                            $bctai_limited_tokens = true;
                            $bctai_limited_tokens_number = $limited_current_role;
                        } else {
                            $bctai_limited_tokens = false;
                        }
                    }
                    #End check limit base role
                    if (!is_user_logged_in() && $bctai_settings['guest_limited'] && $bctai_settings['guest_tokens'] > 0) {
                        $bctai_limited_tokens = true;
                        $bctai_limited_tokens_number = $bctai_settings['guest_tokens'];
                    }
                    #Logs
                    $bctai_save_logs = isset($bctai_settings['save_logs']) && $bctai_settings['save_logs'] ? true : false;
                    $bctai_save_request = isset($bctai_settings['log_request']) && $bctai_settings['log_request'] ? true : false;

                } else {

                    
                    $bctai_limited_tokens = false;
                    $bctai_chat_widget = get_option('bctai_chat_widget', []);
                    $bctai_chat_design = get_option('bctai_chat_design',[]);

                    #Language, Tone and Profession
                    $bctai_chat_language = get_option('bctai_chat_language', 'en');
                    $bctai_chat_tone = isset($bctai_chat_widget['tone']) && !empty($bctai_chat_widget['tone']) ? $bctai_chat_widget['tone'] : 'friendly';
                    $bctai_chat_proffesion2 = isset($bctai_chat_design['proffesion']) && !empty($bctai_chat_design['proffesion']) ? $bctai_chat_design['proffesion'] : 'none';
                    $bctai_chat_proffesion = isset($bctai_chat_widget['proffesion']) && !empty($bctai_chat_widget['proffesion']) ? $bctai_chat_widget['proffesion'] : 'none';
                    #Custom Text
                    $bctai_chat_no_answer = empty($bctai_chat_no_answer) ? 'I dont know' : $bctai_chat_no_answer;
                    #Context
                    $bctai_chat_embedding = get_option('bctai_chat_embedding', false);
                    $bctai_chat_embedding_type = get_option('bctai_chat_embedding_type', false);
                    $bctai_chat_no_answer = get_option('bctai_chat_no_answer', '');
                    $bctai_chat_embedding_top = get_option('bctai_chat_embedding_top', 1);
                    $bctai_chat_with_embedding = false;
                    $bctai_chat_remember_conversation = isset($bctai_chat_widget['remember_conversation']) && !empty($bctai_chat_widget['remember_conversation']) ? $bctai_chat_widget['remember_conversation'] : 'yes';
                    $bctai_chat_content_aware = isset($bctai_chat_widget['content_aware']) && !empty($bctai_chat_widget['content_aware']) ? $bctai_chat_widget['content_aware'] : 'yes';
                    
                    //$bctai_chat_provider = get_option('bctai_chat_provider','OpenAI');

                    switch ($bctai_chat_provider) {
                        case 'Huggingface':
                            $bctai_ai_model = 'Meta-Llama-3.1-8B-Instruct';
                            break;
                        case 'Google':
                            $bctai_ai_model = get_option('wpaicg_google_default_model', 'gemini-pro');
                            break;
                        case 'OpenRouter':
                            $bctai_ai_model = get_option('wpaicg_openrouter_model', '');
                            break;
                        default:
                            $bctai_ai_model = get_option('bctai_chat_model', '');
                            break;
                    }

                    
                    
                    // wp_send_json($bctai_ai_model);
                    // $bctai_ai_model = get_option('bctai_chat_model', '');



                    $bctai_conversation_cut = get_option('bctai_conversation_cut', 10);
                    $bctai_conversation_url = 'bctai_conversation_url';
                    $bctai_chat_addition = get_option('bctai_chat_addition', false);
                    $bctai_chat_addition_text = get_option('bctai_chat_addition_text', '');
                    $bctai_user_aware = isset($bctai_chat_widget['user_aware']) ? $bctai_chat_widget['user_aware'] : 'no';
                    #Token Handling 
                    $bctai_token_limit_message = isset($bctai_chat_widget['limited_message']) ? $bctai_chat_widget['limited_message'] : $bctai_token_limit_message;
                    if (is_user_logged_in() && isset($bctai_chat_widget['user_limited']) && $bctai_chat_widget['user_limited'] && $bctai_chat_widget['user_tokens'] > 0) {
                        $bctai_limited_tokens = true;
                        $bctai_limited_tokens_number = $bctai_chat_widget['user_tokens'];
                    }
                    #Logs
                    $bctai_save_logs = isset($bctai_chat_widget['save_logs']) && $bctai_chat_widget['save_logs'] ? true : false;
                    $bctai_save_request = isset($bctai_chat_widget['log_request']) && $bctai_chat_widget['log_request'] ? true : false;
                    #Check limit base role
                    if (is_user_logged_in() && isset($bctai_chat_widget['role_limited']) && $bctai_chat_widget['role_limited']) {
                        $bctai_roles = (array) wp_get_current_user()->roles;
                        $limited_current_role = 0;
                        foreach ($bctai_roles as $bctai_role) {
                            if (
                                isset($bctai_chat_widget['limited_roles'])
                                && is_array($bctai_chat_widget['limited_roles'])
                                && isset($bctai_chat_widget['limited_roles'][$bctai_role])
                                && $bctai_chat_widget['limited_roles'][$bctai_role] > $limited_current_role
                            ) {
                                $limited_current_role = $bctai_chat_widget['limited_roles'][$bctai_role];
                            }
                        }
                        if ($limited_current_role > 0) {
                            $bctai_limited_tokens = true;
                            $bctai_limited_tokens_number = $limited_current_role;
                        } else {
                            $bctai_limited_tokens = false;
                        }
                    }
                    #End check limit base role
                    if (!is_user_logged_in() && $bctai_chat_widget['guest_limited'] && $bctai_chat_widget['guest_tokens'] > 0) {
                        $bctai_limited_tokens = true;
                        $bctai_limited_tokens_number = $bctai_chat_widget['guest_tokens'];
                    }
                    if (isset($bctai_chat_widget['embedding_index']) && !empty($bctai_chat_widget['embedding_index'])) {
                        $bctai_pinecone_environment = $bctai_chat_widget['embedding_index'];
                    }
                }
                #Chat Bots
                // if (isset($_REQUEST['bot_id']) && !empty($_REQUEST['bot_id'])) {
                    
                // }
                if (!is_user_logged_in()) {
                    $bctai_user_aware = 'no';
                }
                $bctai_human_name = 'Human';
                $bctai_user_name = '';
                if ($bctai_user_aware == 'yes') {
                    $bctai_human_name = wp_get_current_user()->user_login;
                    if (!empty(wp_get_current_user()->display_name)) {
                        $bctai_user_name = 'Username: ' . wp_get_current_user()->display_name;
                        $bctai_human_name = wp_get_current_user()->display_name;
                    }
                }
                #Token handing
                $bctai_chat_token_id = false;
                if ($bctai_limited_tokens) {
                    if (is_user_logged_in()) {
                        $bctai_chat_token_log = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "bctai_chattokens WHERE source = %s AND user_id=%d", $bctai_chat_source, get_current_user_id()));
                        $bctai_token_usage_client = $bctai_chat_token_log ? $bctai_chat_token_log->tokens : 0;
                    } else {
                        $bctai_chat_token_log = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "bctai_chattokens WHERE source = %s AND session_id=%s", $bctai_chat_source, $bctai_client_id));
                        $bctai_token_usage_client = $bctai_chat_token_log ? $bctai_chat_token_log->tokens : 0;
                    }
                    $bctai_chat_token_id = $bctai_chat_token_log ? $bctai_chat_token_log->id : false;
                    if (
                        $bctai_token_usage_client > 0
                        && $bctai_limited_tokens_number > 0
                        && $bctai_token_usage_client > $bctai_limited_tokens_number
                    ) {
                        #check current user limited
                        $still_limited = true;
                        if (is_user_logged_in()) {
                            $user_meta_key = 'bctai_chat_tokens';
                            $user_tokens = get_user_meta(get_current_user_id(), $user_meta_key, true);
                            if (!empty($user_tokens) && $user_tokens > 0) {
                                $still_limited = false;
                            }
                        }
                        if ($still_limited) {
                            $bctai_result['msg'] = $bctai_token_limit_message;
                            wp_send_json($bctai_result);
                            exit;
                        }
                    }
                }
                #End check token handing
                #Start check Log
                $bctai_chat_log_id = false;
                $bctai_chat_log_data = array();

                #Check Audio Converter
                if(isset($_FILES['audio']) && empty($_FILES['audio']['error'])){
                    //$bctai_result['msg'] = 'audio';
                    //wp_send_json($bctai_result);

                    $file = $_FILES['audio'];
                    $file_name = sanitize_file_name(basename($file['name']));
                    $filetype = wp_check_filetype($file_name);
                    $mime_types = ['mp3' => 'audio/mpeg','mp4' => 'video/mp4','mpeg' => 'video/mpeg','m4a' => 'audio/m4a','wav' => 'audio/wav','webm' => 'video/webm'];
                    if(!in_array($filetype['type'], $mime_types)){
                        $bctai_result['msg'] = 'We only accept mp3, mp4, mpeg, mpga, m4a, wav, or webm.';
                        wp_send_json($bctai_result);
                    }
                    if($file['size'] > 26214400){
                        $bctai_result['msg'] = 'Audio file maximum 25MB';
                        wp_send_json($bctai_result);
                    }
                    $tmp_file = $file['tmp_name'];
                    $data_audio_request = array(
                        'audio' => array(
                            'filename' => $file_name,
                            'data' => file_get_contents($tmp_file)
                        ),
                        'model' => 'whisper-1',
                        'response_format' => 'json'
                    );
                    $completion = $open_ai->transcriptions($data_audio_request);
                    $completion = json_decode($completion);
                    if($completion && isset($completion->error)){
                        $bctai_result['msg'] = $completion->error->message;
                        if(empty($bctai_result['msg']) && isset($completion->error->code) && $completion->error->code == 'invalid_api_key'){
                            $bctai_result['msg'] = 'Incorrect API key provided. You can find your API key at https://platform.openai.com/account/api-keys.';
                        }
                        wp_send_json($bctai_result);
                    }
                    $bctai_message = $completion->text;
                }
                #사용자 ID 데이터에 기록
                if (!empty($bctai_message) && $bctai_save_logs) {
                    $bctai_current_context_id = isset($_REQUEST['post_id']) && !empty($_REQUEST['post_id']) ? sanitize_text_field($_REQUEST['post_id']) : '';
                    $bctai_current_context_title = !empty($bctai_current_context_id) ? get_the_title($bctai_current_context_id) : '';
                    $bctai_unique_chat = md5($bctai_client_id);
                    $user_id = get_current_user_id();
                    if($user_id){ //로그인 회원이면 제공
                        $bctai_unique_chat = md5($bctai_client_id.$user_id);
                    }
                    $bctai_chat_log_check = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "bctai_chatlogs WHERE source=%s AND log_session=%s", $bctai_chat_source, $bctai_unique_chat));
                    if (!$bctai_chat_log_check) { //값이 false, 0, 없을때 실행
                        $wpdb->insert(
                            $wpdb->prefix . 'bctai_chatlogs',
                            array(
                                'log_session' => $bctai_unique_chat,
                                'data' => wp_json_encode(array()),
                                'page_title' => $bctai_current_context_title,
                                'source' => $bctai_chat_source,
                                'user_id' => $user_id,
                                'created_at' => time()
                            )
                        );
                        $bctai_chat_log_id = $wpdb->insert_id;
                    } else {
                        $bctai_chat_log_id = $bctai_chat_log_check->id;
                        $bctai_current_log_data = json_decode($bctai_chat_log_check->data, true);
                        if ($bctai_current_log_data && is_array($bctai_current_log_data)) {
                            $bctai_chat_log_data = $bctai_current_log_data;
                        }
                    }
                    $bctai_chat_log_data[] = array('message' => $bctai_message, 'type' => 'user', 'date' => time(), 'ip' => $this->getIpAddress());
                }

                $bctai_embedding_content = '';
                if ($bctai_chat_embedding) {
                    $namespace = false;

                    
                    if (isset($_REQUEST['namespace']) && !empty($_REQUEST['namespace'])) {
                        $namespace = sanitize_text_field($_REQUEST['namespace']);
                    }



                    if($bctai_vector_db_provider === 'qdrant'){
                        $bctai_qdrant_api = get_option('bctai_qdrant_api', '');
                        $bctai_qdrant_endpoint = get_option('bctai_qdrant_endpoint', '');
                        $default_qdrant_collection = get_option('wpaicg_qdrant_default_collection', '');
                        $wpaicg_chat_embedding_top = get_option('wpaicg_chat_embedding_top', 1);


                        $bctai_embeddings_result = $this->wpaicg_embeddings_result_qdrant($bctai_chat_provider, $open_ai, $bctai_qdrant_api, $bctai_qdrant_endpoint, $default_qdrant_collection, $bctai_message, $wpaicg_chat_embedding_top, $namespace);
                        //wp_send_json($bctai_embeddings_result);
                    }else{
                        $bctai_embeddings_result = $this->bctai_embeddings_result($open_ai, $bctai_pinecone_api, $bctai_pinecone_environment, $bctai_message, $bctai_chat_embedding_top, $namespace);
                        // wp_send_json($bctai_embeddings_result);
                    }
                    if($bctai_embeddings_result['bctai_link_url']){
                        $bctai_result['bctai_link_url'] = $bctai_embeddings_result['bctai_link_url'];
                    }

                    

                    
                    if ($bctai_embeddings_result['status'] == 'empty') {
                        $bctai_chat_with_embedding = false;
                    } else {
                        $data2 = $bctai_embeddings_result['data'];

                        // wp_send_json($data2);
                        //$img_url = strpos($data2, 'Img URL:');

                        $img_url_start = strpos($data2, 'Img URL:');
                        if ($img_url_start !== false) {
                            $post_categories_start = strpos($data2, 'Post Categories:');
                            if ($post_categories_start !== false) {
                                $url_part = substr($data2, $img_url_start + strlen('Img URL:'), $post_categories_start - ($img_url_start + strlen('Img URL:')));
                                $url = trim($url_part);
                                $bctai_result['ImgURL'] = $url;
                            } 
                        }
                        

                        $post_content_start = strpos($data2, 'Post Content:');

                        if ($post_content_start !== false) {
                            $content_data = substr($data2, $post_content_start + strlen('Post Content:'));
                            $content_endposition = strpos($content_data, 'Post URL:');
                            
                            $post_url_start = strpos($data2, 'Post URL:');
                            if($post_url_start !== false){
                                $url_data = substr($data2, $post_url_start + strlen('Post URL:'));
                                $url_endposition = strpos($url_data, 'Post Categories:');
                                $url_data = substr($url_data, 0, $url_endposition);
                            }
                            $data2 = substr($content_data, 0, $content_endposition);
                        }
                        if (!$bctai_chat_embedding_type || empty($bctai_chat_embedding_type)) {
                            #only Embeddings
                            if($bctai_embeddings_result['score'][0] > 0.6){
                                $bctai_result['status'] = $bctai_embeddings_result['status'];
                                $bctai_embedding_score = $bctai_embeddings_result['score'][0];
                                $formatted_embedding_score = number_format($bctai_embedding_score, 2);

                                $bctai_result['url'] = $url_data; 
                                $bctai_result['cosine_score'] = $formatted_embedding_score;          
                                //$bctai_result['data'] = $data2 . '(코사인유사도 : '.$formatted_embedding_score.')'; 
                                //$result = $bctai_embeddings_result['infomation_status'];
                                //wp_send_json($result);
                                if(is_user_logged_in()){
                                    $bctai_result['data'] = $data2 ; 
                                }else{
                                    if($bctai_embeddings_result['infomation_status']=='no'){
                                        $bctai_result['data'] = '정보를 공개할수 없습니다. 관리자에 문의해주세요';
                                    }else{
                                        $bctai_result['data'] = $data2 ; 
                                    }
                                }
                                $bctai_result['msg'] = empty($bctai_embeddings_result['data']) ? $bctai_chat_no_answer : $bctai_embeddings_result['data'];

                                $this->bctai_save_chat_log($bctai_chat_log_id, $bctai_chat_log_data, 'ai', $bctai_result['data']);
                                wp_send_json($bctai_result); //바로출력
                            }else{
                                if($file){
                                    $bctai_chat_source ='Audio';
                                }else{
                                    $bctai_chat_source ='Text';
                                }
                                if($_REQUEST['AskGPT']){
                                    $wpdb->insert(
                                        $wpdb->prefix . 'bctai_question',
                                        array(
                                            'log_session' => $bctai_unique_chat,
                                            'data' => $bctai_message,
                                            'page_title' => $bctai_current_context_title,
                                            'source' => $bctai_chat_source,
                                            'user_id' => $user_id,
                                            'created_at' => time()
                                        )
                                    );
                                }else{
                                    $bctai_result['data'] = "질문에 대한 답이 없습니다. GPT에게 질문 하시려면 아래 버튼을 눌러주세요.";
                                    $bctai_result['msg'] = "코사인 유사도 0.4가 넘는 데이터가 없습니다.";
                                    $bctai_result['status'] = "SaveAnswer";
                                    wp_send_json($bctai_result);
                                }
                                
                            }
                        } else {
                            #openai
                            $bctai_result['status'] = $bctai_embeddings_result['status'];
                            if ($bctai_result['status'] == 'error') {
                                $bctai_result['msg'] = empty($bctai_embeddings_result['data']) ? $bctai_chat_no_answer : $bctai_embeddings_result['data'];
                                $this->bctai_save_chat_log($bctai_chat_log_id, $bctai_chat_log_data, 'ai', $bctai_result['data']);
                                wp_send_json($bctai_result);
                                exit;
                            } else {
                                $bctai_total_tokens += $bctai_embeddings_result['tokens']; // Add embedding tokens

                                // $bctai_embedding_content = $bctai_embeddings_result['data'];
                                $bctai_embedding_content = $data2;

                                $bctai_embedding_score = $bctai_embeddings_result['score'][0];
                                $formatted_embedding_score = number_format($bctai_embedding_score, 2);
                                $bctai_result['score']= $formatted_embedding_score;
                                
                            }
                            $bctai_chat_with_embedding = true;
                        }
                    }
                }

                #기록 기억하기
                if ($bctai_chat_remember_conversation == 'yes') {
                    $bctai_session_page = md5($bctai_client_id . $url);

                    if (!isset($_COOKIE[$bctai_conversation_url]) || empty($_COOKIE[$bctai_conversation_url])) {
                        setcookie($bctai_conversation_url, $bctai_session_page, time() + 86400, COOKIEPATH, COOKIE_DOMAIN);
                        $bctai_conversation_messages = array();
                    } else {
                        $bctai_conversation_messages = isset($_COOKIE[$bctai_session_page]) ? $_COOKIE[$bctai_session_page] : '';
                        $bctai_conversation_messages = str_replace("\\", '', $bctai_conversation_messages);
                        if (!empty($bctai_conversation_messages && is_serialized($bctai_conversation_messages))) {
                            $bctai_conversation_messages = unserialize($bctai_conversation_messages);
                            $bctai_conversation_messages = $bctai_conversation_messages ? $bctai_conversation_messages : array();
                        } else {
                            $bctai_conversation_messages = array();
                        }
                    }
                    $bctai_conversation_messages_length = count($bctai_conversation_messages);
                    if ($bctai_conversation_messages_length > $bctai_conversation_cut) {
                        $bctai_conversation_messages_start = $bctai_conversation_messages_length - $bctai_conversation_cut;
                    } else {
                        $bctai_conversation_messages_start = 0;
                    }
                    $bctai_conversation_end_messages = array_splice($bctai_conversation_messages, $bctai_conversation_messages_start, $bctai_conversation_messages_length);
                }
                
                if (!empty($bctai_message)) {

                    $bctai_chatgpt_messages = array();

                    $bctai_chatgpt_messages[] = array('role' => 'system','content'=>"$bctai_chat_proffesion2");
                    $bctai_chatgpt_messages[] = array('role' => 'user', 'content' => "$bctai_embedding_content");
                    
                    //$bctai_chatgpt_messages[] = array('role' => 'user', 'content' => html_entity_decode($bctai_chat_greeting_message, ENT_QUOTES, 'UTF-8'));
                    if ($bctai_chat_remember_conversation == 'yes') {
                        #Clear cookies
                        $bctai_conversation_end_messages[] = $bctai_human_name . ': ' . $bctai_message . "\nAI1: ";
                        foreach ($bctai_conversation_end_messages as $bctai_conversation_end_message) {
                            $bctai_chatgpt_message = $bctai_conversation_end_message;
                            if (strpos($bctai_conversation_end_message, $bctai_human_name . ': ') !== false) {
                                $bctai_chatgpt_message = str_replace($bctai_human_name . ': ', '', $bctai_chatgpt_message);
                                $bctai_chatgpt_message = str_replace("\nAI2: ", '', $bctai_chatgpt_message);
                                if (!empty($bctai_chatgpt_message)) {
                                    $bctai_chatgpt_messages[] = array('role' => 'user', 'content' => $bctai_chatgpt_message);
                                }
                            } else {
                                if (!empty($bctai_chatgpt_message)) {
                                    $bctai_chatgpt_messages[] = array('role' => 'assistant', 'content' => $bctai_chatgpt_message);
                                }
                            }
                            $bctai_chat_greeting_message .= "\n" . $bctai_conversation_end_message;
                        }
                        // $prompt = $bctai_chat_greeting_message;
                    } else {
                        // $prompt = $bctai_chat_greeting_message . "\n" . $bctai_human_name . ": " . $bctai_message . "\nAI3: ";
                        $bctai_chatgpt_messages[] = array('role' => 'user', 'content' => $bctai_message);
                    }
                    
                    if($bctai_chat_provider =='Google'){
                        $bctai_data_request = [
                            'model' => $bctai_ai_model,
                            'messages' => $bctai_chatgpt_messages,
                            'temperature' => floatval($bctai_chat_temperature),
                            'max_tokens' => intval($bctai_chat_max_tokens),
                            'frequency_penalty' => floatval($bctai_chat_frequency_penalty),
                            'presence_penalty' => floatval($bctai_chat_presence_penalty),
                            'top_p' => floatval($bctai_chat_top_p),
                            'sourceModule' => 'chat'
                        ];
                        $complete = $open_ai->chat($bctai_data_request);
                    }else{

                        if ($bctai_ai_model === 'gpt-3.5-turbo' || $bctai_ai_model === 'gpt-3.5-turbo-16k' || $bctai_ai_model == 'gpt-4') {
                            $bctai_data_request = [
                                'model' => $bctai_ai_model,
                                'messages' => $bctai_chatgpt_messages,
                                'temperature' => floatval($bctai_chat_temperature),
                                'max_tokens' => intval($bctai_chat_max_tokens),
                                'frequency_penalty' => floatval($bctai_chat_frequency_penalty),
                                'presence_penalty' => floatval($bctai_chat_presence_penalty),
                                'top_p' => floatval($bctai_chat_top_p)
                            ];
                            $complete = $open_ai->chat($bctai_data_request);
                        } else {

                            //wp_send_json($bctai_ai_model);
                            $bctai_data_request = [
                                'model' => $bctai_ai_model,
                                'messages' => $bctai_chatgpt_messages,
                                'temperature' => floatval($bctai_chat_temperature),
                                'max_tokens' => intval($bctai_chat_max_tokens),
                                'frequency_penalty' => floatval($bctai_chat_frequency_penalty),
                                'presence_penalty' => floatval($bctai_chat_presence_penalty),
                                'top_p' => floatval($bctai_chat_top_p),
                            ];
                            $complete = $open_ai->chat($bctai_data_request);
                        }

                    }
                    $complete = json_decode($complete);

                    wp_send_json($complete);



                    if (isset($complete->error)) {
                        $bctai_result['status'] = 'error';
                        $bctai_result['msg'] = esc_html(trim($complete->error->message));
                        if (empty($bctai_result['msg']) && isset($complete->error->code) && $complete->error->code == 'invalid_api_key') {
                            $bctai_result['msg'] = 'Incorrect API key provided. You can find your API key at https://platform.openai.com/account/api-keys.';
                        }
                    } else {
                        if ($bctai_ai_model === 'gpt-3.5-turbo' || $bctai_ai_model === 'gpt-3.5-turbo-16k' || $bctai_ai_model == 'gpt-4-32k' || $bctai_ai_model == 'gpt-4') {
                            if($bctai_embedding_score){
                                $bctai_result['data'] = $complete->choices[0]->message->content . '(코사인유사도 : '.$formatted_embedding_score.')'; 
                            }else{
                                $bctai_result['data'] = $complete->choices[0]->message->content; //답변
                            }
                        } else {
                            if($bctai_embedding_score){
                                $bctai_result['data'] = $complete->choices[0]->message->content . '(코사인유사도 : '.$formatted_embedding_score.')'; 
                            }else{
                                $bctai_result['data'] = $complete->choices[0]->message->content; //답변
                            }
                        }
                        $bctai_total_tokens += $complete->usage->total_tokens;
                        if (!$bctai_save_request) {
                            $bctai_data_request = false;
                        }
                        $this->bctai_save_chat_log($bctai_chat_log_id, $bctai_chat_log_data, 'ai', $bctai_result['data'], $bctai_total_tokens, false, $bctai_data_request);
                        $bctai_result['status'] = 'success';
                        $bctai_result['log'] = $bctai_chat_log_id;
                        #Save token handing
                        if ($bctai_limited_tokens) {
                            if ($bctai_chat_token_id) {
                                $wpdb->update($wpdb->prefix . 'bctai_chattokens', array(
                                    'tokens' => ($bctai_total_tokens + $bctai_token_usage_client)
                                ), array('id' => $bctai_chat_token_id));
                            } else {
                                $bctai_chattoken_data = array(
                                    'tokens' => $bctai_total_tokens,
                                    'source' => $bctai_chat_source,
                                    'created_at' => time()
                                );
                                if (is_user_logged_in()) {
                                    $bctai_chattoken_data['user_id'] = get_current_user_id();
                                } else {
                                    $bctai_chattoken_data['session_id'] = $bctai_client_id;
                                }
                                $wpdb->insert($wpdb->prefix . 'bctai_chattokens', $bctai_chattoken_data);
                            }
                        }
                        #End save token handing
                        if ($bctai_chat_remember_conversation == 'yes') {
                            $bctai_conversation_end_messages[] = $bctai_result['data'];
                            setcookie($bctai_session_page, serialize($bctai_conversation_end_messages), time() + 86400, COOKIEPATH, COOKIE_DOMAIN);
                        }
                    }
                } else {
                    $bctai_result['status'] = 'error';
                    $bctai_result['msg'] = 'It appears that nothing was inputted.';
                }

            }
            wp_send_json($bctai_result);
        }

        public function getIpAddress()
        {
            $ipAddress = '';
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                // to get shared ISP IP address
                $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
            } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                // check for IPs passing through proxy servers
                // check if multiple IP addresses are set and take the first one
                $ipAddressList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach ($ipAddressList as $ip) {
                    if (!empty($ip)) {
                        // if you prefer, you can check for valid IP address here
                        $ipAddress = $ip;
                        break;
                    }
                }
            } else if (!empty($_SERVER['HTTP_X_FORWARDED'])) {
                $ipAddress = $_SERVER['HTTP_X_FORWARDED'];
            } else if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
                $ipAddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
            } else if (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
                $ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
            } else if (!empty($_SERVER['HTTP_FORWARDED'])) {
                $ipAddress = $_SERVER['HTTP_FORWARDED'];
            } else if (!empty($_SERVER['REMOTE_ADDR'])) {
                $ipAddress = $_SERVER['REMOTE_ADDR'];
            }
            return $ipAddress;
        }

        public function bctai_save_chat_log($bctai_log_id, $bctai_log_data, $type = 'user', $message = '', $tokens = 0, $flag = false, $request = '')
        {
            global $wpdb;
            if ($bctai_log_id) {
                $bctai_log_data[] = array('message' => $message, 'type' => $type, 'date' => time(), 'token' => $tokens, 'flag' => $flag, 'request' => $request);
                $wpdb->update(
                    $wpdb->prefix . 'bctai_chatlogs',
                    array(
                        'data' => wp_json_encode($bctai_log_data),
                        'created_at' => time()
                    ),
                    array(
                        'id' => $bctai_log_id
                    )
                );
            }
        }

        public function bctai_embeddings_result($open_ai, $bctai_pinecone_api, $bctai_pinecone_environment, $bctai_message, $bctai_chat_embedding_top, $namespace = false)
        {
            $result = array('status' => 'error', 'data' => '');

            $Embedding_Provider = get_option('Embedding_Provider','OpenAI');
            if($Embedding_Provider=='OpenAI'){
                $BCT_Embedding_model = get_option('OpenAI_Embedding_model','text-embedding-3-small');
            }else if($Embedding_Provider=='Google'){
                $BCT_Embedding_model = get_option('Google_Embedding_model','text-embedding-004');
            }




            if (!empty($bctai_pinecone_api) && !empty($bctai_pinecone_environment)) {
                $response = $open_ai->embeddings([
                    'input' => $bctai_message,
                    'model' => $BCT_Embedding_model
                ]);
                $response = json_decode($response, true);
                //return $response;
                
                if (isset($response['error']) && !empty($response['error'])) {
                    $result['data'] = $response['error']['message'];
                    if (empty($result['data']) && isset($response['error']['code']) && $response['error']['code'] == 'invalid_api_key') {
                        $result['data'] = 'Incorrect API key provided. You can find your API key at https://platform.openai.com/account/api-keys.';
                    }
                } else {
                    $embedding = $response['data'][0]['embedding'];

                    //return $embedding;
                    if (!empty($embedding)) {
                        $result['tokens'] = $response['usage']['total_tokens'];
                        $headers = array(
                            'Content-Type' => 'application/json',
                            'Api-Key' => $bctai_pinecone_api
                        );
                        $pinecone_body = array(
                            'vector' => $embedding,
                            'topK' => $bctai_chat_embedding_top
                        );
                        if ($namespace) {
                            $pinecone_body['namespace'] = $namespace;
                        }
                        $response = wp_remote_post(
                            'https://' . $bctai_pinecone_environment . '/query',
                            array(
                                'headers' => $headers,
                                'body' => wp_json_encode($pinecone_body)
                            )
                        );

                        //return $response;
                        if (is_wp_error($response)) {
                            $result['data'] = esc_html($response->get_error_message());
                        } else {
                            $body = json_decode($response['body'], true);
                            //return $body;

                            //$result['data'] = $body;
                            //return $result;
                            //return $response;
                            if ($body) {
                                if (isset($body['matches']) && is_array($body['matches']) && count($body['matches'])) {
                                    $data = '';
                                    $score = array();
                                    // $infomation_status = '';
                                    foreach ($body['matches'] as $match) {
                                        $score[] = $match['score'];
                                        $bctai_embedding = get_post($match['id']);
                                        //return $match['id'];

                                        $bctai_information_status = get_post_meta($match['id'], 'information_status', true);
                                        $bctai_link_url = get_post_meta($match['id'], 'bctai_link_url', true);

                                        if($bctai_information_status){
                                            $result['infomation_status'] = $bctai_information_status;
                                            //$infomation_status .= $bctai_information_status;
                                        }
                                        if($bctai_link_url){
                                            $result['bctai_link_url'] = $bctai_link_url;
                                        }

                                        if ($bctai_embedding) {
                                            $data .= empty($data) ? $bctai_embedding->post_content : "\n" . $bctai_embedding->post_content;
                                        }

                                    }
                                    //$result['infomation_status'] = $infomation_status;

                                    $result['score'] = $score;
                                    $result['data'] = $data;
                                    $result['status'] = 'success';
                                }
                            } else {
                                $result['data'] = 'Pinecone return empty';
                            }
                        }
                    }
                }
            } else {
                $bctai_result['data'] = 'Missing PineCone Settings';
            }
            //$result['data'] = $data;
            return $result;
        }


        public function wpaicg_embeddings_result_qdrant($bctai_chat_provider, $open_ai, $bctai_qdrant_api, $bctai_qdrant_endpoint, $default_qdrant_collection, $bctai_message, $wpaicg_chat_embedding_top, $namespace = false)
        {

            //return $bctai_qdrant_endpoint . '/collections/' . $default_qdrant_collection . '/points/search';
            //return 'dkdkdkdkdk';
            $result = array('status' => 'error','data' => '');
            if(!empty($bctai_qdrant_api) && !empty($bctai_qdrant_endpoint && !empty($default_qdrant_collection))) {

                $Embedding_Provider = get_option('Embedding_Provider','OpenAI');

                if($Embedding_Provider=='OpenAI'){
                    $BCT_Embedding_model = get_option('OpenAI_Embedding_model','text-embedding-3-small');
                }else if($Embedding_Provider=='Google'){
                    $BCT_Embedding_model = get_option('Google_Embedding_model','text-embedding-004');
                }

                

                $embedding_engine = $open_ai;
                $model = $BCT_Embedding_model;

                
                $apiParams = [
                    'input' => $bctai_message,
                    'model' => $model
                ];

                // Make the API call
                $response = $embedding_engine->embeddings($apiParams);
                $response = json_decode($response, true);

                //return $response;

                if (isset($response['error']) && !empty($response['error'])) {
                    $result['data'] = $response['error']['message'];
                    
                    if(empty($result['data']) && isset($response['error']['code']) && $response['error']['code'] == 'invalid_api_key'){
                        $result['data'] = 'Incorrect API key provided. You can find your API key at https://platform.openai.com/account/api-keys.';
                    }
                } else {

                    $embedding = $response['data'][0]['embedding'];
                    if (!empty($embedding)) {
                        $result['tokens'] = $response['usage']['total_tokens'];
                        // Prepare Qdrant search query
                        $queryData = [
                            'vector' => $embedding,
                            'limit' => intval($wpaicg_chat_embedding_top)
                        ];
                        
                        $group_id_value = $namespace ?: "default";

                        $queryData['filter'] = [
                            'must' => [
                                [
                                    'key' => 'group_id',
                                    'match' => [
                                        'value' => $group_id_value
                                    ]
                                ]
                            ]
                        ];

                        $query = json_encode($queryData);

                        // Send request to Qdrant
                        $response = wp_remote_post($bctai_qdrant_endpoint . '/collections/' . $default_qdrant_collection . '/points/search', array(
                            'method' => 'POST',
                            'headers' => [
                                'api-key' => $bctai_qdrant_api,
                                'Content-Type' => 'application/json'
                            ],
                            'body' => $query
                        ));

                        $result['QdrantResponse'] = $response;

                        // return $response;
                        
                        if (is_wp_error($response)) {
                            $result['data'] = esc_html($response->get_error_message());
                        } else {
                            $bodyContent = wp_remote_retrieve_body($response);
                            $body = json_decode($bodyContent, true);
                            if (isset($body['result']) && is_array($body['result'])) {
                                $data = '';
                                foreach ($body['result'] as $match) {
                                    // Retrieve post content for each matched ID
                                    $wpaicg_embedding = get_post($match['id']);
                                    if ($wpaicg_embedding) {
                                        $data .= empty($data) ? $wpaicg_embedding->post_content : "\n" . $wpaicg_embedding->post_content;
                                    }
                                }
                                $result['data'] = $data;
                                $result['status'] = 'success';
                            } else {
                                $errror_message_from_api = isset($body['status']['error']) ? $body['status']['error'] : esc_html__('No results from Qdrant.', 'gpt3-ai-content-generator');
                                $errror_message_from_api = esc_html__('Response from Qdrant: ', 'gpt3-ai-content-generator') . $errror_message_from_api;
                                $result['status'] = 'error';
                                $stream_nav_setting = $this->determine_stream_nav_setting($wpaicg_chat_source, $bctai_chat_provider);
                                $stream_pinecone_error = ['msg'    => $errror_message_from_api, 'pineconeError' => true];
                                if ($stream_nav_setting == 1) {
                                    header('Content-Type: text/event-stream');
                                    header('Cache-Control: no-cache');
                                    header( 'X-Accel-Buffering: no' );
                                    echo "data: " . wp_json_encode($stream_pinecone_error) . "\n\n";
                                    ob_implicit_flush( true );
                                    // Flush and end buffer if it exists
                                    if (ob_get_level() > 0) {
                                        ob_end_flush();
                                    }
                                    exit;
                                } else {
                                    $result['data'] = $errror_message_from_api;
                                }
                            }
                        }
                    }
                }
            }
            else{
                $result['data'] = esc_html__('Something wrong with Qdrant setup3. Check your Qdrant settings.','gpt3-ai-content-generator');
            }
            return $result;
        }

        public function bctai_chatbox($atts)
        {
            ob_start();
            include BCTAI_PLUGIN_DIR . 'admin/extra/bctai_chatbox.php';
            $bctai_chatbox = ob_get_clean();
            return $bctai_chatbox;
        }

        public function bctai_chatbox_widget()
        {
            ob_start();
            include BCTAI_PLUGIN_DIR . 'admin/extra/bctai_chatbox_widget.php';
            $bctai_chatbox = ob_get_clean();
            return $bctai_chatbox;
        }
    }
    BCTAI_Chat::get_instance();
}