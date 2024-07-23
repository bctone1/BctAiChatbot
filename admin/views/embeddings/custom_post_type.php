<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if(isset($_POST['bctai_save_builder_settings'])){    
    //echo '<pre>'; print_r('hellohellohellohellohellohellohellohellohello'); echo '</pre>';
    if(isset($_POST['bctai_builder_custom'])){
        $bctai_builder_customs = \BCTAI\bctai_util_core()->sanitize_text_or_array_field($_POST['bctai_builder_custom']);
        echo '<pre>'; print_r($bctai_builder_customs); echo '</pre>';
        foreach ($bctai_builder_customs as $key=>$bctai_builder_custom) {            
            update_option('bctai_builder_custom_'.$key,$bctai_builder_custom);
        }
    }
}
$bctai_all_post_types = get_post_types(array(
    'public'   => true,
    '_builtin' => false,
),'objects');
$bctai_custom_types = [];
//echo '<pre>'; print_r($bctai_all_post_types); echo '</pre>';
foreach($bctai_all_post_types as $key=>$all_post_type){
    if($key != 'product'){
        //echo '<pre>'; print_r($key); echo '</pre>';
        $bctai_assigns = get_option('bctai_builder_custom_'.$key,'');
        //echo '<pre>'; print_r($bctai_assigns); echo '</pre>';
        $meta_keys = \BCTAI\bctai_util_core()->bctai_get_meta_keys($key);
        //echo '<pre>'; print_r($meta_keys); echo '</pre>';
        $taxonomies = \BCTAI\bctai_util_core()->bctai_existing_taxonomies($key);
        //echo '<pre>'; print_r($taxonomies); echo '</pre>';
        $post_type = array(
            'assigns' => $bctai_assigns,
            'label' => $all_post_type->label,
            'standard' => array(
                'bctaip_ID' => esc_html__('ID','wp-bct-ai'),
                'bctaip_post_title' => esc_html__('Title','wp-bct-ai'),
                'bctaip_post_content' => esc_html__('Content','wp-bct-ai'),
                'bctaip_post_excerpt' => esc_html__('Excerpt','wp-bct-ai'),
                'bctaip_post_date' => esc_html__('Date','wp-bct-ai'),
                'bctaip_post_type' => esc_html__('Post Type','wp-bct-ai'),
                'bctaip_post_parent' => esc_html__('Parent','wp-bct-ai'),
                'bctaip_post_status' => esc_html__('Status','wp-bct-ai'),
                'bctaip_permalink' => esc_html__('Permalink','wp-bct-ai'),
            ),
            'custom_fields' => $meta_keys,
            'taxonomies' => $taxonomies,
            'users' => array(
                'bctaiauthor_user_login' => esc_html__('User Login','wp-bct-ai'),
                'bctaiauthor_user_nicename' => esc_html__('Nicename','wp-bct-ai'),
                'bctaiauthor_user_email' => esc_html__('Email','wp-bct-ai'),
                'bctaiauthor_display_name' => esc_html__('Display Name','wp-bct-ai'),
            )
        );
        
        $bctai_custom_types[$key] = $post_type;
        //echo '<pre>'; print_r($bctai_custom_types); echo '</pre>';
    }
}
//echo '<pre>'; print_r($bctai_custom_types); echo '</pre>';
//echo '<pre>'; print_r($bctai_builder_types); echo '</pre>';

