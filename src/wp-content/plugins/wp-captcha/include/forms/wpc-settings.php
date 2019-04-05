<?php
/**
* @version 1.0.0
* @package WP Captcha
*/

if( isset($_POST['captcha_restore_settings']) ) {
            
	$wpc_restore_value_array = array(
		"wpc_captcha_title" =>  "",
		"wpc_enable_login_form" => "true",
		"wpc_enable_register_form" =>  "true",
		"wpc_enable_forgot_form" =>  "true",
		"wpc_enable_comment_form" =>  "true",
		"wpc_enable_hide_comment_form" =>  "true"
	);
	
	$wpc_restore_setting_value = serialize($wpc_restore_value_array);
	$wpc_option = "_wpc_captcha_settings";
	update_option( $wpc_option, $wpc_restore_setting_value );
	
	$message =  __( "Settings restore Successfully.", "wp-captcha");
}    

if( isset($_POST["captcha_settings"]) ) {
	
	if( empty($_POST["wpc_enable_login_form"]) )
	$_POST["wpc_enable_login_form"] = "false";
	else
	$_POST["wpc_enable_login_form"] = $_POST["wpc_enable_login_form"];
	
	if( empty($_POST["wpc_enable_register_form"]) )
	$_POST["wpc_enable_register_form"] = "false";
	else
	$_POST["wpc_enable_register_form"] = $_POST["wpc_enable_register_form"];
	
	if( empty($_POST["wpc_enable_forgot_form"]) )
	$_POST["wpc_enable_forgot_form"] = "false";
	else
	$_POST["wpc_enable_forgot_form"] = $_POST["wpc_enable_forgot_form"];
	
	if( empty($_POST["wpc_enable_comment_form"]) )
	$_POST["wpc_enable_comment_form"] = "false";
	else
	$_POST["wpc_enable_comment_form"] = $_POST["wpc_enable_comment_form"];
	
	if( empty($_POST["wpc_enable_hide_comment_form"]) )
	$_POST["wpc_enable_hide_comment_form"] = "false";
	else
	$_POST["wpc_enable_hide_comment_form"] = $_POST["wpc_enable_hide_comment_form"];
	
	$wpc_value_array = array(
		"wpc_captcha_title"   =>  $_POST["wpc_captcha_title"],
		"wpc_enable_login_form" => $_POST["wpc_enable_login_form"],
		"wpc_enable_register_form" =>  $_POST["wpc_enable_register_form"],
		"wpc_enable_forgot_form" =>  $_POST["wpc_enable_forgot_form"],
		"wpc_enable_comment_form" =>  $_POST["wpc_enable_comment_form"],
		"wpc_enable_hide_comment_form" =>  $_POST["wpc_enable_hide_comment_form"]
	);
	
	$wpc_setting_value = serialize($wpc_value_array);
	$wpc_option = "_wpc_captcha_settings";
	$deprecated = "";
	$autoload = true;
	if( get_option( $wpc_option ) ) 
	{
		$wpc_new_value = $wpc_setting_value;
		update_option( $wpc_option, $wpc_new_value );
	} 
	else 
	{
		add_option( $wpc_option, $wpc_setting_value, $deprecated, $autoload );   
	} 

	$message =  __( "Settings save Successfully.", "wp-captcha");
}   

$options = get_option( '_wpc_captcha_settings' );
$options = unserialize($options);

$wpc_captcha_title = ( $options['wpc_captcha_title'] != "" ) ? sanitize_text_field( $options['wpc_captcha_title'] ) : '';

$wpc_enable_login_form = ( $options['wpc_enable_login_form'] != "" ) ? sanitize_text_field( $options['wpc_enable_login_form'] ) : 'true';

$wpc_enable_register_form = ( $options['wpc_enable_register_form'] != "" ) ? sanitize_text_field( $options['wpc_enable_register_form'] ) : 'true';

$wpc_enable_forgot_form = ( $options['wpc_enable_forgot_form'] != "" ) ? sanitize_text_field( $options['wpc_enable_forgot_form'] ) : 'true';

$wpc_enable_comment_form = ( $options['wpc_enable_comment_form'] != "" ) ? sanitize_text_field( $options['wpc_enable_comment_form'] ) : 'true';

$wpc_enable_hide_comment_form = ( $options['wpc_enable_hide_comment_form'] != "" ) ? sanitize_text_field( $options['wpc_enable_hide_comment_form'] ) : 'true';

