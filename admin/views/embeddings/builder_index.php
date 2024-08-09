<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>


<h2 class="sectionTitle">Index Builder</h2>






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
    <div class="sortArea">
        <!-- <a onclick="return confirm('Warning! All indexes will be deleted from Pinecone and elsewhere. Are you sure?')" href="<?php echo admin_url('admin.php?page=Embeddings&action=builder&sub=deleteall')?>" class="button bctai-danger-btn"><?php echo __('Delete Everything','bctai')?></a> -->
        <button type="button" class="bold">Total (<?php echo esc_html($bctai_total_indexed)?>)</button>
        <button type="button">Re-Index All</button>
        <button type="button" class="red" onclick="window.location.href='<?php echo admin_url('admin.php?page=Embeddings&action=builder&sub=deleteall')?>'">Delete All</button>
    </div>
    <!-- <div class="alignleft actions bulkactions" >
        <a href="<?php echo admin_url('admin.php?page=Embeddings&action=builder&sub=reindexall')?>" class="button button-primary"><?php echo __('Re-Index All','bctai')?></a>
        <button class="button button-primary btn-reindex-builder"><?php echo __('Re-Index Selected','bctai')?></button>
        <a onclick="return confirm('Warning! All indexes will be deleted from Pinecone and elsewhere. Are you sure?')" href="<?php echo admin_url('admin.php?page=Embeddings&action=builder&sub=deleteall')?>" class="button bctai-danger-btn"><?php echo __('Delete Everything','bctai')?></a>
    </div> -->

</div>

<!-- <div class="tablenav top">
    <div class="alignleft actions bulkactions"><?php echo __('Indexed', 'bctai') ?> (<?php echo esc_html($bctai_total_indexed)?>)</div>
</div> -->


<div class="section">
    <div class="sectionContent">
        <div>
            <div class="tableArea">
                <table class="colTable">
                    <colgroup>
                    <col style="width:46px">
                    <col style="width:auto">
                    <col style="width:12%">
                    <col style="width:12%">
                    <col style="width:12%">
                    <col style="width:12%">
                    <col style="width:12%">
                    <col style="width:14%">
                    </colgroup>
        <thead>
            <tr>
                <th class="check-column" scope="col">
                    <div class="check only">
                        <input type="checkbox" class="bctai-select-all">
                        <label for="checkbox11"></label>
                    </div>
                </th>
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

        <tbody>
        <?php
        if($bctai_embeddings->have_posts()) {
            foreach($bctai_embeddings->posts as $bctai_embedding) {
                include __DIR__.'/builder_item.php';
            }
        }
        ?>
        </tbody>
    </table>
    </div>
        </div>
    </div>
</div>


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
