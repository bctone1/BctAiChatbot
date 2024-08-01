<?php
if (!defined('ABSPATH'))
    exit;

    $bctai_ai_model = get_option('bctai_chat_model', '');
    $bctai_chat_provider = get_option('bctai_chat_provider','OpenAI');
    $bctai_OpenRouter_model = get_option('bctai_OpenRouter_model','none');
    $bctai_OpenRouter_APIkey = get_option('bctai_OpenRouter_APIkey');

    //$openrouter_models = get_option('wpaicg_openrouter_model_list',1);
    //echo '<pre>'; print_r($bctai_Open_Router_models); echo '</pre>';


    $bctai_vector_db_provider = get_option('bctai_vector_db_provider', 'pinecone');

?>




<h1 style="font-family: inherit;line-height: 1.46;font-size: 24px;font-weight: 900;"><?php echo __('Provider', 'bctai') ?></h1>

<div class="bctai_form_row">
    <label class="bctai_label" for="bctai_chat_model"><?php echo esc_html__('Provider', 'bctai') ?></label>
    <select class="regular-text" id="bctai_chat_provider" name="bctai_chat_provider" style="max-width: 1000px;border-radius: 16px;background: #f1f1f1 url(<?php echo BCTAI_PLUGIN_URL . 'src/images/icon_arrow_select.png'?>) no-repeat right 17px center;    width: 540px;height: 52px;">
        <option value="OpenAI"><?php echo __('OpenAI', 'bctai') ?></option>
        <option <?php echo $bctai_chat_provider == 'OpenRouter' ? ' selected': ''?> value="OpenRouter"><?php echo __('OpenRouter', 'bctai') ?></option>
        <option <?php echo $bctai_chat_provider == 'Google' ? ' selected': ''?> value="Google"><?php echo __('Google', 'bctai') ?></option>
        <option <?php echo $bctai_chat_provider == 'Microsoft' ? ' selected': ''?> value="Microsoft"><?php echo __('Microsoft', 'bctai') ?></option>
    </select>
</div>




<div class="OpenRouter_wrap"id="tabs-1" style=" display:<?php echo $bctai_chat_provider =='OpenRouter' ? 'block':'none'?>">
    
    <div class="bctai_form_row">
        <label class="bctai_label"><?php echo esc_html__( 'Model', 'gpt3-ai-content-generator' );?></label>
        
        <div class="inputButtonArea" style="display:inline-flex">
            <select id="wpaicg_openrouter_model" name="wpaicg_openrouter_model" class="regular-text"style="max-width: 1000px;background: #f1f1f1 url(<?php echo BCTAI_PLUGIN_URL . 'src/images/icon_arrow_select.png'?>) no-repeat right 17px center;    width: 487px;height: 52px;">
                <?php
                $openrouter_models = get_option( 'bctai_openrouter_model_list', [] );
                $grouped_models = [];
                foreach ( $openrouter_models as $model ) {
                    $provider = explode( '/', $model['id'] )[0];
                    if ( !isset( $grouped_models[$provider] ) ) {
                        $grouped_models[$provider] = [];
                    }
                    $grouped_models[$provider][] = $model;
                }
                ksort( $grouped_models );
                foreach ( $grouped_models as $provider => $models ) {
                    echo '<optgroup label="' . esc_attr( $provider ) . '">';
                    usort( $models, function ( $a, $b ) {
                        return strcmp( $a["name"], $b["name"] );
                    } );
                    foreach ( $models as $model ) {
                        echo '<option value="' . esc_attr( $model['id'] ) . '" ' . selected( $model['id'], get_option( 'wpaicg_openrouter_model' ), false ) . '>' . esc_html( $model['name'] ) . '</option>';
                    }
                    echo '</optgroup>';
                }
                ?>
            </select>
            <button id="syncButton" class="btn btnL bgPrimary iconLoading iconOnly on wpaicg_sync_openrouter_models" type="button" style="border:0px;">
            </button>
        </div>
        
    </div>


    <div class="bctai_form_row"style="margin-bottom: 0px;">
        <label class="bctai_label"><?php echo esc_html__('Api Key', 'bctai') ?></label>
        <input type="text" class="regular-text" id="bctai_OpenRouter_APIkey" name="bctai_OpenRouter_APIkey"value="<?php echo esc_html($bctai_OpenRouter_APIkey); ?>"style="border: 0px;background: #f1f1f1;border-radius: 16px;width: 540px;height: 52px;">
        
    </div>
    <a class="bctai_help_link" style="margin-left: 260px;margin-top: 10px;text-decoration: underline;color: #F53706;font-family: inherit;font-weight: 400;"href="https://openrouter.ai/keys" target="_blank"><?php echo esc_html__('Get Your Key', 'bctai') ?></a>