if(count($bctai_custom_types)){
    foreach($bctai_custom_types as $key=>$bctai_custom_type){
        ?>
        <div class="mb-5">
            <label>
                <input <?php echo in_array($key,$bctai_builder_types) ? ' checked':'';?> type="checkbox" name="bctai_builder_types[]" value="<?php echo esc_html($key)?>">&nbsp;<?php echo esc_html($bctai_custom_type['label'])?>                
            </label>
            <input class="bctai_builder_custom_<?php echo esc_html($key)?>" type="hidden" name="<?php echo in_array($key,$bctai_builder_types) ? 'bctai_builder_custom['.esc_html($key).']' : '';?>" value="<?php echo esc_html($bctai_custom_type['assigns'])?>">            
         
            <a
                class="bctai_assignments_<?php echo esc_html($key)?>"
                data-assigns="<?php echo esc_html($bctai_custom_type['assigns'])?>"
                data-post-type="<?php echo esc_html($key)?>"
                data-post-name="<?php echo esc_html($bctai_custom_type['label'])?>"
                data-custom-fields="<?php echo isset($bctai_custom_type['custom_fields']) && is_array($bctai_custom_type['custom_fields']) && count($bctai_custom_type['custom_fields']) ? esc_html(wp_json_encode($bctai_custom_type['custom_fields'])) : ''?>"
                data-taxonomies="<?php echo isset($bctai_custom_type['taxonomies']) && is_array($bctai_custom_type['taxonomies']) && count($bctai_custom_type['taxonomies']) ? esc_html(wp_json_encode($bctai_custom_type['taxonomies'])) : ''?>"
                data-users="<?php echo isset($bctai_custom_type['users']) && is_array($bctai_custom_type['users']) && count($bctai_custom_type['users']) ? esc_html(wp_json_encode($bctai_custom_type['users'])) : ''?>"
                data-standards="<?php echo isset($bctai_custom_type['standard']) && is_array($bctai_custom_type['standard']) && count($bctai_custom_type['standard']) ? esc_html(wp_json_encode($bctai_custom_type['standard'])) : ''?>"
                href="javascript:void(0)">
                [<?php echo esc_html__('Select Fields','wp-bct-ai')?>]
            </a>
        </div>
        <?php
    }
}
?>
<script>
    jQuery(document).ready(function ($){
        function bctaigetFields(btn){
            let custom_fields = btn.attr('data-custom-fields');
            let taxonomies = btn.attr('data-taxonomies');
            let users = btn.attr('data-users');
            let standards = btn.attr('data-standards');
            let fields = {};
            if(standards !== ''){
                standards = JSON.parse(standards);
                fields['1standards'] = standards;
            }
            if(custom_fields !== ''){
                custom_fields = JSON.parse(custom_fields);
                fields['2custom'] = {};
                $.each(custom_fields, function(idx, item){
                    fields['2custom'][item] = item.replace(/bctaicf_/g,'');
                })
            }
            if(taxonomies !== ''){
                taxonomies = JSON.parse(taxonomies);
                fields['3taxonomies'] = {};
                $.each(taxonomies, function(idx, item){
                    fields['3taxonomies'][item.label] = item.name;
                });
            }
            if(users !== ''){
                users = JSON.parse(users);
                fields['4users'] = users;
            }
            return fields;
        }
        $('.bctai_modal_close').click(function (){
            $('.bctai_modal_close').closest('.bctai_modal').hide();
            $('.bctai-overlay').hide();
        });
        function bctaiAddField(fields, selected_field){
            let field_selected = false;
            let field_name = false;
            if(typeof selected_field !== "undefined"){
                field_selected = selected_field[0];
                field_name = selected_field[1].replace(/\\/g,'');
            }
            let html = '<div class="bctai_assign_field" style="display: flex;justify-content: space-between;padding: 5px;border: 1px solid #ccc;border-radius: 3px;margin-bottom: 10px;background: #f1f1f1;">';
            html += '<select class="regular-text">';
            $.each(fields, function (idx, item){
                if(idx === '1standards'){
                    html += '<optgroup label="<?php echo esc_html__('Standard','wp-bct-ai')?>">';
                }
                if(idx === '2custom'){
                    html += '<optgroup label="<?php echo esc_html__('Custom Fields','wp-bct-ai')?>">';
                }
                if(idx === '3taxonomies'){
                    html += '<optgroup label="<?php echo esc_html__('Taxonomies','wp-bct-ai')?>">';
                }
                if(idx === '4users'){
                    html += '<optgroup label="<?php echo esc_html__('Users','wp-bct-ai')?>">';
                }
                $.each(item, function(idy, name){
                    html += '<option'+(field_selected && field_selected === idy ? ' selected':'')+' value="'+idy+'">'+name+'</option>';
                })
                html += '</optgroup>';
            })
            html += '</select>';
            html += '<input type="text" class="regular-text" value="'+(field_name ?  field_name : '')+'" placeholder="<?php echo esc_html__('Label','gpt3-ai-content-generator')?>">';
            html += '<span class="bctai_assign_delete dashicons dashicons-trash" style="height: 29px;width: 36px;background: #cf0000;border-radius: 2px;cursor: pointer;display: flex;align-items: center;justify-content: center;color: #fff;"></span>';
            html += '</div>';
            return html;
        }
        $(document).on('click','.bctai_assign_delete', function (e){
            $(e.currentTarget).parent().remove();
        })
        $(document).on('click','.bctai_assign_field_btn', function (e){
            let btn = $(e.currentTarget);
            let post_type = btn.attr('data-post-type');
            let assignBtn = $('.bctai_assignments_'+post_type);
            let fields = bctaigetFields(assignBtn);
            let html = bctaiAddField(fields);
            $('.bctai_assigns_fields').append(html);
        })
        $(document).on('click','.bctai_assignments', function (e){
            let btn = $(e.currentTarget);
            let content = '';
            let post_name = btn.attr('data-post-name');
            let post_type = btn.attr('data-post-type');
            let assigns = btn.attr('data-assigns');
            let fields = bctaigetFields(btn);
            content += '<div class="bctai_assigns_fields" data-post-type="'+post_type+'">';
            if(assigns !== ''){
                let assigns_lists = [];
                assigns = assigns.split('||');
                $.each(assigns, function (idx, item){
                    let assign_item = item.split('##');
                    assigns_lists.push(assign_item[0]);
                    content += bctaiAddField(fields,assign_item);
                });

            }
            content += '</div>';
            content += '<div class="bctai_assign_footer"><button data-post-type="'+post_type+'" class="button button-link-delete bctai_assign_field_btn" style="display: block;width: 48%;"><?php echo esc_html__('Add Field','gpt3-ai-content-generator')?></button>';
            content += '<button class="button button-primary bctai_assign_field_save" data-post-type="'+post_type+'" style="display: block;width: 48%"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button></div>';
            $('.bctai_modal_title').html('<?php echo esc_html__('Select Fields','gpt3-ai-content-generator')?>: '+post_name);
            $('.bctai_modal_content').html(content);
            $('.bctai-overlay').show();
            $('.bctai_modal').show();
        });
        $(document).on('click','.bctai_assign_field_save', function (e){
            let btn = $(e.currentTarget);
            let post_type = btn.attr('data-post-type');
            let assigns = [];
            let has_error = false;
            $('.bctai_assigns_fields .bctai_assign_field').each(function (idx, item){
                let field_id = $(item).find('select').val();
                let field_name = $(item).find('input').val();
                if(field_name === ''){
                    has_error = '<?php echo esc_html__('Please insert all fields or remove empty fields','gpt3-ai-content-generator')?>';
                }
                else{
                    assigns.push(field_id+'##'+field_name);
                }
            })
            if(has_error){
                alert(has_error);
            }
            else{
                $('.bctai_builder_custom_'+post_type).val(assigns.join('||'));
                $('.bctai_assignments_'+post_type).attr('data-assigns',assigns.join('||'));
                $('.bctai_modal_content').empty();
                $('.bctai-overlay').hide();
                $('.bctai_modal').hide();
            }
        });
    })
</script>

