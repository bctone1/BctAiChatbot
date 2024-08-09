<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Define the internationalization functionality.
 * 
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 * 
 * @since       0.0.1
 * @package     Wp_BCT_Ai
 * @subpackage  Wp_BCT_Ai/includes
 */

 class Wp_BCT_Ai_i18n {

    /**
     * Load the plugin text domain for translation.
     * 
     * @since   0.0.1
     */
    public function load_plugin_textdoamin() {

        load_plugin_textdoamin(
            'wp-bct-ai',
            false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
        );

    }

 }