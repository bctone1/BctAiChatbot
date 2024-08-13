<?php
if ( ! defined( 'ABSPATH' ) ) exit;
global $wpdb;
$bctai_files_page = isset($_GET['wpage']) && !empty($_GET['wpage']) ? sanitize_text_field($_GET['wpage']) : 1;
$bctai_files_per_page = 20;
$bctai_files_offset = ( $bctai_files_page * $bctai_files_per_page ) - $bctai_files_per_page;
$bctai_files_count_sql = "SELECT COUNT(*) FROM ".$wpdb->posts." f WHERE f.post_type='bctai_convert' AND f.post_status='publish'";
$bctai_files_sql = $wpdb->prepare("SELECT f.* FROM ".$wpdb->posts." f WHERE f.post_type='bctai_convert' AND f.post_status='publish' ORDER BY f.post_date DESC LIMIT %d,%d", $bctai_files_offset,$bctai_files_per_page);
$bctai_files = $wpdb->get_results($bctai_files_sql);
$bctai_files_total = $wpdb->get_var( $bctai_files_count_sql );
?>
<style>
    .check label:before {
        top:3px;
    }
</style>

<h2 class="sectionTitle">Data Converter</h2>


<form id="bctai_data_converter" method="post" action="">
    <?php wp_nonce_field('bctai_data_converter_count','nonce');?>
    <input type="hidden" name="action" value="bctai_data_converter_count">



    <div class="checkArea" style="margin-top:0px;">
        <div class="check">
            <input type="checkbox" class="bctai_converter_data" name="data[]" value="post" id="post">
            <label for="post"><?php echo esc_html__('Posts','bctai')?></label>
        </div>
        <div class="check">
            <input type="checkbox" class="bctai_converter_data" name="data[]" value="page" id="page">
            <label for="page"><?php echo esc_html__('Pages','bctai')?></label>
        </div>

        <?php if(in_array('product',get_post_types()) && class_exists( 'woocommerce' )): ?>

        <div class="check">
            <input type="checkbox" class="bctai_converter_data" name="data[]" value="product" id="product">
            <label for="product"><?php echo esc_html__('Products','bctai')?></label>
        </div>
        
        <?php endif; ?>
        <button class="bctai_converter_button btn btnXS bgPrimary"><span>Convert</span></button>
    </div>

    <div class="bctai-convert-progress bctai-convert-bar">
        <span></span>
        <small>0%</small>
    </div>

</form>


<div class="section">
    <div class="sectionContent">
        <div>
            <div class="tableArea">
