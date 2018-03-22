<?php
/*
SI Captcha Anti-Spam (admin settings page)
*/

//do not allow direct access
if ( strpos(strtolower($_SERVER['SCRIPT_NAME']),strtolower(basename(__FILE__))) ) {
    header('HTTP/1.0 403 Forbidden');
	exit('Forbidden');
}

  if (isset($_POST['submit'])) {

      if ( function_exists('current_user_can') && !current_user_can('manage_options') )
            die(__('You do not have permissions for managing this option', 'si-captcha'));

        check_admin_referer( 'si-captcha-options_update'); // nonce
   // post changes to the options array
   $optionarray_update = array(
         'donated' =>               (isset( $_POST['si_captcha_donated'] ) ) ? 'true' : 'false',// true or false
         'bypass_comment' =>        (isset( $_POST['si_captcha_bypass_comment'] ) ) ? 'true' : 'false',
         'comment' =>               (isset( $_POST['si_captcha_comment'] ) ) ? 'true' : 'false',
         'login' =>                 (isset( $_POST['si_captcha_login'] ) ) ? 'true' : 'false',
         'register' =>              (isset( $_POST['si_captcha_register'] ) ) ? 'true' : 'false',
         'bp_register' =>           (isset( $_POST['si_captcha_bp_register'] ) ) ? 'true' : 'false',
         'ms_register' =>           (isset( $_POST['si_captcha_ms_register'] ) ) ? 'true' : 'false',
         'wpforo_register' =>       (isset( $_POST['si_captcha_wpforo_register'] ) ) ? 'true' : 'false',
         'lostpwd' =>               (isset( $_POST['si_captcha_lostpwd'] ) ) ? 'true' : 'false',
         'wc_checkout' =>           (isset( $_POST['si_captcha_wc_checkout'] ) ) ? 'true' : 'false',
         'jetpack' =>               (isset( $_POST['si_captcha_jetpack'] ) ) ? 'true' : 'false',
         'bbpress_topic' =>         (isset( $_POST['si_captcha_bbpress_topic'] ) ) ? 'true' : 'false',
         'bbpress_reply' =>         (isset( $_POST['si_captcha_bbpress_reply'] ) ) ? 'true' : 'false',
         'enable_session' =>        (isset( $_POST['si_captcha_enable_session'] ) ) ? 'true' : 'false',
         'captcha_small' =>         (isset( $_POST['si_captcha_captcha_small'] ) ) ? 'true' : 'false',
         'external_css' =>          (isset( $_POST['si_captcha_external_css'] ) ) ? 'true' : 'false',
         'comment_label_position' =>       ($_POST['si_captcha_comment_label_position'] != '' ) ? sanitize_text_field($_POST['si_captcha_comment_label_position']) : sanitize_text_field($si_captcha_option_defaults['comment_label_position']), // use default if empty
         'network_individual_on' => (isset( $_POST['si_captcha_network_individual_on'] ) ) ? 'true' : 'false',
         'required_indicator' =>    sanitize_text_field($_POST['si_captcha_required_indicator']),
         'error_spambot' =>         sanitize_text_field($_POST['si_captcha_error_spambot']),
         'error_incorrect' =>       sanitize_text_field($_POST['si_captcha_error_incorrect']),
         'error_empty' =>           sanitize_text_field($_POST['si_captcha_error_empty']),
         'error_token' =>           sanitize_text_field($_POST['si_captcha_error_token']),
         'error_error' =>           sanitize_text_field($_POST['si_captcha_error_error']),
         'error_unreadable' =>      sanitize_text_field($_POST['si_captcha_error_unreadable']),
         'error_cookie' =>          sanitize_text_field($_POST['si_captcha_error_cookie']),
         'label_captcha' =>         sanitize_text_field($_POST['si_captcha_label_captcha']),
         'tooltip_captcha' =>       sanitize_text_field($_POST['si_captcha_tooltip_captcha']),
         'tooltip_refresh' =>       sanitize_text_field($_POST['si_captcha_tooltip_refresh']),

                   );

   // deal with quotes
   foreach($optionarray_update as $key => $val) {
          $optionarray_update[$key] = str_replace('&quot;','"',$val);
   }

   // update the settings then set the options array
   $network_individual_on = false;
   if ($si_captcha_networkwide && get_current_blog_id() == 1) {
            if ($optionarray_update['network_individual_on'] == 'true')
                 $network_individual_on = true;
            // multisite with network activation individual site control, this is main site
            update_site_option('si_captcha_anti_spam', $optionarray_update);
            $si_captcha_opt = get_site_option('si_captcha_anti_spam');
   } else if ($si_captcha_networkwide && get_current_blog_id() > 1) {
        $si_captcha_main_opt = get_site_option('si_captcha_anti_spam');
        if ($si_captcha_main_opt['network_individual_on'] == 'true') {
                $network_individual_on = true;
                $optionarray_update['network_individual_on'] = $si_captcha_main_opt['network_individual_on'];
               // multisite with network activation individual site control, this is not main site
               update_option('si_captcha_anti_spam', $optionarray_update);
               $si_captcha_opt = get_option('si_captcha_anti_spam');
        } else {
                // multisite with network activation master site control, update master site settings
                update_site_option('si_captcha', $optionarray_update);
                $si_captcha_opt = get_site_option('si_captcha_anti_spam');
        }
   } else {
           // no multisite
           update_option('si_captcha_anti_spam', $optionarray_update);
           $si_captcha_opt = get_option('si_captcha_anti_spam');
   }

    // strip slashes on get options array
    foreach($si_captcha_opt as $key => $val) {
           $si_captcha_opt[$key] = stripslashes($val);
    }


  } // end if (isset($_POST['submit']))
