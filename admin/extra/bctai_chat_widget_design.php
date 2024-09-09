<?php




if ( ! defined( 'ABSPATH' ) ) exit;
global $wpdb;
$errors = false;
$message = false;
if ( isset( $_POST['bctai_submit'] ) ) {
    check_admin_referer('bctai_chat_widget_save');
    
    if(!$errors) {
        $bctai_keys = array(
            'bctai_chat_design',
            'bctai_chat_status'
        );
        foreach($bctai_keys as $bctai_key) {
            if(isset($_POST[$bctai_key]) && !empty($_POST[$bctai_key])) {
                $posted_value = stripslashes_deep($_POST[$bctai_key]);
                update_option($bctai_key, \BCTAI\bctai_util_core()->sanitize_text_or_array_field($posted_value));
            }   
            else {
                delete_option($bctai_key);
            }
        }
        $message = "Setting saved successfully";
    }
}
wp_enqueue_script('wp-color-picker');
wp_enqueue_style('wp-color-picker');

$bctai_chat_status = get_option('bctai_chat_status','noachive');

//활성화
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








//for Pro
$Custom_Header_Color = isset($bctai_chat_design['Custom_Header_Color']) && !empty($bctai_chat_design['Custom_Header_Color']) ? $bctai_chat_design['Custom_Header_Color'] : '#ffffff';
$Header_Text_Color =isset($bctai_chat_design['Header_Text_Color']) && !empty($bctai_chat_design['Header_Text_Color']) ? $bctai_chat_design['Header_Text_Color'] : '#569bd4';
$Button_Icon_Color = isset($bctai_chat_design['Button_Icon_Color']) && !empty($bctai_chat_design['Button_Icon_Color']) ? $bctai_chat_design['Button_Icon_Color'] : '#569bd4';
$Message_Background_Color = isset($bctai_chat_design['Message_Background_Color']) && !empty($bctai_chat_design['Message_Background_Color']) ? $bctai_chat_design['Message_Background_Color'] : '#569bd4';
$Message_Name_Color = isset($bctai_chat_design['Message_Name_Color']) && !empty($bctai_chat_design['Message_Name_Color']) ? $bctai_chat_design['Message_Name_Color'] : '#569bd4';
$Message_Date_Color = isset($bctai_chat_design['Message_Date_Color']) && !empty($bctai_chat_design['Message_Date_Color']) ? $bctai_chat_design['Message_Date_Color'] : '#569bd4';
$Chatbot_Message_Background = isset($bctai_chat_design['Chatbot_Message_Background']) && !empty($bctai_chat_design['Chatbot_Message_Background']) ? $bctai_chat_design['Chatbot_Message_Background'] : '#569bd4';
$Chatbot_Message_Text = isset($bctai_chat_design['Chatbot_Message_Text']) && !empty($bctai_chat_design['Chatbot_Message_Text']) ? $bctai_chat_design['Chatbot_Message_Text'] : '#569bd4';
$User_Message_Background = isset($bctai_chat_design['User_Message_Background']) && !empty($bctai_chat_design['User_Message_Background']) ? $bctai_chat_design['User_Message_Background'] : '#569bd4';
$User_Message_Text = isset($bctai_chat_design['User_Message_Text']) && !empty($bctai_chat_design['User_Message_Text']) ? $bctai_chat_design['User_Message_Text'] : '#569bd4';
$Typing_Background_Color = isset($bctai_chat_design['Typing_Background_Color']) && !empty($bctai_chat_design['Typing_Background_Color']) ? $bctai_chat_design['Typing_Background_Color'] : '#569bd4';
$Typing_Button_Icon_Color = isset($bctai_chat_design['Typing_Button_Icon_Color']) && !empty($bctai_chat_design['Typing_Button_Icon_Color']) ? $bctai_chat_design['Typing_Button_Icon_Color'] : '#569bd4';
$Message_Typing_Color = isset($bctai_chat_design['Message_Typing_Color']) && !empty($bctai_chat_design['Message_Typing_Color']) ? $bctai_chat_design['Message_Typing_Color'] : '#569bd4';
$chatbot_name = isset($bctai_chat_design['chatbot_name']) && !empty($bctai_chat_design['chatbot_name']) ? $bctai_chat_design['chatbot_name'] : 'BCT Ai Chatbot';



