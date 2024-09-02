<?php
namespace BCTAI;
if ( ! defined( 'ABSPATH' ) ) exit;
class BCTAI_OpenRouterUrl
{
    const ORIGIN = 'https://openrouter.ai/api/';
    const API_VERSION = 'v1';
    const OPEN_AI_URL = self::ORIGIN . "/" . self::API_VERSION;

    /**
     * @deprecated
     * @param string $engine
     * @return string
     */
    public static function completionURL(string $engine): string
    {
        return self::OPEN_AI_URL . "/engines/$engine/completions";
    }

    /**
     * @return string
     */
    public static function completionsURL(): string
    {
        return self::OPEN_AI_URL . "/completions";
    }

    /**
     * @return string
     */
    public static function speechUrl(): string {
        return self::OPEN_AI_URL . "/audio/speech";
    }

    /**
     *
     * @return string
     */
    public static function editsUrl(): string
    {
        return self::OPEN_AI_URL . "/edits";
    }

    /**
     * @param string $engine
     * @return string
     */
    public static function searchURL(string $engine): string
    {
        return self::OPEN_AI_URL . "/engines/$engine/search";
    }

    /**
     * @param
     * @return string
     */
    public static function enginesUrl(): string
    {
        return self::OPEN_AI_URL . "/engines";
    }

    /**
     * @param string $engine
     * @return string
     */
    public static function engineUrl(string $engine): string
    {
        return self::OPEN_AI_URL . "/engines/$engine";
    }

    /**
     * @param
     * @return string
     */
    public static function assistantsUrl(): string
    {
        return self::OPEN_AI_URL . "/assistants";
    }

    /**
     * @param
     * @return string
     */
    public static function classificationsUrl(): string
    {
        return self::OPEN_AI_URL . "/classifications";
    }

    /**
     * @param
     * @return string
     */
    public static function moderationUrl(): string
    {
        return self::OPEN_AI_URL . "/moderations";
    }

    /**
     * @param
     * @return string
     */
    public static function filesUrl(): string
    {
        return self::OPEN_AI_URL . "/files";
    }

    /**
     * @param
     * @return string
     */
    public static function fineTuneUrl(): string
    {
        return self::OPEN_AI_URL . "/fine_tuning/jobs";
    }

    /**
     * @param
     * @return string
     */
    public static function chatUrl(): string
    {
        return self::OPEN_AI_URL . "/chat/completions";
    }

    /**
     * @param
     * @return string
     */
    public static function fineTuneModel(): string
    {
        return self::OPEN_AI_URL . "/models";
    }

    /**
     * @param
     * @return string
     */
    public static function answersUrl(): string
    {
        return self::OPEN_AI_URL . "/answers";
    }

    /**
     * @param
     * @return string
     */
    public static function imageUrl(): string
    {
        return self::OPEN_AI_URL . "/images";
    }

    /**
     * @param
     * @return string
     */
    public static function transcriptionsUrl(): string
    {
        return self::OPEN_AI_URL . "/audio/transcriptions";
    }

    /**
     * @param
     * @return string
     */
    public static function translationsUrl(): string
    {
        return self::OPEN_AI_URL . "/audio/translations";
    }

    /**
     * @param
     * @return string
     */
    public static function embeddings(): string
    {
        return self::OPEN_AI_URL . "/embeddings";
    }
}

