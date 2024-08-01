<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Verify nonce
if (isset($_GET['bctai_nonce']) && !wp_verify_nonce($_GET['bctai_nonce'], 'bctai_chatlogs_search_nonce')) {
    die('Security check failed');
}

global $wpdb;
$bctai_log_page = isset($_GET['wpage']) && !empty($_GET['wpage']) ? sanitize_text_field($_GET['wpage']) : 1;
$search = isset($_GET['wsearch']) && !empty($_GET['wsearch']) ? sanitize_text_field($_GET['wsearch']) : '';


$where = '';
if(!empty($search)) {
    $where .= $wpdb->prepare(" AND `data` LIKE %s", '%' . $wpdb->esc_like($search) . '%');
}

$query = "SELECT * FROM ".$wpdb->prefix."bctai_chatlogs WHERE 1=1".$where;

$total_query = "SELECT COUNT(1) FROM (${query}) AS combined_table";

$total = $wpdb->get_var( $total_query );


$items_per_page = 10;
$offset = ( $bctai_log_page * $items_per_page ) - $items_per_page;

$bctai_logs = $wpdb->get_results( $wpdb->prepare( $query . " ORDER BY created_at DESC LIMIT %d, %d", $offset, $items_per_page ) );

$totalPage = ceil($total / $items_per_page);

?>










<form action="" method="get">
    <input type="hidden" name="page" value="bctai_chatgpt">
    <input type="hidden" name="action" value="logs">
    <?php wp_nonce_field('bctai_chatlogs_search_nonce', 'bctai_nonce'); ?>
    <div class="bctai-d-flex mb-5">
        <input style="width: 100%" value="<?php echo esc_html($search)?>" class="regular-text" name="wsearch" type="text" placeholder="Type for search">
        <button class="button button-primary">Search</button>
    </div>
</form>
<table class="wp-list-table widefat fixed striped table-view-list posts">
    <thead>
        <tr>
            <th>SessionID</th>
            <th>Date</th>
            <th>User Message</th>
            <th>AI Response</th>
            <th>Page</th>
            <th>Source</th>
            <th>Token</th>
            <th>Estimated</th>
            <th>IP</th>
            <th>UserID</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody class="bctai-builder-list">
    <?php
    

    if($bctai_logs && is_array($bctai_logs) && count($bctai_logs)) {
        foreach( $bctai_logs as $bctai_log ) {

            $user_info = get_userdata($bctai_log->user_id);
            if(!$user_info){
                $user_nickname = 'visitor';
            }else{
                $user_nickname = $user_info->user_nicename;
            }
            $bctai_flagged = false;
            $last_user_message = '';
            $ip_address = '';
            $last_ai_message = '';
            $all_messages = json_decode($bctai_log->data, true);
            //echo '<pre>'; print_r($all_messages); echo '</pre>';
            $all_messages = $all_messages && is_array($all_messages) ? $all_messages : array();
            $tokens = 0;
            // echo '<pre>'; print_r($all_messages); echo '</pre>';
            foreach(array_reverse($all_messages) as $item) {
                if(isset($item['flag']) && !empty($item['flag'])) {
                    $bctai_flagged = $item['flag'];
                }
            // }
            // foreach(array_reverse($all_messages) as $item){
                if(
                    isset($item['type'])&& $item['type'] == 'user'&& empty($last_user_message)
                ){
                    $last_user_message = $item['message'];
                    $ip_address = isset($item['ip']) ? $item['ip'] : '';
                }

                if(
                    isset($item['type'])&& $item['type'] == 'ai'&& empty($last_ai_message)
                ){
                    $last_ai_message = $item['message'];
                }
                if(!empty($last_ai_message) && !empty($last_user_message)){
                    break;
                }
                if(isset($item['token']) && !empty($item['token'])){
                    $tokens += $item['token'];
                }

            }
            $estimated = $tokens * 0.000002;
            // echo '<pre>'; print_r($all_messages); echo '</pre>';
            ?>
            
            <tr>
                <td><?php echo esc_html($bctai_log->id)?></td>
                <td><?php echo esc_html(gmdate('Y.m.d  H:i',$bctai_log->created_at))?></td>
                <td><?php echo esc_html(substr($last_user_message,0,255))?></td>
                <td><?php echo esc_html(substr($last_ai_message,0,255))?></td>
                <td><?php echo esc_html($bctai_log->page_title)?></td>
                <td><?php echo $bctai_log->source == 'widget' ? 'Chat Widget' : ($bctai_log->source == 'shortcode' ? 'Chat Shortcode' : esc_html($bctai_log->source))?></td>
                <td><?php echo $tokens > 0 ? esc_html($tokens) : '--'?></td>
                <td><?php echo $estimated > 0 ? esc_html($estimated).'$' : '--'?></td>
                <td><?php echo esc_html($ip_address)?></td>
                <td><?php echo esc_html($user_nickname)?></td>
                <td>
                    <button class="button button-primary button-small bctai-log-messages" data-messages="<?php echo esc_html(htmlspecialchars(wp_json_encode($all_messages),ENT_QUOTES, 'UTF-8'))?>">View</button>
                </td>
                
            </tr>
            
            <?php
            
        }
    }
    ?>
    </tbody>