?>


<?php

if ( !empty($errors)) {
    echo "<h4 id='setting_message' style='color: red;'>" . esc_html( $errors ) . "</h4>";
} elseif(!empty($message)) {
    echo "<h4 id='setting_message' style='color: green;'>" . esc_html( $message ) . "</h4>";
}
?>


<h2 class="sectionTitle">Design</h2>
        
<form action="" method="post" id="form-chatbox-setting">
<?php wp_nonce_field('bctai_chat_widget_save');?>


    
<div style="display: flex;">
    <div class="setting_wrap" style="width: 60%;">
        <h3 class="chapterTitle">Widget Activate</h3>


        <div class="columnWrap">
            <div class="columnWrap column2">
                <div class="formContent">
                    <select name="bctai_chat_status" class="select_style">
                        <option value=""><?php echo __('No', 'bctai') ?></option>
                        <option <?php echo $bctai_chat_status == 'active' ? ' selected': ''?> value="active"><?php echo __('Yes', 'bctai') ?></option>
                    </select>
                </div>
            </div>
        </div>

        


        <div class="style_wrap">
            <h3 class="chapterTitle">Chatbot Style</h3>

            <p><?php echo __('Select the style of the chat window.','bctai')?></p>
            <div class="select_wrap">
                <div class="first_design">

                    <div class="color_wrap">
                        <div class="design_name" for="Basic"></div>
                        <span><?php echo __('BCT Ai Chatbot-Basic', 'bctai') ?></span>
                        <input <?php echo esc_html($Header_Color) == '#8040ad' ? ' checked': ''?> type="radio" class="select_design" value="#8040ad" name="bctai_chat_design[Header_Color]" id="Basic">
                    </div>
                    <div class="color_wrap">
                        <div class="design_name" style="background:#dfdcdc;" for="Mono"><div style="width: 100%; height:50%; background:black;"for="Mono"></div></div>
                        <span><?php echo __('Mono', 'bctai') ?></span>
                        <input <?php echo esc_html($Header_Color) == '#352f39' ? ' checked': ''?> type="radio" class="select_design" value="#352f39" name="bctai_chat_design[Header_Color]" id="Mono">
                    </div>
                    <div class="color_wrap">
                        <div class="design_name" style="background:#575757;"for="Dark"><div style="width: 100%; height:50%; background:#352F39;"for="Dark"></div></div>
                        <span><?php echo __('Dark', 'bctai') ?></span>
                        <input <?php echo esc_html($Header_Color) == '#352f38' ? ' checked': ''?> type="radio" class="select_design" value="#352f38" name="bctai_chat_design[Header_Color]" id="Dark">
                    </div>
                    <div class="color_wrap">
                        <div class="design_name" style="background:#d7d4d4;"for="Light_Grey"></div>
                        <span><?php echo __('Light Grey', 'bctai') ?></span>
                        <input <?php echo esc_html($Header_Color) == '#F1F1F1' ? ' checked': ''?> type="radio" class="select_design" value="#F1F1F1" name="bctai_chat_design[Header_Color]" id="Light_Grey">
                    </div>
                </div>

                <div class="first_design">
                    <div class="color_wrap">
                        <div class="design_name" style="background:#F53706;"for="Electric_Orange"></div>
                        <span><?php echo __('Electric Orange', 'bctai') ?></span>
                        <input <?php echo esc_html($Header_Color) == '#F53706' ? ' checked': ''?> type="radio" class="select_design" value="#F53706" name="bctai_chat_design[Header_Color]" id="Electric_Orange">
                    </div>
                    <div class="color_wrap">
                        <div class="design_name" style="background:#0080FF;"for="Azure"></div>
                        <span><?php echo __('Azure', 'bctai') ?></span>
                        <input <?php echo esc_html($Header_Color) == '#0080FF' ? ' checked': ''?> type="radio" class="select_design" value="#0080FF" name="bctai_chat_design[Header_Color]" id="Azure">
                    </div>
                    <div class="color_wrap">
                        <div class="design_name" style="background:#00A800;"for="Irish_Green"></div>
                        <span><?php echo __('Irish Green', 'bctai') ?></span>
                        <input <?php echo esc_html($Header_Color) == '#00A800' ? ' checked': ''?> type="radio" class="select_design" value="#00A800" name="bctai_chat_design[Header_Color]" id="Irish_Green" onchange="applyCustomStyle()" >
                    </div>


                    <?php if ( bct_fs()->is_plan('pro') ) { ?>
                        <div class="color_wrap" style="position: relative;">
                            <div class="design_name" style="background:#352F39;color: #FFFFFF;font-size: 24px;line-height: 80px;"for="custom_chatbot_style"><i class="fa-solid fa-gear fa-lg"></i></div>
                            <span><?php echo __('Custom', 'bctai') ?></span>
                            <div style="display: inline-block; margin-right: 5px; width: 24px; height: 15px; background: #F53706; border-radius: 6px; opacity: 1;font: normal normal normal 8px/11px Noto Sans KR;letter-spacing: 0px;color: #FFFFFF;position: absolute;top: 89px;right: 10px;">pro</div>
                            <input <?php echo esc_html($Header_Color) == 'on' ? ' checked': ''?> type="radio" class="select_design" id="custom_chatbot_style" value="on" name="bctai_chat_design[Header_Color]" onchange="applyCustomStyle()" >
                        </div>
                    <?php }else{ ?>
                        <div class="color_wrap" style="position: relative;">
                            <div class="design_name" style="background:#352F39;color: #FFFFFF;font-size: 24px;line-height: 80px;"for="custom_notpro"><i class="fa-solid fa-gear fa-lg"></i></div>
                            <span><?php echo __('Custom', 'bctai') ?></span>
                            <div style="display: inline-block; margin-right: 5px; width: 24px; height: 15px; background: #F53706; border-radius: 6px; opacity: 1;font: normal normal normal 8px/11px Noto Sans KR;letter-spacing: 0px;color: #FFFFFF;position: absolute;top: 89px;right: 10px;">pro</div>
                            <input <?php echo esc_html($Header_Color) == 'on' ? ' checked': ''?> type="radio" class="select_design" id="custom_notpro" value="on" name="bctai_chat_design[Header_Color]" onchange="applyCustomStyle()" >
                        </div>
                    <?php } ?>
                        
                    
                    
                    



                    
                </div>


                <div class="custom_style_handler" style="display:<?php echo ($Header_Color == 'on') ? 'block;' : 'none;'; ?>">

                    <div style="display:flex;">
                        <h3 class="chapterTitle">Custom Style</h3>
                        <p style="margin:auto;width: 24px;height: 15px;background: #F53706 0% 0% no-repeat padding-box;border-radius: 6px;opacity: 1;margin-left:3px;font: normal normal normal 8px/11px Noto Sans KR;letter-spacing: 0px;color: #FFFFFF;opacity: 1;text-align: center;line-height: 15px;">PRO</p>
                    </div>
                    <div class="mb-5">
                        <label class="bctai-form-label"><?php echo __('Header Color', 'bctai') ?>:</label>
                        <input value="<?php echo esc_html($Custom_Header_Color)?>" type="text" class="bctaichat_color Custom_Header_Color" name="bctai_chat_design[Custom_Header_Color]">
                    </div>
                    <div class="mb-5">
                        <label class="bctai-form-label"><?php echo __('Header Text Color', 'bctai') ?>:</label>
                        <input value="<?php echo esc_html($Header_Text_Color)?>" type="text" class="bctaichat_color Header_Text_Color" name="bctai_chat_design[Header_Text_Color]">
                    </div>
                    <div class="mb-5">
                        <label class="bctai-form-label"><?php echo __('Button Icon Color', 'bctai') ?>:</label>
                        <input value="<?php echo esc_html($Button_Icon_Color)?>" type="text" class="bctaichat_color Button_Icon_Color" name="bctai_chat_design[Button_Icon_Color]">
                    </div>

                    <div class="mb-5">
                        <label class="bctai-form-label"><?php echo __('Message Background Color', 'bctai') ?>:</label>
                        <input value="<?php echo esc_html($Message_Background_Color)?>" type="text" class="bctaichat_color Message_Background_Color" name="bctai_chat_design[Message_Background_Color]">
                    </div>

                    <div class="mb-5">
                        <label class="bctai-form-label"><?php echo __('Message Name Color', 'bctai') ?>:</label>
                        <input value="<?php echo esc_html($Message_Name_Color)?>" type="text" class="bctaichat_color Message_Name_Color" name="bctai_chat_design[Message_Name_Color]">
                    </div>

                    <div class="mb-5">
                        <label class="bctai-form-label"><?php echo __('Message Date Color', 'bctai') ?>:</label>
                        <input value="<?php echo esc_html($Message_Date_Color)?>" type="text" class="bctaichat_color Message_Date_Color" name="bctai_chat_design[Message_Date_Color]">
                    </div>

                    <div class="mb-5">
                        <label class="bctai-form-label"><?php echo __('Chatbot Message Background', 'bctai') ?>:</label>
                        <input value="<?php echo esc_html($Chatbot_Message_Background)?>" type="text" class="bctaichat_color Chatbot_Message_Background" name="bctai_chat_design[Chatbot_Message_Background]">
                    </div>

                    <div class="mb-5">
                        <label class="bctai-form-label"><?php echo __('Chatbot Message Text', 'bctai') ?>:</label>
                        <input value="<?php echo esc_html($Chatbot_Message_Text)?>" type="text" class="bctaichat_color Chatbot_Message_Text" name="bctai_chat_design[Chatbot_Message_Text]">
                    </div>

                    <div class="mb-5">
                        <label class="bctai-form-label"><?php echo __('User Message Background', 'bctai') ?>:</label>
                        <input value="<?php echo esc_html($User_Message_Background)?>" type="text" class="bctaichat_color User_Message_Background" name="bctai_chat_design[User_Message_Background]">
                    </div>

                    <div class="mb-5">
                        <label class="bctai-form-label"><?php echo __('User Message Text', 'bctai') ?>:</label>
                        <input value="<?php echo esc_html($User_Message_Text)?>" type="text" class="bctaichat_color User_Message_Text" name="bctai_chat_design[User_Message_Text]">
                    </div>

                    <div class="mb-5">
                        <label class="bctai-form-label"><?php echo __('Typing Background Color', 'bctai') ?>:</label>
                        <input value="<?php echo esc_html($Typing_Background_Color)?>" type="text" class="bctaichat_color Typing_Background_Color" name="bctai_chat_design[Typing_Background_Color]">
                    </div>

                    <div class="mb-5">
                        <label class="bctai-form-label"><?php echo __('Typing Button Icon Color', 'bctai') ?>:</label>
                        <input value="<?php echo esc_html($Typing_Button_Icon_Color)?>" type="text" class="bctaichat_color Typing_Button_Icon_Color" name="bctai_chat_design[Typing_Button_Icon_Color]">
                    </div>

                    <div class="mb-5">
                        <label class="bctai-form-label"><?php echo __('Message Typing Color', 'bctai') ?>:</label>
                        <input value="<?php echo esc_html($Message_Typing_Color)?>" type="text" class="bctaichat_color Message_Typing_Color" name="bctai_chat_design[Message_Typing_Color]">
                    </div>
                </div>
            </div>


            



            
            <div class="select_chatbot_icon">
                <!--커스텀 아이콘 저장하는 input-->
                <input value="<?php echo esc_html($bctai_chat_icon_url)?>" type="hidden" name="bctai_chat_design[icon_url]" class="bctai_chat_icon_url">
                <input value="<?php echo esc_html($bctai_ai_avatar_id)?>" type="hidden" name="bctai_chat_design[ai_avatar_id]" class="bctai_ai_avatar_id">

                <h3 class="chapterTitle">Chatbot Name</h3>
                <p><?php echo __('Please enter the chatbot name.', 'bctai') ?></p>

                <div class="columnWrap">
                    <div class="columnWrap column2">
                        <div class="formContent">
                            <input  type="text" value="<?php echo $chatbot_name?>"name="bctai_chat_design[chatbot_name]" id="systemmessage">
                        </div>
                    </div>
                </div>

                <h3 class="chapterTitle">Chatbot Icon</h3>
                <p><?php echo __('Set the chatbot icon displayed in the chat window', 'bctai') ?>.</p>

                <div class="select_icon_wrap">
                    <input <?php echo $bctai_ai_avatar == 'default' ? ' checked': ''?> class="bctai_chatbox_avatar_default" type="radio" value="default" name="bctai_chat_design[ai_avatar]">
                    <img src="<?php echo esc_html(BCTAI_PLUGIN_URL).'public/images/bctaichatbot_logo_txt.png'?>" style="width: 30px;height: 30px;">
                    <span><?php echo __('BCT Ai Chatbot-Basic', 'bctai') ?></span>
                </div>

                <div class="select_icon_wrap">
                    <input <?php echo $bctai_ai_avatar == 'custom' ? ' checked': ''?> type="radio" class="bctai_chatbox_avatar_custom" value="custom" name="bctai_chat_design[ai_avatar]">
                        <?php if(!empty($bctai_ai_avatar_id) && $bctai_ai_avatar == 'custom'):$bctai_ai_avatar_url = wp_get_attachment_url($bctai_ai_avatar_id);?>
                            <div class="bctai_chatbox_avatar">
                                <img src="<?php echo esc_html($bctai_ai_avatar_url)?>" style="width:30px;height:30px;">
                            </div>
                        <?php else: ?>
                            <div class="bctai_chatbox_avatar">
                                <i class="fa-solid fa-file-arrow-up"></i>
                            </div>
                        <?php endif;?>
                    <span><?php echo __('Upload Icon (30x30 px, png)', 'bctai') ?></span>
                </div>
            </div>

            <div class="select_widget_icon">
                <h3 class="chapterTitle">Widget Icon</h3>
                <p><?php echo __('Set the widget icon displayed on the homepage', 'bctai') ?>.</p>

                <div class="select_icon_wrap">
                    <input <?php echo $bctai_chat_icon == 'default' ? ' checked': ''?> class="bctai_chatbox_icon_default" type="radio" value="default" name="bctai_chat_design[icon]">
                    <img src="<?php echo esc_html(BCTAI_PLUGIN_URL).'public/images/bctaichatbot_logo_txt.png'?>">
                    <span><?php echo __('BCT Ai Chatbot-Basic', 'bctai') ?></span>
                </div>

                <div class="select_icon_wrap">
                    <input <?php echo $bctai_chat_icon == 'custom' ? ' checked': ''?> type="radio" class="bctai_chatbox_icon_custom" value="custom" name="bctai_chat_design[icon]">
                        <?php
                        

                        if(!empty($bctai_chat_icon_url) && $bctai_chat_icon == 'custom'):$bctai_chatbox_icon_url = wp_get_attachment_url($bctai_chat_icon_url);
                        ?>
                            <div class="bctai_chatbox_icon">
                                <img src="<?php echo esc_html($bctai_chatbox_icon_url)?>">
                            </div>
                        <?php else:?>
                            <div class="bctai_chatbox_icon">
                                <i class="fa-solid fa-file-arrow-up"></i>
                            </div>
                        <?php endif;?>
                    <span><?php echo __('Upload Icon (100x100 px, png)', 'bctai') ?></span>
                </div>
            </div>
            
        </div>


        
        <h3 class="chapterTitle">System Message</h3>
        <div class="columnWrap">
            <div class="columnWrap column2">
                <div class="formTitle"><strong>System</strong></div>
                <div class="formContent">
                    <input  type="text" value="<?php echo $bctai_chat_proffesion?>"name="bctai_chat_design[proffesion]" id="systemmessage">
                </div>
            </div>
            <p style="color:red;"> <?php echo __('*The system is a command sent to Chatbot.  For example, “Summarize content you are provided with for a second-grade student.”','bctai')?></p>
        </div>

        
        

        

        

        
           
        
        <div class="mb-5">
            <label class="bctai-form-label" for="systemmessage" style="font: normal normal normal 14px/20px Noto Sans KR;line-height: inherit;"><?php echo __('Postition', 'bctai') ?></label>
            <input <?php echo $bctai_chat_position == 'left' ? ' checked': ''?> style="margin: auto 0px; margin-right: 10px;"class="widget_input_radios" type="radio" value="left" name="bctai_chat_design[position]"><?php echo __('Left', 'bctai') ?>
            <input <?php echo $bctai_chat_position == 'right' ? ' checked': ''?> style="margin: auto 0px; margin-left: 80px;margin-right: 10px;"class="widget_input_radios" type="radio" value="right" name="bctai_chat_design[position]"><?php echo __('Right', 'bctai') ?>
        </div> 





        <h3 class="chapterTitle">Shortcode</h3>
        <div>
            <p><?php echo __('To add the chat box to your website, please include the shortcode', 'bctai') ?><span style="font: normal normal 900 20px/24px Noto Sans KR;">[bctai_chatgpt_widget]</span><?php echo __('in the desired', 'bctai') ?></p>
            <p><?php echo __('location on your site.', 'bctai') ?></p>
            <p><?php echo __('If you prefer to use widget instead of shortcode, go to Widget tab and configure it.', 'bctai') ?></p>
        </div>
    
    </div>
    <!--챗박스-->
    <div class="bctai-chatbox-preview-box" style="width: 40%;position: relative;">
        <?php include __DIR__ . '/bctai_chat_widget.php';?>
    </div>