</div>









<div class="OpenAI_wrap"id="tabs-1" style=" display:<?php echo $bctai_chat_provider =='OpenAI' ? 'block':'none'?>">
    <div class="bctai_form_row">
        <label class="bctai_label" for="bctai_chat_model"><?php echo esc_html__('Model', 'bctai') ?></label>

        <select class="regular-text" id="bctai_chat_model" name="bctai_chat_model"style="max-width: 1000px;border-radius: 16px;background: #f1f1f1 url(<?php echo BCTAI_PLUGIN_URL . 'src/images/icon_arrow_select.png'?>) no-repeat right 17px center;    width: 540px;height: 52px;">
            <?php
            $gpt4_models = ['gpt-4', 'gpt-4-32k'];
            $gpt35_models = ['gpt-3.5-turbo', 'gpt-3.5-turbo-16k', 'gpt-3.5-turbo-instruct'];
            $gpt3_models = ['text-curie-001', 'text-babbage-001', 'text-ada-001'];
            $legacy_models = ['text-davinci-003'];
            $custom_models = get_option('bctai_custom_models', []);
            $current_model = $bctai_ai_model; // This should be the model currently selected
            ?>
            <optgroup label="GPT-4">
                <?php foreach ($gpt4_models as $model): ?>
                    <option value="<?php echo esc_attr($model); ?>" <?php selected($model, $current_model); ?>>
                        <?php echo esc_html($model); ?>
                    </option>
                <?php endforeach; ?>
            </optgroup>
            <optgroup label="GPT-3.5">
                <?php foreach ($gpt35_models as $model): ?>
                    <option value="<?php echo esc_attr($model); ?>" <?php selected($model, $current_model); ?>>
                        <?php echo esc_html($model); ?>
                    </option>
                <?php endforeach; ?>
            </optgroup>
            <optgroup label="GPT-3">
                <?php foreach ($gpt3_models as $model): ?>
                    <option value="<?php echo esc_attr($model); ?>" <?php selected($model, $current_model); ?>>
                        <?php echo esc_html($model); ?>
                    </option>
                <?php endforeach; ?>
            </optgroup>
            <optgroup label="Legacy Models">
                <?php foreach ($legacy_models as $model): ?>
                    <option value="<?php echo esc_attr($model); ?>" <?php selected($model, $current_model); ?>>
                        <?php echo esc_html($model); ?>
                    </option>
                <?php endforeach; ?>
            </optgroup>
            <optgroup label="Custom Models">
                <?php foreach ($custom_models as $model): ?>
                    <option value="<?php echo esc_attr($model); ?>" <?php selected($model, $current_model); ?>>
                        <?php echo esc_html($model); ?>
                    </option>
                <?php endforeach; ?>
            </optgroup>
        </select>
        <!-- <div class="bctai_form_row bctai_beta_notice" style="margin:0px;width:570px;margin-left: 256px;">
            <p style="margin:0px; font-size:16px;"><?php echo esc_html__('Provided models can be linked to OpenAI states.', 'bctai') ?></p>
            <p style="margin:0px; font-size:16px;">
                <?php echo esc_html__('Please proceed with OpenAI registration at this link.', 'bctai')?>
                <a style="color:#F53706;"href="https://openai.com/waitlist/gpt-4-api" target="_blank"><?php echo __('Link','bctai')?></a>
            </p>
        </div> -->
    </div>
    



    

    <div class="bctai_form_row" style="margin-bottom: 0px;">
        <label class="bctai_label"><?php echo esc_html__('Api Key', 'bctai') ?></label>
        <input type="text" class="regular-text" id="label_api_key" name="bctai_settings[api_key]"value="<?php echo esc_html($existingValue['api_key']); ?>"style="border: 0px;background: #f1f1f1;border-radius: 16px;width: 540px;height: 52px;">
    </div>
    <a class="bctai_help_link" style="margin-left: 260px;margin-top: 10px;text-decoration: underline;color: #F53706;font-family: inherit;font-weight: 400;"href="https://beta.openai.com/account/api-keys" target="_blank"><?php echo esc_html__('Get Your Key', 'bctai') ?></a>
    
    
