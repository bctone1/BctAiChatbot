
<style>.chatbot-header i {color:#BFBFBF;    font-size: 15px;}</style>
<?php
if (!defined('ABSPATH')) exit;
global $wp, $wpdb;
$bctai_chat_widget = get_option('bctai_chat_widget', []);
$bctai_chat_design = get_option('bctai_chat_design',[]);
$bctai_ai_name = get_option('_bctai_chatbox_ai_name', 'AI');
$bctai_ai_thining = get_option('_bctai_ai_thinking', 'AI thinking');
$bctai_stt_method = isset($bctai_chat_widget['stt_method']) && !empty($bctai_chat_widget['stt_method']) ? $bctai_chat_widget['stt_method'] : 'Audio';
$bctai_elevenlabs_hide_error = get_option('bctai_elevenlabs_hide_error', false);




//style
$bctai_chat_icon = isset($bctai_chat_design['icon']) && !empty($bctai_chat_design['icon']) ? $bctai_chat_design['icon'] : 'default';
$bctai_ai_avatar = isset($bctai_chat_design['ai_avatar']) && !empty($bctai_chat_design['ai_avatar']) ? $bctai_chat_design['ai_avatar'] : 'default';
$bctai_ai_avatar_id = isset($bctai_chat_design['ai_avatar_id']) && !empty($bctai_chat_design['ai_avatar_id']) ? $bctai_chat_design['ai_avatar_id'] : '';
$Header_Color = isset($bctai_chat_design['Header_Color']) && !empty($bctai_chat_design['Header_Color']) ? $bctai_chat_design['Header_Color'] : '#8040ad';
$Button_Icon_Color = isset($bctai_chat_design['Button_Icon_Color']) && !empty($bctai_chat_design['Button_Icon_Color']) ? $bctai_chat_design['Button_Icon_Color'] : '#569bd4';
$Message_Background_Color = isset($bctai_chat_design['Message_Background_Color']) && !empty($bctai_chat_design['Message_Background_Color']) ? $bctai_chat_design['Message_Background_Color'] : '#569bd4';
$Header_Text_Color =isset($bctai_chat_design['Header_Text_Color']) && !empty($bctai_chat_design['Header_Text_Color']) ? $bctai_chat_design['Header_Text_Color'] : '#569bd4';
$bctai_ai_avatar_url = BCTAI_PLUGIN_URL . 'public/images/bctaichatbot_logo_txt.png';
$chatbot_name = isset($bctai_chat_design['chatbot_name']) && !empty($bctai_chat_design['chatbot_name']) ? $bctai_chat_design['chatbot_name'] : 'BCT Ai Chatbot';
if ($bctai_ai_avatar == 'custom' && $bctai_ai_avatar_id != '') {
    $bctai_ai_avatar_url = wp_get_attachment_url($bctai_ai_avatar_id);
}


// voice
$bctai_audio_enable = isset($bctai_chat_widget['audio_enable']) ? $bctai_chat_widget['audio_enable'] : false;
$bctai_mic_color = isset($bctai_chat_widget['mic_color']) ? $bctai_chat_widget['mic_color'] : '#222';
$bctai_stop_color = isset($bctai_chat_widget['stop_color']) ? $bctai_chat_widget['stop_color'] : '#f00';
$bctai_chat_to_speech = isset($bctai_chat_widget['chat_to_speech']) ? $bctai_chat_widget['chat_to_speech'] : false;
$bctai_google_api_key = get_option('bctai_google_api_key', '');


if (empty($bctai_elevenlabs_api) && empty($bctai_google_api_key)) {
    $bctai_chat_to_speech = false;
}

$bctai_chat_voice_service = isset($bctai_chat_widget['voice_service']) && !empty($bctai_chat_widget['voice_service']) ? $bctai_chat_widget['voice_service'] : 'en-US';
$bctai_voice_language = isset($bctai_chat_widget['voice_language']) && !empty($bctai_chat_widget['voice_language']) ? $bctai_chat_widget['voice_language'] : 'en-US';
$bctai_voice_name = isset($bctai_chat_widget['voice_name']) && !empty($bctai_chat_widget['voice_name']) ? $bctai_chat_widget['voice_name'] : 'en-US-Studio-M';
$bctai_voice_device = isset($bctai_chat_widget['voice_device']) && !empty($bctai_chat_widget['voice_device']) ? $bctai_chat_widget['voice_device'] : '';
$bctai_voice_speed = isset($bctai_chat_widget['voice_speed']) && !empty($bctai_chat_widget['voice_speed']) ? $bctai_chat_widget['voice_speed'] : 1;
$bctai_voice_pitch = isset($bctai_chat_widget['voice_pitch']) && !empty($bctai_chat_widget['voice_pitch']) ? $bctai_chat_widget['voice_pitch'] : 0;




//사용자 아이디
$user_id = get_current_user_id();
$user_info = get_userdata($user_id);


if($user_info){
    $user_nickname = $user_info->user_nicename;

}


//기본템플릿
if($Header_Color =='#8040ad'){
    $chatbot_header_text="#ffffff";
    $chatbot_name_color="#352F39";
    $chatbot_background = "#ffffff";
    $AI_bubble = "#f0f0f1";
    $uer_bubble="#8040ad";
    $button="#8040ad";
    $user_font="#FFFFFF";
    $footer_background="#F1F1F1";
    $footer_arrow_color = $user_font;
    $footer_color = $button;
    //Mono
}else if($Header_Color =='#352f39'){
    $chatbot_header_text="#ffffff";
    $chatbot_name_color="#352F39";
    $chatbot_background = "#ffffff";
    $AI_bubble = "#F1F1F1";
    $uer_bubble="#4F4D5F";
    $button="#352F39";
    $user_font="#FFFFFF";
    $footer_background="#F1F1F1";
    $footer_arrow_color = $user_font;
    $footer_color = $button;
    //Dark
}else if($Header_Color =='#352f38'){
    $chatbot_header_text="#ffffff";
    $chatbot_name_color="#F1F1F1";
    $chatbot_background = "#575757";
    $AI_bubble = "#FFFFFF";
    $uer_bubble="#FFFFFF";
    $button="#FFFFFF";
    $user_font="#352F39";
    $footer_background="#352F39";
    $footer_arrow_color = $user_font;
    $footer_color = $button;
    //Light Gery    
}else if($Header_Color =='#F1F1F1'){
    $chatbot_header_text="#352F39";
    $chatbot_name_color="#4F4D5F";
    $chatbot_background = "#FFFFFF";
    $AI_bubble = "#F1F1F1";
    $uer_bubble="#4F4D5F";
    $button="#352F39";
    $user_font="#FFFFFF";
    $footer_background="#F1F1F1";
    $footer_arrow_color = $user_font;
    $footer_color = $button;
    //Electric Orange
}else if($Header_Color =='#F53706'){
    $chatbot_header_text="#FFFFFF";
    $chatbot_name_color="#352F39";
    $chatbot_background = "#FFFFFF";
    $AI_bubble = "#F1F1F1";
    $uer_bubble="#C14D2F";
    $button="#352F39";
    $user_font="#FFFFFF";
    $footer_background="#F1F1F1";
    $footer_arrow_color = $user_font;
    $footer_color = $button;
    //Azure
}else if($Header_Color =='#0080FF'){
    $chatbot_header_text="#FFFFFF";
    $chatbot_name_color="#352F39";
    $chatbot_background = "#FFFFFF";
    $AI_bubble = "#F1F1F1";
    $uer_bubble="#446C94";
    $button="#352F39";
    $user_font="#FFFFFF";
    $footer_background="#F1F1F1";
    $footer_arrow_color = $user_font;
    $footer_color = $button;
    //Irish Green
}else if($Header_Color =='#00A800'){
    $chatbot_header_text="#FFFFFF";
    $chatbot_name_color="#352F39";
    $chatbot_background = "#FFFFFF";
    $AI_bubble = "#F1F1F1";
    $uer_bubble="#50A15B";
    $button="#352F39";
    $user_font="#FFFFFF";
    $footer_background="#F1F1F1";
    $footer_arrow_color = $user_font;
    $footer_color = $button;
}else if($Header_Color =='on'){
    
    $Header_Color = isset($bctai_chat_design['Custom_Header_Color']) && !empty($bctai_chat_design['Custom_Header_Color']) ? $bctai_chat_design['Custom_Header_Color'] : '#ffffff';
    $chatbot_header_text= isset($bctai_chat_design['Header_Text_Color']) && !empty($bctai_chat_design['Header_Text_Color']) ? $bctai_chat_design['Header_Text_Color'] : '#569bd4';
    $chatbot_background = isset($bctai_chat_design['Message_Background_Color']) && !empty($bctai_chat_design['Message_Background_Color']) ? $bctai_chat_design['Message_Background_Color'] : '#569bd4';
    $chatbot_name_color=isset($bctai_chat_design['Message_Name_Color']) && !empty($bctai_chat_design['Message_Name_Color']) ? $bctai_chat_design['Message_Name_Color'] : '#569bd4';
    $Message_Date_Color=isset($bctai_chat_design['Message_Date_Color']) && !empty($bctai_chat_design['Message_Date_Color']) ? $bctai_chat_design['Message_Date_Color'] : '#569bd4';
    $AI_bubble = isset($bctai_chat_design['Chatbot_Message_Background']) && !empty($bctai_chat_design['Chatbot_Message_Background']) ? $bctai_chat_design['Chatbot_Message_Background'] : '#569bd4';
    $Chatbot_Message_Text=isset($bctai_chat_design['Chatbot_Message_Text']) && !empty($bctai_chat_design['Chatbot_Message_Text']) ? $bctai_chat_design['Chatbot_Message_Text'] : '#569bd4';
    $uer_bubble=isset($bctai_chat_design['User_Message_Background']) && !empty($bctai_chat_design['User_Message_Background']) ? $bctai_chat_design['User_Message_Background'] : '#569bd4';
    $user_font=isset($bctai_chat_design['User_Message_Text']) && !empty($bctai_chat_design['User_Message_Text']) ? $bctai_chat_design['User_Message_Text'] : '#569bd4';
    $button=isset($bctai_chat_design['Typing_Button_Icon_Color']) && !empty($bctai_chat_design['Typing_Button_Icon_Color']) ? $bctai_chat_design['Typing_Button_Icon_Color'] : '#569bd4';
    $footer_background=isset($bctai_chat_design['Typing_Background_Color']) && !empty($bctai_chat_design['Typing_Background_Color']) ? $bctai_chat_design['Typing_Background_Color'] : '#569bd4';

    $Button_Icon_Color=isset($bctai_chat_design['Button_Icon_Color']) && !empty($bctai_chat_design['Button_Icon_Color']) ? $bctai_chat_design['Button_Icon_Color'] : '#569bd4';
    $footer_arrow_color = '#FFFFFF';
    $footer_color = isset($bctai_chat_design['Message_Typing_Color']) && !empty($bctai_chat_design['Message_Typing_Color']) ? $bctai_chat_design['Message_Typing_Color'] : '#569bd4';
    ?>
    
    <style>
    .chatbot-contents .message .date{color: <?php echo esc_html($Message_Date_Color) ?>;}
    .chatbot-contents .message.left .bubble div{color: <?php echo esc_html($Chatbot_Message_Text) ?>;}
    .chatbot-header i {color : <?php echo esc_html ($Button_Icon_Color) ?>;font-size: 19px;}
    </style>
    <?php
}


$bct_menu_structure = get_option('menu_structure','');
$Scenario_status = get_option('Scenario', '');










?>
<style>
.bctai-chatbox .bctai-mic-icon {
    color: <?php echo esc_html($button) ?>;
    position: absolute;
    top: 11px;
    right: 55px;
    font-size: 30px;
}

.btn-remove,.btn-fullscreen,.btn-mail,.btn-Scenario{
    background: <?php echo esc_html($Header_Color) ?>;
    border: 0px;
}

.chatbot-contents .message.right .bubble {
    color: <?php echo esc_html($user_font) ?>;
    background-color:<?php echo esc_html($uer_bubble) ?>;
}
.chatbot-contents {
    padding: 0px 0px 200px 10px;
    <?php
        if($Scenario_status){
    ?>
    padding: 0px 0px 200px 10px;
    <?php } ?>    
    height: 100%;
}

.chatbot-contents .message.left .bubble {
    color: black;
    background:<?php echo esc_html($AI_bubble) ?>;
}



.bctai-chat-widget-typing::placeholder {
    color : <?php echo esc_html($footer_color) ?>;
    line-height: 25px;

}

textarea:focus {
    outline: 0.3px solid <?php echo esc_html($bctai_chat_design['border_text_field']) ?>;
}

.chatbot-contents .message.left .name {
    display: inline-block;
    margin-bottom: 6px;
    padding-left: 5px;
    letter-spacing: -0.5px;
    font-size: 13px;
    font-weight: 500;
    color: <?php echo esc_html($chatbot_name_color)?>;
}
    
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://nsp.pay.naver.com/sdk/js/naverpay.min.js"></script>






<div id="popup-dialog" title="<?php echo __('Contact us','bctai') ?>">

    <span style="font: normal normal normal 14px/20px Noto Sans KR;letter-spacing: 0px;color: #352F39;"><?php echo __('Name','bctai') ?></span><span style="color:red;">*</span>
    <input type="text" id="name" style="height: 52px;background: #F1F1F1 0% 0% no-repeat padding-box;border-radius: 16px;opacity: 1;border:0px;margin-bottom: 15px;">

    <span style="font: normal normal normal 14px/20px Noto Sans KR;letter-spacing: 0px;color: #352F39;"><?php echo __('Email','bctai') ?></span><span style="color:red;">*</span>
    <input type="text" id="email" style="height: 52px;background: #F1F1F1 0% 0% no-repeat padding-box;border-radius: 16px;opacity: 1;border:0px;margin-bottom: 15px;">

    <span style="font: normal normal normal 14px/20px Noto Sans KR;letter-spacing: 0px;color: #352F39;"><?php echo __('Tel','bctai') ?></span>.
    <input type="text" id="phonenumber" style="height: 52px;background: #F1F1F1 0% 0% no-repeat padding-box;border-radius: 16px;opacity: 1;border:0px;margin-bottom: 15px;">

    <span style="font: normal normal normal 14px/20px Noto Sans KR;letter-spacing: 0px;color: #352F39;"><?php echo __('Message','bctai') ?></span>
    <textarea name="" id="contents" cols="30" rows="10" style="height: 140px;background: #F1F1F1 0% 0% no-repeat padding-box;border-radius: 16px;opacity: 1;border:0px;margin-bottom: 15px;"></textarea>
    
    <button id="email-send-button" style="width: 187px; height: 58px; background: #8040AD 0% 0% no-repeat padding-box; border-radius: 16px; border:0px; color:white; display: block; margin:auto;"><?php echo __('SEND','bctai') ?></button>
    
</div>



<div class="bctai-chatbox" id="bctai-chatbox"
    data-stt-method="<?php echo esc_html($bctai_stt_method)?>"
    data-type="widget"
    data-ai-name="<?php echo esc_html($bctai_ai_name)?>"
    data-nonce="<?php echo esc_html(wp_create_nonce('bctai-chatbox')) ?>"
    data-ai-avatar="<?php echo esc_html($bctai_ai_avatar_url) ?>"
    data-url="<?php echo home_url($wp->request) ?>"
    data-speech="<?php echo esc_html($bctai_chat_to_speech) ?>"
    data-post-id="<?php echo get_the_ID() ?>"
    data-user-id="<?php echo $user_nickname?>"
    data-voice="<?php echo esc_html($bctai_elevenlabs_voice) ?>"
    data-voice-error="<?php echo esc_html($bctai_elevenlabs_hide_error) ?>"
    data-voice_service="<?php echo esc_html($bctai_chat_voice_service) ?>"
    data-voice_language="<?php echo esc_html($bctai_voice_language) ?>"
    data-voice_name="<?php echo esc_html($bctai_voice_name) ?>"
    data-voice_device="<?php echo esc_html($bctai_voice_device) ?>"
    data-voice_speed="<?php echo esc_html($bctai_voice_speed) ?>"
    data-voice_pitch="<?php echo esc_html($bctai_voice_pitch) ?>"
    data-welcome-message="<?php echo esc_html( get_option( '_bctai_chatbox_welcome_message', 'Hello human, I am a GPT powered AI chat.' ) )?>"
    data-act-as="<?php echo esc_html($bctai_chat_design['proffesion'])?>"
    data-Scenario_status="<?php echo esc_html($Scenario_status)?>"
>



    <div class="column-m high">
        <div class="chatbot">

            <div class="chatbot-header"style="background-color: <?php echo esc_html($Header_Color) ?>">
                <h2 style="color: <?php echo esc_html($chatbot_header_text) ?>;font-size: 18px;margin: auto 0px;font-weight: normal;font-family: 'Inter';"><?php echo esc_html($chatbot_name) ?></h2>
                <div class="right">
                    <button type="button" class="btn-Scenario" onclick="javascript:openPop('modalMenu');"><i class="fa-solid fa-list"></i></button>
                    <!-- <button type="button" class="btn-mail"><i class="fa-solid fa-envelope"></i></button> -->
                    <button type="button" class="btn-remove" onclick=removeChatlog()><i class="fas fa-redo-alt"></i></button>
                    <button type="button" class="btn-fullscreen"><i class="fa-solid fa-expand"></i></button>
                     
                </div>

            </div>

            <div class="center">
                <button type="button" class="selectMenu" onclick="javascript:openPop('modalMenu');">menu</button>
            </div>
            

            <?php
            if($Scenario_status){
            ?>
            <div style="width:100%; height:100px;display:flex; background:white;">

                <div style="width: 50%;">
                    <div class="order_wrap" style="border-bottom: 0px;">
                        <span>HOT아메리카노</span>       
                    </div>

                    <div class="order_wrap">
                        <span>HOT카페라떼</span>
                    </div>
                </div>

                <div style="width: 50%;">
                    <div class="order_wrap" style="border-bottom: 0px;">
                        <span>ICE아메리카노</span>
                    </div>
                    <div class="order_wrap">
                        <span>ICE카페라떼</span>
                    </div>
                </div>
            </div>
            <?php }?>


            <div class="chatbot-contents"  id ="chatbot-contents"style="background : <?php echo esc_html($chatbot_background) ?>;">
                <div class="messages" id ="messages">
                    <?php $filtered_messages = [];

                                        if($user_id){
                                            $chat_history_qurry = $wpdb->prepare("
                                            SELECT * FROM ".$wpdb->prefix."bctai_chatlogs 
                                            WHERE created_at >= UNIX_TIMESTAMP(NOW() - INTERVAL 7 DAY)
                                            AND SOURCE = 'widget'
                                            AND user_id = '%d'
                                            ORDER BY created_at",$user_id);
                                        }else{
                                            $cookie_value =md5($_COOKIE['bctai_chat_client_id']);
                                            $chat_history_qurry = $wpdb->prepare("
                                            SELECT * FROM ".$wpdb->prefix."bctai_chatlogs 
                                            WHERE created_at >= UNIX_TIMESTAMP(NOW() - INTERVAL 7 DAY)
                                            AND SOURCE = 'widget'
                                            AND log_session = '%s'
                                            ORDER BY created_at",$cookie_value);
                                        }
                                        $bctai_chat_historys = $wpdb->get_results($chat_history_qurry);
                                        if($bctai_chat_historys && is_array($bctai_chat_historys) && count($bctai_chat_historys)) {
                                            foreach( $bctai_chat_historys as $bctai_chat_history) {

                                                $all_messages = json_decode($bctai_chat_history->data, true);

                                                for ($i = 0; $i < count($all_messages); $i += 2) {
                                                        $filtered_messages[] = $all_messages[$i];
                                                        $filtered_messages[] = $all_messages[$i + 1];
                                                }
                                                
                                            }
                                        }
                                    foreach($filtered_messages as $item){
                                        date_default_timezone_set('Asia/Seoul');

                                        if (isset($item['type']) && $item['type'] === 'ai') {
                                    ?>
                    <div style="width: 100%; float: left;margin-top: 10px;">
                        <div class="message left">
                            <span class="name"><img src="<?php echo esc_html($bctai_ai_avatar_url) ?>" alt=""><i class="cbiCon"></i><?php echo esc_html($bctai_ai_name)?></span>
                            <div class="bubble">
                                <div class="txt"><?php echo nl2br($item['message']) ?></div>
                            </div>
                            <div class="date"><?php echo(date('Y-m-d H:i', $item['date']))?></div>
                        </div>
                    </div>

                    <?php }else{?>
                    <div style="width: 100%; float: right;margin-top: 10px;">
                        <div class="message right">
                            <span class="name"><i class="cbiCon"></i></span>
                            <div class="bubble">
                                <div class="txt"><?php echo nl2br($item['message']) ?></div>
                            </div>
                            <div class="date"><?php echo(date('Y-m-d H:i', $item['date']))?></div>
                        </div>
                    </div>
                    <?php }}?>

                </div>
                <span class="bctai-bot-thinking"style="color: <?php echo esc_html($bctai_chat_design['Header_icon_color']) ?>; display:none;">
                    <?php echo esc_html($bctai_ai_thining) ?>
                    <span class="bctai-jumping-dots">
                        <span class="bctai-dot-1">.</span>
                        <span class="bctai-dot-2">.</span>
                        <span class="bctai-dot-3">.</span>
                    </span>
                </span>
            </div>




            <div class="chatbotWriteWrap" style="position: absolute;bottom: 0px;left: 0;width: 100%;display: flex;">
                <div class="inputBtnArea">
                    <button type="button" class="btn onlyIcon iconFile" onclick="alert('click')"><span class="blind">파일첨부</span></button>
                </div>
                <textarea name="text" class="textAreaBox" id="textAreaBox" placeholder="<?php echo esc_html( get_option( '_bctai_typing_placeholder', 'Type a message' ) )?>"></textarea>
                
                <div class="inputBtnArea">
                    <button type="button" class="btn onlyIcon iconChatSpeak"><span class="blind">음성으로 메시지 입력</span></button>
                    <button type="button" class="btn onlyIcon iconChatTyping"><span class="blind">타이핑으로 메시지 입력</span></button>
                </div>
            </div>
            



            <div class="chatbot-footer" style="background:<?php echo esc_html($footer_background)?>;display:none;">
                <div style="width: 80%; height: 100%;">
                    <textarea id="bctai-chat-widget-typing"class="bctai-chat-widget-typing"placeholder="<?php echo esc_html( get_option( '_bctai_typing_placeholder', 'Type a message' ) )?>"style="background:<?php echo esc_html($footer_background)?>; color:<?php echo esc_html($footer_color) ?>;"></textarea>
                </div>
                <div style="width: 20%; height: 100%;">
                    <?php if ($bctai_audio_enable):?>
                    <span class="bctai-mic-icon" data-type="widget">
                        <i class="fa-solid fa-microphone-lines"></i>
                    </span>
                    <?php endif; ?>
                    <button type="button" class="btn-send_widget" id="send-button" style="background:<?php echo esc_html($button);?>; color:<?php echo esc_html($footer_arrow_color);?>">
                        <i class="fa-solid fa-arrow-up"></i>
                    </button>
                </div>
            </div>

            


        </div>

        <div class="popup modalMenu">
            <div class="modalWrap modalFull">
                <div class="modalContainer">
                    <h1 class="blind">전체 메뉴</h1>
                    <div class="modalHeader">
                        <button class="btnCloseMenu"><span class="blind">메뉴 닫기</span></button>
                    </div>
                    
                    <ul class="menuArea">
                        <li><a href="">Menu Dapth 1</a></li>
                        <li><a href="">Menu Dapth 2</a></li>
                        <li><a href="">Menu Dapth 3</a></li>
                    </ul>
                    <button type="button" class="btn btnXL btnWhite iconFaq" onclick="javascript:openPop('modalFaq')" style="border:1px solid black;"><span>1:1 문의</span></button>
                    <ul class="snsArea">
                        <li><a href="#" class="btnHome"><span class="blind">home</span></a></li>
                        <li><a href="#" class="btnInstagram"><span class="blind">Instagram</span></a></li>
                        <li><a href="#" class="btnFacebook"><span class="blind">Facebook</span></a></li>
                        <li><a href="#" class="btnX"><span class="blind">X</span></a></li>
                        <li><a href="#" class="btnYoutube"><span class="blind">Youtube</span></a></li>
                    </ul>
                    <ul class="footerManuArea">
                        <li><a href="">소개</a></li>
                        <li><a href="">이용약관</a></li>
                        <li><a href="">개인정보취급방침</a></li>
                    </ul>
                    <button class="btnSet" onclick="javascript:openPop('modalSetting')" type="button"><span class="blind">설정</span></button>
                </div>
            </div>
        </div>

        <div class="popup modalSetting">
        <div class="modalWrap modalFull">
          <div class="modalContainer">
            <h1 class="blind">설정</h1>
            <div class="modalHeader">
              <button class="btnCloseMenu"><span class="blind">설정창 닫기</span></button>
            </div>

            <div class="settingFormWrap">
              <div class="article">
                <div class="popupFormTitle">챗봇 이름</div>
                <div class="settingFormContent">
                  <input type="text" title="" class="center" placeholder="<?php echo esc_html($chatbot_name) ?>">
                </div>
              </div>

              <div class="article">
                <div class="popupFormTitle">메뉴</div>
                <div class="settingFormContent">
                  <ul class="menuSet">
                    <li class="menuItem">
                      <span>메뉴1</span>
                      <button class="btnDelMenu"></button>
                    </li>
                    <li class="menuItem">
                      <span>메뉴1</span>
                      <button class="btnDelMenu"></button>
                    </li>
                  </ul>
                  <button type="button" class="btn btnXL btnWhite iconAdd"><span>1:1 문의</span></button>
                </div>
              </div>

              <div class="article">
                <div class="popupFormTitle">챗봇아이콘</div>
                <div class="settingFormContent">
                  <ul class="chatbotIconSet squareMenu">
                    <li class="chatbotIconItem">
                      <button type="button" class="btn on">
                        <!-- <img src="../assets/images/chatbot/logo_bctaichatbot.png" alt="BTC AI chatbot icon"> -->
                        <div class="squareMenuText">BCT Ai Chatbot Basic</div>
                      </button>
                    </li>
                    <li class="chatbotIconItem">
                      <button type="button" class="btn">
                        <!-- <img src="../assets/images/chatbot/icon_chatbot_upload.png" alt="flie upload"> -->
                        <div class="squareMenuText">Upload Icon <br>(30x30 px, png)</div>
                      </button>
                    </li>
                    <li class="chatbotIconItem">
                      <button type="button" class="btn v2">
                        <!-- <img src="../assets/images/chatbot/logo_bctaichatbot_l.png" alt=""> -->
                        <div class="squareMenuText">
                          <strong>abcdefghijklmnsedgww</strong>
                          <span>Upload Icon (100x100 px, png)</span>
                        </div>
                      </button>
                    </li>
                  </ul>
                </div>
              </div>

              <div class="article">
                <div class="popupFormTitle">위젯아이콘</div>
                <div class="settingFormContent">
                  <ul class="chatbotIconSet squareMenu">
                    <li class="chatbotIconItem">
                      <button type="button" class="btn on">
                        <!-- <img src="../assets/images/chatbot/logo_bctaichatbot_l.png" alt="BTC AI chatbot icon"> -->
                        <div class="squareMenuText">BCT Ai Chatbot Basic</div>
                      </button>
                    </li>
                    <li class="chatbotIconItem">
                      <button type="button" class="btn">
                        <!-- <img src="../assets/images/chatbot/icon_chatbot_upload.png" alt="flie upload"> -->
                        <div class="squareMenuText">Upload Icon <br>(30x30 px, png)</div>
                      </button>
                    </li>
                  </ul>
                </div>
              </div>

              <div class="article">
                <div class="popupFormTitle">챗봇스타일</div>
                <div class="settingFormContent">
                  <ul class="chatbotIconSet squareMenu">
                    <li class="chatbotIconItem">
                      <button type="button" class="btn on">
                        <!-- <img src="../assets/images/chatbot/icon_chatbot_style_basic.png" alt=""> -->
                        <div class="squareMenuText">BCT Ai Chatbot Basic</div>
                      </button>
                    </li>
                    <li class="chatbotIconItem">
                      <button type="button" class="btn">
                        <!-- <img src="../assets/images/chatbot/icon_chatbot_style_mono.png" alt=""> -->
                        <div class="squareMenuText">Mono</div>
                      </button>
                    </li>
                    <li class="chatbotIconItem">
                      <button type="button" class="btn">
                        <!-- <img src="../assets/images/chatbot/icon_chatbot_style_dark.png" alt=""> -->
                        <div class="squareMenuText">Dark</div>
                      </button>
                    </li>
                    <li class="chatbotIconItem">
                      <button type="button" class="btn">
                        <!-- <img src="../assets/images/chatbot/icon_chatbot_style_light_grey.png" alt=""> -->
                        <div class="squareMenuText">Light Grey</div>
                      </button>
                    </li>
                    <li class="chatbotIconItem">
                      <button type="button" class="btn">
                        <!-- <img src="../assets/images/chatbot/icon_chatbot_style_electric_orange.png" alt=""> -->
                        <div class="squareMenuText">Electric Orange</div>
                      </button>
                    </li>
                    <li class="chatbotIconItem">
                      <button type="button" class="btn">
                        <!-- <img src="../assets/images/chatbot/icon_chatbot_style_azure.png" alt=""> -->
                        <div class="squareMenuText">Azure</div>
                      </button>
                    </li>
                    <li class="chatbotIconItem">
                      <button type="button" class="btn">
                        <!-- <img src="../assets/images/chatbot/icon_chatbot_style_irish_green.png" alt=""> -->
                        <div class="squareMenuText">Irish Green</div>
                      </button>
                    </li>
                    <li class="chatbotIconItem">
                      <button type="button" class="btn">
                        <!-- <img src="../assets/images/chatbot/icon_set.png" alt="setting"> -->
                        <div class="squareMenuText">Custom Style</div>
                      </button>
                    </li>
                  </ul>
                </div>
              </div>

              <div class="btnArea btnAreaBottom">
                <button type="button" class="btn btnXL bgPrimary">save</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      
    </div>


    

</div>


<script>

    jQuery(document).ready(function($) {
        $('.order_wrap').click(function() {
            $.ajax({
                url: bctai_ajax_url,
                type: 'POST',
                data: {
                    action: 'kakao_pay_request'
                },
                success: function(response) {
                    if (response.success) {
                        console.log('Response:', response.data);
                        if (response.data.next_redirect_pc_url) {
                            window.location.href = response.data.next_redirect_pc_url;
                        }
                    } else {
                        console.error('Error:', response.data);
                    }
                },
                error: function(error) {
                    console.error('Error:', error);
                }
            });
        });
    });

    jQuery('#email-send-button').click(function () {
        var name = jQuery('#name').val();
        var email = jQuery('#email').val();
        var Contact = jQuery('#Contact').val();
        var phonenumber = jQuery('#phonenumber').val();
        var contents = jQuery('#contents').val();

        if (!name || !email || !phonenumber || !contents) {
            alert('<?php echo __('Please enter all fields','bctai')?>');
        }else{
            jQuery.ajax({
                url : bctai_ajax_url,
                type : "POST",
                data :{
                    action : 'mail_send',
                    name: name,
                    email: email,
                    //Contact: Contact,
                    phonenumber: phonenumber,
                    contents: contents
                },
                success: function(response) {
                    console.log(response);
                    if(response){
                        jQuery('#popup-dialog').empty().text("<?php echo __('Your application has been successfully completed!','bctai')?>");
                    }else{
                        alert("<?php echo __('A problem has occurred. Please check your email address','bctai')?>");
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX 요청 실패');
                    console.error('에러:', error);
                }
            });
        }
    });



    var menu = <?php echo json_encode($bct_menu_structure); ?>;
    function Scenario(){
        date_Time = formatAMPM(new Date());
        var bctai_messages_box = document.getElementsByClassName('messages')[0];
        let parentChat = document.getElementsByClassName('bctai-chatbox')[0];
        let bctai_ai_avatar = parentChat.getAttribute('data-ai-avatar');
        let bctai_ai_name = parentChat.getAttribute('data-ai-name');
        var bctai_randomnum = '';
        var cosine_score ='';
        var bctai_response_text = "";
        var bctai_message = "";

        bctai_message += "<div style='width: 100%; float: left; margin-top:10px;'><div class='message left'><span class='name'><img src='" + bctai_ai_avatar + "' height='40' width='40'></img><i class='cbiCon'></i>" + bctai_ai_name + "</span><div class='bubble'>";
        bctai_message += "<div class='txt" + AIMsgIndex + "'></div>";

        bctai_message += "<div>";

        
        for (var index = 0; index < menu.length; index++) {
            var childrenString = JSON.stringify(menu[index]['children']);
            var valueString = JSON.stringify(menu[index]['value']); 
            bctai_message += "<button type='button' onclick='search(" + childrenString + "," + valueString + ")' style='width: 100%;height: 50px;'>" + menu[index]['value'] + "</button>";
        }
        bctai_message += "</div>";

        bctai_message += "</div><div class='date'>" + date_Time + "</div></div>";
        bctaiWriteMessage(bctai_messages_box, bctai_message, bctai_randomnum, bctai_response_text,cosine_score);
    }

    function search(a,b){
        if(!a){
            alert("정보가 없습니다.");
        }
        
        let bctaiMessage = '';
        let bctai_question = b;
        date_Time = formatAMPM(new Date());
        var bctai_messages_box = document.getElementsByClassName('messages')[0];
        let parentChat = document.getElementsByClassName('bctai-chatbox')[0];
        let bctai_ai_avatar = parentChat.getAttribute('data-ai-avatar');
        let bctai_ai_name = parentChat.getAttribute('data-ai-name');
        var bctai_randomnum = '';
        var cosine_score ='';        
        var bctai_response_text = '';
        var bctai_message = "";




        bctai_message += "<div style='width: 100%; float: left; margin-top:10px;'><div class='message left'><span class='name'><img src='" + bctai_ai_avatar + "' height='40' width='40'></img><i class='cbiCon'></i>" + bctai_ai_name + "</span><div class='bubble'>";
        bctai_message += "<div class='txt" + AIMsgIndex + "'></div>";

        bctai_message += "<div>";
        
        for (var index = 0; index < a.length; index++) {
            var childrenString = JSON.stringify(a[index]['children']);
            var valueString = JSON.stringify(a[index]['value']);
            var valueImgUrl = JSON.stringify(a[index]['imgurl']);

            bctai_message += "<button type='button' onclick='korea(" + childrenString + "," + valueString + "," + valueImgUrl + ")' style='width: 100%;height: 50px;'>" + a[index]['value'] + "</button>";
        }
        
        bctai_message += "<button type='button' onclick='Scenario()' style='width: 100%;height: 50px;'>이전으로</button>";
        bctai_message += "</div>";
        bctai_message += "</div><div class='date'>" + date_Time + "</div></div>";
        bctaiMessage += "<div style='width: 100%; float: right; margin-top:10px;'><div class='message right archive'><span class='name'><i class='cbiCon'></i></span><div class='bubble'><div class='txt'>";
        bctaiMessage += bctai_question.replace(/\n/g, '<br>');
        bctaiMessage += "</div></div><div class='date'>" + date_Time + "</div></div></div>";
        bctai_messages_box.innerHTML += bctaiMessage;
        bctai_messages_box.scrollTop = bctai_messages_box.scrollHeight;

        bctaiWriteMessage(bctai_messages_box, bctai_message, bctai_randomnum, bctai_response_text,cosine_score);
    }



    function korea(City,gu,dong){
        console.log(dong);
        console.log(City);
        let bctaiMessage = '';
        let bctai_question =gu;
        date_Time = formatAMPM(new Date());
        var bctai_messages_box = document.getElementsByClassName('messages')[0];
        let parentChat = document.getElementsByClassName('bctai-chatbox')[0];
        let bctai_ai_avatar = parentChat.getAttribute('data-ai-avatar');
        let bctai_ai_name = parentChat.getAttribute('data-ai-name');
        var bctai_randomnum = '';
        var cosine_score ='';        
        var bctai_response_text = '';
        var bctai_message = "";

        bctai_message += "<div style='width: 100%; float: left; margin-top:10px;'><div class='message left'><span class='name'><img src='" + bctai_ai_avatar + "' height='40' width='40'></img><i class='cbiCon'></i>" + bctai_ai_name + "</span><div class='bubble'>";
        bctai_message += "<div class='txt" + AIMsgIndex + "'></div>";

        if(City[0]['Buyurl']){
            bctai_message += "<div>";
            bctai_message += "<img src='"+dong+"'>";
            
            bctai_message += `<button type='button' onclick="window.open('${City[0]['Buyurl']}', '_blank')" style='width: 100%; height: 50px;'>${City[0]['value']}</button>`;
            bctai_message += "<button type='button' onclick='Scenario()' style='width: 100%;height: 50px;'>이전으로</button>";
            bctai_message += "</div>";
        }else{
            bctai_response_text= City[0]['value'];
        }

        



        
        bctai_message += "</div><div class='date'>" + date_Time + "</div></div>";
        bctaiMessage += "<div style='width: 100%; float: right; margin-top:10px;'><div class='message right archive'><span class='name'><i class='cbiCon'></i></span><div class='bubble'><div class='txt'>";
        bctaiMessage += bctai_question;
        bctaiMessage += "</div></div><div class='date'>" + date_Time + "</div></div></div>";
        bctai_messages_box.innerHTML += bctaiMessage;
        bctai_messages_box.scrollTop = bctai_messages_box.scrollHeight;

        bctaiWriteMessage(bctai_messages_box, bctai_message, bctai_randomnum, bctai_response_text,cosine_score);
    }

</script>