<?php
/**
 * Remove options on plugin delete.
 *
 * @package WP Captcha
 * @version 1.0.0
 */

/**
 * @uses int $blog_id
 * @uses object $wpdb
 * @uses delete_option()
 * @uses is_multisite()
 * @uses switch_to_blog()
 * @uses wp_get_sites()
 */

if( !defined('WP_UNINSTALL_PLUGIN') ) {
	
  	die( 'You are not allowed to call this page directly.' );
}

global $blog_id, $wpdb;

// Remove settings for all sites in multisite
if( is_multisite() ) {
	
  	$blogs = wp_get_sites();
  
  	foreach($blogs as $blog) {
	  
		switch_to_blog( $blog->blog_id );
		
		delete_option('_wpc_captcha_settings');
  	}
  
} else {
  
	delete_option('_wpc_captcha_settings');
}