</div>








<h1 style="font-family: inherit;line-height: 1.46;font-size: 24px;font-weight: 900;"><?php echo __('Vector Database Setting', 'bctai') ?></h1>

    <div class="bctai_form_row">
        <label class="bctai_label"><?php echo esc_html__('Default Vector DB','bctai')?></label>
        <select name="bctai_vector_db_provider" class="bctai_vector_db_provider" style="border:0px;max-width: 1000px;border-radius: 16px;background: #f1f1f1 url(<?php echo BCTAI_PLUGIN_URL . 'src/images/icon_arrow_select.png'?>) no-repeat right 17px center;    width: 540px;height: 52px;">
            <option value="pinecone" <?php selected($bctai_vector_db_provider, 'pinecone'); ?>>Pinecone</option>
            <option value="qdrant" <?php selected($bctai_vector_db_provider, 'qdrant'); ?>>Qdrant</option>
        </select>
    </div>



    <div class="Pinecone_wrap" style=" display:<?php echo $bctai_vector_db_provider =='pinecone' ? 'block':'none'?>">
        
        
        <div class="bctai-alert">
            <h3 style="margin-bottom: 20px;font-size: 16px;font-weight: 600;margin: 0px 0px 20px 0px;color: #000;">Steps</h3>
            <p><?php echo __('1. Obtain your API key from.Pinecone.', 'bctai') ?></p>
            <p><?php echo __('2. Create an Index on Pinecone.', 'bctai') ?></p>
            <p><?php echo __('3. Ensure your dimension is set to 1536.', 'bctai') ?></p>
            <p><?php echo __('4. Set your metric to cosine.', 'bctai') ?></p>
            <p><?php echo __('5. Input your data.', 'bctai') ?></p>
            <p><?php echo __('6. Navigate to Settings - ChatGPT tab and choose the Embeddings method.', 'bctai') ?></p>

            <div class="btnArea">
                <button type="button" class="btn btnL bgPrimary" style="border: 0px;" href="https://www.pinecone.io/" target="_blank"><span>BUTTON type 2</span></button>
            </div>

        </div>

        


        <div class="bctai_form_row">
            <label class="bctai_label"><?php echo __('Pinecone API', 'bctai') ?></label>
            <input type="text" class="regular-text bctai_pinecone_api" name="bctai_pinecone_api" value="<?php echo esc_attr($bctai_pinecone_api) ?>" style="max-width: 1000px;border-radius: 16px;background: #f1f1f1; width:408px; height: 52px;border:0px;    border-top-right-radius: 0px;border-bottom-right-radius: 0px;">
            <button type="button" class="button-primary bctai_pinecone_indexes" style="font-weight: 700;border-radius: 16px;background: #8040ad;border-color: #8040ad;width: 132px;height: 52px;margin: 0px;border-top-left-radius: 0px;border-bottom-left-radius: 0px;margin-left: -4px;">
                <?php echo esc_html__('SYNC INDEXES', 'bctai') ?>
            </button>
        </div>

        <div class="bctai_form_row">
            <label class="bctai_label"><?php echo esc_html__('Pinecone Index', 'bctai') ?></label>
            <select class="bctai_pinecone_environment" name="bctai_pinecone_environment"old-value="<?php echo esc_attr($bctai_pinecone_environment) ?>" style="max-width: 1000px;border-radius: 16px;background: #f1f1f1 url(data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M5%206l5%205%205-5%202%201-7%207-7-7%202-1z%22%20fill%3D%22%23555%22%2F%3E%3C%2Fsvg%3E) no-repeat right 5px top 55%;    width: 540px;height: 52px;border:0px;">
                <option value="">
                    <?php echo esc_html__('Select Index', 'gpt3-ai-content-generator') ?>
                </option>
                <?php
                foreach ($bctai_pinecone_indexes as $bctai_pinecone_index) {
                    echo '<option' . ($bctai_pinecone_environment == $bctai_pinecone_index['url'] ? ' selected' : '') . ' value="' . esc_html($bctai_pinecone_index['url']) . '">' . esc_html($bctai_pinecone_index['name']) . '</option>';}
                ?>
            </select>
        </div>
    </div>

   


    <div class="Qdrant_wrap" style=" display:<?php echo $bctai_vector_db_provider =='qdrant' ? 'block':'none'?>">
        
        <div class="bctai_form_row">
            <label class="bctai_label"><?php echo __('Qdrant API', 'bctai') ?></label>
            <input type="text" class="regular-text bctai_qdrant_api" name="bctai_qdrant_api" value="<?php echo esc_attr($bctai_qdrant_api) ?>" style="border: 0px;background: #f1f1f1;border-radius: 16px;width: 540px;height: 52px;">
        </div>

        <div class="bctai_form_row">
            <label class="bctai_label"><?php echo __('Qdrant Endpoint', 'bctai') ?></label>
            <input type="text" class="regular-text bctai_qdrant_endpoint" name="bctai_qdrant_endpoint" value="<?php echo esc_attr($bctai_qdrant_endpoint) ?>" style="border: 0px;background: #f1f1f1;border-radius: 16px;width: 540px;height: 52px;">
        </div>

        <div class="bctai_form_row">
            <label class="bctai_label"><?php echo __('Qdrant Collections', 'bctai') ?></label>
            
            <select class="wpaicg_qdrant_collections_dropdown" name="wpaicg_qdrant_default_collection"style="border:0px;max-width: 1000px;border-radius: 16px;background: #f1f1f1 url(<?php echo BCTAI_PLUGIN_URL . 'src/images/icon_arrow_select.png'?>) no-repeat right 17px center;    width: 540px;height: 52px;">
                <?php
                $default_qdrant_collection = get_option('wpaicg_qdrant_default_collection', '');
                $wpaicg_qdrant_collections = get_option('wpaicg_qdrant_collections', []); 
                
                
                
                foreach ($wpaicg_qdrant_collections as $collection):
                    if (is_array($collection) && isset($collection['name'])) {
                        $name = $collection['name'];
                        $dimension = isset($collection['dimension']) ? ' (' . esc_html($collection['dimension']) . ')' : ' (Dimension missing)';
                        $selected = ($name === $default_qdrant_collection) ? ' selected' : '';
                        echo '<option value="'.esc_attr($name).'"'.$selected.'>'.esc_html($name) . $dimension .'</option>';
                    } else {
                        $selected = ($collection === $default_qdrant_collection) ? ' selected' : '';
                        echo '<option value="'.esc_attr($collection).'"'.$selected.'>'.esc_html($collection).'</option>';
                    }
                endforeach;
                ?>
            </select>

            
            
            <!-- <button type="button" style="padding-top: 0.5em;padding-bottom: 0.5em;" class="button button-primary wpaicg_sync_qdrant_collections"></button> -->
            <button type="button" class="btn btnL bgPrimary wpaicg_sync_qdrant_collections" style="border:0px;"><span><?php echo esc_html__('Sync Collections','gpt3-ai-content-generator')?></span></button>
            <!-- <button type="button" style="padding-top: 0.5em;padding-bottom: 0.5em;" class="button wpaicg_create_new_collection_btn"><?php echo esc_html__('Create New','gpt3-ai-content-generator')?></button> -->
            <button type="button" class="btn btnL bgLightGray wpaicg_create_new_collection_btn" style="border:0px;"><span><?php echo esc_html__('Create New','gpt3-ai-content-generator')?></span></button>
            

            <div class="wpaicg_new_collection_input" style="display:none; margin-top: 20px;">
                <div class="nice-form-group">
                    <input type="text" style="width: 50%;" class="wpaicg_new_collection_name" placeholder="<?php echo esc_html__('Enter collection name','gpt3-ai-content-generator')?>">
                    <input type="number" style="width: 20%;" class="wpaicg_new_collection_dimension" value="1536" placeholder="Dimension (e.g., 1536)">
                    <button type="button" style="padding-top: 0.5em;padding-bottom: 0.5em;width: 12%;" class="button button-primary wpaicg_submit_new_collection"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
                </div>
            </div>
        </div>
    </div>

    










