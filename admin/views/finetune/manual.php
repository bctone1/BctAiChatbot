<?php
if (!defined('ABSPATH')) {
    exit;
}
?>


<h2 class="sectionTitle">Data Entry</h2>
<form id="bctai_form_data" action="" method="post">
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row"><div class="formTitle"><strong><?php echo __('Purpose','bctai')?></strong></div></th>
                <td>
                    <select name="purpose">
                        <option value="fine-tune">Fine-tunes</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><div class="formTitle"><strong><?php echo __('Model Base', 'bctai') ?></strong></div></th>
                <td>
                    <select name="model">
                        <option value="gpt-3.5-turbo">gpt-3.5-turbo</option>
                        <option value="babbage-002">babbage-002</option>
                        <option value="davinci-002">davinci-002</option>
                    </select>
                </td>
            </tr>
        </tbody>
    </table>
    

    <div class="bctai_list_data gpt-turbo contentBox">
        <div class="bctai_data_item">
            <div class="formTitle"><h4 class="columnTitle">System</h4></div>
            <div class="formTitle"><h4 class="columnTitle">User</h4></div>
            <div class="formTitle"><h4 class="columnTitle">Assistant</h4></div>
        </div>

        <div class="bctai_data_list">
            <div class="bctai_data_item bctai_data">
                <div>
                    <input type="text" name="data[0][system]" class=" bctai_data_system" placeholder="<?php echo esc_html__('Your name is BCT, and you are an AI that provides accurate answers to questions.', 'bctai') ?>">
                </div>
                <div>
                    <input type="text" name="data[0][user]" class=" bctai_data_user" placeholder="<?php echo esc_html__('What is the capital of South Korea?', 'bctai') ?>">
                </div>
                <div>
                    <input type="text" name="data[0][assistant]" class=" bctai_data_assistant" placeholder="<?php echo esc_html__('The capital of South Korea is Seoul!', 'bctai') ?>">
                    
                    <span class="button-link-delete"></span>
                </div>
            </div>
        </div>
        <div class="btnArea flexCenter" style="margin-top:20px;">
            <button type="button" class="btn btnS bgDarkGray bctai_add_data"><span>Add Contents</span></button>
        </div>
        
    </div>

    <div class="bctai_list_data normal contentBox" style="display:none;">
        <div class="bctai_data_item">
            <div class="formTitle"><h4 class="columnTitle">Prompt</h4></div>
            <div class="formTitle"><h4 class="columnTitle">Completion</h4></div>
        </div>
        <div class="bctai_data_list">
            <div class="bctai_data_item bctai_data">
                <div>
                    <input type="text" name="data[0][prompt]" class=" bctai_data_prompt" placeholder="<?php echo esc_html__('Prompt', 'bctai') ?>">
                </div>
                <div>
                    <input type="text" name="data[0][completion]" class=" bctai_data_completion" placeholder="<?php echo esc_html__('Completion', 'bctai') ?>">
                    <span class="button-link-delete"></span>
                </div>
            </div>
        </div>
        <div class="btnArea flexCenter" style="margin-top:20px;">
            <button type="button" class="btn btnS bgDarkGray bctai_add_data"><span>Add Contents</span></button>
        </div>
        
    </div>
    
    
    <div class="bctai-convert-progress bctai-convert-bar">
        <span></span>
        <small>0%</small>
    </div>

    <div class="bctai-upload-message"></div>

    <div class="btnArea"><button class="btn btnL bgPrimary"><span>Upload</span></button></div>
</form>


        

<form id="bctai_upload_convert" style="display: none" action="" method="post">
    <?php
    wp_nonce_field('bctai-ajax-nonce', 'nonce');
    ?>
    <input type="hidden" name="action" value="bctai_upload_convert">
    <input type="hidden" id="bctai_upload_convert_index" name="index" value="1">
    <input type="hidden" id="bctai_upload_convert_line" name="line" value="0">
    <input type="hidden" id="bctai_upload_convert_lines" value="0">
    <input type="hidden" name="file" value="">
    <input type="hidden" name="purpose" value="fine-tune">
    <input type="hidden" name="model" value="">
    <input type="hidden" name="custom" value="">
</form>


