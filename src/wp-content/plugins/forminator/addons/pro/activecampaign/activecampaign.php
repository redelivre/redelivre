<?php

/**
 * Addon Name: Activecampaign
 * Version: 1.0
 * Plugin URI:  https://premium.wpmudev.org/
 * Description: Integrate Forminator Custom Forms with Activecampaign to get notified in real time.
 * Author: WPMU DEV
 * Author URI: http://premium.wpmudev.org
 */

define( 'FORMINATOR_ADDON_ACTIVECAMPAIGN_VERSION', '1.0' );

function forminator_addon_activecampaign_url() {
	return trailingslashit( forminator_plugin_url() . 'addons/pro/activecampaign' );
}

function forminator_addon_activecampaign_dir() {
	return trailingslashit( dirname( __FILE__ ) );
}

function forminator_addon_activecampaign_assets_url() {
	return trailingslashit( forminator_addon_activecampaign_url() . 'assets' );
}

require_once dirname( __FILE__ ) . '/class-forminator-addon-activecampaign.php';
require_once dirname( __FILE__ ) . '/class-forminator-addon-activecampaign-form-settings.php';
require_once dirname( __FILE__ ) . '/class-forminator-addon-activecampaign-form-hooks.php';
//Direct Load
Forminator_Addon_Loader::get_instance()->register( 'Forminator_Addon_Activecampaign' );