<h3 ><?php echo esc_html__('Instant Embedding', 'bctai') ?></h3>
<p style="margin: 0px;font-size: 16px;"><?php echo esc_html__('Enable this option to quickly embed content.', 'bctai') ?></p>
<p style="margin: 0px;font-size: 16px;"><?php echo esc_html__('select all your contents and click on Instant Embedding button.', 'bctai') ?></p>

<div class="bctai_form_row">
    <label class="bctai_label"><?php echo esc_html__('Enable', 'bctai') ?></label>
    <input <?php echo $bctai_instant_embedding == 'yes' ? ' checked' : ''; ?> type="checkbox"name="bctai_instant_embedding" value="yes">
</div>
    

    
    





<h3 ><?php echo __('Index Builder', 'bctai') ?></h3>
<p style="margin: 0px;font-size: 16px;"><?php echo __('You can create indexes using the index builder.', 'bctai') ?></p>
<p style="margin: 0px;font-size: 16px;"><?php echo __('Index Builder lets you monitor and automatically update your content.', 'bctai') ?></p>

<div class="bctai_form_row">
    <label class="bctai_label"><?php echo esc_html__('Build Index for', 'bctai') ?></label>
    <input <?php echo in_array('post', $bctai_builder_types) ? ' checked' : ''; ?> type="checkbox"name="bctai_builder_types[]" value="post">Posts
    <input <?php echo in_array('page', $bctai_builder_types) ? ' checked' : ''; ?> type="checkbox"name="bctai_builder_types[]" value="page">Pages
    <?php if (class_exists('WooCommerce')):?>
        <input <?php echo in_array('product', $bctai_builder_types) ? ' checked' : ''; ?> type="checkbox"name="bctai_builder_types[]" value="product">Products
    <?php endif;?>
