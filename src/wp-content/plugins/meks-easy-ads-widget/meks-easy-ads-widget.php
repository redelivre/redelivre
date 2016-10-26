<?php 
/*
Plugin Name: Meks Easy Ads Widget
Plugin URI: http://mekshq.com
Description: Display unlimited number of ads inside your WordPress widget. Specify custom ads size, randomize, rotate, enjoy!
Author: Meks
Version: 1.2.2
Author URI: http://mekshq.com
Text Domain: meks-easy-ads-widget
Domain Path: /languages
*/


/*  Copyright 2013  Meks  (email : support@mekshq.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define ('MKS_ADS_WIDGET_URL', trailingslashit(plugin_dir_url(__FILE__)));
define ('MKS_ADS_WIDGET_DIR', trailingslashit(plugin_dir_path(__FILE__)));
define ('MKS_ADS_WIDGET_VER', '1.2.2');

/* Initialize Widget */
if(!function_exists('mks_ads_widget_init')):
	function mks_ads_widget_init() {
		require_once(MKS_ADS_WIDGET_DIR.'inc/class-ads-widget.php');
		register_widget('MKS_Ads_Widget');
	}
endif;

add_action('widgets_init','mks_ads_widget_init');

?>