</div>

<button class="btn btnL bgPrimary" name="bctai_submit"><?php echo __('SAVE', 'bctai') ?></button>
</form>  


            


<script>

function applyCustomStyle() {
    
    
    var customStyleCheckbox = document.getElementById("custom_chatbot_style");
    var custom_style_handler = document.querySelector(".custom_style_handler");
        if (customStyleCheckbox.checked) {
            custom_style_handler.style.display = "block";
        } else {
            custom_style_handler.style.display = "none";
        }
    }




    jQuery(document).ready(function($) {
        let bctai_google_voices = <?php echo wp_json_encode($bctai_google_voices)?>;
        let bctai_elevenlab_api = '<?php echo esc_html($bctai_elevenlabs_api)?>';
        let bctai_google_api_key = '<?php  echo $bctai_google_api_key?>';
        let bctai_roles = <?php echo wp_kses_post(wp_json_encode($bctai_roles))?>;

        
        $('.bctai-chatbox-preview-box > .bctai_chat_widget').addClass('bctai_widget_open');
        $('.bctai-chatbox-preview-box .bctai_toggle').addClass('bctai_widget_open');


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
            }else if(tmp =="custom_notpro"){
                alert('Available in the PRO');
                applyCustomStyle();
            }


        }


        



        function bctaiUpdateRealtime() {
            let Custom_Header_Color = $('.Custom_Header_Color').iris('color');
            let Header_Text_Color = $('.Header_Text_Color').iris('color');
            let Button_Icon_Color = $('.Button_Icon_Color').iris('color');
            let Message_Background_Color = $('.Message_Background_Color').iris('color');
            let Message_Name_Color = $('.Message_Name_Color').iris('color');
            let Message_Date_Color = $('.Message_Date_Color').iris('color');
            let Chatbot_Message_Background = $('.Chatbot_Message_Background').iris('color');
            let Chatbot_Message_Text = $('.Chatbot_Message_Text').iris('color');
            let User_Message_Background = $('.User_Message_Background').iris('color');
            let User_Message_Text = $('.User_Message_Text').iris('color');
            let Typing_Background_Color = $('.Typing_Background_Color').iris('color');
            let Typing_Button_Icon_Color=$('.Typing_Button_Icon_Color').iris('color');
            let Message_Typing_Color=$('.Message_Typing_Color').iris('color');

            $('.chatbot-header h2').css({
                'color': Header_Text_Color
            });
            $(' .chatbot-header, .btn-home, .btn-remove, .btn-mail,.btn-fullscreen').css({
                'background-color': Custom_Header_Color
            });

            $('.chatbot-header i').css({
                'color': Button_Icon_Color
            });

            $('.chatbot-contents').css({
                'background': Message_Background_Color
            });
            $('.chatbot-contents .message.left .name').css({
                'color': Message_Name_Color
            });
            $('.chatbot-contents .message .date').css({
                'color': Message_Date_Color
            });
            $('.chatbot-contents .message.left .bubble').css({
                'background': Chatbot_Message_Background
            });
            $('.chatbot-contents .message.left .bubble div').css({
                'color': Chatbot_Message_Text
            });

            $('.chatbot-contents .message.right .bubble').css({
                'background': User_Message_Background
            });

            $('.chatbot-contents .message.right .bubble .txt').css({
                'color': User_Message_Text
            });

            $('.bctai-chat-widget-typing, .chatbot-footer').css({
                'background': Typing_Background_Color,
                'color':Message_Typing_Color
            });

            $('.btn-send_widget').css({
                'background': Typing_Button_Icon_Color
            });
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
    })
</script>