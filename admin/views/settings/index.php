<?php
if (!defined('ABSPATH'))
    exit;
$bctai_action = isset($_GET['action']) && !empty($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
// echo($bctai_action);
$flag = true;
$errors = '';
wp_enqueue_script('wp-color-picker');
wp_enqueue_style('wp-color-picker');

if (isset($_POST['bctai_submit'])) {
    check_admin_referer('bctai_setting_save');
    global $wpdb;
    $table = $wpdb->prefix . 'bctai';
    $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE name = %s", 'bctai_settings'));
    //var_dump($result);
    $newData = [];
    extract(\BCTAI\bctai_util_core()->sanitize_text_or_array_field($_POST['bctai_settings']));

    if (isset($_POST['bctai_azure_api_key'])) {
        update_option('bctai_azure_api_key', sanitize_text_field($_POST['bctai_azure_api_key']));
    }

    if (isset($_POST['bctai_azure_endpoint'])) {
        update_option('bctai_azure_endpoint', sanitize_text_field($_POST['bctai_azure_endpoint']));
    }

    if (isset($_POST['bctai_azure_deployment'])) {
        update_option('bctai_azure_deployment', sanitize_text_field($_POST['bctai_azure_deployment']));
    }

    if (isset($_POST['bctai_azure_embeddings'])) {
        update_option('bctai_azure_embeddings', sanitize_text_field($_POST['bctai_azure_embeddings']));
    }


    //파인콘 세팅 시작
    if (isset($_POST['bctai_pinecone_api']) && !empty($_POST['bctai_pinecone_api'])) {
        update_option('bctai_pinecone_api', sanitize_text_field($_POST['bctai_pinecone_api']));
    } else {
        delete_option('bctai_pinecone_api');
    }
    if (isset($_POST['bctai_pinecone_environment']) && !empty($_POST['bctai_pinecone_environment'])) {
        update_option('bctai_pinecone_environment', sanitize_text_field($_POST['bctai_pinecone_environment']));
    } else {
        delete_option('bctai_pinecone_environment');
    }
    // if (isset($_POST['bctai_pinecone_sv']) && !empty($_POST['bctai_pinecone_sv'])) {
    //     update_option('bctai_pinecone_sv', sanitize_text_field($_POST['bctai_pinecone_sv']));
    // } else {
    //     delete_option('bctai_pinecone_sv');
    // }
    if (isset($_POST['bctai_builder_enable']) && !empty($_POST['bctai_builder_enable'])) {
        update_option('bctai_builder_enable', 'yes');
    } else {
        delete_option('bctai_builder_enable');
    }
    if (isset($_POST['bctai_builder_types']) && is_array($_POST['bctai_builder_types']) && count($_POST['bctai_builder_types'])) {
        update_option('bctai_builder_types', \BCTAI\bctai_util_core()->sanitize_text_or_array_field($_POST['bctai_builder_types']));
    } else {
        delete_option('bctai_builder_types');
    }
    if (isset($_POST['bctai_instant_embedding']) && !empty($_POST['bctai_instant_embedding'])) {
        update_option('bctai_instant_embedding', \BCTAI\bctai_util_core()->sanitize_text_or_array_field($_POST['bctai_instant_embedding']));
    } else {
        update_option('bctai_instant_embedding', 'no');
    }
    //파인콘 세팅 끝

    //구글 세팅 시작
    
    if (isset($_POST['bctai_chat_enable_sale']) && !empty($_POST['bctai_chat_enable_sale'])) {
        update_option('bctai_chat_enable_sale', sanitize_text_field($_POST['bctai_chat_enable_sale']));
    } else {
        delete_option('bctai_chat_enable_sale');
    }
    if (isset($_POST['bctai_elevenlabs_hide_error']) && !empty($_POST['bctai_elevenlabs_hide_error'])) {
        update_option('bctai_elevenlabs_hide_error', sanitize_text_field($_POST['bctai_elevenlabs_hide_error']));
    } else {
        delete_option('bctai_elevenlabs_hide_error');
    }    
    if (isset($_POST['bctai_google_api_key']) && !empty($_POST['bctai_google_api_key'])) {
        update_option('bctai_google_api_key', sanitize_text_field($_POST['bctai_google_api_key']));
    } else {
        delete_option('bctai_google_api_key');
    }
    if (isset($_POST['bctai_elevenlabs_api']) && !empty($_POST['bctai_elevenlabs_api'])) {
        update_option('bctai_elevenlabs_api', sanitize_text_field($_POST['bctai_elevenlabs_api']));
    } else {
        delete_option('bctai_elevenlabs_api');
        delete_option('bctai_chat_to_speech');
    }
    //구글 세팅 끝

    //오픈라우터 세팅
    if (isset($_POST['bctai_chat_provider']) && !empty($_POST['bctai_chat_provider'])) {
        update_option('bctai_chat_provider', sanitize_text_field($_POST['bctai_chat_provider']));
    }
    if (isset($_POST['bctai_OpenRouter_APIkey']) && !empty($_POST['bctai_OpenRouter_APIkey'])) {
        update_option('bctai_OpenRouter_APIkey', sanitize_text_field($_POST['bctai_OpenRouter_APIkey']));
    }
    if (isset($_POST['wpaicg_openrouter_model']) && !empty($_POST['wpaicg_openrouter_model'])) {
        update_option('wpaicg_openrouter_model', sanitize_text_field($_POST['wpaicg_openrouter_model']));
    }


    //백터 저장소 세팅
    if (isset($_POST['bctai_vector_db_provider']) && !empty($_POST['bctai_vector_db_provider'])) {
        update_option('bctai_vector_db_provider', sanitize_text_field($_POST['bctai_vector_db_provider']));
    }
    if (isset($_POST['bctai_qdrant_api']) && !empty($_POST['bctai_qdrant_api'])) {
        update_option('bctai_qdrant_api', sanitize_text_field($_POST['bctai_qdrant_api']));
    }
    if (isset($_POST['bctai_qdrant_endpoint']) && !empty($_POST['bctai_qdrant_endpoint'])) {
        update_option('bctai_qdrant_endpoint', sanitize_text_field($_POST['bctai_qdrant_endpoint']));
    }
    if (isset($_POST['wpaicg_qdrant_default_collection']) && !empty($_POST['wpaicg_qdrant_default_collection'])) {
        update_option('wpaicg_qdrant_default_collection', sanitize_text_field($_POST['wpaicg_qdrant_default_collection']));
    }

    









    // For the provider:
    if (isset($_POST['bctai_provider'])) {
        update_option('bctai_provider', sanitize_text_field($_POST['bctai_provider']));

        // Check if the provider is Azure
        if ($_POST['bctai_provider'] === 'Azure') {
            // Fetch the current options
            $bctai_chat_shortcode_options = get_option('bctai_chat_shortcode_options', array());
            $bctai_chat_widget = get_option('bctai_chat_widget', array());

            // Set audio_enable and moderation to false
            $bctai_chat_shortcode_options['audio_enable'] = false;
            $bctai_chat_shortcode_options['moderation'] = false;

            // Set audio_enable to false for bctai_chat_widget
            $bctai_chat_widget['audio_enable'] = false;
            $bctai_chat_widget['moderation'] = false;

            // Update the option
            update_option('bctai_chat_shortcode_options', $bctai_chat_shortcode_options);
            update_option('bctai_chat_widget', $bctai_chat_widget);

            // Fetch all chatbots from wp_posts
            global $wpdb;
            $chatbots = $wpdb->get_results("SELECT ID, post_content FROM $wpdb->posts WHERE post_type='bctai_chatbot'");

            // Loop through each chatbot
            foreach ($chatbots as $chatbot) {
                $content = json_decode($chatbot->post_content, true); // Decode the post_content

                if (isset($content['moderation'])) {
                    $content['moderation'] = "0"; // Set moderation to false

                    // Update the wp_posts entry
                    $updated_content = wp_json_encode($content);
                    $wpdb->update(
                        $wpdb->posts,
                        array('post_content' => $updated_content),
                        array('ID' => $chatbot->ID)
                    );
                }
            }
        }
    }

    // if (!is_numeric($temperature) || floatval($temperature) < 0 || floatval($temperature) > 1) {
    //     $errors = sprintf(esc_html__('Please enter a valid temperature value between %d and %d.', 'bctai'), 0, 1);
    //     $flag = false;
    // }

    // if (!is_numeric($max_tokens) || floatval($max_tokens) < 64 || floatval($max_tokens) > 8000) {
    //     $errors = sprintf(esc_html__('Please enter a valid max token value between %d and %d.', 'bctai'), 64, 8000);
    //     $flag = false;
    // }

    // if (!is_numeric($top_p) || floatval($top_p) < 0 || floatval($top_p) > 1) {
    //     $errors = sprintf(esc_html__('Please enter a valid top p value between %d and %d.', 'bctai'), 0, 1);
    //     $flag = false;
    // }

    // if (!is_numeric($best_of) || floatval($best_of) < 1 || floatval($best_of) > 20) {
    //     $errors = sprintf(esc_html__('Please enter a valid best of value between %d and %d.', 'bctai'), 1, 20);
    //     $flag = false;
    // }

    // if (!is_numeric($frequency_penalty) || floatval($frequency_penalty) < 0 || floatval($frequency_penalty) > 2) {
    //     $errors = sprintf(esc_html__('Please enter a valid frequency penalty value between %d and %d.', 'bctai'), 0, 2);
    //     $flag = false;
    // }

    // if (!is_numeric($presence_penalty) || floatval($presence_penalty) < 0 || floatval($presence_penalty) > 2) {
    //     $errors = sprintf(esc_html__('Please enter a valid presence penalty value between %d and %d.', 'bctai'), 0, 2);
    //     $flag = false;
    // }

    if (empty($api_key)) {
        $errors = esc_html__('Please enter a valid API key.', 'bctai');
        $flag = false;
    }

    $data = [
        'name' => 'bctai_settings',
        'temperature' => $temperature,
        'max_tokens' => $max_tokens,
        'top_p' => $top_p,
        'best_of' => $best_of,
        'frequency_penalty' => $frequency_penalty,
        'presence_penalty' => $presence_penalty,
        'img_size' => $img_size,
        'api_key' => $api_key,
        'bctai_language' => $bctai_language,
        'bctai_modify_headings' => (isset($bctai_modify_headings) ? 1 : 0),
        'bctai_add_img' => (isset($bctai_add_img) ? 1 : 0),
        'bctai_add_tagline' => (isset($bctai_add_tagline) ? 1 : 0),
        'bctai_add_intro' => (isset($bctai_add_intro) ? 1 : 0),
        'bctai_add_faq' => (isset($bctai_add_faq) ? 1 : 0),
        'bctai_add_conclusion' => (isset($bctai_add_conclusion) ? 1 : 0),
        'bctai_add_keywords_bold' => (isset($bctai_add_keywords_bold) ? 1 : 0),
        'bctai_number_of_heading' => $bctai_number_of_heading,
        'bctai_heading_tag' => $bctai_heading_tag,
        'bctai_writing_style' => $bctai_writing_style,
        'bctai_writing_tone' => $bctai_writing_tone,
        'bctai_cta_pos' => $bctai_cta_pos,
        'added_date' => gmdate('Y-m-d H:i:s'),
        'modified_date' => gmdate('Y-m-d H:i:s'),
    ];

    if ($flag == true) {

        if (!empty($result->name)) {
            $wpdb->update(
                $table,
                $data,
                [
                    'name' => 'bctai_settings',
                ],
                [
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s'
                ],
                ['%s']
            );
        } else {
            $wpdb->insert($table, $data, [
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            ]);
        }
        $bctai_keys = array(
            'bctai_sleep_time',
            'bctai_chat_model',

        );
        foreach ($bctai_keys as $bctai_key) {
            if (isset($_POST[$bctai_key]) && !empty($_POST[$bctai_key])) {

                if ($bctai_key == 'bctai_editor_button_menus') {

                } else {
                    $posted_value = stripslashes_deep($_POST[$bctai_key]);
                    update_option($bctai_key, \BCTAI\bctai_util_core()->sanitize_text_or_array_field($posted_value));
                    // echo '<pre>'; print_r($posted_value); echo '</pre>';
                }
            } else {
                delete_option($bctai_key);

            }
        }
        $message = esc_html__('Records successfully updated!', 'bctai');
    }

}


$bctai_pinecone_api = get_option('bctai_pinecone_api', '');
$bctai_qdrant_api = get_option('bctai_qdrant_api', '');
$bctai_qdrant_endpoint = get_option('bctai_qdrant_endpoint', '');

//echo $bctai_pinecone_api;
$bctai_pinecone_sv = get_option('bctai_pinecone_sv', '');
$bctai_pinecone_environment = get_option('bctai_pinecone_environment', '');
$bctai_builder_types = get_option('bctai_builder_types', []);
$bctai_builder_enable = get_option('bctai_builder_enable', '');
$bctai_instant_embedding = get_option('bctai_instant_embedding', 'yes');
$bctai_pinecone_indexes = get_option('bctai_pinecone_indexes', '');
$bctai_pinecone_indexes = empty($bctai_pinecone_indexes) ? array() : json_decode($bctai_pinecone_indexes, true);
$bctai_pinecone_environments = array(
    'asia-northeast1-gcp' => 'GCP Asia-Northeast-1 (Tokyo)',
    'asia-northeast1-gcp-free' => 'GCP Asia-Northeast-1 Free (Tokyo)',
    'asia-northeast2-gcp' => 'GCP Asia-Northeast-2 (Osaka)',
    'asia-northeast2-gcp-free' => 'GCP Asia-Northeast-2 Free (Osaka)',
    'asia-northeast3-gcp' => 'GCP Asia-Northeast-3 (Seoul)',
    'asia-northeast3-gcp-free' => 'GCP Asia-Northeast-3 Free (Seoul)',
    'asia-southeast1-gcp' => 'GCP Asia-Southeast-1 (Singapore)',
    'asia-southeast1-gcp-free' => 'GCP Asia-Southeast-1 Free',
    'eu-west1-gcp' => 'GCP EU-West-1 (Ireland)',
    'eu-west1-gcp-free' => 'GCP EU-West-1 Free (Ireland)',
    'eu-west2-gcp' => 'GCP EU-West-2 (London)',
    'eu-west2-gcp-free' => 'GCP EU-West-2 Free (London)',
    'eu-west3-gcp' => 'GCP EU-West-3 (Frankfurt)',
    'eu-west3-gcp-free' => 'GCP EU-West-3 Free (Frankfurt)',
    'eu-west4-gcp' => 'GCP EU-West-4 (Netherlands)',
    'eu-west4-gcp-free' => 'GCP EU-West-4 Free (Netherlands)',
    'eu-west6-gcp' => 'GCP EU-West-6 (Zurich)',
    'eu-west6-gcp-free' => 'GCP EU-West-6 Free (Zurich)',
    'eu-west8-gcp' => 'GCP EU-West-8 (Italy)',
    'eu-west8-gcp-free' => 'GCP EU-West-8 Free (Italy)',
    'eu-west9-gcp' => 'GCP EU-West-9 (France)',
    'eu-west9-gcp-free' => 'GCP EU-West-9 Free (France)',
    'gcp-starter' => 'GCP Starter',
    'northamerica-northeast1-gcp' => 'GCP Northamerica-Northeast1',
    'northamerica-northeast1-gcp-free' => 'GCP Northamerica-Northeast1 Free',
    'southamerica-northeast2-gcp' => 'GCP Southamerica-Northeast2 (Toronto)',
    'southamerica-northeast2-gcp-free' => 'GCP Southamerica-Northeast2 Free (Toronto)',
    'southamerica-east1-gcp' => 'GCP Southamerica-East1 (Sao Paulo)',
    'southamerica-east1-gcp-free' => 'GCP Southamerica-East1 Free (Sao Paulo)',
    'us-central1-gcp' => 'GCP US-Central-1 (Iowa)',
    'us-central1-gcp-free' => 'GCP US-Central-1 Free (Iowa)',
    'us-east1-aws' => 'AWS US-East-1 (Virginia)',
    'us-east1-aws-free' => 'AWS US-East-1 Free (Virginia)',
    'us-east-1-aws' => 'AWS US-East-1 (Virginia)',
    'us-east-1-aws-free' => 'AWS US-East-1 Free (Virginia)',
    'us-east1-gcp' => 'GCP US-East-1 (South Carolina)',
    'us-east1-gcp-free' => 'GCP US-East-1 Free (South Carolina)',
    'us-east4-gcp' => 'GCP US-East-4 (Virginia)',
    'us-east4-gcp-free' => 'GCP US-East-4 Free (Virginia)',
    'us-west1-gcp' => 'GCP US-West-1 (N. California)',
    'us-west1-gcp-free' => 'GCP US-West-1 Free (N. California)',
    'us-west2-gcp' => 'GCP US-West-2 (Oregon)',
    'us-west2-gcp-free' => 'GCP US-West-2 Free (Oregon)',
    'us-west3-gcp' => 'GCP US-West-3 (Salt Lake City)',
    'us-west3-gcp-free' => 'GCP US-West-3 Free (Salt Lake City)',
    'us-west4-gcp' => 'GCP US-West-4 (Las Vegas)',
    'us-west4-gcp-free' => 'GCP US-West-4 Free (Las Vegas)'
);

$bctai_chat_enable_sale = get_option('bctai_chat_enable_sale', false);
$bctai_elevenlabs_hide_error = get_option('bctai_elevenlabs_hide_error', false);
$bctai_elevenlabs_api = get_option('bctai_elevenlabs_api', '');
$bctai_google_api_key = get_option('bctai_google_api_key', '');

global $wpdb;
$table = $wpdb->prefix . 'bctai';
$existingValue = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE name = %s", 'bctai_settings'), ARRAY_A);

// echo '<pre>'; print_r($existingValue); echo '</pre>';


?>
<!-- <script>
    jQuery(function () {
        jQuery("#bctai_tabs").tabs();
    });
</script> -->


<?php


if (!empty($message) && $flag == true) {
    echo "<h4 id='setting_message' style='color: green;'>" . esc_html($message) . "</h4>";
} else if($errors){
    echo "<h4 id='setting_message' style='color: red;'>" . esc_html($errors) . "</h4>";
}else{

}
$bctai_custom_models = get_option('bctai_custom_models', array());
$bctai_custom_models = array_merge(array('text-davinci-003', 'text-curie-001', 'text-babbage-001', 'text-ada-001'), $bctai_custom_models);
?>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        
        if (jQuery("#bctai_setting_message").text() != '') {
            alert("dldl");
            jQuery("#bctai_setting_message").delay(4000).slideUp(300);
        }
    });
