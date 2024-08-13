<?php
if (!defined('ABSPATH'))
    exit;
$flag = true;
$errors = '';

?>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<div>
    <div style="text-align: center; margin-bottom: 60px;">
    <img src="<?php echo BCTAI_PLUGIN_URL . 'public/images/bctaichatbot_logo_F.png' ?>" style="width:229px; height:252px;">

    <h1 style="text-align: center;font: normal normal 300 20px / 29px Noto Sans KR;letter-spacing: -0.1px;color: #4F4D5F;margin: 25px 0px 12px 0px;">
        <strong style="text-align: center;font: normal normal 900 20px / 29px Noto Sans KR;letter-spacing: -0.1px;color: #4F4D5F;"><?php echo __('Thanks', 'bctai') ?></strong>
        <?php echo __('For Choosing BCT AI Chatbot', 'bctai') ?>
    </h1>

        <p style="margin: 0px; text-align: center;font: normal normal 300 16px / 23px Noto Sans KR;letter-spacing: 0px;color: #352F39;opacity: 1;"><?php echo __('You can collect a lot of data from the website, learn (fine-tune) it,', 'bctai') ?></p>
        <p style="margin: 0px; text-align: center;font: normal normal 300 16px / 23px Noto Sans KR;letter-spacing: 0px;color: #352F39;opacity: 1;"><?php echo __('and run the learning model to an AI chatbot to provide the beloved chatbot service on your WordPress homepage.', 'bctai') ?></p>
    </div>

    <div style="display:flex;     justify-content: space-evenly;">
        <div class="Home_Box">
            <div>
                <i class="fas fa-solid fa-book fa-2xl" style="color: #8040ad;font-size:40px;    margin: 25px 0px;"></i>
                <h2><?php echo __('Document', 'bctai') ?></h2>
                <p style="margin: 0px;"><?php echo __('Prepared for BCT AI Chatbot users.', 'bctai') ?></p>
                <p style="margin: 0px;"><?php echo __("We'll help you understand how the plugin works and guide you on how to use it.", 'bctai') ?></p>
            </div>

            <a href="https://bctaichatbot.com/blog/category/guides/" target="_blank" class="Home_box_btn" >
                <span><?php echo __('Go Docs', 'bctai') ?></span>
                <i class="fa-solid fa-up-right-from-square"style=""></i>
            </a>
        </div>

        <div class="Home_Box">
            <div>
                <i class="fas fa-solid fa-circle-question fa-2xl" style="color: #8040ad;font-size:40px;    margin: 25px 0px;"></i>
                <h2><?php echo __('Guide', 'bctai') ?></h2>

                <div style="display: flex;">
                    <div style="width:50%;">
                        <p style="margin:5px 0px;"><strong style="color: #9f6fc1;font-size: 18px;margin-right: 5px;">-</strong><a href="https://bctaichatbot.com/blog/category/guides/start/" target='_blank'>Getting started</a></p>
                        <p style="margin:5px 0px;"><strong style="color: #9f6fc1;font-size: 18px;margin-right: 5px;">-</strong><a href="https://bctaichatbot.com/blog/category/guides/embedding-002/" target='_blank'>Embedding</a></p>
                        <p style="margin:5px 0px;"><strong style="color: #9f6fc1;font-size: 18px;margin-right: 5px;">-</strong><a href="https://bctaichatbot.com/blog/category/guides/audio-settings/" target='_blank'>Audio</a></p>
                        <p style="margin:5px 0px;"><strong style="color: #9f6fc1;font-size: 18px;margin-right: 5px;">-</strong><a href="https://bctaichatbot.com/blog/category/guides/statistics/" target='_blank'>Statistics</a></p>
                    </div>
                    <div style="width:50%;">
                        <p style="margin:5px 0px;"><strong style="color: #9f6fc1;font-size: 18px;margin-right: 5px;">-</strong><a href="https://bctaichatbot.com/blog/category/guides/basic-setting/" target='_blank'>API Key Setting</a></p>
                        <p style="margin:5px 0px;"><strong style="color: #9f6fc1;font-size: 18px;margin-right: 5px;">-</strong><a href="https://bctaichatbot.com/blog/category/guides/fine-tuning-settings/" target='_blank'>Fine-tuning</a></p>                        
                        <p style="margin:5px 0px;"><strong style="color: #9f6fc1;font-size: 18px;margin-right: 5px;">-</strong><a href="https://bctaichatbot.com/blog/category/guides/ai-chatbot-settings/" target='_blank'>AI Chatbot</a></p>
                        <p style="margin:5px 0px;"><strong style="color: #9f6fc1;font-size: 18px;margin-right: 5px;">-</strong><a href="https://bctaichatbot.com/blog/category/guides/use-cases/" target='_blank'>Use cases</a></p>
                        
                    </div>
                </div>

                
                
            </div>
        </div>

        <div class="Home_Box">
            <div>
                <i class="fas fa-solid fa-envelope fa-2xl"style="color: #8040ad;font-size:40px;    margin: 25px 0px;"></i>
                <h2><?php echo __('Support', 'bctai') ?></h2>
                <p style="margin: 0px;"><?php echo __('Want to consult with an expert? Take advantage of 1:1 email consultation with BCT AI Chatbot’s experts.', 'bctai') ?></p>
            </div>

            <a href="https://bctaichatbot.com/inquiry/" target="_blank"class="Home_box_btn">
                <span><?php echo __('Get Support', 'bctai') ?></span>
                <i class="fa-solid fa-up-right-from-square"style=""></i>
            </a>
        </div>
    </div>

    <div class="BCT_AI_Chatbot_getPRO">

        <h1 style="color: #b4b3bb;font-size: 20px;font-weight: 100;width: 250px;">
            <span style="font-size:18px; font-weight:300;">BCT AI Chatbot</span>
            <strong style="color: white;font-size:18px; font-weight:900;">PRO</strong>
        </h1>

        <?php echo __('<p style="width: 70%;line-height: 21px;color: #bdbcc3;font-weight:300; font-size: 14px;">Supports upgraded features such as more sophisticated AI training, audio support functions, improved chatbots, and advanced recommendation settings. Introduce BCT AI Chatbot with expanded features at a reasonable price!</p>', 'bctai') ?>
        <a id="purchase"href="#" style="width: 188px;display: block;background: red;position: absolute;right: 0px;border-bottom-right-radius: 16px;border-top-right-radius: 16px;color: white;text-decoration: none;font-size: 18px;line-height: 70px;font-weight: 900;    text-align: center;">GET PRO<i class="fa-solid fa-up-right-from-square" style="color: #ffffff; margin-left: 14px;"></i></a>
    </div>

    
</div>


<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="https://checkout.freemius.com/checkout.min.js"></script>
<script>
    var handler = FS.Checkout.configure({
        plugin_id:  '15409',
        plan_id:    '25688',
        public_key: 'pk_68f1d4ad1f4ad58b208fc68574689',
        // image:      'http://bctr0031.blogcodi.gethompy.com/wp-content/plugins/bct-ai-chatbotv0.7.1/public/images/bctaichatbot_logo_F.png'
    });
    
    $('#purchase').on('click', function (e) {
        handler.open({
            name     : 'bctchatbot',
            licenses : $('#licenses').val(),
            purchaseCompleted  : function (response) {
            },
            success  : function (response) {
            }
        });
        e.preventDefault();
    });
</script>

