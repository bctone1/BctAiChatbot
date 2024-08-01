<?php
if ( ! defined( 'ABSPATH' ) ) exit;
global $wpdb;
$bctai_sub_action = isset($_GET['sub']) && !empty($_GET['sub']) ? sanitize_text_field($_GET['sub']) : false;
if($bctai_sub_action == 'reindexall'){
    //echo '<pre>'; print_r($bctai_sub_action); echo '</pre>';
    $bctai_embeddings = $wpdb->get_results("SELECT ID FROM ".$wpdb->posts." WHERE post_type='bctai_builder'");
    //echo '<pre>'; print_r($bctai_embeddings); echo '</pre>';
    if($bctai_embeddings && count($bctai_embeddings)) {
        foreach($bctai_embeddings as $bctai_embedding){
            $parent_id = get_post_meta($bctai_embedding->ID,'bctai_parent',true);
            if($parent_id && get_post($parent_id)){
                update_post_meta($bctai_embedding->ID,'bctai_indexed','reindex');
                update_post_meta($parent_id,'bctai_indexed','reindex');
            }
        }
    }
    echo '<script>window.location.href = "'.admin_url('admin.php?page=Embeddings&action=builder').'";</script>';
    exit;  
}
if($bctai_sub_action == 'deleteall'){
    //echo '<pre>'; print_r($bctai_sub_action); echo '</pre>';
    $wpdb->query("DELETE FROM ".$wpdb->postmeta." WHERE meta_key IN ('bctai_indexed','bctai_source','bctai_parent','bctai_error_msg')");
    $bctai_embeddings = $wpdb->get_results("SELECT ID FROM ".$wpdb->posts." WHERE post_type='bctai_builder'");
    //echo '<pre>'; print_r($bctai_embeddings); echo '</pre>';
    if($bctai_embeddings && count($bctai_embeddings)) {
        $bctai_embedding_ids = wp_list_pluck($bctai_embeddings,'ID');
        BCTAI\BCTAI_Embeddings::get_instance()->bctai_delete_embeddings_ids($bctai_embedding_ids);
    }
    echo '<script>window.location.href = "'.admin_url('admin.php?page=Embeddings&action=builder').'";</script>';
    exit;
}



$bctai_builder_sub = isset($_GET['sub']) && !empty($_GET['sub']) ? sanitize_text_field($_GET['sub']) : false;
$bctai_builder_types = get_option('bctai_builder_types',[]);
//echo '<pre>'; print_r($bctai_builder_types); echo '</pre>';
$bctai_builder_enable = get_option('bctai_builder_enable','');
// $bctai_total_indexed = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->posts." p LEFT JOIN ".$wpdb->postmeta." m ON m.post_id = p.ID WHERE p.post_type='bctai_builder' AND m.meta_key='bctai_indexed' AND m.meta_value='yes'");
$sql_count_indexed = $wpdb->prepare("SELECT COUNT(p.ID) FROM ".$wpdb->postmeta." m LEFT JOIN ".$wpdb->posts." p ON p.ID=m.post_id WHERE p.post_type IN ('post','legalprotech','page') AND p.post_status = 'publish' AND m.meta_key='bctai_indexed' AND m.meta_value IN ('error','skip','yes')");
$bctai_total_indexed = $wpdb->get_var($sql_count_indexed);
$bctai_total_errors = array();
$bctai_total_skips = array();
if($bctai_builder_types && is_array($bctai_builder_types) && count($bctai_builder_types)) {
    $ids = implode("','",$bctai_builder_types);
    $commaDelimitedPlaceholders = implode(',', array_fill(0, count($bctai_builder_types), '%s'));    

    $bctai_total_errors = $wpdb->get_results("SELECT p.ID,p.post_title FROM " . $wpdb->posts . " p LEFT JOIN " . $wpdb->postmeta . " m ON m.post_id = p.ID WHERE p.post_type IN ('".$ids."') AND m.meta_key='bctai_indexed' AND m.meta_value='error'");
    //echo '<pre>'; print_r($bctai_total_errors); echo '</pre>';
    $bctai_total_skips = $wpdb->get_results("SELECT p.ID,p.post_title FROM " . $wpdb->posts . " p LEFT JOIN " . $wpdb->postmeta . " m ON m.post_id = p.ID WHERE p.post_type IN ('".$ids."') AND m.meta_key='bctai_indexed' AND m.meta_value='skip'");
    //echo '<pre>'; print_r($bctai_total_skips); echo '</pre>';
}
?>
<style>
    .bctai_modal{
        top: 5%;
        height: 90%;
        position: relative;
    }
    .bctai_modal_content{
        max-height: calc(100% - 103px);
        overflow-y: auto;
    }
    .bctai-builder-process{
        margin-bottom: 10px;
    }
    .bctai-builder-process-content{
        height: 20px;
        width: 100%;
        /* width: 94%; */
        background: #dbdbdb;
        border-radius: 4px;
        position: relative;
        overflow: hidden;
    }
    .bctai-percent{
        position: absolute;
        display: block;
        height: 20px;
        /* background: #0d969d; */
        background: #8040ad;
    }
    .bctai-numbers{}
    .wp-core-ui .button.bctai-danger-btn{
        background: #c90000;
        color: #fff;
        border-color: #cb0000;
    }

</style>
<?php
if(!$bctai_builder_sub) {
    include __DIR__.'/builder_index.php';
}
if($bctai_builder_sub == 'errors'){
    include __DIR__.'/builder_errors.php';
}
if($bctai_builder_sub == 'skip'){
    include __DIR__.'/builder_skip.php';
}
?>


