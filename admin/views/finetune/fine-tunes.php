<?php
if ( ! defined( 'ABSPATH' ) ) exit;
global $wpdb;
$bctai_files_page = isset($_GET['wpage']) && !empty($_GET['wpage']) ? sanitize_text_field($_GET['wpage']) : 1;
$bctai_files_per_page = 10;
$bctai_files_offset = ( $bctai_files_page * $bctai_files_per_page ) - $bctai_files_per_page;
$bctai_files_count_sql = "SELECT COUNT(*) FROM ".$wpdb->posts." f WHERE f.post_type='bctai_finetune' AND (f.post_status='publish' OR f.post_status = 'future')";
$bctai_files_sql = $wpdb->prepare("SELECT f.*
       ,(SELECT fn.meta_value FROM " . $wpdb->postmeta . " fn WHERE fn.post_id=f.ID AND fn.meta_key='bctai_model' LIMIT 1) as model 
       ,(SELECT fp.meta_value FROM " . $wpdb->postmeta . " fp WHERE fp.post_id=f.ID AND fp.meta_key='bctai_updated_at' LIMIT 1) as updated_at 
       ,(SELECT fm.meta_value FROM " . $wpdb->postmeta . " fm WHERE fm.post_id=f.ID AND fm.meta_key='bctai_name' LIMIT 1) as ft_model 
       ,(SELECT fc.meta_value FROM " . $wpdb->postmeta . " fc WHERE fc.post_id=f.ID AND fc.meta_key='bctai_org' LIMIT 1) as org_id 
       ,(SELECT fs.meta_value FROM " . $wpdb->postmeta . " fs WHERE fs.post_id=f.ID AND fs.meta_key='bctai_status' LIMIT 1) as ft_status 
       ,(SELECT ft.meta_value FROM " . $wpdb->postmeta . " ft WHERE ft.post_id=f.ID AND ft.meta_key='bctai_fine_tune' LIMIT 1) as finetune 
       ,(SELECT fd.meta_value FROM " . $wpdb->postmeta . " fd WHERE fd.post_id=f.ID AND fd.meta_key='bctai_deleted' LIMIT 1) as deleted
        FROM " . $wpdb->posts . " f WHERE f.post_type='bctai_finetune' AND (f.post_status='publish' OR f.post_status = 'future') ORDER BY f.post_date DESC LIMIT %d,%d",$bctai_files_offset,$bctai_files_per_page);
// echo '<pre>'; print_r($bctai_files_sql); echo '</pre>';
$bctai_files = $wpdb->get_results($bctai_files_sql);
$bctai_files_total = $wpdb->get_var( $bctai_files_count_sql );
?>
<style>
    .bctai_delete_finetune,.bctai_cancel_finetune{
        color: #bb0505;
    }
</style>
<h1 style="font-weight: bolder; margin-bottom:20px;"><?php echo __('Trainings', 'bctai') ?></h1></br>

<h1 class="wp-heading-inline"><?php echo esc_html__('Fine-tunes', 'bctai') ?></h1>
<button href="javascript:void(0)" class="page-title-action bctai_sync_finetunes"style="color: #8040ad; border: 1px solid #8040ad;"><?php echo esc_html__('Sync Fine-tunes', 'bctai') ?></button>
<table class="wp-list-table widefat fixed striped table-view-list comments">
    <thead>
        <tr>
            <th><?php echo esc_html__('ID', 'bctai') ?></th>
            <th><?php echo esc_html__('Object', 'bctai') ?></th>
            <th><?php echo esc_html__('Model', 'bctai') ?></th>
            <th><?php echo esc_html__('Created At', 'bctai') ?></th>
            <th><?php echo esc_html__('FT Model', 'bctai') ?></th>
            <th><?php echo esc_html__('Org ID', 'bctai') ?></th>
            <th><?php echo esc_html__('Status', 'bctai') ?></th>
            <th><?php echo esc_html__('Updated', 'bctai') ?></th>
            <th><?php echo esc_html__('Trainings', 'bctai') ?></th>
            <th><?php echo esc_html__('Action', 'bctai') ?></th>
        </tr>
    </thead>
    <tbody>
    <?php
    if($bctai_files && is_array($bctai_files) && count($bctai_files)):
        // echo '<pre>';print_r ($bctai_files);echo '<pre>';
        foreach($bctai_files as $bctai_file):

            ?>
            <tr>
                <td><?php echo esc_html($bctai_file->post_title);?></td>
                <td>fine-tune</td>
                <td><?php echo esc_html($bctai_file->model);?></td>
                <td><?php echo esc_html($bctai_file->post_date);?></td>
                <td><?php echo esc_html($bctai_file->ft_model);?></td>
                <td><?php echo esc_html($bctai_file->org_id);?></td>
                <td class="bctai-finetune-<?php echo !$bctai_file->deleted ? esc_html($bctai_file->ft_status) : 'deleted';?>"><?php echo !$bctai_file->deleted ? esc_html($bctai_file->ft_status) : 'Deleted';?></td>
                <td><?php echo esc_html(gmdate('Y-m-d H:i', strtotime($bctai_file->post_date))); ?></td>
                <td>
                    <a class="bctai_get_other button button-small" data-type="events" data-id="<?php echo esc_html($bctai_file->ID);?>" href="javascript:void(0)" style="color: #8040ad;border-color: #8040ad;margin: 2px 0px;width: 100px;    text-align: center;"><?php echo esc_html__('Events', 'bctai') ?></a><br>
                    <a class="bctai_get_other button button-small mb-5" data-id="<?php echo esc_html($bctai_file->ID);?>" data-type="hyperparameters" href="javascript:void(0)" style="color: #8040ad;border-color: #8040ad;margin: 2px 0px;width: 100px;    text-align: center;"><?php echo esc_html__('Hyper-params', 'bctai') ?></a><br>
                    <a class="bctai_get_other button button-small mb-5" data-id="<?php echo esc_html($bctai_file->ID);?>" data-type="result_files" href="javascript:void(0)" style="color: #8040ad;border-color: #8040ad;margin: 2px 0px;width: 100px;    text-align: center;"><?php echo esc_html__('Result files', 'bctai') ?></a><br>
                    <a class="bctai_get_other button button-small mb-5" data-id="<?php echo esc_html($bctai_file->ID);?>" data-type="training_file" href="javascript:void(0)" style="color: #8040ad;border-color: #8040ad;margin: 2px 0px;width: 100px;    text-align: center;"><?php echo esc_html__('Training-files', 'bctai') ?></a><br>
                </td>
                <td>
                    <?php
                    if(!$bctai_file->deleted):
                        if($bctai_file->ft_status == 'pending'):
                        ?>
                    <a class="bctai_cancel_finetune button button-small button-link-delete" data-id="<?php echo esc_html($bctai_file->ID);?>" href="javascript:void(0)" style="color: #d63638;border-color: #d63638;margin: 2px 0px;width: 100px;    text-align: center;"><?php echo esc_html__('Cancel', 'bctai') ?></a><br>
                    <?php
                        endif;
                        if(!empty($bctai_file->ft_model)):
                    ?>
                    <a class="bctai_delete_finetune button button-small button-link-delete" data-id="<?php echo esc_html($bctai_file->ID);?>" href="javascript:void(0)" style="color: #d63638;border-color: #d63638;margin: 2px 0px;width: 100px;    text-align: center;"><?php echo esc_html__('Delete', 'bctai') ?></a><br>
                    <?php
                        endif;
                    endif;
                    ?>
                </td>
            </tr>
            <?php
        endforeach;
    endif;
    ?>
    </tbody>
</table>
<div class="bctai-paginate mb-5">
    <?php
    echo paginate_links( array(
        'base'      =>  admin_url('admin.php?page=AI+Training&action=fine-tunes&wpage=%#%'),
        'total'     =>  ceil($bctai_files_total / $bctai_files_per_page),
        'current'   =>  $bctai_files_page,
        'format'       => '?wpaged=%#%',
        'show_all'     => false,
        'prev_next'    => false,
        'add_args'     => false,
    ));
    ?>
</div>
<script>
    jQuery(document).ready(function ($){
        var bctaiAjaxRunning = false;
        $('.bctai_modal_close').click(function (){
            $('.bctai_modal_close').closest('.bctai_modal').hide();
            $('.bctai-overlay').hide();
        })
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
        var bctai_get_other = $('.bctai_get_other');
        var bctai_get_finetune = $('.bctai_get_finetune');
        var bctai_cancel_finetune = $('.bctai_cancel_finetune');
        var bctai_delete_finetune = $('.bctai_delete_finetune');
        var bctai_ajax_url = '<?php echo admin_url('admin-ajax.php')?>';
        bctai_cancel_finetune.click(function (){
            var conf = confirm('<?php echo esc_html__('Are you sure?', 'bctai') ?>');
            if(conf) {
                var btn = $(this);
                var id = btn.attr('data-id');
                if (!bctaiAjaxRunning) {
                    bctaiAjaxRunning = true;
                    $.ajax({
                        url: bctai_ajax_url,
                        data: {action: 'bctai_cancel_finetune', id: id,'nonce': '<?php echo wp_create_nonce('bctai-ajax-nonce')?>'},
                        dataType: 'JSON',
                        type: 'POST',
                        beforeSend: function () {
                            bctaiLoading(btn);
                        },
                        success: function (res) {
                            //alert(res.msg)
                            bctaiRmLoading(btn);
                            bctaiAjaxRunning = false;
                            if (res.status === 'success') {
                                window.location.reload();
                            } else {
                                alert(res.msg);
                            }
                        },
                        error: function () {
                            bctaiRmLoading(btn);
                            bctaiAjaxRunning = false;
                            alert('<?php echo esc_html__('Something went wrong', 'bctai') ?>');
                        }
                    })
                }
            }
        });
        bctai_delete_finetune.click(function (){
            var conf = confirm('<?php echo esc_html__('Are you sure?', 'bctai') ?>');
            if(conf) {
                var btn = $(this);
                var id = btn.attr('data-id');
                if (!bctaiAjaxRunning) {
                    bctaiAjaxRunning = true;
                    $.ajax({
                        url: bctai_ajax_url,
                        data: {action: 'bctai_delete_finetune', id: id,'nonce': '<?php echo wp_create_nonce('bctai-ajax-nonce')?>'},
                        dataType: 'JSON',
                        type: 'POST',
                        beforeSend: function () {
                            bctaiLoading(btn);
                        },
                        success: function (res) {
                            //alert(res.msg)
                            bctaiRmLoading(btn);
                            bctaiAjaxRunning = false;
                            if (res.status === 'success') {
                                window.location.reload();
                            } else {
                                alert(res.msg);
                            }
                        },
                        error: function () {
                            bctaiRmLoading(btn);
                            bctaiAjaxRunning = false;
                            alert('<?php echo esc_html__('Something went wrong', 'bctai') ?>');
                        }
                    })
                }
            }
        });
        bctai_get_other.click(function (){
            alert("이벤트 버튼 클릭");
            var btn = $(this);
            var id = btn.attr('data-id');
            var type = btn.attr('data-type');
            var bctaiTitle = btn.text().trim();
            if(!bctaiAjaxRunning){
                bctaiAjaxRunning = true;
                $.ajax({
                    url: bctai_ajax_url,
                    data: {action: 'bctai_other_finetune', id: id, type: type,'nonce': '<?php echo wp_create_nonce('bctai-ajax-nonce')?>'},
                    dataType: 'JSON',
                    type: 'POST',
                    beforeSend: function () {
                        bctaiLoading(btn);
                    },
                    success: function (res){
                        bctaiRmLoading(btn);
                        bctaiAjaxRunning = false;
                        if(res.status === 'success'){
                            $('.bctai_modal_title').html(bctaiTitle);
                            $('.bctai_modal_content').html(res.html);
                            $('.bctai-overlay').show();
                            $('.bctai_modal').show();
                        }
                        else {
                            alert(res.msg);
                            console.log(res);
                        }
                    },
                    error: function (){
                        bctaiRmLoading(btn);
                        bctaiAjaxRunning = false;
                        alert('<?php echo esc_html__('Something went wrong', 'bctai') ?>');
                    }
                })
            }            
        })
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
    })
</script>