</div>


   


<h1 style="font: normal normal 900 24px/35px Noto Sans KR; margin:0px;"><?php echo __('Google Setting', 'bctai') ?></h1>



<div class="bctai_form_row">
    <label class="bctai_label"><?php echo __('Google API Key','bctai')?></label>
    <div id="bctai_message" style="display: none;"></div>
    <input type="text" class="regular-text bctai_google_api_key" value="<?php echo esc_html($bctai_google_api_key)?>" name="bctai_google_api_key" style="border: 0px;background: #f1f1f1;border-radius: 16px;width: 540px;height: 52px;">
    <a class="bctai_help_link" style="color: #8040AD;"href="https://cloud.google.com/text-to-speech" target="_blank"><?php echo esc_html__('Get Your Api Key', 'bctai') ?></a>
</div>

<div class="bctai_form_row">
    <label class="bctai_label"><?php echo __('Sync Google Voices','bctai')?></label>
    <button class="button button-primary bctai_sync_google_voices" type="button" style="width: 187px;
    height: 58px;
    background: #8040AD 0% 0% no-repeat padding-box;
    border-radius: 16px;
    opacity: 1;
    border: 0px;
    font: normal normal bold 14px / 20px Noto Sans KR;
    letter-spacing: 0px;
    color: #FFFFFF;"><?php echo __('SYNC','bctai')?></button>
</div>




<div class="submit_wrap"><input type="submit" value="<?php echo __('SAVE','bctai') ?>" name="bctai_submit" class="bct_submit_input"></div>


