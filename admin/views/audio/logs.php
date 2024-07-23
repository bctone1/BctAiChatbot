<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if (isset($_GET['search']) && !empty($_GET['search']) && !wp_verify_nonce($_GET['bctai_nonce'], 'bctai_audiolog_search_nonce')) {
    die(BCTAI_NONCE_ERROR);
}
if(isset($_GET['audio_delete']) && !empty($_GET['audio_delete'])){
    if(!wp_verify_nonce($_GET['_wpnonce'], 'bctai_delete_'.sanitize_text_field($_GET['audio_delete']))){
        die(BCTAI_NONCE_ERROR);
    }
    wp_delete_post(sanitize_text_field($_GET['audio_delete']));
    echo '<script>window.location.href = "'.admin_url('admin.php?page=Audio&action=logs').'"</script>'; 
}
$bctai_audio_page = isset($_GET['wpage']) && !empty($_GET['wpage']) ? sanitize_text_field($_GET['wpage']) : 1;
$args = array(
    'post_type' =>  'bctai_audio',
    'posts_per_page'    => 40,
    'paged'     =>  $bctai_audio_page,
    'order'     => 'DESC',
    'orderby'   => 'date'
);

// echo '<pre>'; print_r($args); echo '</pre>';
$search = '';
if(isset($_GET['search']) && !empty($_GET['search'])){
    $search = sanitize_text_field($_GET['search']);
    $args['s'] = $search;
}
$bctai_audios = new WP_Query($args);
?>
<style>
    .bctai_modal{
        height: 40%;
    }
    .bctai_modal_content{
        height: calc(100% - 80px);
        overflow-y: auto;
    }
    .bctai_modal_content pre{
        overflow-y: unset;
    }
</style>

<h1 style="font-weight: bolder;margin-left: 30px;">
    <?php echo __('Logs', 'bctai') ?>
</h1>

<div>
    <div class="bctai-mb-10">
        <form action="" method="GET">
            <?php wp_nonce_field('bctai_audiolog_search_nonce', 'bctai_nonce'); ?>
            <input type="hidden" name="page" value="bctai_audio">
            <input type="hidden" name="action" value="logs">
            <input value="<?php echo esc_html($search)?>" name="search" type="text" placeholder="<?php echo __('Search Audio','bctai') ?>">
            <button class="button button-primary"><?php echo __('Search', 'bctai') ?></button>
        </form>
    </div>
</div>
<table class="wp-list-table widefat fixed striped table-view-list posts">
    <thead>
        <tr>
            <th width="40"><?php echo __('ID', 'bctai') ?></th>
            <th><?php echo __('Title', 'bctai') ?></th>
            <th><?php echo __('Format', 'bctai') ?></th>
            <th><?php echo __('Date', 'bctai') ?></th>
            <th><?php echo __('Duration', 'bctai') ?></th>
            <th><?php echo __('Action', 'bctai') ?></th>
        </tr>
    </thead>
    <tbody>
    <?php
        if($bctai_audios->have_posts()) {
            foreach($bctai_audios->posts as $bctai_audio) {
                $bctai_response = get_post_meta($bctai_audio->ID, 'bctai_response', true);
                $bctai_duration = get_post_meta($bctai_audio->ID, 'bctai_duration', true);
            ?>
            <tr>
                <td><?php echo esc_html($bctai_audio->ID)?></td>
                <td>
                    <?php
                    if($bctai_response == 'post'):
                    $bctai_post_id = get_post_meta($bctai_audio->ID, 'bctai_post', true);
                    ?>
                    <a href="<?php echo admin_url('post.php?post='.esc_html($bctai_post_id).'&action=edit')?>" class="bctai-view-content">
                    <?php
                    else:
                    ?>
                        <a data-response="<?php echo esc_html($bctai_response)?>" href="javascript:void(0)" class="bctai-view-content" data-content="<?php echo esc_html($bctai_audio->post_content)?>">
                    <?php
                    endif;
                    ?>
                    <?php
                    if($bctai_response == 'post') {
                        $post_title = get_the_tile($bctai_post_id);
                        if(empty($post_title)){
                            echo esc_html($bctai_audio->post_title);
                        }
                        else{
                            echo esc_html($post_title);
                        }
                    }
                    else {
                        echo esc_html($bctai_audio->post_title);
                    }
                    ?>
                    </a>
                </td>
                <td><?php echo esc_html($bctai_response)?></td>
                <td><?php echo esc_html(gmdate('d.m.Y H:i',strtotime($bctai_audio->post_date)))?></td>
                <td><?php echo esc_html(BCTAI\BCTAI_Audio::get_instance()->bctai_seconds_to_time((int)$bctai_duration))?></td>
                <td>
                    <?php
                    if($bctai_response != 'post'):
                        //echo '<pre>'; print_r($bctai_audio->ID); echo '</pre>';
                    ?>
                        <a download href="<?php echo wp_nonce_url(site_url('index.php?bctai_download_audio='.$bctai_audio->ID),'bctai_download_'.$bctai_audio->ID)?>" class="button button-primary button-small">Download</a>                        
                    <?php
                    endif;
                    ?>
                    <a onclick="return confirm('Are you sure?')" href="<?php echo wp_nonce_url(admin_url('admin.php?page=Audio&action=logs&audio_delete='.$bctai_audio->ID),'bctai_delete_'.$bctai_audio->ID)?>" class="button button-link-delete button-small">Delete</a>
                </td>
            </tr>
            <?php
            }
        }
    ?>
    </tbody>
</table>
<div class="bctai-paginate">
    <?php
    echo paginate_links( array(
        'base'         => admin_url('admin.php?page=Audio&action=logs&wpage=%#%'),
        'total'        => $bctai_audios->max_num_pages,
        'current'      => $bctai_audio_page,
        'format'       => '?wpage=%#%',
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
        })
        $('.bctai-view-content').click(function (){
            var content = $(this).attr('data-content');
            var response = $(this).attr('data-response');
            var html = '';
            html += content.replace(/\n/g, "<br />");
            if(response === 'json' || response === 'verbose_json') {


            }
            else{
                $('.bctai_modal_content').html(html);
            }
            $('.bctai-overlay').show();
            $('.bctai_modal').show();
            $('.bctai_modal_title').html('View Content');
        })
    })
</script>