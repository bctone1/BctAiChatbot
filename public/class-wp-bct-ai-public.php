<?php

/**
 * The public-facing functionality of the plugin.
 * 
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 * 
 * @package     Wp_BCT_Ai
 * @subpackage  Wp_BCT_Ai/public
 * @author      bctone <bct@bctone.kr>
 */
class Wp_BCT_Ai_Public {

    /**
     * The ID of this plugin.
     * 
     * @since   0.0.1
     * @access  private
     * @var     string      $plugin_name        The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     * 
     * @since   0.0.1
     * @access  private
     * @var     string      $version        The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     * 
     * @since   0.0.1
     * @param   string      $plugin_name        The name of the plugin.
     * @param   string      $version        The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     * 
     * @since   0.0.1
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         * 
         * An instance of this class should be passed to the run() function
         * defined in Wp_BCT_Ai_Loader as all of the hooks are defined
         * in that particular class.
         * 
         * The Wp_BCT_Ai_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

         //wp_enqueue_style( $this->plugin_name, plugin_dir_url( __DIR__ ) . 'src/css/wp-bct-ai-public.css', array(), $this->version, 'all' );
         wp_enqueue_style('main-css', plugin_dir_url( __DIR__ ) . 'src/css/main.css', array(), $this->version, 'all' );
         wp_enqueue_style( 'jquery-ui', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css' );


    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     * 
     * @since   0.0.1
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         * 
         * An instance of this class should be passed to the run() function
         * defined in Wp_BCT_Ai_Loader as all of the hooks are defined
         * in that particular class.
         * 
         * The Wp_BCT_Ai_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        //wp_enqueue_script( $this->plugin_name, plugin_dir_url( __DIR__ ) . 'src/js/wp-bct-ai-public.js', array( 'jquery' ), $this->version, false );
        //wp_enqueue_script( 'jquery' );

        //wp_enqueue_script( 'jquery-ui', '//code.jquery.com/ui/1.12.1/jquery-ui.js', array('jquery'), '1.12.1', true );
        
        wp_enqueue_script('jquery-ui', plugin_dir_url( __DIR__ ) . 'src/js/jquery.js', array(), $this->version, 'all' );
         
         
    }


}