<script>
    jQuery(document).ready(function ($) {
        // $('.bgPrimary').click(function(){
        //     //alert("dslskl");

        // });

        $('#bctai_chat_provider').change(function(){
            if(this.value == 'OpenAI'){
                $('.OpenAI_wrap').css('display','block');
                $('.OpenRouter_wrap').css('display','none');
            }else if(this.value == 'OpenRouter'){
                $('.OpenRouter_wrap').css('display','block');
                $('.OpenAI_wrap').css('display','none');
            }

        });

        $('.bctai_vector_db_provider').change(function(){
            if(this.value == 'pinecone'){
                $('.Pinecone_wrap').css('display','block');
                $('.Qdrant_wrap').css('display','none');
            }else if(this.value == 'qdrant'){
                $('.Qdrant_wrap').css('display','block');
                $('.Pinecone_wrap').css('display','none');
            }

        });


        $('.wpaicg_submit_new_collection').click(function() {
            //alert("click");
            var collectionName = $('.wpaicg_new_collection_name').val().trim();
            var dimension = parseInt($('.wpaicg_new_collection_dimension').val().trim(), 10); // Parse as integer
            var apiKey = $('input[name="bctai_qdrant_api"]').val().trim();
            var endpoint = $('input[name="bctai_qdrant_endpoint"]').val().trim();
            if (!collectionName) {
                alert('Please enter a collection name.');
                return;
            }
            if (!dimension || isNaN(dimension)) {
                alert('Please enter a valid dimension as a number.');
                return;
            }
            if (dimension < 128 || dimension > 65536) {
                alert('Please enter a dimension between 128 and 65536.');
                return;
            }

            wpaicgLoading($('.wpaicg_submit_new_collection'));

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php') ?>',
                type: 'POST',
                data: {
                    action: 'wpaicg_create_collection',
                    nonce: '<?php echo wp_create_nonce('wpaicg-ajax-nonce') ?>',
                    collection_name: collectionName,
                    dimension: dimension,
                    api_key: apiKey,
                    endpoint: endpoint
                },
                success: function(response) {
                    var result = JSON.parse(response);
                    if (result.status && result.status.error) {
                        alert('Error: ' + result.status.error);
                    } else {
                        // Add the new collection to the dropdown
                        $('.wpaicg_qdrant_collections_dropdown').append($('<option>', {
                            value: collectionName,
                            text: collectionName + ' (' + dimension + ')'
                        })).val(collectionName);

                        // Update collections in the options table
                        var updatedCollections = $('.wpaicg_qdrant_collections_dropdown option').map(function() {
                            return $(this).val();
                        }).get();

                        $.post('<?php echo admin_url('admin-ajax.php') ?>', {
                            action: 'wpaicg_save_qdrant_collections',
                            nonce: '<?php echo wp_create_nonce('wpaicg-ajax-nonce') ?>',
                            collections: updatedCollections
                        });

                        $('.wpaicg_new_collection_input').hide();
                        $('.wpaicg_new_collection_name').val('');
                        $('.wpaicg_sync_qdrant_collections').click();
                    }
                    wpaicgRmLoading($('.wpaicg_submit_new_collection'));
                },
                error: function() {
                    alert('Error: Unable to create collection.');
                    wpaicgRmLoading($('.wpaicg_submit_new_collection'));
                }
            });
        });




        $('.wpaicg_create_new_collection_btn').click(function() {
            $('.wpaicg_new_collection_input').show();
        });



        function updateCollectionsDropdown(collections) {
            var dropdown = $('.wpaicg_qdrant_collections_dropdown');
            if (collections.length > 0) {
                dropdown.empty().show();
                $.each(collections, function(index, collection) {
                    var displayText = collection.name + ' (' + collection.dimension + ')';
                    dropdown.append($('<option></option>').attr('value', collection.name).text(displayText));
                });
            } else {
                dropdown.hide();
            }
        }

        

        function wpaicgLoading(btn){
            btn.attr('disabled','disabled');
            if(!btn.find('spinner').length){
                btn.append('<span class="spinner"></span>');
            }
            btn.find('.spinner').css('visibility','unset');
        }
        function wpaicgRmLoading(btn){
            btn.removeAttr('disabled');
            btn.find('.spinner').remove();
        }



        $('.wpaicg_sync_qdrant_collections').click(function() {
            //alert("wpaicg_sync_qdrant_collections click");
            var btn = $(this);
            // get api key
            var apiKey = $('input[name="bctai_qdrant_api"]').val().trim();

            //alert(apiKey);

            if (!apiKey) {
                alert('Please enter a valid API key.');
                return;
            }
            // get endpoint
            var endpoint = $('input[name="bctai_qdrant_endpoint"]').val().trim();
            if (!endpoint) {
                alert('Please enter a valid endpoint.');
                return;
            }
            wpaicgLoading(btn);

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php') ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'wpaicg_show_collections',
                    nonce: '<?php echo wp_create_nonce('wpaicg-ajax-nonce') ?>',
                    api_key: apiKey,
                    endpoint: endpoint
                },
                success: function(response) {
                    console.log(response);
                    if (response.success) {
                        var collections = response.data;
                        alert("response.success");
                        updateCollectionsDropdown(collections);

                        // Save the collections to the options table
                        $.post('<?php echo admin_url('admin-ajax.php') ?>', {
                            action: 'wpaicg_save_qdrant_collections',
                            nonce: '<?php echo wp_create_nonce('wpaicg-ajax-nonce') ?>',
                            collections: collections
                        });
                    } else {
                        // Handle error response
                        alert('Error: ' + (response.data.error || 'Unable to sync collections.'));
                    }
                    wpaicgRmLoading(btn);
                },
                error: function(xhr, status, error) {
                    // Handle low-level HTTP error
                    alert('Error: ' + (error || 'Unable to sync collections.'));
                    wpaicgRmLoading(btn);
                }
            });
        });



        $('.wpaicg_sync_openrouter_models').click(function() {
            //alert("dkdkdkdkdk");
            var btn = $(this);
            var icon = btn.find('svg'); // Select the SVG icon
            var originalContent = btn.html(); // Save the original button content

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php')?>',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    action: 'wpaicg_sync_openrouter_models',
                    nonce: '<?php echo wp_create_nonce('wpaicg_sync_openrouter_models'); ?>'
                },
                beforeSend: function() {
                    icon.addClass('rotating');
                },
                success: function(response) {
                    console.log(response);
                    icon.removeClass('rotating');
                    btn.html(originalContent);
                    if (response.success) {
                        alert("Model list updated successfully");
                        //window.location.reload();
                    } else {
                        alert(response.data || 'An error occurred.');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    icon.removeClass('rotating');
                    btn.html(originalContent);
                    alert('Error: ' + errorThrown);
                }
            });
        });









        // Function to toggle OpenAI and Azure fields
        function toggleProviderFields() {
            var provider = $('#bctai_provider').val();
            if (provider === 'Azure') {
                $('.bctai_azure_settings').show();
                $('#label_api_key').closest('.bctai_form_row').hide();
                $('#bctai_ai_model').closest('.bctai_form_row').hide();
                $('.bctai_beta_notice').hide();
            } else {
                $('.bctai_azure_settings').hide();
                $('#label_api_key').closest('.bctai_form_row').show();
                $('#bctai_ai_model').closest('.bctai_form_row').show();
            }
        }

        // Provider change event to toggle Azure and OpenAI settings visibility
        $('#bctai_provider').on('change', toggleProviderFields);

        // Call on page load to set initial state
        toggleProviderFields();



        $('#bctai_ai_model').on('change', function () {
            if ($(this).val() === 'gpt-3.5-turbo' || $(this).val() === 'gpt-3.5-turbo-16k' || $(this).val() === 'gpt-4' || $(this).val() === 'gpt-4-32k') {
                $('.bctai_sleep_time').show();
            }
            else {
                $('.bctai_sleep_time').hide();
            }
            if ($(this).val() === 'gpt-4' || $(this).val() === 'gpt-4-32k') {
                $('.bctai_beta_notice').show();
            }
            else {
                $('.bctai_beta_notice').hide();
            }
        })

        // Before saving, validate the Azure fields if Azure is selected as the provider
        $('form').on('submit', function (e) {
            var provider = $('#bctai_provider').val();
            if (provider === 'Azure') {
                var apiKey = $('#bctai_azure_api_key').val();
                var endpoint = $('#bctai_azure_endpoint').val();
                var deployment = $('#bctai_azure_deployment').val();

                if (!apiKey || !endpoint || !deployment) {
                    alert('<?php echo esc_js(__('Please fill in all the mandatory fields for Azure.', 'bctai')); ?>');
                    e.preventDefault();
                }
            }
        });


        function showMessageSuccess(message) {
            $("#bctai_message").css({
                'color': 'green',
            }).text(message).fadeIn().delay(10000).fadeOut();
        }

        function showMessageError(message) {
            $("#bctai_message").css({
                'color': 'red',
            }).text(message).fadeIn().delay(10000).fadeOut();
        }

        $('.bctai_elevenlabs_api').on('input', function(){
            if($(this).val() === ''){
                $('.bctai_elevenlabs_service').hide();
            }
            else {
                $('.bctai_elevenlabs_service').show();
            }            
        });
        $('.bctai_google_api_key').on('input', function (){
            if($(this).val() === ''){
                $('.bctai_google_service').hide();
            }
            else{
                $('.bctai_google_service').show();
            }
        });
        $('.bctai_sync_voices').click(function(){
            alert('elevenlabs voices');
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php')?>',
                data: {action: 'bctai_sync_voices',nonce: '<?php echo wp_create_nonce('bctai_sync_voices')?>'},
                dataType: 'json',
                type: 'post',
                beforeSend: function(){
                    $('.bctai_sync_voices').attr('disabled','disabled');
                    $('.bctai_sync_voices').text('<?php echo __('Syncing voices...Please wait...','bctai')?>');
                },
                success: function(res){
                    //alert(res.message)
                    $('.bctai_sync_voices').removeAttr('disabled');
                    $('.bctai_sync_voices').text('<?php echo esc_html('Sync')?>');
                    if(res.status === 'success') {
                        showMessageSuccess('<?php echo __('Voices synced successfully!','bctai')?>');
                    }else{
                        showMessageError(res.message);
                    }
                }

            })
        });
        $('.bctai_sync_models').click(function(){
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php')?>',
                data: {action: 'bctai_sync_models',nonce: '<?php echo wp_create_nonce('bctai_sync_models')?>'},
                dataType: 'json',
                type: 'post',
                beforeSend: function(){
                    $('.bctai_sync_models').attr('disabled','disabled');
                    $('.bctai_sync_models').text('<?php echo __('Syncing models...Please wait...','bctai')?>');
                },
                success: function(res){
                    $('.bctai_sync_models').removeAttr('disabled');
                    $('.bctai_sync_models').text('<?php echo __('Sync','bctai')?>');
                    if(res.status === 'success') {
                        showMessageSuccess('<?php echo __('Models synced successfully!','bctai')?>');
                    } else {
                        showMessageError(res.message);
                    }
                }

            });
        });
        $('.bctai_sync_google_voices').click(function(){  
            var apiKey = $('.bctai_google_api_key').val();
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php')?>',
                data: {
                    action: 'bctai_sync_google_voices',
                    nonce: '<?php echo wp_create_nonce('bctai_sync_google_voices')?>',
                    apikey : apiKey
                    
                },
                dataType: 'json',
                type: 'post',
                beforeSend: function(){
                    $('.bctai_sync_google_voices').attr('disabled','disabled');
                    $('.bctai_sync_google_voices').text('<?php echo __('Syncing voices...Please wait...', 'bctai')?>');
                },
                success: function(res){
                    console.log(res);
                    $('.bctai_sync_google_voices').removeAttr('disabled');
                    $('.bctai_sync_google_voices').text('<?php echo __('Sync', 'bctai')?>');
                    if(res.status === 'success'){
                        showMessageSuccess('<?php echo __('Voices synced successfully!','bctai')?>');
                    }else{
                        showMessageError(res.msg);
                    }
                }

            });
        })

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
            // var bctai_pinecone_sv = $('.bctai_pinecone_sv').val();
            var old_value = $('.bctai_pinecone_environment').attr('old-value');

            if (bctai_pinecone_api !== '') {
                $.ajax({
                    //url: 'https://controller.' + bctai_pinecone_sv + '.pinecone.io/databases', //240419 이전url
                    url: 'https://api.pinecone.io/indexes',
                    headers: { "Api-Key": bctai_pinecone_api },
                    dataType: 'json',
                    beforeSend: function () {
                        bctaiLoading(btn);
                        btn.html('<?php echo esc_html__('Syncing...', 'wp-bct-ai') ?>');
                    },


                    success: function (res) {
                        alert("<?php echo __('확인 되었습니다! 아래 index를 선택해주세요.')?>");

                        if(res.indexes && res.indexes.length){
                            var selectList = '<option value=""><?php echo esc_html__('Select Index','bctai')?></option>';
                            var formattedIndexes = [];

                            res.indexes.forEach(function(index){
                                selectList += '<option value="'+index.host+'"'+(old_value === index.host ? ' selected':'')+'>'+index.name+'</option>';
                                formattedIndexes.push({name: index.name, url: index.host});
                            });

                            $('.bctai_pinecone_environment').html(selectList);

                            // Save formatted indexes to the database
                            $.post('<?php echo admin_url('admin-ajax.php')?>', {
                                action: 'bctai_pinecone_indexes',
                                nonce: '<?php echo wp_create_nonce('bctai-ajax-nonce') ?>',
                                indexes: JSON.stringify(formattedIndexes),
                                api_key: bctai_pinecone_api
                            });
                        }
                        btn.html('<?php echo esc_html__('Sync Indexes','bctai')?>');
                        bctaiRmLoading(btn);

                        


                        




                    },
                    error: function (e) {
                        btn.html('<?php echo esc_html__('Sync Indexes', 'wp-bct-ai') ?>');
                        bctaiRmLoading(btn);
                        // alert(e.responseText);
                        alert("<?php echo __('API키를 확인해주세요.','bctai')?>");
                    }
                });
            }
            else {
                alert('<?php echo esc_html__('Please add Pinecone API key and Pinecone Environment before start sync', 'bctai') ?>')
            }
        })



    })
</script>