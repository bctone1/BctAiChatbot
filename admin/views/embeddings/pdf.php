<?php
if ( ! defined( 'ABSPATH' ) ) exit;
global $wpdb;
$bctai_embedding_page = isset($_GET['wpage']) && !empty($_GET['wpage']) ? sanitize_text_field($_GET['wpage']) : 1;
$bctai_sub_action = isset($_GET['sub']) && !empty($_GET['sub']) ? sanitize_text_field($_GET['sub']) : false;
if($bctai_sub_action == 'deleteall'){
    $ids = $wpdb->get_results("SELECT ID FROM ".$wpdb->posts." WHERE post_type='bctai_pdfadmin'");
    $ids = wp_list_pluck($ids,'ID');
    if(count($ids)) {
        BCTAI\BCTAI_PDF::get_instance()->bctai_delete_embeddings_ids($ids,'bctai_pdfadmin');
    }
    echo '<script>window.location.href = "'.admin_url('admin.php?page=Embeddings&action=pdf').'";</script>';
    exit;
}
?>

<h2 class="sectionTitle">PDF</h2>
<div class="contentBox" style="margin-bottom: 20px;">
    <div class="columnWrap">
        <div class="columnWrap column2">
            <div class="formTitle"><strong>Select PDF File</strong></div>
            <div class="formContent">
                <div class="fileArea">
                    <input type="file" class="bctai_pdf_file" accept="application/pdf">
                </div>
            </div>
        </div>
    </div>
    
    
    <div class="btnArea">
        <button class="btn btnL bgPrimary bctai_pdf_start"><?php echo esc_html__('Embeddings','bctai')?></button>
        
    </div>

    <div class="bctai_pdf_progress" style="display: none">
        <span></span>
    </div>
    <div class="bctai_pdf_message"></div>

</div>





<?php
$bctai_embeddings = new WP_Query(array(
    'post_type' => 'bctai_pdfadmin',
    'posts_per_page' => 40,
    'paged' => $bctai_embedding_page,
    'order' => 'DESC',
    'orderby' => 'date'
));
?>
<?php
if($bctai_embeddings->have_posts()):
    ?>
    <div class="tablenav top bctai-mb-10">
        <div class="alignleft actions bulkactions">
            <a onclick="return confirm('<?php echo esc_html__('Warning! All indexes will be deleted from Pinecone and elsewhere. Are you sure?','bctai')?>')" href="<?php echo admin_url('admin.php?page=Embeddings&action=pdf&sub=deleteall')?>" class="button bctai-danger-btn"><?php echo esc_html__('Delete Everything','bctai')?></a>
            <button class="button btn-delete-embeddings bctai-danger-btn"><?php echo esc_html__('Delete Selected','bctai')?></button>
        </div>
    </div>
