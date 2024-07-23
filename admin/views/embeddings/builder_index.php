<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<style>
    a{color: #c08aad;}
    .widefat td, .widefat th{
        color: #50575e;
        border: 0.1px solid #ACAABC;
    }
</style>
<h1 style="font: normal normal 900 24px/35px Noto Sans KR; margin:0px;"><?php echo __('Index Builder', 'bctai') ?></h1>


<h3><?php echo __('Indexed Pages', 'bctai') ?></h3>
<?php
if($bctai_builder_types && is_array($bctai_builder_types) && count($bctai_builder_types)){
    foreach($bctai_builder_types as $bctai_builder_type){
        $sql_count_data = $wpdb->prepare("SELECT COUNT(p.ID) FROM ".$wpdb->posts." p WHERE p.post_type=%s AND p.post_status = 'publish'",$bctai_builder_type);
        $total_data = $wpdb->get_var($sql_count_data);
        $sql_done_data = $wpdb->prepare("SELECT COUNT(p.ID) FROM ".$wpdb->postmeta." m LEFT JOIN ".$wpdb->posts." p ON p.ID=m.post_id WHERE p.post_type=%s AND p.post_status = 'publish' AND m.meta_key='bctai_indexed' AND m.meta_value IN ('error','skip','yes')",$bctai_builder_type);
        $total_converted = $wpdb->get_var($sql_done_data);
        if($total_data > 0){
            $percent_process = ceil($total_converted*100/$total_data);
            //echo '<pre>'; print_r($percent_process); echo '</pre>';
            ?>
            <div class="bctai-builder-process bctai-builder-process-<?php echo esc_html($bctai_builder_type)?>">
                <strong>
                    <?php
                    if($bctai_builder_type == 'post'){
                        echo esc_html__('Posts','bctai');
                    }
                    elseif($bctai_builder_type == 'page'){
                        echo esc_html__('Pages','bctai');
                    }
                    elseif($bctai_builder_type == 'product'){
                        echo esc_html__('Products','bctai');
                    }
                    else{
                        echo ucwords(str_replace(array('-','_'),'',$bctai_builder_type));
                    }
                    ?>
                    <small class="bctai-numbers">(<?php echo esc_html($total_converted)?>/<?php echo esc_html($total_data)?>)</small>
                </strong>
                <div class="bctai-builder-process-content">
                    <span class="bctai-percent" style="width: <?php echo esc_html($percent_process)?>%"></span>
                </div>
            </div>
            <?php
        }
    }
}
?>
<?php
$bctai_embedding_page = isset($_GET['wpage']) && !empty($_GET['wpage']) ? sanitize_text_field($_GET['wpage']) : 1;
$bctai_embeddings = new WP_Query(array(
    'post_type'         => 'bctai_builder',
    'posts_per_page'    => 40,
    'paged'             => $bctai_embedding_page,
    'order'             => 'DESC',
    'orderby'           => 'meta_value',
    'meta_key'          => 'bctai_start'
));

?>
<div class="tablenav top">
    <div class="alignleft actions bulkactions" >
        <a href="<?php echo admin_url('admin.php?page=Embeddings&action=builder&sub=reindexall')?>" class="button button-primary" style="background: #8040ad;border: 0px;border-radius: 10px;width: 130px;text-align: center;"><?php echo __('Re-Index All','bctai')?></a>
        <button class="button button-primary btn-reindex-builder" style="background: #8040ad;border: 0px;border-radius: 10px;width: 130px;text-align: center;"><?php echo __('Re-Index Selected','bctai')?></button>
        <a onclick="return confirm('Warning! All indexes will be deleted from Pinecone and elsewhere. Are you sure?')" href="<?php echo admin_url('admin.php?page=Embeddings&action=builder&sub=deleteall')?>" class="button bctai-danger-btn" style="border: 0px;border-radius: 10px;width: 130px;text-align: center;" ><?php echo __('Delete Everything','bctai')?></a>
    </div>
</div>
<div class="tablenav top">
    <div class="alignleft actions bulkactions">
        <?php echo __('Indexed', 'bctai') ?> (<?php echo esc_html($bctai_total_indexed)?>)
        <!-- <a href="<?php echo admin_url('admin.php?page=Embeddings&action=builder&sub=errors')?>"><?php echo __('Failed', 'bctai') ?> (<?php echo esc_html(count($bctai_total_errors))?>)</a> | -->
        <!-- <a href="<?php echo admin_url('admin.php?page=Embeddings&action=builder&sub=skip')?>"><?php echo __('Skipped', 'bctai') ?> (<?php echo esc_html(count($bctai_total_skips))?>)</a> -->
    </div>
</div>
<table class="wp-list-table widefat fixed striped table-view-list posts">
    <thead>
        <tr>
            <td id="cb" class="manage-column column-cb check-column" scope="col"><input type="checkbox" class="bctai-select-all"></td> 
            <th><?php echo __('Title', 'bctai') ?></th>
            <th><?php echo __('Token', 'bctai') ?></th>
            <th><?php echo __('Estimated', 'bctai') ?></th>
            <th><?php echo __('Source', 'bctai') ?></th>
            <th><?php echo __('Status', 'bctai') ?></th>
            <th><?php echo __('Start', 'bctai') ?></th>
            <th><?php echo __('Completed', 'bctai') ?></th>
            <th><?php echo __('Action', 'bctai') ?></th>
        </tr>
    </thead>
    <tbody class="bctai-builder-list">
    <?php
    if($bctai_embeddings->have_posts()) {
        foreach($bctai_embeddings->posts as $bctai_embedding) {
            include __DIR__.'/builder_item.php';
        }
    }
    ?>
    </tbody>
</table>
<div class="bctai-paginate">
    <?php
    echo paginate_links( array(
        'base'         => admin_url('admin.php?page=Embeddings&action=builder&wpage=%#%'),
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
        var bctaiCurrentPage = <?php echo esc_html($bctai_embedding_page);?>;
        $('.bctai_modal_close').click(function (){
            
            $('.bctai_modal_close').closest('.bctai_modal').hide();
            $('.bctai-overlay').hide();
        });
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
        


        $(document).on('click','.bctai-embedding-content',function (e){
            var btn = $(e.currentTarget);
            var content = btn.attr('data-content');
            content = content.replace(/\n/g,'<br>');
            $('.bctai_modal_title').html('Embedding Content');
            $('.bctai_modal_content').html(content);
            $('.bctai-overlay').show();
            $('.bctai_modal').show();
        });


        $(document).on('click','.bctai_reindex', function (e){
            var btn = $(e.currentTarget);
            var id = btn.attr('data-id');
            var conf = confirm('Are you sure?');
            if(conf){
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php')?>',
                    data: {action: 'bctai_builder_reindex', id: id},
                    dataType: 'JSON',
                    type: 'POST',
                    beforeSend: function (){
                        bctaiLoading(btn);
                    },
                    success: function (res){
                        alert('Success')
                        window.location.reload();

                    },
                    error: function (){
                        bctaiRmLoading(btn);
                        alert('Something went wrong');
                    }
                })    
            }
        });
        $(document).on('click','.bctai_delete' ,function (e){
            //alert("delete click");
            var btn = $(e.currentTarget);
            var id = btn.attr('data-id');
            var pinecone_nonce = '<?php echo wp_create_nonce('bctai-ajax-nonce') ?>';
            //console.log(pinecone_nonce);
            // console.log(id);
            var conf = confirm('Are you sure?');
            if(conf) {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php')?>',
                    data: {action: 'bctai_builder_delete', id: id, nonce:pinecone_nonce},
                    dataType: 'JSON',
                    type: 'POST',
                    beforeSend: function (){
                        bctaiLoading(btn);
                    },
                    success: function (res){
                        console.log(res);
                        bctaiRmLoading(btn);
                        if(res.status === 'success'){
                            $('#bctai-builder-'+id).remove();
                        }
                        else{
                            alert(res.msg);
                        }
                    },
                    error: function (){
                        bctaiRmLoading(btn);
                        alert('Something went wrong');
                    }
                })
            }
        }); 
    })
</script>
