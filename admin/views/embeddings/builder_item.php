<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$token = get_post_meta($bctai_embedding->ID, 'bctai_embedding_token', true);
$bctai_source = get_post_meta($bctai_embedding->ID, 'bctai_source', true);
$bctai_indexed = get_post_meta($bctai_embedding->ID, 'bctai_indexed', true);
$bctai_start = get_post_meta($bctai_embedding->ID, 'bctai_start',true);
$bctai_completed = get_post_meta($bctai_embedding->ID,'bctai_completed',true);

//var_dump($bctai_source);
//  echo '<pre>';print_r($bctai_embedding);echo '<pre>';
?>
<tr id="bctai-builder-<?php echo esc_html($bctai_embedding->ID)?>">
    <th scope="row" class="check-column">
        <input class="cb-select-embedding" id="cb-select-<?php echo esc_html($bctai_embedding->ID);?>" type="checkbox" name="ids[]" value="<?php echo esc_html($bctai_embedding->ID);?>">
    </th>
    <td><a data-content="<?php echo esc_html($bctai_embedding->post_content)?>" href="javascript:void(0)" class="bctai-embedding-content"><?php echo esc_html($bctai_embedding->post_title)?></a></td>
    <td><?php echo esc_html($token)?></td>
    <td><?php echo !empty($token) ? (number_format((int)esc_html($token)*0.0004/1000,5)).'$': '--'?></td>
    <td>
        <?php
        if($bctai_source == 'post') {
            echo esc_html__('Posts','bctai');
        }
        if($bctai_source == 'page') {
            echo esc_html__('Page','bctai');
        }
        if($bctai_source == 'product') {
            echo esc_html__('Product','bctai');
        }
        if($bctai_source == 'legalprotech') {
            echo esc_html__('Legalprotech','bctai');
        }
        ?>
    </td>
    <td class="builder-status">
        <?php
        if($bctai_indexed == '' || $bctai_indexed == 'yes') {
            echo '<span style="color: #018b25;font-weight: bold;">'.esc_html(__('Success', 'bctai')).'</span>';
        }
        if($bctai_indexed == 'error') {
            echo '<span style="color: #ff0000;font-weight: bold;">'.esc_html(__('Error', 'bctai')).'</span>';
            if(!empty($bctai_error_msg)){
                echo '<p>'.esc_html($bctai_error_msg).'</p>';
            }
        }
        if($bctai_indexed == 'reindex') {
            echo '<span style="color: #d73e1c;font-weight: bold;">'.esc_html(__('Pending','bctai')).'</span>';
        }
        ?>
    </td>
    <td>
        <?php
        if(!empty($bctai_start)) {
            echo esc_html(gmdate('Y.m.d. H:i',$bctai_start));
        }
        ?>
    </td>
    <td>
        <?php
        if(!empty($bctai_completed)) {
            echo esc_html(gmdate('Y.m.d. H:i',$bctai_completed));
        }
        ?>
    </td>
    <td>
        <?php
        if($bctai_indexed != 'reindex'):
        ?>
        <button data-id="<?php echo esc_html($bctai_embedding->ID)?>" class="button button-primary button-small bctai_reindex" style="    width: 63px;
    height: 33px;
    background: #8040AD 0% 0% no-repeat padding-box;
    border-radius: 16px;
    border: 0px;
    opacity: 1;
    color: #FFFFFF;"><?php echo __('Re-Index','bctai')?></button>
        <?php
        endif;
        ?>
        <button data-id="<?php echo esc_html($bctai_embedding->ID)?>" class="button button-link-delete button-small bctai_delete" style="    width: 63px;
    height: 33px;
    border: 1px solid var(--unnamed-color-707070);
    background: #F1F1F1 0% 0% no-repeat padding-box;
    border: 1px solid #707070;
    border-radius: 16px;
    opacity: 1;
    color: #4F4D5F;" ><?php echo __('Delete','bctai')?></button>
    </td>
</tr>