<?php
endif;
?>
<table class="wp-list-table widefat fixed striped table-view-list posts">
    <thead>
    <tr>
        <td id="cb" class="manage-column column-cb check-column" scope="col"><input type="checkbox" class="bctai-select-all"></td>
        <th scope="col"><?php echo esc_html__('Filename','bctai')?></th>
        <th scope="col"><?php echo esc_html__('Token','bctai')?></th>
        <th scope="col"><?php echo esc_html__('Date','bctai')?></th>
        <th scope="col"><?php echo esc_html__('Status','bctai')?></th>
        <th scope="col"><?php echo esc_html__('Action','bctai')?></th>
    </tr>
    </thead>
    <tbody>
    <?php
    if($bctai_embeddings->have_posts()){
        foreach ($bctai_embeddings->posts as $bctai_embedding){
            $bctai_indexed = get_post_meta($bctai_embedding->ID, 'bctai_indexed', true);

            $token = get_post_meta($bctai_embedding->ID,'bctai_embedding_token',true);
            ?>
            <tr id="bctai-builder-<?php echo esc_html($bctai_embedding->ID)?>">
                <th scope="row" class="check-column">
                    <input class="cb-select-embedding" id="cb-select-<?php echo esc_html($bctai_embedding->ID);?>" type="checkbox" name="ids[]" value="<?php echo esc_html($bctai_embedding->ID);?>">
                </th>
                <td><a data-content="<?php echo htmlentities(wp_kses_post($bctai_embedding->post_content),ENT_QUOTES,'UTF-8')?>" href="javascript:void()" class="bctai-embedding-content"><?php echo esc_html($bctai_embedding->post_title)?></a></td>
                <td><?php echo esc_html($token)?></td>
                <td><?php echo esc_html($bctai_embedding->post_date)?></td>
                <td><?php
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
        ?></td>
                <td><button data-id="<?php echo esc_html($bctai_embedding->ID)?>" class="button button-link-delete bctai_delete button-small"><?php echo esc_html__('Delete','bctai')?></button></td>
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
        'base'         => admin_url('admin.php?page=Embeddings&action=pdf&wpage=%#%'),
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
        var bctai_pdf_start = $('.bctai_pdf_start');
        var bctai_pdf_file = $('.bctai_pdf_file');
        var bctai_pdf_message = $('.bctai_pdf_message');
        var bctai_pdf_progress = $('.bctai_pdf_progress');

        function bctaiDoPDFPage(start,filename, contents,callback){
            var content = contents[start];
            var page = start+1;
            //alert("start");
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php')?>',
                type:'POST',
                dataType: 'JSON',
                beforeSend: function(){
                    bctai_pdf_message.html('<?php echo esc_html__('Embedding page: ','bctai')?>'+page);
                },
                data: {
                    nonce: '<?php echo wp_create_nonce('bctai-ajax-action')?>',
                    content: content,
                    action: 'bctai_admin_pdf',
                    page: page,
                    filename: filename
                },
                success: function(res){
                    console.log(res.status);
                    console.log(res);
                    //alert("stop");
                    if(res.status === 'success'){
                        var width = bctai_pdf_progress.width();
                        var readWidth = width*0.1;
                        var leftWidth = width - (width*0.1);
                        var perWidth = leftWidth/contents.length;
                        var progressWidth = readWidth+(perWidth*page);
                        bctai_pdf_progress.find('span').width(progressWidth);
                        if(page === contents.length){
                            callback(res);
                        }
                        else{
                            bctaiDoPDFPage(page,filename,contents,callback);
                        }
                    }
                    else{
                        bctaiRmLoading(bctai_pdf_start);
                        bctai_pdf_file.val('');
                        bctai_pdf_progress.addClass('bctai_error');
                        bctai_pdf_message.html(res.msg);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ajax 요청 실패:', status, error);
                }
            })
        }
        bctai_pdf_start.on('click',async function (){
            //alert("click");
            if(bctai_pdf_file[0].files.length){
                var file = bctai_pdf_file[0].files[0];
                if(file.type === 'application/pdf'){
                    bctaiLoading(bctai_pdf_start);
                    bctai_pdf_message.show();
                    bctai_pdf_progress.show();
                    bctai_pdf_message.html('<?php echo esc_html__('Reading PDF file','bctai')?>');
                    bctai_pdf_progress.removeClass('bctai_error');
                    bctai_pdf_progress.find('span').width('10%');
                    var _OBJECT_URL = URL.createObjectURL(file);
                    var loadingTask = pdfjsLib.getDocument({url: _OBJECT_URL});
                    console.log(loadingTask);
                    var pageContents = [];
                    var pageNumbers = 0;
                    await loadingTask.promise.then(async function (pdf) {
                        pageNumbers = pdf.numPages;
                        for (var i = 1; i <= pageNumbers; i++) {
                            var page = await pdf.getPage(i);
                            var textContent = await page.getTextContent();
                            pageContents.push(textContent.items.map(u => u.str).join("\n"));
                        }
                    });
                    if(pageContents.length) {
                        console.log(file);
                        bctaiDoPDFPage(0,file.name,pageContents,function(res){
                            bctaiRmLoading(bctai_pdf_start);
                            bctai_pdf_file.val('');
                            if(res.status === 'success'){
                                bctai_pdf_message.html('<?php echo esc_html__('Your PDF embedded successfully','bctai')?>')
                                setTimeout(function (){
                                    window.location.reload();
                                },2000)
                            }
                            else{
                                bctai_pdf_progress.addClass('bctai_error');
                                bctai_pdf_message.html(res.msg);
                            }
                        })
                    }
                    else{
                        bctaiRmLoading(bctai_pdf_start);
                        bctai_pdf_file.val('');
                        alert('<?php echo esc_html__('Your PDF file empty','bctai')?>')
                    }
                }
                else{
                    alert('<?php echo esc_html__('Please select PDF file','bctai')?>')
                }
            }
            else{
                alert('<?php echo esc_html__('Please select PDF file before start','bctai')?>')
            }
        });
        $('.bctai_modal_close').click(function () {
            $('.bctai_modal_close').closest('.bctai_modal').hide();
            $('.bctai-overlay').hide();
        })
        $('.bctai-embedding-content').click(function () {
            var content = $(this).attr('data-content');
            content = content.replace(/\n/g, "<br />");
            $('.bctai_modal_title').html('<?php echo esc_html__('Embedding Content', 'bctai')?>');
            $('.bctai_modal_content').html(content);
            $('.bctai-overlay').show();
            $('.bctai_modal').show();
        });
        $('.btn-delete-embeddings').click(function (){
            var conf = confirm('<?php echo esc_html__('Warning! Entries will be deleted from Pinecone and elsewhere. Are you sure?','bctai')?>')
            if(conf) {
                var btn = $(this);
                var ids = [];
                $('.cb-select-embedding:checked').each(function (idx, item) {
                    ids.push($(item).val())
                });
                if (ids.length) {
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php')?>',
                        data: {type: 'bctai_pdfadmin',action: 'bctai_pdfs_delete', ids: ids,'nonce': '<?php echo wp_create_nonce('bctai-ajax-nonce')?>'},
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
                    alert('<?php echo esc_html__('Nothing to do','bctai')?>');
                }
            }
        });
        $(document).on('click','.bctai_delete' ,function (e){
            var btn = $(e.currentTarget);
            var id = btn.attr('data-id');
            var conf = confirm('<?php echo esc_html__('Are you sure?','bctai')?>');
            if(conf){
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php')?>',
                    data: {type: 'bctai_pdfadmin',action: 'bctai_pdfs_delete', ids: [id],'nonce': '<?php echo wp_create_nonce('bctai-ajax-nonce')?>'},
                    dataType: 'JSON',
                    type: 'POST',
                    beforeSend: function (){
                        bctaiLoading(btn);
                    },
                    success: function (res){
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
                        alert('<?php echo esc_html__('Something went wrong','bctai')?>');
                    }
                })
            }
        });

    })
</script>