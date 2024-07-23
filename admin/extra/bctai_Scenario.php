<?php
if ( ! defined( 'ABSPATH' ) ) exit;
global $wpdb;
// $errors = false;
// $message = false;
if ( isset( $_POST['bctai_submit'] ) ) {
    //echo $_POST['bctai_submit'];
    //echo $_POST['bctai_Scenario_category'];
    //echo '<pre>'; print_r($_POST['bctai_Scenario_category']); echo '</pre>';
    




    check_admin_referer('bctai_chat_widget_save');    

    if( isset($_POST['bctai_chat_temperature']) && ( !is_numeric( $_POST['bctai_chat_temperature'] ) || floatval( $_POST['bctai_chat_temperature'] ) < 0 || floatval( $_POST['bctai_chat_temperature'] ) > 1 )) {
        $errors = 'Please enter a valid temperature value between 0 and 1.';
    }
    if( isset($_POST['bctai_chat_max_tokens']) && ( !is_numeric( $_POST['bctai_chat_max_tokens'] ) || floatval( $_POST['bctai_chat_max_tokens'] ) < 64 || floatval( $_POST['bctai_chat_max_tokens'] ) > 8000 )) {
        $errors = 'Please enter a valid max token value between 64 and 8000.';
    }
    if( isset($_POST['bctai_chat_top_p']) && ( !is_numeric( $_POST['bctai_chat_top_p'] ) || floatval( $_POST['bctai_chat_top_p'] ) < 0 || floatval( $_POST['bctai_chat_top_p'] ) > 1 )) {
        $errors = 'Please enter a valid top p value between 0 and 1.';
    }
    if( isset($_POST['bctai_chat_best_of']) && ( !is_numeric( $_POST['bctai_chat_best_of'] ) || floatval( $_POST['bctai_chat_best_of'] ) < 1 || floatval( $_POST['bctai_chat_top_p'] ) > 20 )) {
        $errors = 'Please enter a valid best of value between 1 and 20.';
    }
    if( isset($_POST['bctai_chat_frequency_penalty']) && ( !is_numeric( $_POST['bctai_chat_frequency_penalty'] ) || floatval( $_POST['bctai_chat_frequency_penalty'] ) < 0 || floatval( $_POST['bctai_chat_frequency_penalty'] ) > 2 )) {
        $errors = 'Please enter a valid frequency penalty value between 0 and 2.';
    }
    if (isset($_POST['bctai_chat_presence_penalty']) && ( !is_numeric( $_POST['bctai_chat_presence_penalty'] ) || floatval( $_POST['bctai_chat_presence_penalty'] ) < 0 || floatval( $_POST['bctai_chat_presence_penalty'] ) > 2 ) ){
        $errors = 'Please enter a valid presence penalty value between 0 and 2.';
    }
    if(!$errors) {
        $bctai_keys = array(
            // '_bctai_chatbox_you',
            // '_bctai_ai_thinking',
            // '_bctai_typing_placeholder',
            // '_bctai_chatbox_welcome_message',
            // '_bctai_chatbox_ai_name',
            // 'bctai_chat_widget',
            // 'bctai_chat_model',
            // 'bctai_chat_language',
            //'bctai_chat_temperature',
            'bctai_Scenario_category'
            // 'bctai_chat_max_tokens',
            // 'bctai_chat_top_p',
            // 'bctai_chat_best_of',
            // 'bctai_chat_frequency_penalty',
            // 'bctai_chat_presence_penalty',
            // 'bctai_chat_no_answer',
            // 'bctai_conversation_cut',
            // 'bctai_chat_embedding',
            // 'bctai_chat_embedding_type',
            // 'bctai_chat_embedding_top',
            // 'bctai_chat_no_answer',
            // 'bctai_chat_addition',
            // 'bctai_chat_addition_text',
            // 'bctai_chat_status'
        );
        foreach($bctai_keys as $bctai_key) {
            if(isset($_POST[$bctai_key]) && !empty($_POST[$bctai_key])) {
                // Strip slashes from the POST data
                $posted_value = stripslashes_deep($_POST[$bctai_key]);
                update_option($bctai_key, \BCTAI\bctai_util_core()->sanitize_text_or_array_field($posted_value));
            }   
            else {
                delete_option($bctai_key);
            }
        }
        if(isset($_POST['bctai_azure_model'])){
            $new_deployment_name = sanitize_text_field($_POST['bctai_azure_model']);
            update_option('bctai_azure_deployment', $new_deployment_name);
        }
        $message = "Setting saved successfully";
    }
}



wp_enqueue_script('wp-color-picker');
wp_enqueue_style('wp-color-picker');




$table = $wpdb->prefix . 'bctai';
$existingValue = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE name = %s", 'bctai_settings' ), ARRAY_A );
$bctai_chat_widget = get_option('bctai_chat_widget',[]);
// echo '<pre>'; print_r($bctai_chat_widget); echo '</pre>';
$bctai_chat_status = get_option('bctai_chat_status','noachive');
//tone,profession

//디자인 활성화
$bctai_chat_design = get_option('bctai_chat_design',[]);
// echo '<pre>'; print_r($bctai_chat_design); echo '</pre>';
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




//parameters
$bctai_custom_models = get_option('bctai_custom_models',array());
$bctai_custom_models = array_merge(array('text-davinci-003','text-curie-001','text-babbage-001','text-ada-001'),$bctai_custom_models);

