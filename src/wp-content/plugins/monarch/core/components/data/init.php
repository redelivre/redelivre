<?php

if ( ! function_exists( 'et_core_data_init' ) ):
function et_core_data_init() {}
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
 * @param mixed $value The value being passed-through
 *
 * @return mixed
 */
function et_sanitized_previously( $value ) {
	return $value;
}
endif;