<script>
    jQuery(document).ready(function ($){

        function bctaiSortData() {
            if($('select[name="model"]').val() === 'gpt-3.5-turbo'){
                $(item).find('.bctai_data_system').attr('name','data['+idx+'][system]');
                $(item).find('.bctai_data_user').attr('name','data['+idx+'][user]');
                $(item).find('.bctai_data_assistant').attr('name','data['+idx+'][assistant]');
            } else {
                $('.bctai_list_data.normal .bctai_data').each(function (idx, item){
                    $(item).find('.bctai_data_prompt').attr('name','data['+idx+'][prompt]');
                    $(item).find('.bctai_data_completion').attr('name','data['+idx+'][completion]');
                })
            }
        }
        function bctaiLoading(btn) {
            btn.attr('disabled','disabled');
            if(!btn.find('spinner').length){
                btn.append('<span class="spinner"></span>');
            }
            btn.find('.spinner').css('visibility','unset');
        }
        function bctaiRmLoading(btn) {
            btn.removeAttr('disabled');
            btn.find('.spinner').remove();
        }
        var progressBar = $('.bctai-convert-bar');
        var bctai_add_data = $('.normal .bctai_add_data');
        var bctai_add_gpt_turbo_data = $('.gpt-turbo .bctai_add_data');
        var bctai_ajax_url = '<?php echo admin_url('admin-ajax.php')?>';
        var form = $('#bctai_form_data');
        var bctai_item = '<div class="bctai_data_item bctai_data"><div><input type="text" name="data[0][prompt]" class=" bctai_data_prompt" placeholder="Prompt"> </div><div><input type="text" name="data[0][completion]" class=" bctai_data_completion" placeholder="Completion"><button type="button" class="btnDel button-link-delete"><span class="blind">아이템 삭제</span></button></div></div>';
        var bctai_gpt_turbo_item = '<div class="bctai_data_item bctai_data"><div><input type="text" name="data[0][system]" class=" bctai_data_system" placeholder="<?php echo esc_html__('Content', 'bctai') ?>"> </div><div><input type="text" name="data[0][user]" class=" bctai_data_user" placeholder="<?php echo esc_html__('Content', 'bctai') ?>"> </div><div><input type="text" name="data[0][assistant]" class=" bctai_data_assistant" placeholder="<?php echo esc_html__('Content', 'bctai') ?>"><button type="button" class="btnDel button-link-delete"><span class="blind">아이템 삭제</span></button></div></div>';
        bctai_add_data.click(function (){
            //alert('normal');
            $('.bctai_list_data.normal').find('.bctai_data_list').append(bctai_item);
            if($('.bctai_list_data.normal').find('.bctai_data_list').find('.bctai_data').length > 1){
                $('.bctai_list_data.normal').find('span.button-link-delete').show();
            } else {
                $('.bctai_list_data.normal').find('span.button-link-delete').hide();
            }
            bctaiSortData();
        })
        bctai_add_gpt_turbo_data.click(function (){
            //alert('gpt-turbo');
            $('.bctai_list_data.gpt-turbo').find('.bctai_data_list').append(bctai_gpt_turbo_item);
            if($('.bctai_list_data.gpt-turbo').find('.bctai_data_list').find('.bctai_data').length > 1){
                $('.bctai_list_data.gpt-turbo').find('span.button-link-delete').show();
            } else {
                $('.bctai_list_data.gpt-turbo').find('span.button-link-delete').hide();
            }
            bctaiSortData();
        });
        $(document).on('click','.bctai_data button', function (e){
            // alert('delete');
            if($(this).closest('.bctai_data_list').find('.bctai_data').length < 3){
                $(this).closest('.bctai_data_list').find('span.button-link-delete').hide();
            } else {
                $(this).closest('.bctai_data_list').find('span.button-link-delete').show();
            }
            $(e.currentTarget).parent().parent().remove();
            bctaiSortData();
        });

        function bctaiFileUpload(data, btn) {
            /** 
             * data = 
             *  action = bctai_upload_convert
             *  index = 1
             *  line = 0
             *  file = filename.jsonl
             *  purpose = file-tune
             *  model = ada
             *  custom = file name
            */
            //alert(data)
            var bctai_upload_convert_index = parseInt($('#bctai_upload_convert_index').val());
            $.ajax({
                url: bctai_ajax_url,
                data: data,
                type: 'POST',
                dataType: 'JSON',
                success: function (res){
                    //alert(res.msg)
                    //console.log(res.msg)
                    if(res.status === 'success') {
                        if(res.next === 'DONE') {
                            if($('select[name=model]').val() === 'gpt-3.5-turbo'){
                                $('.bctai_list_data.gpt-turbo .bctai_data_list').html(bctai_gpt_turbo_item);
                            } else {
                                $('.bctai_list_data.normal .bctai_data_list').html(bctai_item);
                            }                            
                            $('.bctai-upload-message').html('Upload successfully. Now, head over to the Datasets tab and create your fine-tuning using this data.');
                            progressBar.find('small').html('100%');
                            progressBar.find('span').css('width','100%');
                            bctaiRmLoading(btn);
                            setTimeout(function (){
                                $('#bctai_upload_convert_line').val('0');
                                $('#bctai_upload_convert_index').val('1');
                                progressBar.hide();
                                progressBar.removeClass('bctai_error')
                                progressBar.find('span').css('width',0);
                                progressBar.find('small').html('0%');
                            },2000);
                        }
                        else {
                            $('#bctai_upload_convert_line').val(res.next);
                            $('#bctai_upload_convert_index').val(bctai_upload_convert_index+1);
                            var data = $('#bctai_upload_convert').serialize();
                            bctaiFileUpload(data, btn);
                        }
                    }
                    else {
                        progressBar.addClass('bctai_error');
                        bctaiRmLoading(btn);
                        alert(res.msg);
                    }
                },
                error: function (){
                    progressBar.addClass('bctai_error');
                    bctaiRmLoading(btn);
                    alert('Something went wrong');
                }
            })
        }

        function bctaiProcessData(lists,start,file,btn) {
            var purpose = $('select[name=purpose]').val();
            var model = $('select[name=model]').val();
            var name = $('input[name=custom]').val();

            var data = {
                action: 'bctai_data_insert',
                model: model,                
                file: file,
                nonce: '<?php echo wp_create_nonce('bctai-ajax-nonce') ?>'
            };

            if(model === 'gpt-3.5-turbo'){
                data['messages'] = lists[start].messages;
            } else {
                data['prompt'] = lists[start].prompt;
                data['completion'] = lists[start].completion;
            }

            $.ajax({
                url: bctai_ajax_url,                
                data: data,
                dataType: 'JSON',
                type: 'POST',
                success: function (res){
                    //alert(res.msg)
                    if(res.status === 'success') {
                        var percent = Math.ceil((start+1)*90/lists.length);
                        progressBar.find('small').html(percent+'%');
                        progressBar.find('span').css('width',percent+'%');
                        if((start+1) === lists.length) {
                            /*Save file done*/
                            $('#bctai_upload_convert input[name=model]').val(model);
                            $('#bctai_upload_convert input[name=purpose]').val(purpose);
                            $('#bctai_upload_convert input[name=custom]').val(name);
                            $('#bctai_upload_convert input[name=file]').val(res.file);
                            var data = $('#bctai_upload_convert').serialize();
                            bctaiFileUpload(data, btn);
                        }
                        else {
                            file = res.file;
                            bctaiProcessData(lists,(start+1),file, btn);
                        }
                    }
                    else {
                        progressBar.addClass('bctai_error');
                        bctaigRmLoading(btn);
                        alert(res.msg);
                    }
                },
                error: function (){
                    progressBar.addClass('bctai_error');
                    bctaiRmLoading(btn);
                    alert('Something went wrong');
                }
            })
        }
        form.on('submit', function (){
            var total = 0;
            var lists = [];
            var btn = form.find('.bctai_submit');
            if($('select[name="model"]').val() == 'gpt-3.5-turbo'){
                $('.bctai_list_data.gpt-turbo .bctai_data').each(function (idx, item){
                    var item_system = $(item).find('.bctai_data_system').val();
                    var item_user = $(item).find('.bctai_data_user').val();
                    var item_assistant = $(item).find('.bctai_data_assistant').val();
                    if(item_system !== '' && item_user !== '' && item_assistant !== ''){
                        total += 1;
                        lists.push({"messages":[{role: "system", content: item_system }, {role: "user", content: item_user }, {role: "assistant", content: item_assistant }]})
                    }
                });

            } else {
                $('.bctai_list_data.normal .bctai_data').each(function (idx, item){
                    var item_prompt = $(item).find('.bctai_data_prompt').val();
                    var item_completion = $(item).find('.bctai_data_completion').val();
                    if(item_prompt !== '' && item_completion !== '') {
                        total += 1;
                        lists.push({prompt: item_prompt,completion: item_completion})
                    }
                });
            }
            
            if(total >= 1){
                $('#bctai_upload_convert_line').val('0');
                $('#bctai_upload_convert_index').val('1');
                $('.bctai-upload-message').empty();
                progressBar.show();
                progressBar.removeClass('bctai_error');
                progressBar.find('span').css('width',0);
                progressBar.find('small').html('0%');
                bctaiLoading(btn);
                bctaiProcessData(lists,0,'',btn);
            }
            else {
                alert('Please insert least one row');
            }
            return false;
        })

        $(document).on('change','select[name="model"]', function (e){
            if($(this).val() === 'gpt-3.5-turbo'){
                $('.bctai_list_data.normal').hide();
                $('.bctai_list_data.gpt-turbo').show();
            } else {
                $('.bctai_list_data.gpt-turbo').hide();
                $('.bctai_list_data.normal').show();
            }
        });

    })
</script>

