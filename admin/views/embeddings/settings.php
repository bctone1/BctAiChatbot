<?php
if (!defined('ABSPATH'))
    exit;
global $wpdb;

$bctai_embeddings_settings_updated = false;
if (isset($_POST['bctai_save_builder_settings'])) {
    check_admin_referer('bctai_embeddings_settings');
    if (isset($_POST['bctai_pinecone_api']) && !empty($_POST['bctai_pinecone_api'])) {
        update_option('bctai_pinecone_api', sanitize_text_field($_POST['bctai_pinecone_api']));
    } else {
        delete_option('bctai_pinecone_api');
    }
    if (isset($_POST['bctai_pinecone_environment']) && !empty($_POST['bctai_pinecone_environment'])) {
        update_option('bctai_pinecone_environment', sanitize_text_field($_POST['bctai_pinecone_environment']));
    } else {
        delete_option('bctai_pinecone_environment');
    }
    if (isset($_POST['bctai_pinecone_sv']) && !empty($_POST['bctai_pinecone_sv'])) {
        update_option('bctai_pinecone_sv', sanitize_text_field($_POST['bctai_pinecone_sv']));
    } else {
        delete_option('bctai_pinecone_sv');
    }
    if (isset($_POST['bctai_builder_enable']) && !empty($_POST['bctai_builder_enable'])) {
        update_option('bctai_builder_enable', 'yes');
    } else {
        delete_option('bctai_builder_enable');
    }
    if (isset($_POST['bctai_builder_types']) && is_array($_POST['bctai_builder_types']) && count($_POST['bctai_builder_types'])) {
        update_option('bctai_builder_types', \BCTAI\bctai_util_core()->sanitize_text_or_array_field($_POST['bctai_builder_types']));
    } else {
        delete_option('bctai_builder_types');
    }
    if (isset($_POST['bctai_instant_embedding']) && !empty($_POST['bctai_instant_embedding'])) {
        update_option('bctai_instant_embedding', \BCTAI\bctai_util_core()->sanitize_text_or_array_field($_POST['bctai_instant_embedding']));
    } else {
        update_option('bctai_instant_embedding', 'no');
    }
    $bctai_embeddings_settings_updated = true;

}
$bctai_pinecone_api = get_option('bctai_pinecone_api', '');
$bctai_pinecone_sv = get_option('bctai_pinecone_sv', '');
$bctai_pinecone_environment = get_option('bctai_pinecone_environment', '');
$bctai_builder_types = get_option('bctai_builder_types', []);
$bctai_builder_enable = get_option('bctai_builder_enable', '');
$bctai_instant_embedding = get_option('bctai_instant_embedding', 'yes');
$bctai_pinecone_indexes = get_option('bctai_pinecone_indexes', '');
$bctai_pinecone_indexes = empty($bctai_pinecone_indexes) ? array() : json_decode($bctai_pinecone_indexes, true);
$bctai_pinecone_environments = array(
    'asia-northeast1-gcp' => 'GCP Asia-Northeast-1 (Tokyo)',
    'asia-northeast1-gcp-free' => 'GCP Asia-Northeast-1 Free (Tokyo)',
    'asia-northeast2-gcp' => 'GCP Asia-Northeast-2 (Osaka)',
    'asia-northeast2-gcp-free' => 'GCP Asia-Northeast-2 Free (Osaka)',
    'asia-northeast3-gcp' => 'GCP Asia-Northeast-3 (Seoul)',
    'asia-northeast3-gcp-free' => 'GCP Asia-Northeast-3 Free (Seoul)',
    'asia-southeast1-gcp' => 'GCP Asia-Southeast-1 (Singapore)',
    'asia-southeast1-gcp-free' => 'GCP Asia-Southeast-1 Free',
    'eu-west1-gcp' => 'GCP EU-West-1 (Ireland)',
    'eu-west1-gcp-free' => 'GCP EU-West-1 Free (Ireland)',
    'eu-west2-gcp' => 'GCP EU-West-2 (London)',
    'eu-west2-gcp-free' => 'GCP EU-West-2 Free (London)',
    'eu-west3-gcp' => 'GCP EU-West-3 (Frankfurt)',
    'eu-west3-gcp-free' => 'GCP EU-West-3 Free (Frankfurt)',
    'eu-west4-gcp' => 'GCP EU-West-4 (Netherlands)',
    'eu-west4-gcp-free' => 'GCP EU-West-4 Free (Netherlands)',
    'eu-west6-gcp' => 'GCP EU-West-6 (Zurich)',
    'eu-west6-gcp-free' => 'GCP EU-West-6 Free (Zurich)',
    'eu-west8-gcp' => 'GCP EU-West-8 (Italy)',
    'eu-west8-gcp-free' => 'GCP EU-West-8 Free (Italy)',
    'eu-west9-gcp' => 'GCP EU-West-9 (France)',
    'eu-west9-gcp-free' => 'GCP EU-West-9 Free (France)',
    'gcp-starter' => 'GCP Starter',
    'northamerica-northeast1-gcp' => 'GCP Northamerica-Northeast1',
    'northamerica-northeast1-gcp-free' => 'GCP Northamerica-Northeast1 Free',
    'southamerica-northeast2-gcp' => 'GCP Southamerica-Northeast2 (Toronto)',
    'southamerica-northeast2-gcp-free' => 'GCP Southamerica-Northeast2 Free (Toronto)',
    'southamerica-east1-gcp' => 'GCP Southamerica-East1 (Sao Paulo)',
    'southamerica-east1-gcp-free' => 'GCP Southamerica-East1 Free (Sao Paulo)',
    'us-central1-gcp' => 'GCP US-Central-1 (Iowa)',
    'us-central1-gcp-free' => 'GCP US-Central-1 Free (Iowa)',
    'us-east1-aws' => 'AWS US-East-1 (Virginia)',
    'us-east1-aws-free' => 'AWS US-East-1 Free (Virginia)',
    'us-east-1-aws' => 'AWS US-East-1 (Virginia)',
    'us-east-1-aws-free' => 'AWS US-East-1 Free (Virginia)',
    'us-east1-gcp' => 'GCP US-East-1 (South Carolina)',
    'us-east1-gcp-free' => 'GCP US-East-1 Free (South Carolina)',
    'us-east4-gcp' => 'GCP US-East-4 (Virginia)',
    'us-east4-gcp-free' => 'GCP US-East-4 Free (Virginia)',
    'us-west1-gcp' => 'GCP US-West-1 (N. California)',
    'us-west1-gcp-free' => 'GCP US-West-1 Free (N. California)',
    'us-west2-gcp' => 'GCP US-West-2 (Oregon)',
    'us-west2-gcp-free' => 'GCP US-West-2 Free (Oregon)',
    'us-west3-gcp' => 'GCP US-West-3 (Salt Lake City)',
    'us-west3-gcp-free' => 'GCP US-West-3 Free (Salt Lake City)',
    'us-west4-gcp' => 'GCP US-West-4 (Las Vegas)',
    'us-west4-gcp-free' => 'GCP US-West-4 Free (Las Vegas)'
);
if ($bctai_embeddings_settings_updated) {
    ?>
    <div class="notice notice-success">
        <p>Records updated successfully</p>
    </div>
    <?php
}
?>
<style>
    .bctai_modal {
        width: 600px;
        left: calc(50% - 300px);
        height: 40%;
    }

    .bctai_modal_content {
        height: calc(100% - 103px);
        overflow-y: auto;
    }

    .bctai_assign_footer {
        position: absolute;
        bottom: 0;
        display: flex;
        justify-content: space-between;
        width: calc(100% - 20px);
        align-items: center;
        border-top: 1px solid #ccc;
        left: 0;
        padding: 3px 10px;
    }