$bctai_chat_temperature = get_option('bctai_chat_temperature',1);
$bctai_chat_max_tokens = get_option('bctai_chat_max_tokens',1500);
$bctai_chat_top_p = get_option('bctai_chat_top_p',0.001);
$bctai_chat_best_of = get_option('bctai_chat_best_of',1);
$bctai_chat_frequency_penalty = get_option('bctai_chat_frequency_penalty',0);
$bctai_chat_presence_penalty = get_option('bctai_chat_presence_penalty',0);
//custom text
$bctai_footer_text = isset($bctai_chat_widget['footer_text']) && !empty($bctai_chat_widget['footer_text']) ? $bctai_chat_widget['footer_text'] : '';
//context
$bctai_chat_remember_conversation = isset($bctai_chat_widget['remember_conversation']) && !empty($bctai_chat_widget['remember_conversation']) ? $bctai_chat_widget['remember_conversation'] : 'yes';
$bctai_conversation_cut = get_option('bctai_conversation_cut', 10);
$bctai_user_aware = isset($bctai_chat_widget['user_aware']) && !empty($bctai_chat_widget['user_aware']) ? $bctai_chat_widget['user_aware'] : 'no';
$bctai_chat_content_aware = isset($bctai_chat_widget['content_aware']) && !empty($bctai_chat_widget['content_aware']) ? $bctai_chat_widget['content_aware'] : 'yes';
$bctai_chat_embedding = get_option('bctai_chat_embedding',false);
$bctai_chat_embedding_type = get_option('bctai_chat_embedding_type',false);



$bctai_chat_embedding_top = get_option('bctai_chat_embedding_top',false);
$bctai_pinecone_api = get_option('bctai_pinecone_api','');
$bctai_pinecone_environment = get_option('bctai_pinecone_environment','');
$bctai_embedding_field_disabled = empty($bctai_pinecone_api) || empty($bctai_pinecone_environment) ? true : false;

$bctai_pinecone_indexes = get_option('bctai_pinecone_indexes', '');
$bctai_pinecone_indexes = empty($bctai_pinecone_indexes) ? array() : json_decode($bctai_pinecone_indexes,true);
//voicechat
$bctai_audio_enable = isset($bctai_chat_widget['audio_enable']) ? $bctai_chat_widget['audio_enable'] : false;
$bctai_mic_color = isset($bctai_chat_widget['mic_color']) ? $bctai_chat_widget['mic_color'] : '#222';
$bctai_stop_color = isset($bctai_chat_widget['stop_color']) ? $bctai_chat_widget['stop_color'] : '#f00';
$bctai_chat_to_speech = isset($bctai_chat_widget['chat_to_speech']) ? $bctai_chat_widget['chat_to_speech'] : false;
$bctai_elevenlabs_voice = isset($bctai_chat_widget['elevenlabs_voice']) ? $bctai_chat_widget['elevenlabs_voice'] : '';

$bctai_elevenlabs_api = get_option('bctai_elevenlabs_api', '');

$bctai_chat_voice_service = isset($bctai_chat_widget['voice_service']) ? $bctai_chat_widget['voice_service'] : 'google';
$bctai_google_voices = get_option('bctai_google_voices',[]);
$bctai_google_api_key = get_option('bctai_google_api_key', '');
//token handling
$bctai_user_limited = isset($bctai_chat_widget['user_limited']) ? $bctai_chat_widget['user_limited'] : false;
$bctai_user_tokens = isset($bctai_chat_widget['user_tokens']) ? $bctai_chat_widget['user_tokens'] : 0;
$bctai_roles = wp_roles()->get_names();
$bctai_chat_widget['role_limited'] = isset($bctai_chat_widget['role_limited']) && !empty($bctai_chat_widget['role_limited']) ? $bctai_chat_widget['role_limited'] : false;
$bctai_chat_widget['limited_roles'] = isset($bctai_chat_widget['limited_roles']) && !empty($bctai_chat_widget['limited_roles']) ? $bctai_chat_widget['limited_roles'] : array();
$bctai_guest_limited = isset($bctai_chat_widget['guest_limited']) ? $bctai_chat_widget['guest_limited'] : false;
$bctai_guest_tokens = isset($bctai_chat_widget['guest_tokens']) ? $bctai_chat_widget['guest_tokens'] : 0;
$bctai_limited_message = isset($bctai_chat_widget['limited_message']) && !empty($bctai_chat_widget['limited_message']) ? $bctai_chat_widget['limited_message'] : 'You have reached your token limit.';
$bctai_reset_limit = isset($bctai_chat_widget['reset_limit']) ? $bctai_chat_widget['reset_limit'] : 0;
$bctai_include_footer = (isset($bctai_chat_widget['footer_text']) && !empty($bctai_chat_widget['footer_text'])) ? 5 : 0;
//log
$bctai_save_logs = isset($bctai_chat_widget['save_logs']) && !empty($bctai_chat_widget['save_logs']) ? $bctai_chat_widget['save_logs'] : false;
$bctai_log_notice = isset($bctai_chat_widget['log_notice']) && !empty($bctai_chat_widget['log_notice']) ? $bctai_chat_widget['log_notice'] : false;
$bctai_log_notice_message = isset($bctai_chat_widget['log_notice_message']) && !empty($bctai_chat_widget['log_notice_message']) ? $bctai_chat_widget['log_notice_message'] : 'Please note that your conversations will be recorded.';

//디자인이 기본일때

//echo '<pre>'; print_r($bctai_chat_widget); echo '</pre>';


//시나리오 카테고리
$bctai_Scenario_category = get_option('bctai_Scenario_category',[]);
echo '<pre>'; print_r($bctai_Scenario_category); echo '</pre>';


//$first_category =isset($bctai_Scenario_category[0]) && !empty($bctai_Scenario_category[0]) ? $bctai_Scenario_category[0] : '첫번째 카테고리';


//echo '<pre>'; print_r($first_category); echo '</pre>';

?>


