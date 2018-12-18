<?php

if ( ! function_exists( 'et_core_data_init' ) ):
function et_core_data_init() {}
endif;


if ( ! function_exists( 'et_' ) ):
function et_() {
	global $et_;

	if ( ! $et_ ) {
		$et_ = ET_Core_Data_Utils::instance();
	}

	return $et_;
}
endif;


if ( ! function_exists( 'et_html_attr' ) ):
/**
 * Generates a properly escaped attribute string.
 *
 * @param string $name         The attribute name.
 * @param string $value        The attribute value.
 * @param bool   $space_before Whether or not the result should start with a space. Default is `true`.
 *
 * @return string
 */
function et_html_attr( $name, $value, $space_before = true ) {
	$result = ' ' . esc_attr( $name ) . '="' . esc_attr( $value ) . '"';

	return $space_before ? $result : trim( $result );
}
endif;

if ( ! function_exists( 'et_html_attrs' ) ):
/**
 * Generate properly escaped attributes string
 *
 * @since 3.10
 *
 * @param array $attributes Array of attributes
 *
 * @return string
 */
function et_html_attrs( $attributes = array() ) {
	$output = '';

	foreach ( $attributes as $name => $value ) {
		$parsed_value = is_array( $value ) ? implode( ' ', $value ) : $value;

		$output .= et_html_attr( $name, $parsed_value );
	}

	return $output;
}
endif;


if ( ! function_exists( 'et_sanitized_previously' ) ):
/**
 * Semantical previously sanitized acknowledgement
 *
 * @deprecated {@see et_core_sanitized_previously()}
 *
 * @since 3.17.3 Deprecated
 *
 * @param mixed $value The value being passed-through
 *
 * @return mixed
 */
function et_sanitized_previously( $value ) {
	et_debug( "You're Doing It Wrong! Attempted to call " . __FUNCTION__ . "(), use et_core_sanitized_previously() instead." );
	return $value;
}
endif;

if ( ! function_exists( 'et_core_sanitized_previously' ) ):
/**
 * Semantical previously sanitized acknowledgement
 *
 * @since 3.17.3
 *
 * @param mixed $value The value being passed-through
 *
 * @return mixed
 */
function et_core_sanitized_previously( $value ) {
	return $value;
}
endif;

/**
 * Pass thru semantical previously escaped acknowledgement
 *
 * @since 3.17.3
 *
 * @param string value being passed through
 * @return string
 */
if ( ! function_exists( 'et_core_esc_previously' ) ) :
function et_core_esc_previously( $passthru ) {
	return $passthru;
}
endif;

/**
 * Pass thru function used to pacfify phpcs sniff.
 * Used when the nonce is checked elsewhere.
 *
 * @since 3.17.3
 *
 * @return void
 */
if ( ! function_exists( 'et_core_nonce_verified_previously' ) ) :
function et_core_nonce_verified_previously() {
	// :)
}
endif;

/**
 * Pass thru semantical escaped by WordPress core acknowledgement
 *
 * @since 3.17.3
 *
 * @param string value being passed through
 * @return string
 */
if ( ! function_exists( 'et_core_esc_wp' ) ) :
function et_core_esc_wp( $passthru ) {
	return $passthru;
}
endif;

/**
 * Pass thru semantical intentionally unescaped acknowledgement
 *
 * @since 3.17.3
 *
 * @param string value being passed through
 * @param string excuse the value is allowed to be unescaped
 * @return string
 */
if ( ! function_exists( 'et_core_intentionally_unescaped' ) ) :
function et_core_intentionally_unescaped( $passthru, $excuse ) {
	// Add valid excuses as they arise
	$valid_excuses = array(
		'cap_based_sanitized',
		'fixed_string',
		'react_jsx',
		'html',
		'underscore_template',
	);

	if ( ! in_array( $excuse, $valid_excuses ) ) {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'This is not a valid excuse to not escape the passed value.', 'et_core' ), esc_html( et_get_theme_version() ) );
	}

	return $passthru;
}
endif;

/**
 * Sanitize value depending on user capability
 *
 * @since 3.17.3
 *
 * @return string value being passed through
 */
if ( ! function_exists( 'et_core_sanitize_value_by_cap' ) ) :
function et_core_sanitize_value_by_cap( $passthru, $sanitize_function = 'et_sanitize_html_input_text', $cap = 'unfiltered_html' ) {
	if ( ! current_user_can( $cap ) ) {
		$passthru = $sanitize_function( $passthru );
	}

	return $passthru;
}
endif;

/**
 * Pass thru semantical intentionally unsanitized acknowledgement
 *
 * @since 3.17.3
 *
 * @param string value being passed through
 * @param string excuse the value is allowed to be unsanitized
 * @return string
 */
if ( ! function_exists( 'et_core_intentionally_unsanitized' ) ) :
function et_core_intentionally_unsanitized( $passthru, $excuse ) {
	// Add valid excuses as they arise
	$valid_excuses = array();

	if ( ! in_array( $excuse, $valid_excuses ) ) {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'This is not a valid excuse to not sanitize the passed value.', 'et_core' ), esc_html( et_get_theme_version() ) );
	}

	return $passthru;
}
endif;

/**
 * Fixes unclosed HTML tags
 *
 * @since 3.18.4
 *
 * @param string $content source HTML
 *
 * @return string
 */
if ( ! function_exists( 'et_core_fix_unclosed_html_tags' ) ):
function et_core_fix_unclosed_html_tags( $content ) {
	// Exit if source has no HTML tags or we miss what we need to fix them anyway
	if ( false === strpos( $content, '<' ) || ! class_exists( 'DOMDocument' ) ) {
		return $content;
	}

	$doc = new DOMDocument();
	@$doc->loadHTML( sprintf(
		'<html><head>%s</head><body>%s</body></html>',
		// Use WP charset
		sprintf( '<meta http-equiv="content-type" content="text/html; charset=%s" />', get_bloginfo( 'charset' ) ),
		// Wrap content within a container to force a single node
		sprintf( '<div>%s</div>', $content )
	) );

	// Grab fixed content and remove its container
	return preg_replace(
		'|<div>([\s\S]+?)</div>|',
		'$1',
		$doc->saveHTML( $doc->getElementsByTagName( 'body' )->item( 0 )->childNodes->item( 0 ) )
	);
}
endif;