//$wpc_enable_contact_seven_form = ( $options['wpc_enable_contact_seven_form'] != "" ) ? sanitize_text_field( $options['wpc_enable_contact_seven_form'] ) : 'true';

if( !empty($message) ) {
	
	echo '<div id="message" class="wpc_update">';
	echo '<p><strong>';
	echo $message;
	echo '</strong></p>';
	echo '</div>';
}
?>

<form method="post" action="">
  <table class="form-table">
    <tbody>
      <tr valign="top">
        <th scope="row"> <label for="wpc_captcha_title">
            <?php _e( 'Captcha Title in Form', 'wp-captcha' ); ?>
          </label>
        </th>
        <td><input class="regular-text" type="text" name="wpc_captcha_title" id="wpc_captcha_title" value="<?php echo $wpc_captcha_title; ?>">
          <p class="description">
            <?php _e( 'Please enter here Captcha Title.', 'wp-captcha' ); ?>
          </p></td>
      </tr>
      <tr valign="top">
        <th scope="row"> <label for="wpc_enable_login_form">
            <?php _e( 'Login Form', 'wp-captcha' ); ?>
          </label>
        </th>
        <td><p class="description">
            <input type="checkbox" name="wpc_enable_login_form" value="true" <?php checked($wpc_enable_login_form, "true"); ?>>
            <?php _e( 'Enable Captcha for Login Form.', 'wp-captcha' ); ?>
          </p></td>
      </tr>
      <tr valign="top">
        <th scope="row"> <label for="wpc_enable_register_form">
            <?php _e( 'Registration Form', 'wp-captcha' ); ?>
          </label>
        </th>
        <td><p class="description">
            <input type="checkbox" name="wpc_enable_register_form" value="true" <?php checked($wpc_enable_register_form, "true"); ?>>
            <?php _e( 'Enable Captcha for Registration Form.', 'wp-captcha' ); ?>
          </p></td>
      </tr>
      <tr valign="top">
        <th scope="row"> <label for="wpc_enable_forgot_form">
            <?php _e( 'Forgot Password Form', 'wp-captcha' ); ?>
          </label>
        </th>
        <td><p class="description">
            <input type="checkbox" name="wpc_enable_forgot_form" value="true" <?php checked($wpc_enable_forgot_form, "true"); ?>>
            <?php _e( 'Enable Captcha for Forgot Password Form.', 'wp-captcha' ); ?>
          </p></td>
      </tr>
      <tr valign="top">
        <th scope="row"> <label for="wpc_enable_comment_form">
            <?php _e( 'Comments Form', 'wp-captcha' ); ?>
          </label>
        </th>
        <td><p class="description">
            <input type="checkbox" name="wpc_enable_comment_form" value="true" <?php checked($wpc_enable_comment_form, "true"); ?>>
            <?php _e( 'Enable Captcha for Comments Form.', 'wp-captcha' ); ?>
          </p></td>
      </tr>
      <tr valign="top">
        <th scope="row"> 
        	<label for="wpc_enable_hide_comment_form">
            
          	</label>
        </th>
        <td><p class="description">
            <input type="checkbox" name="wpc_enable_hide_comment_form" value="true" <?php checked($wpc_enable_hide_comment_form, "true"); ?>>
            <?php _e( 'Disable Captcha in Comments Form for Registered Users.', 'wp-captcha' ); ?>
          </p></td>
      </tr>
      <!--<tr valign="top">
        <th scope="row"> <label for="wpc_enable_contact_seven_form">
            <?php _e( 'Contact Form 7', 'wp-captcha' ); ?>
          </label>
        </th>
        <td><p class="description">
            <input type="checkbox" name="wpc_enable_contact_seven_form" value="true" <?php //checked($wpc_enable_contact_seven_form, "true"); ?>>
            <?php //_e( 'Enable Captcha for Contact Form Seven.', 'wp-captcha' ); ?>
          </p></td>
      </tr>-->
      <tr valign="top">
        <td colspan="2"><input type="submit" name="captcha_settings" id="captcha_settings" class="button button-primary" value="Save">
          <input type="submit" name="captcha_restore_settings" id="captcha_restore_settings" class="button button-primary" value="Restore Default"></td>
      </tr>
    </tbody>
  </table>
</form>