<style>
    
    #fs_account{
        min-height: 1000px;
    }
    
    
    
    .bctai_widget_open .bctai_chat_widget_content {
        height: 690px;
        width: 400px;
    }

    .high {
        height: 690px;
    }
    .bctai_chat_widget_content {
        height: 690px;
        width: 400px;
        position: absolute;
        bottom: calc(100% + 15px);
    }

    .bctai_widget_open .bctai_chat_widget_content .bctai-chatbox {
        top: -30px;
    }
    .bctai_chat_widget_content .bctai-chatbox {
        position: absolute;
        top: 103%;
        left: 0;
        transition: top 300ms cubic-bezier(0.17, 0.04, 0.03, 0.94);
    }

    .bctai_chat_widget .bctai_toggle {
        margin-left:10px;
        cursor: pointer;
    }
    .bctai_chat_widget .bctai_toggle img {
        width: 75px;
        height: 75px;
    }
    

    .bctai_chat_widget{
        position: absolute;
        left: 0px;
        top: 730px;
    }
    .select_style{
    height: 50px;
    width: 90%;
    border: 0px;
    margin: 0px 10px;
    
    }
    
</style>

<?php
$bctai_chat_model = get_option('bctai_chat_model','');


$bctai_chat_language = get_option('bctai_chat_language', '');
if ( !empty($errors)) {
    echo "<h4 id='setting_message' style='color: red;'>" . esc_html( $errors ) . "</h4>";
} elseif(!empty($message)) {
    echo "<h4 id='setting_message' style='color: green;'>" . esc_html( $message ) . "</h4>";
}
?>

<h1 style="font: normal normal 900 24px/35px Noto Sans KR; margin:0px;"><?php echo __('Scenario', 'bctai') ?></h1>


<div class="bctai-faq-item-default" style="display: none" >
    <div style="display:flex;"draggable="true">
        <input id="bctai_Scenario_category_index" type="text" name="bctai_Scenario_category[__INDEX__]" style="width:70%; height: 50px;">
        <div style="display:flex; width:30%;">
            <button type="button" style="width: 50%;">삭제</button>
            <button type="button" style="width: 50%;">관리</button>
        </div>
    </div>
</div>



<form action="" method="post">


<?php wp_nonce_field('bctai_chat_widget_save')?>

    
<div style="display: flex;">


    <div class="setting_wrap" style="width: 70%; display:flex;">

        <div class="bctai-faq-list" style="border: 1px solid gray;width: 400px;height: 700px;position:relative;">

            

            <?php 
            if ($bctai_Scenario_category) {
                foreach ($bctai_Scenario_category as $index => $item) {
            ?>
            <div id="test"style="display:flex;">
                <input id="bctai_Scenario_category_index" type="text" name="bctai_Scenario_category[<?php echo $item; ?>]" value="<?php echo esc_html( $item ); ?>" style="width:70%; height: 50px;">
                <div style="display:flex; width:30%;">

                    <button type="button" style="width: 50%;" id="delete">삭제</button>
                    <button type="button" style="width: 50%;" id="control">관리</button>
                </div>
            </div>


            
                    
            <?php
                }
            }
            ?>

            <button type="button" class="button button-primary btn-add-faq"style="width:100%;background: #9f89af;border-color: #9f89af;position: absolute; bottom: 0;">Add More</button>
        </div>


        <div class="bctai-faq-list" style="border: 1px solid gray;width: 400px;height: 700px;position:relative; margin:0px 10px; display:none;">

            

            <?php 
            if ($bctai_Scenario_category) {
                foreach ($bctai_Scenario_category as $index => $item) {
            ?>
            


            <?php
                }
            }
            ?>

            <button type="button" class="button button-primary btn-add-faq"style="width:100%;background: #9f89af;border-color: #9f89af;position: absolute; bottom: 0;">Add More</button>
        </div>


       
        

        




        

    </div>




    <!--챗박스-->
    <div class="bctai-chatbox-preview-box" style="width: 30%;position: relative;">
        <?php include __DIR__ . '/bctai_chat_widget.php';?>
    </div>
</div>

<button class="button button-primary" value="bct-submit" name="bctai_submit" style="width: 187px;height: 60px;background: #8040ad;border-radius: 16px;border: 0px;margin-top: 30px;"><?php echo __('SAVE', 'bctai') ?></button>

</form>  





            


