<?php 
if ( ! defined( 'ABSPATH' ) ) exit;
global $wpdb;


if (isset($_GET['board_id'])) {
    $board_id= $_GET['board_id'];
    echo $board_name;

}



if($board_id){
    $query = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "kboard_board_content
    WHERE board_id =%d;",$board_id);
}else{
    $query = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "kboard_board_content");
    
}
$kbord_contents = $wpdb->get_results($query);

//보드 이름 파악
$kboard_counting_query = $wpdb->prepare("SELECT uid, board_name FROM wp_kboard_board_setting;");
$kbord_boards = $wpdb->get_results($kboard_counting_query);
?>

<select name="" id="board_value">
    <option value="" >all</option>
    <?php 
    if($kbord_boards){
        foreach($kbord_boards as $kbord_board){ ?>
        <option <?php selected($kbord_board->uid, $board_id); ?> value="<?php echo esc_html($kbord_board->uid)?>"><?php echo esc_html($kbord_board->board_name)?></option>
    <?php }} ?>
    
</select>

<div style="margin:10px 0px;">
    <button class="btn btnL bgDarkGray" onclick ="changeboard()">게시판 확인</button>
    <a class="btn btnL bgLightGray" href="javascript:void(0)" class="button button-primary bctai_kboard_embedding"><?php echo esc_html__('Instant Embedding','bctai')?></a>

</div>








<script>
    let home_url = '<?php echo home_url(); ?>';
    function changeboard(){
        var board_value = document.getElementById("board_value").value;
        var newUrl = home_url+"/wp-admin/admin.php?page=Embeddings&action=kboard&board_id=" + encodeURIComponent(board_value);
        window.location.href = newUrl;
        //alert(board_value);

    }



    jQuery(document).ready(function ($){


        function bctaiKboardInstantEmbedding(start,ids) {
                        console.log(ids);
                        let id = ids[start];
                        //alert(id);
                        let nextId = start+1;
                        let embedding = $('#bctai-instant-embedding-'+id);
                        if(bctaiInstantWorking) {
                            bctaiInstantAjax = $.ajax({
                                url: '<?php echo admin_url('admin-ajax.php')?>',
                                data: {
                                    action: 'bctai_kboard_instant_embeddings',
                                    id: id,
                                    'nonce': '<?php echo wp_create_nonce('bctai-ajax-nonce')?>'
                                },
                                type: 'POST',
                                dataType: 'JSON',
                                success: function (res) {
                                    //alert(JSON.stringify(res));
                                    console.log(JSON.stringify(res));
                                    if (res.status === 'success') {
                                        bctaiInstantSuccess += 1;
                                        $('.bctai-embedding-remain').html(bctaiInstantSuccess+'/'+ids.length);
                                        embedding.css({
                                            'background-color': '#cde5dd'
                                        });
                                        embedding.removeClass('bctai-instant-pending');
                                        embedding.find('.bctai-instant-embedding-status').html('Indexed');
                                        embedding.find('.bctai-instant-embedding-status').css({
                                            'font-style': 'normal',
                                            'font-weight': 'bold',
                                            'color': '#008917'
                                        })
                                    } else {
                                        embedding.css({
                                            'background-color': '#e5cdcd'
                                        });
                                        embedding.find('.bctai-instant-embedding-status').html('Error');
                                        embedding.find('.bctai-instant-embedding-status').css({
                                            'font-style': 'normal',
                                            'font-weight': 'bold',
                                            'color': '#e30000'
                                        })
                                        embedding.append('<div style="color: #e30000;font-size: 12px;">' + res.msg + '</div>');
                                    }
                                    //alert(nextId);
                                    //alert(ids.length);
                                    if (nextId < ids.length) {
                                        bctaiKboardInstantEmbedding(nextId, ids);
                                    } else {
                                        //alert('ccc');
                                        $('.bctai_modal_close').show();
                                        $('.bctai-instant-embedding-cancel').hide();
                                    }
                                },
                                error: function () {
                                    //alert('error');
                                    embedding.css({
                                        'background-color': '#e5cdcd'
                                    });
                                    embedding.find('.bctai-instant-embedding-status').html('Error');
                                    embedding.find('.bctai-instant-embedding-status').css({
                                        'font-style': 'normal',
                                        'font-weight': 'bold',
                                        'color': '#e30000'
                                    })
                                    embedding.append('<div style="color: #e30000;font-size: 12px;">Either something went wrong or you cancelled it.</div>');
                                    if (nextId < ids.length) {
                                        bctaiKboardInstantEmbedding(nextId, ids);
                                    } else {
                                        $('.bctai_modal_close').show();
                                        $('.bctai-instant-embedding-cancel').hide();
                                    }
                                }
                            })
                        }
                    }



        $('.bctai_kboard_embedding').click(function(){
            let form = $(this).closest('#posts-filter');
            var btn = $(this);
            var ids = [];
            let titles = {};


            $('.cb-select-embedding:checked').each(function (idx, item) {
                let post_id = $(item).val();
                ids.push(post_id);

                
                // let test = $('#kboard_post-1 .kboard-title');
                // console.log(test.text());


                let post_name = $('#kboard_post-' + post_id + ' .kboard-title'); // 공백 추가
                //alert(post_name.text());


                titles[post_id] = post_name.text();
            });
            // console.log(titles);
            // console.log(ids);


            if (ids.length) {
                let html = '';
                bctaiInstantWorking = true;
                bctaiInstantSuccess = 0;
                $('.bctai_modal_title').html('Instant Embedding<span style="font-weight: bold;font-size: 16px;background: #fba842;padding: 1px 5px;border-radius: 3px;display: inline-block;margin-left: 6px;color: #222;" class="bctai-embedding-remain">0/'+ids.length+'</span>');
                $('.bctai_modal').css({
                    top: '5%',
                    height: '90%'
                })
                $('.bctai_modal_content').css({
                    'max-height': 'calc(100% - 103px)',
                    'overflow-y': 'auto'
                })
                $.each(ids, function(idx, id){
                    html += '<div class="bctai-instant-pending" id="bctai-instant-embedding-'+id+'" style="background: #ebebeb;border-radius: 3px;padding: 5px;margin-bottom: 5px;border: 1px solid #dfdfdf;"><div style="display: flex; justify-content: space-between;"><span>'+titles[id]+'</span><span style="font-style: italic" class="bctai-instant-embedding-status">Indexing...</span></div></div>';
                });
                html += '<div style="text-align: center"><button class="button button-link-delete bctai-instant-embedding-cancel">Cancel</button></div>';
                $('.bctai_modal_content').html(html);
                $('.bctai-overlay').show();
                $('.bctai_modal').show();
                $('.bctai_modal_close').show();
                
                bctaiKboardInstantEmbedding(0,ids);
                


            } else {
                alert('Nothing to do');
            }
        });

        $('.bctai_modal_close').click( function(){
            //alert("click");
            $('.bctai_modal').hide();
            $('.bctai-overlay').hide();
        });

    });
