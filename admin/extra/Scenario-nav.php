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
            //'bctai_Scenario_category'
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




$bct_menu_structure = get_option('menu_structure','');
//echo '<pre>'; print_r($bct_menu_structure); echo '</pre>';


function generateMenu($menuArray) {

    foreach ($menuArray as $menuItem) {

        $html .= '<li class="menu-item" draggable="true" data-id="' . htmlspecialchars($menuItem['id']) .'">';
        $html .= '<input type="text" value="' . htmlspecialchars($menuItem['value']) . '" data-id="' . htmlspecialchars($menuItem['id']) . '">';
        $html .= '<input type="text" value="' . htmlspecialchars($menuItem['imgurl']) . '" class="test1234" placeholder="Img url 입력">';
        $html .= '<input type="text" value="' . htmlspecialchars($menuItem['Buyurl']) . '" class="BuyLink" placeholder="BuyLink">';
        $html .='<button type="button" onclick="deleteLi(this)">삭제</button>';

        if (isset($menuItem['children']) && is_array($menuItem['children'])) {
            $html .= '<ul class="sub-menu">';

            foreach($menuItem['children'] as $submenuItem){

                $html .= '<li class="menu-item" draggable="true" data-id="' . htmlspecialchars($submenuItem['id']) .'">';
                $html .= '<input type="text" value="' . htmlspecialchars($submenuItem['value']) . '" data-id="' . htmlspecialchars($submenuItem['id']) . '">';
                $html .= '<input type="text" value="' . htmlspecialchars($submenuItem['imgurl']) . '" class="test1234" placeholder="Img url 입력">';
                $html .= '<input type="text" value="' . htmlspecialchars($submenuItem['Buyurl']) . '" class="BuyLink" placeholder="BuyLink">';
                $html .='<button type="button" onclick="deleteLi(this)">삭제</button>';

                if (isset($submenuItem['children']) && is_array($submenuItem['children'])) {
                    $html .= '<ul class="sub-menu">';

                    foreach($submenuItem['children'] as $lastmenuItem){

                        $html .= '<li class="menu-item" draggable="true" data-id="' . htmlspecialchars($lastmenuItem['id']) .'">';
                        $html .= '<input type="text" value="' . htmlspecialchars($lastmenuItem['value']) . '" data-id="' . htmlspecialchars($lastmenuItem['id']) . '">';
                        $html .= '<input type="text" value="' . htmlspecialchars($lastmenuItem['imgurl']) . '" class="test1234" placeholder="Img url">';
                        $html .= '<input type="text" value="' . htmlspecialchars($lastmenuItem['Buyurl']) . '" class="BuyLink" placeholder="BuyLink">';
                        $html .='<button type="button" onclick="deleteLi(this)">삭제</button>';
                        
                        $html .= '</li>';
                    }
                    $html .= '</ul>';
                }

                $html .= '</li>';
            }
            $html .= '</ul>';
            
        }

        $html .= '</li>';
    }

    return $html;
}

$menuHtml = generateMenu($bct_menu_structure);



$bctai_chat_model = get_option('bctai_chat_model','');
$bctai_chat_language = get_option('bctai_chat_language', '');
if ( !empty($errors)) {
    echo "<h4 id='setting_message' style='color: red;'>" . esc_html( $errors ) . "</h4>";
} elseif(!empty($message)) {
    echo "<h4 id='setting_message' style='color: green;'>" . esc_html( $message ) . "</h4>";
}



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










   #menu-list {
  list-style-type: none;
  padding: 40px;
  width: 70%;
}
#menu-list li{
    border:0.5px solid gray;

}
#menu-list input{
    width: 500px;

}

.menu-item {
  background-color: #f4f4f4;
  padding: 10px;
  /* margin-bottom: 5px; */
  cursor: grab;
}

.menu-item:active {
  cursor: grabbing;
}

.sub-menu {
  list-style-type: none;
  padding-left: 20px;
  margin-top: 5px;
}


    
</style>




<h2>메뉴 관리</h2>

<div style="display: flex;">


    <div class="setting_wrap" style="width: 70%; ">
    <ul id="menu-list" name="menu-list">
        <?php echo $menuHtml; ?>  
    </ul>



    <button id="add-menu-btn">메뉴 추가</button>
    <button id="save-btn">저장</button>

        
    </div>



    <div class="bctai-chatbox-preview-box" style="width: 30%;position: relative;">
        <?php include __DIR__ . '/bctai_chat_widget.php';?>
    </div>
