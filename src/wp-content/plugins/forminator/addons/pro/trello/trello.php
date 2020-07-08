<?php

/**
 * Addon Name: Trello
 * Version: 1.0
 * Plugin URI:  https://premium.wpmudev.org/
 * Description: Integrate Forminator Custom Forms with Trello to get notified in real time.
 * Author: WPMU DEV
 * Author URI: http://premium.wpmudev.org
 */

define( 'FORMINATOR_ADDON_TRELLO_VERSION', '1.1' );

function forminator_addon_trello_url() {
	return trailingslashit( forminator_plugin_url() . 'addons/pro/trello' );
}

function forminator_addon_trello_dir() {
	return trailingslashit( dirname( __FILE__ ) );
}

function forminator_addon_trello_assets_url() {
	return trailingslashit( forminator_addon_trello_url() . 'assets' );
}

require_once dirname( __FILE__ ) . '/class-forminator-addon-trello.php';

require_once dirname( __FILE__ ) . '/class-forminator-addon-trello-form-settings.php';
require_once dirname( __FILE__ ) . '/class-forminator-addon-trello-form-hooks.php';

require_once dirname( __FILE__ ) . '/class-forminator-addon-trello-poll-settings.php';
require_once dirname( __FILE__ ) . '/class-forminator-addon-trello-poll-hooks.php';

require_once dirname( __FILE__ ) . '/class-forminator-addon-trello-quiz-settings.php';
require_once dirname( __FILE__ ) . '/class-forminator-addon-trello-quiz-hooks.php';

//Direct Load
Forminator_Addon_Loader::get_instance()->register( 'Forminator_Addon_Trello' );