?>
<?php if ( !empty($_POST ) ) : ?>
<div id="message" class="updated"><p><strong><?php _e('Your settings have been saved.', 'si-captcha') ?></strong></p></div>
<?php endif; ?>
<div class="wrap">
<h2><?php _e('SI Captcha Anti-Spam settings', 'si-captcha') ?></h2>

<script type="text/javascript">
    function toggleVisibility(id) {
       var e = document.getElementById(id);
       if(e.style.display == 'block')
          e.style.display = 'none';
       else
          e.style.display = 'block';
    }
</script>

<?php
     // find out if multisite with network activation master site control
     $network_individual_on = false;
     if ($si_captcha_networkwide && get_current_blog_id() > 1) {
        $si_captcha_main_opt = get_site_option('si_captcha_anti_spam');
        if ($si_captcha_main_opt['network_individual_on'] == 'true') {
              $network_individual_on = true;
        }
     }

  // get the options
   if ( $si_captcha_networkwide && ! $network_individual_on ) {
        // multisite with network activation master site control
             $this_blog_id = get_current_blog_id();
             if ($this_blog_id > 1 ) {
               echo '<div class="fs-notice">';
		       echo __( 'SI Captcha Anti-Spam is Network Activated and Main site configured. It is not necessary to change any settings here.', 'si-captcha' ).' ';
               echo __( 'Settings are controlled at the main site and are Networkwide. If you want individual sites to each have their own unique settings: go to the main site SI Captcha Anti-Spam settings and enable "Allow Multisite network activated sites to have individual SI Captcha Anti-Spam settings".', 'si-captcha' );

	       echo "</div>\n";
               return;
             }
   }

    $si_captcha_base_url = plugin_dir_url( __FILE__ );

if (function_exists('get_transient')) {
  require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

  // Before, try to access the data, check the cache.
  if (false === ($api = get_transient('si_captcha_info'))) {
    // The cache data doesn't exist or it's expired.

    $api = plugins_api('plugin_information', array('slug' => 'si-captcha-for-wordpress' ));
    if ( !is_wp_error($api) ) {
      // cache isn't up to date, write this fresh information to it now to avoid the query for xx time.
      $myexpire = 60 * 15; // Cache data for 15 minutes
      set_transient('si_captcha_info', $api, $myexpire);
    }
  }
  if ( !is_wp_error($api) ) {
	$plugins_allowedtags = array('a' => array('href' => array(), 'title' => array(), 'target' => array()),
								'abbr' => array('title' => array()), 'acronym' => array('title' => array()),
								'code' => array(), 'pre' => array(), 'em' => array(), 'strong' => array(),
								'div' => array(), 'p' => array(), 'ul' => array(), 'ol' => array(), 'li' => array(),
								'h1' => array(), 'h2' => array(), 'h3' => array(), 'h4' => array(), 'h5' => array(), 'h6' => array(),
								'img' => array('src' => array(), 'class' => array(), 'alt' => array()));
	//Sanitize HTML
	foreach ( (array)$api->sections as $section_name => $content )
		$api->sections[$section_name] = wp_kses($content, $plugins_allowedtags);
	foreach ( array('version', 'author', 'requires', 'tested', 'homepage', 'downloaded', 'slug') as $key )
		$api->$key = wp_kses($api->$key, $plugins_allowedtags);

      if ( ! empty($api->downloaded) ) {
        echo sprintf(__('Downloaded %s times', 'si-captcha'),number_format_i18n($api->downloaded));
        echo '.';
      }

?>
		<?php if ( ! empty($api->rating) ) : ?>
		<div class="fs-star-holder" title="<?php echo esc_attr(sprintf(__('(Average rating based on %s ratings)', 'si-captcha'),number_format_i18n($api->num_ratings))); ?>">
			<div class="fs-star fs-star-rating" style="width: <?php echo esc_attr($api->rating) ?>px"></div>
			<div class="fs-star fs-star5"><img src="<?php echo $si_captcha_base_url; ?>star.png" alt="<?php _e('5 stars', 'si-captcha') ?>" /></div>
			<div class="fs-star fs-star4"><img src="<?php echo $si_captcha_base_url; ?>star.png" alt="<?php _e('4 stars', 'si-captcha') ?>" /></div>
			<div class="fs-star fs-star3"><img src="<?php echo $si_captcha_base_url; ?>star.png" alt="<?php _e('3 stars', 'si-captcha') ?>" /></div>
			<div class="fs-star fs-star2"><img src="<?php echo $si_captcha_base_url; ?>star.png" alt="<?php _e('2 stars', 'si-captcha') ?>" /></div>
			<div class="fs-star fs-star1"><img src="<?php echo $si_captcha_base_url; ?>star.png" alt="<?php _e('1 star', 'si-captcha') ?>" /></div>
		</div>
		<small><?php echo sprintf(__('(Average rating based on %s ratings)', 'si-captcha'),number_format_i18n($api->num_ratings)); ?> <a target="_blank" href="https://wordpress.org/support/plugin/si-captcha-for-wordpress/reviews/"> <?php _e('rate', 'si-captcha') ?></a></small>
        <br />
		<?php endif; ?>

<?php
  } // if ( !is_wp_error($api)
 }// end if (function_exists('get_transient'