</div>










            


<script>
jQuery(document).ready(function ($){
    $('.bctai-chatbox-preview-box > .bctai_chat_widget').addClass('bctai_widget_open');
    $('.bctai-chatbox-preview-box .bctai_toggle').addClass('bctai_widget_open');

    

    const menuList = document.getElementById('menu-list');
    const saveBtn = document.getElementById('save-btn');
    const addMenuBtn = document.getElementById('add-menu-btn');

    menuList.addEventListener('dragstart', function(e) {
        draggedItem = e.target;
        e.dataTransfer.setData('text/plain', e.target.dataset.id);
    });

    menuList.addEventListener('dragover', function(e) {
        e.preventDefault();
    });

    menuList.addEventListener('drop', function(e) {
        e.preventDefault();
        const id = e.dataTransfer.getData('text/plain');

        const draggedElement = document.querySelector(`[data-id='${id}']`);
        const targetElement = e.target.closest('li');
        const subMenu = targetElement ? targetElement.querySelector('.sub-menu') : null;


        
        if (subMenu) {
            subMenu.appendChild(draggedElement);
        } else if (targetElement) {
            const newSubMenu = document.createElement('ul');
            newSubMenu.classList.add('sub-menu');
            newSubMenu.appendChild(draggedElement);
            //menuList.appendChild(draggedElement);
            targetElement.appendChild(newSubMenu);
        } else {
            menuList.appendChild(draggedElement);
        }
    });

    addMenuBtn.addEventListener('click', function() {
        const newMenuItem = document.createElement('li');
        newMenuItem.classList.add('menu-item');
        newMenuItem.draggable = true;

        const newMenuId = Math.floor(Math.random() * 10000); 
        newMenuItem.setAttribute('data-id', newMenuId);

        const inputElement = document.createElement('input');
        inputElement.type = 'text';
        inputElement.value = `새로운 메뉴 ${newMenuId}`;
        inputElement.setAttribute('data-id', newMenuId);

        const inputImgLink = document.createElement('input');
        inputImgLink.type = 'text';
        inputImgLink.placeholder = 'Img url 입력';
        inputImgLink.classList.add('test1234');

        const inputBuyLink = document.createElement('input');
        inputBuyLink.type = 'text';
        inputBuyLink.placeholder = '구매링크 작성';
        inputBuyLink.classList.add('BuyLink');


        const deleteButton = document.createElement('button'); 
        deleteButton.textContent = '삭제';
        deleteButton.addEventListener('click', function() { 
            newMenuItem.remove();
        });
        

        newMenuItem.appendChild(inputElement);
        newMenuItem.appendChild(inputImgLink);
        newMenuItem.appendChild(inputBuyLink);
        newMenuItem.appendChild(deleteButton); 
        

        menuList.appendChild(newMenuItem);
    });


    saveBtn.addEventListener('click', function() {
        console.log(menuList);
        const menuStructure = getMenuStructure(menuList);
        saveMenuOrder(menuStructure);
    });


    function getMenuStructure(list) {
        const items = list.querySelectorAll(':scope > .menu-item');

        return Array.from(items).map(item => {
            const subMenu = item.querySelector('.sub-menu');
            const input = item.querySelector('input');
            const value = input ? input.value : '';

            const imgInput = item.querySelector('input.test1234');
            const imgurl = imgInput ? imgInput.value : '';

            const BuyInput = item.querySelector('input.BuyLink');
            const Buyurl = BuyInput ? BuyInput.value : '';


            return {
                id: item.dataset.id, 
                value: value,
                imgurl: imgurl,
                Buyurl: Buyurl,
                children: subMenu ? getMenuStructure(subMenu) : [] 
            };
        });
    }


    function saveMenuOrder(menuOrder){
        console.log(menuOrder);

        

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php')?>',
            type:'POST',
            dataType: 'JSON',
            data: {
                nonce: '<?php echo wp_create_nonce('bctai-ajax-action')?>',
                content: menuOrder,
                action: 'bctai_Scenario_menu'
            },
            success: function(res){
                alert("성공");
                console.log(res);
                window.location.reload();
                
                if(res.status === 'success'){

                }else{
                    
                }
            },
            error: function(xhr, status, error) {
                console.error('Ajax 요청 실패:', status, error);
            }
        })
    }

})


function deleteLi(button) {
    var listItem = button.parentElement; 
    listItem.remove(); 
    }
</script>