</script>

<form action="" id="posts-filter">
    <table class="wp-list-table widefat fixed striped table-view-list posts">
        <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column" scope="col"><input type="checkbox" class="bctai-select-all"></td> 
                <th><?php echo __('uid', 'bctai') ?></th>
                <th><?php echo __('title', 'bctai') ?></th>
                <th><?php echo __('content', 'bctai') ?></th>
                <th><?php echo __('board_id', 'bctai') ?></th>
                <th><?php echo __('member_uid', 'bctai') ?></th>
                <th><?php echo __('member_display', 'bctai') ?></th>
                <th><?php echo __('date', 'bctai') ?></th>
                
            </tr>
        </thead>

        <tbody class="bctai-builder-list">
        <?php if($kbord_contents) {
            foreach($kbord_contents as $kbord_content) { ?>
            <tr id="kboard_post-<?php echo esc_html($kbord_content->uid)?>">
                
                <th scope="row" class="check-column">
                    <input class="cb-select-embedding" id="cb-select-<?php echo esc_html($kbord_content->uid);?>" type="checkbox" name="ids[]" value="<?php echo esc_html($kbord_content->uid);?>">
                </th>
                <td> <?php echo esc_html($kbord_content->uid)?> </td>
                <td class="kboard-title" vlaue="<?php echo esc_html($kbord_content->title)?>"> <?php echo esc_html($kbord_content->title)?> </td>
                <td> <?php echo esc_html($kbord_content->content)?> </td>
                <td> <?php echo esc_html($kbord_content->board_id)?> </td>
                <td> <?php echo esc_html($kbord_content->member_uid)?> </td>
                <td> <?php echo esc_html($kbord_content->member_display)?> </td>
                <td> <?php echo esc_html($kbord_content->date)?> </td>
            </tr>
        <?php } }?>
        </tbody>
    </table>


</form>




