<?php
namespace BCTAI;

if (!defined('ABSPATH')) {
    exit;
}

if(!class_exists('\\BCTAI\BCTAI_FineTune')){
    class BCTAI_FineTune
    {
        private static $instance = null;
        public $bctai_max_file_size = 10485760;

        public static function get_instance()
        {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        
        public function __construct()
        {            
            add_action('wp_ajax_bctai_finetune_upload', [$this,'bctai_finetune_upload']);
            add_action('wp_ajax_bctai_get_finetune', [$this,'bctai_get_finetune']);
            add_action('wp_ajax_bctai_get_finetune_file', [$this,'bctai_get_finetune_file']);
            add_action('wp_ajax_bctai_create_finetune_modal', [$this,'bctai_create_finetune_modal']);
            add_action('wp_ajax_bctai_create_finetune', [$this,'bctai_create_finetune']);            
            add_action('wp_ajax_bctai_fetch_finetunes', [$this,'bctai_finetunes']);
            add_action('wp_ajax_bctai_fetch_finetune_files', [$this,'bctai_files']);            
            add_action('wp_ajax_bctai_delete_finetune', [$this,'bctai_delete_finetune']);
            add_action('wp_ajax_bctai_delete_finetune_file', [$this,'bctai_delete_finetune_file']);            
            add_action('wp_ajax_bctai_cancel_finetune', [$this,'bctai_cancel_finetune']);
            add_action('wp_ajax_bctai_other_finetune', [$this,'bctai_other_finetune']);            
            add_action('wp_ajax_bctai_data_converter_count',[$this,'bctai_data_converter_count']);
            add_action('wp_ajax_bctai_data_converter',[$this,'bctai_data_converter']);
            add_action('wp_ajax_bctai_upload_convert',[$this,'bctai_upload_convert']);
            add_action('wp_ajax_bctai_data_insert', [$this,'bctai_data_insert']);
            add_action('wp_ajax_bctai_finetune_events', [$this,'bctai_finetune_events']);
            add_action('wp_ajax_bctai_download', [$this,'bctai_download']);           
            //upload_mimes or mime_types
            add_filter('upload_mimes', function ($mime_types){
                $mime_types['jsonl'] = 'application/octet-stream';
                return $mime_types;
            });            
        }

        
        public function bctaiUploadOpenAI($file, $open_ai)
        {
            $model = isset($_POST['model']) && !empty($_POST['model']) ? sanitize_text_field($_POST['model']) : 'gpt-3.5-turbo';
            $name = isset($_POST['custom']) && !empty($_POST['custom']) ? sanitize_title($_POST['custom']) : '';
            //echo '<script>';
            //echo 'console.log("'.$model.'")';
            //echo '</script>';
            //$result = $model;
            //$result = $open_ai;
            $result = $open_ai->uploadFile(array(
                'file'  => array(
                    'data'  => file_get_contents($file),
                    'filename'  => basename($file),
                ),
            ));
            $result = json_decode($result);
            if(isset($result->error)) {
                return trim($result->error->message);
            } else {
                $bctai_file_id = wp_insert_post(array(
                    'post_title' => $result->id,
                    'post_date' => date('Y-m-d H:i:s', $result->created_at),
                    'post_status' => 'publish',
                    'post_type' => 'bctai_file',
                ));
                if(!is_wp_error($bctai_file_id)) {
                    add_post_meta($bctai_file_id, 'bctai_filename', $result->filename);
                    add_post_meta($bctai_file_id, 'bctai_purpose', $result->purpose);
                    add_post_meta($bctai_file_id, 'bctai_model', $model);
                    add_post_meta($bctai_file_id, 'bctai_custom_name', $name);
                    add_post_meta($bctai_file_id, 'bctai_file_size', $result->bytes);
                } else {
                    return $bctai_file_id->get_error_message();
                }
                return 'success';
            }                        
            //return $result;
        }

        public function bctai_finetune_upload()
        {
            $bctai_result = array('status' => 'error', 'msg' => 'Something went wrong');
            if ( ! wp_verify_nonce( $_POST['nonce'], 'bctai-ajax-nonce' ) ) {
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = BCTAI_NONCE_ERROR;
                wp_send_json($bctai_result);
            }
            if(isset($_FILES['file']) && empty($_FILES['file']['error'])){
                $bctai_provider = get_option('bctai_provider', 'OpenAI');
                $open_ai = BCTAI_OpenAI::get_instance()->openai();
                // if provider not openai then assign azure to $open_ai
                if ($bctai_provider != 'OpenAI') {
                    $open_ai = BCTAI_AzureAI::get_instance()->azureai();
                }
                if(!$open_ai){
                    $bctai_result['msg'] = esc_html__('Missing API Setting', 'bctai');
                    wp_send_json($bctai_result);
                }
                $file_name = sanitize_file_name(basename($_FILES['file']['name']));
                $filetype = wp_check_filetype($file_name);
                if($filetype['ext'] !== 'jsonl'){
                    $bctai_result['msg'] = esc_html__('Only files with the jsonl extension are supported', 'bctai');
                    wp_send_json($bctai_result);
                }
                $tmp_file = $_FILES['file']['tmp_name'];
                $c_file = $tmp_file;
                $purpose = isset($_POST['purpose']) && !empty($_POST['purpose']) ? sanitize_text_field($_POST['purpose']) : 'fine-tune';
                $model = isset($_POST['model']) && !empty($_POST['model']) ? sanitize_text_field($_POST['model']) : 'gpt-3.5-turbo';
                $name = isset($_POST['name']) && !empty($_POST['name']) ? sanitize_title($_POST['name']) : '';
                $result = $open_ai->uploadFile(array(
                    'file' => array(
                        'data' => file_get_contents($tmp_file),
                        'filename' => basename($_FILES['file']['name'])
                    ),
                ));                
                $result = json_decode($result);
                if(isset($result->error)){
                    $bctai_result['msg'] = $result->error->message;
                } else {
                    $bctai_file_id = wp_insert_post(array(
                        'post_title' => $result->id,
                        'post_date' => date('Y-m-d H:i:s',get_date_from_gmt(date('Y-m-d H:i:s',$result->created_at), 'U')),
                        'post_status' => 'publish',
                        'post_type' => 'bctai_file',
                    ));                  
                    if(!is_wp_error($bctai_file_id)) {
                        $bctai_result['status'] = 'success';
                        add_post_meta($bctai_file_id, 'bctai_filename',$result->filename);
                        add_post_meta($bctai_file_id, 'bctai_purpose',$result->purpose);
                        add_post_meta($bctai_file_id, 'bctai_model',$model);
                        add_post_meta($bctai_file_id, 'bctai_custom_name',$name);
                        add_post_meta($bctai_file_id, 'bctai_file_size',$result->bytes);
                    } else {
                        $bctai_result['msg'] = $bctai_file_id->get_error_message();
                    }
                }
            } else {
                $bctai_result['msg'] = esc_html__('File upload required', 'bctai');
            }

            wp_send_json($bctai_result);
        }

        public function bctai_create_finetune_modal()
        {
            $bctai_result = array('status' => 'error', 'msg' => 'Something went wrong');
            if ( !wp_verify_nonce( $_POST['nonce'], 'bctai-ajax-nonce' ) ) {
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = BCTAI_NONCE_ERROR;
                wp_send_json($bctai_result);
            }
            $models = $this->bctai_get_models();
            // wp_send_json($models);
            if(is_array($models)){
                $bctai_result['status'] = 'success';
                $bctai_result['data'] = $models;
            }
            else{
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = $models;
            }
            wp_send_json($bctai_result);
        }

        public function bctai_get_models()
        {
            $result = false;
            $bctai_provider = get_option('bctai_provider', 'OpenAI');
            $open_ai = BCTAI_OpenAI::get_instance()->openai();
            // if provider not openai then assing azure to $open_ai
            // if ($bctai_provider != 'OpenAI') {
            //     $open_ai = BCTAI_AzureAI::get_instance()->azureai();
            // }
            if ($open_ai) {
                $result = $open_ai->listModels();
                $json_parse = json_decode($result);
                if(isset($json_parse->error)){
                    return $json_parse->error->message;
                } elseif(isset($json_parse->data) && is_array($json_parse->data) && count($json_parse->data)){
                    $result = array();
                    foreach($json_parse->data  as $item){
                        if($item->owned_by != 'openai' && $item->owned_by != 'system' && $item->owned_by != 'openai-dev' && $item->owned_by != 'openai-internal'){
                            $result[] = $item->id;
                        }
                    }
                    if(count($result)){
                        update_option('bctai_custom_models', $result);
                    }
                }
            }
            return $result;
        }


        public function bctai_download()
        {
            $bctai_provider = get_option('bctai_provider', 'OpenAI');
            $open_ai = BCTAI_OpenAI::get_instance()->openai();
            // if provider not openai then assing azure to $open_ai
            if ($bctai_provider != 'OpenAI') {
                $open_ai = BCTAI_AzureAI::get_instance()->azureai();
            }
            if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
                $id = sanitize_text_field($_REQUEST['id']);
                if (!$open_ai) {
                    echo 'Missing API Setting';
                } else {
                    $result = $open_ai->retrieveFileContent($id);
                    $json_parse = json_decode($result);
                    if(isset($json_parse->error)){
                        echo esc_html($json_parse->error->message);
                    } else {
                        $filename = $id.'.csv';
                        header('Content-Type: application/csv');
                        header('Content-Disposition: attachment; filename="' . $filename . '";');
                        $f = fopen('php://output', 'w');
                        $lines = explode("\n", $result);
                        foreach($lines as $line) {
                            $line = explode(';',$line);
                            fputcsv($f, $line, ';');
                        }
                    }
                }
            }
            die();
        }


        public function bctai_create_finetune()
        {
            $bctai_result = array('status' => 'error', 'msg' => 'Something went wrong12');
            if ( !wp_verify_nonce( $_POST['nonce'], 'bctai-ajax-nonce' ) ) {
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = BCTAI_NONCE_ERROR;
                wp_send_json($bctai_result);
            }            
            if(isset($_POST['id']) && !empty($_POST['id'])){
               $bctai_file = get_post(sanitize_text_field($_POST['id'])) ;
               if($bctai_file){
                    $bctai_provider = get_option('bctai_provider', 'OpenAI');                  
                    $open_ai = BCTAI_OpenAI::get_instance()->openai();
                    // if provider not openai then assing azure to $open_ai
                    if ($bctai_provider != 'OpenAI') {
                        $open_ai = BCTAI_AzureAI::get_instance()->azureai();
                    }
                    if(!$open_ai){
                        $bctai_result['msg'] = esc_html__('Missing API Setting', 'bctai');
                        wp_send_json($bctai_result);
                    }
                    $model = get_post_meta($bctai_file->ID,'bctai_model', true);
                    $suffix = get_post_meta($bctai_file->ID,'bctai_custom_name', true);
                    $dataSend = [
                        'training_file' =>  $bctai_file->post_title,
                    ];
                    if(isset($_POST['model']) && !empty($_POST['model'])){
                        $dataSend['model'] = sanitize_text_field($_POST['model']);
                    } else {
                        $dataSend['model'] = $model;
                        $dataSend['suffix'] = $suffix;
                    }
                    if(empty($dataSend['model'])){
                        $dataSend['model'] = 'gpt-3.5-turbo';
                    }
                    //model -> gpt-3.5-turbo
                    //$bctai_result['msg'] = $dataSend['model'];
                    //wp_send_json($bctai_result);
                    $result = $open_ai->createFineTune($dataSend);
                    //wp_send_json($result);
                    //$bctai_result['msg'] = $result;
                    //wp_send_json($bctai_result);
                    $bctai_result['model'] = $model;
                    $result = json_decode($result);
                    if(!isset($result->status)){
                        $bctai_result['status'] = 'error00000000000000';
                        $bctai_result['msg'] = 'There is a problem.';
                        // $bctai_result['msg'] = $result->error->message;
                    }
                    else {
                        date_default_timezone_set('Asia/Seoul');
                        update_post_meta($bctai_file->ID,'bctai_fine_tune',$result->id);
                        $bctai_file_id = wp_insert_post(array(
                            'post_title'    =>  $result->id,
                            // 'post_date'     =>  date('Y-m-d H:i:s', $result->created_at),
                            'post_date'     =>  date('Y-m-d H:i'),
                            'post_status'   =>  'publish',
                            'post_type'     =>  'bctai_finetune',
                        ));
                        add_post_meta($bctai_file_id, 'bctai_model', $result->model);
                        add_post_meta($bctai_file_id, 'bctai_updated_at', date('Y-m-d H:i:s', $result->updated_at));
                        add_post_meta($bctai_file_id, 'bctai_name', $result->fine_tuned_model);
                        add_post_meta($bctai_file_id, 'bctai_org', $result->organization_id);
                        add_post_meta($bctai_file_id, 'bctai_status', $result->status);
                        $bctai_result['status'] = 'success';
                        $bctai_result['data'] = $result;
                    }
                } else {
                    $bctai_result['msg'] = esc_html__('File not found', 'bctai');
                }
            }
            wp_send_json($bctai_result);
        }

        public function bctai_other_finetune()
        {
            $bctai_result = array('status' => 'error', 'msg' => 'Something went wrong12');
            if ( !wp_verify_nonce( $_POST['nonce'], 'bctai-ajax-nonce' ) ) {
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = BCTAI_NONCE_ERROR;
                wp_send_json($bctai_result);
            }
            if(
                isset($_POST['id'])
                && !empty($_POST['id'])
                && isset($_POST['type'])
                && !empty($_POST['type'])
                && in_array($_POST['type'], array('hyperparameters', 'result_files', 'training_file', 'events'))
            ){
                $bctai_type = sanitize_text_field($_POST['type']);

                $bctai_file = get_post(sanitize_text_field($_POST['id']));
                if($bctai_file){
                    $bctai_provider = get_option('bctai_provider', 'OpenAI');
                    $open_ai = BCTAI_OpenAI::get_instance()->openai();
                    // if provider not openai then assing azure to $open_ai
                    // if ($bctai_provider != 'OpenAI') {
                    //     $open_ai = BCTAI_AzureAI::get_instance()->azureai();
                    // }
                    if(!$open_ai){
                        $bctai_result['msg'] = esc_html__('Missing API Setting', 'bctai');
                        wp_send_json($bctai_result);
                    }

                    if ($bctai_type === 'events') {
                        $result = $open_ai->listFineTuneEvents($bctai_file->post_title);
                        $bctai_type = 'data';
                    } else {
                        $result = $open_ai->retrieveFineTune($bctai_file->post_title);
                    }
                    
                    $result = json_decode($result);
                    //wp_send_json($result);
                    
                    //$bctai_result['msg'] = $bctai_type;
                    //wp_send_json($bctai_result);

                    
                    if(isset($result->error) && empty($result->error)){
                        $bctai_result['msg'] = $result->error->message;
                    } elseif(isset($result->$bctai_type)) {
                        $bctai_data = $result->$bctai_type;

                        if ($bctai_type === 'data') {
                            $bctai_type = 'events';
                        } else if ($bctai_type === 'hyperparameters') {
                            $bctai_type = 'hyperparams';
                        } else if ($bctai_type === 'result_files') {
                            if (isset($bctai_data->error)) {
                                $bctai_result['msg'] = $bctai_data->error->message;
                            } else {
                                $resultFiles = [];
                                if ($bctai_data) {
                                    foreach ($bctai_data as $key => $val) {
                                        $resultData = $open_ai->retrieveFile($val);
                                        $bctai_res = json_decode($resultData);
                                        $resultFiles[] = $bctai_res;
                                    }
                                    $bctai_data = $resultFiles;
                                }
                            }
                        } else if ($bctai_type === 'training_file') {

                            $resultFiles = [];
                            $resultData = $open_ai->retrieveFile($bctai_data);
                            $bctai_res = json_decode($resultData);
                            $resultFiles[] = $bctai_res;
                            $bctai_data = $resultFiles;
                            $bctai_type = 'training_files';
                        }

                        ob_start();
                        include BCTAI_PLUGIN_DIR . 'admin/views/finetune/' . $bctai_type . '.php';
                        $bctai_result['html'] = ob_get_clean();
                        $bctai_result['status'] = 'success';
                    }
                } else{
                    $bctai_result['msg'] = esc_html__('Fine Tune not found', 'bctai');
                }
            }
            wp_send_json($bctai_result);
        }

        public function bctai_finetunes()
        {
            global $wpdb;
            $bctai_result = array('status' => 'error', 'msg' => 'Something went wrong1');
            if ( !wp_verify_nonce( $_POST['nonce'], 'bctai-ajax-nonce' ) ) {
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = BCTAI_NONCE_ERROR;
                wp_send_json($bctai_result);
            }
            $bctai_provider = get_option('bctai_provider', 'OpenAI');
            $open_ai = BCTAI_OpenAI::get_instance()->openai();
            // if provider not openai then assing azure to $open_ai
            if ($bctai_provider != 'OpenAI') {
                $open_ai = BCTAI_AzureAI::get_instance()->azureai();
            }
            if(!$open_ai){
                $bctai_result['msg'] = esc_html__('Missing API Setting','bctai');
                wp_send_json($bctai_result);
            }
            $result = $open_ai->listFineTunes();
            //$bctai_result['msg'] = $result;
            $result = json_decode($result);
            if(isset($result->error)) {
                $bctai_result['msg'] = $result->error->message;
            } else {
                if(isset($result->data) && is_array($result->data) && count($result->data)) {
                    $bctai_result['status'] = 'success';
                    $bctaiExist = array();
                    $finetune_models = array();
                    foreach($result->data as $item) {
                        $bctaiExist[] = $item->id;
                        $bctai_check = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->posts . " WHERE post_type='bctai_finetune' AND post_title=%s", $item->id));
                        if(!$bctai_check) {
                            $bctai_file_id = wp_insert_post(array(
                                'post_title' => $item->id,
                                'post_date' => date('Y-m-d H:i:s', $item->created_at),
                                'post_status' => 'publish',
                                'post_type' => 'bctai_finetune',
                            ));
                            if (!is_wp_error($bctai_file_id)) {
                                add_post_meta($bctai_file_id, 'bctai_model', $item->model);
                                add_post_meta($bctai_file_id, 'bctai_updated_at', date('Y-m-d H:i:s', $item->updated_at));
                                add_post_meta($bctai_file_id, 'bctai_name', $item->fine_tuned_model);
                                add_post_meta($bctai_file_id, 'bctai_org', $item->organization_id);
                                add_post_meta($bctai_file_id, 'bctai_status', $item->status);
                                add_post_meta($bctai_file_id, 'bctai_fine_tune', $item->training_files->id);
                            } else {
                                $bctai_result['status'] = 'error';
                                $bctai_result['msg'] = $bctai_file_id->get_error_message();
                                break;
                            }
                        } else {
                            $bctai_file_id = $bctai_check->ID;
                            update_post_meta($bctai_check->ID, 'bctai_model', $item->model);
                            update_post_meta($bctai_check->ID, 'bctai_updated_at', date('Y-m-d H:i:s', $item->updated_at));
                            update_post_meta($bctai_check->ID, 'bctai_name', $item->fine_tuned_model);
                            update_post_meta($bctai_check->ID, 'bctai_org', $item->organization_id);
                            update_post_meta($bctai_check->ID, 'bctai_status', $item->status);
                            if(isset($item->training_files->id)) {
                                update_post_meta($bctai_check->ID, 'bctai_fine_tune', $item->training_files->id);
                            }
                        }
                        if(!empty($item->fine_tuned_model)) {
                            $resultModel = $open_ai->retrieveModel($item->fine_tuned_model);                            
                            $resultModel = json_decode($resultModel);
                            if(isset($resultModel->error)){
                                wp_delete_post($bctai_file_id);
                            } elseif($item->status == 'succeeded'){
                                $finetune_models[] = $item->fine_tuned_model;
                            }
                        }
                    }
                    update_option('bctai_custom_models', $finetune_models);
                    if(count($bctaiExist)) {
                        $commaDelimitedPlaceholders = implode(',', array_fill(0, count($bctaiExist), '%s'));
                        $wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->posts . " WHERE post_type='bctai_finetune' AND post_title NOT IN ($commaDelimitedPlaceholders)",$bctaiExist));
                    } else {
                        $wpdb->query("DELETE FROM " . $wpdb->posts . " WHERE post_type='bctai_finetune'");
                    }
                } else {
                    $bctai_result['status'] = 'success';
                    $wpdb->query("DELETE FROM " . $wpdb->posts . " WHERE post_type='bctai_finetune'");
                }
            }
            wp_send_json($bctai_result);
        }

        public function bctai_save_files($items)
        {
            global $wpdb;  
            $bctaiExist = array();
            foreach($items as $item) {
                if($item->purpose !== 'fine-tune-results' && $item->status != 'deleted') {                    
                    $bctai_check = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->posts . " WHERE post_type='bctai_file' AND post_title=%s",$item->id));
                    $bctaiExist[] = $item->id;
                    if (!$bctai_check) {
                        $bctai_file_id = wp_insert_post(array(
                            'post_title' => $item->id,
                            'post_date' => date('Y-m-d H:i:s', $item->created_at),
                            'post_status' => 'publish',
                            'post_type' => 'bctai_file',
                        ));
                        if (!is_wp_error($bctai_file_id)) {
                            add_post_meta($bctai_file_id, 'bctai_filename', $item->filename);
                            add_post_meta($bctai_file_id, 'bctai_purpose', $item->purpose);
                            add_post_meta($bctai_file_id, 'bctai_file_size', $item->bytes);
                        } else {
                            $bctai_result['status'] = 'error';
                            $bctai_result['msg'] = $bctai_file_id->get_error_message();
                            break;
                        }
                    } else {
                        update_post_meta($bctai_check->ID, 'bctai_filename', $item->filename);
                        update_post_meta($bctai_check->ID, 'bctai_purpose', $item->purpose);
                        update_post_meta($bctai_check->ID, 'bctai_file_size', $item->bytes);
                    }
                }
            }
            if(count($bctaiExist)) {
                $commaDelimitedPlaceholders = implode(',', array_fill(0, count($bctaiExist), '%s'));
                $wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->posts . " WHERE post_type='bctai_file' AND post_title NOT IN ($commaDelimitedPlaceholders)", $bctaiExist));
            }
            else {
                $wpdb->query("DELETE FROM ".$wpdb->posts." WHERE post_type='bctai_file'");
            }

        }

        public function bctai_files()
        {
            global $wpdb;
            $bctai_result = array('status' => 'error','msg' => 'Something went wrong15');
            if ( ! wp_verify_nonce( $_POST['nonce'], 'bctai-ajax-nonce' ) ) {
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = BCTAI_NONCE_ERROR;
                wp_send_json($bctai_result);
            }
            $open_ai = BCTAI_OpenAI::get_instance()->openai();
            if(!$open_ai){
                $bctai_result['msg'] = 'Missing API Setting';
                wp_send_json($bctai_result);
            }
            $result = $open_ai->listFiles();
            $result = json_decode($result);
            if(isset($result->error)) {
                $bctai_result['msg'] = $result->error->message;
            }
            else {
                if(isset($result->data) && is_array($result->data) && count($result->data)){
                    $bctai_result['status'] = 'success';
                    $this->bctai_save_files($result->data);
                }
                else {
                    $bctai_result['status'] = 'success';
                    $wpdb->query("DELETE FROM ".$wpdb->posts." WHERE post_type='bctai_file'");
                }
            }
            wp_send_json($bctai_result);
        }

        public function bctai_data_insert()
        {
            $bctai_result = array('status' => 'error','msg' => 'Something went wrong11');
            if ( ! wp_verify_nonce( $_POST['nonce'], 'bctai-ajax-nonce' ) ) {
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = BCTAI_NONCE_ERROR;
                wp_send_json($bctai_result);
            }

            $bctai_file_generation = false;
            if ($_POST['model'] === 'gpt-3.5-turbo') {
                if (
                    isset($_POST['messages'])
                    && !empty($_POST['messages'])
                ) {
                    $message = isset($_POST['messages']) ? $_POST['messages'] : array();
                    foreach ($message as $role => $content) {
                        $data[$role]['role'] = $content['role'];
                        $data[$role]['content'] = sanitize_text_field($content['content']);
                    }
                    $data = array(
                        'messages' => $data,
                    );
                    $bctai_file_generation = true;
                }
            } else {
                if(
                    isset($_POST['prompt'])
                    && !empty($_POST['prompt'])
                    && isset($_POST['completion'])
                    && !empty($_POST['completion'])
                ) {
                    $data = array(
                        'prompt'        =>  sanitize_text_field($_POST['prompt']).' ->',
                        'completion'    =>  strip_tags(sanitize_text_field($_POST['completion']))
                    );
                    $bctai_file_generation = true;
                }
            }

            if ($bctai_file_generation) {            
                $file = isset($_POST['file']) && !empty($_POST['file']) ? sanitize_text_field($_POST['file']) : md5(time()).'.jsonl';
                //$bctai_result['msg'] = $file;
                //$upload = wp_upload_dir();
                //$upload_dir = $upload['basedir'];
                //$upload_dir = $upload_dir . '/bctai-entry';
                //if (! is_dir($upload_dir)) {
                //    mkdir( $upload_dir, 0755 );
                //}
                $bctai_json_file = fopen(wp_upload_dir()['basedir'] . '/' . $file, "a");
                fwrite($bctai_json_file, json_encode($data) . PHP_EOL);
                fclose($bctai_json_file);
                $bctai_result['file'] = $file;
                $bctai_result['status'] = 'success';
            }
            wp_send_json($bctai_result);
        }

        public function bctai_upload_convert()
        {
            $bctai_result = array('status' => 'error', 'msg' => 'Something went wrong17');
            if ( !wp_verify_nonce( $_POST['nonce'], 'bctai-ajax-nonce' ) ) {
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = BCTAI_NONCE_ERROR;
                wp_send_json($bctai_result);
            }
            if(isset($_POST['file'])&& !empty($_POST['file']))
            {
                //$bctai_result['msg'] = 'bctai_upload_convert';
                //wp_send_json($bctai_result);
                $filename = sanitize_text_field($_POST['file']);
                $line = isset($_POST['line']) && !empty($_POST['line']) ? sanitize_text_field($_POST['line']) : 0;
                $index = isset($_POST['index']) && !empty($_POST['index']) ? sanitize_text_field($_POST['index']) : 1;                
                $file = wp_upload_dir()['basedir'] . '/' . $filename;
                //$bctai_result['msg'] = $file;                
                if(file_exists($file)) {
                    //$bctai_result['msg'] = 'its ok';
                    $bctai_provider = get_option('bctai_provider', 'OpenAI');
                    $open_ai = BCTAI_OpenAI::get_instance()->openai();
                    // if provider not openai then assing azure to $open_ai
                    if ($bctai_provider != 'OpenAI') {
                        $open_ai = BCTAI_AzureAI::get_instance()->azureai();
                    }
                    if(!$open_ai) {
                        $bctai_result['msg'] = esc_html__('Missing API Setting', 'bctai');
                    } else {
                        $bctai_lines = file($file);
                        $bctai_file_size = filesize($file);
                        if ($bctai_file_size < $this->bctai_max_file_size) {
                            $result = $this->bctaiUploadOpenAI($file, $open_ai);
                            $bctai_result['next'] = 'DONE';
                        } else {
                            $filename =  str_replace('.jsonl', '', $filename);
                            $filename = $filename . '-' . $index . '.jsonl';
                            try {
                                $split_file = wp_upload_dir()['basedir'] . '/' . $filename;
                                $bctai_json_file = fopen($split_file, "a");
                                $bctai_content = '';
                                for($i = $line; $i <= count($bctai_lines); $i++) {
                                    if($i == count($bctai_lines)){
                                        $bctai_content .= $bctai_lines[$i];
                                        $bctai_result['next'] = 'DONE';
                                    } else {
                                        if(mb_strlen($bctai_content, '8bit') > $this->bctai_max_file_size) {
                                            $bctai_result['next'] = $i + 1;
                                            break;
                                        } else {
                                            $bctai_content .= $bctai_lines[$i];
                                        }
                                    }
                                }
                                fwrite($bctai_json_file, $bctai_content);
                                fclose($bctai_json_file);
                                $result = $this->bctaiUploadOpenAI($split_file, $open_ai);
                                //$bctai_result['msg'] = $result;
                                unlink($split_file);
                            } catch (\Exception $exception) {
                                $result = $exception->getMessage();
                            }
                        }
                        if($result == 'success'){
                            $bctai_result['status'] = 'success';
                        } else {
                            $bctai_result['msg'] = $result;
                        }
                    }                    
                } else {
                    $bctai_result['msg'] = esc_html__('The file has been removed', 'bctai');
                }
            } else {
                $bctai_result['msg'] = esc_html__('The file does not exist or removed', 'bctai');
            }
            wp_send_json($bctai_result);
        }

        public function bctai_delete_finetune_file()
        {
            $bctai_result = array('status' => 'error', 'msg' => 'Something went wrong');
            if ( !wp_verify_nonce( $_POST['nonce'], 'bctai-ajax-nonce' ) ) {
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = BCTAI_NONCE_ERROR;
                wp_send_json($bctai_result);
            }
            if(isset($_POST['id']) && !empty($_POST['id'])){
                $bctai_file = get_post(sanitize_text_field($_POST['id']));
                if($bctai_file) {
                    $bctai_provider = get_option('bctai_provider', 'OpenAI');
                    $open_ai = BCTAI_OpenAI::get_instance()->openai();
                    // if provider not openai then assing azure to $open_ai
                    if ($bctai_provider != 'OpenAI') {
                        $open_ai = BCTAI_AzureAI::get_instance()->azureai();
                    }
                    if(!$open_ai) {
                        $bctai_result['msg'] = esc_html__('Missing API Setting', 'bctai');
                        wp_send_json($bctai_result);
                    }
                    //$bctai_result['msg'] = $bctai_file->post_title;
                    $result = $open_ai->deleteFile($bctai_file->post_title);
                    $result = json_decode($result);
                    if(isset($result->error)) {
                        $bctai_result['msg'] = $result->error->message;
                    } else {
                        wp_delete_post($bctai_file->ID);
                        $bctai_result['status'] = 'success';
                    }
                } else {
                    $bctai_result['msg'] = esc_html__('File not found', 'bctai');
                }
            }
            wp_send_json($bctai_result);
        }

        public function bctai_get_finetune_file()
        {
            $bctai_result = array('status' => 'error', 'msg' => 'Something went wrong');
            if ( !wp_verify_nonce( $_POST['nonce'], 'bctai-ajax-nonce' ) ) {
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = BCTAI_NONCE_ERROR;
                wp_send_json($bctai_result);
            }
            if(isset($_POST['id']) && !empty($_POST['id'])){
                $bctai_file = get_post(sanitize_text_field($_POST['id']));
                if($bctai_file) {
                    $bctai_provider = get_option('bctai_provider', 'OpenAI');
                    $open_ai = BCTAI_OpenAI::get_instance()->openai();
                    // if provider not openai then assing azure to $open_ai
                    if ($bctai_provider != 'OpenAI') {
                        $open_ai = BCTAI_AzureAI::get_instance()->azureai();
                    }
                    if(!$open_ai) {
                        $bctai_result['msg'] = esc_html__('Missing API Setting', 'bctai');
                        wp_send_json($bctai_result);
                    }
                    $result = $open_ai->retrieveFileContent($bctai_file->post_title);
                    //$bctai_result['msg'] = $result;
                    $json_parse = json_decode($result);
                    if(isset($json_parse->error)){
                        $bctai_result['msg'] = $json_parse->error->message;
                    } else {
                        $bctai_result['status'] = 'success';
                        $bctai_result['data'] = $result;
                    }
                } else {
                    $bctai_result['msg'] = esc_html__('File not found', 'bctai');
                }
            }
            wp_send_json($bctai_result);
        }


        public function bctai_finetune_events()
        {
            $bctai_result = array('status' => 'error', 'msg' => esc_html__('Something went wrong','bctai'));   
            if(isset($_POST['id']) && !empty($_POST['id'])){
                $bctai_file = get_post(sanitize_text_field($_POST['id']));
                if($bctai_file){
                    $open_ai = BCTAI_OpenAI::get_instance()->openai();
                    if(!$open_ai){
                        $bctai_result['msg'] = esc_html__('Missing API Setting','bctai');
                        wp_send_json($bctai_result);
                    }
                    $result = $open_ai->retrieveFineTune($bctai_file->post_title);
                    $result = json_decode($result);
                    if(isset($result->error)){
                        $bctai_result['msg'] = $result->error->message;
                    }
                    else{
                        $bctai_result['status'] = 'success';
                        $bctai_result['data'] = $result->events;
                    }
                }
                else{
                    $bctai_result['msg'] = esc_html__('Fine Tune not found','bctai');
                }
            }
            wp_send_json($bctai_result);
        }


        public function bctai_get_finetune()
        {
            $bctai_result = array('status' => 'error', 'msg' => esc_html__('Something went wrong','bctai'));
            if(isset($_POST['id']) && !empty($_POST['id'])){
                $bctai_file = get_post(sanitize_text_field($_POST['id']));
                if($bctai_file){
                    $open_ai = BCTAI_OpenAI::get_instance()->openai();
                    if(!$open_ai){
                        $bctai_result['msg'] = esc_html__('Missing API Setting','bctai');
                        wp_send_json($bctai_result);
                    }
                    $result = $open_ai->retrieveFineTune($bctai_file->post_title);
                    $result = json_decode($result);
                    if(isset($result->error)){
                        $bctai_result['msg'] = $result->error->message;
                    }
                    else{
                        $bctai_result['status'] = 'success';
                        $bctai_result['data'] = $result;
                    }
                }
                else{
                    $bctai_result['msg'] = esc_html__('Fine Tune not found','bctai');
                }
            }
            wp_send_json($bctai_result);
        }


        public function bctai_cancel_finetune()
        {
            $bctai_result = array('status' => 'error', 'msg' => 'Something went wrong');
            if ( !wp_verify_nonce( $_POST['nonce'], 'bctai-ajax-nonce' ) ) {
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = BCTAI_NONCE_ERROR;
                wp_send_json($bctai_result);
            }
            if(isset($_POST['id']) && !empty($_POST['id'])) {
                $bctai_file = get_post(sanitize_text_field($_POST['id']));
                if($bctai_file) {
                    $bctai_provider = get_option('bctai_provider', 'OpenAI');
                    $open_ai = BCTAI_OpenAI::get_instance()->openai();
                    // if provider not openai then assing azure to $open_ai
                    if ($bctai_provider != 'OpenAI') {
                        $open_ai = BCTAI_AzureAI::get_instance()->azureai();
                    }
                    if(!$open_ai) {
                        $bctai_result['msg'] = esc_html__('Missing API Setting', 'bctai');
                        wp_send_json($bctai_result);
                    }
                    $result = $open_ai->cancelFineTune($bctai_file->post_title);
                    //$bctai_result['msg'] = $result;
                    $result = json_decode($result);
                    if(isset($result->error)){
                        $bctai_result['msg'] = $result->error->message;
                    } else {
                        add_post_meta($bctai_file->ID, 'bctai_status', 'cancelled');
                        $bctai_result['status'] = 'success';
                    }
                } else {
                    $bctai_result['msg'] = esc_html__('File not found', 'bctai');
                }
            }
            wp_send_json($bctai_result);
        }

        public function bctai_delete_finetune()
        {
            $bctai_result = array('status' => 'error', 'msg' => 'Something went wrong');
            if ( !wp_verify_nonce( $_POST['nonce'], 'bctai-ajax-nonce' ) ) {
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = BCTAI_NONCE_ERROR;
                wp_send_json($bctai_result);
            }
            if(isset($_POST['id']) && !empty($_POST['id'])){
                $bctai_file = get_post(sanitize_text_field($_POST['id']));
                if($bctai_file) {
                    $bctai_provider = get_option('bctai_provider', 'OpenAI');
                    $open_ai = BCTAI_OpenAI::get_instance()->openai();
                    // if provider not openai then assing azure to $open_ai
                    if ($bctai_provider != 'OpenAI') {
                        $open_ai = BCTAI_AzureAI::get_instance()->azureai();
                    }
                    if(!$open_ai) {
                        $bctai_result['msg'] = esc_html__('Missing API Setting', 'bctai');
                        wp_send_json($bctai_result);
                    }
                    $ft_model = get_post_meta($bctai_file->ID, 'bctai_name', true);
                    //$bctai_result['msg'] = $ft_model;
                    if(!empty($ft_model)) {
                        $result = $open_ai->deleteFineTune($ft_model);
                        $result = json_decode($result);
                        if(isset($result->error)){
                            $bctai_result['msg'] = $result->error->message;
                        } else {
                            update_post_meta($bctai_file->ID, 'bctai_deleted', '1');
                            $bctai_result['status'] = 'success';
                        }
                    } else {
                        $bctai_result['msg'] = esc_html__('That model does not exist', 'bctai');
                    }
                } else {
                    $bctai_result['msg'] = esc_html__('File not found', 'bctai');
                }
            }
            wp_send_json($bctai_result);
        }


        public function bctai_data_converter_count()
        {
            global $wpdb;            
            if ( ! wp_verify_nonce( $_POST['nonce'], 'bctai_data_converter_count' ) ) {
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = BCTAI_NONCE_ERROR;
                wp_send_json($bctai_result);
            }
            $bctai_result = array('status' => 'error','msg' => esc_html__('Something went wrong','bctai'));            
            //$bctai_result['msg'] = $_POST['data'];
            //wp_send_json($bctai_result);
            if(isset($_POST['data']) && is_array($_POST['data']) && count($_POST['data'])) {
                $types = \BCTAI\bctai_util_core()->sanitize_text_or_array_field($_POST['data']);
                $commaDelimitedPlaceholders = implode(',', array_fill(0, count($types), '%s'));
                $sql = $wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->posts." WHERE post_status='publish' AND post_type IN ($commaDelimitedPlaceholders)", $types);
                $bctai_result['count'] = $wpdb->get_var($sql);
                $bctai_result['status'] = 'success';
                $bctai_result['types'] = $types;
            }
            else {
                $bctai_result['msg'] = esc_html__('Please select least one data to convert','bctai');
            }

            wp_send_json($bctai_result);
        }


        public function bctai_data_converter()
        {
            global $wpdb;
            $bctai_result = array('status' => 'error','msg' => esc_html__('Something went wrong555','bctai'));
             if ( ! wp_verify_nonce( $_POST['nonce'], 'bctai-ajax-nonce' ) ) {
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = BCTAI_NONCE_ERROR;
                wp_send_json($bctai_result);
            }
            if(
                isset($_POST['types'])
                && is_array($_POST['types'])
                && count($_POST['types'])
                && isset($_POST['per_page'])
                && !empty($_POST['per_page'])
                && isset($_POST['total'])
                && !empty($_POST['total'])
            ){
                $types = \BCTAI\bctai_util_core()->sanitize_text_or_array_field($_POST['types']);
                $bctai_total = sanitize_text_field($_POST['total']);
                $bctai_per_page = sanitize_text_field($_POST['per_page']);
                $bctai_page = isset($_POST['page']) && !empty($_POST['page']) ? sanitize_text_field($_POST['page']) : 1;
                if(isset($_POST['file']) && !empty($_POST['file'])){
                    $bctai_file = sanitize_text_field($_POST['file']);
                }
                else {
                    $bctai_file = md5(time()).'.jsonl';
                }
                if(isset($_POST['id']) && !empty($_POST['id'])){
                    $bctai_convert_id = sanitize_text_field($_POST['id']);
                }
                else {
                    $bctai_convert_id = wp_insert_post(array(
                        'post_title' => $bctai_file,
                        'post_type' => 'bctai_convert',
                        'post_status' => 'publish'
                    ));
                }
                try {
                    $bctai_json_file = fopen(wp_upload_dir()['basedir'] . '/' . $bctai_file, "a");
                    $bctai_content = '';
                    $bctai_content_legalprotech = array();
                    $bctai_offset = ($bctai_page * $bctai_per_page) - $bctai_per_page;
                    $sql = $wpdb->prepare("SELECT ID, post_title, post_content, post_type FROM ".$wpdb->posts." WHERE post_status='publish' AND post_type IN ('".implode("','",$types)."') ORDER BY post_date ASC LIMIT %d,%d",$bctai_offset,$bctai_per_page);                    
                    $bctai_data = $wpdb->get_results($sql);               
                    //$bctai_result['status'] = 'success';
                    //$bctai_result['msg'] = json_encode($bctai_data);
                    //wp_send_json($bctai_result);
                    if($bctai_data && is_array($bctai_data) && count($bctai_data)){
                        //$bctai_result['status'] = 'success';
                        //$bctai_result['msg'] = $bctai_data[0]->post_type;
                        //wp_send_json($bctai_result);                        
                        foreach($bctai_data as $item){

                            if($item->post_type == 'legalprotech') {
                                $bctai_content_legalprotech = get_post_meta($item->ID);

                                //$bctai_result['status'] = 'success';
                                //$bctai_result['msg'] = $item->post_type;
                                //wp_send_json($bctai_result);

                                $bctai_content = 'Country: ' . implode('', $bctai_content_legalprotech['country']).", ";
                                $bctai_content .= 'Company Name: ' . implode('', $bctai_content_legalprotech['company_name']).", ";
                                $bctai_content .= 'Company Address: ' . implode('', $bctai_content_legalprotech['company_address']).", ";
                                $bctai_content .= 'Zip Code: ' . implode('', $bctai_content_legalprotech['zip_code']).", ";
                                $bctai_content .= 'Industry Group: ' . implode('', $bctai_content_legalprotech['industry_group']).", ";
                                $bctai_content .= 'Featured Products: ' . implode('', $bctai_content_legalprotech['featured_products']).", ";
                                $bctai_content .= 'Phone: ' . implode('', $bctai_content_legalprotech['phone']).", ";
                                $bctai_content .= 'Tel: ' . implode('', $bctai_content_legalprotech['tel']).", ";
                                $bctai_content .= 'Fax: ' . implode('', $bctai_content_legalprotech['fax']).", ";
                                $bctai_content .= 'Website: ' . implode('', $bctai_content_legalprotech['website']).", ";
                                $bctai_content .= 'Email: ' . implode('', $bctai_content_legalprotech['email']).", ";
                                $bctai_content .= 'Recent News: ' . implode('', $bctai_content_legalprotech['recent_news']).", ";
                                $bctai_content .= 'Equity Relationship: ' . implode('', $bctai_content_legalprotech['equity_relationship']).", ";
                                $bctai_content .= 'Stock Related Information: ' . implode('', $bctai_content_legalprotech['stock_related_information']).", ";
                                $bctai_content .= 'Business Registration: ' . implode('', $bctai_content_legalprotech['business_registration']).", ";
                                $bctai_content .= 'Listing Date: ' . implode('', $bctai_content_legalprotech['listing_date']).", ";

                                //$data = array(
                                //    "prompt" => $item->post_title.' ->',
                                //    "completion" => strip_tags($bctai_content),
                                //);

                                $itxt = "BCTONE is a system chat";
                                
                                // 기존의 배열
                                $data = [];

                                // 새로운 메시지 객체를 생성하여 배열에 추가
                                $newMessage = [
                                    "messages" => [
                                        ["role" => "system", "content" => $itxt],
                                        ["role" => "user", "content" => $item->post_title],
                                        ["role" => "assistant", "content" => strip_tags($bctai_content)]
                                    ]
                                ];
                                
                                // 배열에 새로운 메시지 객체를 추가                                
                                array_push($data, $newMessage);

                                $json_data = json_encode($data);

                                // 처음 대괄호와 마지막 대괄호 삭제함
                                $data_striped = substr($json_data, 1, -1);

                                fwrite($bctai_json_file, $data_striped . PHP_EOL);

                            }
                            else {

                                $data = array(
                                    "prompt" => $item->post_title . ' ->',
                                    "completion" => strip_tags($item->post_content),
                                );

                                fwrite($bctai_json_file, json_encode($data) . PHP_EOL);
                            }
                            
                        }
                    }
                    fclose($bctai_json_file);
                    $bctai_max_page = ceil($bctai_total / $bctai_per_page);
                    if($bctai_max_page == $bctai_page){
                        $bctai_result['next_page'] = 'DONE';
                        wp_update_post(array(
                            'ID' => $bctai_convert_id,
                            'post_modified' => date('Y-m-d H:i:s')
                        ));
                    } else {
                        $bctai_result['next_page'] = $bctai_page + 1;
                    }
                    $bctai_result['file'] = $bctai_file;
                    $bctai_result['id'] = $bctai_convert_id;
                    $bctai_result['status'] = 'success';
                } catch (\Exception $exception) {
                    $bctai_result['msg'] = $exception->getMessage();
                }
            } else {
                $bctai_result['msg'] = esc_html__('Please select least one data to convert','bctai');
            }

            wp_send_json($bctai_result);
        }
        


        public static function bctai_finetune()
        {
            include BCTAI_PLUGIN_DIR.'admin/views/finetune/index.php';
        }
    }
    BCTAI_FineTune::get_instance();
}