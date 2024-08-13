<?php
namespace BCTAI;
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('\\BCTAI\BCTAI_Embeddings')) {
    class BCTAI_Embeddings
    {
        private static $instance = null;
        public $bctai_max_file_size = 10485760;

        public static function get_instance()
        {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self(); 
            }
            return self::$instance;
        }

        public function __construct()
        {
            //add_action('admin_menu', array( $this, 'bctai_menu'));
            add_action('wp_ajax_bctai_embeddings', [$this,'bctai_embeddings']);            
            add_action('wp_ajax_bctai_builder_reindex',[$this,'bctai_builder_reindex']);
            add_action('wp_ajax_bctai_builder_delete', [$this,'bctai_builder_delete']);            
            add_action('wp_ajax_bctai_builder_list',[$this,'bctai_builder_list']);
            add_action('wp_ajax_bctai_delete_embeddings',[$this,'bctai_delete_embeddings']);


            $bctai_instant_embedding = get_option('bctai_instant_embedding', 'yes');
            if($bctai_instant_embedding == 'yes') {
                // add_action('manage_kboard_posts_columns',[$this,'bctai_instant_embedding_button']);
                add_action('manage_posts_extra_tablenav',[$this,'bctai_instant_embedding_button']);
                add_action('admin_footer', [$this, 'bctai_instant_embedding_footer']);
                add_action('wp_ajax_bctai_instant_embedding', [$this,'bctai_instant_embedding']);

                add_action('wp_ajax_bctai_kboard_instant_embeddings', [$this,'bctai_kboard_instant_embeddings']);
            }
            /*Pinecone sync Indexes*/
            add_action('wp_ajax_bctai_pinecone_indexes',[$this,'bctai_pinecone_indexes']);

            //delete_all_embeddings_posts
            add_action('wp_ajax_bctai_delete_all_embeddings', array($this, 'bctai_delete_all_embeddings'));

            //질문리스트
            add_action('wp_ajax_set_results_per_page', array($this, 'set_results_per_page'));
            add_action('wp_ajax_reload_items_embeddings', array($this, 'reload_items_embeddings'));
            add_action('wp_ajax_search_question_content', array($this, 'search_question_content'));
        }

        public function search_question_content() {

            // wp_send_json("요청 성공");
            global $wpdb; // Access the global database object
            //check_ajax_referer('gpt4_ajax_pagination_nonce', 'nonce');
        
            $search_term = isset($_POST['search_term']) ? sanitize_text_field($_POST['search_term']) : '';
            $results_per_page = isset($_POST['results_per_page']) ? intval($_POST['results_per_page']) : get_option('bctai_knowledge_builder_page', 3);
            $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
            $offset = ($page - 1) * $results_per_page;
        
            
            $query = $wpdb->prepare(
                "SELECT ID, post_title, post_date, post_type FROM {$wpdb->posts} 
                WHERE post_type IN ('wpaicg_embeddings', 'wpaicg_pdfadmin','wpaicg_builder') AND post_status = 'publish' 
                AND post_content LIKE %s
                ORDER BY post_date DESC 
                LIMIT %d, %d",
                '%' . $wpdb->esc_like($search_term) . '%', $offset, $results_per_page
            );

            // Execute the query
            //$posts = $wpdb->get_results($query);

            $query = $wpdb->prepare("
            Select * from " . $wpdb->prefix . "bctai_question
            where data like %s
            order by id desc
            LIMIT %d OFFSET %d",'%' . $wpdb->esc_like($search_term) . '%',$results_per_page, $offset);


            //받아온 데이터를 저장하는 변수
            $posts = $wpdb->get_results($query);
                
            // Prepare content HTML
            $output = '';
            foreach ($posts as $post) {
                $output .= $this->generate_table_row($post);
            }
        
            // Get total posts for pagination
            // $total_posts_query = $wpdb->prepare(
            //     "SELECT COUNT(*) FROM {$wpdb->posts} 
            //     WHERE post_type IN ('wpaicg_embeddings', 'wpaicg_pdfadmin','wpaicg_builder') AND post_status = 'publish' 
            //     AND post_content LIKE %s",
            //     '%' . $wpdb->esc_like($search_term) . '%'
            // );
            // $total_posts = $wpdb->get_var($total_posts_query);
            // $total_pages = ceil($total_posts / $results_per_page);
        
            
            //$updated_pagination_html = $this->generate_smart_pagination($page, $total_pages);
        
            // Return the filtered results and updated pagination
            wp_send_json_success(array(
                'content' => $output,
                //'pagination' => $updated_pagination_html,
            ));
        }

        public function set_results_per_page() {
            //check_ajax_referer('gpt4_ajax_pagination_nonce', 'nonce');
        
            $results_per_page = isset($_POST['results_per_page']) ? intval($_POST['results_per_page']) : 3;
        
            if (update_option('bctai_knowledge_builder_page', $results_per_page)) {
                wp_send_json_success();
            } else {
                wp_send_json_error(['msg' => esc_html__('Failed to set results per page', 'gpt3-ai-content-generator')]);
            }
        
            die();
        }


        public function generate_smart_pagination($current_page, $total_pages) {
            $html = '<div class="gpt4-pagination">';
            $range = 2; // Adjust as needed. This will show two pages before and after the current page.
            $showEllipses = false;
        
            for ($i = 1; $i <= $total_pages; $i++) {
                // Always show the first page, the last page, and the current page with $range pages on each side.
                if ($i == 1 || $i == $total_pages || ($i >= $current_page - $range && $i <= $current_page + $range)) {
                    $html .= sprintf('<a href="#" data-page="%d">%d</a> ', $i, $i);
                    $showEllipses = true;
                } elseif ($showEllipses) {
                    $html .= '... ';
                    $showEllipses = false;
                }
            }
        
            $html .= '</div>';
            return $html;
        }

        

        public function reload_items_embeddings() {
            global $wpdb;
            
        
            $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
            $results_per_page = isset($_POST['results_per_page']) ? intval($_POST['results_per_page']) : get_option('bctai_knowledge_builder_page', 3);
            $offset = ($page - 1) * $results_per_page;
            $search_term = isset($_POST['search_term']) ? sanitize_text_field($_POST['search_term']) : '';
        
            $query = $wpdb->prepare("
            Select * from " . $wpdb->prefix . "bctai_question
            order by id desc
            LIMIT %d OFFSET %d",$results_per_page, $offset);

            $posts = $wpdb->get_results($query);
    
            $output = '';
            foreach ( $posts as $post ) {
                $output .= $this->generate_table_row($post);
            }

            wp_send_json_success(['content' => $output,]);

            die();
        }




        

        public function generate_table_row($post) {
            $formatted_date = date('Y.m.d H:i', $post->created_at);

        
            return "<tr id='post-row-{$post->id}'>
                        <td class='column-id'>" . esc_html($post->id) . "</td>
                        <td class='column-content'>" . esc_html($post->data) . "</td>
                        <td class='column-details'>" . esc_html($post->page_title) . "</td>
                        <td class='column-source'>" . esc_html($post->source) . "</td>
                        <td class='column-source'>" . esc_html($formatted_date) . "</td>
                        <td class='column-source'>
                            <a href='" . esc_url(admin_url('post-new.php?post_title=' . urlencode($post->data))) . "' class='btn btnXS bgPrimary'>Add Post</a>
                        </td>
                    </tr>";
        }
        



        public function bctai_delete_all_embeddings()
        {

            if (!wp_verify_nonce($_POST['nonce'], 'bctai-ajax-nonce')) {
                wp_send_json_error(['message' => 'Nonce verification failed.']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'You do not have permission for this action.']);
                return;
            }
        
            global $wpdb;
            $ids = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE post_type IN ('bctai_embeddings')");
        
            if (empty($ids)) {
                wp_send_json_error(['message' => 'No posts found to delete.']);
                return;
            }
        
            $this->bctai_delete_embeddings_ids($ids);
            // Clean up postmeta
            $meta_keys = ['bctai_indexed', 'bctai_source', 'bctai_parent', 'bctai_error_msg'];
            foreach ($meta_keys as $meta_key) {
                $wpdb->delete($wpdb->postmeta, ['meta_key' => $meta_key]);
            }
            
            wp_send_json_success(['message' => 'All embeddings have been deleted.']);
        }





        public function bctai_pinecone_indexes()
        {
            if ( ! wp_verify_nonce( $_POST['nonce'], 'bctai-ajax-nonce' ) ) {
                die(BCTAI_NONCE_ERROR);
            }
            
            $indexes = sanitize_text_field(str_replace("\\",'',$_REQUEST['indexes']));
            update_option('bctai_pinecone_indexes',$indexes);
            if(isset($_REQUEST['api_key']) && !empty($_REQUEST['api_key'])){
                update_option('bctai_pinecone_api', sanitize_text_field($_REQUEST['api_key']));
            }
            // if(isset($_REQUEST['server']) && !empty($_REQUEST['server'])){
            //     update_option('bctai_pinecone_sv', sanitize_text_field($_REQUEST['server']));
            // }
        }


        

        public function bctai_builder_reindex()
        {
            $bctai_result = array('status' => 'error', 'msg' => 'Something went wrong16');
            if ( ! wp_verify_nonce( $_POST['nonce'], 'bctai-ajax-nonce' ) ) {
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = BCTAI_NONCE_ERROR;
                wp_send_json($bctai_result);
            }


            if(isset($_POST['id']) && !empty($_POST['id'])){
                $id = sanitize_text_field($_POST['id']);

                $parent_id = get_post_meta($id,'bctai_parent',true);
                
                if($parent_id && get_post($parent_id)){
                    update_post_meta($id,'bctai_indexed','reindex');
                    update_post_meta($parent_id,'bctai_indexed','reindex');
                    $bctai_result['status'] = 'success';
                }
                else {
                    $bctai_result['msg'] = 'Data need convert has been deleted';
                }
            }
            wp_send_json($bctai_result);
        }

        public function bctai_builder_delete()
        {
            // $bctai_result['msg'] = 'a;dflkjasd';
            // return $bctai_result;

            $bctai_result = array('status' => 'error', 'msg' => 'Something went wrong-1');
            if ( ! wp_verify_nonce( $_POST['nonce'], 'bctai-ajax-nonce' ) ) {
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = BCTAI_NONCE_ERROR;
                wp_send_json($bctai_result);
            }
            if(isset($_POST['id']) && !empty($_POST['id'])) {
                $id = sanitize_text_field($_POST['id']);
                $bctai_pinecone_api = get_option('bctai_pinecone_api', '');
                $bctai_pinecone_environment = get_option('bctai_pinecone_environment', '');
                if(empty($bctai_pinecone_api) || empty($bctai_pinecone_environment)) {
                    $bctai_result['msg'] = 'Missing Pinecone API Settings';
                }
                else {
                    $headers = array(
                        'Content-Type'  =>  'application/json',
                        'Api-Key'       =>  $bctai_pinecone_api
                    );
                    //$response = wp_remote_get('https://controller.'.$bctai_pinecone_sv.'.pinecone.io/databases',array(
                    $response = wp_remote_get('https://api.pinecone.io/indexes',array(
                        'headers'       =>  $headers  
                    ));

                    
                    if(is_wp_error($response)) {
                        $bctai_result['msg'] = $response->get_error_message();
                        return $bctai_result;
                    }


                    $response_code = $response['response']['code'];

                    if($response_code !== 200) {
                        $bctai_result['msg'] = $response['body'];
                        return $bctai_result;
                    }

                    
                    $response = wp_remote_request('https://' . $bctai_pinecone_environment . '/vectors/delete?ids='.$id, array(
                        'method'        =>  'DELETE',
                        'headers'       =>  $headers
                    ));
                    if(is_wp_error($response)){
                        $bctai_result['msg'] = $response->get_error_message();
                        
                    }
                    else {
                        wp_delete_post($id);
                        $bctai_result['status'] = 'success';
                    }
                }

            }            
            wp_send_json($bctai_result);
        }


        public function bctai_builder_list()
        {
            global $wpdb;
            $bctai_result = array('status' => 'success', 'msg' => esc_html__('Something went wrong23', 'bctai'));
            if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'bctai-ajax-nonce' ) ) {
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = BCTAI_NONCE_ERROR;
                wp_send_json($bctai_result);
            }
            $bctai_embedding_page = isset($_REQUEST['wpage']) && !empty($_REQUEST['wpage']) ? sanitize_text_field($_REQUEST['wpage']) : 1;



        }


        public function bctai_instant_embedding()
        {
            $bctai_result = array('status' => 'error','msg' => 'Missing ID request');           
            //$bctai_result['status'] = 'success';
            //wp_send_json($bctai_result);
            if(!current_user_can('manage_options')){
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = esc_html__('You do not have permission for this action.','bctai');
                wp_send_json($bctai_result);
            }
            if ( ! wp_verify_nonce( $_POST['nonce'], 'bctai-ajax-nonce' ) ) {
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = BCTAI_NONCE_ERROR;
                wp_send_json($bctai_result);
            }
            if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])){
                $id = sanitize_text_field($_REQUEST['id']);
                $bctai_data = get_post($id);
                //wp_send_json($bctai_data);


                $value = get_post_meta($id, 'information_status', true);

                //wp_send_json($value);
                

                #$bctai_result['status'] = 'success';
                #$bctai_result['msg'] = $bctai_data;
                #wp_send_json($bctai_result);  
                
                //wp_send_json($bctai_data);

                if($bctai_data) {
                    $result = $this->bctai_builder_data($bctai_data,$value);
                    if($result == 'success'){
                        $bctai_result['status'] = 'success';
                        $bctai_result['msg'] = 'embedding_complete';
                    }
                    else {
                        $bctai_result['msg'] = $result;
                    }
                }
                else {
                    $bctai_result['msg'] = 'Data not found';
                }

            }
            wp_send_json($bctai_result);
        }

        public function bctai_kboard_instant_embeddings()
        {
            global $wpdb;
            // wp_send_json($bctai_result);
            $bctai_result = array('status' => 'error','msg' => 'Missing ID request');           
            if(!current_user_can('manage_options')){
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = esc_html__('You do not have permission for this action.','bctai');
                wp_send_json($bctai_result);
            }
            if ( ! wp_verify_nonce( $_POST['nonce'], 'bctai-ajax-nonce' ) ) {
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = BCTAI_NONCE_ERROR;
                wp_send_json($bctai_result);
            }

            if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])){


                $id = sanitize_text_field($_REQUEST['id']);
                
                $query = $wpdb->prepare("SELECT ID FROM " . $wpdb->prefix . "posts
                WHERE post_type ='kboard' AND post_name=%d;",$id);
                $kbord_post_id = $wpdb->get_var($query);
                if($kbord_post_id){
                    $bctai_data = get_post($kbord_post_id);
                }else{
                    $bctai_result['status'] = 'error';
                    $bctai_result['msg'] = esc_html__('missing kboard posts','bctai');
                    wp_send_json($bctai_result);
                }
                //wp_send_json($bctai_data);


                if($bctai_data) {
                    $result = $this->bctai_builder_data($bctai_data);
                    if($result == 'success'){
                        $bctai_result['status'] = 'success';
                        $bctai_result['msg'] = 'embedding_complete';
                    }
                    else {
                        $bctai_result['msg'] = $result;
                    }
                }


                
                else {
                    $bctai_result['msg'] = 'Data not found';
                }

            }
            wp_send_json($bctai_result);
        }



        public function bctai_instant_embedding_footer()
        {
            ?>
            <script>
                jQuery(document).ready(function ($){
                    let bctaiInstantAjax = false;
                    let bctaiInstantWorking = true;
                    let bctaiInstantSuccess = 0;                    
                    $(document).on('click', '.bctai-instant-embedding-cancel', function (){
                        bctaiInstantWorking = false;
                        if(bctaiInstantAjax) {
                            bctaiInstantAjax.abort();
                        }
                        let pendings = $('.bctai-instant-pending');
                        pendings.find('.bctai-instant-embedding-status').html('Cancelled');
                        pendings.find('.bctai-instant-embedding-status').css({
                            'font-style': 'normal',
                            'font-weight': 'bold',
                            'color': '#e30000'
                        })
                        $('.bctai_modal_close').show();
                        $('.bctai-instant-embedding-cancel').hide();
                    });
                    function bctaiInstantEmbedding(start,ids) {
                        //alert(ids);
                        let id = ids[start];
                        //alert(id);
                        let nextId = start+1;
                        let embedding = $('#bctai-instant-embedding-'+id);
                        if(bctaiInstantWorking) {
                            bctaiInstantAjax = $.ajax({
                                url: '<?php echo admin_url('admin-ajax.php')?>',
                                data: {action: 'bctai_instant_embedding', id: id,'nonce': '<?php echo wp_create_nonce('bctai-ajax-nonce')?>'},
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
                                        bctaiInstantEmbedding(nextId, ids);
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
                                        bctaiInstantEmbedding(nextId, ids);
                                    } else {
                                        $('.bctai_modal_close').show();
                                        $('.bctai-instant-embedding-cancel').hide();
                                    }
                                }
                            })
                        }
                    }
                    $('.bctai-instan-embedding-btn').click(function (){
                        let form = $(this).closest('#posts-filter');
                        let ids = [];
                        let titles = {};
                        form.find('.wp-list-table th.check-column input[type=checkbox]:checked').each(function (idx, item){
                            let post_id = $(item).val();
                            ids.push(post_id);


                            let row = form.find('#post-'+post_id);
                            let post_name = row.find('.column-title .row-title').text();
                            //console.log(post_name);
                            if(post_name === ''){
                                post_name = row.find('.column-name .row-title').text();
                            }
                            titles[post_id] = post_name.trim();
                        });
                        console.log(ids);
                        if(ids.length === 0) {
                            alert('Please select data to index');
                        }
                        else {
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
                            $('.bctai_modal_close').hide();
                            //console.log(ids);
                            bctaiInstantEmbedding(0,ids);
                        }
                    })
                    $('.bctai_modal_close').click( function(){
                        //alert("click");
                        $('.bctai_modal').hide();
                        $('.bctai-overlay').hide();
                    });
                })
            </script>
            <?php
        }

        public function bctai_instant_embedding_button($which)
        {
            global $post_type;
            $post_types = array('post', 'page', 'product');

            $bctai_all_post_types = get_post_types(array(
                'public' => true,
                '_builtin' => false,
            ), 'array');
            
            $post_types = wp_parse_args($post_types, array_keys($bctai_all_post_types));
            if(in_array($post_type,$post_types)):
                //if(current_user_can('bctai_instant_embedding')):
            ?>
                <div class="alignleft actions">
                    <a style="height: 32px; color: #8040ad; border: 1px solid #8040ad; background: #f6f7f7;" href="javascript:void(0)" class="button button-primary bctai-instan-embedding-btn"><?php echo esc_html__('Instant Embedding','bctai')?></a>
                </div>
            <?php
                //endif;
            endif;
        }


        public function bctai_delete_embeddings()
        {
            $bctai_result = array('status' => 'success');
            if ( ! wp_verify_nonce( $_POST['nonce'], 'bctai-ajax-nonce' ) ) {
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = BCTAI_NONCE_ERROR;
                // $bctai_result['msg'] = "BCTAI_NONCE_ERROR";
                wp_send_json($bctai_result);
            }

            $ids = bctai_util_core()->sanitize_text_or_array_field($_REQUEST['ids']);
            $this->bctai_delete_embeddings_ids($ids);
            wp_send_json($bctai_result);
        }

        public function bctai_delete_embeddings_ids($ids)
        {
            global $wpdb;
            $bctai_pinecone_api = get_option('bctai_pinecone_api','');
            $bctai_pinecone_environment = get_option('bctai_pinecone_environment','');
            $index_host_url = 'https://' . $bctai_pinecone_environment . '/vectors/delete';

            if (empty($bctai_pinecone_api) || empty($bctai_pinecone_environment)) {
                return esc_html__('Missing Pinecone API Settings', 'bctai');
            } else {
                $headers = [
                    'Content-Type' => 'application/json',
                    'Api-Key' => $bctai_pinecone_api
                ];
                $body = json_encode([
                    'deleteAll' => 'true',
                    //'ids' => ['id-1','id-2']
                    'namespace' => 'Default'
                ]);
                $response = wp_remote_post($index_host_url, [
                    'headers' => $headers,
                    'body' => $body
                ]);
            }

            foreach ($ids as $id){
                wp_delete_post($id);
            }
        }


        public function bctai_custom_post_type($content, $post) 
        {            
            if(!in_array($post->post_type, array('post','page','product'))) {                
                $bctai_custom_post_fields = get_option('bctai_builder_custom_'.$post->post_type,'');                
                $new_content = '';              
                if(!empty($bctai_custom_post_fields)){                    
                    $exs = explode('||',$bctai_custom_post_fields);
                    foreach($exs as $ex){
                        $item = explode('##',$ex);
                        if($item && is_array($item) && count($item) == 2) {
                            $key = $item[0];    // custom post types ex) legalprotech
                            $name = $item[1];                            
                            /*Check is standard field*/
                            if(substr($key,0,7) == 'bctaip_'){
                                $post_key = str_replace('bctaip_','',$key);
                                if($post_key == 'post_content'){
                                    $post_value = $content;
                                }
                                elseif($post_key == 'post_date'){
                                    $post_value = get_the_date('', $post->ID);
                                }
                                elseif($post_key == 'post_parent'){
                                    $post_value = get_the_title($post->post_parent);
                                }
                                elseif($post_key == 'permalink'){
                                    $post_value = get_permalink($post->ID);
                                }
                                else{
                                    $post_value = $post->$post_key;
                                }
                                $new_content .= (empty($new_content) ? '': "\n"). $name.': '.$post_value;
                            }
                            /*Check if Custom Meta*/
                            if(substr($key,0,8) == 'bctaicf_'){
                                $meta_key = str_replace('bctaicf_','',$key);
                                $meta_value = get_post_meta($post->ID,$meta_key,true);
                                $meta_value = apply_filters('bctai_meta_value_embedding',$meta_value,$post,$meta_key);
                                if(is_array($meta_value)){
                                    $meta_value = $this->bctai_print_array($meta_value);
                                }
                                $new_content .= (empty($new_content) ? '': "\n"). $name.': '.$meta_value;
                            }
                            /*Check if is author fields*/
                            if(substr($key,0,12) == 'bctaiauthor_'){
                                $user_key = str_replace('bctaiauthor_','',$key);
                                $author = get_user_by('ID',$post->post_author);
                                $new_content .= (empty($new_content) ? '': "\n"). $name.': '.$author->$user_key;
                            }
                            /*Check Taxonomies*/
                            if(substr($key,0,8) == 'bctaitx_'){
                                $taxonomy = str_replace('bctaitx_','',$key);
                                $terms = get_the_terms($post->ID,$taxonomy);
                                if(!is_wp_error($terms)){
                                    $terms_string = join(', ', wp_list_pluck($terms, 'name'));
                                    if(!empty($terms_string)){
                                        $new_content .= (empty($new_content) ? '': "\n"). $name.': '.$terms_string;
                                    }
                                }
                            }
                        }
                    }
                    if(empty($new_content)){
                        $new_content .= esc_html__('Post Title','bctai').': '.$post->post_title;
                        $new_content .= "\n".esc_html__('Post Content','bctai').': '.$content;
                    }
                }
                else {
                    $new_content .= 'Post Title: '.$post->post_title;
                    $new_content .= "\n".'Post Content: '.$content;    
                }
                $content = $new_content;
            }
            return $content;
        }


        public function bctai_print_array($arr, $pad = 0, $padStr = "\t")
        {
            $outerPad = $pad;
            $innerPad = $pad + 1;
            $out = '[';
            foreach ($arr as $k => $v) {
                if (is_array($v)) {
                    $out .= str_repeat($padStr, $innerPad) . $k . ': ' . $this->bctai_print_array($v, $innerPad);
                } else {
                    $out .= str_repeat($padStr, $innerPad) . $k . ': ' . $v;
                }
            }
            $out .= str_repeat($padStr, $outerPad) . ']';
            return $out;
        }
       

        public function bctai_builder_data($bctai_data,$value)
        {            

            global $wpdb;
            $bctai_content = $bctai_data->post_content;   
            // return $bctai_content;
            $bctai_title = $bctai_data->post_title;
            preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $bctai_content, $matches);
            if ($matches && is_array($matches) && count($matches)) {
                $pattern = get_shortcode_regex($matches[1]);
                $bctai_content = preg_replace_callback("/$pattern/", 'strip_shortcode_tag', $bctai_content);
            }
            $bctai_content = trim($bctai_content);
            $bctai_content = preg_replace("/<((?:style)).*>.*<\/style>/si", ' ',$bctai_content);
            $bctai_content = preg_replace("/<((?:script)).*>.*<\/script>/si", ' ',$bctai_content);
            $bctai_content = preg_replace('/<a(.*)href="([^"]*)"(.*)>(.*?)<\/a>/i', '$2', $bctai_content);
            $bctai_content = wp_strip_all_tags($bctai_content);
            $bctai_content = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $bctai_content);
            $bctai_content = trim($bctai_content);
            // return  $bctai_content;

            $bctai_link_url = $bctai_data->post_content;
            $bctai_link_url = trim($bctai_link_url);

            preg_match('/<a href="([^"]*)"[^>]*>(.*?)<\/a>/', $bctai_link_url, $matches);

            if (!empty($matches)) {
                $bctai_link_url = $matches[0]; // 전체 <a> 태그를 포함한 부분을 $bctai_link_url에 저장
            } else {
                $bctai_link_url = ''; // 매치되는 <a> 태그가 없을 경우 빈 문자열
            }

            //return $bctai_link_url;

            if( $bctai_data->post_type == 'legalprotech' ) {
                $bctai_content = $bctai_data->post_title;
            }
            // if (empty($bctai_content))
            //     {
            //     update_post_meta($bctai_data->ID, 'bctai_indexed', 'skip');
            //     return 'Empty content or probably a shortcode'; 
            //     }else
            if($bctai_data)
                {
                /*Check If is Re-Index*/                
                $check = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->postmeta . " WHERE meta_key='bctai_parent' AND meta_value=%d",$bctai_data->ID));
                $bctai_old_builder = false;
                if ($check) {
                    $bctai_old_builder = $check->post_id;
                }
                /*Check if old index exist*/
                $bctai_old_index_builder = get_post($check->post_id);
                if(!$bctai_old_index_builder) {
                    $bctai_old_builder = false;
                }
                /*For Post*/
                if($bctai_data->post_type == 'post') {
                    $bctai_new_content = esc_html__('Post Title').': '.$bctai_data->post_title."\n";
                    $bctai_new_content .= esc_html__('Post Content').': '.$bctai_content."\n";
                    $bctai_new_content .= esc_html__('Post URL').': '.get_permalink($bctai_data->ID)."\n";
                    $image_url = get_the_post_thumbnail_url($bctai_data->ID);
                    if($image_url){
                        $bctai_new_content .= esc_html__('Img URL','bctai').': '.$image_url;
                    }
                    //$bctai_new_content .= esc_html__('Infomation Status').': '.$bctai_acf_select."\n";
                    



                    /*Categories*/
                    $categories_name = wp_get_post_categories($bctai_data->ID, array('fields' => 'names'));
                    if($categories_name && is_array($categories_name) && count($categories_name)){
                        $bctai_new_content .= "\n".esc_html__('Post Categories','bctai').": ".implode(',',$categories_name);
                    }
                    $bctai_content = $bctai_new_content;
                }
                /*For Page*/
                if($bctai_data->post_type == 'page') {
                    $bctai_new_content = esc_html__('Page Title','bctai').': '.$bctai_data->post_title."\n";
                    $bctai_new_content .= esc_html__('Page Content','bctai').': '.$bctai_content."\n";
                    $bctai_new_content .= esc_html__('Page URL','bctai').': '.get_permalink($bctai_data->ID);
                    $bctai_content = $bctai_new_content;
                }
                /*For Product*/
                if($bctai_data->post_type == 'product' && class_exists('WC_Product_Factory')){
                    $wooFac = new \WC_Product_Factory();
                    $bctai_product = $wooFac->get_product($bctai_data->ID);
                    if($bctai_product) {
                        $bctai_content_product = '';
                        $product_sku = $bctai_product->get_sku();
                        if (!empty($product_sku)) {
                            $bctai_content_product .= esc_html__('Product SKU', 'bctai').': ' . $product_sku . "\n";
                        }
                        $product_title = $bctai_product->get_title();
                        $bctai_content_product .= esc_html__('Product Name', 'bctai').': ' . $product_title . "\n";
                        if(!empty($bctai_content)){
                            $bctai_content_product .= esc_html__('Product Description','bctai').': ' . $bctai_content . "\n";
                        }
                        if(!empty($bctai_data->post_excerpt)){
                            $bctai_content_product .= esc_html__('Product Short Description', 'bctai').': ' . $bctai_data->post_excerpt . "\n";
                        }
                        $product_url = $bctai_product->get_permalink();
                        $bctai_content_product .= esc_html__('Product URL', 'bctai').': ' . $product_url . "\n";
                        $product_regular_price = $bctai_product->get_regular_price();
                        if (!empty($product_regular_price)) {
                            $bctai_content_product .= esc_html__('Product Regular Price', 'bctai').": " . $product_regular_price.' '.get_option('woocommerce_currency','USD') . "\n";
                        }
                        $product_sale_price = $bctai_product->get_sale_price();
                        if (!empty($product_sale_price)) {
                            $bctai_content_product .= esc_html__('Product Sale Price', 'bctai').': ' . $product_sale_price.' '.get_option('woocommerce_currency','USD') . "\n";
                        }
                        $product_tax_status = $bctai_product->get_tax_status();
                        if (!empty($product_tax_status)) {
                            $bctai_content_product .= esc_html__('Tax Status', 'bctai').': ' . $product_tax_status . "\n";
                        }
                        $product_tax_class = $bctai_product->get_tax_class();
                        if (!empty($product_tax_class)) {
                            $bctai_content_product .= esc_html__('Tax Class', 'bctai').': ' . $product_tax_class . "\n";
                        }
                        $product_external_url = '';
                        if ($bctai_product->get_type() == 'external') {
                            $product_external_url = $product_url;
                        }
                        if (!empty($product_external_url)) {
                            $bctai_content_product .= esc_html__('External Product URL', 'bctai').': ' . $product_tax_class . "\n";        
                        }
                        $product_shipping_weight = $bctai_product->get_weight();
                        if (!empty($product_shipping_weight)) {
                            $bctai_content_product .= esc_html__('Shipping Weight', 'bctai').': ' . $product_shipping_weight .' '.get_option('woocommerce_weight_unit','oz'). "\n";
                        }
                        $product_dimensions = '';
                        if (!empty($bctai_product->get_length()) || !empty($bctai_product->get_width()) || !empty($bctai_product->get_height())) {
                            $dimension_unit = get_option('woocommerce_dimension_unit','cm');
                            $product_dimensions = $bctai_product->get_length() .$dimension_unit. ', ' . $bctai_product->get_width().$dimension_unit . ', ' . $bctai_product->get_height().$dimension_unit;
                        }
                        if (!empty($product_dimensions)) {
                            $bctai_content_product .= esc_html__('Dimensions', 'bctai').': ' . $product_dimensions . "\n";
                        }
                        $product_stock_status = $bctai_product->get_stock_status();
                        $stock_status_options = wc_get_product_stock_status_options();
                        if(isset($stock_status_options[$product_stock_status]) && !empty($stock_status_options[$product_stock_status])){
                            $bctai_content_product .= esc_html__('Stock Status', 'bctai').': '.$stock_status_options[$product_stock_status]."\n";
                        }
                        $product_attributes = $bctai_product->get_attributes();
                        if ($product_attributes && is_array($product_attributes) && count($product_attributes)) {
                            $bctai_content_product .= esc_html__('Custom Product Attributes', 'bctai').': ';
                            foreach ($product_attributes as $keyx => $att) {
                                $options = $att->get_options();
                                $bctai_content_product .= $att->get_name() . ': ';
                                foreach ($options as $key => $option) {
                                    $bctai_content_product .= $key == 0 ? $option : ',' . $option;
                                }
                                if ($key + 1 == count($options)) {
                                    $bctai_content_product .= '; ';
                                }
                            }
                            $bctai_content_product .= "\n";
                        }
                        $bctai_content = $bctai_content_product;
                    }
                }

                // ACF가 활성화되어 있다면 코드 실행
                if (function_exists('get_field_objects')) {
                    $acf_fields = get_field_objects($bctai_data->ID);
                    if($acf_fields){
                        foreach ($acf_fields as $field_name => $field_data) {
                            if (isset($field_data['value'])) {
                                $combined_values .= $field_name.':'.$field_data['value'] ."\n";
                            }
                        }
                        $bctai_content = $bctai_content.$combined_values;
                    }
                }
                //return $bctai_content;//이부분

                /*For custom post type*/
                if($bctai_data->post_type == 'legalprotech') {
                    $bctai_content = '';
                    $bctai_content_legalprotech = array();

                    foreach( (array) get_post_meta( $bctai_data->ID ) as $k => $v ) {
                        $bctai_content_legalprotech[$k] = $v;                        
                    }

                    $bctai_content = 'Country : ' . implode('', $bctai_content_legalprotech['country'])."\n";
                    $bctai_content .= 'Company Name : ' . implode('', $bctai_content_legalprotech['company_name'])."\n";
                    $bctai_content .= 'Company Address : ' . implode('', $bctai_content_legalprotech['company_address'])."\n";
                    $bctai_content .= 'Zip Code : ' . implode('', $bctai_content_legalprotech['zip_code'])."\n";
                    $bctai_content .= 'Industry Group : ' . implode('', $bctai_content_legalprotech['industry_group'])."\n";
                    $bctai_content .= 'Featured Products : ' . implode('', $bctai_content_legalprotech['featured_products'])."\n";
                    $bctai_content .= 'Phone : ' . implode('', $bctai_content_legalprotech['phone'])."\n";
                    $bctai_content .= 'Tel : ' . implode('', $bctai_content_legalprotech['tel'])."\n";
                    $bctai_content .= 'Fax : ' . implode('', $bctai_content_legalprotech['fax'])."\n";
                    $bctai_content .= 'Website : ' . implode('', $bctai_content_legalprotech['website'])."\n";
                    $bctai_content .= 'Email : ' . implode('', $bctai_content_legalprotech['email'])."\n";
                    $bctai_content .= 'Recent News : ' . implode('', $bctai_content_legalprotech['recent_news'])."\n";
                    $bctai_content .= 'Equity Relationship : ' . implode('', $bctai_content_legalprotech['equity_relationship'])."\n";
                    $bctai_content .= 'Stock Related Information : ' . implode('', $bctai_content_legalprotech['stock_related_information'])."\n";
                    $bctai_content .= 'Business Registration : ' . implode('', $bctai_content_legalprotech['business_registration'])."\n";
                    $bctai_content .= 'Listing Date : ' . implode('', $bctai_content_legalprotech['listing_date'])."\n";

                    $bctai_content = apply_filters('bctai_embedding_content_custom_post_type',$bctai_content,$bctai_data);

                }
                else {
                    $bctai_content = $this->bctai_custom_post_type($bctai_content,$bctai_data);   
                    //$bctai_result['msg'] = $bctai_content;
                    //return $bctai_result['msg'];
                    $bctai_content = apply_filters('bctai_embedding_content_custom_post_type',$bctai_content,$bctai_data);
                    //$bctai_result['msg'] = json_encode($bctai_content);
                    //return $bctai_result['msg'];
                }
                /*End for custom post_type*/
                
                //$bctai_result = $this->bctai_save_embedding($bctai_content, 'bctai_builder', $bctai_data->post_title, $bctai_old_builder,$value);
                
                $bctai_result = $this->bctai_save_embedding($bctai_title, 'bctai_builder', $bctai_data->post_title, $bctai_old_builder,$value,$bctai_link_url);
                // return $bctai_result;

                if ($bctai_result && is_array($bctai_result) && isset($bctai_result['status'])) {
                    if ($bctai_result['status'] == 'error') {
                        /**
                         * If save embedding error
                         */
                        if ($bctai_old_builder) {
                            $embedding_id = $bctai_old_builder;
                        } else {

                            //$bctai_result['msg'] = 'qqqqqqqq';
                            //return $bctai_result['msg'];

                            $embedding_data = array(
                                'post_type' => 'bctai_builder',
                                'post_title' => $bctai_data->post_title,
                                'post_content' => $bctai_content,
                                'post_status' => 'publish'
                            );
                            $embedding_id = wp_insert_post($embedding_data);
                        }
                        
                        update_post_meta($bctai_data->ID, 'bctai_indexed', 'error');
                        update_post_meta($embedding_id, 'bctai_indexed', 'error');
                        update_post_meta($embedding_id, 'bctai_source', $bctai_data->post_type);
                        update_post_meta($embedding_id, 'bctai_parent', $bctai_data->ID);
                        update_post_meta($embedding_id, 'bctai_error_msg', $bctai_result['msg']);
                        //update_post_meta($embedding_id, 'information_status', $value);
                        return $bctai_result['msg'];
                    } else {
                        wp_update_post(array(
                            'ID' => $bctai_result['id'],
                            'post_content' => $bctai_content
                        ));      
                                          
                        update_post_meta($bctai_data->ID, 'bctai_indexed', 'yes');
                        update_post_meta($bctai_result['id'], 'bctai_indexed', 'yes');
                        update_post_meta($bctai_result['id'], 'bctai_source', $bctai_data->post_type);
                        update_post_meta($bctai_result['id'], 'bctai_parent', $bctai_data->ID);
                        return 'success';
                    }
                } else {
                    if ($bctai_old_builder) {
                        $embedding_id = $bctai_old_builder;
                    } else {
                        //$bctai_result['msg'] = $bctai_data->post_title;
                        //return $bctai_result['msg'];
                        $embedding_data = array(
                            'post_type' => 'bctai_builder',
                            'post_title' => $bctai_data->post_title,
                            'post_content' => $bctai_content,
                            'post_status' => 'publish'
                        );
                        $embedding_id = wp_insert_post($embedding_data);
                    }
                    update_post_meta($embedding_id, 'bctai_source', $bctai_data->post_type);
                    update_post_meta($embedding_id, 'bctai_parent', $bctai_data->ID);
                    update_post_meta($bctai_data->ID, 'bctai_indexed', 'error');
                    update_post_meta($embedding_id, 'bctai_indexed', 'error');
                    update_post_meta($embedding_id, 'bctai_error_msg', 'Something went wrong31');
                    return 'Something went wrong22';
                }
            }
        }

        public function bctai_save_embedding($content, $post_type = '', $title = '', $embeddings_id = false,$value,$bctai_link_url)
        {
            //return $content;

            global $wpdb;
            $bctai_result = array('status' => 'error', 'msg' => 'Something went wrong34');
            $openai = BCTAI_OpenAI::get_instance()->openai();




            $content = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $content);
            if($openai) {




                $bctai_pinecone_api = get_option('bctai_pinecone_api','');
                $bctai_pinecone_environment = get_option('bctai_pinecone_environment','');
                if(empty($bctai_pinecone_api) || empty($bctai_pinecone_environment)) {
                    $bctai_result['msg'] = 'Missing Pinecone API Settings';
                }
                else {
                                 






                    $response = $openai->embeddings(array(
                        'input' =>  $content,
                        'model' => 'text-embedding-3-small'
                    ));
                    $response = json_decode($response,true);
                    if(isset($response['error']) && !empty($response['error'])) {
                        $bctai_result['msg'] = $response['error']['message'];
                        if(empty($bctai_result['msg']) && isset($response['error']['code']) && $response['error']['code'] == 'invalid_api_key'){
                            $bctai_result['msg'] = 'Incorrect API key provided. You can find your API key at https://platform.openai.com/account/api-keys.';
                        }
                    }

                    else {
                        $embedding = $response['data'][0]['embedding'];
                        if(empty($embedding)) {
                            $bctai_result['msg'] = 'No data returned';
                        }
                        else {



                            if(!$embeddings_id) {
                                $embedding_title = empty($title) ? mb_substr($content, 0, 50, 'utf-8') : $title;
                                if(strpos($embedding_title, 'Question') !== false) {
                                    $embedding_title_temp = explode('Category1', $embedding_title);
                                    $embedding_title = $embedding_title_temp[0];
                                }                                                               
                                $embedding_data = array(
                                    'post_type' => 'bctai_embeddings',
                                    'post_title' => $embedding_title,
                                    'post_content' => $content,
                                    'post_status' => 'publish'
                                );
                                if(!empty($post_type)) {
                                    $embedding_data['post_type'] = $post_type;
                                }
                                $embeddings_id = wp_insert_post($embedding_data);

                                //return $embeddings_id;

                                if(is_wp_error($embeddings_id)) {
                                    $bctai_result['msg'] = $embeddings_id->get_error_message();
                                    $bctai_result['status'] = 'error';
                                    return $bctai_result;
                                }

                                
                                if(isset($_REQUEST['type']) && !empty($_REQUEST['type'])){
                                    add_post_meta($embeddings_id,'bctai_embedding_type',sanitize_text_field($_REQUEST['type']));
                                }
                            }




                            if(is_wp_error($embeddings_id)){
                                $bctai_result['msg'] = $embeddings_id->get_error_message();
                            }
                            else {
                                update_post_meta($embeddings_id,'bctai_start',time());
                                update_post_meta($embeddings_id, 'information_status', $value);
                                if($bctai_link_url){
                                    update_post_meta($embeddings_id, 'bctai_link_url', $bctai_link_url);
                                }
                                $usage_tokens = $response['usage']['total_tokens'];
                                add_post_meta($embeddings_id, 'bctai_embedding_token', $usage_tokens);


                                $bctai_vector_db_provider = get_option('bctai_vector_db_provider', 'pinecone');

                                //Qdrant
                                if($bctai_vector_db_provider === 'qdrant'){
                                    

                                    $bctai_qdrant_endpoint = rtrim(get_option('bctai_qdrant_endpoint', ''), '/') . '/collections';
                                    $default_qdrant_collection = get_option('wpaicg_qdrant_default_collection', '');
                                    $qdrant_url = $bctai_qdrant_endpoint . '/' . $default_qdrant_collection . '/points?wait=true';
                                    $bctai_qdrant_api = get_option('bctai_qdrant_api', '');
                                    $group_id = 'default';

                                    //return $embeddings_id;

                                    $formatted_vector = array(
                                        'id' => (int)$embeddings_id,
                                        'vector' => $embedding,
                                        'payload' => array('group_id' => $group_id)
                                    );
    
                                    $vectors = array('points' => array($formatted_vector));
                                    
                                    $response = wp_remote_request($qdrant_url, array(
                                        'method'    => 'PUT',
                                        'headers' => ['api-key' => $bctai_qdrant_api, 'Content-Type' => 'application/json'],
                                        'body'      => json_encode($vectors)
                                    ));
                                    // return $response;

                                    if(is_wp_error($response)){
                                        $bctai_result['msg'] = $response->get_error_message();
                                        wp_delete_post($embeddings_id);
                                        $wpdb->delete($wpdb->postmeta, array(
                                            'meta_value' => $embeddings_id,
                                            'meta_key' => 'bctai_parent'
                                        ));
                                    }
                                    else{
                                        $body = json_decode($response['body'],true);
                                        // return $body;
                                        if($body){
                                            if ($body['status'] === 'ok') {
                                                $bctai_result['status'] = 'success';
                                                // return 123123123;
                                                $bctai_result['id'] = $embeddings_id;
                                                update_post_meta($embeddings_id,'bctai_completed',time());
                                            }
                                            else{
                                                $error_message = 'Unknown error occurred';
                                                if (isset($body['error'])) {
                                                    $error_message = $body['error'];
                                                } elseif (isset($body['status']['error'])) {
                                                    $error_message = $body['status']['error'];
                                                }
    
                                                // Set the error message in the result array
                                                $bctai_result['msg'] = "Response from API: " . $error_message;
                                                wp_delete_post($embeddings_id);
                                                $wpdb->delete($wpdb->postmeta, array(
                                                    'meta_value' => $embeddings_id,
                                                    'meta_key' => 'bctai_parent'
                                                ));
                                            }
                                        }
                                        else{
                                            $bctai_result['msg'] = esc_html__('No data returned','gpt3-ai-content-generator');
                                            wp_delete_post($embeddings_id);
                                            $wpdb->delete($wpdb->postmeta, array(
                                                'meta_value' => $embeddings_id,
                                                'meta_key' => 'bctai_parent'
                                            ));
                                        }
                                    }
                                }else{
                                    //Pinecone
                                    $headers = array(
                                        'Content-Type' => 'application/json',
                                        'Api-Key' => $bctai_pinecone_api
                                    );
                                    $response = wp_remote_get('https://api.pinecone.io/indexes',array(
                                        'headers'   =>  $headers
                                    ));
                                    if(is_wp_error($response)) {
                                        $bctai_result['msg'] = $response->get_error_message();
                                        return $bctai_result;
                                    }
                                    $response_code = $response['response']['code'];                    
                                    if($response_code !== 200){
                                        $bctai_result['msg'] = $response['response']['message'];
                                        return $bctai_result;
                                    } 

                                    $pinecone_url = 'https://' . $bctai_pinecone_environment . '/vectors/upsert'; 

                                    $vectors = array(
                                        array(
                                            'id'    => (string)$embeddings_id,
                                            'values'    => $embedding
                                        )
                                    );
                                    $response = wp_remote_post($pinecone_url, array(
                                        'headers'   => $headers,
                                        'body' => wp_json_encode(array('vectors' => $vectors))
                                    ));
    
                                    if(is_wp_error($response)){
                                        $bctai_result['msg'] = $response->get_error_message();
                                        wp_delete_post($embeddings_id);
                                        $wpdb->delete($wpdb->postmeta, array(
                                            'meta_value' => $embeddings_id,
                                            'meta_key' => 'bctai_parent'
                                        ));
                                    }
                                    else {
                                        $body = json_decode($response['body'],true);
                                        if($body){
                                            if(isset($body['code']) && isset($body['message'])){    
                                                $bctai_result['msg'] = wp_strip_all_tags($body['message']);
                                                wp_delete_post($embeddings_id);
                                                $wpdb->delete($wpdb->postmeta, array(
                                                    'meta_value' => $embeddings_id,
                                                    'meta_key' => 'bctai_parent'
                                                ));
                                            }
                                            else {
                                                $bctai_result['status'] = 'success';
                                                $bctai_result['id'] = $embeddings_id;
                                                update_post_meta($embeddings_id,'bctai_completed',time());
                                            }
                                        }
                                        else {
                                            $bctai_result['msg'] = 'No data returned';
                                            wp_delete_post($embeddings_id);
                                            $wpdb->delete($wpdb->postmeta, array(
                                                'meta_value' => $embeddings_id,
                                                'meta_key' => 'bctai_parent'
                                            ));
                                        }
                                    }

                                }

                            }
                        }
                    }                    
                }
            }
            else {
                $bctai_result['msg'] = 'Missing OpenAI API Settings';
            }

            return $bctai_result;
        }

        public function bctai_embeddings()
        {            
            $bctai_result = array('status' => 'error', 'msg' => 'Something went wrong');
            if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'bctai_embeddings_save' ) ) {
                $bctai_result['status'] = 'error';
                $bctai_result['msg'] = BCTAI_NONCE_ERROR;
                wp_send_json($bctai_result);
            }
            if(isset($_POST['content']) && !empty($_POST['content'])){
                $content = wp_kses_post(wp_strip_all_tags($_POST['content']));
                if(!empty($content)){
                    $bctai_result = $this->bctai_save_embedding($content);
                }
                else $bctai_result['msg'] = 'Please insert content';
            }
            wp_send_json($bctai_result);
        }
    }
    BCTAI_Embeddings::get_instance();
}