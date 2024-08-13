<?php
namespace BCTAI;

if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('\\BCTAI\\BCTAI_PDF')) {
    class BCTAI_PDF
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
            add_action('wp_ajax_bctai_pdf_embedding',[$this,'bctai_pdf_embedding']);
            add_action('wp_ajax_bctai_admin_pdf',[$this,'bctai_admin_pdf']);
            add_action('wp_ajax_nopriv_bctai_pdf_embedding',[$this,'bctai_pdf_embedding']);
            add_action('wp_ajax_bctai_example_questions',[$this,'bctai_example_questions']);
            add_action('wp_ajax_nopriv_bctai_example_questions',[$this,'bctai_example_questions']);
            // add_action( 'admin_enqueue_scripts', [$this,'bctai_enqueue_scripts'],1);
            // add_action( 'wp_enqueue_scripts', [$this,'bctai_enqueue_scripts'],1);
            add_action('wp_ajax_bctai_pdfs_delete',[$this,'bctai_pdfs_delete']);
        }

        public function bctai_admin_pdf()
        {   
            $bctai_result = array('status' => 'error');
            if ( ! wp_verify_nonce( $_POST['nonce'], 'bctai-ajax-action' ) ) {
                $bctai_result['msg'] = esc_html__('Nonce verification failed','gpt3-ai-content-generator');
                wp_send_json($bctai_result);
            }
            $content = sanitize_text_field($_REQUEST['content']);
            //wp_send_json($_REQUEST['content']);
            $page = sanitize_text_field($_REQUEST['page']);
            $filename = sanitize_text_field($_REQUEST['filename']);
            $bctai_provider = get_option('bctai_provider', 'OpenAI');
            $openai = bctai_OpenAI::get_instance()->openai();
            // if provider not openai then assing azure to $open_ai
            if($bctai_provider != 'OpenAI'){
                $openai = bctai_AzureAI::get_instance()->azureai();
            }
            if($openai){
                $bctai_pinecone_api = get_option('bctai_pinecone_api','');
                $bctai_pinecone_environment = get_option('bctai_pinecone_environment','');
                if(empty($bctai_pinecone_api) || empty($bctai_pinecone_environment)){
                    $bctai_result['msg'] = esc_html__('Missing Pinecone API Settings','gpt3-ai-content-generator');
                }
                else{
                    $bctai_model = ($bctai_provider === 'Azure') ? get_option('bctai_azure_embeddings') : 'text-embedding-3-small';
                    $response = $openai->embeddings(array(
                        'input' => $content,
                        'model' => $bctai_model
                    ));
                    $response = json_decode($response,true);
                    //wp_send_json($response);
                    if(isset($response['error']) && !empty($response['error'])) {
                        $bctai_result['msg'] = $response['error']['message'];
                        if(empty($bctai_result['msg']) && isset($response['error']['code']) && $response['error']['code'] == 'invalid_api_key'){
                            $bctai_result['msg'] = 'Incorrect API key provided. You can find your API key at https://platform.openai.com/account/api-keys.';
                        }
                    }
                    else{
                        $embedding = $response['data'][0]['embedding'];
                        if(empty($embedding)){
                            $bctai_result['msg'] = esc_html__('No data returned','gpt3-ai-content-generator');
                        }
                        else{
                            $pinecone_url = 'https://' . $bctai_pinecone_environment . '/vectors/upsert';
                            $headers = array(
                                'Content-Type' => 'application/json',
                                'Api-Key' => $bctai_pinecone_api
                            );
                            $embedding_data = array(
                                'post_type' => 'bctai_pdfadmin',
                                'post_title' => $filename.' - Page: '.$page,
                                'post_content' => $content,
                                'post_excerpt' => $bctai_pinecone_environment,
                                'post_status' => 'publish'
                            );
                            $embeddings_id = wp_insert_post($embedding_data,true);
                            if(is_wp_error($embeddings_id)){
                                $bctai_result['msg'] = $embeddings_id->get_error_message();
                                add_post_meta($embeddings_id, 'bctai_indexed', 'error');
                            }
                            //wp_send_json($embeddings_id);
                            else{
                                update_post_meta($embeddings_id,'bctai_start',time());
                                $usage_tokens = $response['usage']['total_tokens'];
                                add_post_meta($embeddings_id, 'bctai_embedding_token', $usage_tokens);
                                add_post_meta($embeddings_id, 'bctai_indexed', 'yes');
                                $vectors = array(
                                    array(
                                        'id' => (string)$embeddings_id,
                                        'values' => $embedding
                                    )
                                );
                                $response = wp_remote_post($pinecone_url, array(
                                    'headers' => $headers,
                                    'body' => wp_json_encode(array('vectors' => $vectors))
                                ));
                                //wp_send_json($response);
                                if(is_wp_error($response)){
                                    $bctai_result['msg'] = $response->get_error_message();
                                    wp_delete_post($embeddings_id);
                                }
                                else{
                                    update_post_meta($embeddings_id,'bctai_complete',time());
                                    $bctai_result['status'] = 'success';
                                }
                            }
                        }
                    }
                }
            }
            else{
                $bctai_result['msg'] = esc_html__('Missing OpenAI API Settings','gpt3-ai-content-generator');
            }
            //$bctai_result['msg'] = 'returnnnnn';
            wp_send_json($bctai_result);
        }

        public function bctai_pdfs_delete()
        {
            $bctai_result = array('status' => 'error');
            if ( ! wp_verify_nonce( $_POST['nonce'], 'bctai-ajax-nonce' ) ) {
                $bctai_result['msg'] = esc_html__('Nonce verification failed','gpt3-ai-content-generator');
                wp_send_json($bctai_result);
            }
            $type = 'bctai_pdfembed';
            if(isset($_REQUEST['type']) && !empty($_REQUEST['type'])){
                $type = sanitize_text_field($_REQUEST['type']);
            }
            $ids = bctai_util_core()->sanitize_text_or_array_field($_REQUEST['ids']);
            $this->bctai_delete_embeddings_ids($ids,$type);
            $bctai_result['status'] = 'success';
            wp_send_json($bctai_result);
        }

        public function bctai_delete_embeddings_ids($ids,$type = 'bctai_pdfembed')
        {
            $posts = new \WP_Query(array(
                'post__in' => $ids,
                'posts_per_page' => -1,
                'post_type' => $type
            ));
            if($posts->post_count){
                $bctai_pinecone_api = get_option('bctai_pinecone_api','');
                $bctai_pinecone_environment = get_option('bctai_pinecone_environment','');
                foreach($posts->posts as $post){
                    if(!empty($post->post_excerpt)){
                        $bctai_pinecone_environment = $post->post_excerpt;
                    }
                    try {
                        $headers = array(
                            'Content-Type' => 'application/json',
                            'Api-Key' => $bctai_pinecone_api
                        );
                        $pinecone_ids = 'ids='.$post->ID;
                        $response = wp_remote_request('https://' . $bctai_pinecone_environment . '/vectors/delete?'.$pinecone_ids, array(
                            'method' => 'DELETE',
                            'headers' => $headers
                        ));
                    }
                    catch (\Exception $exception){

                    }
                    wp_delete_post($post->ID);
                }
            }
        }

        // public function bctai_enqueue_scripts()
        // {
        //     $bctai_settings = get_option('bctai_chat_shortcode_options', array());
        //     $bctai_chat_widget = get_option('bctai_chat_widget', array());
        
        //     $is_pdf_enabled_in_shortcodes = isset($bctai_settings['embedding_pdf']) && $bctai_settings['embedding_pdf'];
        //     $is_pdf_enabled_in_widgets = isset($bctai_chat_widget['embedding_pdf']) && $bctai_chat_widget['embedding_pdf'];
        //     $is_pdf_enabled_in_chatbots = false;
        
        //     // If PDF embedding is not enabled in shortcodes or widgets, then check chatbot posts
        //     if (!$is_pdf_enabled_in_shortcodes && !$is_pdf_enabled_in_widgets) {
        //         // Query chatbot posts
        //         $args = array(
        //             'post_type' => 'bctai_chatbot',
        //             'posts_per_page' => -1
        //         );
        //         $chatbot_posts = get_posts($args);
        
        //         // Loop through chatbot posts and check if PDF embedding is enabled in any of them
        //         foreach ($chatbot_posts as $post) {
        //             $chatbot_data = json_decode($post->post_content, true);
        
        //             if (isset($chatbot_data['embedding_pdf']) && $chatbot_data['embedding_pdf']) {
        //                 $is_pdf_enabled_in_chatbots = true;
        //                 break;
        //             }
        //         }
        //     }
        
        //     if ($is_pdf_enabled_in_shortcodes || $is_pdf_enabled_in_widgets || $is_pdf_enabled_in_chatbots || is_admin()) {
        //         wp_enqueue_script('bctai-pdf', bctai_PLUGIN_URL.'lib/js/pdf.js', array(), null, true);
        //     }
        
        //     wp_enqueue_script('bctai-chat-pro', bctai_PLUGIN_URL.'lib/js/bctai-chat-pro.js', array(), null, true);
        // } 
        
        public function bctai_example_questions()
        {
            $bctai_result = array('status' => 'error');
            if ( ! wp_verify_nonce( $_POST['nonce'], 'bctai-chatbox' ) ) {
                $bctai_result['msg'] = esc_html__('Nonce verification failed','gpt3-ai-content-generator');
                wp_send_json($bctai_result);
            }
            $bctai_provider = get_option('bctai_provider', 'OpenAI');
            $openai = bctai_OpenAI::get_instance()->openai();
            // if provider not openai then assing azure to $open_ai
            if($bctai_provider != 'OpenAI'){
                $openai = bctai_AzureAI::get_instance()->azureai();
            }
            if($openai){
                $type = isset($_REQUEST['type']) && !empty($_REQUEST['type']) ? sanitize_text_field($_REQUEST['type']) : 'shortcode';
                $bot_id = isset($_REQUEST['bot_id']) && !empty($_REQUEST['bot_id']) ? sanitize_text_field($_REQUEST['bot_id']) : 0;
                $content = sanitize_text_field($_REQUEST['content']);
                $language = 'en';
                $embedding_pdf_message = "Congrats! Your PDF is uploaded now! You can ask questions about your document.\nExample Questions:[questions]";
                if($type == 'shortcode'){
                    $bctai_chat_shortcode_options = get_option('bctai_chat_shortcode_options',[]);
                    if(isset($bctai_chat_shortcode_options['embedding_pdf_message']) && !empty($bctai_chat_shortcode_options['embedding_pdf_message'])){
                        $embedding_pdf_message = $bctai_chat_shortcode_options['embedding_pdf_message'];
                    }
                    if(isset($bctai_chat_shortcode_options['language']) && !empty($bctai_chat_shortcode_options['language'])){
                        $language = $bctai_chat_shortcode_options['language'];
                    }
                }
                else{
                    $bctai_chat_widget = get_option('bctai_chat_widget',[]);
                    $language = get_option('bctai_chat_language','en');
                    if(isset($bctai_chat_widget['embedding_pdf_message']) && $bctai_chat_widget['embedding_pdf_message']){
                        $embedding_pdf_message = $bctai_chat_widget['embedding_pdf_message'];
                    }
                }
                if($bot_id){
                    $bot = get_post($bot_id);
                    if(strpos($bot->post_content,'\"') !== false) {
                        $bot->post_content = str_replace('\"', '&quot;', $bot->post_content);
                    }
                    if(strpos($bot->post_content,"\'") !== false) {
                        $bot->post_content = str_replace('\\', '', $bot->post_content);
                    }
                    $bot_config = json_decode($bot->post_content,true);
                    if(isset($bot_config['embedding_pdf_message']) && !empty($bot_config['embedding_pdf_message'])){
                        $embedding_pdf_message = $bot_config['embedding_pdf_message'];
                    }
                    if(isset($bot_config['language']) && !empty($bot_config['language'])){
                        $language = $bot_config['language'];
                    }
                }
                $generator = bctai_Generator::get_instance();
                $generator->openai($openai);
                $bctai_language_file = bctai_PLUGIN_DIR . 'admin/chat/languages/' . $language . '.json';
                if (!file_exists($bctai_language_file)) {
                    $bctai_language_file = bctai_PLUGIN_DIR . 'admin/chat/languages/en.json';
                }
                $bctai_language_json = wp_remote_get($bctai_language_file);
                $bctai_languages = json_decode($bctai_language_json, true);
                $prompt = "Give me 3 questions about this text: ".$content;
                if(isset($bctai_languages['question_prompt']) && !empty($bctai_languages['question_prompt'])){
                    $prompt = sprintf($bctai_languages['question_prompt'],$content);
                }
                $model_name = ($bctai_provider === 'Azure') ? get_option('bctai_azure_deployment') : 'gpt-3.5-turbo-16k';
                $opts = array(
                    "model" => $model_name,
                    "temperature" => 0.5,
                    "max_tokens" => 700,
                    "frequency_penalty" => 0,
                    "presence_penalty" => 0,
                    "top_p" => 1,
                    "prompt" => $prompt
                );
                $result = $generator->bctai_request($opts);
                if($result['status'] == 'error'){
                    $bctai_result['msg'] = $result['msg'];
                }
                else{
                    $data = $result['data'];
                    $embedding_pdf_message = str_replace('[questions]',"\n".$data,$embedding_pdf_message);
                    $bctai_result['status'] = 'success';
                    $bctai_result['data'] = $embedding_pdf_message;
                    $bctai_result['prompt'] = $prompt;
                }

            }
            else{
                $bctai_result['msg'] = esc_html__('Missing OpenAI API Settings','gpt3-ai-content-generator');
            }
            wp_send_json($bctai_result);
        }

        public function bctai_pdf_embedding()
        {
            $bctai_result = array('status' => 'error');
            if ( ! wp_verify_nonce( $_POST['nonce'], 'bctai-chatbox' ) ) {
                $bctai_result['msg'] = esc_html__('Nonce verification failed','gpt3-ai-content-generator');
                wp_send_json($bctai_result);
            }
            $type = isset($_REQUEST['type']) && !empty($_REQUEST['type']) ? sanitize_text_field($_REQUEST['type']) : 'shortcode';
            $bot_id = isset($_REQUEST['bot_id']) && !empty($_REQUEST['bot_id']) ? sanitize_text_field($_REQUEST['bot_id']) : 0;
            $filename = sanitize_text_field($_REQUEST['filename']);
            $page = sanitize_text_field($_REQUEST['page']);
            $content = sanitize_text_field($_REQUEST['content']);
            $namespace = sanitize_text_field($_REQUEST['namespace']);
            $bctai_provider = get_option('bctai_provider', 'OpenAI');
            $openai = bctai_OpenAI::get_instance()->openai();
            // if provider not openai then assing azure to $open_ai
            if($bctai_provider != 'OpenAI'){
                $openai = bctai_AzureAI::get_instance()->azureai();
            }
            $use_embedding = false;
            if($openai){
                $bctai_pinecone_api = get_option('bctai_pinecone_api','');
                $bctai_pinecone_environment = get_option('bctai_pinecone_environment','');
                if($type == 'shortcode'){
                    $bctai_chat_shortcode_options = get_option('bctai_chat_shortcode_options',[]);
                    if(isset($bctai_chat_shortcode_options['embedding']) && $bctai_chat_shortcode_options['embedding']){
                        $use_embedding = true;
                    }
                    if(isset($bctai_chat_shortcode_options['embedding_index']) && !empty($bctai_chat_shortcode_options['embedding_index'])){
                        $bctai_pinecone_environment = $bctai_chat_shortcode_options['embedding_index'];
                    }
                }
                else{
                    $bctai_chat_widget = get_option('bctai_chat_widget',[]);
                    $bctai_chat_embedding = get_option('bctai_chat_embedding',false);
                    if($bctai_chat_embedding){
                        $use_embedding = true;
                    }
                    if(isset($bctai_chat_widget['embedding_index']) && !empty($bctai_chat_widget['embedding_index'])){
                        $bctai_pinecone_environment = $bctai_chat_widget['embedding_index'];
                    }
                }
                if($bot_id){
                    $bot = get_post($bot_id);
                    if(strpos($bot->post_content,'\"') !== false) {
                        $bot->post_content = str_replace('\"', '&quot;', $bot->post_content);
                    }
                    if(strpos($bot->post_content,"\'") !== false) {
                        $bot->post_content = str_replace('\\', '', $bot->post_content);
                    }
                    $bot_config = json_decode($bot->post_content,true);
                    if(isset($bot_config['embedding']) && !empty($bot_config['embedding'])){
                        $use_embedding = true;
                    }
                    else{
                        $use_embedding = false;
                    }
                    if(isset($bot_config['embedding_index']) && !empty($bot_config['embedding_index'])){
                        $bctai_pinecone_environment = $bot_config['embedding_index'];
                    }
                }
                if($use_embedding){
                    $bctai_model = ($bctai_provider === 'Azure') ? get_option('bctai_azure_embeddings') : 'text-embedding-3-small';
                    $response = $openai->embeddings(array(
                        'input' => $content,
                        'model' => $bctai_model
                    ));
                    $response = json_decode($response,true);
                    if(isset($response['error']) && !empty($response['error'])) {
                        $bctai_result['msg'] = $response['error']['message'];
                        if(empty($bctai_result['msg']) && isset($response['error']['code']) && $response['error']['code'] == 'invalid_api_key'){
                            $bctai_result['msg'] = 'Incorrect API key provided. You can find your API key at https://platform.openai.com/account/api-keys.';
                        }
                    }
                    else{
                        $embedding = $response['data'][0]['embedding'];
                        if(empty($embedding)){
                            $bctai_result['msg'] = esc_html__('No data returned','gpt3-ai-content-generator');
                        }
                        else{
                            $pinecone_url = 'https://' . $bctai_pinecone_environment . '/vectors/upsert';
                            $headers = array(
                                'Content-Type' => 'application/json',
                                'Api-Key' => $bctai_pinecone_api
                            );
                            $embedding_data = array(
                                'post_type' => 'bctai_pdfembed',
                                'post_title' => $filename.' - Page: '.$page,
                                'post_content' => $content,
                                'post_excerpt' => $bctai_pinecone_environment,
                                'post_status' => 'publish'
                            );
                            $embeddings_id = wp_insert_post($embedding_data);
                            if(is_wp_error($embeddings_id)){
                                $bctai_result['msg'] = $embeddings_id->get_error_message();
                            }
                            else{
                                update_post_meta($embeddings_id,'bctai_start',time());
                                $usage_tokens = $response['usage']['total_tokens'];
                                add_post_meta($embeddings_id, 'bctai_embedding_token', $usage_tokens);
                                $vectors = array(
                                    array(
                                        'id' => (string)$embeddings_id,
                                        'values' => $embedding
                                    )
                                );
                                $response = wp_remote_post($pinecone_url, array(
                                    'headers' => $headers,
                                    'body' => wp_json_encode(array('vectors' => $vectors,'namespace' => $namespace))
                                ));
                                if(is_wp_error($response)){
                                    $bctai_result['msg'] = $response->get_error_message();
                                    wp_delete_post($embeddings_id);
                                }
                                else{
                                    $bctai_result['status'] = 'success';
                                }
                            }
                        }
                    }
                }
                else{
                    $bctai_result['status'] = 'no_embedding';
                }
            }
            else{
                $bctai_result['msg'] = esc_html__('Missing OpenAI API Settings','gpt3-ai-content-generator');
            }
            wp_send_json($bctai_result);
        }

    }
    BCTAI_PDF::get_instance();
}
