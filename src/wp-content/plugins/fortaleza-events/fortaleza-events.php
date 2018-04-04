<?php
error_reporting(0);
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * Dashboard. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           fortaleza-events
 *
 * @wordpress-plugin
 * Plugin Name:       fortaleza-events
 * Description:       This plugin is developed to get the events through API
 * Version:           1.0.0(new)
 * Author:            cWebco WP Plugin Team
 * Author URI:        http://www.cWebConsultants.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       fortaleza-events
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/* Plugin Name */
$cwebPluginName="fortaleza-events";

/* Use Domain as the folder name */
$PluginTextDomain="fortaleza-events";


/**
 * The code that runs during plugin activation.
*/
function activate_this_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/classes/activate-class.php';
	Plugin_Activator::activate();
}
/**
 * The code that runs during plugin deactivation.
*/
function deactivate_this_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/classes/deactive-class.php';
	Plugin_Deactivator::deactivate();
}

/* Register Hooks For Start And Deactivate */
register_activation_hook( __FILE__, 'activate_this_plugin' );
register_deactivation_hook( __FILE__, 'deactivate_this_plugin' );

/**
 * The core plugin class that is used to define internationalization,
*/
require plugin_dir_path( __FILE__ ) . 'includes/classes/classCweb.php';

/*Include the Files in which we define the sortcodes for front End */
require plugin_dir_path( __FILE__ ) . 'public/short-codes.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_plugin_name() {
	$plugin = new cWebClass();
	$plugin->run();
}
run_plugin_name();

/* Constant */
define('CWEB_FS_PATH1', plugin_dir_path(__FILE__) );
define('CWEB_WS_PATH1', plugin_dir_url(__FILE__) );

/*
 * Include Custom Feild Files
 */

//Declares Common Fucntion File 
require plugin_dir_path( __FILE__ ) . 'includes/function/fucntions.php';
