<?php
/**
 * Addon Name: Mailchimp
 * Version: 1.0
 * Plugin URI:  https://premium.wpmudev.org/
 * Description: Integrate Forminator Custom Forms with Mailchimp email list easily
 * Author: WPMU DEV
 * Author URI: http://premium.wpmudev.org
 */

define( 'FORMINATOR_ADDON_MAILCHIMP_VERSION', '1.0' );

function forminator_addon_mailchimp_url() {
	return trailingslashit( forminator_plugin_url() . 'addons/pro/mailchimp' );
}

function forminator_addon_mailchimp_assets_url() {
	return trailingslashit( forminator_addon_mailchimp_url() . 'assets' );
}

require_once dirname( __FILE__ ) . '/forminator-addon-mailchimp.php';
require_once dirname( __FILE__ ) . '/forminator-addon-mailchimp-form-settings.php';
require_once dirname( __FILE__ ) . '/forminator-addon-mailchimp-form-hooks.php';
//Direct Load
Forminator_Addon_Loader::get_instance()->register( 'Forminator_Addon_Mailchimp' );
