<?PHP
/*
 * Plugin Name: 	WPML Widgets
 * Plugin URI: 		http://www.jeroensormani.com
 * Description: 	Easily select which widgets you want to show for which languages
 * Version: 		1.0.4
 * Author: 			Jeroen Sormani
 * Author URI: 		http://www.jeroensormani.com
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
 *	Class WPML_Widgets.
 *
 *	Main WPML Widgets class.
 *
 *	@class       WPML_Widgets
 *	@version     1.0.0
 *	@author      Jeroen Sormani
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WPML_Widgets {


	/**
	 * Instace of WPML_Widgets.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var object $instance The instance of WPML_Widgets.
	 */
	private static $instance;


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// check if WPML is activated
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) :
		    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		endif;

		if ( ! in_array( 'sitepress-multilingual-cms/sitepress.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) :
			if ( ! is_plugin_active_for_network( 'sitepress-multilingual-cms/sitepress.php' ) ) :
				add_action( 'admin_notices', array( $this, 'wpml_nag_message' ) );
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
	 * Instance.
	 *
	 * An global instance of the class. Used to retrieve the instance
	 * to use on other files/plugins/themes.
	 *
	 * @since 1.0.0
	 *
	 * @return object Instance of the class.
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) :
			self::$instance = new self();
		endif;

		return self::$instance;

	}


	/**
	 * Widget dropdown.
	 *
	 * Add a dropdown to every widget.
	 *
	 * @since 1.0.0
	 *
	 * @param	array 	$widget		Widget instance.
	 * @param 	null	$form		Return null if new fields are added.
	 * @param	array	$instance	An array of the widget's settings.
	 */
	public function ww_widget_dropdown( $widget, $form, $instance ) {

		$languages = icl_get_languages();

		?><p>
			<label for='wpml_language'><?php _e( 'Display on language:', 'wpml-widgets' ); ?> </label>
			<select id='wpml_language' name='wpml_language'><?php
			foreach ( $languages as $language ) :

				$wpml_language = isset( $instance['wpml_language'] ) ? $instance['wpml_language'] : null;
				?><option <?php selected( $language['language_code'], $wpml_language ); ?> value='<?php echo $language['language_code']; ?>'><?php
					echo $language['native_name'];
				?></option><?php

			endforeach;

			$selected = ( ! isset( $instance['wpml_language'] ) || 'all' == $instance['wpml_language'] ) ? true : false;
			?><option <?php selected( $selected ); ?> value='all'><?php _e( 'All Languages', 'wpml-widgets' ); ?></option>

			</select>
		</p><?php

	}


	/**
	 * Update widget.
	 *
	 * Update the value of the dropdown on widget update.
	 *
	 * @since 1.0.0
	 *
	 * @param	array 	$instance 		List of data.
	 * @param	array	$new_instance	New instance data.
	 * @param 	array	$old_instance	List of old isntance data.
	 * @param 	array	$this2			Class of ..?.
	 * @return	array					List of modified instance.
	 */
	public function ww_widget_update( $instance, $new_instance, $old_instance, $this2 ) {

		$instance['wpml_language'] = $_POST['wpml_language'];

		return $instance;

	}


	/**
	 * Display widget.
	 *
	 * Filter the widgets.
	 *
	 * @since 1.0.0
	 *
	 * @param	array 	$instance 	List of widget data.
	 * @param	array	$widget		Widget data.
	 * @param 	array	$args		List of args.
	 * @return	array				List of modified widget instance.
	 */
	public function ww_display_widget( $instance, $widget, $args ) {

		if ( isset( $instance['wpml_language'] ) && $instance['wpml_language'] != ICL_LANGUAGE_CODE && $instance['wpml_language'] != 'all' ) :
			return false;
		endif;

		return $instance;

	}


	/**
	 * Nag message.
	 *
	 * Display a nag message when WPML is not actiavted.
	 *
	 * @since 1.0.3
	 */
	public function wpml_nag_message() {

		// Check if message should be dismissed
		if ( isset( $_GET['dismiss_wpml_widgets_nag'] ) && 1 == $_GET['dismiss_wpml_widgets_nag'] ) :
			update_option( 'ignore_wpml_widgets_nag', 'yes' );
		endif;

		if ( 'yes' != get_option( 'ignore_wpml_widgets_nag' ) ) :
			?><div class='updated'>
				<p><?php
					_e( 'Hey, I see WPML is not activated, please activate it before using WPML Widgets.', 'wpml-widgets' );
					?><a class='alignright installer-dismiss-nag' href='<?php echo esc_url( add_query_arg( 'dismiss_wpml_widgets_nag', true ) ); ?>' data-repository='wpml'><?php
						_e( 'Dismiss', 'wpml-widgtes' );
					?></a>
				</p>
			</div><?php
		endif;

	}


}


/**
 * The main function responsible for returning the WPML_Widgets object.
 *
 * Use this function like you would a global variable, except without needing to declare the global.
 *
 * Example: <?php WPML_Widgets()->method_name(); ?>
 *
 * @since 1.0.3
 *
 * @return object WPML_Widgets class object.
 */
if ( ! function_exists( 'WPML_Widgets' ) ) :

 	function WPML_Widgets() {
		return WPML_Widgets::instance();
	}

endif;

WPML_Widgets();
