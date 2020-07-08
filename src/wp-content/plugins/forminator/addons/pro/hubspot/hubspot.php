<?php

/**
 * Addon Name: HubSpot
 * Version: 1.0
 * Plugin URI:  https://premium.wpmudev.org/
 * Description: Integrate Forminator Custom Forms with HubSpot to get notified in real time.
 * Author: WPMU DEV
 * Author URI: http://premium.wpmudev.org
 */

define( 'FORMINATOR_ADDON_HUBSPOT_VERSION', '1.0' );

function forminator_addon_hubspot_url() {
	return trailingslashit( forminator_plugin_url() . 'addons/pro/hubspot' );
}

function forminator_addon_hubspot_dir() {
	return trailingslashit( dirname( __FILE__ ) );
}

function forminator_addon_hubspot_assets_url() {
	return trailingslashit( forminator_addon_hubspot_url() . 'assets' );
}

require_once dirname( __FILE__ ) . '/class-forminator-addon-hubspot.php';
require_once dirname( __FILE__ ) . '/class-forminator-addon-hubspot-form-settings.php';
require_once dirname( __FILE__ ) . '/class-forminator-addon-hubspot-form-hooks.php';
//Direct Load
Forminator_Addon_Loader::get_instance()->register( 'Forminator_Addon_HubSpot' );
