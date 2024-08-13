<?php
if ( ! defined( 'ABSPATH' ) ) exit;
global $wpdb;
$bctai_files_page = isset($_GET['wpage']) && !empty($_GET['wpage']) ? sanitize_text_field($_GET['wpage']) : 1;
$bctai_files_per_page = 20;
$bctai_files_offset = ( $bctai_files_page * $bctai_files_per_page ) - $bctai_files_per_page;
$bctai_files_count_sql = "SELECT COUNT(*) FROM ".$wpdb->posts." f WHERE f.post_type='bctai_file' AND (f.post_status='publish' OR f.post_status = 'future')";
$bctai_files_sql = $wpdb->prepare("SELECT f.*
       ,(SELECT fn.meta_value FROM ".$wpdb->postmeta." fn WHERE fn.post_id=f.ID AND fn.meta_key='bctai_filename') as filename 
       ,(SELECT fp.meta_value FROM ".$wpdb->postmeta." fp WHERE fp.post_id=f.ID AND fp.meta_key='bctai_purpose') as purpose 
       ,(SELECT fm.meta_value FROM ".$wpdb->postmeta." fm WHERE fm.post_id=f.ID AND fm.meta_key='bctai_purpose') as model 
       ,(SELECT fc.meta_value FROM ".$wpdb->postmeta." fc WHERE fc.post_id=f.ID AND fc.meta_key='bctai_custom_name') as custom_name 
       ,(SELECT fs.meta_value FROM ".$wpdb->postmeta." fs WHERE fs.post_id=f.ID AND fs.meta_key='bctai_file_size') as file_size 
       ,(SELECT ft.meta_value FROM ".$wpdb->postmeta." ft WHERE ft.post_id=f.ID AND ft.meta_key='bctai_fine_tune') as finetune 
       FROM ".$wpdb->posts." f WHERE f.post_type='bctai_file' AND (f.post_status='publish' OR f.post_status = 'future') ORDER BY f.post_date DESC LIMIT %d,%d",$bctai_files_offset,$bctai_files_per_page);
//var_dump($bctai_files_sql);
$bctai_files = $wpdb->get_results($bctai_files_sql);
//var_dump($bctai_files);
$bctai_files_total = $wpdb->get_var( $bctai_files_count_sql );
$fileTypes = array(
    'fine-tune' => 'Fine-Tune',
//    'answers' => 'Answers',
//    'search' => 'Search',
//    'classifications' => 'Classifications'
);
?>

<?php
$bctaiMaxFileSize = wp_max_upload_size();
if($bctaiMaxFileSize > 104857600){
    $bctaiMaxFileSize = 104857600;
}
?>

<h2 class="sectionTitle">Datasets</h2>

<button href="javascript:void(0)" class="btn btnXS bgPrimary bctai_sync_files" style="margin-bottom:20px;"><?php echo esc_html__('Sync Files','bctai')?></button>



<div class="section">
    <div class="sectionContent">
        <div>
            <div class="tableArea">
<table>
<colgroup>
                    <col style="width:auto">
                    <col style="width:100px;">
                    <col style="width:auto">
                    <col style="width:auto">
                    <col style="width:auto">
                    <col style="width:20%;">
                    </colgroup>
    <thead>
        <tr>
            <th><?php echo esc_html__('ID','bctai')?></th>
            <th><?php echo esc_html__('Size','bctai')?></th>
            <th><?php echo esc_html__('Created At','bctai')?></th>
            <th><?php echo esc_html__('Filename','bctai')?></th>
            <th><?php echo esc_html__('Purpose','bctai')?></th>
            <th><?php echo esc_html__('Action','bctai')?></th>
        </tr>
    </thead>
    <tbody>
    <?php
    if($bctai_files && is_array($bctai_files) && count($bctai_files)):
        foreach($bctai_files as $bctai_file):
            ?>
            <tr>
                <td><?php echo esc_html($bctai_file->post_title)?></td>
                <td><?php echo esc_html(size_format($bctai_file->file_size))?></td>
                <td><?php echo esc_html($bctai_file->post_date)?></td>
                <td><?php echo esc_html($bctai_file->filename)?></td>
                <td><?php echo !empty($bctai_file->purpose) ? esc_html($fileTypes[$bctai_file->purpose]) : 'Fine-Tune'?></td>
                <td>
                    <button data-id="<?php echo esc_html($bctai_file->ID);?>" class="bctai_create_fine_tune btn btnXS bgGray"><?php echo esc_html__('Create Fine-Tune','bctai')?></button>
                    <button data-id="<?php echo esc_html($bctai_file->ID);?>" class="bctai_retrieve_content btn btnXS bgPrimary"><?php echo esc_html__('Retrieve Content','bctai')?></button>
                    <button data-id="<?php echo esc_html($bctai_file->ID);?>" class="button-link-delete bctai_delete_file btn btnXS bgLightGrayBorder"><?php echo esc_html__('Delete','bctai')?></button>
                </td>
            </tr>
        <?php
        endforeach;
    endif;
    ?>
    </tbody>
</table>
</div>
</div>
</div>
</div>


<div class="bctai-paginate mb-5">
    <?php
    echo paginate_links( array(
        'base'      =>  admin_url('admin.php?page=bctai_finetune&wpage=%#%'),
        'total'     =>  ceil($bctai_files_total / $bctai_files_per_page),
        'current'   =>  $bctai_files_page,
        'format'    =>  '?wpaged=%#%',
        'show_all'     => false,
        'prev_next'    => false,
        'add_args'     => false,
    ));
    ?>
</div>
<script>
    jQuery(document).ready(function ($){
        $('.bctai_modal_close').click(function (){
            $('.bctai_modal_close').closest('.bctai_modal').hide();
            $('.bctai_modal_close').closest('.bctai_modal').removeClass('bctai-small-modal');
            $('.bctai-overlay').hide();
        })
        function bctaiLoading(btn){
            btn.attr('disabled','disabled');
            if(!btn.find('spinner').length){
                btn.append('<span class="spinner"></span>');
            }
            btn.find('.spinner').css('visibility','unset');
        }
        function bctaiRmLoading(btn){
            btn.removeAttr('disabled');
            btn.find('.spinner').remove();
        }
        var bctai_max_file_size = <?php echo esc_html($bctaiMaxFileSize)?>;
        var bctai_max_size_in_mb = '<?php echo size_format(esc_html($bctaiMaxFileSize))?>';
        var bctai_file_button = $('#bctai_file_button');
        var bctai_file_upload = $('#bctai_file_upload');
        var bctai_file_purpose = $('#bctai_file_purpose');
        var bctai_file_name = $('#bctai_file_name');
        var bctai_file_model = $('#bctai_file_model');
        var bctai_progress = $('.bctai_progress');
        var bctai_error_message = $('.bctai-error-msg');
        var bctai_create_fine_tune = $('.bctai_create_fine_tune');
        var bctai_retrieve_content = $('.bctai_retrieve_content');
        var bctai_delete_file = $('.bctai_delete_file');
        var bctai_ajax_url = '<?php echo admin_url('admin-ajax.php')?>';
        var bctaiAjaxRunning = false;
        $('.bctai_sync_files').click(function (){
            var btn = $(this);
            if(!bctaiAjaxRunning) {
                $.ajax({
                    url: bctai_ajax_url,
                    data: {action: 'bctai_fetch_finetune_files','nonce': '<?php echo wp_create_nonce('bctai-ajax-nonce')?>'},
                    dataType: 'JSON',
                    type: 'POST',
                    beforeSend: function () {
                        bctaiAjaxRunning = true;
                        bctaiLoading(btn);
                    },
                    success: function (res) {
                        //alert(res.msg)
                        bctaiAjaxRunning = false;
                        bctaiRmLoading(btn);
                        if (res.status === 'success') {
                            window.location.reload();
                        } else {
                            alert(res.msg);
                        }
                    },
                    error: function () {
                        bctaiAjaxRunning = false;
                        bctaiRmLoading(btn);
                        alert('<?php echo esc_html__('Something went wrong','bctai')?>');
                    }
                })
            }
        })
        bctai_delete_file.click(function (){   
            if(!bctaiAjaxRunning) {
                var conf = confirm('<?php echo esc_html__('Are you sure?','bctai')?>');
                if (conf) {
                    var btn = $(this);
                    var id = btn.attr('data-id');
                    $.ajax({
                        url: bctai_ajax_url,
                        data: {action: 'bctai_delete_finetune_file', id: id,'nonce': '<?php echo wp_create_nonce('bctai-ajax-nonce')?>'},
                        dataType: 'JSON',
                        type: 'POST',
                        beforeSend: function () {
                            bctaiAjaxRunning = true;
                            bctaiLoading(btn);
                        },
                        success: function (res) {
                            //alert(res.msg)
                            bctaiAjaxRunning = false;
                            bctaiRmLoading(btn);
                            if (res.status === 'success') {
                                window.location.reload();
                            } else {
                                alert(res.msg);
                            }
                        },
                        error: function () {
                            bctaiAjaxRunning = false;
                            bctaiRmLoading(btn);
                            alert('<?php echo esc_html__('Something went wrong','bctai')?>');
                        }
                    })
                }
                else {
                    bctaiAjaxRunning = false;
                }
            }
        });

        $(document).on('click','#bctai_create_finetune_btn', function (e){
            alert("create");
            if(!bctaiAjaxRunning) {
                var btn = $(e.currentTarget);
                var id = $('#bctai_create_finetune_id').val();
                var model = $('#bctai_create_finetune_model').val();
                $.ajax({
                    url: bctai_ajax_url,
                    data: {action: 'bctai_create_finetune', id: id, model: model,'nonce': '<?php echo wp_create_nonce('bctai-ajax-nonce')?>'},
                    dataType: 'JSON',
                    type: 'POST',
                    beforeSend: function () {
                        bctaiAjaxRunning = true;
                        bctaiLoading(btn);
                    },
                    success: function (res) {
                        //alert(res.msg);
                        bctaiRmLoading(btn);
                        bctaiAjaxRunning = false;
                        if (res.status === 'success') {
                            $('.bctai_modal_content').empty();
                            $('.bctai-overlay').hide();
                            $('.bctai_modal').hide();
                            alert('Congratulations! Your fine-tuning was created successfully. You can track its progress in the "Trainings" tab.');

                        } else {
                            alert(res.msg);
                            console.log(res);
                        }
                    },
                    error: function () {
                        bctaiAjaxRunning = false;
                        bctaiRmLoading(btn);
                        alert('Something went wrong');
                    }
                });
            }
        });



        bctai_create_fine_tune.click(function (){
            if(!bctaiAjaxRunning) {
                var btn = $(this);
                var id = btn.attr('data-id');
                $.ajax({
                    url: bctai_ajax_url,
                    data: {action: 'bctai_create_finetune_modal','nonce': '<?php echo wp_create_nonce('bctai-ajax-nonce')?>'},
                    dataType: 'JSON',
                    type: 'POST',
                    beforeSend: function () {
                        bctaiAjaxRunning = true;
                        bctaiLoading(btn);
                    },
                    success: function (res) {
                        bctaiAjaxRunning = false;
                        bctaiRmLoading(btn);
                        if (res.status === 'success') {
                            $('.bctai_modal_content').empty();
                            $('.bctai-overlay').show();
                            $('.bctai_modal').show();
                            $('.bctai_modal_title').html('<?php echo esc_html__('Choose Model','bctai')?>');
                            $('.bctai_modal').addClass('bctai-small-modal');
                            var html = '<input type="hidden" id="bctai_create_finetune_id" value="' + id + '"><p><label>Select Model</label>';
                            html += '<select style="width: 100%" id="bctai_create_finetune_model">';                            
                            html += '<option value=""><?php echo esc_html__('New Model','bctai')?></option>';
                            $.each(res.data, function (idx, item) {
                                html += '<option value="' + item + '">' + item + '</option>';
                            })
                            html += '</select>';
                            html += '</p>';
                            html += '<p><button style="width: 100%" class="button button-primary" id="bctai_create_finetune_btn"><?php echo esc_html__('Create', 'bctai'); ?></button></p>';
                            $('.bctai_modal_content').append(html)
                        } else {
                            alert(res.msg);
                        }
                    },
                    error: function () {
                        bctaiAjaxRunning = false;
                        bctaiRmLoading(btn);
                        alert('<?php echo esc_html__('Something went wrong','bctai')?>');
                    }
                })
            }
        });
        bctai_retrieve_content.click(function (){
            if(!bctaiAjaxRunning) {
                var btn = $(this);
                var id = btn.attr('data-id');
                $.ajax({
                    url: bctai_ajax_url,
                    data: {action: 'bctai_get_finetune_file', id: id,'nonce': '<?php echo wp_create_nonce('bctai-ajax-nonce')?>'},
                    dataType: 'JSON',
                    type: 'POST',
                    beforeSend: function () {
                        bctaiAjaxRunning = true;
                        bctaiLoading(btn);
                    },
                    success: function (res) {
                        //alert(res.msg)
                        bctaiAjaxRunning = false;
                        bctaiRmLoading(btn);
                        if (res.status === 'success') {
                            $('.bctai_modal_title').html('<?php echo esc_html('File Content','bctai') ?>');
                            $('.bctai_modal_content').html('<pre>' + res.data + '</pre>');
                            $('.bctai-overlay').show();
                            $('.bctai_modal').show();
                        } else {
                            alert(res.msg);
                        }
                    },
                    error: function () {
                        bctaiAjaxRunning = false;
                        bctaiRmLoading(btn);
                        alert('<?php echo esc_html__('Something went wrong','bctai')?>');
                    }
                })
            }
        });


    })
</script>

