<?php
if ( ! defined( 'ABSPATH' ) ) exit;
global $wpdb;
$bctai_chat_widget = get_option('bctai_chat_widget',[]);

$bctai_chat_status = get_option('bctai_chat_status','noachive');
$current_context_ID = get_the_ID();
$bctai_bot_content = $wpdb->get_row("SELECT * FROM ".$wpdb->postmeta." WHERE meta_key='bctai_widget_page_".$current_context_ID."'");

$bctai_chat_design = get_option('bctai_chat_design',[]);
// echo '<pre>'; print_r($bctai_chat_design); echo '</pre>';


if($bctai_bot_content && isset($bctai_bot_content->post_id)) {
    $bctai_bot = get_post($bctai_bot_content->post_id);
    if($bctai_bot) {
        $bctai_chat_widget = json_decode($bctai_bot->post_content, true);
        $bctai_chat_status = 'active';
    }
}



//디자인
$bctai_chat_position = isset($bctai_chat_design['position']) && !empty($bctai_chat_design['position']) ? $bctai_chat_design['position'] : 'left';
$bctai_chat_icon = isset($bctai_chat_design['icon']) && !empty($bctai_chat_design['icon']) ? $bctai_chat_design['icon'] : 'default';
$bctai_chat_icon_url = $bctai_chat_icon == 'default' ||  empty($bctai_chat_icon_url) ? BCTAI_PLUGIN_URL.'public/images/bctaichatbot_logo_txt.png' :  wp_get_attachment_url($bctai_chat_icon_url);



if($bctai_chat_status == 'active'):
?>


<style>
    .btnChatbot {width:100px;height:100px;transform:scale(1);border:0px;background:url(<?php echo esc_html($bctai_chat_icon_url)?>);background-size: cover;}
</style>


<div class="bctai_chat_widget<?php echo $bctai_chat_position == 'left' ? ' bctai_widget_left' : ' bctai_widget_right'?>">
    <div class="bctai_chat_widget_content" style="visibility: hidden;">
        <?php include BCTAI_PLUGIN_DIR . 'admin/extra/bctai_chatbox_widget.php'; ?>
    </div>
    <button class="btnChatbot"><span class="blind">챗봇 오픈</span></button>
</div>









<script>
    var bctai_chat_widget_toggle = document.getElementsByClassName('btnChatbot')[0];
    var bctai_chat_widget = document.getElementsByClassName('bctai_chat_widget')[0];
    var bctai_chat_widget_content = document.getElementsByClassName('bctai_chat_widget_content')[0];
    
    bctai_chat_widget_toggle.addEventListener('click', function (e) {
        
        e.preventDefault();
        if(bctai_chat_widget_toggle.classList.contains('bctai_widget_open')) {
            //alert("닫기");
            bctai_chat_widget_toggle.classList.remove('bctai_widget_open');
            bctai_chat_widget.classList.remove('bctai_widget_open');
            bctai_chat_widget_content.style.overflow = 'hidden';
            bctai_chat_widget_content.style.visibility ='hidden';
        } 
        else {
            //alert("열기");
            bctai_chat_widget_content.style.visibility ='visible';
            bctai_chat_widget.classList.add('bctai_widget_open');
            bctai_chat_widget_toggle.classList.add('bctai_widget_open');
            setTimeout(function() {
                bctai_chat_widget_content.style.overflow = 'unset';
            }, 170);
            if(window.innerWidth < 350) {
                bctai_chat_widget.getElementsByClassName('bctai-chatbox')[0].style.width = window.innerWidth+'px';
                bctai_chat_widget.getElementsByClassName('bctai_chat_widget_content')[0].style.width = window.innerWidth+'px';
            }
        }
    });
    window.onresize = function(){
        if(window.innerWidth < 350){
            bctai_chat_widget.getElementsByClassName('bctai-chatbox')[0].style.width  = window.innerWidth+'px';
            bctai_chat_widget.getElementsByClassName('bctai_chat_widget_content')[0].style.width  = window.innerWidth+'px';
        }
    }

    function closePopup() {
        document.getElementById('popup').style.display = 'none';
    }
</script>
<?php
endif;
?>