<?php
if (!defined('ABSPATH'))
    exit;

?>

<h1 style="font-family: inherit;line-height: 1.46;font-size: 24px;font-weight: 900;"><?php echo __('Contents Builder', 'bctai') ?></h1>

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


<div class="bctai-nomu-item-default" style="display: none">
    <div class="bctai-nomu-item">
        <p style="font: normal normal 900 24px/35px Noto Sans KR; margin:0px;"><?php echo __('Embedding Content','bctai') ?></p>

        <span class="bctai-nomu-close"><i class="fa-solid fa-x" style="font-weight: 900;"></i></span>
        <p>
            <label><strong style="font-size: 14px;font-weight: normal;"><?php echo esc_html__('Question', 'bctai') ?></strong></label>
            <input type="text" class="bctai-nomu-Question" style="width: 1006px;height: 52px;background: #F1F1F1 0% 0% no-repeat padding-box;border-radius: 16px;opacity: 1; border:0px;">
        </p>
        <p>
            <label><strong style="font-size: 14px;font-weight: normal;"><?php echo esc_html__('Category1', 'bctai') ?></strong></label>
            <input type="text" class="bctai-nomu-Category1" style="width: 1006px;height: 52px;background: #F1F1F1 0% 0% no-repeat padding-box;border-radius: 16px;opacity: 1; border:0px;">
        </p>
        <p>
            <label><strong style="font-size: 14px;font-weight: normal;"><?php echo esc_html__('Category2', 'bctai') ?></strong></label>
            <input type="text" class="bctai-nomu-Category2" style="width: 1006px;height: 52px;background: #F1F1F1 0% 0% no-repeat padding-box;border-radius: 16px;opacity: 1; border:0px;">
        </p>
        <p>
            <label><strong style="font-size: 14px;font-weight: normal;"><?php echo esc_html__('Response', 'bctai') ?></strong></label>
            <textarea class="bctai-nomu-Response" rows="5" style="width: 1006px;height: 52px;background: #F1F1F1 0% 0% no-repeat padding-box;border-radius: 16px;opacity: 1; border:0px;"></textarea>
        </p>
        <p>
            <label><strong style="font-size: 14px;font-weight: normal;"><?php echo esc_html__('Example', 'bctai') ?></strong></label>
            <textarea class="bctai-nomu-Example" rows="5" style="width: 1006px;height: 52px;background: #F1F1F1 0% 0% no-repeat padding-box;border-radius: 16px;opacity: 1; border:0px;"></textarea>
        </p>
        <p>
            <label><strong style="font-size: 14px;font-weight: normal;"><?php echo esc_html__('Judicial Precedent', 'bctai') ?></strong></label>
            <textarea class="bctai-nomu-Judicial" rows="7" style="width: 1006px;height: 52px;background: #F1F1F1 0% 0% no-repeat padding-box;border-radius: 16px;opacity: 1; border:0px;"></textarea>
        </p>
    </div>
</div>



<form action="" method="post" id="bctai_embeddings_form">
    <?php wp_nonce_field('bctai_embeddings_save');?>

    <input type="hidden" name="action" value="bctai_embeddings">
    <div class="bctai-embeddings-success"style="padding: 10px;background: #fff;border-left: 2px solid #11ad6b;display: none"><?php echo esc_html__('Record saved successfully', 'bctai') ?></div>
    <div class="bctai-mb-10" style="display: flex;">
        <p style="font-family: inherit;font-size: 14px;font-weight: 500;"><strong><?php echo esc_html__('Content Type', 'bctai') ?></strong></p>

        <select name="type" class="regular-text bctai-select-entry-type"style="margin-left: 130px; background: #f1f1f1 url(data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M5%206l5%205%205-5%202%201-7%207-7-7%202-1z%22%20fill%3D%22%23555%22%2F%3E%3C%2Fsvg%3E) no-repeat right 5px top 55%;border-radius: 16px;opacity: 1;    width: 540px;height: 52px; max-width:1000px;">
            <option value="free"><?php echo esc_html__('Free Text', 'bctai') ?></option>
            <option value="faq"><?php echo esc_html__('FAQ', 'bctai') ?></option>
            <option value="knowledge"><?php echo esc_html__('KnowledgeBase', 'bctai') ?></option>
            

        </select>
    </div>




    <div class="bctai-mb-10 bctai-data-entry bctai-free" style="display: flex;">
        <p style="">
            <strong><?php echo esc_html__('Content', 'bctai') ?></strong>
        </p>
        <textarea name="content" class="bctai-embeddings-content" rows="15"style="width: 1365px;height: 283px;margin-left: 160px;background: #f1f1f1;border: 0px;border-radius: 16px;opacity: 1;"></textarea>
    </div>











    <div class="bctai-mb-10 bctai-data-entry bctai-faq" style="display: none;width: 1000px;margin-left: 210px;">
        <div class="bctai-faq-list">
            
        </div>
        <button type="button" class="button button-primary btn-add-faq"style="width: 1066px;background: #9f89af;border-color: #9f89af; ">Add More</button>
    </div>

    <div class="bctai-mb-10 bctai-data-entry bctai-knowledge" style="display: none; width: 1000px;margin-left: 210px;">
        <div class="bctai-knowledge-list"></div>
        <button type="button" class="button button-primary btn-add-knowledge"style="width: 1066px; background: #9f89af;border-color: #9f89af; ">Add More</button>
    </div>

    <div class="bctai-mb-10 bctai-data-entry bctai-nomu" style="display: none; width: 1000px;margin-left: 210px;">
        <div class="bctai-nomu-list"></div>
        <button type="button" class="button button-primary btn-add-nomu"style="width: 1066px;background: #9f89af;border-color: #9f89af; ">Add More</button>
    </div>



    <button class="button button-primary" style="width: 187px;height: 58px;border-radius: 16px;background: #8040ad;border: 0px;margin-left: 206px;font-size: 14px;font-weight: bold;    margin-top: 20px;">
        <?php echo esc_html__('SAVE', 'bctai') ?>
    </buttion>
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