</script>
<style>
    @media screen and (max-width: 2560px) and (max-height: 1440px) {}
</style>

<script>
    jQuery(document).ready(function ($) {
        var $sub_menu = $(".BCT_nav li");
        var $sub_menu_ul = $(".sub_menu_ul");

        $sub_menu.hover(function (x) {
            // console.log(x);
            $sub_menu.css("background", "#8040ad");
            $sub_menu_ul.css("display", "none");

            var idx = $(this).index();
            var section = $sub_menu_ul.eq(idx);
            var hov_menu = $sub_menu.eq(idx);

            hov_menu.css({
                "background": "#352f39",
                "transition": "background 0.3s ease",
            });
            section.css({
                "display": "flex",
                "transition": "display 0.3s ease",
            });

            section.hover(function () {
                $sub_menu.css("background", "#8040ad");
                $sub_menu_ul.css("display", "none");

                // console.log("asdfasdf");
                hov_menu.css("background", "#352f39");
                section.css("display", "flex");
            }, function () {
                $sub_menu.css("background", "#8040ad");
                $sub_menu_ul.css("display", "none");

                var idx = $(this).index();
                var section = $sub_menu_ul.eq(0);
                var hov_menu = $sub_menu.eq(0);

                hov_menu.css("background", "#352f39");
                section.css("display", "flex");

            });


        }, function () {
            $sub_menu.css("background", "#8040ad");
            $sub_menu_ul.css("display", "none");

            var idx = $(this).index();
            var section = $sub_menu_ul.eq(0);
            var hov_menu = $sub_menu.eq(0);

            hov_menu.css("background", "#352f39");
            section.css("display", "flex");
        });


    });
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<div><img src="<?php echo BCTAI_PLUGIN_URL . 'public/images/bctaichatbot_logo.png' ?>" style="margin: 15px 0px;"></div>