$si_captcha_update = '';
if (isset($api->version)) {
 if ( version_compare($api->version, $si_captcha_version, '>') ) {
     $si_captcha_update = ', <a href="'.admin_url( 'plugins.php' ).'">'.sprintf(__('a newer version is available: %s', 'si-captcha'),$api->version).'</a>';
     echo '<div id="message" class="updated">';
     echo '<a href="'.admin_url( 'plugins.php' ).'">'.sprintf(__('A newer version of SI Captcha Anti-Spam is available: %s', 'si-captcha'),$api->version).'</a>';
     echo "</div>\n";
  }else{
     $si_captcha_update = ' '. __('(latest version)', 'si-captcha');
  }
}
?>

<p>
<?php echo __('Version:', 'si-captcha'). ' '.$si_captcha_version.$si_captcha_update; ?> |
<a href="https://wordpress.org/plugins/si-captcha-for-wordpress/changelog/" target="_blank"><?php echo __('Changelog', 'si-captcha'); ?></a> |
<a href="https://wordpress.org/plugins/si-captcha-for-wordpress/faq/" target="_blank"><?php echo __('FAQ', 'si-captcha'); ?></a> |
<a href="https://wordpress.org/support/plugin/si-captcha-for-wordpress/reviews/" target="_blank"><?php echo __('Rate This', 'si-captcha'); ?></a> |
<a href="https://wordpress.org/support/plugin/si-captcha-for-wordpress" target="_blank"><?php echo __('Support', 'si-captcha'); ?></a> |
<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=KXJWLPPWZG83S" target="_blank"><?php echo __('Donate', 'si-captcha'); ?></a> |
<a href="http://www.642weather.com/weather/scripts.php" target="_blank"><?php echo __('Mikes Free PHP Scripts', 'si-captcha'); ?></a>
</p>


