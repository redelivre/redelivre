<?php
/**
 * Plugin Name: Forminator Simple Addon
 * Version: 1
 * Description: Simple Addon forminator.
 * Author: WPMU DEV
 * Author URI: http://premium.wpmudev.org
 * Text Domain: external_forminator
 * Domain Path: /languages/
 */

//Direct Load
define( 'FORMINATOR_ADDON_SIMPLE_VERSION', '1.0' );

function forminator_addon_simple_url() {
	return trailingslashit( plugin_dir_url( __FILE__ ) );
}

function forminator_addon_simple_assets_url() {
	return trailingslashit( forminator_addon_simple_url() . 'assets' );
}

add_action( 'forminator_addons_loaded', 'load_forminator_addon_simple' );
function load_forminator_addon_simple() {
	require_once dirname( __FILE__ ) . '/forminator-addon-simple.php';
	if ( class_exists( 'Forminator_Addon_Loader' ) ) {
		Forminator_Addon_Loader::get_instance()->register( 'Forminator_Addon_Simple' );
	}
}