<table>
<colgroup>
                    <col style="width:auto">
                    <col style="width:auto">
                    <col style="width:auto">
                    <col style="width:70px">
                    <col style="width:15%">
                    </colgroup>
    <thead>
    <tr>
        <th><?php echo esc_html__('Filename','bctai')?></th>
        <th><?php echo esc_html__('Start','bctai')?></th>
        <th><?php echo esc_html__('Completed','bctai')?></th>
        <th><?php echo esc_html__('Size','bctai')?></th>
        <th><?php echo esc_html__('Action','bctai')?></th>
    </tr>
    </thead>
    <tbody>
    <?php
    if($bctai_files && is_array($bctai_files) && count($bctai_files)):
    foreach($bctai_files as $bctai_file):
        $file = wp_upload_dir()['basedir'].'/'.$bctai_file->post_title;
        if(file_exists($file)):

    ?>
    <tr> 
        <td><?php echo esc_html($bctai_file->post_title);?></td>
        <td><?php echo esc_html(gmdate('Y.m.d H:i',strtotime($bctai_file->post_date)));?></td>
        <td><?php echo esc_html(gmdate('Y.m.d H:i',strtotime($bctai_file->post_modified)));?></td>
        <td><?php echo esc_html(size_format(filesize($file)));?></td>
        <td>
            <a class="btn btnXS bgPrimary"href="<?php echo wp_upload_dir()['baseurl'].'/'.esc_html($bctai_file->post_title)?>" download><?php echo esc_html__('Download','bctai')?></a>
            <button class="btn btnXS bgLightGrayBorder bctai_convert_upload" data-lines="<?php echo esc_html(count(file($file)))?>" data-file="<?php echo esc_html($bctai_file->post_title)?>"><?php echo esc_html__('Upload','bctai')?></button>
        </td>
    </tr>
    <?php
        endif;
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
        'base'         => admin_url('admin.php?page=AI+Training&action=data&wpage=%#%'),
        'total'        => ceil($bctai_files_total / $bctai_files_per_page),
        'current'      => $bctai_files_page,
        'format'       => '?wpaged=%#%',
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
        });
        var form = $('#bctai_data_converter');
        var btn = $('.bctai_converter_button');
        var progressBar = $('.bctai-convert-bar');
        var bctai_ajax_url = '<?php echo admin_url('admin-ajax.php')?>';
        var bctai_convert_upload = $('.bctai_convert_upload');
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
        function bctaiConverter(data){
            $.ajax({
                url: bctai_ajax_url,
                data: data,
                type: 'POST',
                dataType: 'JSON',
                success: function (res){
                    //alert(res.msg);
                    if(res.status === 'success'){
                        if(res.next_page === 'DONE'){
                            bctaiRmLoading(btn);
                            progressBar.find('small').html('100%');
                            progressBar.find('span').css('width','100%');
                            setTimeout(function (){
                                window.location.reload();
                            },1000);
                        }
                        else{
                            var percent = Math.ceil(data.page*100/data.total);
                            progressBar.find('small').html(percent+'%');
                            progressBar.find('span').css('width',percent+'%');
                            data.page = res.next_page;
                            //alert(data.page);
                            data.file = res.file;
                            data.id = res.id;
                            bctaiConverter(data);
                        }
                    }
                    else{
                        progressBar.addClass('bctai_error');
                        bctaiRmLoading(btn);
                        alert(res.msg);
                    }
                },
                error: function (){
                    progressBar.addClass('bctai_error');
                    bctaiRmLoading(btn);
                    alert('<?php echo esc_html__('Something went wrong','bctai')?>');
                }
            })
        }

        //convert 클릭시
        form.on('submit', function (){
            alert('convert');
            if(!$('.bctai_converter_data:checked').length){
                alert('<?php echo esc_html__('Please select least one data to convert','bctai')?>');
            }
            else{
                var data = form.serialize();
                $.ajax({
                    url: bctai_ajax_url,
                    data: data,
                    dataType: 'JSON',
                    type: 'POST',
                    beforeSend: function (){
                        progressBar.show();
                        progressBar.removeClass('bctai_error')
                        progressBar.find('span').css('width',0);
                        progressBar.find('small').html('0%');
                        bctaiLoading(btn)
                    },
                    success: function (res){
                        //alert(res.msg);
                        if(res.status === 'success'){
                            //alert(res.count);
                            if(res.count > 0){
                                bctaiConverter({
                                    action: 'bctai_data_converter',
                                    types: res.types, 
                                    total: res.count, 
                                    page: 1,
                                    per_page: 100,
                                    'nonce': '<?php echo wp_create_nonce('bctai-ajax-nonce')?>'
                                });
                            }
                            else{
                                progressBar.addClass('bctai_error');
                                bctaiRmLoading(btn);
                                alert('<?php echo esc_html__('Nothing to convert','bctai')?>');
                            }
                        }
                        else{
                            progressBar.addClass('bctai_error');
                            bctaiRmLoading(btn);
                            alert(res.msg);
                        }
                    },
                    error: function (){
                        progressBar.addClass('bctai_error');
                        bctaiRmLoading(btn);
                        alert('<?php echo esc_html__('Something went wrong','bctai')?>');
                    }
                })

            }
            return false;
        });
        bctai_convert_upload.click(function (){
            var btn = $(this);
            var file = btn.attr('data-file');
            var lines = btn.attr('data-lines');
            $('.bctai-overlay').show();
            $('.bctai_modal').show();
            $('.bctai_modal_title').html('File Setting');
            $('.bctai_modal').addClass('bctai-small-modal');
            $('.bctai_modal_content').empty();
            var html = '<form id="bctai_upload_convert" action="" method="post"><?php wp_nonce_field('bctai-ajax-nonce','nonce');?><input type="hidden" name="action" value="bctai_upload_convert"><input type="hidden" id="bctai_upload_convert_index" name="index" value="1"><input id="bctai_upload_convert_line" type="hidden" name="line" value="0"><input id="bctai_upload_convert_lines" type="hidden" value="'+lines+'"><input type="hidden" name="file" value="'+file+'"><p><label><?php echo esc_html__('Purpose','bctai')?></label><select style="width: 100%" name="purpose"><option value="fine-tune"><?php echo esc_html__('Fine-Tune','bctai')?></option></select></p>';
            //html += '<p><label><?php echo esc_html__('Model Base','bctai')?></label><select style="width: 100%" name="model"><option value="ada">ada</option><option value="babbage">babbage</option><option value="curie">curie</option><option value="davinci">davinci</option></select></p>';
            html += '<p><label><?php echo esc_html__('Model Base','bctai')?></label><select style="width: 100%" name="model"><option value="gpt-3.5-turbo">gpt-3.5-turbo</option><option value="babbage-002">babbage-002</option><option value="davinci-002">davinci-002</option></select></p>';
            html += '<p><label><?php echo esc_html__('Custom Name','bctai')?></label><input style="width: 100%" type="text" name="custom"></p>';
            html += '<div class="bctai-convert-progress bctai-upload-bar"><span></span><small>0%</small></div>';
            html += '<div class="bctai-upload-message"></div><p><button style="width: 100%" class="button button-primary" id="bctai_create_finetune_btn"><?php echo esc_html__('Upload','bctai')?></button></p>';
            $('.bctai_modal_content').append(html);
        });
        function bctaiFileUpload(data, btn){
            var bctai_upload_convert_index = parseInt($('#bctai_upload_convert_index').val());
            var total_lines = parseInt($('#bctai_upload_convert_lines').val());
            var bctai_upload_bar = $('.bctai-upload-bar');
            $.ajax({
                url: bctai_ajax_url,
                data: data,
                type: 'POST',
                dataType: 'JSON',
                success: function (res){
                    //alert(res.msg);
                    if(res.status === 'success'){
                        if(res.next === 'DONE'){
                            $('.bctai-upload-message').html('<?php echo esc_html__('Upload successfully','bctai')?>');
                            bctaiRmLoading(btn);
                            bctai_upload_bar.find('small').html('100%');
                            bctai_upload_bar.find('span').css('width','100%');
                        }
                        else{
                            var percent = Math.ceil(res.next*100/total_lines);
                            bctai_upload_bar.find('small').html(percent+'%');
                            bctai_upload_bar.find('span').css('width',percent+'%');
                            $('#bctai_upload_convert_line').val(res.next);
                            $('#bctai_upload_convert_index').val(bctai_upload_convert_index+1);
                            var data = $('#bctai_upload_convert').serialize();
                            bctaiFileUpload(data,btn);
                        }
                    }
                    else{
                        bctai_upload_bar.addClass('bctai_error');
                        bctaiRmLoading(btn);
                        alert(res.msg);
                    }
                },
                error: function (){
                    bctai_upload_bar.addClass('bctai_error');
                    bctaiRmLoading(btn);
                    alert('<?php echo esc_html__('Something went wrong','bctai')?>');
                }
            })
        }
        $(document).on('submit','#bctai_upload_convert', function (e){
            $('#bctai_upload_convert_index').val(1);
            $('#bctai_upload_convert_line').val(0);
            $('.bctai-upload-message').empty();
            var form = $(e.currentTarget);
            var data = form.serialize();
            var btn = form.find('button');
            bctaiLoading(btn);
            var bctai_upload_bar = $('.bctai-upload-bar');
            bctai_upload_bar.show();
            bctai_upload_bar.removeClass('bctai_error');
            bctai_upload_bar.find('span').css('width',0);
            bctai_upload_bar.find('small').html('0%');
            bctaiFileUpload(data,btn);
            return false;
        })
    })
</script>