<?php

/*
    Plugin Name: Mobilize
    Plugin URI: http://www.ethymos.com.br
    Description: 
    Author: Ethymos
    Version: 1.0
    Author URI: http://ethymos.com.br
    Text Domain: http://ethymos.com.br
    Domain Path:
 */

///////////////
// Constants //
///////////////

$upload_dir = wp_upload_dir();
define('INC_MOBILIZE', dirname(__FILE__));
define('MOBILIZE_MATERIAL_DIR', $upload_dir['basedir'].'/mobilize/');
define('MOBILIZE_MATERIAL_URL', $upload_dir['baseurl'].'/mobilize/');

//////////////////
// Dependences  //
//////////////////

require INC_MOBILIZE.'/includes/wideimage/WideImage.php';
require INC_MOBILIZE.'/includes/smartView.php';
require INC_MOBILIZE.'/includes/functions-mobilize.php';
require INC_MOBILIZE.'/vendor.php';

/////////////
// Actions //
/////////////

add_action('add_meta_boxes_page', array('Mobilize', 'createPageTemplate'));
add_action('save_post', array('Mobilize', 'savePage'));
