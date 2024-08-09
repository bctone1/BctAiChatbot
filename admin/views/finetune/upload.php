<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$fileTypes = array(
    'fine-tune' =>  'Fine-Tune',

);
$bctaiMaxFileSize = wp_max_upload_size();
if($bctaiMaxFileSize > 104857600){
    $bctaiMaxFileSize = 104857600;
}
?>

<div class="section_Upload">
    <div class="sectionHeader">
        <h2 class="sectionTitle">Upload</h2>
    </div>


    <div class="sectionContent">
        <div class="columnWrap">
            <div class="column1">
                <div class="contentBox">

                    <div class="columnWrap">
                        <div class="columnWrap column2">
                            <div class="formTitle"><strong>File Upload</strong></div>
                            <div class="formContent">
                                <div class="fileArea">
                                    <input type="file" id="bctai_file_upload" title="파일첨부">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="columnWrap column2">
                        <div class="formTitle"><strong>Purpose</strong></div>
                        <div class="formContent">
                            <select id="bctai_file_purpose">
                                <?php foreach($fileTypes as $key=>$fileType) {
                                    echo '<option value="'.esc_html($key).'">'.esc_html($fileType).'</option>';
                                } ?>
                            </select>
                        </div>
                    </div>
                    <div class="columnWrap column2">
                        <div class="formTitle"><strong>Model Base</strong></div>
                        <div class="formContent">
                            <select id ="bctai_file_model">
                                <option value="gpt-3.5-turbo">gpt-3.5-turbo</option>
                                <option value="babbage-002">babbage-002</option>                  
                                <option value="davinci-002">davinci-002</option>
                            </select>
                            <div class="discription"><?php echo size_format($bctaiMaxFileSize)?>. (It is supposed to be at least 100mb if you want to upload larger datasets)</div>
                            <div class="bctai_upload_success" style="display: none;margin-bottom: 5px;color: green;"><?php echo __('File uploaded successfully you can view it in Datasets tab.', 'bctai') ?></div>
                            <div class="bctai_progress" style="display: none"><span></span><small>Uploading</small></div>
                            <div class="bctai-error-msg"></div>
                            <div class="btnArea">
                                <button id="bctai_file_button" class="btn btnL bgPrimary"><?php echo __('Upload', 'bctai') ?></button>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>

    
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
        var bctai_upload_success = $('.bctai_upload_success');
        bctai_file_button.click(function (){
            if(bctai_file_upload[0].files.length === 0){
                alert('<?php echo esc_html__('Please select file','bctai')?>');
            }
            else{
                var bctai_file = bctai_file_upload[0].files[0];
                var bctai_file_extension = bctai_file.name.substr( (bctai_file.name.lastIndexOf('.') +1) );
                if(bctai_file_extension !== 'jsonl'){
                    bctai_file_upload.val('');
                    alert('<?php echo esc_html__('Only accept JSONL file type','bctai')?>');
                }
                else if(bctai_file.size > bctai_max_file_size){
                    bctai_file_upload.val('');
                    alert('<?php echo esc_html__('Dataset allow maximum','bctai')?> '+bctai_max_size_in_mb)
                }
                else{
                    var formData = new FormData();
                    formData.append('action', 'bctai_finetune_upload');
                    formData.append('file', bctai_file);
                    formData.append('purpose', bctai_file_purpose.val());
                    formData.append('model', bctai_file_model.val());
                    formData.append('name', bctai_file_name.val());
                    formData.append('nonce','<?php echo wp_create_nonce('bctai-ajax-nonce')?>');
                    $.ajax({
                        url: bctai_ajax_url,
                        type: 'POST',
                        dataType: 'JSON',
                        data: formData,
                        beforeSend: function (){
                            bctai_progress.find('span').css('width','0');
                            bctai_progress.show();
                            bctaiLoading(bctai_file_button);
                            bctai_error_message.hide();
                            bctai_upload_success.hide();
                        },
                        xhr: function() {
                            var xhr = $.ajaxSettings.xhr();
                            xhr.upload.addEventListener("progress", function(evt) {
                                if (evt.lengthComputable) {
                                    var percentComplete = evt.loaded / evt.total;
                                    bctai_progress.find('span').css('width',(Math.round(percentComplete * 100))+'%');
                                }
                            }, false);
                            return xhr;
                        },
                        success: function(res) {
                            if(res.status === 'success'){
                                bctaiRmLoading(bctai_file_button);
                                bctai_progress.hide();
                                bctai_file_upload.val('');
                                bctai_upload_success.show();
                            }
                            else{
                                bctaiRmLoading(bctai_file_button);
                                bctai_progress.find('small').html('Error');
                                bctai_progress.addClass('bctai_error');
                                bctai_error_message.html(res.msg);
                                bctai_error_message.show();
                            }
                        },
                        cache: false,
                        contentType: false,
                        processData: false,
                        error: function (){
                            bctai_file_upload.val('');
                            bctaiRmLoading(bctai_file_button);
                            bctai_progress.addClass('bctai_error');
                            bctai_progress.find('small').html('Error');
                            bctai_error_message.html('Something went wrong1');
                            bctai_error_message.show();
                        }
                    });
                }
            }
        })
    })
</script>