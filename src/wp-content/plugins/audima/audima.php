<?php
/*
   Plugin Name: Audima
   Plugin URI: https://audima.co/startnow/
   Version: 1.8.0
   Author: Audima
   Description: Insere o Player da Audima nos seus Posts
   Text Domain: audima
   Author URI:  https://audima.co/
   Author: Audima
   License: Proprietary
  */

/*
    "WordPress Plugin Template" Copyright (C) 2017 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This following part of this file is part of WordPress Plugin Template for WordPress.

    WordPress Plugin Template is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WordPress Plugin Template is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Contact Form to Database Extension.
    If not, see http://www.gnu.org/licenses/gpl-3.0.html
*/

$Audima_minimalRequiredPhpVersion = '5.4';

/**
 * Check the PHP version and give a useful error message if the user's version is less than the required version
 *
 * @return boolean true if version check passed. If false, triggers an error which WP will handle, by displaying
 * an error message on the Admin page
 */
function Audima_noticePhpVersionWrong()
{
    global $Audima_minimalRequiredPhpVersion;
    echo '<div class="updated fade">' .
        __('Error: plugin "Audima" requires a newer version of PHP to be running.', 'audima') .
        '<br/>' . __('Minimal version of PHP required: ',
            'audima') . '<strong>' . $Audima_minimalRequiredPhpVersion . '</strong>' .
        '<br/>' . __('Your server\'s PHP version: ', 'audima') . '<strong>' . phpversion() . '</strong>' .
        '</div>';
}


function Audima_PhpVersionCheck()
{
    global $Audima_minimalRequiredPhpVersion;
    if (version_compare(phpversion(), $Audima_minimalRequiredPhpVersion) < 0) {
        add_action('admin_notices', 'Audima_noticePhpVersionWrong');

        return false;
    }

    return true;
}

/**
 * function consult api
 */
function callAudimaRest($url, $data)
{
    $url = 'http://audio.audima.co/rest/audiowidget' . $url;
    $postData = json_encode($data);
    $result = wp_remote_post($url, array('body' => $postData));


    if (!is_array($result)) {
        return false;
    }

    if (isset($result['response']['code']) && $result['response']['code'] == "200") {
        $bodyResult = isset($result['body']) ? $result['body'] : "";
        return json_decode($bodyResult, true);
    }

    return false;
}
/**
 * check plano
 */
function verifyPlan()
{
    $url = get_bloginfo('wpurl');

    $bodyResult = callAudimaRest(
        '/plan',
        array(
            'url' => $url,

        )
    );

    if ($bodyResult === false) {
        return;
    }
    return $bodyResult;

}

/**
 * @return void
 */
function adminUpgrade() {
    $plan = verifyPlan();
    if ($plan['plan'] === "free") {

        echo '<div class="notice notice-warning">' .
            '<p>' .
          '<a target="_blank" href="http://audima.co/pagamento?blogid=' .  $plan['blogid']. '">Olá seja premium na audima </a> ' .
            '</p>' .
            '</div>';
    }

}

/**
 * Initialize internationalization (i18n) for this plugin.
 * References:
 *      http://codex.wordpress.org/I18n_for_WordPress_Developers
 *      http://www.wdmac.com/how-to-create-a-po-language-translation#more-631
 *
 * @return void
 */
function Audima_i18n_init()
{
    $pluginDir = dirname(plugin_basename(__FILE__));
    load_plugin_textdomain('audima', false, $pluginDir . '/languages/');
}


//////////////////////////////////
// Run initialization
/////////////////////////////////

// Initialize i18n
add_action('plugins_loadedi', 'Audima_i18n_init');




 add_action('admin_notices', 'adminUpgrade');


// Run the version check.
// If it is successful, continue with initialization for this plugin
if (Audima_PhpVersionCheck()) {
    // Only load and run the init function if we know PHP version can parse it
    include_once('audima_init.php');
    Audima_init(__FILE__);
}