if (!class_exists('\\BCTAI\\BCTAI_OpenRouter')){
    class BCTAI_OpenRouter
    {
        private  static $instance = null ;
        private $engine = "davinci";
        private $model = "text-davinci-003";

        public $temperature;
        public $max_tokens;
        public $top_p;
        public $frequency_penalty;
        public $presence_penalty;
        public $best_of;
        public $img_size;
        public $api_key;
        public $wpai_language;
        public $wpai_add_img;
        public $wpai_add_intro;
        public $wpai_add_conclusion;
        public $wpai_add_tagline;
        public $wpai_add_faq;
        public $wpai_add_keywords_bold;
        public $wpai_number_of_heading;
        public $wpai_modify_headings;
        public $wpai_heading_tag;
        public $wpai_writing_style;
        public $wpai_writing_tone;
        public $wpai_target_url;
        public $wpai_target_url_cta;
        public $wpai_cta_pos;


        private $headers;
        public $response;

        private $timeout = 200;
        private $stream_method;

        public static function get_instance()
        {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        private function retrieveApiKey()
        {
            $api_key = get_option('bctai_OpenRouter_APIkey');
            return $api_key ?: '';
        }

        public function openai()
        {

            $bctai_OpenRouter_APIkey = get_option('bctai_OpenRouter_APIkey');
            global $wpdb;
            $bctaiTable = $wpdb->prefix . 'bctai';
            $sql = $wpdb->prepare( 'SELECT * FROM ' . $bctaiTable . ' where name=%s','bctai_settings' );
            $bctai_settings = $wpdb->get_row( $sql, ARRAY_A );          
            //var_dump($bctai_settings);
            if($bctai_settings && isset($bctai_settings['api_key']) && !empty($bctai_settings['api_key'])) {           
                add_action('http_api_curl', array($this, 'filterCurlForStream'));                
                $this->headers = [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$bctai_OpenRouter_APIkey,
                ];
                unset($bctai_settings['ID']);
                unset($bctai_settings['name']);
                unset($bctai_settings['added_date']);
                unset($bctai_settings['modified_date']);
                foreach($bctai_settings as $key=>$bctai_setting) {
                    $this->$key = $bctai_setting;
                }
                return $this;
            }
            else return false;
        }

        public function filterCurlForStream($handle)
        {
            if ($this->stream_method !== null){
                curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($handle, CURLOPT_WRITEFUNCTION, function ($curl_info, $data) {
                    return call_user_func($this->stream_method, $this, $data);
                });
            }
        }

        /**
         * Create speech from text.
         * 
         * @param array $opts Options for speech generation.
         * @return bool|string
         */
        public function createSpeech(array $opts) {
            $url = BCTAI_OpenRouterUrl::speechUrl();

            return $this->sendRequest($url, 'POST', $opts);
        }

        public function listModels()
        {
            $url = BCTAI_OpenRouterUrl::fineTuneModel();

            return $this->sendRequest($url, 'GET');
        }

        public function retrieveModel($model)
        {
            $model = "/$model";
            $url = BCTAI_OpenRouterUrl::fineTuneModel() . $model;

            return $this->sendRequest($url, 'GET');
        }

        public function setResponse($content="")
        {
            $this->response = $content;
        }

        public function complete($opts)
        {
            $engine = $opts['engine'] ?? $this->engine;
            $url = BCTAI_OpenRouterUrl::completionURL($engine);
            unset($opts['engine']);

            return $this->sendRequest($url, 'POST', $opts);
        }

        public function completion($opts, $stream = null)
        {
            if ($stream != null && array_key_exists('stream', $opts)) {
                if (! $opts['stream']) {
                    throw new \Exception(
                        'Please provide a stream function.'
                    );
                }
                $this->stream_method = $stream;
            }

            $opts['model'] = $opts['model'] ?? $this->model;
            $url = BCTAI_OpenRouterUrl::completionsURL();

            return $this->sendRequest($url, 'POST', $opts);
        }

        public function chat($opts, $stream = null)
        {
            if ($stream != null && array_key_exists('stream', $opts)) {
                if (! $opts['stream']) {
                    throw new \Exception(
                        'Please provide a stream function.'
                    );
                }
                $this->stream_method = $stream;
            }

            $opts['model'] = $opts['model'] ?? $this->model;

            $url = BCTAI_OpenRouterUrl::chatUrl();
            return $this->sendRequest($url, 'POST', $opts);
        }

        public function transcriptions($opts)
        {
            $url = BCTAI_OpenRouterUrl::transcriptionsUrl();
            return $this->sendRequest($url, 'POST', $opts);
        }

        public function translations($opts)
        {
            $url = BCTAI_OpenRouterUrl::translationsUrl();
            return $this->sendRequest($url, 'POST', $opts);
        }

        public function createEdit($opts)
        {
            $url = BCTAI_OpenRouterUrl::editsUrl();

            return $this->sendRequest($url, 'POST', $opts);
        }

        public function image($opts)
        {
            $url = BCTAI_OpenRouterUrl::imageUrl() . "/generations";

            return $this->sendRequest($url, 'POST', $opts);
        }

        public function imageEdit($opts)
        {
            $url = BCTAI_OpenRouterUrl::imageUrl() . "/edits";

            return $this->sendRequest($url, 'POST', $opts);
        }

        public function createImageVariation($opts)
        {
            $url = BCTAI_OpenRouterUrl::imageUrl() . "/variations";

            return $this->sendRequest($url, 'POST', $opts);
        }

        public function search($opts)
        {
            $engine = $opts['engine'] ?? $this->engine;
            $url = BCTAI_OpenRouterUrl::searchURL($engine);
            unset($opts['engine']);

            return $this->sendRequest($url, 'POST', $opts);
        }

        public function answer($opts)
        {
            $url = BCTAI_OpenRouterUrl::answersUrl();
            return $this->sendRequest($url, 'POST', $opts);
        }

        public function classification($opts)
        {
            $url = BCTAI_OpenRouterUrl::classificationsUrl();

            return $this->sendRequest($url, 'POST', $opts);
        }

        public function moderation($opts)
        {
            $url = BCTAI_OpenRouterUrl::moderationUrl();

            return $this->sendRequest($url, 'POST', $opts);
        }

        public function uploadFile($opts)
        {
            $url = BCTAI_OpenRouterUrl::filesUrl();

            return $this->sendRequest($url, 'POST', $opts);
        }

        public function retrieveFile($file_id)
        {
            $file_id = "/$file_id";
            $url = BCTAI_OpenRouterUrl::filesUrl() . $file_id;

            return $this->sendRequest($url, 'GET');
        }

        public function retrieveFileContent($file_id)
        {
            $file_id = "/$file_id/content";
            $url = BCTAI_OpenRouterUrl::filesUrl() . $file_id;

            return $this->sendRequest($url, 'GET');
        }

        public function deleteFile($file_id)
        {
            $file_id = "/$file_id";
            $url = BCTAI_OpenRouterUrl::filesUrl() . $file_id;

            return $this->sendRequest($url, 'DELETE');
        }

        public function createFineTune($opts)
        {
            $url = BCTAI_OpenRouterUrl::fineTuneUrl();

            return $this->sendRequest($url, 'POST', $opts);
        }

        public function listFineTunes()
        {
            $url = BCTAI_OpenRouterUrl::fineTuneUrl();

            return $this->sendRequest($url, 'GET');
        }

        public function retrieveFineTune($fine_tune_id)
        {
            $fine_tune_id = "/$fine_tune_id";
            $url = BCTAI_OpenRouterUrl::fineTuneUrl() . $fine_tune_id;

            return $this->sendRequest($url, 'GET');
        }

        /**
         * @param $fine_tune_id
         * @return bool|string
         */
        public function cancelFineTune($fine_tune_id)
        {
            $fine_tune_id = "/$fine_tune_id/cancel";
            $url = BCTAI_OpenRouterUrl::fineTuneUrl() . $fine_tune_id;

            return $this->sendRequest($url, 'POST');
        }

        /**
         * @param $fine_tune_id
         * @return bool|string
         */
        public function listFineTuneEvents($fine_tune_id)
        {
            $fine_tune_id = "/$fine_tune_id/events";
            $url = BCTAI_OpenRouterUrl::fineTuneUrl() . $fine_tune_id;

            return $this->sendRequest($url, 'GET');
        }

        /**
         * @param $fine_tune_id
         * @return bool|string
         */
        public function deleteFineTune($fine_tune_id)
        {
            $fine_tune_id = "/$fine_tune_id";
            $url = BCTAI_OpenRouterUrl::fineTuneModel() . $fine_tune_id;

            return $this->sendRequest($url, 'DELETE');
        }

        /**
         * @param
         * @return bool|string
         * @deprecated
         */
        public function engines()
        {
            $url = BCTAI_OpenRouterUrl::enginesUrl();

            return $this->sendRequest($url, 'GET');
        }

        /**
         * @param $engine
         * @return bool|string
         * @deprecated
         */
        public function engine($engine)
        {
            $url = BCTAI_OpenRouterUrl::engineUrl($engine);

            return $this->sendRequest($url, 'GET');
        }

        /**
         * @param $opts
         * @return bool|string
         */
        public function embeddings($opts)
        {
            $url = BCTAI_OpenRouterUrl::embeddings();

            return $this->sendRequest($url, 'POST', $opts);
        }

        /**
         * @param int $timeout
         */
        public function setTimeout(int $timeout)
        {
            $this->timeout = $timeout;
        }

        private function setUpHeaders() {
            global $wpdb;
            $wpaicgTable = $wpdb->prefix . 'wpaicg';
            $sql = $wpdb->prepare('SELECT * FROM ' . $wpaicgTable . ' WHERE name = %s', 'wpaicg_settings');
            $wpaicg_settings = $wpdb->get_row($sql, ARRAY_A);
            $api_key = $this->retrieveApiKey();
        
            $this->headers['Authorization'] = 'Bearer ' . $api_key;
            $this->headers['OpenAI-Beta'] = 'assistants=v1';
            $this->headers['X-Title'] = 'test site';
        }

        
        /**
         * @param array $query
         * @return bool|string
         */
        public function listAssistants($query = [])
        {
            // Set up headers
            $this->setUpHeaders();

            $url = BCTAI_OpenRouterUrl::assistantsUrl();

            // Add query parameters to the URL if they exist
            if (count($query) > 0) {
                $url .= '?' . http_build_query($query);
            }

            return $this->sendRequest($url, 'GET');
        }


        public function deleteAssistant($assistant_id) {
            $url = BCTAI_OpenRouterUrl::assistantsUrl() . '/' . $assistant_id;
        
            // Set up headers
            $this->setUpHeaders();
        
            return $this->sendRequest($url, 'DELETE');
        }

        public function createAssistant($assistant_data) {
            $url = BCTAI_OpenRouterUrl::assistantsUrl();
        
            // Set up headers
            $this->setUpHeaders();
        
            return $this->sendRequest($url, 'POST', $assistant_data);
        }

        public function modifyAssistant($assistant_id, $assistant_data) {
            $url = BCTAI_OpenRouterUrl::assistantsUrl() . '/' . $assistant_id;
        
            // Set up headers
            $this->setUpHeaders();
        
            return $this->sendRequest($url, 'POST', $assistant_data);
        }
        
        public function create_body_for_file($file, $boundary)
        {
            $filePurpose = isset($file['purpose']) && $file['purpose'] === 'assistants' ? 'assistants' : 'fine-tune';
            $fields = array(
                'purpose' => $filePurpose,
                'file' => $file['filename']
            );

            $body = '';
            foreach ($fields as $name => $value) {
                $body .= "--$boundary\r\n";
                $body .= "Content-Disposition: form-data; name=\"$name\"";
                if ($name == 'file') {
                    $body .= "; filename=\"{$value}\"\r\n";
                    $body .= "Content-Type: application/json\r\n\r\n";
                    $body .= $file['data'] . "\r\n";
                } else {
                    $body .= "\r\n\r\n$value\r\n";
                }
            }
            $body .= "--$boundary--\r\n";
            return $body;
        }

        public function create_body_for_audio($file, $boundary, $fields)
        {
            $fields['file'] = $file['filename'];
            unset($fields['audio']);
            $body = '';
            foreach ($fields as $name => $value) {
                $body .= "--$boundary\r\n";
                $body .= "Content-Disposition: form-data; name=\"$name\"";
                if ($name == 'file') {
                    $body .= "; filename=\"{$value}\"\r\n";
                    $body .= "Content-Type: application/json\r\n\r\n";
                    $body .= $file['data'] . "\r\n";
                } else {
                    $body .= "\r\n\r\n$value\r\n";
                }
            }
            $body .= "--$boundary--\r\n";
            return $body;
        }

        public function listFiles()
        {
            $url = BCTAI_OpenRouterUrl::filesUrl();

            return $this->sendRequest($url, 'GET');
        }

        /**
         * @param string $url
         * @param string $method
         * @param array $opts
         * @return bool|string
         */
        private function sendRequest(string $url, string $method, array $opts = [])
        {
            // return 'dkdkdkdkdkddkdk';
            $post_fields = json_encode($opts);
            // Check if the request is for text-to-speech
            if (array_key_exists('tts', $opts)) {
                // Retrieve API key from the database

                global $wpdb;
                $wpaicgTable = $wpdb->prefix . 'wpaicg';
                $sql = $wpdb->prepare('SELECT * FROM ' . $wpaicgTable . ' WHERE name = %s', 'wpaicg_settings');
                $wpaicg_settings = $wpdb->get_row($sql, ARRAY_A);
                $api_key = $this->retrieveApiKey();

                // Add the Authorization header with the API key
                $this->headers['Authorization'] = 'Bearer ' . $api_key;
            }

            if (array_key_exists('file', $opts)) {
                $boundary = wp_generate_password(24, false);
                $this->headers['Content-Type'] = 'multipart/form-data; boundary='.$boundary;
                $post_fields = $this->create_body_for_file($opts['file'], $boundary);
            }
            elseif (isset($opts['purpose']) && $opts['purpose'] === 'assistants') {
                $boundary = wp_generate_password(24, false);
                $this->headers['Content-Type'] = 'multipart/form-data; boundary='.$boundary;
                global $wpdb;
                $wpaicgTable = $wpdb->prefix . 'wpaicg';
                $sql = $wpdb->prepare('SELECT * FROM ' . $wpaicgTable . ' WHERE name = %s', 'wpaicg_settings');
                $wpaicg_settings = $wpdb->get_row($sql, ARRAY_A);
                $api_key = $this->retrieveApiKey();

                // Add the Authorization header with the API key
                $this->headers['Authorization'] = 'Bearer ' . $api_key;
                $post_fields = $this->create_body_for_file(['filename' => $opts['filename'], 'data' => $opts['data'], 'purpose' => $opts['purpose']], $boundary);
            }
            elseif (array_key_exists('audio', $opts)) {
                $boundary = wp_generate_password(24, false);
                $this->headers['Content-Type'] = 'multipart/form-data; boundary='.$boundary;
                $post_fields = $this->create_body_for_audio($opts['audio'], $boundary, $opts);
            } else {
                $this->headers['Content-Type'] = 'application/json';
            }
            $stream = false;
            if (array_key_exists('stream', $opts) && $opts['stream']) {
                $stream = true;
            }
            $http_referer = get_site_url();
            $x_title = get_bloginfo('name');
            $this->headers['HTTP-Referer'] = $http_referer;
            $this->headers['X-Title'] = $x_title;
            $request_options = array(
                'timeout' => $this->timeout,
                'headers' => $this->headers,
                'method' => $method,
                'body' => $post_fields,
                'stream' => $stream
            );
            if($post_fields == '[]'){
                unset($request_options['body']);
            }

            $response = wp_remote_request($url,$request_options);
            if(is_wp_error($response)){
                return json_encode(array('error' => array('message' => $response->get_error_message())));
            }
            else{
                if ($stream){
                    return $this->response;
                }
                else{
                    return wp_remote_retrieve_body($response);
                }
            }
        }
    }
}
