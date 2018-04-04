<?php
/** @wordpress-plugin
 * Author:            CwebConsultants
 * Author URI:        http://www.cwebconsultants.com/
 */

class Plugin_Deactivator {
	/* De-activate Class */
	public static function deactivate() {
		/* Delete Table And Post type*/
		global $wpdb;
                // delete all_agenda page
                $_all_agenda = get_option('all_agenda');
                if(!empty($_all_agenda)){
                    wp_delete_post($_all_agenda);
                    delete_option( 'all_agenda', $_all_agenda, '', 'yes' );
                }
                $listing_layout=get_option('listing_layout');
                if(!empty( $listing_layout)){
                    delete_option('listing_layout');
                }
            //$wpdb->query( "DROP TABLE IF EXISTS wp_creator_short_data" );

                // delete highlights terms
               
            }
}
