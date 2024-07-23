<?php
namespace BCTAI;

if (!defined('ABSPATH')) {
    exit;
}

class BCTAIAZURE_Url
{

    private static $azure_api_url;
    private static $deployment_name;
    private static $api_version;
    private static $image_api_version;
    private static $deployment_name_embedding;
    private static $api_version_embedding;
    private static $finetune_version;

    public function __construct()
    {
        // Fetching values from wp_options table
        self::$azure_api_url = get_option('bctai_azure_endpoint', ''); // Default to an empty string if not set
        self::$deployment_name = get_option('bctai_azure_deployment', ''); // Default to an empty string if not set
        self::$deployment_name_embedding = get_option('bctai_azure_embeddings', "text-embedding-ada-002"); // Default to "text-embedding-ada-002" if not set

        // Static values
        self::$api_version_embedding = 'api-version=2023-05-15';
        self::$api_version = 'api-version=2023-03-15-preview';
        self::$image_api_version = 'api-version=2023-06-01-preview';
        self::$finetune_version = 'api-version=2023-05-15';
    }


    /**
     *
     * @return string
     */
    public static function editsUrl(): string
    {
        return self::$azure_api_url . "/edits";
    }

    /**
     * @param string $engine
     * @return string
     */
    public static function searchURL(string $engine): string
    {
        return self::$azure_api_url . "/engines/$engine/search";
    }

    /**
     * @param
     * @return string
     */
    public static function enginesUrl(): string
    {
        return self::$azure_api_url . "/engines";
    }

    /**
     * @param string $engine
     * @return string
     */
    public static function engineUrl(string $engine): string
    {
        return self::$azure_api_url . "/engines/$engine";
    }

    /**
     * @param
     * @return string
     */
    public static function classificationsUrl(): string
    {
        return self::$azure_api_url . "/classifications";
    }

    /**
     * @param
     * @return string
     */
    public static function moderationUrl(): string
    {
        return self::$azure_api_url . "/moderations";
    }

    /**
     * @param
     * @return string
     */
    public static function filesUrl(): string
    {
        return self::$azure_api_url . "/files" . "?" . self::$finetune_version;
    }

    /**
     * @param
     * @return string
     */
    public static function chatUrl(): string
    {
        return self::$azure_api_url . "openai/deployments/" . self::$deployment_name . "/chat/completions?" . self::$api_version;
    }

    /**
     * @param
     * @return string
     */
    public static function answersUrl(): string
    {
        return self::$azure_api_url . "/answers";
    }

    /**
     * @param
     * @return string
     */
    public static function imageUrl(): string
    {
        return self::$azure_api_url . "openai/images/generations:submit?" . self::$image_api_version;
    }

    /**
     * @param
     * @return string
     */
    public static function transcriptionsUrl(): string
    {
        return self::$azure_api_url . "/audio/transcriptions";
    }

    /**
     * @param
     * @return string
     */
    public static function transaltionsUrl(): string
    {
        return self::$azure_api_url . "/audio/translations";
    }

    /**
     * @param
     * @return string
     */
    public static function embeddings(): string
    {
        return self::$azure_api_url . "openai/deployments/" . self::$deployment_name_embedding . "/embeddings?" . self::$api_version_embedding;
    }

}

