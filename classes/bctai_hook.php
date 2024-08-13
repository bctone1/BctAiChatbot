<?php
namespace BCTAI;
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('\\BCTAI\\BCTAI_Hook')) {
    class BCTAI_Hook
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
            add_action( 'admin_menu', array( $this, 'bctai_change_menu_name' ) );
            //add_action( 'admin_head', array( $this, 'bctai_hooks_admin_header' ) );
            add_action( 'wp_footer', [$this, 'bctai_footer'], 1 );
            add_action( 'wp_head',[$this,'bctai_head_seo'], 1 );
            add_action( 'admin_enqueue_scripts', 'wp_enqueue_media' );
            add_action( 'admin_footer', array( $this, 'bctai_admin_footer') );
            add_editor_style(BCTAI_PLUGIN_URL.'admin/css/editor.css');
            // add_action( 'admin_enqueue_scripts', [$this,'bctai_enqueue_scripts'] );
            add_action( 'wp_enqueue_scripts', [$this, 'wp_enqueue_scripts_hook'] );

            //카카오페이 핸들러
            add_action('wp_ajax_kakao_pay_request', [$this,'kakao_pay_ajax_handler']);
            add_action('wp_ajax_nopriv_kakao_pay_request', [$this,'kakao_pay_ajax_handler']);
        }


        function kakao_pay_ajax_handler() {

            $url = 'https://open-api.kakaopay.com/online/v1/payment/ready';
            $secret_key = 'DEV2602A5BBC14DCA3DD2AF1ED3385EE3F4603A3';
            $data = array(
                'cid' => 'TC0ONETIME',
                'partner_order_id' => '1001',
                'partner_user_id' => 'yeongbin',
                'item_name' => '초코파이',
                'quantity' => '1',
                'total_amount' => '2200',
                'vat_amount' => '200',
                'tax_free_amount' => '0',
                'approval_url' => 'https://c0013.bctcloud.kr',
                'fail_url' => 'https://c0013.bctcloud.kr',
                'cancel_url' => 'https://c0013.bctcloud.kr'
            );
        
            $args = array(
                'body' => json_encode($data),
                'headers' => array(
                    'Authorization' => 'KakaoAK ' . $secret_key,
                    'Content-Type' => 'application/json'
                )
            );
        
            $response = wp_remote_post($url, $args);
        
            if (is_wp_error($response)) {
                wp_send_json_error('Failed to connect to KakaoPay API');
            } else {
                $body = wp_remote_retrieve_body($response);
                wp_send_json_success(json_decode($body));
            }
        }


        

        public function bctai_enqueue_scripts()
        {
            // wp_enqueue_script('bctai-chat-shortcode', BCTAI_PLUGIN_URL.'src/js/bctai-chat.js',array(),null,true);
            // wp_enqueue_script('bctai-pdf-script', BCTAI_PLUGIN_URL.'src/js/pdf.js',null,null,true);
            // wp_enqueue_style('bctai-extra-css', BCTAI_PLUGIN_URL.'src/css/bctai_extra.css',array(),null);


        }



        public function bctai_change_menu_name()
        {
            global $menu;
            global $submenu;
            //var_dump($submenu);
            $submenu['bctai'][0][0] = 'Settings';
            $bctai_arr = array();
            $bctai_next = array();
            foreach( $submenu['bctai'] as $key => $bctai_sub ) {

                if ( $key == 1 ) {
                    $bctai_next[] = $bctai_sub;
                } else {
                    $bctai_arr[] = $bctai_sub;
                }

            }
            $submenu['bctai'] = array_merge( $bctai_arr, $bctai_next );
        } 

        
        public function bctai_footer()
        {
            

            include BCTAI_PLUGIN_DIR.'admin/extra/bctai_chat_widget.php';
            ?>
            <script>
                var bctaiUserLoggedIn = <?php echo is_user_logged_in() ? 'true' : 'false';?>;
            </script>
            <?php


            $current_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $page_id = get_the_ID();
            $user_id = get_current_user_id();
            $cookie_value =md5($_COOKIE['bctai_chat_client_id']);
            

            $wpdb->insert(
                $wpdb->prefix . 'bctai_visitor_count',
                array(
                    'session' => $cookie_value,
                    'page_user' => $user_id,
                    'page_url' => $page_id,
                    'time' => time()
                )
            );
        }


        public function bctai_admin_footer()
        {
            ?>
            <div class="bctai-overlay" style="display: none">
                <div class="bctai_modal">
                    <div class="bctai_modal_head">
                        <span class="bctai_modal_title">GPT3 Modal</span>
                        <span class="bctai_modal_close">&times;</span>
                    </div>
                    <div class="bctai_modal_content"></div>
                </div>
            </div>
            <div class="bctai-overlay-second" style="display: none">
                <div class="bctai_modal_second">
                    <div class="bctai_modal_head_second">
                        <span class="bctai_modal_title_second">GPT3 Modal</span>
                        <span class="bctai_modal_close_second">&times;</span>
                    </div>
                    <div class="bctai_modal_content_second"></div>
                </div>
            </div>
            <div class="bctai_lds-ellipsis" style="display: none">
                <div class="bctai-generating-title">Generating content..</div>
                <div class="bctai-generating-process"></div>
                <div class="bctai-timer"></div>
            </div>
            <script>
                let bctai_ajax_url = '<?php echo admin_url('admin-ajax.php')?>';
            </script>
            <?php
        }

        public function wp_enqueue_scripts_hook()
        {
            // wp_enqueue_script('bctai-chat-script', BCTAI_PLUGIN_URL.'src/js/bctai-chat.js',null,null,true);
            //wp_enqueue_script('bctai-pdf-script', BCTAI_PLUGIN_URL.'src/js/pdf.js',null,null,true);
        }

        public function bctai_head_seo()
        {
            global $wpdb;

            


            $bctai_chat_widget = get_option('bctai_chat_widget',[]);
            $bctai_chat_design = get_option('bctai_chat_design',[]);


            //system
            $bctai_chat_proffesion = isset($bctai_chat_design['proffesion']) && !empty($bctai_chat_design['proffesion']) ? $bctai_chat_design['proffesion'] : 'none';
            $bctai_chat_position = isset($bctai_chat_design['position']) && !empty($bctai_chat_design['position']) ? $bctai_chat_design['position'] : 'left';
            //style
            $bctai_chat_icon = isset($bctai_chat_design['icon']) && !empty($bctai_chat_design['icon']) ? $bctai_chat_design['icon'] : 'default';
            $bctai_chat_icon_url = isset($bctai_chat_design['icon_url']) && !empty($bctai_chat_design['icon_url']) ? $bctai_chat_design['icon_url'] : '';
            $bctai_ai_avatar = isset($bctai_chat_design['ai_avatar']) && !empty($bctai_chat_design['ai_avatar']) ? $bctai_chat_design['ai_avatar'] : 'default';
            $bctai_ai_avatar_id = isset($bctai_chat_design['ai_avatar_id']) && !empty($bctai_chat_design['ai_avatar_id']) ? $bctai_chat_design['ai_avatar_id'] : '';





            $Header_Color = isset($bctai_chat_design['Header_Color']) && !empty($bctai_chat_design['Header_Color']) ? $bctai_chat_design['Header_Color'] : '#8040ad';



            $Button_Icon_Color = isset($bctai_chat_design['Button_Icon_Color']) && !empty($bctai_chat_design['Button_Icon_Color']) ? $bctai_chat_design['Button_Icon_Color'] : '#569bd4';
            $Message_Background_Color = isset($bctai_chat_design['Message_Background_Color']) && !empty($bctai_chat_design['Message_Background_Color']) ? $bctai_chat_design['Message_Background_Color'] : '#569bd4';
            $Header_Text_Color =isset($bctai_chat_design['Header_Text_Color']) && !empty($bctai_chat_design['Header_Text_Color']) ? $bctai_chat_design['Header_Text_Color'] : '#569bd4';

            //context
            $bctai_chat_remember_conversation = isset($bctai_chat_widget['remember_conversation']) && !empty($bctai_chat_widget['remember_conversation']) ? $bctai_chat_widget['remember_conversation'] : 'yes';
            $bctai_chat_content_aware = isset($bctai_chat_widget['content_aware']) && !empty($bctai_chat_widget['content_aware']) ? $bctai_chat_widget['content_aware'] : 'yes';
            //token handling
            $bctai_include_footer = (isset($bctai_chat_widget['footer_text']) && !empty($bctai_chat_widget['footer_text'])) ? 5 : 0;
            ?>
            <style>  
                .bctai_widget_open .bctai_chat_widget_content {
                    height: 690px;
                    width: 400px;
                }

                .high {
                    height: 690px;
                    width: 400px;
                }
                .bctai_chat_widget_content{
                    height: 690px;
                    width: 400px;
                    position: absolute;
                    bottom: calc(100% + 15px);
                }

                .bctai_widget_open .bctai_chat_widget_content .bctai-chatbox {
                    top: -30px;
                }
    
                
                
                .bctai_chat_widget {
                    position: fixed;
                }
                .bctai_widget_left {
                    bottom: 15px;
                    left: 15px;
                }
                .bctai_widget_right {
                    bottom: 15px;
                    right: 15px;
                }
                .bctai_widget_right .bctai_chat_widget_content {
                    right: 0;
                    height: <?php echo esc_html($bctai_chat_height)?>px;
                    
                }
                .bctai_widget_left .bctai_chat_widget_content {
                    visibility: hidden;
                    left: 0;
                    height: <?php echo esc_html($bctai_chat_height)?>px;
                    
                }
                
                .bctai_chat_widget_content .bctai-chatbox {
                    position: absolute;
                    top: 103%;
                    left: 0;
                    transition: top 300ms cubic-bezier(0.17, 0.04, 0.03, 0.94);
                }
                .bctai_chat_widget .bctai_toggle{
                    margin-left:10px;
                        cursor: pointer;
                }
                .bctai_chat_widget .bctai_toggle img{
                    width: 75px;
                    height: 75px;
                }    
            </style>

            <script>
                let bctai_ajax_url = '<?php echo admin_url('admin-ajax.php')?>';
            </script>



            <?php

        }


    }
    BCTAI_Hook::get_instance();
}