</table>
<div class="bctai-paginate">
<?php
if($totalPage > 1) {
    echo paginate_links( array(
        'base'         => admin_url('admin.php?page=AI+ChatBot&action=logs&wpage=%#%'),
        'total'        => $totalPage,
        'current'      => $bctai_log_page,
        'format'       => '?wpage=%#%',
        'show_all'     => false,
        'prev_next'    => false,
        'add_args'     => false,
    ));
}
?>
</div>
<script>
    jQuery(document).ready(function ($){
        $('.bctai_modal_close').click(function (){
            $('.bctai_modal_close').closest('.bctai_modal').hide();
            $('.bctai-overlay').hide();
        });
        function htmlEntities(str) {
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }
        function bctaiReplaceStr(str) {
            str = str.replace(/\\n/g,'---NEWLINE---');
            str = str.replace(/\n/g,'---NEWLINE---');
            str = str.replace(/\t/g,'---NEWTAB---');
            str = str.replace(/\\t/g,'---NEWTAB---');
            str = str.replace(/\\/g,'');
            str = str.replace(/---NEWLINE---/g,"\n");
            str = str.replace(/---NEWTAB---/g,"\t");
            return str;
        };
        $('.bctai-log-messages').click(function (){            
            var bctai_messages = $(this).attr('data-messages');
            if(bctai_messages !== ''){
                bctai_messages = JSON.parse(bctai_messages);
                console.log(bctai_messages);
                var html = '';
                $('.bctai_modal_title').html('<?php echo esc_html__('View Chat Log','wp-bct-ai')?>');
                $.each(bctai_messages, function (idx, item){
                    html += '<div class="bctai_message" style="margin-bottom: 10px;">';
                    if(item.type === 'ai'){
                        html += '<strong><?php echo esc_html__('AI','wp-bct-ai')?>:</strong>&nbsp;';
                    }
                    else{
                        html += '<strong><?php echo esc_html__('User','wp-bct-ai')?>:</strong>&nbsp;';
                    }
                    let html_Entities = htmlEntities(item.message);
                    //console.log(html_Entities);
                    html_Entities = html_Entities.replace(/\\/g,'');
                    html += html_Entities.replace(/```([\s\S]*?)```/g,'<code>$1</code>');
                    if(typeof item.flag !== "undefined" && item.flag !== '' && item.flag !== false){
                        html += '<span style="display: inline-block;font-size: 12px;font-weight: bold;background: #b71a1a;padding: 1px 5px;border-radius: 3px;color: #fff;margin-left: 5px;"><?php echo esc_html__('Flagged as','wp-bct-ai')?> '+item.flag+'<span>';
                    }
                    if(typeof item.request !== "undefined" && typeof item.request === 'object'){
                        html += '<a href="javascript:void(0)" class="show_message_request">[<?php echo esc_html__('details','wp-bct-ai')?>]</a>';
                        html += '<div class="bctai_request" style="display: none;padding: 10px;background: #e9e9e9;border-radius: 4px;"><pre style="white-space: pre-wrap">'+bctaiReplaceStr(JSON.stringify(item.request,undefined, 4))+'</pre></div>';
                    }
                    html += '</div>';
                })
                $('.bctai_modal_content').html(html);
                $('.bctai-overlay').show();
                $('.bctai_modal').show();
            }
        });
        $(document).on('click','.show_message_request', function (e){
            let el = $(e.currentTarget);
            if(el.hasClass('activeated')){
                el.removeClass('activeated');
                el.html('[<?php echo esc_html__('details','wp-bct-ai')?>]');
                el.closest('.bctai_message').find('.bctai_request').slideUp();
            }
            else{
                el.addClass('activeated');
                el.html('[<?php echo esc_html__('hide','wp-bct-ai')?>]');
                el.closest('.bctai_message').find('.bctai_request').slideDown();
            }
        })
    })
</script>