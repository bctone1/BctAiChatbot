<?php
if (!defined('ABSPATH'))
    exit;
global $wpdb;


// $current_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
// $user_id = get_current_user_id();

// echo $user_id;


?>




<h1 style="font: normal normal 900 24px/35px Noto Sans KR;"><?php echo __('Available in the PRO', 'bctai') ?></h1>

<div style="display:flex;">

    <div>
        <p style="font: normal normal bold 16px/22px Noto Sans KR;letter-spacing: -0.16px;color: #352F39;opacity: 1;width: 1067px;"><?php echo __('This feature is provided in the PRO.','bctai')?></p>
       

        

    </div>

    <div style="width: 523px;height: 300px;background: #F1F1F1 0% 0% no-repeat padding-box;border-radius: 16px;opacity: 1; margin-left:auto;text-align: center;padding:50px 0px;">
        <img src="<?php echo BCTAI_PLUGIN_URL . 'public/images/bctaichatbot_logo_F.png' ?>"style="width: 105px;height: 115px;">
        <p style="font: normal normal bold 20px/16px Noto Sans KR;letter-spacing: -0.2px;color: #352F39;opacity: 1;margin:20px 0px 0px 0px;"><?php echo __('Purchase the PRO version and','bctai');?></p>
        <p style="font: normal normal bold 20px/16px Noto Sans KR;letter-spacing: -0.2px;color: #352F39;opacity: 1;margin:10px 0px 0px 0px;"><?php echo __('experience all the expanded exciting features.','bctai');?></p>
        
        <select id="licenses" style="margin: 10px 0px 10px 0px;background: #f1f1f1 url(data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M5%206l5%205%205-5%202%201-7%207-7-7%202-1z%22%20fill%3D%22%23555%22%2F%3E%3C%2Fsvg%3E) no-repeat right 5px top 55%;border-radius: 16px;border: 0px;">
            <option value="1" selected="selected">Single Site License</option>
            <option value="2">2-Site License</option>
            <option value="5">5-Site License</option>
        </select>
        </br>
        <button id="purchase" style="border:0px;cursor: pointer;background: #F53706;width: 142px;height: 58px;display: inline-block;border-radius: 16px;color: white;text-decoration: none;font: normal normal bold 18px / 50px Noto Sans KR;"><?php echo __('GET PRO','bctai');?></button>

    </div>

    
    
</div>

<?php
// if ( bct_fs()->is_plan__premium_only('pro') ) {
//     echo "문의한 내용 통계";
// }

// if ( bct_fs()->is_not_paying() ) {
//     echo '<a href="' . bct_fs()->get_upgrade_url() . '" style="border:0px;cursor: pointer;background: #F53706;width: 142px;height: 58px;display: inline-block;border-radius: 16px;color: white;text-decoration: none;font: normal normal bold 18px / 50px Noto Sans KR;    text-align: center;line-height: 57px;">' .__('GET PRO', 'bctai') .'</a>';
// }
?>

<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="https://checkout.freemius.com/checkout.min.js"></script>
<script>
    var handler = FS.Checkout.configure({
        plugin_id:  '15409',
        plan_id:    '25688',
        public_key: 'pk_68f1d4ad1f4ad58b208fc68574689',
        
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

