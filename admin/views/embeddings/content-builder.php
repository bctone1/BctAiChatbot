<?php
if (!defined('ABSPATH'))
    exit;

?>


<h2 class="sectionTitle">Contents Builder</h2>


<div class="bctai-faq-item-default" style="display: none">
    
    <div class="bctai-faq-item">
        <p style="font: normal normal 900 24px/35px Noto Sans KR; margin:0px;"><?php echo __('Embedding Content','bctai') ?></p>
        <span class="bctai-faq-close"><i class="fa-solid fa-x" style="font-weight: 900;"></i></span>
        
        
        <p>
            <label><strong style="font-size: 14px;font-weight: normal;"><?php echo esc_html__('Question', 'bctai') ?></strong></label>
            <textarea class="bctai-faq-Question" style="width: 1006px;height: 52px;background: #F1F1F1 0% 0% no-repeat padding-box;border-radius: 16px;opacity: 1; border:0px;"></textarea>
        </p>

        <p>
            <label><strong style="font-size: 14px;font-weight: normal;"><?php echo esc_html__('Answer', 'bctai') ?></strong></label>
            <textarea class="bctai-faq-Answer"style="width: 1006px;height: 191px;background: #F1F1F1 0% 0% no-repeat padding-box;border-radius: 16px;opacity: 1; border:0px;"></textarea>
        </p>

    </div>
</div>


<div class="bctai-knowledge-item-default" style="display: none">
    
    <div class="bctai-knowledge-item">
        <p style="font: normal normal 900 24px/35px Noto Sans KR; margin:0px;"><?php echo __('Embedding Content','bctai') ?></p>

        <span class="bctai-knowledge-close"><i class="fa-solid fa-x" style="font-weight: 900;"></i></span>
        <p>
            <label><strong style="font-size: 14px;font-weight: normal;"><?php echo esc_html__('Topic', 'bctai') ?></strong></label>
            <textarea class="bctai-knowledge-Topic" style="width: 1006px;height: 52px;background: #F1F1F1 0% 0% no-repeat padding-box;border-radius: 16px;opacity: 1; border:0px;"></textarea>
        </p>
        <p>
            <label><strong style="font-size: 14px;font-weight: normal;"><?php echo esc_html__('Description', 'bctai') ?></strong></label>
            <textarea class="bctai-knowledge-Description" style="width: 1006px;height: 191px;background: #F1F1F1 0% 0% no-repeat padding-box;border-radius: 16px;opacity: 1; border:0px;"></textarea>
        </p>
    </div>
</div>





<form action="" method="post" id="bctai_embeddings_form">
    <?php wp_nonce_field('bctai_embeddings_save');?>
    <input type="hidden" name="action" value="bctai_embeddings">
    <div class="bctai-embeddings-success"style="padding: 10px;background: #fff;border-left: 2px solid #11ad6b;display: none"><?php echo esc_html__('Record saved successfully', 'bctai') ?></div>


    <div class="columnWrap">
        <div class="columnWrap column2">
            <div class="formTitle"><strong>Content Type</strong></div>
            <div class="formContent">
                <select name="type"class="bctai-select-entry-type">
                    <option value="free"><?php echo esc_html__('Free Text', 'bctai') ?></option>
                    <option value="faq"><?php echo esc_html__('FAQ', 'bctai') ?></option>
                    <option value="knowledge"><?php echo esc_html__('KnowledgeBase', 'bctai') ?></option>
                </select>
            </div>
        </div>
    </div>

    <div class="bctai-data-entry bctai-free columnWrap" style="display: flex;">
        <div class="columnWrap column2">
            <div class="formTitle"><strong>Content</strong></div>
            <div class="formContent">
                <textarea name="content" class="bctai-embeddings-content" rows="15"></textarea>
            </div>
        </div>
    </div>

    








    <div class="bctai-data-entry bctai-faq" style="display: none;width: 1000px;margin-left: 210px;">
        <div class="bctai-faq-list"></div>
        <button type="button" class="button button-primary btn-add-faq"style="width: 1066px;background: #9f89af;border-color: #9f89af; ">Add More</button>
    </div>

    <div class="bctai-data-entry bctai-knowledge" style="display: none; width: 1000px;margin-left: 210px;">
        <div class="bctai-knowledge-list"></div>
        <button type="button" class="button button-primary btn-add-knowledge"style="width: 1066px; background: #9f89af;border-color: #9f89af; ">Add More</button>
    </div>

    


    <button class="btn btnL bgPrimary" type="submit" value ="<?php echo __('SAVE','bctai') ?>" name="bctai_submit" style="margin:20px 0px 0px 245px;">SAVE</button>

