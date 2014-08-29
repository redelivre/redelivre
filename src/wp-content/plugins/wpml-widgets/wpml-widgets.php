<?PHP
/*
Plugin Name: WPML Widgets
Plugin URI: http://www.jeroensormani.com
Description: Easily select which widgets you want to show for which languages
Version: 1.0.2
Author: Jeroen Sormani
Author URI: http://www.jeroensormani.com
*/

/*
 * Copyright Jeroen Sormani
 *
 *     This file is part of WPML Widgets,
 *     a plugin for WordPress.
 *
 *     WPML Widgets is free software:
 *     You can redistribute it and/or modify it under the terms of the
 *     GNU General Public License as published by the Free Software
 *     Foundation, either version 3 of the License, or (at your option)
 *     any later version.
 *
 *     WPML Widgets is distributed in the hope that
 *     it will be useful, but WITHOUT ANY WARRANTY; without even the
 *     implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 *     PURPOSE. See the GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with WordPress. If not, see <http://www.gnu.org/licenses/>.
 */


/**
 *	Class Wpml_Widgets
 *
 *	Main WPML Widgets class
 *
 *	@class       Wpml_Widgets
 *	@version     1.0.0
 *	@author      Jeroen Sormani
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Wpml_Widgets {
	
	
	/**
	 * __construct function.
	 */
	public function __construct() {
				
		// check if WPML is activated
		
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) :
		    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		endif;

		
		if ( ! in_array( 'sitepress-multilingual-cms/sitepress.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) :
			if ( ! is_plugin_active_for_network( 'sitepress-multilingual-cms/sitepress.php' ) ) :
				return;
			endif;
		endif;
			
		// Add dropdown to widgets
		add_action( 'in_widget_form', array( $this, 'ww_widget_dropdown' ), 10, 3 );
		
		// Update dropdown value on widget update
		add_filter( 'widget_update_callback', array( $this, 'ww_widget_update' ), 10, 4 );
		
		// Filter widgets by language
		add_filter( 'widget_display_callback', array( $this, 'ww_display_widget' ), 10, 3 );
	}

	
	/**
	 * Widget dropdown.
	 *
	 * Add a dropdown to every widget.
	 */
	public function ww_widget_dropdown( $widget, $form, $instance ) {

		$languages = icl_get_languages();
		
		?><p><label for='wpml_language'><?php _e( 'Display on language:', 'wpml-widgets' ); ?> </label>
		<select id='wpml_language' name='wpml_language'><?php
		foreach ( $languages as $language ) :

			$selected = ( $language['language_code'] == $instance['wpml_language'] ) ? 'SELECTED' : null;
			?><option <?php echo $selected; ?> value='<?php echo $language['language_code']; ?>'><?php echo $language['native_name']; ?></option><?php
			
		endforeach;
		
		$selected = ( 'all' == $instance['wpml_language'] || !isset( $instance['wpml_language'] ) ) ? 'SELECTED' : null;
		?>
			<option <?php echo $selected; ?> value='all'><?php _e( 'All Languages', 'wpml-widgets' ); ?></option>
		</select></p>
		<?php

	}
	
	
	/**
	 * Update widget.
	 *
	 * Update the value of the dropdown on widget update.
	 */
	public function ww_widget_update( $instance, $new_instance, $old_instance, $this2 ) {
		
		$instance["wpml_language"] = $_POST["wpml_language"];		

		return $instance;
		
	}
	
	
	/**
	 * Display widget.
	 *
	 * Filter the widgets.
	 */
	public function ww_display_widget( $instance, $widget, $args ) {
	
		if ( isset( $instance['wpml_language'] ) && $instance['wpml_language'] != ICL_LANGUAGE_CODE && $instance['wpml_language'] != 'all' ) :
			return false;
		endif;
		
		return $instance;
		
	}	
	
}
new Wpml_Widgets();


?>