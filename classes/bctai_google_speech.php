<?php
namespace BCTAI;

if (!defined('ABSPATH'))
    exit;
if (!class_exists('\\BCTAI\\BCTAI_Google_Speech')) {
    class BCTAI_Google_Speech
    {
        public $api_key;
        public $languages = array(
            'en-US' => 'English (United States)',
            'ko-KR' => '한국어 (대한민국)'
        );
        public $url = 'https://texttospeech.googleapis.com/v1/';
        // public $url = 'https://texttospeech.googleapis.com/v1beta1/';


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
        public function create_audio_log_table()
        {
            global $wpdb;
            $bctAudioLogsTable = $wpdb->prefix . 'bctai_audio_logs';
            if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $bctAudioLogsTable)) != $bctAudioLogsTable) {
                $charset_collate = $wpdb->get_charset_collate();
                $sql = "CREATE TABLE " . $bctAudioLogsTable . " (
                `id` mediumint(11) NOT NULL AUTO_INCREMENT,
                `log_session` VARCHAR(255) NOT NULL,
                `created_at` VARCHAR(255) NOT NULL,
                `type` VARCHAR(255) NOT NULL,
                `request_text` VARCHAR(255) NOT NULL,
                `size` VARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
                ) $charset_collate";
                 require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                 $wpdb->query($sql);
            }

            $bct_STT_logs = $wpdb->prefix . 'bctai_STT_logs';
            if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $bct_STT_logs)) != $bct_STT_logs) {
                $charset_collate = $wpdb->get_charset_collate();
                $sql = "CREATE TABLE " . $bct_STT_logs . " (
                `id` mediumint(11) NOT NULL AUTO_INCREMENT,
                `log_session` VARCHAR(255) NOT NULL,
                `created_at` VARCHAR(255) NOT NULL,
                `type` VARCHAR(255) NOT NULL,
                `request_text` VARCHAR(255) NOT NULL,
                `size` VARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
                ) $charset_collate";
                 require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                 $wpdb->query($sql);
            }



        }



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
            add_action('wp_ajax_bctai_sync_google_voices', [$this, 'bctai_sync_google_voices']);
            add_action('wp_ajax_bctai_google_voices', [$this, 'voices']);
            add_action('wp_ajax_bctai_google_speech', [$this, 'speech']);
            add_action('wp_ajax_nopriv_bctai_google_speech', [$this, 'speech']);

            //구글 메일 추가
            add_action('wp_ajax_nopriv_mail_send',[$this,'mail_send']);
            add_action('wp_ajax_mail_send',[$this,'mail_send']);

            $this->create_audio_log_table();
        }

        public function mail_send(){
            //wp_send_json(true);
            
            global $wpdb;
            $_name = $_POST['name'];
            $_email= $_POST['email'];
            $_Phonenumber= $_POST['phonenumber'];
            $_Contents= $_POST['contents'];


            $user_id = get_current_user_id();
            if($user_id){
                $chat_history_qurry = $wpdb->prepare("
                SELECT * FROM ".$wpdb->prefix."bctai_chatlogs 
                WHERE created_at >= UNIX_TIMESTAMP(NOW() - INTERVAL 7 DAY)
                AND SOURCE = 'widget'
                AND user_id = '%d'
                ORDER BY created_at",$user_id);
            }else{
                $cookie_value =md5($_COOKIE['bctai_chat_client_id']);
                //echo $cookie_value;
                $chat_history_qurry = $wpdb->prepare("
                SELECT * FROM ".$wpdb->prefix."bctai_chatlogs 
                WHERE created_at >= UNIX_TIMESTAMP(NOW() - INTERVAL 7 DAY)
                AND SOURCE = 'widget'
                AND log_session = '%s'
                ORDER BY created_at",$cookie_value);
            }
            $bctai_chat_historys = $wpdb->get_results($chat_history_qurry);

            foreach( $bctai_chat_historys as $bctai_chat_history) {
                $all_messages = json_decode($bctai_chat_history->data, true);
            }

            $String_message = '';
            $String_message .='Name:' . $_name . "\r\n";
            $String_message .='Email:' . $_email . "\r\n";
            $String_message .='Phone:' . $_Phonenumber . "\r\n";
            $String_message .='Contents:' . $_Contents . "\r\n" . "\r\n";
            foreach($all_messages as $item){
                if($item['type']=='user'){
                    $String_message .= "User \r\n" . $item['message'] . "\r\n";
                }else{
                    $String_message .= "AI \r\n" . $item['message'] . "\r\n";
                }
                
            }

            
            
            $to = get_option('admin_email');
            $subject = $_email;
            $body = $String_message;
            $headers = array(
                'MIME-Version: 1.0; Content-Type: text/html; charset=UTF-8;',
            );
            $result = wp_mail($to, $subject, $body, $headers);
            if ( $result ) {
                wp_send_json(true);
            } else {
                wp_send_json(false);
            }

        }
        

        


        public function devices()
        {
            return array(
                '' => 'Default',
                'handset-class-device' => 'Smartphone',
                'headphone-class-device' => 'Headphones or earbuds',
                'small-bluetooth-speaker-class-device' => 'Small home speaker',
            );
        }

        public function bctai_sync_google_voices()
        {
            $result = array('status' => 'error2222', 'msg' => 'Missing Google API Key');

            if (!wp_verify_nonce(sanitize_text_field($_REQUEST['nonce']), 'bctai_sync_google_voices')) {
                $result['msg'] = BCTAI_NONCE_ERROR;
                wp_send_json($result);
                exit;
            }

            //$apiKey = get_option('bctai_google_api_key', '');
            $apiKey = $_REQUEST['apikey'];

            if (!empty($apiKey)) {
                $google_voices = array();
                foreach ($this->languages as $key => $language) {
                    $response = wp_remote_get($this->url . 'voices?languageCode=' . $key . '&key=' . $apiKey);
                    if (is_wp_error($response)) {
                        $result['status'] = 'error';
                        $result['msg'] = $response->get_error_message();
                        break;
                    } else {
                        $body = wp_remote_retrieve_body($response);
                        $body = json_decode($body, true);
                        if (isset($body['error'])) {
                            $result['status'] = 'error2222';
                            $result['msg'] = $body['error']['message'];
                            break;
                        } else {
                            $result['status'] = 'success';
                            $google_voices[$key] = $body['voices'];
                        }
                    }
                }
                $result['voices'] = $google_voices;
                update_option('bctai_google_voices', $google_voices);
            }
            wp_send_json($result);
        }

        public function voices()
        {
            $result = array('status' => 'error', 'msg' => 'Missing Google API Key');
            if (!wp_verify_nonce(sanitize_text_field($_REQUEST['nonce']), 'bctai-ajax-action')) {
                $result['msg'] = BCTAI_NONCE_ERROR;
                wp_send_json($result);
                exit;
            }
            $apiKey = get_option('bctai_google_api_key', '');
            $language = isset($_REQUEST['language']) && !empty($_REQUEST['language']) ? sanitize_text_field($_REQUEST['language']) : 'en-US';
            if (!empty($apiKey)) {
                $response = wp_remote_get($this->url . 'voices?languageCode=' . $language . '&key=' . $apiKey);
                if (is_wp_error($response)) {
                    $result['msg'] = $response->get_error_message();
                } else {
                    $body = wp_remote_retrieve_body($response);
                    $body = json_decode($body, true);
                    if (isset($body['error'])) {
                        $result['msg'] = $body['error']['message'];
                    } else {
                        $result['status'] = 'success';
                        $result['voices'] = $body['voices'];
                    }
                }
            }
            wp_send_json($result);
        }

        public function speech()
        {
            global $wpdb;
            // $result['msg'] = $_REQUEST['type'];
            // wp_send_json($result);
            $result = array('status' => 'error', 'msg' => 'Missing parameters');
            $language = 'en-US';
            $voiceName = 'en-US-Studio-M';
            $device = '';
            $speed = 1;
            $pitch = 0;
            $apiKey = get_option('bctai_google_api_key', '');
            if (!wp_verify_nonce(($_REQUEST['nonce']), 'bctai-chatbox')) {
                $result['msg'] = BCTAI_NONCE_ERROR;
                wp_send_json($result);
            }
            if (empty($apiKey)) {
                $result['msg'] = 'Missing Google API Key';
                wp_send_json($result);
            }
            if (isset($_REQUEST['language']) && !empty($_REQUEST['language'])) {
                $language = sanitize_text_field($_REQUEST['language']);
                if (!isset($this->languages[$language])) {
                    $language = 'en-US';
                }
            }
            if (isset($_REQUEST['name']) && !empty($_REQUEST['name'])) {
                $voiceName = sanitize_text_field($_REQUEST['name']);
            }
            if (isset($_REQUEST['device']) && !empty($_REQUEST['device'])) {
                $device = sanitize_text_field($_REQUEST['device']);
            }
            if (isset($_REQUEST['speed']) && !empty($_REQUEST['speed'])) {
                $speed = sanitize_text_field($_REQUEST['speed']);
            }
            if (isset($_REQUEST['pitch']) && !empty($_REQUEST['pitch'])) {
                $pitch = sanitize_text_field($_REQUEST['pitch']);
            }
            if (isset($_REQUEST['text']) && !empty($_REQUEST['text'])) {
                $text = sanitize_text_field($_REQUEST['text']);
                $text = str_replace("\\", '', $text);
                $params = array(
                    'audioConfig' => array(
                        'audioEncoding' => 'LINEAR16',
                        'pitch' => $pitch,
                        'speakingRate' => $speed,
                    ),
                    'input' => array(
                        'text' => $text
                    ),
                    'voice' => array(
                        'languageCode' => $language,
                        'name' => $voiceName
                    )
                );
                if (!empty($device)) {
                    $params['audioConfig']['effectsProfileId'] = array($device);
                }
                
                $response = wp_remote_post($this->url . 'text:synthesize?fields=audioContent&key=' . $apiKey, array(
                    'headers' => array(
                        'Content-Type' => 'application/json'
                    ),
                    'body' => wp_json_encode($params),
                    'timeout' => 1000
                ));
                if (is_wp_error($response)) {
                    $result['msg'] = $response->get_error_message();
                } else {
                    $body = wp_remote_retrieve_body($response);
                    $body = json_decode($body, true);
                    if (isset($body['error'])) {
                        $result['msg'] = $body['error']['message'];
                    } elseif (isset($body['audioContent']) && !empty($body['audioContent'])) {
                        $result['audio'] = $body['audioContent'];
                        $result['status'] = 'success';

                        $decodedAudio = base64_decode($result['audio']);
                        $audioSizeBytes = strlen($decodedAudio);
                        $result['size'] = $audioSizeBytes / 1024;
                        $bctai_client_id = $this->bctai_get_cookie_id();
                        $bctai_current_context_id = isset($_REQUEST['post_id']) && !empty($_REQUEST['post_id']) ? sanitize_text_field($_REQUEST['post_id']) : '';
                        $bctai_unique_chat = md5($bctai_client_id . '-' . $bctai_current_context_id);
                        $decoded_audio = base64_decode($result['audio']);

                        $wpdb->insert(
                            $wpdb->prefix . 'bctai_audio_logs',
                            array(
                                'log_session' => $bctai_unique_chat,
                                'created_at' => gmdate('Y-m-d'),
                                'type' => $_REQUEST['type'],
                                'request_text' => $_REQUEST['text'],
                                //'source' => $decoded_audio,
                                'size' => $result['size']

                            )
                        );
                        if ($wpdb->last_error) {
                            $result['msg'] = 'insert_error : '. $wpdb->last_error;
                            wp_send_json($result['msg']);
                            
                        }
                        // $bctai_audio_log_id = $wpdb->insert_id;

                    } else {
                        $result['msg'] = __('Google does not return audio','bctai');
                    }
                }
            }
            wp_send_json($result);
        }

    }
    BCTAI_Google_Speech::get_instance();
}