</form>




<script>
    jQuery(document).ready(function ($) {
        document.addEventListener('input', function (e) {
            if (e.target.tagName.toLowerCase() === 'textarea') {
                e.target.style.height = 'auto';
                e.target.style.height = (e.target.scrollHeight) + 'px';
            }
        });

        var bctaiFaqList = $('.bctai-faq-list');
        var bctaiKnowledgeList = $('.bctai-knowledge-list');
        var bctaiNomuList = $('.bctai-nomu-list');


        function bctaiLoading(btn) {
            btn.attr('disabled', 'disabled');
            if (!btn.find('spinner').length) {
                btn.append('<span class="spinner"></span>');
            }
            btn.find('.spinner').css('visibility', 'unset');
        }
        function bctaiRmLoading(btn) {
            btn.removeAttr('disabled');
            btn.find('.spinner').remove();
        }
        $('.bctai-select-entry-type').on('change', function () {
            var type = $(this).val();
            $('.bctai-data-entry').hide();
            if (type !== 'free') {
                $('.bctai-' + type + '-list').empty();
                $('.bctai-' + type + '-list').append($('.bctai-' + type + '-item-default').html());
            }
            $('.bctai-' + $(this).val()).show();
        })
        $('.btn-add-faq').click(function () {
            bctaiFaqList.append($('.bctai-faq-item-default').html());
        });
        $('.btn-add-knowledge').click(function () {
            bctaiKnowledgeList.append($('.bctai-knowledge-item-default').html());
        });
        $('.btn-add-nomu').click(function () {
            bctaiNomuList.append($('.bctai-nomu-item-default').html());
        });


        $(document).on('click', '.bctai-knowledge-close,.bctai-faq-close,.bctai-nomu-close', function (e) {
            var btn = $(e.currentTarget);
            btn.parent().remove();
        });
        var bctai_types = {
            faq: ['Question', 'Answer'],
            knowledge: ['Topic', 'Description'],
            nomu: ['Question', 'Category1', 'Category2', 'Response', 'Example', 'Judicial'],
        }

        
        $('#bctai_embeddings_form').on('submit', function (e) {
            console.log(e);
            alert('click');


            var form = $(e.currentTarget);
            var btn = form.find('button');
            var type = $('.bctai-select-entry-type').val();
            //alert(type);
            var has_empty = false;
            var content;
            if (type !== 'free') {
                var custom_content = '';
                $('.bctai-' + type + '-list .bctai-' + type + '-item').each(function (idx, item) {
                    $.each(bctai_types[type], function (idy, name) {
                        var input_name = $(item).find('.bctai-' + type + '-' + name);
                        if (input_name !== undefined) {
                            if (input_name.val() !== '') {
                                custom_content += name + ': ' + input_name.val() + "\n";
                            }
                            else {
                                has_empty = true;
                            }
                        }
                        else {
                            has_empty = true;
                        }
                    })
                });
                content = custom_content;
                //alert(content);
            }
            else {
                content = $('.bctai-embeddings-content').val();
            }
            if (has_empty) {
                alert('Ensure that all fields are filled in.');
                return false;
            }
            if (type !== 'free') {
                $('.bctai-embeddings-content').val(custom_content);
            }
            if (content === '') {
                alert('Please insert content')
            }
            else {
                var data = form.serialize();
                console.log(data);
                //alert(data);
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php') ?>',
                    data: data,
                    dataType: 'json',
                    type: 'post',
                    beforeSend: function () {
                        bctaiLoading(btn)
                    },
                    success: function (res) {
                        console.log(res);
                        bctaiRmLoading(btn);
                        if (res.status === 'success') {
                            $('.bctai-embeddings-success').show();
                            $('.bctai-embeddings-content').val('');
                            if (type !== 'free') {
                                $('.bctai-' + type + '-list').empty();
                            }
                            setTimeout(function () {
                                $('.bctai-embeddings-success').hide();
                            }, 2000)
                        }
                        else {
                            console.log(res);
                            alert(res.msg)
                        }
                    },
                    error: function () {
                        bctaiRmLoading(btn);
                        alert('[error] Something went wrong');
                    }
                })
            }
            return false;
        })
    })
</script>