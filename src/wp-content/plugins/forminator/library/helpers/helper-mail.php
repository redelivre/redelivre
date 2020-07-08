<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Mail Helper function
 **/

/**
 * Set the message variables
 *
 * @since 1.0
 * @param $embed_id
 * @param $embed_title
 * @param $embed_url
 * @param $user_name
 * @param $user_email
 * @param $user_login
 * @param $site_url
 *
 * @return array
 */
function forminator_set_message_vars( $embed_id, $embed_title, $embed_url, $user_name, $user_email, $user_login, $site_url ) {
	$message_vars                = array();
	$message_vars['user_ip']     = Forminator_Geo::get_user_ip();
	$message_vars['date_mdy']    = date( 'm/d/Y' );// phpcs:ignore
	$message_vars['date_dmy']    = date( 'd/m/Y' );// phpcs:ignore
	$message_vars['embed_id']    = $embed_id;
	$message_vars['embed_title'] = $embed_title;
	$message_vars['embed_url']   = $embed_url;
	$message_vars['user_agent']  = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : 'none';
	$message_vars['refer_url']   = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : $embed_url;
	$message_vars['http_refer']  = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : $embed_url;
	$message_vars['user_name']   = $user_name;
	$message_vars['user_email']  = $user_email;
	$message_vars['user_login']  = $user_login;
	$message_vars['site_url']    = $site_url;

	return $message_vars;
}

/**
 * Get global sender email from Global Settings
 *
 * @since 1.1
 * @return string
 */
function get_global_sender_email_address() {
	$global_sender_email = get_option( 'forminator_sender_email_address', 'noreply@' . wp_parse_url( get_site_url(), PHP_URL_HOST ) );

	return apply_filters( 'forminator_sender_email_address', $global_sender_email );
}

/**
 * Get global sender name from Global Settings
 *
 * @since 1.1
 * @return string
 */
function get_global_sender_name() {
	$global_sender_email = get_option( 'forminator_sender_name', get_option( 'blogname' ) );

	return apply_filters( 'forminator_sender_name', $global_sender_email );
}