<div class="bctai_container">
    <div class="menu_wrap" style="overflow: hidden; width: 100%;">
        <ul class="BCT_nav">
            <li class="sub_menu" style="background:#352f39;"><a href="admin.php?page=bctaichat"><i class="fas fa-solid fa-house fa-lg" style="color:#ffffff;"></i>   <?php echo __('general Setting', 'bctai') ?></a></li>
            <li class="sub_menu"><a href="admin.php?page=Embeddings"><i class="fas fa-solid fa-quote-left fa-lg" style="color:#ffffff;"></i>   <?php echo __('Embeddings', 'bctai') ?></a></li>
            <li class="sub_menu"><a href="admin.php?page=Fine-tuning"><i class="fas fa-code fa-lg"style="color: #ffffff;"></i>   <?php echo __('Fine-tuning', 'bctai') ?> </a></li>
            <li class="sub_menu"><a href="admin.php?page=Audio"><i class="fa-solid fa-volume-high fa-lg"style="color: #ffffff;"></i>   <?php echo __('Audio', 'bctai') ?></a></li>
            <li class="sub_menu"><a href="admin.php?page=AI+ChatBot"><i class="fa-solid fa-comment-dots fa-lg"style="color: #ffffff;"></i>   <?php echo __('AI ChatBot', 'bctai') ?> </a></li>
            <li class="sub_menu"><a href="admin.php?page=Statistics"><i class="fas fa-solid fa-chart-simple fa-lg"style="color: #ffffff;"></i>  <?php echo __('Statistics', 'bctai') ?> </a></li>
        </ul>
        <ul class="sub_menu_ul" style="display:flex;">
            <li class="nav_object"><a href="admin.php?page=bctaichat" id ="tag_bold1"><?php echo __('Home', 'bctai') ?></a></li>
            <li class="nav_object"><a href="admin.php?page=bctaichat&action=Setting" id ="tag_bold2"><?php echo __('Setting', 'bctai') ?></a></li>
        </ul>

        <ul class="sub_menu_ul">
            <li class="nav_object"><a href="admin.php?page=Embeddings"><?php echo __('Content Builder', 'bctai') ?></a></li>
            <li class="nav_object"><a href="admin.php?page=Embeddings&action=entries"><?php echo __('Entries', 'bctai') ?></a></li>
            <li class="nav_object"><a href="admin.php?page=Embeddings&action=builder"><?php echo __('Index Builder', 'bctai') ?></a></li>
            <li class="nav_object">
                <a href="admin.php?page=Embeddings&action=pdf"><?php echo __('PDF','bctai')?>
                    <span style="width: 24px;height: 15px;background: #F53706 0% 0% no-repeat padding-box;border-radius: 6px;display: inline-block;line-height: 13px;">
                        <span style="font: normal normal normal 8px/11px Noto Sans KR;">PRO</span>
                    </span>
                </a>
            </li>
            <li class="nav_object">
                <a href="admin.php?page=Embeddings&action=kboard"><?php echo __('kboard','bctai')?>
                    <span style="width: 24px;height: 15px;background: #F53706 0% 0% no-repeat padding-box;border-radius: 6px;display: inline-block;line-height: 13px;">
                        <span style="font: normal normal normal 8px/11px Noto Sans KR;">PRO</span>
                    </span>
                </a>
            </li>
        </ul>

        <ul class="sub_menu_ul">
            <li class="nav_object"><a href="admin.php?page=Fine-tuning&action=manual"><?php echo __('Data Entry', 'bctai') ?></a></li>
            <li class="nav_object"><a href="admin.php?page=Fine-tuning&action=upload"><?php echo __('Upload', 'bctai') ?></a></li>
            <li class="nav_object"><a href="admin.php?page=Fine-tuning&action=data"><?php echo __('Data Converter', 'bctai') ?></a></li>
            <li class="nav_object"><a href="admin.php?page=Fine-tuning&action=files"><?php echo __('Datasets', 'bctai') ?></a></li>
            <li class="nav_object"><a href="admin.php?page=Fine-tuning&action=fine-tunes"><?php echo __('Trainings', 'bctai') ?></a></li>
        </ul>

        <ul class="sub_menu_ul">
            <li class="nav_object"><a href="admin.php?page=Audio"><?php echo __('Speech To Text', 'bctai') ?></a></li>
            <li class="nav_object"><a href="admin.php?page=Audio&action=Web_Speech_API"><?php echo __('Web Speech API', 'bctai') ?></a></li>
            <?php
                $STTEvaluation_status = get_option('STT Evaluation', 'false');

                if($STTEvaluation_status){

                ?>
                <li class="nav_object"><a href="admin.php?page=Audio&action=bct-tts-evaluation"><?php echo __('STT WER Test(Web API)','bctai')?></a></li>
                <li class="nav_object"><a href="admin.php?page=Audio&action=bct-STT-google"><?php echo __('STT WER Test(google)','bctai')?></a></li>
                <?php }?>
        </ul>

        <ul class="sub_menu_ul">
            <li class="nav_object"><a href="admin.php?page=AI+ChatBot"><?php echo __('Design','bctai')?></a></li>
            <li class="nav_object"><a href="admin.php?page=AI+ChatBot&action=Settings"><?php echo __('Settings','bctai')?></a></li>
            <li class="nav_object"><a href="admin.php?page=AI+ChatBot&action=logs"><?php echo __('Logs','bctai')?></a></li>
        </ul>

        <ul class="sub_menu_ul">
            <li class="nav_object"><a href="admin.php?page=Statistics"><?php echo __('Dashboard', 'bctai') ?></a></li>
        </ul>

        
    </div>




        <form action="" method="post">
            <?php
            wp_nonce_field('bctai_setting_save');
            ?>
            

            <div id="fs_account">
            <?php
            if (empty($bctai_action)):
                include __DIR__ . '/Home.php';
                echo "<style>#tag_bold1 {font-size: 14px;font-weight: normal;color: #fff;}</style>";
            elseif ($bctai_action == 'Setting'):
                include __DIR__ . '/ai.php';
                echo "<style>#tag_bold2 {font-size: 14px;font-weight: normal;color: #fff;}</style>";
            elseif ($bctai_action == 'Pinecone_Setting'):
                include __DIR__ . '/settings.php';
                echo "<style>#tag_bold3 {font-size: 14px;font-weight: normal;color: #fff;}</style>";
            elseif ($bctai_action == 'GoogleTTS_Setting'):
                include __DIR__ . '/bctai_chat_settings.php';
                echo "<style>#tag_bold4 {font-size: 14px;font-weight: normal;color: #fff;}</style>";
            endif;
            ?>
            </div>


        </form>
</div>