<form name="formoptions" action="<?php echo admin_url( 'options-general.php?page=si-captcha-for-wordpress/si-captcha.php' ); ?>" method="post">
        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="form_type" value="upload_options" />
        <?php wp_nonce_field('si-captcha-options_update');

 if ( is_multisite() && (! $si_captcha_networkwide ) ) {
        // multisite without network activation
               echo '<div class="fs-notice">';
		       echo __( '<strong>Individual Site Settings Enabled</strong><br />Note: SI Captcha Anti-Spam is not Network Activated, this means each site will have individual SI Captcha Anti-Spam settings.', 'si-captcha' ).' ';
               echo __( 'If you want it this way, that is OK, but if you want the master site to control all the sites: go to the main site, then Network Activate this plugin, then go to SI Captcha Anti-Spam settings and disable "Allow Multisite network activated sites to have individual SI Captcha Anti-Spam settings".', 'si-captcha' );
	       echo "</div>\n";
   }


    ?>
        <p class="submit">
          <input type="submit" name="submit"class="button button-primary" value="<?php _e('Save Changes', 'si-captcha') ?>" />
        </p>

      <p><?php echo __('If you do not like image captcha and code entry, you can uninstall this plugin and try my new plugin:', 'si-captcha' ).' <a href="https://wordpress.org/plugins/fast-secure-recaptcha/" target="_blank">'. __('Fast Secure reCAPTCHA with Google No CAPTCHA reCAPTCHA', 'si-captcha').'</a>'; ?>
       </p>
       
        <fieldset class="options">

        <table width="100%" cellspacing="2" cellpadding="5" class="form-table">


     <tr>
       <th scope="row" style="width: 75px;"><?php _e('Enable CAPTCHA:', 'si-captcha'); ?></th>
      <td>

    <input name="si_captcha_login" id="si_captcha_login" type="checkbox" <?php if ( $si_captcha_opt['login'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="si_captcha_login"><?php _e('Login form.', 'si-captcha') ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_captcha_login_tip');"><?php _e('help', 'si-captcha'); ?></a>
    <div class="fscf_tip" id="si_captcha_login_tip">
    <?php _e('Require that the user pass a CAPTCHA test before login.', 'si-captcha') ?>
    </div>
    <br />

    <input name="si_captcha_register" id="si_captcha_register" type="checkbox" <?php if ( $si_captcha_opt['register'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="si_captcha_register"><?php _e('Register form.', 'si-captcha') ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_captcha_register_tip');"><?php _e('help', 'si-captcha'); ?></a>
    <div class="fscf_tip" id="si_captcha_register_tip">
    <?php _e('Require that the user pass a CAPTCHA test before registering.', 'si-captcha') ?>
    </div>
    <br />

    <input name="si_captcha_lostpwd" id="si_captcha_lostpwd" type="checkbox" <?php if ( $si_captcha_opt['lostpwd'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="si_captcha_lostpwd"><?php _e('Lost password form.', 'si-captcha') ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_captcha_lostpwd_tip');"><?php _e('help', 'si-captcha'); ?></a>
    <div class="fscf_tip" id="si_captcha_lostpwd_tip">
    <?php _e('Require that the user pass a CAPTCHA test before lost password request.', 'si-captcha') ?>
    </div>
    <br />

    <input name="si_captcha_comment" id="si_captcha_comment" type="checkbox" <?php if ( $si_captcha_opt['comment'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="si_captcha_comment"><?php _e('Comment form.', 'si-captcha') ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_captcha_comment_tip');"><?php _e('help', 'si-captcha'); ?></a>
    <div class="fscf_tip" id="si_captcha_comment_tip">
    <?php _e('Require that the user pass a CAPTCHA test before posting comments.', 'si-captcha') ?>
    </div>
    <br />

    <input name="si_captcha_ms_register" id="si_captcha_ms_register" type="checkbox" <?php if ( $si_captcha_opt['ms_register'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="si_captcha_ms_register"><?php _e('Multisite register form.', 'si-captcha') ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_captcha_ms_register_tip');"><?php _e('help', 'si-captcha'); ?></a>
    <div class="fscf_tip" id="si_captcha_ms_register_tip">
    <?php _e('Require that the user pass a CAPTCHA test before registering in Multisite.', 'si-captcha') ?>
    </div>
    <br />

    <input name="si_captcha_bp_register" id="si_captcha_bp_register" type="checkbox" <?php if ( $si_captcha_opt['bp_register'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="si_captcha_bp_register"><?php _e('BuddyPress register form.', 'si-captcha') ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_captcha_bp_register_tip');"><?php _e('help', 'si-captcha'); ?></a>
    <div class="fscf_tip" id="si_captcha_bp_register_tip">
    <?php _e('Require that the user pass a CAPTCHA test before registering in BuddyPress.', 'si-captcha') ?>
    </div>
    <br />

    <input name="si_captcha_wpforo_register" id="si_captcha_wpforo_register" type="checkbox" <?php if ( $si_captcha_opt['wpforo_register'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="si_captcha_wpforo_register"><?php _e('wpForo Forum register form.', 'si-captcha') ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_captcha_wpforo_register_tip');"><?php _e('help', 'si-captcha'); ?></a>
    <div class="fscf_tip" id="si_captcha_wpforo_register_tip">
    <?php _e('Require that the user pass a CAPTCHA test before registering in wpForo Forum.', 'si-captcha') ?>
    </div>
    <br />

    <input name="si_captcha_wc_checkout" id="si_captcha_woocommerce" type="checkbox" <?php if ( $si_captcha_opt['wc_checkout'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="si_captcha_wc_checkout"><?php _e('WooCommerce checkout.', 'si-captcha') ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_captcha_wc_checkout_tip');"><?php _e('help', 'si-captcha'); ?></a>
    <div class="fscf_tip" id="si_captcha_wc_checkout_tip">
    <?php _e('Require that the user pass a CAPTCHA test on WooCommerce checkout form when not logged in.', 'si-captcha') ?>
    </div>
    <br />

    <input name="si_captcha_jetpack" id="si_captcha_jetpack" type="checkbox" <?php if ( $si_captcha_opt['jetpack'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="si_captcha_jetpack"><?php _e('Jetpack Contact Form.', 'si-captcha') ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_captcha_jetpack_tip');"><?php _e('help', 'si-captcha'); ?></a>
    <div class="fscf_tip" id="si_captcha_jetpack_tip">
    <?php _e('Require that the user pass a CAPTCHA test on Jetpack Contact Form.', 'si-captcha') ?>
    </div>
    <br />

    <input name="si_captcha_bbpress_topic" id="si_captcha_bbpress_topic" type="checkbox" <?php if ( $si_captcha_opt['bbpress_topic'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="si_captcha_bbpress_topic"><?php _e('bbPress New Topic Form.', 'si-captcha') ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_captcha_bbpress_topic_tip');"><?php _e('help', 'si-captcha'); ?></a>
    <div class="fscf_tip" id="si_captcha_bbpress_topic_tip">
    <?php _e('Require that the user pass a CAPTCHA test on bbPress New Topic Form.', 'si-captcha') ?>
    </div>
    <br />

    <input name="si_captcha_bbpress_reply" id="si_captcha_bbpress_reply" type="checkbox" <?php if ( $si_captcha_opt['bbpress_reply'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="si_captcha_bbpress_reply"><?php _e('bbPress Reply to Topic Form.', 'si-captcha') ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_captcha_bbpress_reply_tip');"><?php _e('help', 'si-captcha'); ?></a>
    <div class="fscf_tip" id="si_captcha_bbpress_reply_tip">
    <?php _e('Require that the user pass a CAPTCHA test on bbPress Reply to Topic Form.', 'si-captcha') ?>
    </div>
    <br />

    <input name="si_captcha_bypass_comment" id="si_captcha_bypass_comment" type="checkbox" <?php if( $si_captcha_opt['bypass_comment'] == 'true' ) echo 'checked="checked"'; ?> />
    <label name="si_captcha_bypass_comment" for="si_captcha_bypass_comment"><?php _e('No comment form CAPTCHA for logged in users', 'si-captcha') ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_captcha_bypass_comment_tip');"><?php _e('help', 'si-captcha'); ?></a>
    <div class="fscf_tip" id="si_captcha_bypass_comment_tip">
    <?php _e('Logged in users will not have to pass a CAPTCHA test on comments form.', 'si-captcha') ?>
    </div>
    <br />

   <?php
      $show_this = true;
     if ( is_multisite() && get_current_blog_id() > 1 ) {
            $show_this = false;
     }
    if ( $show_this ) {

       ?>

    <input name="si_captcha_network_individual_on" id="si_captcha_network_individual_on" type="checkbox" <?php if ( $si_captcha_opt['network_individual_on'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="si_captcha_network_individual_on"><?php echo __('Allow Multisite network activated sites to have individual SI Captcha Anti-Spam settings.', 'si-captcha'); ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_captcha_network_individual_on_tip');"><?php _e('help', 'si-captcha'); ?></a>
    <div class="fscf_tip" id="si_captcha_network_individual_on_tip">
    <?php _e('Enabling this setting allows: individual site settings for this plugin on multisite with network activation turned on. The default is for all sites to use the master site settings.', 'si-captcha'); ?>
    </div>
    <br />


 <?php }
    if ($si_captcha_networkwide && $network_individual_on) {
      // multisite with network activation and individual site control turned on, but this site cannot change these two settings

      echo '<div class="fs-notice">';
	  echo __( 'SI Captcha Anti-Spam is Network Activated and individual site configured. The next setting can only be modified by the main site settings menu.', 'si-captcha' );
     echo "</div>\n";

    ?>

     <?php echo ( $si_captcha_main_opt['network_individual_on'] == 'true' ) ? __('Main site admin enabled:', 'si-captcha') : __('Main site admin disabled:', 'si-captcha') ; ?>
    <?php echo  ' ' . __('Allow Multisite network activated sites to have individual SI Captcha Anti-Spam settings.', 'si-captcha'); ?>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_captcha_network_individual_on_disabled_tip');"><?php _e('help', 'si-captcha'); ?></a>
    <div class="fscf_tip" id="si_captcha_network_individual_on_disabled_tip">
    <?php _e('Enabling this setting allows: individual site settings for this plugin on multisite with network activation turned on. The default is for all sites to use the master site settings.', 'si-captcha'); ?>
    </div>
    <br />


  <?php } ?>

    </td>
    </tr>

     <tr>
       <th scope="row" style="width: 75px;"><?php _e('Style:', 'si-captcha'); ?></th>
      <td>

    <input name="si_captcha_captcha_small" id="si_captcha_captcha_small" type="checkbox" <?php if ( $si_captcha_opt['captcha_small'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="si_captcha_captcha_small"><?php echo __('Enable small size CAPTCHA image.', 'si-captcha'); ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_captcha_captcha_small_tip');"><?php _e('help', 'si-captcha'); ?></a>
    <div class="fscf_tip" id="si_captcha_captcha_small_tip">
    <?php _e('Makes the CAPTCHA image size small instead of large.', 'si-captcha'); ?>
    </div>
    <br />

    <input name="si_captcha_external_css" id="si_captcha_external_css" type="checkbox" <?php if ( $si_captcha_opt['external_css'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="si_captcha_external_css"><?php echo __('Enable external CAPTCHA CSS.', 'si-captcha'); ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_captcha_external_css_tip');"><?php _e('help', 'si-captcha'); ?></a>
    <div class="fscf_tip" id="si_captcha_external_css_tip">
    <?php _e('Enable to not load this plugin CSS into wp_head. This allows you to load your own modified CSS into your theme instead.', 'si-captcha'); ?>
   <br /><br />
  <strong><?php _e('External Style Sheet CSS starting point for custom setup:', 'si-captcha'); ?></strong><br />
/*------------------------------------------------*/<br />
/*------------[SI Captcha Anti-Spam]--------------*/<br />
/*------------------------------------------------*/<br />
<?php
$styles = $this->si_captcha_get_styles();
   foreach ($styles as $line)  {
        echo $line . "<br />\n";
   }
?>

    </div>
    <br />


    <label for="si_captcha_comment_label_position"><?php echo __('CAPTCHA input label position on the comment form:', 'si-captcha'); ?></label>
      <select id="si_captcha_comment_label_position" name="si_captcha_comment_label_position">
<?php
$captcha_pos_array = array(
'input-label-required' => __('input-label-required', 'si-captcha'), // wp
'label-required-input' => __('label-required-input', 'si-captcha'), // bp
'label-required-linebreak-input' => __('label-required-linebreak-input', 'si-captcha'), // wp-twenty ten
'label-input-required' => __('label-input-required', 'si-captcha'), // suffusion theme on wp

);
$selected = '';
foreach ($captcha_pos_array as $k => $v) {
 if ($si_captcha_opt['comment_label_position'] == "$k")  $selected = ' selected="selected"';
 echo '<option value="'.esc_attr($k).'"'.$selected.'>'.esc_html($v).'</option>'."\n";
 $selected = '';
}
?>
</select>
        <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_captcha_comment_label_position_tip');"><?php _e('help', 'si-captcha'); ?></a>
        <div style="text-align:left; display:none" id="si_captcha_comment_label_position_tip">
        <?php _e('Changes position of the CAPTCHA input labels on the comment form. Some themes have different label positions on the comment form. After changing this setting, be sure to view the comments to verify the setting is correct.', 'si-captcha') ?>
        </div>
        <br />


       </td>
    </tr>



  <tr>

        <th scope="row" style="width: 75px;"><?php _e('Options:', 'si-captcha') ?></th>
        <td>


    <input name="si_captcha_enable_session" id="si_captcha_enable_session" type="checkbox" <?php if ( $si_captcha_opt['enable_session'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="si_captcha_enable_session"><?php _e('Enable PHP sessions.', 'si-captcha'); ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_captcha_enable_session_tip');"><?php _e('help', 'si-captcha'); ?></a>
    <div style="text-align:left; display:none" id="si_captcha_enable_session_tip">
    <?php _e('Enables PHP session handling. Only enable this if you have CAPTCHA token errors. Enable this setting to use PHP sessions for the CAPTCHA. PHP Sessions must be supported by your web host or there may be session errors.', 'si-captcha'); ?>
    </div>
    <br />

        <?php
         if ( $si_captcha_opt['enable_session'] != 'true' ){
            $check_this_dir = untrailingslashit( $si_captcha_dir_ns );
           if(is_writable($check_this_dir)) {
				//echo '<span style="color: green">OK - Writable</span> ' . substr(sprintf('%o', fileperms($check_this_dir)), -4);
           } else if(!file_exists($check_this_dir)) {
              echo '<span style="color: red;">';
              echo __('There is a problem with the directory', 'si-captcha');
              echo ' /captcha/cache/. ';
	          echo __('The directory is not found, a <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank">permissions</a> problem may have prevented this directory from being created.', 'si-captcha');
              echo ' ';
              echo __('Fixing the actual problem is recommended, but you can check this setting on the SI CAPTCHA options page: "Use PHP sessions" and the captcha will work (if PHP sessions are supported by your web host).', 'si-captcha');
              echo '</span><br />';
           } else {
             echo '<span style="color: red;">';
             echo __('There is a problem with the directory', 'si-captcha') .' /captcha/cache/. ';
             echo __('The directory Unwritable (<a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank">fix permissions</a>)', 'si-captcha').'. ';
             echo __('Permissions are: ', 'si-captcha');
             echo ' ';
             echo substr(sprintf('%o', fileperms($check_this_dir)), -4);
             echo ' ';
             echo __('Fixing this may require assigning 0755 permissions or higher (e.g. 0777 on some hosts. Try 0755 first, because 0777 is sometimes too much and will not work.)', 'si-captcha');
             echo ' ';
             echo __('Fixing the actual problem is recommended, but you can check this setting on the SI CAPTCHA options page: "Use PHP sessions" and the captcha will work (if PHP sessions are supported by your web host).', 'si-captcha');
             echo '</span><br />';
          }
         }

        ?>

    </td>
    </tr>


        </table>

  <table cellspacing="2" cellpadding="5" class="form-table">

        <tr>
          <th scope="row" style="width: 75px;"><?php echo __('Error Messages:', 'si-captcha'); ?></th>
         <td>


        <strong><?php _e('Customize Text:', 'si-captcha'); ?></strong>
        <a style="cursor:pointer;" title="<?php echo __('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_captcha_labels_tip');"><?php echo __('help', 'si-captcha'); ?></a>
       <div class="fscf_tip" id="si_captcha_labels_tip">
       <?php echo __('These fields can be filled in to override the error message that displays when the form is submitted and the CAPTCHA does not pass validation.', 'si-captcha'); ?>
       </div>
       <br />


        <label for="si_captcha_required_indicator"><?php echo __('Required', 'si-captcha'); ?></label><input name="si_captcha_required_indicator" id="si_captcha_required_indicator" type="text" value="<?php echo esc_attr($si_captcha_opt['required_indicator']);  ?>" size="50" /><br />
        <label for="si_captcha_error_spambot"><?php echo __('Possible spam bot', 'si-captcha'); ?></label><input name="si_captcha_error_spambot" id="si_captcha_error_spambot" type="text" value="<?php echo esc_attr($si_captcha_opt['error_spambot']);  ?>" size="50" /><br />
        <label for="si_captcha_error_incorrect"><?php echo __('Wrong CAPTCHA', 'si-captcha'); ?></label><input name="si_captcha_error_incorrect" id="si_captcha_error_incorrect" type="text" value="<?php echo esc_attr($si_captcha_opt['error_incorrect']);  ?>" size="50" /><br />
        <label for="si_captcha_error_empty"><?php echo __('Empty CAPTCHA', 'si-captcha'); ?></label><input name="si_captcha_error_empty" id="si_captcha_error_empty" type="text" value="<?php echo esc_attr($si_captcha_opt['error_empty']);  ?>" size="50" /><br />
        <label for="si_captcha_error_token"><?php echo __('Missing CAPTCHA token', 'si-captcha'); ?></label><input name="si_captcha_error_token" id="si_captcha_error_token" type="text" value="<?php echo esc_attr($si_captcha_opt['error_token']);  ?>" size="50" /><br />
        <label for="si_captcha_error_unreadable"><?php echo __('Unreadable CAPTCHA token', 'si-captcha'); ?></label><input name="si_captcha_error_unreadable" id="si_captcha_error_unreadable" type="text" value="<?php echo esc_attr($si_captcha_opt['error_unreadable']);  ?>" size="50" /><br />
        <label for="si_captcha_error_cookie"><?php echo __('Unreadable CAPTCHA cookie', 'si-captcha'); ?></label><input name="si_captcha_error_cookie" id="si_captcha_error_cookie" type="text" value="<?php echo esc_attr($si_captcha_opt['error_cookie']);  ?>" size="50" /><br />
        <label for="si_captcha_error_error"><?php echo __('ERROR', 'si-captcha'); ?></label><input name="si_captcha_error_error" id="si_captcha_error_error" type="text" value="<?php echo esc_attr($si_captcha_opt['error_error']);  ?>" size="50" /><br />
        <label for="si_captcha_label_captcha"><?php echo __('CAPTCHA Code', 'si-captcha'); ?></label><input name="si_captcha_label_captcha" id="si_captcha_label_captcha" type="text" value="<?php echo esc_attr($si_captcha_opt['label_captcha']);  ?>" size="50" /><br />
        <label for="si_captcha_tooltip_captcha"><?php echo __('CAPTCHA Image', 'si-captcha'); ?></label><input name="si_captcha_tooltip_captcha" id="si_captcha_tooltip_captcha" type="text" value="<?php echo esc_attr($si_captcha_opt['tooltip_captcha']);  ?>" size="50" /><br />
        <label for="si_captcha_tooltip_refresh"><?php echo __('Refresh Image', 'si-captcha'); ?></label><input name="si_captcha_tooltip_refresh" id="si_captcha_tooltip_refresh" type="text" value="<?php echo esc_attr($si_captcha_opt['tooltip_refresh']);  ?>" size="50" />

        </td>
    </tr>

          </table>
        </fieldset>

    <input name="si_captcha_donated" id="si_captcha_donated" type="checkbox" <?php if( $si_captcha_opt['donated'] == 'true' ) echo 'checked="checked"'; ?> />
    <label name="si_captcha_donated" for="si_captcha_donated"><?php echo __('I have donated to help contribute for the development of this plugin. This checkbox makes the donate button go away', 'si-captcha'); ?></label>
    <br />

    <p class="submit">
       <input type="submit" name="submit" class="button button-primary" value="<?php _e('Save Changes', 'si-captcha') ?>" />
    </p>

</form>

<h3><?php _e('Don\'t Forget to Test the CAPTCHA', 'si-captcha') ?></h3>

<p>
<?php  _e('After installing or updating plugins and themes, be sure to test the CAPTCHA on each form where it is enabled. It should display, allow actions on valid code, and block actions on invalid code.', 'si-captcha');
?>
</p>

<?php
if ($si_captcha_opt['donated'] != 'true') {
 ?>

  <table style="border:none; width:850px;">
  <tr>
  <td>
  <div style="width:385px;height:200px; float:left;background-color:white;padding: 10px 10px 10px 10px; border: 1px solid #ddd; background-color:#FFFFE0;">
		<div>
         <h3><?php echo __('Donate', 'si-captcha'); ?></h3>

<?php
_e('If you find this plugin useful to you, please consider making a donation to help contribute to my time invested and to further development. Thanks for your kind support!', 'si-captcha') ?><br />
<a style="cursor:pointer;" title="<?php esc_attr_e('You have 1 message from Mike Challis', 'si-captcha'); ?>" onclick="toggleVisibility('si_captcha_mike_challis_tip');"><?php _e('You have 1 message from Mike Challis', 'si-captcha'); ?></a>
<br /><br />
   </div>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick" />
<input type="hidden" name="hosted_button_id" value="KXJWLPPWZG83S" />
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" style="border:none;" name="submit" alt="Paypal Donate" />
<img alt="" style="border:none;" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
</form>
  </td>
 </tr>
 </table>

<br />

<div class="fscf_tip" id="si_captcha_mike_challis_tip">
<img src="<?php echo  $si_captcha_base_url; ?>si-captcha.jpg" class="fscf_left fscf_img" width="250" height="185" alt="Mike Challis" /><br />
<?php _e('Mike Challis says: "Hello, I have many hours coding this plugin just for you. Please consider making a donation. If you are not able to, that is OK.', 'si-captcha'); ?>
<?php echo ' '; _e('Please also rate my plugin."', 'si-captcha'); ?>
 <a href="https://wordpress.org/support/plugin/si-captcha-for-wordpress/reviews/" target="_blank"><?php _e('Rate This', 'si-captcha'); ?></a>.
<br /><br />
<a style="cursor:pointer;" title="Close" onclick="toggleVisibility('si_captcha_mike_challis_tip');"><?php _e('Close this message', 'si-captcha'); ?></a>
<div class="clear"></div><br />
</div>

<?php
}
?>

<p><strong><?php _e('WordPress plugins by Mike Challis:', 'si-captcha') ?></strong></p>
<ul>
<li><a href="https://wordpress.org/plugins/si-contact-form/" target="_blank"><?php echo __('Fast Secure Contact Form', 'si-captcha'); ?></a></li>
<li><a href="https://wordpress.org/plugins/fast-secure-recaptcha/" target="_blank"><?php echo __('Fast Secure reCAPTCHA', 'si-captcha'); ?></a></li>
<li><a href="https://wordpress.org/plugins/si-captcha-for-wordpress/" target="_blank"><?php echo __('SI CAPTCHA Anti-Spam', 'si-captcha'); ?></a></li>
<li><a href="https://wordpress.org/plugins/visitor-maps/" target="_blank"><?php echo __('Visitor Maps and Who\'s Online', 'si-captcha'); ?></a></li>
</ul>

