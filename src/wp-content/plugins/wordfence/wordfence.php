<?php
/*
Plugin Name: Wordfence Security
Plugin URI: http://www.wordfence.com/
Description: Wordfence Security - Anti-virus, Firewall and Malware Scan
Author: Wordfence
Version: 7.3.2
Author URI: http://www.wordfence.com/
Network: true
*/
if(defined('WP_INSTALLING') && WP_INSTALLING){
	return;
}

if (!defined('ABSPATH')) {
	exit;
}
define('WORDFENCE_VERSION', '7.3.2');
define('WORDFENCE_BUILD_NUMBER', '1558031354');
define('WORDFENCE_BASENAME', function_exists('plugin_basename') ? plugin_basename(__FILE__) :
	basename(dirname(__FILE__)) . '/' . basename(__FILE__));

global $wp_plugin_paths;
foreach ($wp_plugin_paths as $dir => $realdir) {
	if (strpos(__FILE__, $realdir) === 0) {
		define('WORDFENCE_FCPATH', $dir . '/' . basename(__FILE__));
		define('WORDFENCE_PATH', trailingslashit($dir));
		break;
	}
}
if (!defined('WORDFENCE_FCPATH')) {
	/** @noinspection PhpConstantReassignmentInspection */
	define('WORDFENCE_FCPATH', __FILE__);
	/** @noinspection PhpConstantReassignmentInspection */
	define('WORDFENCE_PATH', trailingslashit(dirname(WORDFENCE_FCPATH)));
}


if(get_option('wordfenceActivated') != 1){
	add_action('activated_plugin','wordfence_save_activation_error'); function wordfence_save_activation_error(){ update_option('wf_plugin_act_error',  ob_get_contents()); }
}
if(! defined('WORDFENCE_VERSIONONLY_MODE')){ //Used to get version from file.
	$maxMemory = @ini_get('memory_limit');
	$last = strtolower(substr($maxMemory, -1));
	$maxMemory = (int) $maxMemory;
	
	if ($last == 'g') { $maxMemory = $maxMemory * 1024 * 1024 * 1024; }
	else if ($last == 'm') { $maxMemory = $maxMemory * 1024 * 1024; }
	else if ($last == 'k') { $maxMemory = $maxMemory * 1024; }
	
	if ($maxMemory < 134217728 /* 128 MB */ && $maxMemory > 0 /* Unlimited */) {
		if (strpos(ini_get('disable_functions'), 'ini_set') === false) {
			@ini_set('memory_limit', '128M'); //Some hosts have ini set at as little as 32 megs. 128 is the min sane amount of memory.
		}
	}

	/**
	 * Constant to determine if Wordfence is installed on another WordPress site one or more directories up in
	 * auto_prepend_file mode.
	 */
	define('WFWAF_SUBDIRECTORY_INSTALL', class_exists('wfWAF') &&
		!in_array(realpath(dirname(__FILE__) . '/vendor/wordfence/wf-waf/src/init.php'), get_included_files()));
	if (!WFWAF_SUBDIRECTORY_INSTALL) {
		require_once 'vendor/wordfence/wf-waf/src/init.php';
		if (!wfWAF::getInstance()) {
			define('WFWAF_AUTO_PREPEND', false);
			require_once 'waf/bootstrap.php';
		}
	}
	
	//Modules

	//Load
	require_once('lib/wordfenceConstants.php');
	require_once('lib/wordfenceClass.php');
	wordfence::install_actions();
}
