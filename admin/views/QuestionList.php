<?php
if (!defined('ABSPATH'))
    exit;
global $wpdb;
if (isset($_GET['bctai_nonce']) && !wp_verify_nonce($_GET['bctai_nonce'], 'bctai_chatlogs_search_nonce')) {
    die('Security check failed');
}



$posts_per_page = get_option('bctai_knowledge_builder_page', 10);
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($page - 1) * $posts_per_page;


$query = $wpdb->prepare("
Select * from " . $wpdb->prefix . "bctai_question
order by id desc
LIMIT %d OFFSET %d",$posts_per_page, $offset);



$posts = $wpdb->get_results($query);










$total_posts = $wpdb->get_var("select count(*) from " . $wpdb->prefix . "bctai_question ");



$query2 = "SELECT * FROM ".$wpdb->prefix."bctai_question WHERE 1=1";

$bctai_log_page = isset($_GET['wpage']) && !empty($_GET['wpage']) ? sanitize_text_field($_GET['wpage']) : 1;

$items_per_page = 10;
$offset = ( $bctai_log_page * $items_per_page ) - $items_per_page;

$bctai_logs = $wpdb->get_results( $wpdb->prepare( $query2 . " ORDER BY created_at DESC LIMIT %d, %d", $offset, $items_per_page ));

$totalPage = ceil($total_posts / $items_per_page);



?>


<div class="search-area" style="margin-bottom: 1em;display: flex;justify-content: space-between;">
    <input type="text" id="search-input" placeholder="<?php echo esc_attr__('Search...', ' '); ?>" style="width: 100%; max-width: 300px;">
    <select id="results-per-page" name="results-per-page">
        <?php
        $options = [3, 5, 10, 25, 50, 100, 500, 1000];
        foreach ($options as $option) {
            $selected = ($option == $posts_per_page) ? 'selected' : '';
            echo "<option value='$option' $selected>$option</option>";
        }
        ?>
    </select>
</div>


<div class="wpaicg-table-responsive">
    <table id="paginated-table" class="wp-list-table widefat striped">
        <thead>
            <tr>
                <th class="column-id"><?php echo esc_html__('ID', ' '); ?></th>
                <th class="column-content"><?php echo esc_html__('Content', ' '); ?></th>
                <th class="column-details"><?php echo esc_html__('Details', ' '); ?></th>
                <th class="column-source"><?php echo esc_html__('Source', ' '); ?></th>
                <th class="column-date"><?php echo esc_html__('Date', ' '); ?></th>
                <th class="column-action"><?php echo esc_html__('Action', ' '); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $bctai_logs as $post ) : ?>
                <?php echo \BCTAI\BCTAI_Embeddings::get_instance()->generate_table_row($post); ?>
                <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="bctai-paginate">
<?php
if($totalPage > 1) {
    echo paginate_links( array(
        'base'         => admin_url('admin.php?page=QuestionList&action=logs&wpage=%#%'),
        'total'        => $totalPage,
        'current'      => $bctai_log_page,
        'format'       => '?wpage=%#%',
        'show_all'     => false,
        'prev_next'    => false,
        'add_args'     => false,
    ));
}
?>
</div>


<button id="reload-items" class="button button-secondary" title="Refresh">
    <svg id="reload-icon" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-ccw"><polyline points="1 4 1 10 7 10"></polyline><polyline points="23 20 23 14 17 14"></polyline><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"></path></svg>
</button>

<script>

jQuery(document).ready(function($) {



    $('#results-per-page').on('change', function() {
        //alert("change");
        var resultsPerPage = $(this).val();
        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        // var nonce = $('#gpt4_pagination_nonce').val();
        var searchTerm = $('#search-input').val();
        
        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'set_results_per_page',
                results_per_page: resultsPerPage,
                // nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#reload-items').click();
                } else {
                    alert('Failed to set results per page.');
                }
            }
        });
    });


    $('#reload-items').on('click', function(e) {
            e.preventDefault();
            var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
            // var nonce = $('#gpt4_pagination_nonce').val();
            var searchTerm = $('#search-input').val();
            var resultsPerPage = $('#results-per-page').val();
            $('#reload-icon').addClass('spinrefresh'); 

            $.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'reload_items_embeddings',
                    // nonce: nonce,
                    search_term: searchTerm,
                    results_per_page: resultsPerPage
                },
                success: function(response) {
                    console.log(response);
                    if (response.success) {
                        $('#paginated-table tbody').html(response.data.content);
                        $('.gpt4-pagination').html(response.data.pagination);
                    } else {
                        alert('Failed to reload items.');
                    }
                    $('#reload-icon').removeClass('spinrefresh');
                },
                error: function() {
                    alert('Failed to reload items.');
                    $('#reload-icon').removeClass('spinrefresh'); // Ensure spinning stops on error
                }
            });
        });






        
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }

    $('#search-input').on('keyup', debounce(function() {
        //alert("dldkl");

        var searchTerm = $(this).val();
        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        // var nonce = $('#gpt4_pagination_nonce').val();
        var resultsPerPage = $('#results-per-page').val();

            $.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'search_question_content',
                    search_term: searchTerm,
                    //nonce: nonce,
                    page: 1,
                    results_per_page: resultsPerPage
                },
                success: function(response) {
                    //console.log(response);

                    if (response.success) {
                        $('#paginated-table tbody').html(response.data.content);
                        $('.gpt4-pagination').html(response.data.pagination); // Update pagination as well
                    } else {
                        alert('No results found.');
                    }
                },
                error: function() {
                    alert('Search failed. Please try again.');
                }
            });
    }, 250)); 



})


</script>