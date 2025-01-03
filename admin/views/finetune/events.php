<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="bctai-modal-content">
    <?php
    if(isset($bctai_data) && is_array($bctai_data) && count($bctai_data)):
        usort($bctai_data, function ($item1, $item2) {
            return $item2->created_at <=> $item1->created_at;
        });
        ?>
        <table class="wp-list-table widefat fixed striped table-view-list comments">
            <thead>
            <tr>
                <th><?php echo esc_html__('Object', 'wp-bct-ai') ?></th>
                <th><?php echo esc_html__('ID', 'wp-bct-ai') ?></th>
                <th><?php echo esc_html__('Level', 'wp-bct-ai') ?></th>
                <th><?php echo esc_html__('Created At', 'wp-bct-ai') ?></th>
                <th><?php echo esc_html__('Type', 'wp-bct-ai') ?></th>
                <th><?php echo esc_html__('Message', 'wp-bct-ai') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach($bctai_data as $item){
                ?>
                <tr>
                    <td><?php echo esc_html($item->object) ?></td>
                    <td><?php echo esc_html($item->id) ?></td>
                    <td><?php echo esc_html($item->level) ?></td>
                    <td><?php echo esc_html(gmdate('Y-m-d H:i:s', $item->created_at)) ?></td>
                    <td><?php echo esc_html($item->type) ?></td>
                    <td><?php echo esc_html($item->message) ?></td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    <?php
    else:
        ?>
        No events
    <?php
    endif;
    ?>

</div>


