<?php
namespace BCTAI;
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('\\BCTAI\\BCTAI_Audio')) {
    class BCTAI_Audio
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
            add_action( 'wp_ajax_bctai_audio_settings', array( $this, 'bctai_settings' ) );
            add_action( 'wp_ajax_bctai_audio_converter', array( $this, 'bctai_audio_converter' ) );
            add_action( 'wp_ajax_bctai_speech_record', array( $this, 'bctai_speech_record' ) );
            add_action('init',[$this,'bctai_download_audio'],1);
            add_filter( 'upload_mimes', function ($mime_types){
                $mime_types['wav'] = 'audio/wav';
                $mime_types['xwav'] = 'audio/x-wav';
                return $mime_types;
            });
        }

        

        public function bctai_audio()
        {
            include BCTAI_PLUGIN_DIR . 'admin/views/audio/index.php';
        }


        public function bctai_seconds_to_time( $seconds )
        {
            $dtF = new \DateTime( '@0' );
            $dtT = new \DateTime( "@{$seconds}" );
            return $dtF->diff( $dtT )->format( '%h hours, %i minutes and %s seconds' );
        }


        public function bctai_settings()
        {
            $bctai_result['status'] = 'success';
            if ( ! wp_verify_nonce( $_POST['nonce'], 'bctai-ajax-nonce' ) ) {
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = BCTAI_NONCE_ERROR;
                wp_send_json($bctai_result);
            }
            $purpose = isset($_REQUEST['purpose']) && !empty($_REQUEST['purpose']) ? sanitize_text_field($_REQUEST['purpose']) : 'transcriptions';            
            $type = isset($_REQUEST['type']) && !empty($_REQUEST['type']) ? sanitize_text_field($_REQUEST['type']) : 'upload';
            $model = isset($_REQUEST['model']) && !empty($_REQUEST['model']) ? sanitize_text_field($_REQUEST['model']) : 'whisper-1';
            $prompt = isset($_REQUEST['prompt']) && !empty($_REQUEST['prompt']) ? sanitize_text_field($_REQUEST['prompt']) : '';
            $response = isset($_REQUEST['response']) && !empty($_REQUEST['response']) ? sanitize_text_field($_REQUEST['response']) : 'post';
            $title = isset($_REQUEST['title']) && !empty($_REQUEST['title']) ? sanitize_text_field($_REQUEST['title']) : '';
            $category = isset($_REQUEST['category']) && !empty($_REQUEST['category']) ? sanitize_text_field($_REQUEST['category']) : '';
            $author_id = isset($_REQUEST['author']) && !empty($_REQUEST['author']) ? sanitize_text_field($_REQUEST['author']) : '';
            $status = isset($_REQUEST['status']) && !empty($_REQUEST['status']) ? sanitize_text_field($_REQUEST['status']) : 'draft';            
            $temperature = isset($_REQUEST['temperature']) && !empty($_REQUEST['temperature']) ? sanitize_text_field($_REQUEST['temperature']) : 0;
            $language = isset($_REQUEST['language']) && !empty($_REQUEST['language']) ? sanitize_text_field($_REQUEST['language']) : 'en';
            $bctai_audio_settings = array(
                'purpose'   => $purpose,
                'type'      => $type,
                'model'     => $model,
                'response'  => $response,
                'category'  => $category,
                'status'    => $status,
                'author'    => $author_id,
                'temperature'   => $temperature,
                'language'  => $language,
            );
            update_option('bctai_audio_setting', $bctai_audio_settings);
            wp_send_json($bctai_result);
        }

        public function bctai_audio_converter()
        {
            $bctai_generator_start = microtime( true );
            $bctai_result = array('status' => 'error', 'msg' => 'Something went wrong');
            if( ! wp_verify_nonce( $_POST['nonce'], 'bctai-ajax-nonce' ) ) {
                $bctai_result['status'] = 'success';
                $bctai_result['msg'] = BCTAI_NONCE_ERROR;
                wp_send_json($bctai_result);
            }
            $purpose = isset($_REQUEST['purpose']) && !empty($_REQUEST['purpose']) ? sanitize_text_field($_REQUEST['purpose']) : 'transcriptions';
            $type = isset($_REQUEST['type']) && !empty($_REQUEST['type']) ? sanitize_text_field($_REQUEST['type']) : 'upload';
            $model = isset($_REQUEST['model']) && !empty($_REQUEST['model']) ? sanitize_text_field($_REQUEST['model']) : 'whisper-1';
            $prompt = isset($_REQUEST['prompt']) && !empty($_REQUEST['prompt']) ? sanitize_text_field($_REQUEST['prompt']) : '';
            $response = isset($_REQUEST['response']) && !empty($_REQUEST['response']) ? sanitize_text_field($_REQUEST['response']) : 'post';
            $title = isset($_REQUEST['title']) && !empty($_REQUEST['title']) ? sanitize_text_field($_REQUEST['title']) : '';
            $category = isset($_REQUEST['category']) && !empty($_REQUEST['category']) ? sanitize_text_field($_REQUEST['category']) : '';
            $author_id = isset($_REQUEST['author']) && !empty($_REQUEST['author']) ? sanitize_text_field($_REQUEST['author']) : '';
            $status = isset($_REQUEST['status']) && !empty($_REQUEST['status']) ? sanitize_text_field($_REQUEST['status']) : 'draft';
            $temperature = isset($_REQUEST['temperature']) && !empty($_REQUEST['temperature']) ? sanitize_text_field($_REQUEST['temperature']) : 0;
            $language = isset($_REQUEST['language']) && !empty($_REQUEST['language']) ? sanitize_text_field($_REQUEST['language']) : 'en';            
            $mime_types = ['mp3' => 'audio/mpeg','mp4' => 'video/mp4','mpeg' => 'video/mpeg','m4a' => 'audio/m4a','wav' => 'audio/wav','xwav' => 'audio/x-wav','webm' => 'video/webm'];            
            if($type == 'upload' && !isset($_FILES['file'])){
                $bctai_result['msg'] = 'An audio file is mandatory.';
                wp_send_json($bctai_result);
            }
            if(($response == 'post') && empty($title)){
                $bctai_result['msg'] = 'Please insert title';
                wp_send_json($bctai_result);
            }
            if($type == 'upload') {
                $file = $_FILES['file'];
                $file_name = sanitize_file_name(basename($file['name']));
                $filetype = wp_check_filetype($file_name);
                if(!in_array($filetype['type'], $mime_types)){
                    $bctai_result['msg'] = 'We only accept mp3, mp4, mpeg, mpga, m4a, wav, or webm.';
                    wp_send_json($bctai_result);
                }
                if($file['size'] > 26214400){
                    $bctai_result['msg'] = 'Audio file maximum 25MB';
                    wp_send_json($bctai_result);
                }
            }
            if($type == 'record') {
                $file = $_FILES['recorded_audio'];
                $file_name = sanitize_file_name(basename($file['name']));
                $filetype = wp_check_filetype($file_name);
                if(!in_array($filetype['type'], $mime_types)){
                    $bctai_result['msg'] = esc_html__('We only accept mp3, mp4, mpeg, mpga, m4a, wav, or webm.','gpt3-ai-content-generator');
                    wp_send_json($bctai_result);
                }
                if($file['size'] > 26214400){
                    $bctai_result['msg'] = 'Audio file maximum 25MB';
                    wp_send_json($bctai_result);
                }
                $tmp_file = $file['tmp_name'];

            }
            $open_ai = BCTAI_OpenAI::get_instance()->openai();
            if(!$open_ai) {
                $bctai_result['msg'] = 'Missing API Setting';
                wp_send_json($bctai_result);
            }
            if(!method_exists($open_ai, $purpose)){
                $bctai_result['msg'] = 'Method does not exist';
                wp_send_json($bctai_result);
            }
            if($type == 'upload') {
                $tmp_file = $file['tmp_name'];
            }
            $response_format = $response == 'post' ? 'text' : $response;
            $data_request = array(
                'audio' => array(
                    'filename' => $file_name,
                    'data' => wp_remote_get($tmp_file)
                ),
                'model' => $model,
                'temperature' => $temperature,
                'response_format' => $response_format,
                'prompt' => $prompt
            );            
            if($purpose == 'transcriptions' && !empty($language)) {
                $data_request['language'] = $language;
            }            
            $completion = $open_ai->$purpose($data_request);
            //$bctai_result['status'] = 'success';
            //$bctai_result['msg'] = $completion;
            //wp_send_json($bctai_result);
            $result = json_decode($completion);
            if($result && isset($result->error)){
                $bctai_result['msg'] = $result->error->message;
                if(empty($bctai_result['msg']) && isset($result->error->code) && $result->error->code == 'invalid_api_key'){
                    $bctai_result['msg'] = 'Incorrect API key provided. You can find your API key at https://platform.openai.com/account/api-keys.';
                }
                wp_send_json($bctai_result);
            }
            $bctai_result['status'] = 'success';
            $text_generated = $completion;
            $bctai_result['data'] = $text_generated;
            $bctai_generator_end = microtime( true ) - $bctai_generator_start;
            if(empty($text_generated)){
                $bctai_resulticg_result['msg'] = 'The model predicted a completion that begins with a stop sequence, resulting in no output. Consider adjusting your prompt or stop sequences.';
                wp_send_json($bctai_result);
            }
            $bctai_audio_id = wp_insert_post(array(
                'post_title' => $file_name,
                'post_type' => 'bctai_audio',
                'post_content' => $text_generated,
                'post_status' => 'publish'
            ));
            add_post_meta($bctai_audio_id, 'bctai_duration',$bctai_generator_end);
            add_post_meta($bctai_audio_id, 'bctai_response', $response);
            add_post_meta($bctai_audio_id, 'bctai_type', $type);
            if($response == 'post') {
                $post_data = array(
                    'post_title' => $title,
                    'post_content' => $text_generated,
                    'post_type' => $response,
                    'post_status' => $status
                );
                if(!empty($author_id)) {
                    $post_data['post_author'] = $author_id;
                }
                $bctai_post_id = wp_insert_post($post_data);
                if($response == 'post' && !empty($category)){
                    wp_set_post_terms($bctai_post_id, $category, 'category');
                }
                add_post_meta($bctai_audio_id, 'bctai_post', $bctai_post_id);
            }
            wp_send_json($bctai_result);
        }


        public function bctai_download_audio()
        {
            if(isset($_GET['bctai_download_audio']) && !empty($_GET['bctai_download_audio'])){
                $audio_id = sanitize_text_field($_GET['bctai_download_audio']);
                if(!wp_verify_nonce($_GET['_wpnonce'], 'bctai_download_'.$audio_id)){
                    die(BCTAI_NONCE_ERROR);
                }
                $audio = get_post($audio_id);
                if($audio){
                    $response = get_post_meta($audio_id,'bctai_response',true);
                    $response = empty($response) ? 'text' : $response;
                    $content = $audio->post_content;
                    $filename = 'bctai_audio_'.$audio_id;
                    if($response == 'text' || $response == 'post'){
                        header('Content-Type: text/plain');
                        $filename .= '.txt';
                    }
                    header('Content-Disposition: attachment; filename="'.$filename.'"');
                    header('Content-Length: ' . strlen($content));
                    header('Connection: close');
                    echo wp_kses_post($content);
                }
                exit;
            }
        }


        public function bctai_speech_record()
        {
            
            // $bctai_result['status'] = 'success';
            // $bctai_result['msg'] = 'hello';
            // wp_send_json($bctai_result);

            // $file = $_FILES['audio'];
            // wp_send_json($file);


            $mime_types = ['mp3' => 'audio/mpeg','mp4' => 'video/mp4','mpeg' => 'video/mpeg','m4a' => 'audio/m4a','wav' => 'audio/wav','webm' => 'video/webm'];
            //wp_send_json($mime_types);
            
            if ( !wp_verify_nonce( $_POST['nonce'], 'bctai-ajax-nonce' ) ) {
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = BCTAI_NONCE_ERROR;               
                wp_send_json($bctai_result);
            }

            $open_ai = BCTAI_OpenAI::get_instance()->openai();

            if ( !$open_ai ) {                
                $bctai_result['msg'] = 'Missing API Setting';               
                wp_send_json($bctai_result);
                exit;
            }

            $file = $_FILES['audio'];
            //wp_send_json($file);

            $file_name = sanitize_file_name(basename($file['name']));
            $filetype = wp_check_filetype($file_name);

            if(!in_array($filetype['type'], $mime_types)){
                $bctai_result['msg'] = 'We only accept mp3, mp4, mpeg, mpga, m4a, wav, or webm.';
                wp_send_json($bctai_result);
            }

            if($file['size'] > 26214400){
                $bctai_result['msg'] = 'Audio file maximum 25MB';
                wp_send_json($bctai_result);
            }
            $tmp_file = $file['tmp_name'];
            $data_request = array(
                'audio' => array(
                    'filename' => $file_name,
                    'data' => file_get_contents($tmp_file)
                ),
                'model' => 'whisper-1',
                'response_format' => 'json'
            );
            //wp_send_json($data_request);
            
            //$bctai_result['status'] = 'success';
            //$bctai_result['msg'] = $data_request;
            $completion = $open_ai->transcriptions($data_request);
            $completion = json_decode($completion);
            // wp_send_json($completion);

            if($completion && isset($completion->error)){
                $bctai_result['msg'] = $completion->error->message;
                if(empty($bctai_result['msg']) && isset($completion->error->code) && $completion->error->code == 'invalid_api_key'){
                    $bctai_result['msg'] = 'Incorrect API key provided. You can find your API key at https://platform.openai.com/account/api-keys.';
                }
                wp_send_json($bctai_result);
            }
            $text_generated = trim($completion->text);            
            if(empty($text_generated)){
                $bctai_result['msg'] = 'Please speak louder or say more words for accurate recognition.';
                wp_send_json($bctai_result);
            }

            $bctai_result['status'] = 'success';
            $bctai_result['text'] = $text_generated;

            wp_send_json($bctai_result);
        }

    }

    BCTAI_Audio::get_instance();
}