if (!class_exists('\\BCTAI\\BCTAI_AzureAI')) {
    class BCTAI_AzureAI
    {
        private static $instance = null;
        private $engine = "davinci";
        private $model = "text-davinci-003";
        private $headers;
        public $response;

        private $timeout = 200;
        private $stream_method;

        public static function get_instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
                $AzureUrlCheckObj = new BCTAIAZURE_Url();
            }
            return self::$instance;
        }

        public function azureai()
        {
            // Fetch the Azure API key from wp_options table
            $azure_api_key = get_option('bctai_azure_api_key', ''); // Default to an empty string if not set

            if (!empty($azure_api_key)) {
                add_action('http_api_curl', array($this, 'filterCurlForStream'));
                $this->headers = [
                    'Content-Type' => 'application/json',
                    'api-key' => $azure_api_key,
                ];

                global $wpdb;
                $bctaiTable = $wpdb->prefix . 'bctai';
                $sql = $wpdb->prepare('SELECT * FROM ' . $bctaiTable . ' where name=%s', 'bctai_settings');
                $bctai_settings = $wpdb->get_row($sql, ARRAY_A);

                if ($bctai_settings) {
                    unset($bctai_settings['ID']);
                    unset($bctai_settings['name']);
                    unset($bctai_settings['added_date']);
                    unset($bctai_settings['modified_date']);

                    foreach ($bctai_settings as $key => $bctai_setting) {
                        $this->$key = $bctai_setting;
                    }
                }

                return $this;
            } else {
                return false;
            }
        }


        public function filterCurlForStream($handle)
        {
            if ($this->stream_method !== null) {
                curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($handle, CURLOPT_WRITEFUNCTION, function ($curl_info, $data) {
                    return call_user_func($this->stream_method, $this, $data);
                });
            }
        }


        public function setResponse($content = "")
        {
            $this->response = $content;
        }


        public function chat($opts, $stream = null)
        {
            if ($stream != null && array_key_exists('stream', $opts)) {
                if (!$opts['stream']) {
                    throw new \Exception(
                        'Please provide a stream function.'
                    );
                }
                $this->stream_method = $stream;
            }

            $opts['model'] = $opts['model'] ?? $this->model;

            $url = BCTAIAZURE_Url::chatUrl();
            return $this->sendRequest($url, 'POST', $opts);
        }


        public function transcriptions($opts)
        {
            $url = BCTAIAZURE_Url::transcriptionsUrl();
            return $this->sendRequest($url, 'POST', $opts);
        }


        public function translations($opts)
        {
            $url = BCTAIAZURE_Url::translationsUrl();
            return $this->sendRequest($url, 'POST', $opts);
        }


        public function createEdit($opts)
        {
            $url = BCTAIAZURE_Url::editsUrl();
            return $this->sendRequest($url, 'POST', $opts);
        }


        public function image($opts)
        {
            $url = BCTAIAZURE_Url::imageUrl();
            return $this->sendRequest($url, 'POST', $opts, true);
        }


        public function imageEdit($opts)
        {
            $url = BCTAIAZURE_Url::imageUrl() . "/edits";
            return $this->sendRequest($url, 'POST', $opts);
        }


        public function createImageVariation($opts)
        {
            $url = BCTAIAZURE_Url::imageUrl() . "/variations";
            return $this->sendRequest($url, 'POST', $opts);
        }


        public function search($opts)
        {
            $engine = $opts['engine'] ?? $this->engine;
            $url = BCTAIAZURE_Url::searchURL($engine);
            unset($opts['engine']);

            return $this->sendRequest($url, 'POST', $opts);
        }


        public function answer($opts)
        {
            $url = BCTAIAZURE_Url::answersUrl();
            return $this->sendRequest($url, 'POST', $opts);
        }


        public function classification($opts)
        {
            $url = BCTAIAZURE_Url::classificationsUrl();
            return $this->sendRequest($url, 'POST', $opts);
        }


        public function moderation($opts)
        {
            $url = BCTAIAZURE_Url::moderationUrl();
            return $this->sendRequest($url, 'POST', $opts);
        }


        public function uploadFile($opts)
        {
            $url = BCTAIAZURE_Url::filesUrl();
            return $this->sendRequest($url, 'POST', $opts);
        }


        public function retrieveFile($file_id)
        {
            $file_id = "/$file_id";
            $url = BCTAIAZURE_Url::filesUrl() . $file_id;

            return $this->sendRequest($url, 'GET');
        }


        public function retrieveFileContent($file_id)
        {
            $file_id = "/$file_id/content";
            $url = BCTAIAZURE_Url::filesUrl() . $file_id;

            return $this->sendRequest($url, 'GET');
        }


        public function deleteFile($file_id)
        {
            $file_id = "/$file_id";
            $url = BCTAIAZURE_Url::filesUrl() . $file_id;

            return $this->sendRequest($url, 'DELETE');
        }


        /**
         * @param
         * @return bool|string
         * @deprecated
         */
        public function engines()
        {
            $url = BCTAIAZURE_Url::enginesUrl();

            return $this->sendRequest($url, 'GET');
        }


        /**
         * @param $engine
         * @return bool|string
         * @deprecated
         */
        public function engine($engine)
        {
            $url = BCTAIAZURE_Url::engineUrl($engine);

            return $this->sendRequest($url, 'GET');
        }


        /**
         * @param $opts
         * @return bool|string
         */
        public function embeddings($opts)
        {
            $url = BCTAIAZURE_Url::embeddings();

            return $this->sendRequest($url, 'POST', $opts);
        }


        /**
         * @param int $timeout
         */
        public function setTimeout(int $timeout)
        {
            $this->timeout = $timeout;
        }


        public function create_body_for_file($file, $boundary)
        {
            $fields = array(
                'purpose' => 'fine-tune',
                'file' => $file['filename'],
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


        public function bctai_azure_images($image_result_url, $get_request_options)
        {
            


        }


        /**
         * @param string $url
         * @param string $method
         * @param array $opts
         * @return bool|string
         */
        private function sendRequest(string $url, string $method, array $opts = [], $isDalle = false)
        {
            $post_fields = json_encode($opts);
            if (array_key_exists('file', $opts)) {
                $boundary = wp_generate_password(24, false);
                $this->headers['Content-Type'] = 'multipart/form-data; boundary=' . $boundary;
                $post_fields = $this->create_body_for_file($opts['file'], $boundary);
            } elseif (array_key_exists('audio', $opts)) {
                $boundary = wp_generate_password(24, false);
                $this->headers['Content-Type'] = 'multipart/form-data; boundary=' . $boundary;
                $post_fields = $this->create_body_for_audio($opts['audio'], $boundary, $opts);
            } else {
                $this->headers['Content-Type'] = 'application/json';
            }
            $stream = false;
            if (array_key_exists('stream', $opts) && $opts['stream']) {
                $stream = true;
            }

            $request_options = array(
                'timeout' => $this->timeout,
                'headers' => $this->headers,
                'method' => $method,
                'body' => $post_fields,
                'stream' => $stream,
            );

            if ($post_fields == '[]') {
                unset($request_options['body']);
            }


            $response = wp_remote_request($url, $request_options);

            $responseData = wp_remote_retrieve_body($response);
            $responseData = json_decode($responseData);

            if (is_wp_error($response)) {
                return json_encode(array('error' => array('message' => $response->get_error_message())));
            } else if (isset($responseData->error) && $responseData->error != "") {
                return json_encode($responseData);
            } else {
                if ($stream) {
                    return $this->response;
                } else {
                    if ($isDalle) {
                        $image_result_url = wp_remote_retrieve_header($response, 'operation-location');
                        $method = "GET";

                        $get_request_options = array(
                            'timeout' => $this->timeout,
                            'headers' => $this->headers,
                            'method' => $method,
                            'stream' => $stream,
                        );

                        $response = $this->bctai_azure_images($image_result_url, $get_request_options);
                        return $response;

                    } else {
                        return wp_remote_retrieve_body($response);
                    }
                }
            }
        }
    }
}


