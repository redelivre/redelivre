<?php
/**
 * Are you running a WordPress Multisite or Multi-Network? 
 * Are you a developer who sets up social integration for clients?
 * This file is for you! Configure using the instructions below,
 * and then drop this file into wp-content/mu-plugins. 
 */

///////////////////////////////////////////////////////////////////////////////
// Step 1: Store your license key in the code!
// By putting your license key in a constant, you eliminate this setup step
// for each site _and_ your license key will not be visible to your clients.
///////////////////////////////////////////////////////////////////////////////

@define('SHAREPRESS_MU_LICENSE_KEY',  '');

///////////////////////////////////////////////////////////////////////////////
// Step 2: Subdomain or Subfolder install?
// If you're running a subfolder-based multisite installation, then you're
// in luck! You can setup just one Facebook app, and put the App Id and Secret
// in the constants below. This is known as "MU-mode" and will mean that your
// users won't see App configuration steps in their SharePress experiences! 
//
// If, however, you're running a subdomain-based installation, then you should 
// leave these configuration options empty, and, sadly, you (or your users) 
// will need to setup a new Facebook App for each of your sites.
///////////////////////////////////////////////////////////////////////////////

@define('SHAREPRESS_MU_APP_ID',       '');
@define('SHAREPRESS_MU_APP_SECRET',   '');

///////////////////////////////////////////////////////////////////////////////
// Step 3: Filter target list
// If you use multisite for customers or clients and/or you are the admin of
// their Facebook pages you will be the one running SharePress setup, and that 
// means that your Facebook account will have access to multiple Facebook
// pages. This generally means that your customers will be able to see targets
// in SharePress that you don't want them to be able to see! This is solved
// using the filter below. If you wish to use this, uncomment the first line,
// and then configure the filter to your liking.
///////////////////////////////////////////////////////////////////////////////

// Uncomment the next line if you want to use this filtering feature:
//add_filter('sharepress_ok_page_names', '__my_sharepress_ok_page_names');

// Then configure this filter to your liking
function __my_sharepress_ok_page_names($pages) {
  // This function needs to return an array that contains the titles of
  // the Facebook pages that you want to be visible and selectable as
  // targets for scheduled posting.

  // The $pages argument is an array of Facebook page titles that the
  // active user can see by default, based on the Facebook session that
  // has been configured for use with SharePress.

  // So you have two options:
  // 1. Create a new array that has only the titles of pages that you
  // want to appear.
  // 2. Filter the $pages argument and return that, again containing
  // only the pages you want to appear.

  // So for example, if your client's Facebook page is titled 
  // "My Super Awesome Facebook Page", then you would use the following
  // snippet of code to filter his targets list to only that page:
  //
  // return array('My Super Awesome Facebook Page');
  
  // The personal wall of the user who setup SharePress will not appear
  // by default. To include this as a target option, include the pseudo
  // page name "wall" in the array returned from this function:
  //
  // return array('wall', 'My Super Awesome Facebook Page');

  // More advanced and multisite/multinetwork configuration options below:

  // $current_site is an object that represents the site that is currently
  // running this code - one of the blogs in your multisite installation.
  // You can use this variable in a multisite or multinetwork environment
  // to configure available pages on a site-by-site basis--see example code below.
  $current_site = get_current_site();

  // if ($current_site->blog_id == 1) {
  //   $pages = array('OK Page Title for Blog #1');
  // } else if ($current_site->blog_id == 2) {
  //   $pages = array('OK Page Title for Blog #2');
  // } else {
  //   // Consider having an "empty" default such that new sites don't get
  //   // to see all of the targets available simply because they haven't been
  //   // configured yet.
  //   $pages = array();
  // }

  // Simply returning an unaltered copy of the $pages argument is the same
  // as performing no filtering whatsoever.
  return $pages;
}

///////////////////////////////////////////////////////////////////////////////
// THAT'S IT! You're all done.
// You should not need to edit below this line
///////////////////////////////////////////////////////////////////////////////

class SharePressMu {
  
  private static $plugin;
  static function load() {
    $class = __CLASS__; 
    return ( self::$plugin ? self::$plugin : ( self::$plugin = new $class() ) );
  }

  private function __construct() {
    add_action('init', array($this, 'init'));
  }

  function init() {
    if (defined('SHAREPRESS_MU_APP_ID') && SHAREPRESS_MU_APP_ID && defined('SHAREPRESS_MU_APP_SECRET') && SHAREPRESS_MU_APP_SECRET) {
      define('SHAREPRESS_MU', true);
      add_filter('pre_option_sharepress_api_key', array($this, 'api_key'));
      add_filter('pre_option_sharepress_app_secret', array($this, 'app_secret')); 
    } else {
      define('SHAREPRESS_MU', false);
    }
  }

  function api_key($value) {
    return SHAREPRESS_MU_APP_ID;
  }

  function app_secret($value) {
    return SHAREPRESS_MU_APP_SECRET;
  }
  
}

SharePressMu::load();