</style>
<form action="" method="post">
    <?php
    wp_nonce_field('bctai_embeddings_settings');
    ?>
    <h1 style="font-weight: bolder;margin-left: 30px;">
        <?php echo __('Setting', 'kkkk') ?>
    </h1>
    <h3 style="margin-left: 30px;">Pinecone</h3>
    <div class="bctai-alert">
        <h3>Steps</h3>
        <p>1. Obtain your API key from <a href="https://www.pinecone.io/" target="_blank">Pinecone</a>.</p>
        <p>2. Create an Index on Pinecone.</p>
        <p>3. Ensure your dimension is set to <b>1536</b>.</p>
        <p>4. Set your metric to <b>cosine</b>.</p>
        <p>5. Input your data.</p>
        <p>6. Navigate to Settings - ChatGPT tab and choose the Embeddings method.</p>
    </div>
    <table class="form-table" style="margin-left: 30px;">
        <tr>
            <th scope="row" >Pinecone API</th>
            <td>
                <input type="text" class="regular-text bctai_pinecone_api" name="bctai_pinecone_api" style="margin-left:0px;"
                    value="<?php echo esc_attr($bctai_pinecone_api) ?>">
            </td>
        </tr>
        <tr>
            <th scope="row">Pinecone Environment</th>
            <td>
                <select class="bctai_pinecone_sv" name="bctai_pinecone_sv">
                    <?php
                    foreach ($bctai_pinecone_environments as $key => $bctai_pinecone_environment_detail) {
                        echo '<option' . ($bctai_pinecone_sv == $key ? ' selected' : '') . ' value="' . $key . '">' . $key . '</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row">&nbsp;</th>
            <td>
                <button type="button" class="button button-primary bctai_pinecone_indexes" style="border-radius: 13px;     background: #8040ad;border-color: #8040ad;">
                    <?php echo esc_html__('Sync Indexes', 'wp-bct-ai') ?>
                </button>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <?php echo esc_html__('Pinecone Index', 'wp-bct-ai') ?>
            </th>
            <td>
                <select class="bctai_pinecone_environment" name="bctai_pinecone_environment"
                    old-value="<?php echo esc_attr($bctai_pinecone_environment) ?>">
                    <option value="">
                        <?php echo esc_html__('Select Index', 'gpt3-ai-content-generator') ?>
                    </option>
                    <?php
                    foreach ($bctai_pinecone_indexes as $bctai_pinecone_index) {
                        echo '<option' . ($bctai_pinecone_environment == $bctai_pinecone_index['url'] ? ' selected' : '') . ' value="' . esc_html($bctai_pinecone_index['url']) . '">' . esc_html($bctai_pinecone_index['name']) . '</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>
    </table>
    <h3 style="margin-left: 30px;">Instant Embedding</h3>
    <p style="margin-left: 30px;">Enable this option to get instant embeddings for your content. Go to your post, page or products page and select
        all your contents and click on Instant Embedding button.</p>
    <table class="form-table" style="margin-left: 30px;">
        <tr>
            <th scope="row">Enable:</th>
            <td>
                <div class="mb-5">
                    <label>
                        <input <?php echo $bctai_instant_embedding == 'yes' ? ' checked' : ''; ?> type="checkbox"
                            name="bctai_instant_embedding" value="yes">
                </div>
            </td>
        </tr>
    </table>
    <h3 style="margin-left: 30px;">Index Builder</h3>
    <p style="margin-left: 30px;">You can use index builder to build your index. Difference between index builder and instant embedding is that
        once you complete the cron job, index builder will monitor your content and will update the idex automatically.
    </p>
    <table class="form-table" style="margin-left: 30px;">
        <tr>
            <th scope="row">Build Index for:</th>
            <td>
                <div class="mb-5">
                    <div class="mb-5"><label>
                            <input <?php echo in_array('post', $bctai_builder_types) ? ' checked' : ''; ?> type="checkbox"
                                name="bctai_builder_types[]" value="post">&nbsp;Posts
                        </label></div>
                    <div class="mb-5"><label>
                            <input <?php echo in_array('page', $bctai_builder_types) ? ' checked' : ''; ?> type="checkbox"
                                name="bctai_builder_types[]" value="page">&nbsp;Pages
                        </label></div>
                    <?php
                    if (class_exists('WooCommerce')):
                        ?>
                        <div class="mb-5">
                            <label>
                                <input <?php echo in_array('product', $bctai_builder_types) ? ' checked' : ''; ?> type="checkbox"
                                    name="bctai_builder_types[]" value="product">&nbsp;Products
                            </label>
                        </div>
                        <?php
                    endif;
                    ?>
                    <?php
                    include __DIR__ . '/custom_post_type.php';
                    ?>
                </div>
            </td>
        </tr>
    </table>
    <button class="button button-primary" name="bctai_save_builder_settings" style="margin-left: 30px; width: 200px;height: 50px;border-radius: 13px;background: #8040ad;border: 0px; font-size: 20px;">Save</button>
</form>
<script>
    jQuery(document).ready(function ($) {
        function bctaiLoading(btn) {
            btn.attr('disabled', 'disabled');
            if (!btn.find('spinner').length) {
                btn.append('<span class="spinner"></span>');
            }
            btn.find('.spinner').css('visibility', 'unset');
        }
        function bctaiRmLoading(btn) {
            btn.removeAttr('disabled');
            btn.find('.spinner').remove();
        }
        $('.bctai_pinecone_indexes').click(function () {
            var btn = $(this);
            var bctai_pinecone_api = $('.bctai_pinecone_api').val();
            var bctai_pinecone_sv = $('.bctai_pinecone_sv').val();
            var old_value = $('.bctai_pinecone_environment').attr('old-value');
            if (bctai_pinecone_api !== '' && bctai_pinecone_sv !== '') {
                $.ajax({
                    url: 'https://controller.' + bctai_pinecone_sv + '.pinecone.io/databases',
                    headers: { "Api-Key": bctai_pinecone_api },
                    dataType: 'json',
                    beforeSend: function () {
                        bctaiLoading(btn);
                        btn.html('<?php echo esc_html__('Syncing...', 'wp-bct-ai') ?>');
                    },
                    success: function (res) {
                        //alert(res.length);
                        if (res.length) {
                            var selectedLists = [];
                            var totalIndex = res.length;
                            var currentIndex = 0;
                            for (var i = 0; i < res.length; i++) {
                                currentIndex = i + 1;
                                var indexName = res[i];
                                $.ajax({
                                    url: 'https://controller.' + bctai_pinecone_sv + '.pinecone.io/databases/' + indexName,
                                    headers: { "Api-Key": bctai_pinecone_api },
                                    dataType: 'json',
                                    success: function (resi) {
                                        selectedLists.push({ name: indexName, url: resi.status.host });
                                        if (totalIndex === currentIndex) {
                                            btn.html('<?php echo esc_html__('Sync Indexes', 'wp-bct-ai') ?>');
                                            $.post('<?php echo admin_url('admin-ajax.php') ?>', {
                                                action: 'bctai_pinecone_indexes',
                                                nonce: '<?php echo wp_create_nonce('bctai-ajax-nonce') ?>',
                                                indexes: JSON.stringify(selectedLists),
                                                api_key: bctai_pinecone_api,
                                                server: bctai_pinecone_sv
                                            });
                                            bctaiRmLoading(btn)
                                            var selectList = '<option value=""><?php echo esc_html__('Select Index', 'wp-bct-ai') ?></option>';
                                            for (var j = 0; j < selectedLists.length; j++) {
                                                var selectedList = selectedLists[j];
                                                selectList += '<option' + (old_value === selectedList.url ? ' selected' : '') + ' value="' + selectedList.url + '">' + selectedList.name + '</option>';
                                            }
                                            $('.bctai_pinecone_environment').html(selectList);
                                        }
                                    }
                                })
                            }
                        }
                    },
                    error: function (e) {
                        btn.html('<?php echo esc_html__('Sync Indexes', 'wp-bct-ai') ?>');
                        bctaiRmLoading(btn);
                        alert(e.responseText);
                    }
                });
            }
            else {
                alert('<?php echo esc_html__('Please add Pinecone API key and Pinecone Environment before start sync', 'wp-bct-ai') ?>')
            }
        })
    })
</script>