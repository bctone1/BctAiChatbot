<?php
if (!defined('ABSPATH'))
    exit;
$bctai_action = isset($_GET['action']) && !empty($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<div><img src="<?php echo BCTAI_PLUGIN_URL . 'public/images/bctaichatbot_logo.png' ?>" style="margin: 15px 0px;"></div>

    <div class="bctai_container">

        <div class="menu_wrap" style="overflow: hidden; width: 100%;">
            <ul class="BCT_nav">
                <li class="sub_menu"><a href="admin.php?page=bctaichat"><i class="fas fa-solid fa-house fa-lg" style="color:#ffffff;"></i>   <?php echo __('General Setting', 'bctai') ?></a></li>
                <li class="sub_menu" style="background:#352f39;"><a href="admin.php?page=Embeddings"><i class="fas fa-solid fa-quote-left fa-lg" style="color:#ffffff;"></i>   <?php echo __('Embeddings', 'bctai') ?></a></li>
                <li class="sub_menu"><a href="admin.php?page=Fine-tuning"><i class="fas fa-code fa-lg"style="color: #ffffff;"></i>   <?php echo __('Fine-tuning', 'bctai') ?> </a></li>
                <li class="sub_menu"><a href="admin.php?page=Audio"><i class="fa-solid fa-volume-high fa-lg"style="color: #ffffff;"></i>   <?php echo __('Audio', 'bctai') ?></a></li>
                <li class="sub_menu"><a href="admin.php?page=AI+ChatBot"><i class="fa-solid fa-comment-dots fa-lg"style="color: #ffffff;"></i>   <?php echo __('AI ChatBot', 'bctai') ?> </a></li>
                <li class="sub_menu"><a href="admin.php?page=Statistics"><i class="fas fa-solid fa-chart-simple fa-lg"style="color: #ffffff;"></i>  <?php echo __('Statistics', 'bctai') ?> </a></li>
                
            </ul>
            <ul class="sub_menu_ul">
                <li class="nav_object"><a href="admin.php?page=bctaichat"><?php echo __('Home', 'bctai') ?></a></li>
                <li class="nav_object"><a href="admin.php?page=bctaichat&action=Setting"><?php echo __('Setting', 'bctai') ?></a></li>
            </ul>
            <ul class="sub_menu_ul" style="display:flex;">
                <li class="nav_object"><a href="admin.php?page=Embeddings" id ="tag_bold1"><?php echo __('Content Builder','bctai')?></a></li>
                <li class="nav_object"><a href="admin.php?page=Embeddings&action=entries" id ="tag_bold2"><?php echo __('Entries','bctai')?></a></li>                
                <li class="nav_object"><a href="admin.php?page=Embeddings&action=builder" id ="tag_bold3"><?php echo __('Index Builder','bctai')?></a></li>
                
                <li class="nav_object">
                    <a href="admin.php?page=Embeddings&action=pdf" id ="tag_bold4"><?php echo __('PDF','bctai')?>
                        <span style="width: 24px;height: 15px;background: #F53706 0% 0% no-repeat padding-box;border-radius: 6px;display: inline-block;line-height: 13px;">
                            <span style="font: normal normal normal 8px/11px Noto Sans KR;">PRO</span>
                        </span>
                    </a>
                </li>
                <li class="nav_object">
                    <a href="admin.php?page=Embeddings&action=kboard" id ="tag_bold5"><?php echo __('kboard','bctai')?>
                        <span style="width: 24px;height: 15px;background: #F53706 0% 0% no-repeat padding-box;border-radius: 6px;display: inline-block;line-height: 13px;">
                            <span style="font: normal normal normal 8px/11px Noto Sans KR;">PRO</span>
                        </span>
                    </a>
                </li>

            </ul>

            <ul class="sub_menu_ul">
                <li class="nav_object"><a href="admin.php?page=Fine-tuning&action=manual"><?php echo __('Data Entry','bctai')?></a></li>
                <li class="nav_object"><a href="admin.php?page=Fine-tuning&action=upload"><?php echo __('Upload','bctai')?></a></li>
                <li class="nav_object"><a href="admin.php?page=Fine-tuning&action=data"><?php echo __('Data Converter','bctai')?></a></li>
                <li class="nav_object"><a href="admin.php?page=Fine-tuning&action=files"><?php echo __('Datasets','bctai')?></a></li>
                <li class="nav_object"><a href="admin.php?page=Fine-tuning&action=fine-tunes"><?php echo __('Trainings','bctai')?></a></li>
            </ul>

            <ul class="sub_menu_ul">
                <li class="nav_object"><a href="admin.php?page=Audio"><?php echo __('Speech To Text','bctai')?></a></li>
                <li class="nav_object"><a href="admin.php?page=Audio&action=Web_Speech_API"><?php echo __('Web Speech API','bctai')?></a></li>
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
                <li class="nav_object"><a href="admin.php?page=Statistics"><?php echo __('Dashboard','bctai')?></a></li>
            </ul>

            
        </div>



        <div id="fs_account">
        <?php
        if (empty($bctai_action)) {
            echo "<style>#tag_bold1 {font-size: 14px;font-weight: normal;color: #fff;}</style>";
            include __DIR__ . '/content-builder.php';
            
        } elseif ($bctai_action == 'entries') {
            echo "<style>#tag_bold2 {font-size: 14px;font-weight: normal;color: #fff;}</style>";
            include __DIR__ . '/entries.php';
            
        } elseif ($bctai_action == 'builder') {
            echo "<style>#tag_bold3 {font-size: 14px;font-weight: normal;color: #fff;}</style>";
            include __DIR__ . '/builder.php';
            
        } elseif ($bctai_action == 'pdf') {
            echo "<style>#tag_bold4 {font-size: 14px;font-weight: normal;color: #fff;}</style>";

            if ( bct_fs()->is_plan('pro') ) {
                include __DIR__ . '/pdf.php'; 
            }else{
                include __DIR__ . '/getpro.php';
            }
            
        } elseif ($bctai_action == 'settings') {
            echo "<style>#tag_bold4 {font-size: 14px;font-weight: normal;color: #fff;}</style>";
            include __DIR__ . '/settings.php';
            
        } elseif ($bctai_action == 'kboard') {
            echo "<style>#tag_bold5 {font-size: 14px;font-weight: normal;color: #fff;}</style>";

            if ( bct_fs()->is_plan('pro') ) {
                include __DIR__ . '/kboard.php';
            }else{
                include __DIR__ . '/getpro.php';
            }

            
            
        }elseif ($bctai_action == 'custompost') {
            echo "<style>#tag_bold5 {font-size: 14px;font-weight: normal;color: #fff;}</style>";
            include __DIR__ . '/custom_post_type.php';   
        }
        
        ?>
        </div>
    </div>





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
                var section = $sub_menu_ul.eq(1);
                var hov_menu = $sub_menu.eq(1);

                hov_menu.css("background", "#352f39");
                section.css("display", "flex");

            });


        }, function () {
            $sub_menu.css("background", "#8040ad");
            $sub_menu_ul.css("display", "none");

            var idx = $(this).index();
            var section = $sub_menu_ul.eq(1);
            var hov_menu = $sub_menu.eq(1);

            hov_menu.css("background", "#352f39");
            section.css("display", "flex");
        });
    });
</script>