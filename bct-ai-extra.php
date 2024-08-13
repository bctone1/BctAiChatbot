<?php
if ( ! defined( 'ABSPATH' ) ) exit;
define( 'BCTAI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BCTAI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BCTAI_NONCE_ERROR',__('Invalid nonce. This means we are unable to verify the validity of the nonce. There are couple of possible reasons for this. 1. A cache plugin is caching the nonce. 2. The nonce has expired. 3. Invalid SSL certificate. 4. Network issue. Please check and try again.',''));

require_once __DIR__.'/classes/bctai_util.php';
require_once __DIR__.'/classes/bctai_chat.php';
require_once __DIR__.'/classes/bctai_embeddings.php';
require_once __DIR__.'/classes/bctai_finetune.php';
require_once __DIR__.'/classes/bctai_audio.php';
require_once __DIR__.'/classes/bctai_hook.php';
require_once __DIR__.'/classes/bctai_google_speech.php';
require_once __DIR__.'/classes/bctai_pdf.php';
require_once __DIR__.'/classes/wpaicg_openroutermethod.php';
require_once __DIR__.'/classes/wpaicg_qdrant.php';


