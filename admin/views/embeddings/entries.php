<?php
if ( ! defined( 'ABSPATH' ) ) exit;
global $wpdb;
$bctai_embedding_page = isset($_GET['wpage']) && !empty($_GET['wpage']) ? sanitize_text_field($_GET['wpage']) : 1;
$bctai_sub_action = isset($_GET['sub']) && !empty($_GET['sub']) ? sanitize_text_field($_GET['sub']) : false;
if($bctai_sub_action == 'reindexall') {
    $ids = $wpdb->get_results("SELECT ID FROM ".$wpdb->posts." WHERE post=type='bctai_embeddings'");
    //var_dump($ids);

}
if($bctai_sub_action == 'deleteall'){

}
$bctai_embeddings = new WP_Query(array(
    'post_type'         => 'bctai_embeddings',
    'posts_per_page'    => 40,
    'paged'             => $bctai_embedding_page,
    'order'             => 'DESC',
    'orderby'           => 'date'
));
#var_dump($bctai_embeddings);
?>
<style>
.bctai_modal {
    top: 5%;
    height: 90%;
    position: relative;
}
.bctai_modal_content {
    max-height: calc(100% - 103px);
    overflow-y: auto;
}
.wp-core-ui .button.bctai-danger-btn {
    background: #c90000;
        color: #fff;
        border-color: #cb0000;
}
a{color: #c08aad;}
</style>
<?php
if($bctai_embeddings->have_posts()):
?>
<h1 style="font: normal normal 900 24px/35px Noto Sans KR; margin:0px;"><?php echo __('Entries', 'bctai') ?></h1>

<div class="tablenav top bctai-mb-10" style="padding: 0px 30px;">
    <div class="alignleft actions bulkactions">



        
        <button id="delete-all-posts" class="button button-primary" style="background: #8040ad;border: 0px;border-radius: 13px;width: 130px;text-align: center;" title="Delete All Data"><?php echo __('Delete All', 'bctai') ?></button>
        <button class="button btn-delete-embeddings bctai-danger-btn"style="background: #8040ad;border: 0px;border-radius: 13px;width: 130px;text-align: center;"><?php echo __('Delete Selected', 'bctai') ?></button>
    </div>
</div>
<?php
endif;
?>
<table class="wp-list-table widefat fixed striped table-view-list posts" style="margin:0px auto; width: 1640px;">
    <thead>
        <tr>
            <td id="cb" class="manage-column column-cb check-column" scope="col"><input type="checkbox" class="bctai-select-all"></td>
            <th scope="col"><?php echo __('Content', 'bctai') ?></th>
            <th scope="col"><?php echo __('Token', 'bctai') ?></th>
            <th scope="col"><?php echo __('Estimated', 'bctai') ?></th>
            <th scope="col"><?php echo __('Type', 'bctai') ?></th>
            <th scope="col"><?php echo __('Date', 'bctai') ?></th>
            <th scope="col"><?php echo __('Status', 'bctai') ?></th>
            <th scope="col"><?php echo __('Action', 'bctai') ?></th>
        </tr>    
    </thead>
    <tbody>
    <?php    
    if($bctai_embeddings->have_posts()) {
        foreach ($bctai_embeddings->posts as $bctai_embedding) {
            $token = get_post_meta($bctai_embedding->ID,'bctai_embedding_token',true);
            $bctai_embedding_type = get_post_meta($bctai_embedding->ID,'bctai_embedding_type',true);
            $bctai_embedding_status = get_post_meta($bctai_embedding->ID,'bctai_embeddings_reindex',true);
            ?>
            <tr id="bctai-builder-<?php echo esc_html($bctai_embedding->ID)?>">
                <th scope="row" class="check-column">
                    <input class="cb-select-embedding" id="cb-select-<?php echo esc_html($bctai_embedding->ID);?>" type="checkbox" name="ids[]" value="<?php echo esc_html($bctai_embedding->ID);?>">
                </th>
                <td><a data-content="<?php echo htmlentities(wp_kses_post($bctai_embedding->post_content),ENT_QUOTES,'UTF-8')?>" href="javascript:void()" class="bctai-embedding-content"><?php echo esc_html($bctai_embedding->post_title)?>..</a></td>
                <td><?php echo esc_html($token)?></td>
                <td><?php echo !empty($token) ? (number_format((int)esc_html($token)*0.0004/1000,5)).'$': '--'?></td>
                <td>
                    <?php
                    if(!$bctai_embedding_type || $bctai_embedding_type == '' || $bctai_embedding_type == 'free'){
                        echo __('Free Text','bctai');
                    }
                    if($bctai_embedding_type == 'faq'){
                        echo __('FAQ','bctai');
                    }
                    if($bctai_embedding_type == 'knowledge'){
                        echo __('KnowledgeBase','bctai');
                    }
                    if($bctai_embedding_type == 'nomu'){
                        echo __('Nomu','bctai');
                    }

                    ?>
                </td>
                <td><?php echo esc_html($bctai_embedding->post_date)?></td>
                <td><?php echo $bctai_embedding_status ? '<span>Pending</span>' : '<span style="color: #26a300">' . __('succeeded','bctai') . '</span>'; ?></td>
                <td><button style=" background: #8040ad;color: white;border: 0px;border-radius: 20px;width: 60px;height: 30px;" data-id="<?php echo esc_html($bctai_embedding->ID)?>" class="button button-link-delete bctai_delete button-small"><?php echo __('Delete', 'bctai') ?></button></td>
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
        'base'         => admin_url('admin.php?page=Embeddings&action=entries&wpage=%#%'),
        'total'        => $bctai_embeddings->max_num_pages,
        'current'      => $bctai_embedding_page,
        'format'       => '?wpage=%#%',
        'show_all'     => false,
        'prev_next'    => false,
        'add_args'     => false,
    ));
    ?>
</div>
<script>
    jQuery(document).ready(function ($){


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


        $('.bctai_modal_close').click(function (){
            $('.bctai_modal_close').closest('.bctai_modal').hide();
            $('.bctai-overlay').hide();
        })


        
        $('.bctai-embedding-content').click(function (){
            var content = $(this).attr('data-content');
            content = content.replace(/\n/g, "<br />");
            $('.bctai_modal_title').html('Embedding Content');
            $('.bctai_modal_content').html(content);
            $('.bctai-overlay').show();
            $('.bctai_modal').show();
        });



        $('.btn-delete-embeddings').click(function (){
            var conf = confirm('Warning! Entries will be deleted from Pinecone and elsewhere. Are you sure?')
            if(conf) {
                var btn = $(this);
                var ids = [];
                $('.cb-select-embedding:checked').each(function (idx, item) {
                    ids.push($(item).val())
                });
                if (ids.length) {
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php')?>',
                        data: {
                            action: 'bctai_delete_embeddings',
                            ids: ids,
                            nonce: '<?php echo wp_create_nonce('bctai-ajax-nonce'); ?>'

                        },
                        dataType: 'JSON',
                        type: 'POST',
                        beforeSend: function () {
                            bctaiLoading(btn);
                        },
                        success: function (res) {
                            window.location.reload();
                        },
                        error: function () {

                        }
                    });
                } else {
                    alert('Nothing to do');
                }
            }
        });





        $(document).on('click','.bctai_delete', function (e){
            var btn = $(e.currentTarget);
            var id = btn.attr('data-id');
            var conf = confirm('Are you sure?');
            if(conf) {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php')?>',
                    data: {
                        action: 'bctai_builder_delete',
                        id: id,
                        nonce: '<?php echo wp_create_nonce('bctai-ajax-nonce'); ?>'
                    },
                    dataType: 'JSON',
                    type: 'POST',
                    beforeSend: function (){
                        bctaiLoading(btn);
                    },
                    success: function(res){
                        bctaiLoading(btn);
                        if(res.status === 'success') {
                            $('#bctai-builder-'+id).remove();
                        }
                        else {
                            console.log(res);
                        }
                    },
                    error: function (){
                        bctaiRmLoading(btn);
                        alert('Something went wrong-2');
                    }
                })
            }
        });


        $('#delete-all-posts').click(function(e) {
            e.preventDefault();
            var confirmDeletion = confirm('Are you sure you want to delete all data?');
            if (!confirmDeletion) {
                return;
            }

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'bctai_delete_all_embeddings',
                    nonce: '<?php echo wp_create_nonce('bctai-ajax-nonce'); ?>'
                },
                success: function(response) {
                    window.location.reload();
                },
                error: function() {
                    alert('An error occurred while trying to delete all data.');
                }
            });
        });


    })
</script>
