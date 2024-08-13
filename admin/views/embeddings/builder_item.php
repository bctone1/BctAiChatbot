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
    <th id="cb" class="check-column" scope="col">
        <div class="check only">
            <input id="cb-select-<?php echo esc_html($bctai_embedding->ID);?>" type="checkbox" name="ids[]" value="<?php echo esc_html($bctai_embedding->ID);?>">
            <label for="checkbox11"></label>
        </div>
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
                <button data-id="<?php echo esc_html($bctai_embedding->ID)?>" class="btn btnXS bgPrimary bctai_reindex"><span>Re-Index</span></button>
            <?php
            endif;
            ?>
            <button data-id="<?php echo esc_html($bctai_embedding->ID)?>" class="btn btnXS bgLightGrayBorder bctai_delete"><span>Delete</span></button>
        
    </td>
</tr>