<script>
    jQuery(document).ready(function($) {

        $('#test button:first-of-type').on('click', function() {
            //alert('asd;lkf');
            $(this).closest('#test').remove();
        });
        
        $('#test button:nth-of-type(2)').on('click', function() {
            alert('두 번째 버튼이 클릭되었습니다!');
        }); 

        




        var bctScenarioList = $('.bctai-faq-list');
        var bctai_Scenario_category_index = '#bctai_Scenario_category_index';
        let ScenarioIndex = bctScenarioList.find(bctai_Scenario_category_index).length;

        $('.btn-add-faq').click(function () {
            let newFaqItem = $('.bctai-faq-item-default').html().replace(/__INDEX__/g, ScenarioIndex);
            $('.bctai-faq-list').append(newFaqItem);
            ScenarioIndex++;
        });





        function bctaiLoading(btn){
            btn.attr('disabled','disabled');
            if(btn.find('.spinner').length === 0){
                btn.append('<span class="bctai-spinner spinner"></span>');
            }
            btn.find('.spinner').css('visibility','unset');
        }
        function bctaiRmLoading(btn){
            btn.removeAttr('disabled');
            btn.find('.spinner').remove();
        }




        $('.bctai_sync_finetunes').click(function (){
            var btn = $(this);
            $.ajax({
                url: bctai_ajax_url,
                data: {action: 'bctai_fetch_finetunes','nonce': '<?php echo wp_create_nonce('bctai-ajax-nonce')?>'},
                dataType: 'JSON',
                type: 'POST',
                beforeSend: function () {
                    bctaiLoading(btn);
                },
                success: function (res){
                    //alert(res.msg);
                    bctaiRmLoading(btn);
                    if(res.status === 'success'){
                        window.location.reload();
                    }
                    else{
                        alert(res.msg);
                    }
                },
                error: function (){
                    bctaiRmLoading(btn);
                    alert('Something went wrong');
                }
            })
        })




        let bctai_google_voices = <?php echo wp_json_encode($bctai_google_voices)?>;
        let bctai_elevenlab_api = '<?php echo esc_html($bctai_elevenlabs_api)?>';
        let bctai_google_api_key = '<?php  echo $bctai_google_api_key?>';
        let bctai_roles = <?php echo wp_kses_post(wp_json_encode($bctai_roles))?>;
        
        //common
        $('.bctai-collapse-title').click(function() {
            if(!$(this).hasClass('bctai-collapse-active')) {
                $('.bctai-collapse').removeClass('bctai-collapse-active');
                $('.bctai-collapse-title span').html('+');
                $(this).find('span').html('-');
                $(this).parent().addClass('bctai-collapse-active');
            }
        });
        $('.bctai_modal_close_second').click(function (){
            $('.bctai_modal_close_second').closest('.bctai_modal_second').hide();
            $('.bctai-overlay-second').hide();
        });
        $('.bctai-chatbox-preview-box > .bctai_chat_widget').addClass('bctai_widget_open');
        $('.bctai-chatbox-preview-box .bctai_toggle').addClass('bctai_widget_open');
        function bctaiChangeAvatarRealtime() {
            var bctai_user_avatar_check = $('input[name=_bctai_chatbox_you]').val()+':';
            var bctai_ai_avatar_check = $('input[name=_bctai_chatbox_ai_name]').val()+':';
            //alert($('.bctaichat_use_avatar').prop('checked'));
            if($('.bctaichat_use_avatar').prop('checked')) {
                bctai_user_avatar_check = '<img src="<?php echo get_avatar_url(get_current_user_id())?>" height="40" width="40">';
                bctai_ai_avatar_check = '<?php echo esc_html(BCTAI_PLUGIN_URL) . 'admin/images/DefaltIMG.png';?>';
                if($('.bctai_chatbox_avatar_custom').prop('checked') && $('.bctai_chatbox_avatar img').length) {
                    bctai_ai_avatar_check = $('.bctai_chatbox_avatar img').attr('src');
                    //alert(bctai_ai_avatar_check);
                }
                bctai_ai_avatar_check = '<img src="'+bctai_ai_avatar_check+'" height="40" width="40">';
            }

            $('.bctai-chat-ai-message').each(function (idx, item) {
                $(item).find('strong').html(bctai_ai_avatar_check);
            });
            $('.bctai-chat-user-message').each(function (idx, item) {
                $(item).find('strong').html(bctai_user_avatar_check);
            });
        }
        $('input[name=_bctai_chatbox_you],input[name=_bctai_chatbox_ai_name]').on('input', function() {
            bctaiChangeAvatarRealtime();
        });
        $('.bctaichat_use_avatar, .bctai_chatbox_avatar_default, .bctai_chatbox_avatar_custom').on('click', function () {
            bctaiChangeAvatarRealtime();
        });

        $('.design_name').click(function() {
            var targetInputId = $(this).attr('for');
            $('#' + targetInputId).prop('checked', true);
            select_design(targetInputId);
        });


        $('.select_design').click(function(){
            var selectedValue = $(this).attr('id');
            select_design(selectedValue);
        });

        function select_design(tmp){
            if(tmp == "Basic") {
                $('.btn-remove, .btn-fullscreen, .btn-mail').css({'background': "#8040ad"});
                $('.chatbot-header').css({'background': "#8040ad"});
                $('.chatbot-header h2').css({'color': "#ffffff"});
                $('.chatbot-contents .message.right .bubble').css({'background': "#8040ad"});
                $('.chatbot-contents .message.right .bubble').css({'color': "#FFFFFF"});
                $('.btn-send_widget').css({'background': "#8040ad"});
                $('.btn-send_widget').css({'color': "#FFFFFF"});
                $('.bctai-chat-widget-typing').css({'background': "#F1F1F1"});
                $('.bctai-chat-widget-typing').css({'color': "#8040ad"});
                $('.chatbot-contents .message.left .name').css({'color': "#352F39"});
                $('.chatbot-contents').css({'background': "#ffffff"});
                $('.chatbot-contents .message.left .bubble').css({'background': "#f0f0f1"});
                $('.bctai-chatbox .bctai-mic-icon').css({'color': "#8040ad"});
                $('.chatbot-footer').css({'background': "#F1F1F1"});
                applyCustomStyle();
            }else if (tmp == "Mono"){
                $('.btn-remove, .btn-fullscreen, .btn-mail').css({'background': "#352f39"});
                $('.chatbot-header').css({'background': "#352f39"});
                $('.chatbot-header h2').css({'color': "#ffffff"});
                $('.chatbot-contents .message.right .bubble').css({'background': "#4F4D5F"});
                $('.chatbot-contents .message.right .bubble').css({'color': "#FFFFFF"});
                $('.btn-send_widget').css({'background': "#352F39"});
                $('.btn-send_widget').css({'color': "#FFFFFF"});
                $('.bctai-chat-widget-typing').css({'background': "#F1F1F1"});
                $('.bctai-chat-widget-typing').css({'color': "#352F39"});
                $('.chatbot-contents .message.left .name').css({'color': "#352F39"});
                $('.chatbot-contents').css({'background': "#ffffff"});
                $('.chatbot-contents .message.left .bubble').css({'background': "#F1F1F1"});
                $('.bctai-chatbox .bctai-mic-icon').css({'color': "#352F39"});
                $('.chatbot-footer').css({'background': "#F1F1F1"});
                applyCustomStyle();
            }else if (tmp == "Dark"){
                $('.btn-remove, .btn-fullscreen, .btn-mail').css({'background': "#352f38"});
                $('.chatbot-header').css({'background': "#352f38"});
                $('.chatbot-header h2').css({'color': "#ffffff"});
                $('.chatbot-contents .message.right .bubble').css({'background': "#FFFFFF"});
                $('.chatbot-contents .message.right .bubble').css({'color': "#352F39"});
                $('.btn-send_widget').css({'background': "#FFFFFF"});
                $('.btn-send_widget').css({'color': "#352F39"});
                $('.bctai-chat-widget-typing').css({'background': "#352F39"});
                $('.bctai-chat-widget-typing').css({'color': "#FFFFFF"});
                $('.chatbot-contents .message.left .name').css({'color': "#F1F1F1"});
                $('.chatbot-contents').css({'background': "#575757"});
                $('.chatbot-contents .message.left .bubble').css({'background': "#F1F1F1"});
                $('.bctai-chatbox .bctai-mic-icon').css({'color': "#FFFFFF"});
                $('.chatbot-footer').css({'background': "#352F39"});
                applyCustomStyle();
            }else if (tmp == "Light_Grey"){
                $('.btn-remove, .btn-fullscreen, .btn-mail').css({'background': "#F1F1F1"});
                $('.chatbot-header').css({'background': "#F1F1F1"});
                $('.chatbot-header h2').css({'color': "#352F39"});
                $('.chatbot-contents .message.right .bubble').css({'background': "#4F4D5F"});
                $('.chatbot-contents .message.right .bubble').css({'color': "#FFFFFF"});
                $('.btn-send_widget').css({'background': "#352F39"});
                $('.btn-send_widget').css({'color': "#FFFFFF"});
                $('.bctai-chat-widget-typing').css({'background': "#F1F1F1"});
                $('.bctai-chat-widget-typing').css({'color': "#352F39"});
                $('.chatbot-contents .message.left .name').css({'color': "#4F4D5F"});
                $('.chatbot-contents').css({'background': "#ffffff"});
                $('.chatbot-contents .message.left .bubble').css({'background': "#F1F1F1"});
                $('.bctai-chatbox .bctai-mic-icon').css({'color': "#352F39"});
                $('.chatbot-footer').css({'background': "#F1F1F1"});
                applyCustomStyle();
            }else if (tmp == "Electric_Orange"){
                $('.btn-remove, .btn-fullscreen, .btn-mail').css({'background': "#F53706"});
                $('.chatbot-header').css({'background': "#F53706"});
                $('.chatbot-header h2').css({'color': "#ffffff"});
                $('.chatbot-contents .message.right .bubble').css({'background': "#C14D2F"});
                $('.chatbot-contents .message.right .bubble').css({'color': "#FFFFFF"});
                $('.btn-send_widget').css({'background': "#352F39"});
                $('.btn-send_widget').css({'color': "#FFFFFF"});
                $('.bctai-chat-widget-typing').css({'background': "#F1F1F1"});
                $('.bctai-chat-widget-typing').css({'color': "#352F39"});
                $('.chatbot-contents .message.left .name').css({'color': "#4F4D5F"});
                $('.chatbot-contents').css({'background': "#ffffff"});
                $('.chatbot-contents .message.left .bubble').css({'background': "#F1F1F1"});
                $('.bctai-chatbox .bctai-mic-icon').css({'color': "#352F39"});
                $('.chatbot-footer').css({'background': "#F1F1F1"});
                applyCustomStyle();
            }else if (tmp == "Azure"){
                $('.btn-remove, .btn-fullscreen, .btn-mail').css({'background': "#0080FF"});
                $('.chatbot-header').css({'background': "#0080FF"});
                $('.chatbot-header h2').css({'color': "#ffffff"});
                $('.chatbot-contents .message.right .bubble').css({'background': "#446C94"});
                $('.chatbot-contents .message.right .bubble').css({'color': "#FFFFFF"});
                $('.btn-send_widget').css({'background': "#352F39"});
                $('.btn-send_widget').css({'color': "#FFFFFF"});
                $('.bctai-chat-widget-typing').css({'background': "#F1F1F1"});
                $('.bctai-chat-widget-typing').css({'color': "#352F39"});
                $('.chatbot-contents .message.left .name').css({'color': "#4F4D5F"});
                $('.chatbot-contents').css({'background': "#ffffff"});
                $('.chatbot-contents .message.left .bubble').css({'background': "#F1F1F1"});
                $('.bctai-chatbox .bctai-mic-icon').css({'color': "#352F39"});
                $('.chatbot-footer').css({'background': "#F1F1F1"});
                applyCustomStyle();
            }else if(tmp =="Irish_Green"){
                $('.btn-remove, .btn-fullscreen, .btn-mail').css({'background': "#00A800"});
                $('.chatbot-header').css({'background': "#00A800"});
                $('.chatbot-header h2').css({'color': "#ffffff"});
                $('.chatbot-contents .message.right .bubble').css({'background': "#50A15B"});
                $('.chatbot-contents .message.right .bubble').css({'color': "#FFFFFF"});
                $('.btn-send_widget').css({'background': "#352F39"});
                $('.btn-send_widget').css({'color': "#FFFFFF"});
                $('.bctai-chat-widget-typing').css({'background': "#F1F1F1"});
                $('.bctai-chat-widget-typing').css({'color': "#352F39"});
                $('.chatbot-contents .message.left .name').css({'color': "#4F4D5F"});
                $('.chatbot-contents').css({'background': "#ffffff"});
                $('.chatbot-contents .message.left .bubble').css({'background': "#F1F1F1"});
                $('.bctai-chatbox .bctai-mic-icon').css({'color': "#352F39"});
                $('.chatbot-footer').css({'background': "#F1F1F1"});
                applyCustomStyle();
            }else if(tmp =="custom_chatbot_style"){
                applyCustomStyle();
            }


        }


        



        function bctaiUpdateRealtime() {
            let fontsize = $('.bctai_chat_widget_font_size').val();
            let Userfontcolor = $('.bctaichat_User_font_color').iris('color');
            let bgcolor = $('.bctaichat_bg_color').iris('color');
            let inputbg = $('.bctaichat_input_color').iris('color');
            let inputborder = $('.bctaichat_input_border').iris('color');
            let buttoncolor = $('.bctaichat_send_color').iris('color');
            let userbg = $('.bctaichat_user_color').iris('color');
            let aibg = $('.bctaichat_ai_color').iris('color');
            let useavatar = $('.bctaichat_use_avatar').val();
            let width = $('.bctai_chat_widget_width').val();
            let height = $('.bctai_chat_widget_height').val();            
            let mic_color = $('.bctai_chat_widget_mic_color').iris('color');
            let AI_fontcolor = $('.AI_fontcolor').iris('color');

            
            let Input_font_Color=$('.Input_font_Color').iris('color');
            $('.bctai-mic-icon').css('color', mic_color);
            let footernote = $('.bctai-footer-note').val();
            let footerheight = 0;

            let Header_icon_color = $('.Header_icon_color').iris('color');

            if(footernote === '') {
                footerheight = 18;
                $('.bctai-chatbox-footer').hide();
                $('.bctai-chatbox-type').css('padding','5px');
            }
            else {
                $('.bctai-chatbox-type').css('padding','5px 5px 0 5px');
                $('.bctai-chatbox-footer').show();
                $('.bctai-chatbox-footer').html(footernote);
            }
            if($('.bctai_chat_widget_audio').prop('checked')){
                $('.bctai-mic-icon').show();
            }
            else{
                $('.bctai-mic-icon').hide();
            }





            $(' .chatbot-header, .btn-home, .btn-remove, .btn-menual,.btn-fullscreen, .chatbot-contents .message.right .bubble').css({
                'background-color': userbg
            });
            $('.chatbot-contents .message.right .bubble').css({
                'font-size': fontsize+'px',
                'color': Userfontcolor,
            })
           
            $('.chatbot-contents .message.left .bubble').css({
                'background-color': aibg
            });
            $('.chatbot-contents').css({
                'background-color': bgcolor
            });
            $('.bctai-chat-widget-typing').css({
                'border-color': inputborder,
                'background-color': inputbg,
                'color' : Input_font_Color
            });
            $('.chatbot-contents .message.left .bubble').css({
                'font-size': fontsize+'px',
                'color': AI_fontcolor
            });
            // $('.chatbot-footer .btn-send_widget').css({
            //     'color': buttoncolor,
            //     'background-color': inputborder
            // });
            $('.chatbot-header i, .chatbot-header h2').css({
                'color': Header_icon_color
            });
            
            







            $('.bctai-chatbox-send').css('color',buttoncolor);
            if(width === '' || parseInt(width) === 0){
                width = 350;
            }
            if(height === '' || parseInt(height) === 0){
                height = 400;
            }
            $('.bctai-chatbox-preview-box').height((parseInt(height)+100)+'px');
            $('.bctai_widget_open .bctai_chat_widget_content').css({
                'height': height+'px',
                'width': width+'px',
            });
            $('.bctai_chat_widget_content .bctai-chatbox-content').css({
                'height': (height - 58 + footerheight)+'px'
            });
            $('.bctai_chat_widget_content .bctai-chatbox-content ul').css({
                'height': (height - 82 + footerheight)+'px'
            });
            $('.high').css({
                'height':height+'px'
            });
            // $('.chatbot-footer').css({
            //     'width': (width-42)+'px',
            // });


        }

        //style
        $('.bctai_chat_widget_font_size, .bctai_chat_widget_width, .bctai_chat_widget_height').on('input', function() {
            bctaiUpdateRealtime();
        });
        $('.bctai_chat_widget_audio,.bctaichat_use_avatar').click(function() {
            bctaiUpdateRealtime();
        });
        $('.bctaichat_color').wpColorPicker({
            change: function (event, ui) {
                bctaiUpdateRealtime();
            },
            clear: function(event) {
                bctaiUpdateRealtime();
            }
        });
        $('.bctai-footer-note').on('input', function() {
            bctaiUpdateRealtime();
        });
        $('.bctai_chatbox_icon').click(function (e){
            //alert("click");
            e.preventDefault();
            $('.bctai_chatbox_icon_default').prop('checked',false);
            $('.bctai_chatbox_icon_custom').prop('checked',true);
            var button = $(e.currentTarget),
                custom_uploader = wp.media({
                    title: '<?php echo __('Insert image')?>',
                    library : {
                        type : 'image'
                    },
                    button: {
                        text: '<?php echo __('Use this image')?>'
                    },
                    multiple: false
                }).on('select', function() {
                    var attachment = custom_uploader.state().get('selection').first().toJSON();
                    button.html('<img src="'+attachment.url+'">');
                    $('.bctai_chatbox_icon').css('border','0px');
                    $('.bctai_chat_icon_url').val(attachment.id);
                }).open();
        });
        $('.bctai_chatbox_avatar').click(function (e) {
            //alert("click2");
            e.preventDefault();
            $('.bctai_chatbox_avatar_default').prop('checked',false);
            $('.bctai_chatbox_avatar_custom').prop('checked',true);
            var button = $(e.currentTarget),
                custom_uploader = wp.media({
                    title: '<?php echo __('Insert image')?>',
                    library : {
                        type : 'image'
                    },
                    button: {
                        text: '<?php echo __('Use this image')?>'
                    },
                    multiple: false
                }).on('select', function() {
                    var attachment = custom_uploader.state().get('selection').first().toJSON();
                    button.html('<img src="'+attachment.url+'" style="width:30px;height:30px;">');
                    $('.bctai_ai_avatar_id').val(attachment.id);
                    $('.bctai_chatbox_avatar').css('border','0px');

                }).open();                
        });

        //parameters


        //context
        $('#bctai_chat_excerpt').on('click', function (){
            if($(this).prop('checked')){
                $('#bctai_chat_excerpt').removeClass('asdisabled');
                $('#bctai_chat_embedding').prop('checked',false);
                $('#bctai_chat_embedding').addClass('asdisabled');
                $('#bctai_chat_embedding_type').val('openai');
                $('#bctai_chat_embedding_type').addClass('asdisabled');
                $('#bctai_chat_embedding_type').attr('disabled','disabled');
                $('#bctai_chat_embedding_top').attr('disabled','disabled');
                $('#bctai_chat_embedding_top').val(1);
                $('#bctai_chat_embedding_index').attr('disabled','disabled');
                $('#bctai_chat_embedding_index').addClass('asdisabled');
            }
            else{
                $(this).prop('checked',true);
            }
        });
        $('#bctai_chat_addition').on('click', function (){
            if($(this).prop('checked')){
                $('#bctai_chat_addition_text').removeAttr('disabled');
            }
            else{
                $('#bctai_chat_addition_text').attr('disabled','disabled');
            }
        });     
        $('#bctai_chat_embedding').on('click', function() {
            if($(this).prop('checked')){
                $('#bctai_chat_excerpt').prop('checked',false);
                $('#bctai_chat_excerpt').addClass('asdisabled');
                $('#bctai_chat_addition').prop('checked',false);
                $('#bctai_chat_embedding').removeClass('asdisabled');
                $('#bctai_chat_embedding_type').val('openai');
                $('#bctai_chat_embedding_type').removeClass('asdisabled');
                $('#bctai_chat_embedding_type').removeAttr('disabled');
                $('#bctai_chat_embedding_top').val(1);
                $('#bctai_chat_embedding_top').removeClass('asdisabled');
                $('#bctai_chat_embedding_top').removeAttr('disabled');
                $('#bctai_chat_embedding_index').removeAttr('disabled','disabled');
                $('#bctai_chat_embedding_index').removeClass('asdisabled');
            }
            else{
                $(this).prop('checked',true);
            }
        });
        <?php
        if(!$bctai_embedding_field_disabled):
        ?>
        $('#bctai_chat_content_aware').on('change', function() {
            if($(this).val() === 'yes'){
                $('#bctai_chat_excerpt').removeAttr('disabled');
                $('#bctai_chat_excerpt').prop('checked',true);
                $('#bctai_chat_embedding').removeAttr('disabled');
                $('#bctai_chat_embedding_type').removeAttr('disabled');
                $('#bctai_chat_embedding').addClass('asdisabled');
                $('#bctai_chat_embedding_type').val('openai');
                $('#bctai_chat_embedding_type').addClass('asdisabled');
                $('#bctai_chat_embedding_top').val(1);
                $('#bctai_chat_embedding_top').addClass('asdisabled');
                $('#bctai_chat_embedding_index').removeAttr('disabled');
                $('#bctai_chat_embedding_index').addClass('asdisabled');
                
            }
            else{
                $('#bctai_chat_embedding_type').removeClass('asdisabled');
                $('#bctai_chat_excerpt').removeClass('asdisabled');
                $('bctai_chat_embedding').removeClass('asdisabled');
                $('#bctai_chat_excerpt').prop('checked',false);
                $('#bctai_chat_embedding').prop('checked',false);
                $('#bctai_chat_excerpt').attr('disabled','disabled');
                $('#bctai_chat_embedding').attr('disabled','disabled');
                $('#bctai_chat_embedding_type').attr('disabled','disabled');
                $('#bctai_chat_embedding_top').attr('disabled','disabled');
                $('#bctai_chat_embedding_top').removeClass('asdisabled');
                $('#bctai_chat_embedding_index').attr('disabled','disabled');
                $('#bctai_chat_embedding_index').removeClass('asdisabled');
            }
        })
        <?php
        else:
        ?>
        $('#bctai_chat_content_aware').on('change', function() {
            if($(this).val() === 'yes'){
                $('#bctai_chat_excerpt').removeAttr('disabled');
                $('#bctai_chat_excerpt').prop('checked',true);
            }
            else{
                $('#bctai_chat_excerpt').removeClass('asdisabled');
                $('#bctai_chat_excerpt').prop('checked',false);
                $('#bctai_chat_excerpt').attr('disabled','disabled');
            }
        })
        <?php
        endif;
        ?>

        
        $(document).on('click','.bctai_chat_to_speech', function(e){
            let parent = $(e.currentTarget).parent().parent();
            let voice_service = parent.find('.bctai_voice_service');
            //alert(JSON.stringify(voice_service));
            if($(e.currentTarget).prop('checked')){
                if(bctai_elevenlab_api !== '' || bctai_google_api_key !== ''){
                    voice_service.removeAttr('disabled');
                }
                if(bctai_elevenlab_api !== ''){
                    parent.find('.bctai_elevenlabs_voice').removeAttr('disabled');
                    parent.find('.bctai_elevenlabs_model').removeAttr('disabled');
                }
                if(bctai_google_api_key !== ''){
                    parent.find('.bctai_voice_language').removeAttr('disabled');
                    parent.find('.bctai_voice_name').removeAttr('disabled');
                    parent.find('.bctai_voice_device').removeAttr('disabled');
                    parent.find('.bctai_voice_speed').removeAttr('disabled');
                    parent.find('.bctai_voice_pitch').removeAttr('disabled');
                }
            }
            else {
                voice_service.attr('disabled','disabled');
                parent.find('.bctai_elevenlabs_voice').attr('disabled','disabled');
                parent.find('.bctai_elevenlabs_model').attr('disabled','disabled');
                parent.find('.bctai_voice_language').attr('disabled','disabled');
                parent.find('.bctai_voice_name').attr('disabled','disabled');
                parent.find('.bctai_voice_device').attr('disabled','disabled');
                parent.find('.bctai_voice_speed').attr('disabled','disabled');
                parent.find('.bctai_voice_pitch').attr('disabled','disabled');
            }
        });
        $(document).on('change','.bctai_voice_service',function(e){
            let parent = $(e.currentTarget).parent().parent();
            //alert(JSON.stringify(parent));
            if($(e.currentTarget).val() === 'google'){
                parent.find('.bctai_voice_service_elevenlabs').hide();
                parent.find('.bctai_voice_service_google').show();
            }
            else{
                parent.find('.bctai_voice_service_elevenlabs').show();
                parent.find('.bctai_voice_service_google').hide();
            }
        });
        $(document).on('keypress','.bctai_voice_speed,.bctai_voice_pitch', function (e){
            var charCode = (e.which) ? e.which : e.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode !== 46) {
                return false;
            }
            return true;
        });

        // Function to check the selected value and toggle the notice
        function bctaitoggleNotice() {
            // Check if Text to Speech is enabled
            var isTextToSpeechEnabled = $('.bctai_chat_to_speech').prop('checked');

            if (isTextToSpeechEnabled && $('.bctai_elevenlabs_model').val() === 'eleven_multilingual_v1') {
                $('.bctai-notice').show();
            } else {
                $('.bctai-notice').hide();
            }
        }

        // Run the function immediately on page load
        bctaitoggleNotice();

        // Listen for changes on the model dropdown
        $('.bctai_elevenlabs_model').on('change', bctaitoggleNotice);

        // Listen for changes on the Text to Speech checkbox
        $('.bctai_chat_to_speech').on('change', bctaitoggleNotice);

        function bctaisetVoices(element){
            let parent = element.parent().parent();
            let language = element.val();
            let voiceNameInput = parent.find('.bctai_voice_name');
            //alert(JSON.stringify(voiceNameInput));
            voiceNameInput.empty();
            let selected = voiceNameInput.attr('data-value');
            $.each(bctai_google_voices[language], function (idx, item){
                voiceNameInput.append('<option'+(selected === item.name ? ' selected':'')+' value="'+item.name+'">'+item.name+' - '+item.ssmlGender+'</option>');
            })
        }
        function bctaicollectVoices(element){
            //alert(JSON.stringify(element));
            if(!Object.keys(bctai_google_voices).length === 0){
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php')?>',
                    data: {action: 'bctai_sync_google_voices',nonce: '<?php echo wp_create_nonce('bctai_sync_google_voices')?>'},
                    dataType: 'json',
                    type: 'post',
                    success: function(res){
                       if(res.status === 'success'){
                            bctai_google_voices = res.voices;
                            bctaisetVoices(element);
                        }else{
                            alert(res.message);
                        }
                    }
                });
            }
            else {
                bctaisetVoices(element);
            }
        }
        $(document).on('change','.bctai_voice_language', function(e){
            //alert(JSON.stringify(e.currentTarget));
            bctaicollectVoices($(e.currentTarget));
        })
        if($('.bctai_voice_language').length){
            bctaicollectVoices($('.bctai_voice_language'));
        }

        //token handing        
        $(document).on('keypress','.bctai_user_token_limit_text,.bctai_update_role_limit,.bctai_guest_token_limit_text', function (e){
            var charCode = (e.which) ? e.which : e.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode !== 46) {
                return false;
            }
            return true;
        });        
        $('.bctai_limit_set_role').click(function () {
            if(!$(this).hasClass('disabled')) {
                if ($('.bctai_role_limited').prop('checked')) {
                    let html = '';
                    $.each(bctai_roles, function (key, role) {
                        let valueRole = $('.bctai_role_'+key).val();
                        html += '<div style="padding: 5px;display: flex;justify-content: space-between;align-items: center;"><label><strong>'+role+'</strong></label><input class="bctai_update_role_limit" data-target="'+key+'" value="'+valueRole+'" placeholder="Empty for no-limit" type="text"></div>';
                    });
                    html += '<div style="padding: 5px"><button class="button button-primary bctai_save_role_limit" style="width: 100%;margin: 5px 0;">Save</button></div>';
                    $('.bctai_modal_title_second').html('Role Limit');
                    $('.bctai_modal_content_second').html(html);
                    $('.bctai-overlay-second').css('display','flex');
                    $('.bctai_modal_second').show();

                } else {
                    $.each(bctai_roles, function (key, role) {
                        $('.bctai_role_' + key).val('');
                    })
                }
            }
        });
        $(document).on('click','.bctai_save_role_limit', function (e){
            $('.bctai_update_role_limit').each(function (idx, item){
                let input = $(item);
                let target = input.attr('data-target');
                $('.bctai_role_'+target).val(input.val());
            });
            $('.bctai_modal_close_second').closest('.bctai_modal_second').hide();
            $('.bctai-overlay-second').hide();
        });
        $('.bctai_role_limited').click(function (){
            if($(this).prop('checked')){
                $('.bctai_user_token_limit').prop('checked',false);
                $('.bctai_user_token_limit_text').attr('disabled','disabled');
                $('.bctai_limit_set_role').removeClass('disabled');
            }
            else{
                $('.bctai_limit_set_role').addClass('disabled');
            }
        });
        $('.bctai_user_token_limit').click(function (){
            if($(this).prop('checked')){
                $('.bctai_user_token_limit_text').removeAttr('disabled');
                $('.bctai_role_limited').prop('checked',false);
                $('.bctai_limit_set_role').addClass('disabled');
            }
            else{
                $('.bctai_user_token_limit_text').val('');
                $('.bctai_user_token_limit_text').attr('disabled','disabled');
            }
        });
        $('.bctai_guest_token_limit').click(function (){
            if($(this).prop('checked')){
                $('.bctai_guest_token_limit_text').removeAttr('disabled');
            }
            else{
                $('.bctai_guest_token_limit_text').val('');
                $('.bctai_guest_token_limit_text').attr('disabled','disabled');
            }
        });

        // log
        $(document).on('click', '.bctai_chatbot_save_logs', function(e){
            if($(e.currentTarget).prop('checked')){
                $('.bctai_chatbot_log_request').removeAttr('disabled');
                $('.bctai_chatbot_log_notice').removeAttr('disabled');
                $('.bctai_chatbot_log_notice_message').removeAttr('disabled');
            }
            else{
                $('.bctai_chatbot_log_request').attr('disabled','disabled');
                $('.bctai_chatbot_log_request').prop('checked',false);
                $('.bctai_chatbot_log_notice').attr('disabled','disabled');
                $('.bctai_chatbot_log_notice').prop('checked',false);
                $('.bctai_chatbot_log_notice_message').attr('disabled','disabled');
            }
        });

    })
</script>