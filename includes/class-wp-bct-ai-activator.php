<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Fired during plugin activation.
 * 
 * This class defines all code necessary to run during the plugin's activation.
 * 
 * @since       0.0.1
 * @package     Wp_BCT_Ai_Activator
 * @subpackage  Wp_BCT_Ai_Activator/includes
 */
class Wp_BCT_Ai_Activator {

    /**
     * Short Description. (use period)
     * 
     * Long Description.
     * 
     * @since   0.0.1
     */
    public static function activate() {
		self::createTable();
	}

    public static function createTable()
	{
		global $wpdb;

		$bctaiTable = $wpdb->prefix . 'bctai';
		if($wpdb->get_var("SHOW TABLES LIKE '$bctaiTable'") != $bctaiTable) {
			$charset_collate = $wpdb->get_charset_collate();
			$sql = "CREATE TABLE $bctaiTable (
						ID mediumint(11) NOT NULL AUTO_INCREMENT,
						name text NOT NULL,
						temperature float NOT NULL,
						max_tokens float NOT NULL,
						top_p float NOT NULL,
						best_of float NOT NULL,
						frequency_penalty float NOT NULL,
						presence_penalty float NOT NULL,
						img_size text NOT NULL,
						api_key text NOT NULL,
						bctai_language VARCHAR(255) NOT NULL,
						bctai_add_img BOOLEAN NOT NULL,
						bctai_add_intro BOOLEAN NOT NULL,
						bctai_add_conclusion BOOLEAN NOT NULL,
						bctai_add_tagline BOOLEAN NOT NULL,
						bctai_add_faq BOOLEAN NOT NULL,
						bctai_add_keywords_bold BOOLEAN NOT NULL,
						bctai_number_of_heading INT NOT NULL,
						bctai_modify_headings BOOLEAN NOT NULL,
						bctai_heading_tag VARCHAR(10) NOT NULL,
						bctai_writing_style VARCHAR(255) NOT NULL,
						bctai_writing_tone VARCHAR(255) NOT NULL,
						bctai_target_url VARCHAR(255) NOT NULL,
						bctai_target_url_cta VARCHAR(255) NOT NULL,
						bctai_cta_pos VARCHAR(255) NOT NULL,
						added_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
						modified_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
						PRIMARY KEY  (ID)
					) $charset_collate;";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
            $sampleData = [
                'name'						=> 'bctai_settings',
                'temperature' 				=> '0.7',
                'max_tokens' 				=> '1500',
                'top_p' 					=> '0.01',
                'best_of' 					=> '1',
                'frequency_penalty' 		=> '0.01',
                'presence_penalty' 			=> '0.01',
                'img_size' 					=> '512x512',
                'api_key' 					=> 'sk..',
                'bctai_language' 			=> 'en',
                'bctai_add_img' 			=> 'false',
                'bctai_add_intro' 			=> 'false',
                'bctai_add_conclusion' 		=> 'false',
                'bctai_add_tagline' 		=> 'false',
                'bctai_add_faq' 			=> 'false',
                'bctai_add_keywords_bold' 	=> 'false',
                'bctai_number_of_heading' 	=>  5,
                'bctai_modify_headings' 	=> 'false',
                'bctai_heading_tag' 		=> 'h1',
                'bctai_writing_style' 		=> 'infor',
                'bctai_writing_tone' 		=> 'formal',
                'bctai_cta_pos' 			=> 'beg',
                'added_date' 				=> gmdate('Y-m-d H:i:s'),
                'modified_date'				=> gmdate('Y-m-d H:i:s')

            ];

            $result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $bctaiTable WHERE name = %s", 'bctai_settings' ) );

            if(!empty($result->name)) {
                $wpdb->update(
                    $bctaiTable,
                    $sampleData,
                    [
                        'name'			=> 'bctai_settings'
                    ],
                    [
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s'
                    ],
                    [
                        '%s'
                    ]
                );
            } else {
                $wpdb->insert(
                    $bctaiTable,
                    $sampleData,
                    [
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s'
                    ]
                );
            }
		}
	}
}