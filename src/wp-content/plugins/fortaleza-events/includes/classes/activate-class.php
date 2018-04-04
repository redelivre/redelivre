<?php
/** @wordpress-plugin
 * Author:            CwebConsultants
 * Author URI:        http://www.cwebconsultants.com/
 */
class Plugin_Activator {
	/* Activate Class */
	public static function activate() {
        global $wpdb;	
        //add page  All agenda
            $_all_agenda = get_option('all_agenda');
            if(!empty($_all_agenda)){
                if(FALSE === get_post_status( $_all_agenda )){
                    $login_not_exist = 1;
                }
            }else{
                    $login_not_exist = 1;
            }
           if($login_not_exist == 1){
                $page['post_type']    = 'page';
                $page['post_content'] = '';
                $page['post_parent']  = 0;
                $page['post_author']  = 1;
                $page['post_status']  = 'publish';
                $page['post_title']   = 'Programaçãos';
                $page = apply_filters('yourplugin_add_new_page', $page, 'teams');
                $pageid = wp_insert_post($page);
                add_option( 'all_agenda', $pageid, '', 'yes' );
                
            }//end
            
            //bydeafult listing columns optiom
            $listing_layout=get_option('listing_layout');
            if(empty( $listing_layout)){
                update_option('listing_layout','three_colums');
            }
            // update permalink
            
            update_option('permalink_structure','/%postname%/');

            
        }//end public class
 } // end main class