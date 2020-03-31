<?php
/*
* Plugin Name:       WP Crowdfunding
* Plugin URI:        https://www.themeum.com/product/wp-crowdfunding-plugin/
* Description:       The Ultimate Fundraising and Backer Plugin for WordPress.
* Version:           2.0.2
* Author:            Themeum
* Author URI:        https://themeum.com
* Text Domain:       wp-crowdfunding
* Requires at least: 4.5
* Tested up to:      5.3
* License:           GPL-2.0+
* License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
*/

defined( 'ABSPATH' ) || exit;

/**
* Support for Multi Network Site
*/
if( !function_exists('is_plugin_active_for_network') ){
    require_once(ABSPATH . '/wp-admin/includes/plugin.php');
}

/**
* @Type
* @Version
* @Directory URL
* @Directory Path
* @Plugin Base Name
*/
define('WPCF_FILE', __FILE__);
define('WPCF_VERSION', '2.0.2');
define('WPCF_DIR_URL', plugin_dir_url( WPCF_FILE ));
define('WPCF_DIR_PATH', plugin_dir_path( WPCF_FILE ));
define('WPCF_BASENAME', plugin_basename( WPCF_FILE ));

/**
* Load Text Domain Language
*/
add_action('init', 'wpcf_language_load');
function wpcf_language_load(){
    load_plugin_textdomain('wp-crowdfunding', false, basename(dirname( WPCF_FILE )).'/languages/');
}

if (!function_exists('wpcf_function')) {
    function wpcf_function() {
        require_once WPCF_DIR_PATH . 'includes/Functions.php';
        return new \WPCF\Functions();
    }
}

if (!class_exists( 'Crowdfunding' )) {
    require_once WPCF_DIR_PATH . 'includes/Crowdfunding.php';
    new \WPCF\Crowdfunding();
}