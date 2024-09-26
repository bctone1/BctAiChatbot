<?php
/*
*Plugin Name:       BCT AI Chatbot
*Description:       ChatGPT, Embeddings, AI Training, STT/TTS, Custom Post Types
*Version:           0.9.5
*Author:            Bctone
*Author URI:        https://bctaichatbot.com/
*License:           GPL-2.0+
*License URI:       https://www.gnu.org/licenses/gpl-2.0.html
*Text Domain:       bctai
*Domain Path:       /languages
*/
if ( !defined( 'WPINC' ) ) {
    die;
}
define( 'WP_BCT_AI_VERSION', '0.9.5' );
if( !class_exists( '\\BCTAI\\BCTAI_OpenAI' ) ) {
    require_once __DIR__ . '/includes/class-wp-bct-ai-openai.php';
}
if ( !class_exists( '\\BCTAI\\WPAICG_OpenRouter' ) ) {
    require_once __DIR__ . '/includes/class-wp-ai-openrouter.php';
}
if ( !class_exists( '\\BCTAI\\WPAICG_Google' ) ) {
    require_once __DIR__ . '/includes/class-wp-ai-google.php';
}
if ( !class_exists( '\\BCTAI\\BCTAI_Huggingface' ) ) {
    require_once __DIR__ . '/includes/class-wp-bct-ai-huggingface.php';
}




if ( ! function_exists( 'bct_fs' ) ) {
    // Create a helper function for easy SDK access.
    function bct_fs() {
        global $bct_fs;

        if ( ! isset( $bct_fs ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $bct_fs = fs_dynamic_init( array(
                'id'                  => '15409',
                'slug'                => 'bctai',
                'premium_slug'        => 'bctchatbot-premium',
                'type'                => 'plugin',
                'public_key'          => 'pk_68f1d4ad1f4ad58b208fc68574689',
                'is_premium'          => false,
                // If your plugin is a serviceware, set this option to false.
                //'has_premium_version' => true,
                'has_addons'          => false,
                'has_paid_plans'      => true,
                'menu'                => array(
                    'slug'           => 'bctaichat',
                    'first-path'     => 'admin.php?page=bctaichat',
                    'contact'        => false,
                    'support'        => false,
                ),
                'is_live'        => true,
            ) );
        }

        return $bct_fs;
    }

    // Init Freemius.
    bct_fs();
    // Signal that SDK was initiated.
    do_action( 'bct_fs_loaded' );
}

global $wpdb;
$bctai_visitor_count = $wpdb->prefix . 'bctai_visitor_count';

if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $bctai_visitor_count)) != $bctai_visitor_count) {
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE " . $bctai_visitor_count . "(
        `id` mediumint(11) NOT NULL AUTO_INCREMENT,
        `session` VARCHAR(255) NOT NULL,
        `page_user` VARCHAR(255) NOT NULL,
        `page_url` VARCHAR(255) NOT NULL,
        `time` INT NOT NULL,
        PRIMARY KEY (id)
        ) $charset_collate";
    $wpdb->query($sql);
}










function activate_wp_bct_ai()
{
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-bct-ai-activator.php';
    Wp_BCT_Ai_Activator::activate();
}
function deactivate_wp_bct_ai()
{
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-bct-ai-deactivator.php';
    Wp_BCT_Ai_Deactivator::deactivate();
}
function uninstall_wp_bct_ai(){
    
    global $wpdb;

    
    $site_options = $wpdb->get_results( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'bctai%'" );
    foreach ($site_options as $option) {

        $option_name = $option->option_name;
        delete_option($option_name);
        delete_site_option($option_name);
    }

    $site_options2 = $wpdb->get_results( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_bctai%'" );
    foreach ($site_options2 as $option) {

        $option_name = $option->option_name;
        delete_option($option_name);
        delete_site_option($option_name);
    }


    $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bctai" );
    $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bctai_audio_logs" );
    $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bctai_chatlogs" );
    $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bctai_chattokens" );
    $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bctai_visitor_count" );

    // $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bctai_form_logs" );
    // $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bctai_formtokens" );
    // $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bctai_promptbase_logs" );
    
}



// 새로운 모듈 관리 기능 추가
require_once plugin_dir_path(__FILE__) . 'modules/ModuleInterface.php';
require_once plugin_dir_path(__FILE__) . 'modules/ModuleManager.php';

// Register activation and deactivation hooks
add_action('wp_ajax_activate_module', 'activate_module');
add_action('wp_ajax_deactivate_module', 'deactivate_module');

function activate_module() {
    if (!current_user_can('manage_options')) {
        wp_die('권한이 없습니다.');
    }

    $module = sanitize_text_field($_POST['module']);
    $moduleManager = new \Modules\ModuleManager();
    $moduleManager->activateModule($module);

    
    $update_STTEvaluation_status =  update_option($module, 'true');

    echo $update_STTEvaluation_status;
    //echo 'hello, world!11';
    wp_die();
}

function deactivate_module() {
    if (!current_user_can('manage_options')) {
        wp_die('권한이 없습니다.');
    }

    $module = sanitize_text_field($_POST['module']);
    $moduleManager = new \Modules\ModuleManager();
    $moduleManager->deactivateModule($module);

    $update_STTEvaluation_status =  update_option($module, '');


    echo $update_STTEvaluation_status;
    //echo 'bye!';
    wp_die();
}


register_activation_hook( __FILE__, 'activate_wp_bct_ai' );

register_deactivation_hook( __FILE__, 'deactivate_wp_bct_ai' );

register_uninstall_hook( __FILE__, 'uninstall_wp_bct_ai' );

require plugin_dir_path( __FILE__ ) . '/includes/class-wp-bct-ai.php';

function run_wp_bct_ai()
{
    $plugin = new Wp_BCT_Ai();
    $plugin->run();
}

function load_bctai_plugin_textdomain() {
    load_plugin_textdomain( 'bctai', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'load_bctai_plugin_textdomain' );



function select_infomation_status() {
    add_meta_box(
        'yes_no_metabox',          // 메타박스 ID
        '정보를 공개하시겠습니까?',               // 메타박스 제목
        'render_infomation_metabox',   // 콜백 함수
        'post',                    // 해당 포스트 타입
        'side',                    // 위치 (사이드바)
        'default'                  // 우선순위
    );
}
add_action('add_meta_boxes', 'select_infomation_status');

function render_infomation_metabox($post) {
    $value = get_post_meta($post->ID, 'information_status', true);

    wp_nonce_field('save_infomation_status', 'save_infomation_status_nonce');

    ?>
    <p>
        <label>
            <input type="radio" name="yes_no_field" value="yes" <?php checked($value, 'yes'); ?> />
            Yes
        </label><br />
        <label>
            <input type="radio" name="yes_no_field" value="no" <?php checked($value, 'no'); ?> />
            No
        </label>
    </p>
    <?php
}


function save_infomation_status($post_id) {
    // 보안 확인
    if (!isset($_POST['save_infomation_status_nonce']) || !wp_verify_nonce($_POST['save_infomation_status_nonce'], 'save_infomation_status')) {
        return;
    }
    // 자동 저장인지 확인
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    // 사용자 권한 확인
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    // 입력 값 저장
    if (isset($_POST['yes_no_field'])) {
        update_post_meta($post_id, 'information_status', sanitize_text_field($_POST['yes_no_field']));
    }
}
add_action('save_post', 'save_infomation_status');








add_action('init', 'add_cors_headers');

function add_cors_headers() {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
}







run_wp_bct_ai();

require_once __DIR__ . '/bct-ai-extra.php';
