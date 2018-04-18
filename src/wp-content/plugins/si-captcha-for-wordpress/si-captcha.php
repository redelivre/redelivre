<?php
/*
Plugin Name: SI Captcha Anti-Spam
Plugin URI: https://wordpress.org/plugins/si-captcha-for-wordpress/
Description: Adds Secure Image CAPTCHA to WordPress pages for comments, login, registration, lost password, BuddyPress register, bbPress register, wpForo register, bbPress New Topic and Reply to Topic Forms, Jetpack Contact Form, and WooCommerce checkout. In order to post comments, login, or register, users will have to pass the CAPTCHA test. Prevents spam from automated bots. Compatible with Akismet and Multisite Network Activate.
Author: fastsecure
Author URI: http://www.642weather.com/weather/scripts.php
Text Domain: si-captcha
Domain Path: /languages
License: GPLv2 or later
Version: 3.0.3
*/

$si_captcha_version = '3.0.3';

/*  Copyright (C) 2008-2017 Mike Challis  (http://www.642weather.com/weather/contact_us.php)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}


if (!class_exists('siCaptcha')) {

class siCaptcha {

    public $si_captcha_version;
    public $si_captcha_add_script = false;
    private $si_captcha_add_reg = false;
    private $si_captcha_add_jetpack = false;
    private $si_captcha_networkwide = false;
    private $si_captcha_on_comments = false;
    private $si_captcha_checkout_validated = false;

function si_captcha_admin_menu() {

    add_options_page( __('SI Captcha Anti-Spam settings', 'si-captcha'), __('SI Captcha Anti-Spam', 'si-captcha'), 'manage_options', __FILE__,array(&$this,'si_captcha_options_page'));

}

function si_captcha_plugin_row_meta( $links, $file ) {
    if ( $file == plugin_basename( __FILE__ ) ) {
	    $links[] = '<a href="https://wordpress.org/support/plugin/si-captcha-for-wordpress" target="_new">'.__('Support', 'si-captcha').'</a>';
		$links[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=KXJWLPPWZG83S" target="_new">'.__('Donate', 'si-captcha').'</a>';
	}
	return $links;
}


function si_captcha_plugin_action_links( $links, $file ) {
    //Static so we don't call plugin_basename on every plugin row.
	static $this_plugin;
	if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);

	if ( $file == $this_plugin ){
	     $settings_link = '<a href="options-general.php?page=si-captcha-for-wordpress/si-captcha.php">' . __('Settings', 'si-captcha') . '</a>';
	     array_unshift( $links, $settings_link );
    }
	return $links;
} // end function si_captcha_plugin_action_links


function si_captcha_init() {
         global $si_captcha_opt, $si_captcha_networkwide;

         load_plugin_textdomain('si-captcha', false, dirname(plugin_basename(__FILE__)).'/languages' );

 	add_filter( 'plugin_row_meta', array($this,'si_captcha_plugin_row_meta'), 10, 2 );

  // is it networkwide installed?
 if ( ! function_exists( 'is_plugin_active_for_network' ) )
     require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

  if ( is_multisite() && is_plugin_active_for_network('si-captcha-for-wordpress/si-captcha.php') )
     $si_captcha_networkwide = true;

  //$this->si_captcha_get_options();
  $this->si_captcha_determine_current_page();


    if ( isset($si_captcha_opt['enable_session']) && $si_captcha_opt['enable_session'] != 'true') {
     // add javascript (conditionally to footer)
     add_action( 'wp_footer', array($this,'si_captcha_add_script'));
    }


     if ($si_captcha_opt['external_css'] == 'false') {
         add_action('wp_head', array($this, 'si_captcha_head'));
         add_action('login_head', array($this, 'si_captcha_head'));
     }

     // comment form
     if ($si_captcha_opt['comment'] == 'true') {
       if( ! is_user_logged_in() ) {
               add_action('comment_form_after_fields', array($this, 'si_captcha_comment_form'), 99);
               add_action('comment_form', array($this, 'si_captcha_comment_form_legacy'), 99); // legacy themes
	   } else {
	           add_filter ('comment_form_field_comment', array($this, 'si_captcha_comment_form_logged_in'), 11);
               add_action('comment_form', array($this, 'si_captcha_comment_form_legacy'), 99); // legacy themes
	   }
        add_action('wp_footer', array($this, 'si_captcha_add_script'));
        add_filter('preprocess_comment', array($this, 'si_captcha_comment_post'), 1);
     }

     // register form
     // if ($si_captcha_opt['register'] == 'true' && isset($this->is_reg)) { // was not working on bbPress [bbp-register] shortcode 
     if ($si_captcha_opt['register'] == 'true' ) {
        add_action('woocommerce_register_form', array($this, 'si_captcha_register_form'), 70);
        add_filter('woocommerce_registration_errors', array($this, 'si_captcha_register_post'), 10, 3);
        add_action('register_form', array($this, 'si_captcha_register_form'), 99);
        add_filter('registration_errors', array($this, 'si_captcha_register_post'), 10, 3);
        add_action('login_footer', array($this, 'si_captcha_add_script'), 10);
     }

     // login form
     if ($si_captcha_opt['login'] == 'true' ) {
        add_action('login_form', array($this, 'si_captcha_login_form' ), 99);
        add_filter('login_form_middle', array($this, 'si_captcha_inline_login_form'), 99);
        add_action('woocommerce_login_form' ,array($this, 'si_captcha_wc_login_form' ), 99);		
	    add_filter('authenticate', array($this, 'si_captcha_check_login_captcha'), 15);
        add_action('login_footer', array($this, 'si_captcha_add_script'), 10);
     }

     // lost passwordform
     if ($si_captcha_opt['lostpwd'] == 'true' && isset($this->is_lostpassword)) {
 	    add_action('lostpassword_form', array( $this, 'si_captcha_lostpassword_form'), 99);
        add_action('woocommerce_lostpassword_form', array($this, 'si_captcha_lostpassword_form'), 99);
	    add_action('lostpassword_post', array($this, 'si_captcha_lostpassword_post'), 10);		
        add_action('login_footer', array($this, 'si_captcha_add_script'), 10);		
     }

     // woocommerce checkout form
     if ( ! is_user_logged_in() ) {
           // show captcha for woocommerce checkout but only when the setting is enabled and not logged in
 		   add_action('woocommerce_checkout_after_order_review', array($this, 'si_captcha_wc_checkout_form'), 99);
           add_action('woocommerce_after_checkout_validation', array($this, 'si_captcha_wc_checkout_post') );
     }

     // wpForo registration
     if(function_exists('is_wpforo_page') && $si_captcha_opt['wpforo_register'] == 'true' ){
	   if(is_wpforo_page()){
         add_action('register_form', array($this, 'si_captcha_register_form'), 99);
         add_filter('registration_errors', array($this, 'si_captcha_register_post'), 10, 3);
         add_action('wp_footer', array($this, 'si_captcha_add_script'));
       }
     }

     // bp register - create an account
     if ($si_captcha_opt['bp_register'] == 'true') {
        add_action('bp_account_details_fields', array($this, 'si_captcha_bp_register_form'), 99);
        add_action('bp_signup_validate', array($this, 'si_captcha_bp_signup_validate'), 10);
     }

     // wp multisite
     if ($si_captcha_opt['ms_register'] == 'true' && isset($this->is_signup)) {
       // register for multisite
        add_action('signup_extra_fields', array($this, 'si_captcha_ms_register_form'));
        add_action('signup_blogform', array($this, 'si_captcha_ms_register_form'));
       // logged in user signup new site
	    add_filter('wpmu_validate_user_signup', array($this, 'si_captcha_mu_signup_validate'));
        add_filter('wpmu_validate_blog_signup', array($this, 'si_captcha_mu_site_signup_validate'));
     }

     // bbPress New Topic, Reply to Topic
     if(class_exists( 'bbPress' )) {
            if ($si_captcha_opt['bbpress_topic'] == 'true') {
                add_action('bbp_theme_after_topic_form_content', array($this, 'si_captcha_bbpress_topic_form'));
                add_action('bbp_new_topic_pre_extras', array($this, 'si_captcha_bbpress_topic_validate'));
                add_action('wp_footer', array($this, 'si_captcha_add_script'));
            }
            if ($si_captcha_opt['bbpress_reply'] == 'true') {
                add_action('bbp_theme_after_reply_form_content', array($this, 'si_captcha_bbpress_topic_form'));
                add_action('bbp_new_reply_pre_extras', array($this, 'si_captcha_bbpress_topic_validate'));
                add_action('wp_footer', array($this, 'si_captcha_add_script'));
            }
     }

     // jetpack contact form
      if ($si_captcha_opt['jetpack'] == 'true') {
        add_filter('jetpack_contact_form_is_spam', array($this, 'si_captcha_jetpack_validate'));
        add_filter('the_content', array($this, 'si_captcha_jetpack_form'));
        add_filter('widget_text', array($this, 'si_captcha_jetpack_form'), 0);
        add_filter('widget_text', 'shortcode_unautop');
        add_filter('widget_text', 'do_shortcode');
        add_shortcode('si-captcha', array($this, 'si_captcha_jetpack_shortcode'));
        add_action('wp_footer', array($this, 'si_captcha_add_script'));
     }

} // end function si_captcha_init


function si_captcha_get_options() {
  global $si_captcha_opt, $si_captcha_option_defaults, $si_captcha_networkwide;

  $default_position = ( function_exists('bp_loaded') ) ? 'label-required-input' : 'input-label-required';

  $si_captcha_option_defaults = array(
         'donated' => 'false',        // a checkbox that makes the donate button go away
         'bypass_comment' => 'true',  // enable No comment form CAPTCHA for logged in users
         'comment' => 'true',         // enable on comment form
         'login' => 'false',          // enable on login form
         'register' => 'true',        // enable on register form
         'bp_register' => 'true',     // enable on buddypress register form
         'ms_register' => 'true',     // enable on multisite register form
         'wpforo_register' => 'true', // enable on wpForo Forum register form
         'lostpwd'  => 'true',        // enable on lost password form
         'wc_checkout' => 'true',     // enable on WooCommerce checkout form
         'jetpack' => 'true',         // enable on Jetpack contact form
         'bbpress_topic' => 'true',   // enable on bbPress New Topic form
         'bbpress_reply' => 'true',   // enable on bbPress Reply to Topic form
         'enable_session' => 'false',
         'captcha_small' => 'true',   // enable small CAPTCHA size
         'comment_label_position' => $default_position,
         'external_css' => 'false',   // enable use external css for CAPTCHA divs
         'network_individual_on' => 'false',  // Allow Multisite network activated sites to have individual FS CAPTCHA settings
         'label_captcha' =>    '',
         'error_spambot' =>    '',
         'error_incorrect' =>    '',
         'error_empty' =>    '',
         'error_token' =>    '',
         'error_unreadable' =>    '',
         'error_cookie' =>    '',
         'error_error' =>    '',
         'required_indicator' => ' *',
         'tooltip_captcha' =>  '',
         'tooltip_refresh' =>  '',

 );

     $network_individual_on = false;
     if ($si_captcha_networkwide && get_current_blog_id() > 1) {
        $si_captcha_main_opt = get_site_option('si_captcha_anti_spam');
        if ($si_captcha_main_opt['network_individual_on'] == 'true')
              $network_individual_on = true; // this is like a global that is also used in admin settings
     }

  // get the options
   if ( $si_captcha_networkwide && get_current_blog_id() == 1 ) {
             // multisite with network activation, this is main site
             add_site_option('si_captcha_anti_spam', $si_captcha_option_defaults, '', 'yes');
             $si_captcha_opt = get_site_option('si_captcha_anti_spam');
  } else  if ( $si_captcha_networkwide && get_current_blog_id() > 1 ) {
          if ( $network_individual_on ) {
             // multisite with network activation individual site control
             add_option('si_captcha_anti_spam', $si_captcha_option_defaults, '', 'yes');
             $si_captcha_opt = get_option('si_captcha_anti_spam');
          } else {
             // multisite with network activation master site control
             add_site_option('si_captcha_anti_spam', $si_captcha_option_defaults, '', 'yes');
             $si_captcha_opt = get_site_option('si_captcha_anti_spam');
          }
  } else {
          // no multisite
          add_option('si_captcha_anti_spam', $si_captcha_option_defaults, '', 'yes');
          $si_captcha_opt = get_option('si_captcha_anti_spam');
  }

  // array merge incase this version has added new options
  $si_captcha_opt = array_merge($si_captcha_option_defaults, $si_captcha_opt);

  // strip slashes on get options array
  foreach($si_captcha_opt as $key => $val) {
           $si_captcha_opt[$key] = stripslashes($val);
  }

  if ( defined('XMLRPC_REQUEST') && XMLRPC_REQUEST )
      $si_captcha_opt['login'] = 'false'; // always disable captcha on xmlrpc login connections

} // end function si_captcha_get_options


function si_captcha_options_page() {
  global $si_captcha_opt, $si_captcha_option_defaults, $si_captcha_version, $si_captcha_networkwide;
  global $si_captcha_dir, $si_captcha_url, $si_captcha_url_ns, $si_captcha_dir_ns;

  require_once('si-captcha-admin.php');

}// end function si_captcha_options_page


function si_captcha_check_requires() {
  global $si_captcha_dir, $si_captcha_add_script;

  $ok = 'ok';
  // Test for some required things, print error message if not OK.
  if ( !extension_loaded('gd') || !function_exists('gd_info') ) {
       echo '<p style="color:maroon">'.__('ERROR: si-captcha.php plugin: GD image support not detected in PHP!', 'si-captcha').'</p>';
       echo '<p>'.__('Contact your web host and ask them to enable GD image support for PHP.', 'si-captcha').'</p>';
      $ok = 'no';
  }
  if ( !function_exists('imagepng') ) {
       echo '<p style="color:maroon">'.__('ERROR: si-captcha.php plugin: imagepng function not detected in PHP!', 'si-captcha').'</p>';
       echo '<p>'.__('Contact your web host and ask them to enable imagepng for PHP.', 'si-captcha').'</p>';
      $ok = 'no';
  }
  if ( !file_exists("$si_captcha_dir/securimage.php") ) {
       echo '<p style="color:maroon">'.__('ERROR: si-captcha.php plugin: securimage.php not found.', 'si-captcha').'</p>';
       $ok = 'no';
  }
  if ($ok == 'no')  return false;
  $si_captcha_add_script = true;
  return true;
} // end function si_captcha_check_requires


// displays the CAPTCHA in the forms
function si_captcha_captcha_html($label = 'si_image', $form_id = 'com', $no_echo = false) {
  global $si_captcha_url, $si_captcha_dir, $si_captcha_url_ns, $si_captcha_dir_ns, $si_captcha_opt;

  $capt_disable_sess = 0;
   if ($si_captcha_opt['enable_session'] != 'true')
     $capt_disable_sess = 1;

  // url for no session captcha image
  $securimage_show_url = $si_captcha_url .'/securimage_show.php?';
  $securimage_size = 'width="175" height="60"';
  if($si_captcha_opt['captcha_small'] == 'true' || $label == 'si_image_side_login' ) {
    $securimage_show_url .= 'si_sm_captcha=1&amp;';
    $securimage_size = 'width="132" height="45"';
  }

  $parseUrl = parse_url($si_captcha_url);
  $securimage_url = $parseUrl['path'];

  $securimage_show_url .= 'si_form_id=' .$form_id;

  if($capt_disable_sess) {
     // clean out old captcha no session temp files
    $this->si_captcha_clean_temp_dir($si_captcha_dir_ns, 30);
    // pick new prefix token
    $prefix_length = 16;
    $prefix_characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz';
    $prefix = '';
    $prefix_count = strlen($prefix_characters);
    while ($prefix_length--) {
        $prefix .= $prefix_characters[mt_rand(0, $prefix_count-1)];
    }
    $securimage_show_rf_url = $securimage_show_url . '&amp;prefix=';
    $securimage_show_url .= '&amp;prefix='.$prefix;
  }

  $html = '';

  $html .= '<img id="'.$label.'" src="'.$securimage_show_url.'" '.$securimage_size.' alt="';
  $html .= ($si_captcha_opt['tooltip_captcha'] != '') ? esc_attr( $si_captcha_opt['tooltip_captcha'] ) : esc_attr(__('CAPTCHA', 'si-captcha'));
  $html .= '" title="';
  $html .= ($si_captcha_opt['tooltip_captcha'] != '') ? esc_attr( $si_captcha_opt['tooltip_captcha'] ) : esc_attr(__('CAPTCHA', 'si-captcha'));
  $html .= '" />'."\n";
  if($capt_disable_sess) {
        $html .= '    <input id="si_code_'.$form_id.'" name="si_code_'.$form_id.'" type="hidden"  value="'.esc_attr($prefix).'" />'."\n";
  }

  $html .= '    <div id="si_refresh_'.$form_id.'">'."\n";
  $html .= '<a href="#" rel="nofollow" title="';
  $html .= ($si_captcha_opt['tooltip_refresh'] != '') ? esc_attr( $si_captcha_opt['tooltip_refresh'] ) : esc_attr(__('Refresh', 'si-captcha'));
  if($capt_disable_sess) {
    $html .= '" onclick="si_captcha_refresh(\''.$label.'\',\''.$form_id.'\',\''.$securimage_url.'\',\''.$securimage_show_rf_url.'\'); return false;">'."\n";
  }else{
    $html .= '" onclick="document.getElementById(\''.$label.'\').src = \''.$securimage_show_url.'&amp;sid=\''.' + Math.random(); return false;">'."\n";
  }
  $html .= '      <img class="si_captcha_refresh" src="'.$si_captcha_url.'/images/refresh.png" width="22" height="20" alt="';
  $html .= ($si_captcha_opt['tooltip_refresh'] != '') ? esc_attr( $si_captcha_opt['tooltip_refresh'] ) : esc_attr(__('Refresh', 'si-captcha'));
  $html .= '" onclick="this.blur();" /></a>
  </div>
  ';

  if ( $no_echo ) return $html;
  echo $html;

} // end function si_captcha_captcha_html


function si_captcha_start_session() {
 // a PHP session cookie is set so that the captcha can be remembered and function
 // this has to be set before any header output
 // echo "before starting session si captcha";
  if( !isset( $_SESSION ) ) { // play nice with other plugins
   if ( !defined('XMLRPC_REQUEST') ) { // buddypress fix
      //set the $_SESSION cookie into HTTPOnly mode for better security
      if (version_compare(PHP_VERSION, '5.2.0') >= 0)  // supported on PHP version 5.2.0  and higher
        @ini_set("session.cookie_httponly", 1);
      session_cache_limiter ('private, must-revalidate');
      session_start();
      //echo "session started si captcha"; exit;
   }
  }

} // function si_captcha_start_session

// needed for making temp directories for attachments and captcha session files
function si_captcha_init_temp_dir($dir) {
    $dir = trailingslashit( $dir );
    // make the temp directory
	wp_mkdir_p( $dir );
	//@chmod( $dir, 0733 );
	$htaccess_file = $dir . '.htaccess';
	if ( !file_exists( $htaccess_file ) ) {
	   if ( $handle = @fopen( $htaccess_file, 'w' ) ) {
		   fwrite( $handle, "Deny from all\n" );
		   fclose( $handle );
	   }
    }
    $php_file = $dir . 'index.php';
	if ( !file_exists( $php_file ) ) {
       	if ( $handle = @fopen( $php_file, 'w' ) ) {
		   fwrite( $handle, '<?php //do not delete ?>' );
		   fclose( $handle );
     	}
	}
} // end function si_captcha_init_temp_dir

// needed for emptying temp directories for attachments and captcha session files
function si_captcha_clean_temp_dir($dir, $minutes = 60) {
    // deletes all files over xx minutes old in a temp directory
  	if ( ! is_dir( $dir ) || ! is_readable( $dir ) || ! is_writable( $dir ) )
		return false;

	$count = 0;
	if ( $handle = @opendir( $dir ) ) {
		while ( false !== ( $file = readdir( $handle ) ) ) {
			if ( $file == '.' || $file == '..' || $file == '.htaccess' || $file == 'index.php')
				continue;

			$stat = @stat( $dir . $file );
			if ( ( $stat['mtime'] + $minutes * 60 ) < time() ) {
			    @unlink( $dir . $file );
				$count += 1;
			}
		}
		closedir( $handle );
	}
	return $count;
} // end function si_captcha_clean_temp_dir


function si_captcha_admin_head() {
 // only load this header css on the admin settings page for this plugin
if(isset($_GET['page']) && is_string($_GET['page']) && preg_match('/si-captcha.php$/',$_GET['page']) ) {
?>
<!-- begin SI Captcha Anti-Spam - admin settings page header css -->
<style type="text/css">
div.fs-star-holder { position: relative; height:19px; width:100px; font-size:19px;}
div.fs-star {height: 100%; position:absolute; top:0px; left:0px; background-color: transparent; letter-spacing:1ex; border:none;}
.fs-star1 {width:20%;} .fs-star2 {width:40%;} .fs-star3 {width:60%;} .fs-star4 {width:80%;} .fs-star5 {width:100%;}
.fs-star.fs-star-rating {background-color: #fc0;}
.fs-star img{display:block; position:absolute; right:0px; border:none; text-decoration:none;}
div.fs-star img {width:19px; height:19px; border-left:1px solid #fff; border-right:1px solid #fff;}
.fs-notice{background-color:#ffffe0;border-color:#e6db55;border-width:1px;border-style:solid;padding:5px;margin:5px 5px 20px;-moz-border-radius:3px;-khtml-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;}
.fscf_left {clear:left; float:left;}
.fscf_img {margin:0 10px 10px 0;}
.fscf_tip {text-align:left; display:none;color:#006B00;padding:5px;}
</style>
<!-- end SI Captcha Anti-Spam - admin settings page header css -->
<?php
  } // end if(isset($_GET['page'])

} // end function si_captcha_admin_head

function si_captcha_head(){
  global $si_captcha_opt;
  echo '<script type="text/javascript" src="'.plugins_url('si-captcha-for-wordpress/captcha/si_captcha.js?ver='.time()).'"></script>'."\n";

 // only load this css on the blog pages where login/register could be
if( $si_captcha_opt['external_css'] == 'true' )
  return;

?>
<!-- begin SI CAPTCHA Anti-Spam - login/register form style -->
<style type="text/css">
<?php
$styles = $this->si_captcha_get_styles();
   foreach ($styles as $line)  {
        echo $line . "\n";
   }
?>
</style>
<!-- end SI CAPTCHA Anti-Spam - login/register form style -->
<?php
} // end function si_captcha_head


// this function adds the captcha to the comment form
function si_captcha_comment_form() {
    global $si_captcha_opt, $si_captcha_on_comments;

    // skip the captcha if user is logged in and the settings allow
    if (is_user_logged_in() && $si_captcha_opt['bypass_comment'] == 'true')
               // logged in user can bypass captcha
               return;

    if ($si_captcha_on_comments)
      return;

// the captch html
// Test for some required things, print error message right here if not OK.
if ($this->si_captcha_check_requires()) {

echo '

<div ';
echo ($si_captcha_opt['captcha_small'] == 'true') ? 'class="si_captcha_small"' : 'class="si_captcha_large"';
echo '>';
$this->si_captcha_captcha_html('si_image_com','com');
echo '</div>

';
echo '<p id="si_captcha_code_p">
';
echo $this->si_captcha_comment_label_html();
echo '</p>

';

}
    // prevent double captcha fields
    $si_captcha_on_comments = true;
    return true;
} // end function si_captcha_comment_form

// this function adds the captcha to the comment form on old themes
function si_captcha_comment_form_legacy() {
    global $si_captcha_opt, $si_captcha_on_comments;

    // skip the captcha if user is logged in and the settings allow
    if (is_user_logged_in() && $si_captcha_opt['bypass_comment'] == 'true')
               // logged in user can bypass captcha
               return;

    if ($si_captcha_on_comments)
      return;

echo '
<div id="captchaImgDiv">
';

// the captch html
// Test for some required things, print error message right here if not OK.
if ($this->si_captcha_check_requires()) {

echo '
<div ';
echo ($si_captcha_opt['captcha_small'] == 'true') ? 'class="si_captcha_small"' : 'class="si_captcha_large"';
echo '>';
$this->si_captcha_captcha_html('si_image_com','com');
echo '</div>

';
echo '<p id="si_captcha_code_p">
';
echo $this->si_captcha_comment_label_html();
echo '</p>
</div>
';

// rearrange submit button display order
//if ($si_captcha_opt['captcha_rearrange'] == 'true') {
     print  <<<EOT
      <script type='text/javascript'>
          var sUrlInput = document.getElementById("comment");
                  var oParent = sUrlInput.parentNode;
          var sSubstitue = document.getElementById("captchaImgDiv");
                  oParent.appendChild(sSubstitue, sUrlInput);
      </script>
            <noscript>
          <style type='text/css'>#submit {display:none;}</style><br />
EOT;
  echo '           <input name="submit" type="submit" id="submit-alt" tabindex="6" value="'.__('Submit Comment', 'si-captcha').'" />
          </noscript>
  ';

//}


}else{
 echo '</div>';
}

    // prevent double captcha fields
    $si_captcha_on_comments = true;
    return true;
} // end function si_captcha_comment_form_legacy

// this function adds the captcha to the comment form when user is logged in
function si_captcha_comment_form_logged_in($comment_field) {
    global $si_captcha_opt, $si_captcha_on_comments;

    // skip the captcha if user is logged in and the settings allow
    if (is_user_logged_in() && $si_captcha_opt['bypass_comment'] == 'true')
               // logged in user can bypass captcha
               return $comment_field;

    if ($si_captcha_on_comments)
          return $comment_field;

// the captch html
// Test for some required things, print error message right here if not OK.
if ($this->si_captcha_check_requires()) {
$html = '

<div ';
$html .= ($si_captcha_opt['captcha_small'] == 'true') ? 'class="si_captcha_small"' : 'class="si_captcha_large"';
$html .= '>';
$html .= $this->si_captcha_captcha_html('si_image_com','com', true);
$html .= '</div>
<br />
';
$html .= '<p>
';
$html .= $this->si_captcha_comment_label_html();
$html .= '</p>

';
}
    // prevent double captcha fields
    $si_captcha_on_comments = true;
    return $comment_field . "\n" . $html;
} // end function si_captcha_comment_form_logged_in


function si_captcha_comment_label_html() {
    global $si_captcha_opt;

$label_string = '<label for="si_captcha_code" >';
$label_string .= ($si_captcha_opt['label_captcha'] != '') ? $si_captcha_opt['label_captcha'] : __('CAPTCHA Code', 'si-captcha');
$label_string .= '</label>';
$required_string = '<span class="required">'.$si_captcha_opt['required_indicator']."</span>\n";
$input_string = '<input id="si_captcha_code" name="si_captcha_code" type="text" />
';

$html = '';

 if ($si_captcha_opt['comment_label_position'] == 'label-required-input' || $si_captcha_opt['comment_label_position'] == 'left'  ) { // buddypress (label-required-input)(label left)
      $html .= $label_string . $required_string . $input_string; // BP
 } else if ($si_captcha_opt['comment_label_position'] == 'label-required-linebreak-input' ||  $si_captcha_opt['comment_label_position'] == 'top' ) {
     $html .= $label_string . $required_string .'<br />'. $input_string; // regular WP - twenty ten
 } else if ($si_captcha_opt['comment_label_position'] == 'label-input-required' ||  $si_captcha_opt['comment_label_position'] == 'right' ) {
      $html .= $label_string . $input_string . $required_string; // suffusion
 } else if ($si_captcha_opt['comment_label_position'] == 'input-label-required' ) {
     $html .= $input_string . $label_string . $required_string; // regular WP
 } else {
      $html .= $input_string . $label_string . $required_string;  // regular WP
 }

 return $html;
} // end function si_captcha_comment_label_html


// this function checks the captcha posted with comments
function si_captcha_comment_post($comment) {
    global $si_captcha_opt;

    // skip the captcha if user is logged in and the settings allow
    if (is_user_logged_in() && $si_captcha_opt['bypass_comment'] == 'true') {
           // skip captcha
           return $comment;
    }

    // skip captcha for comment replies from admin menu
    if ( isset($_POST['action']) && $_POST['action'] == 'replyto-comment' &&
    ( check_ajax_referer( 'replyto-comment', '_ajax_nonce', false ) || check_ajax_referer( 'replyto-comment', '_ajax_nonce-replyto-comment', false )) ) {
          // skip captcha
          return $comment;
    }

    // Skip captcha for trackback or pingback
    if ( $comment['comment_type'] != '' && $comment['comment_type'] != 'comment' ) {
               // skip captcha
               return $comment;
    }

   $validate_result = $this->si_captcha_validate_code('com', 'unlink');
   if($validate_result != 'valid') {
       $error = ($si_captcha_opt['error_error'] != '') ? $si_captcha_opt['error_error'] : __('ERROR', 'si-captcha');
       wp_die( "<strong>$error</strong>: $validate_result", $error, array( 'back_link' => true ) ); // back_link makes go back link

   }

   return $comment;
} // end function si_captcha_comment_post


// this function adds the captcha to the login form
function si_captcha_login_form() {
   global $si_captcha_opt;

   if ($si_captcha_opt['login'] != 'true') {
        return true; // captcha setting is disabled for login
   }

// Test for some required things, print error message right here if not OK.
if ($this->si_captcha_check_requires()) {
// the captcha html - login form
echo '

<div ';
echo ($si_captcha_opt['captcha_small'] == 'true') ? 'class="si_captcha_small"' : 'class="si_captcha_large"';
echo '>';
$this->si_captcha_captcha_html('si_image_log','log');
echo '</div>

<p class="si_captcha_code">
 <label for="si_captcha_code">';
  echo ($si_captcha_opt['label_captcha'] != '') ? $si_captcha_opt['label_captcha'] : __('CAPTCHA Code', 'si-captcha');
  echo '<br />
<input id="si_captcha_code" name="si_captcha_code" class="input" type="text" value="" /></label>
</p>


';
}

  return true;
} //  end function si_captcha_login_form


// this function adds the captcha to the login form
function si_captcha_wc_login_form() {
   global $si_captcha_opt;

   if ($si_captcha_opt['login'] != 'true') {
        return true; // captcha setting is disabled for login
   }

// Test for some required things, print error message right here if not OK.
if ($this->si_captcha_check_requires()) {
// the captcha html - login form
echo '

<div ';
echo ($si_captcha_opt['captcha_small'] == 'true') ? 'class="si_captcha_small"' : 'class="si_captcha_large"';
echo '>';
$this->si_captcha_captcha_html('si_image_log','log');
echo '</div>
<br />
<p class="form-row">
 <label for="si_captcha_code">';
  echo ($si_captcha_opt['label_captcha'] != '') ? $si_captcha_opt['label_captcha'] : __('CAPTCHA Code', 'si-captcha');
  echo '</label>
<input id="si_captcha_code" name="si_captcha_code" class="input-text" type="text" value="" />
</p>

';
}

  return true;
} //  end function si_captcha_login_form

// this function adds the captcha to the buddypress inline login form
function si_captcha_inline_login_form() {
  global $si_captcha_opt;

   if ($si_captcha_opt['login'] != 'true') {
        return true; // captcha setting is disabled for login
   }

// Test for some required things, print error message right here if not OK.
if ($this->si_captcha_check_requires()) {

// the captcha html - buddypress sidebar login form
$html = '
<div class="si_captcha_inline_login">
<div class="si_captcha_small">
';
  $html .= $this->si_captcha_captcha_html('si_image_side_login','log', true);
$html .= '
</div>

<label for="captcha_code_side_login">';
  $html .= ($si_captcha_opt['label_captcha'] != '') ? $si_captcha_opt['label_captcha'] : __('CAPTCHA Code', 'si-captcha');
  $html .= '</label>
<input id="captcha_code_side_login" name="si_captcha_code" class="input" type="text" value="" />
</div>

';
}

  return $html;
} //  end function si_captcha_inline_login_form


// this is checking login post for captcha validation on WP and woocommerce
function si_captcha_check_login_captcha($user) {
    global $si_captcha_opt;

     if ( isset($this->is_login) && empty($_POST['log']) && empty($_POST['pwd'])) {
            // woocommerce uses 'logon' and 'password' post vars instead of 'log' and 'pwd', so check this on main wp login page only
            // this is main wp login page and the page just loaded, or form not filled out, don't bother trying to validate captcha now
	 		return $user;
     }

		// if the $user object itself is a WP_Error object, we simply append
		// errors to it, otherwise we create a new one.
		$errors = is_wp_error($user) ? $user : new WP_Error();

        // begin SI CAPTCHA check
        $validate_result = $this->si_captcha_validate_code('log', 'unlink');
        if($validate_result != 'valid') {
            $print_error = ($si_captcha_opt['error_error'] != '') ? $si_captcha_opt['error_error'] : __('ERROR', 'si-captcha');
			$errors->add('sicaptcha-error', "<strong>$print_error</strong>: $validate_result");

			// invalid captcha detected, the returned $user object should be a WP_Error object
			$user = is_wp_error($user) ? $user : $errors;

			// do not allow WordPress to try authenticating the user, either using cookie or username/password pair
			remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
			remove_filter('authenticate', 'wp_authenticate_cookie', 30, 3);
		}

		return $user;
} // end function si_captcha_check_login_captcha



// this function adds the captcha to the woocommerce checkout form
function si_captcha_wc_checkout_form() {
    global $si_captcha_opt;

if ($si_captcha_opt['wc_checkout'] == 'true' ) {

// Test for some required things, print error message right here if not OK.
if ($this->si_captcha_check_requires()) {
// the captcha html -  form
echo '
<div class="clear"></div>

<div ';
echo ($si_captcha_opt['captcha_small'] == 'true') ? 'class="si_captcha_small"' : 'class="si_captcha_large"';
echo '>';
$this->si_captcha_captcha_html('si_image_checkout','checkout');
echo '</div>

<br />
 <label for="si_captcha_code">';
  echo ($si_captcha_opt['label_captcha'] != '') ? $si_captcha_opt['label_captcha'] : __('CAPTCHA Code', 'si-captcha');
  echo '<br />
<input id="si_captcha_code" name="si_captcha_code" class="input-text" type="text" value="" /></label>

';
}

}

return true;
} // end function si_captcha_wc_checkout_form


// this function checks the captcha posted with woocommerce checkout page
function si_captcha_wc_checkout_post() {
    global $si_captcha_dir, $si_captcha_dir_ns, $si_captcha_opt, $si_captcha_checkout_validated;

   if ($si_captcha_opt['wc_checkout'] == 'true' ) {
      $validate_result = $this->si_captcha_validate_code('checkout', 'unlink');
      if($validate_result != 'valid') {
               wc_add_notice( $validate_result, 'error' );
      }  else {
               $si_captcha_checkout_validated = true;
      }
   } else {
           $si_captcha_checkout_validated = true;   // always allow registering during checkot
   }
   return;
} // function si_captcha_wc_checkout_post


// this function adds the captcha to the register form
function si_captcha_register_form() {
   global $si_captcha_opt, $si_captcha_add_reg;

  if ( $si_captcha_add_reg )     // prevent double reg captcha fields woocommerce 2
          return true;

// Test for some required things, print error message right here if not OK.
if ($this->si_captcha_check_requires()) {
// the captcha html - register form
echo '

<div ';
echo ($si_captcha_opt['captcha_small'] == 'true') ? 'class="si_captcha_small"' : 'class="si_captcha_large"';
echo '>';
$this->si_captcha_captcha_html('si_image_reg','reg');
echo '</div>

<p>
 <label for="si_captcha_code">';
  echo ($si_captcha_opt['label_captcha'] != '') ? $si_captcha_opt['label_captcha'] : __('CAPTCHA Code', 'si-captcha');
  echo '<br />
<input id="si_captcha_code" name="si_captcha_code" class="input" type="text" value="" /></label>
</p>

';
      // prevent double captcha fields woocommerce 2
  $si_captcha_add_reg = true;
}


  return true;
} // end function si_captcha_register_form


// this function checks the captcha posted with registration page
function si_captcha_register_post( $errors = '' ) {
   global $si_captcha_dir, $si_captcha_dir_ns, $si_captcha_opt, $si_captcha_checkout_validated;

   if ( ! is_wp_error( $errors ) )
          $errors = new WP_Error();

   if ($si_captcha_checkout_validated)
       return $errors; // skip because already validated a captcha at woocommerce checkout, checked the box "Create an account"


   $validate_result = $this->si_captcha_validate_code('reg', 'unlink');
   if($validate_result != 'valid') {
       $error = ($si_captcha_opt['error_error'] != '') ? $si_captcha_opt['error_error'] : __('ERROR', 'si-captcha');
       $errors->add('si_captcha_error', "<strong>$error</strong>: $validate_result");
   }
   return $errors;
} // end function si_captcha_register_post


// this function adds the captcha to the bp register form
function si_captcha_bp_register_form() {
   global $bp, $si_captcha_opt;

   if ($si_captcha_opt['register'] != 'true') {
        return true; // captcha setting is disabled for registration
   }


// the captcha html - bp register form
if (!empty($bp->signup->errors['si_captcha_field']))
    echo '<div class="error">'. $bp->signup->errors['si_captcha_field']. '</div>';


// Test for some required things, print error message right here if not OK.
if ($this->si_captcha_check_requires()) {
// the captcha html - register form
echo '

<div ';
echo ($si_captcha_opt['captcha_small'] == 'true') ? 'class="si_captcha_small"' : 'class="si_captcha_large"';
echo '>';
$this->si_captcha_captcha_html('si_image_reg','reg');
echo '</div>
<br />
<p>
 <label for="si_captcha_code">';
  echo ($si_captcha_opt['label_captcha'] != '') ? $si_captcha_opt['label_captcha'] : __('CAPTCHA Code', 'si-captcha');
  echo '<br />
<input id="si_captcha_code" name="si_captcha_code" class="input" type="text" value="" /></label>
</p>

';
}

  return true;
} // end function si_captcha_bp_register_form


// this function checks the captcha posted with BuddyPress registration page
function si_captcha_bp_signup_validate() {
   global $bp, $si_captcha_opt;

   $validate_result = $this->si_captcha_validate_code('reg', 'unlink');
   if($validate_result != 'valid')
        $bp->signup->errors['si_captcha_field'] = $validate_result;
   return;
} // end function si_captcha_bp_signup_validate


// this function adds the captcha to the multisite register form
function si_captcha_ms_register_form( $errors ) {
   global $si_captcha_opt;

   if ($si_captcha_opt['ms_register'] != 'true')
        return true; // captcha setting is disabled for multisite registration


   if ( $errmsg = $errors->get_error_message('si_captcha_error') )
			echo '<p class="error">' . $errmsg . '</p>';

// the captcha html - ms register form
// Test for some required things, print error message right here if not OK.
if ($this->si_captcha_check_requires()) {
// the captcha html - register form
echo '

<div ';
echo ($si_captcha_opt['captcha_small'] == 'true') ? 'class="si_captcha_small"' : 'class="si_captcha_large"';
echo '>';
$this->si_captcha_captcha_html('si_image_reg','reg');
echo '</div>

<p>
 <label for="si_captcha_code">';
  echo ($si_captcha_opt['label_captcha'] != '') ? $si_captcha_opt['label_captcha'] : __('CAPTCHA Code', 'si-captcha');
  echo '<br />
<input id="si_captcha_code" name="si_captcha_code" class="input" type="text" value="" /></label>
</p>

';
}

  return true;
} // end function si_captcha_ms_register_form


// this function adds the captcha to the lostpassword form
function si_captcha_lostpassword_form() {
   global $si_captcha_opt;

// Test for some required things, print error message right here if not OK.
if ($this->si_captcha_check_requires()) {
// the captcha html - lostpassword form
echo '

<div ';
echo ($si_captcha_opt['captcha_small'] == 'true') ? 'class="si_captcha_small"' : 'class="si_captcha_large"';
echo '>';
$this->si_captcha_captcha_html('si_image_reg','reg');
echo '</div>

<p>
 <label>';
  echo ($si_captcha_opt['label_captcha'] != '') ? $si_captcha_opt['label_captcha'] : __('CAPTCHA Code', 'si-captcha');
  echo '<br />
<input id="si_captcha_code" name="si_captcha_code" class="input" type="text" value="" /></label>
</p>

';
}

  return true;
} // end function si_captcha_lostpassword_form


// this function checks the captcha posted with lost password page
function si_captcha_lostpassword_post($errors = '') {
  global $si_captcha_dir, $si_captcha_dir_ns, $si_captcha_opt;

 if ( ! is_wp_error( $errors ) )
        $errors = new WP_Error();

   $validate_result = $this->si_captcha_validate_code('reg', 'unlink');
   if($validate_result != 'valid') {
       $error = ($si_captcha_opt['error_error'] != '') ? $si_captcha_opt['error_error'] : __('ERROR', 'si-captcha');

       if ( isset($_POST['wc_reset_password']) && isset($_POST['_wp_http_referer']) ) {
               // woocommerce  /my-account/lost-password/ needs in page error
               $errors->add('si_captcha_error', "<strong>$error</strong>: $validate_result");
               return $errors;
       } else {
               // wp-login.php needs >> Back link
               wp_die( "<strong>$error</strong>: $validate_result", $error, array( 'back_link' => true ) ); // back link makes go back link
       }
   }
   return;
} // function si_captcha_lostpassword_post


  // this function checks the captcha posted with multisite registration page
function si_captcha_mu_signup_validate( $result ) {
  global $si_captcha_dir, $si_captcha_dir_ns, $si_captcha_opt;

   if (isset($_POST['stage']) && 'validate-blog-signup' == $_POST['stage'])
		// user is registering a new blog, captcha is not required at this stage
		return $result;

   $validate_result = $this->si_captcha_validate_code('reg', 'unlink');
   if($validate_result != 'valid')
		$result['errors']->add( 'si_captcha_error', $validate_result );
   return $result;
} // end function si_captcha_mu_signup_validate


// multisite
// the user is already registered and is registering a new site
function si_captcha_mu_site_signup_validate(array $result) {
   global $si_captcha_dir, $si_captcha_dir_ns, $si_captcha_opt;

   if (is_user_logged_in()) {
       $validate_result = $this->si_captcha_validate_code('reg', 'unlink');
       if($validate_result != 'valid')
		   $result['errors']->add( 'si_captcha_error', $validate_result );
   }
   return $result;
 } // end si_captcha_mu_site_signup_validate


// this function adds the captcha to the bbPress New Topic and Reply form
function si_captcha_bbpress_topic_form() {
   global $si_captcha_opt;

// Test for some required things, print error message right here if not OK.
if ($this->si_captcha_check_requires()) {
// the captcha html - lostpassword form
echo '

<div ';
echo ($si_captcha_opt['captcha_small'] == 'true') ? 'class="si_captcha_small"' : 'class="si_captcha_large"';
echo '>';
$this->si_captcha_captcha_html('si_image_bbpress_topic','bbpress_topic');
echo '</div>

<p>
 <label>';
  echo ($si_captcha_opt['label_captcha'] != '') ? $si_captcha_opt['label_captcha'] : __('CAPTCHA Code', 'si-captcha');
  echo '<br />
<input id="si_captcha_code" name="si_captcha_code" class="input" type="text" value="" /></label>
</p>

';
}
  return true;
} // end function si_captcha_bbpress_topic_form


  // this function checks the captcha posted with bbPress New Topic and Reply form
function si_captcha_bbpress_topic_validate() {
   global $si_captcha_opt;

   $validate_result = $this->si_captcha_validate_code('bbpress_topic', 'unlink');
      if($validate_result != 'valid') {
       $error = ($si_captcha_opt['error_error'] != '') ? $si_captcha_opt['error_error'] : __('ERROR', 'si-captcha');
       bbp_add_error('si-captcha-wrong', "<strong>$error</strong>: $validate_result");
   }
   return;
} // end function si_captcha_bp_signup_validate


function si_captcha_jetpack_validate($bool) {
       $success = false;

       $validate_result = $this->si_captcha_validate_code('jetpack', 'unlink');
       if($validate_result == 'valid') {
           $success = true;
       }
       if ( !$success && apply_filters('si_captcha_fail', true, $bool) ) {
            $this->jetpack_failed = $validate_result;
            return new WP_Error( 'si_captcha_error', $validate_result );
       }

       return $bool;
} // end function si_captcha_jetpack_validate


// append field to jetpack contact form shortcode
function si_captcha_jetpack_form($content) {
  global $si_captcha_add_jetpack;

  //if ( $si_captcha_add_jetpack )     // prevent double captcha fields jetpack
  //        return $content;

   $si_captcha_add_jetpack = true;

   return preg_replace_callback( '/\[contact-form(.*?)?\](.*?)?\[\/contact-form\]/si',
   array($this, 'si_captcha_jetpack_append_field_callback'),
   $content );
} // end function si_captcha_jetpack_form


function si_captcha_jetpack_append_field_callback($m)   {
        $fields = isset($m[2]) ? $m[2] : null;
        $_fields = $fields . '[si-captcha]';
        return str_replace($fields, $_fields, array_shift($m));
} // end function si_captcha_jetpack_append_field_callback


function si_captcha_jetpack_shortcode($atts) {
   global $si_captcha_opt;

   $text_incorrect = ($si_captcha_opt['error_incorrect'] != '') ? $si_captcha_opt['error_incorrect'] : __('Wrong CAPTCHA', 'si-captcha');

// the captcha html - jetpack contact form
        ob_start();

// Test for some required things, print error message right here if not OK.
if ($this->si_captcha_check_requires()) {
// the captcha html - lostpassword form

echo '

<div ';
echo ($si_captcha_opt['captcha_small'] == 'true') ? 'class="si_captcha_small"' : 'class="si_captcha_large"';
echo '>';
$this->si_captcha_captcha_html('si_image_jetpack','jetpack');
echo '</div>

<div>
<br />
 <label>';
  echo ($si_captcha_opt['label_captcha'] != '') ? $si_captcha_opt['label_captcha'] : __('CAPTCHA Code', 'si-captcha');
  echo '<br />
<input id="si_captcha_code" name="si_captcha_code" class="input" type="text" value="" /></label>
</div>

';

           if ( isset($this->jetpack_failed) && $this->jetpack_failed ) : ?>
                <p class="si-captcha-jetpack-error">
                    <?php echo $this->jetpack_failed; ?>
                </p>
            <?php endif; ?>

        <?php
}

        return apply_filters('si_captcha_field', ob_get_clean());
} // end function si_captcha_jetpack_shortcode


// check if the posted capcha code was valid for any of the forms called
function si_captcha_validate_code($form_id = 'com', $unlink = 'unlink') {
       global $si_captcha_dir, $si_captcha_dir_ns, $si_captcha_opt;

  if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'lostpassword' && $form_id == 'log')
        return 'valid';  // fixes lostpassword page because add_filter('login_errors' is also being called before

  if (isset($_POST['si_captcha_code']) && empty($_POST['si_captcha_code']))
        return ($si_captcha_opt['error_empty'] != '') ? $si_captcha_opt['error_empty'] : __('Empty CAPTCHA', 'si-captcha');

  if($si_captcha_opt['enable_session'] != 'true') {
   //captcha without sessions
      if (empty($_POST['si_captcha_code']) || $_POST['si_captcha_code'] == '') {
         return ($si_captcha_opt['error_empty'] != '') ? $si_captcha_opt['error_empty'] : __('Empty CAPTCHA', 'si-captcha');
      }else if (!isset($_POST["si_code_$form_id"]) || empty($_POST["si_code_$form_id"])) {
          return ($si_captcha_opt['error_token'] != '') ? $si_captcha_opt['error_token'] : __('Missing CAPTCHA token', 'si-captcha');
      }else{
         $prefix = 'xxxxxx';
         if ( isset($_POST["si_code_$form_id"]) && is_string($_POST["si_code_$form_id"]) && preg_match('/^[a-zA-Z0-9]{15,17}$/',$_POST["si_code_$form_id"]) ){
           $prefix = $_POST["si_code_$form_id"];
         }
         if ( is_readable( $si_captcha_dir_ns . $prefix . '.php' ) ) {
			include( $si_captcha_dir_ns . $prefix . '.php' );
			if ( 0 == strcasecmp( trim(strip_tags($_POST['si_captcha_code'])), $captcha_word ) ) {
              // captcha was matched
             if($unlink == 'unlink')
                @unlink ($si_captcha_dir_ns . $prefix . '.php');
              return 'valid';
			} else {
               return ($si_captcha_opt['error_incorrect'] != '') ? $si_captcha_opt['error_incorrect'] : __('Wrong CAPTCHA', 'si-captcha');
            }
	     } else {
           return ($si_captcha_opt['error_unreadable'] != '') ? $si_captcha_opt['error_unreadable'] : __('Unreadable CAPTCHA token file', 'si-captcha');
	    }
	  }

  }else{
   //captcha with PHP sessions
   if (!isset($_SESSION["securimage_code_si_$form_id"]) || empty($_SESSION["securimage_code_si_$form_id"])) {
          return ($si_captcha_opt['error_cookie'] != '') ? $si_captcha_opt['error_cookie'] : __('Unreadable CAPTCHA cookie', 'si-captcha');
   }else{
      $captcha_code = trim(strip_tags($_POST['si_captcha_code']));

      require_once "$si_captcha_dir/securimage.php";
      $img = new Securimage_Captcha_si();
      $img->form_id = $form_id; // makes compatible with multi-forms on same page
      $valid = $img->check("$captcha_code");
      // Check, that the right CAPTCHA password has been entered, display an error message otherwise.
      if($valid == true) {
          // ok can continue
          return 'valid';
      } else {
          return ($si_captcha_opt['error_incorrect'] != '') ? $si_captcha_opt['error_incorrect'] : __('Wrong CAPTCHA', 'si-captcha');
      }
   }
  }
} // end function si_captcha_validate_code


function si_captcha_get_styles(){
    $styles = array(
    '.si_captcha_small { width:175px; height:45px; padding-top:10px; padding-bottom:10px; }',
    '.si_captcha_large { width:250px; height:60px; padding-top:10px; padding-bottom:10px; }',
    'img#si_image_com { border-style:none; margin:0; padding-right:5px; float:left; }',
    'img#si_image_reg { border-style:none; margin:0; padding-right:5px; float:left; }',
    'img#si_image_log { border-style:none; margin:0; padding-right:5px; float:left; }',
    'img#si_image_side_login { border-style:none; margin:0; padding-right:5px; float:left; }',
    'img#si_image_checkout { border-style:none; margin:0; padding-right:5px; float:left; }',
    'img#si_image_jetpack { border-style:none; margin:0; padding-right:5px; float:left; }',
    'img#si_image_bbpress_topic { border-style:none; margin:0; padding-right:5px; float:left; }',
    '.si_captcha_refresh { border-style:none; margin:0; vertical-align:bottom; }',
    'div#si_captcha_input { display:block; padding-top:15px; padding-bottom:5px; }',
    'label#si_captcha_code_label { margin:0; }',
    'input#si_captcha_code_input { width:65px; }',
    'p#si_captcha_code_p { clear: left; padding-top:10px; }',
    '.si-captcha-jetpack-error { color:#DC3232; }',
      );
      return $styles;
} // end function si_captcha_get_styles


function si_captcha_add_css(){
   global $si_captcha_opt, $si_captcha_add_script;

   if (!$si_captcha_add_script)
      return;

  // only load this css on the blog pages where the captcha could be
  wp_enqueue_script('jquery');
if( $si_captcha_opt['external_style'] != 'true' ) {
?>
<script type="text/javascript">
//<![CDATA[
var si_captcha_styles = "\
<!-- begin SI CAPTCHA Anti-Spam - comment form style -->\
<style type='text/css'>\
<?php
$styles = $this->si_captcha_get_styles();
   foreach ($styles as $line)  {
        echo $line . '\
';
   }
?>
</style>\
<!-- end SI CAPTCHA Anti-Spam - comment form style -->\
";
jQuery(document).ready(function($) {
$('head').append(si_captcha_styles);
});
//]]>
</script>
<?php
  }
} // end function si_captcha_add_css


// only load this javascript on the blog pages where captcha needs to display
function si_captcha_add_script(){
   global $si_captcha_opt, $si_captcha_url, $si_captcha_add_script;

   if (!$si_captcha_add_script)
      return;

   // only load this javascript on the blog pages where the captcha is on
   wp_enqueue_script('si_captcha', $si_captcha_url.'/si_captcha.js', array(), '1.0', true);

} // end  function si_captcha_add_script


// set a transient used to remind admin to enter API keys upon activation
function si_captcha_admin_activated() {

    if ( current_user_can( 'manage_options' ) ) {
		          set_transient( 'si_captcha_admin_notice', true, 5 );
    }
} // end function si_captcha_admin_activated


function si_captcha_activated_notice() {
          // remind admin to configure settings upon activation

	   if ( current_user_can('manage_options') && get_transient('si_captcha_admin_notice') ){
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><?php
			printf(
				__( '<strong>SI CAPTCHA needs your attention:</strong> To make it work, you need to configure the settings. <br />You can do so at the <a href="%s">SI CAPTCHA settings page</a>.' , 'si-captcha' ),
				admin_url( add_query_arg( 'page' , 'si-captcha-for-wordpress/si-captcha.php' , 'options-general.php' ) )
			);
		?></p></div>

        <?php
        // Delete transient, only display this notice once.
        delete_transient('si_captcha_admin_notice');
    }
} // end function si_captcha_activated_notice


function si_captcha_determine_current_page() {
		// only strip the host and scheme (including https), so
		// we can properly compare with REQUEST_URI later on.
		$login_path    = preg_replace('#https?://[^/]+/#i', '', wp_login_url());
		$register_path = preg_replace('#https?://[^/]+/#i', '', wp_registration_url());
        $lostpassword_path = preg_replace('#https?://[^/]+/#i', '', wp_lostpassword_url());
        $myaccount_page_url = '';
        if ( class_exists( 'WooCommerce' ) ) {
              $myaccount_page = get_option( 'woocommerce_myaccount_page_id' );
             if ( $myaccount_page ) {
               $myaccount_page_url =  preg_replace('#https?://[^/]+/#i', '',  get_permalink( $myaccount_page ) );
              }
        }

		global $pagenow;

		$request_uri = ltrim($_SERVER['REQUEST_URI'], '/');
        if (!empty($lostpassword_path) && strpos($request_uri, $lostpassword_path) === 0) {
			// user is requesting lost password page
			$this->is_lostpassword = true;
		}
		elseif (!empty($register_path) && strpos($request_uri, $register_path) === 0) {
			// user is requesting regular user registration page
			$this->is_reg = true;
		}
        elseif (  (class_exists( 'WooCommerce' ) && $myaccount_page_url != '') && strpos($request_uri, $myaccount_page_url) === 0) {
            // user is requesting woocommerce registration page
			$this->is_reg = true;
        }
		elseif (!empty($login_path) && strpos($request_uri, $login_path) === 0) {
			// user is requesting the wp-login page
			$this->is_login = true;
		}
		elseif (!empty($pagenow) && $pagenow == 'wp-signup.php') {
			// user is requesting wp-signup page (multi-site page for user/site registration)
			$this->is_signup = true;
		}
} // function si_captcha_determine_current_page


} // end of class
} // end of if ! class


if (class_exists("siCaptcha")) {
 $si_captcha = new siCaptcha();
}

if (isset($si_captcha)) {
   global $si_captcha_opt;


  $si_captcha_dir = plugin_dir_path( __FILE__ ) . 'captcha';
  $si_captcha_url = plugin_dir_url( __FILE__ ) . 'captcha';

  // only used for the no-session captcha setting
  $si_captcha_url_ns = $si_captcha_url  . '/cache/';
  $si_captcha_dir_ns = $si_captcha_dir . '/cache/';
  $si_captcha->si_captcha_init_temp_dir($si_captcha_dir_ns);


  $si_captcha->si_captcha_get_options();

  // init actions
  add_action('init', array(&$si_captcha, 'si_captcha_init'));

  if ( isset($si_captcha_opt['enable_session']) && $si_captcha_opt['enable_session'] == 'true') {
     // start the PHP session
     add_action('plugins_loaded', array(&$si_captcha, 'si_captcha_start_session'));
  }

  // admin options
  add_action('admin_menu', array(&$si_captcha,'si_captcha_admin_menu'),1);
  add_action('admin_head', array(&$si_captcha,'si_captcha_admin_head'),1);

  register_activation_hook(__FILE__, array(&$si_captcha, 'si_captcha_admin_activated'), 1);

  add_action('admin_notices',array( &$si_captcha , 'si_captcha_activated_notice'));
  add_action('network_admin_notices',array( &$si_captcha , 'si_captcha_activated_notice'));

  // adds "Settings" link to the plugin action page
  add_filter( 'plugin_action_links', array(&$si_captcha,'si_captcha_plugin_action_links'),10,2);


}

// end of file