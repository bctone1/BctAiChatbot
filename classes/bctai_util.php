<?php

namespace BCTAI;
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('\\BCTAI\\BCTAI_Util')) {
    class BCTAI_Util
    {
        private static $instance = null;

        public static function get_instance()
        {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            add_filter('sanitize_text_field',[$this,'modify_sanitize_text_field'],10,2);
        }
        
        public function modify_sanitize_text_field($filtered, $str)
        {
            return str_replace("\\",'',$filtered);
        }

        public function bctai_random($length = 10) {
            $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[wp_rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }

        public function get_embedding_models() {

            $models = array(
                'OpenAI' => array(
                    'text-embedding-3-small' => 1536,
                    'text-embedding-3-large' => 3072,
                    'text-embedding-ada-002' => 1536
                ),
                'Google' => array(
                    'embedding-001' => 768,
                    'text-embedding-004' => 768,
                )
            );
        
            $Embedding_models = array(
                'text-embedding-3-small' => 1536,
                'text-embedding-3-large' => 3072,
                'text-embedding-ada-002' => 1536,
                'embedding-001' => 768,
                'text-embedding-004' => 768,
            );
        
            return $models;
        }

        

        public function sanitize_text_or_array_field($array_or_string)
        {
            //var_dump($array_or_string);
            if (is_string($array_or_string)) {
                $array_or_string = sanitize_text_field($array_or_string);
            } elseif (is_array($array_or_string)) {
                foreach ($array_or_string as $key => &$value) {
                    if (is_array($value)) {
                        $value = $this->sanitize_text_or_array_field($value);
                    } else {
                        $value = sanitize_text_field($value);
                    }
                }
            }

            return $array_or_string;
        }

        public function bctai_is_pro()
        {
            // echo '<script>alert("Hello");</script>';
            return bctai_fs()->is_plan__premium_only( 'pro' );
        }



        public function bctai_get_meta_keys($post_type = false)
        {
            if (empty($post_type)) return array();

            $post_type = ($post_type == 'product' and class_exists('WooCommerce')) ? array('product') : array($post_type);

            global $wpdb;
            $table_prefix = $wpdb->prefix;

            $post_type = array_map(function($item) use ($wpdb) {
                return $wpdb->prepare('%s', $item);
            }, $post_type);

            $post_type_in = implode(',', $post_type);

            //echo '<pre>'; print_r($table_prefix); echo '</pre>';
            //echo '<pre>'; print_r($post_type_in); echo '</pre>';

            $meta_keys = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT {$table_prefix}postmeta.meta_key FROM {$table_prefix}postmeta, {$table_prefix}posts WHERE {$table_prefix}postmeta.post_id = {$table_prefix}posts.ID AND {$table_prefix}posts.post_type IN ({$post_type_in}) AND {$table_prefix}postmeta.meta_key NOT LIKE '_edit%' AND {$table_prefix}postmeta.meta_key NOT LIKE '_oembed_%' LIMIT 1000"));            

            $_existing_meta_keys = array();
            if ( ! empty($meta_keys)){
                $exclude_keys = array('_first_variation_attributes', '_is_first_variation_created');
                foreach ($meta_keys as $meta_key) {
                    if ( strpos($meta_key->meta_key, "_tmp") === false && strpos($meta_key->meta_key, "_v_") === false && ! in_array($meta_key->meta_key, $exclude_keys))
                        $_existing_meta_keys[] = 'bctaicf_'.$meta_key->meta_key;
                }
            }
            return $_existing_meta_keys;
        }


        public function bctai_existing_taxonomies($post_type = false)
        {
            if (empty($post_type)) return array();

            $post_taxonomies = array_diff_key($this->bctai_get_taxonomies_by_object_type(array($post_type), 'object'), array_flip(array('post_format')));
            $_existing_taxonomies = array();
            if ( ! empty($post_taxonomies)){
                foreach ($post_taxonomies as $tx) {
                    if (strpos($tx->name, "pa_") !== 0)
                        $_existing_taxonomies[] = array(
                            'name' => empty($tx->label) ? $tx->name : $tx->label,
                            'label' => 'bctaitx_'.$tx->name,
                            'type' => 'cats'
                        );
                }
            }
            return $_existing_taxonomies;

        }


        function bctai_get_taxonomies_by_object_type($object_type, $output = 'names') {
            global $wp_taxonomies;

            is_array($object_type) or $object_type = array($object_type);
            $field = ('names' == $output) ? 'name' : false;
            $filtered = array();
            foreach ($wp_taxonomies as $key => $obj) {
                if (array_intersect($object_type, $obj->object_type)) {
                    $filtered[$key] = $obj;
                }
            }
            if ($field) {
                $filtered = wp_list_pluck($filtered, $field);
            }
            return $filtered;
        }


        public function bctai_tabs($prefix, $menus, $selected = false)
        {
            foreach($menus as $key=>$menu){
                $capability = $prefix;
                if(is_string($key)){
                    $capability .= '_'.$key;
                }
                if($capability == 'bctai_finetune_fine-tunes'){
                    $capability = 'bctai_finetune_file-tunes';
                }
                if(current_user_can($capability) || in_array('administrator', (array)wp_get_current_user()->roles)){
                    $url = admin_url('admin.php?page='.$prefix);
                    if(is_string($key)){
                        $url .= '&action='.$key;
                    }
                    ?>
                    <a class="nav-tab<?php echo $key === $selected ? ' nav-tab-active':''?>" href="<?php echo esc_html($url)?>">
                        <?php
                        echo esc_html($menu);                        
                        ?>
                    </a>
                    <?php
                }
            }
        }
        

    }
}
if(!function_exists(__NAMESPACE__.'\bctai_util_core')) {
    function bctai_util_core() {
        return BCTAI_Util::get_instance();
    }
}

