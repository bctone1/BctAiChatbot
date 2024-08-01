<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="bctai-modal-content">
<?php
if(isset($bctai_data) && is_array($bctai_data) && count($bctai_data)):
?>
<table class="wp-list-table widefat fixed striped table-view-list comments">
    <thead>
        <tr>
            <th><?php echo esc_html__('ID','wp-bct-ai')?></th>
            <<th><?php echo esc_html__('Purpose','wp-bct-ai')?></th>
            <th><?php echo esc_html__('Created At','wp-bct-ai')?></th>
            <th><?php echo esc_html__('Filename','wp-bct-ai')?></th>
            <th><?php echo esc_html__('Status','wp-bct-ai')?></th>
            <th><?php echo esc_html__('Download','wp-bct-ai')?></th>
        </tr>
    </thead>
    <tbody>
    <?php
    foreach($bctai_data as $item){
        ?>
        <tr>
            <td><?php echo esc_html($item->id)?></td>
            <td><?php echo esc_html($item->purpose)?></td>
            <td><?php echo esc_html(gmdate('Y-m-d H:i:s',$item->created_at))?></td>
            <td><?php echo esc_html($item->filename)?></td>
            <td><?php echo esc_html($item->status)?></td>
            <td><a download="download" href="<?php echo admin_url('admin-ajax.php?action=bctai_download&id='.$item->id)?>"><?php echo esc_html__('Download','wp-bct-ai')?></a></td>
        </tr>
        <?php
    }
    ?>
    </tbody>
</table>
<?php
else:
    echo esc_html__('Fine-tuning has not yet been completed.','wp-bct-ai');
endif;
?>
</div>