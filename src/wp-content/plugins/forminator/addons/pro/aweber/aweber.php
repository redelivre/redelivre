<?php

/**
 * Addon Name: Aweber
 * Version: 1.0
 * Plugin URI:  https://premium.wpmudev.org/
 * Description: Integrate Forminator Custom Forms with Aweber to get notified in real time.
 * Author: WPMU DEV
 * Author URI: http://premium.wpmudev.org
 */

define( 'FORMINATOR_ADDON_AWEBER_VERSION', '1.0' );

function forminator_addon_aweber_url() {
	return trailingslashit( forminator_plugin_url() . 'addons/pro/aweber' );
}

function forminator_addon_aweber_dir() {
	return trailingslashit( dirname( __FILE__ ) );
}

function forminator_addon_aweber_assets_url() {
	return trailingslashit( forminator_addon_aweber_url() . 'assets' );
}

require_once dirname( __FILE__ ) . '/class-forminator-addon-aweber.php';
require_once dirname( __FILE__ ) . '/class-forminator-addon-aweber-form-settings.php';
require_once dirname( __FILE__ ) . '/class-forminator-addon-aweber-form-hooks.php';
//Direct Load
Forminator_Addon_Loader::get_instance()->register( 'Forminator_Addon_Aweber' );
