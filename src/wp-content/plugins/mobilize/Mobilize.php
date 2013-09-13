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

define('INC_MOBILIZE', dirname(__FILE__));
define('MOBILIZE_MATERIAL_DIR', INC_MOBILIZE.'/uploads/');
define('MOBILIZE_MATERIAL_URL', get_bloginfo('url').'/wp-content/plugins/mobilize/uploads/');

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

add_action('add_meta_boxes', array('Mobilize', 'createPageTemplate'));
add_action('save_post', array('Mobilize', 'savePage'));