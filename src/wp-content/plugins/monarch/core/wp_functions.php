<?php


if ( ! function_exists( '_sanitize_text_fields' ) ):
/**
 * Internal helper function to sanitize a string from user input or from the db
 *
 * @since 4.7.0
 * @access private
 *
 * @param string $str String to sanitize.
 * @param bool $keep_newlines optional Whether to keep newlines. Default: false.
 * @return string Sanitized string.
 */
function _sanitize_text_fields( $str, $keep_newlines = false ) {
	$filtered = wp_check_invalid_utf8( $str );

	if ( strpos( $filtered, '<' ) !== false ) {
		$filtered = wp_pre_kses_less_than( $filtered );
		// This will strip extra whitespace for us.
		$filtered = wp_strip_all_tags( $filtered, false );

		// Use html entities in a special case to make sure no later
		// newline stripping stage could lead to a functional tag
		$filtered = str_replace( "<\n", "&lt;\n", $filtered );
	}

	if ( ! $keep_newlines ) {
		$filtered = preg_replace( '/[\r\n\t ]+/', ' ', $filtered );
	}

	$filtered = trim( $filtered );
	$found    = false;

	while ( preg_match( '/%[a-f0-9]{2}/i', $filtered, $match ) ) {
		$filtered = str_replace( $match[0], '', $filtered );
		$found    = true;
	}

	if ( $found ) {
		// Strip out the whitespace that may now exist after removing the octets.
		$filtered = trim( preg_replace( '/ +/', ' ', $filtered ) );
	}

	return $filtered;
}
endif;


if ( ! function_exists( 'get_site' ) ):
/**
 * Retrieves site data given a site ID or site object.
 *
 * Site data will be cached and returned after being passed through a filter.
 * If the provided site is empty, the current site global will be used.
 *
 * @since 4.6.0
 *
 * @param WP_Site|int|null $site Optional. Site to retrieve. Default is the current site.
 * @return WP_Site|null The site object or null if not found.
 */
function get_site( $site = null ) {
	if ( empty( $site ) ) {
		$site = get_current_blog_id();
	}

	if ( $site instanceof WP_Site ) {
		$_site = $site;
	} elseif ( is_object( $site ) ) {
		$_site = new WP_Site( $site );
	} else {
		$_site = WP_Site::get_instance( $site );
	}

	if ( ! $_site ) {
		return null;
	}

	/**
	 * Fires after a site is retrieved.
	 *
	 * @since 4.6.0
	 *
	 * @param WP_Site $_site Site data.
	 */
	$_site = apply_filters( 'get_site', $_site );

	return $_site;
}
endif;


if ( ! function_exists( 'sanitize_textarea_field' ) ):
/**
 * Sanitizes a multiline string from user input or from the database.
 *
 * The function is like sanitize_text_field(), but preserves
 * new lines (\n) and other whitespace, which are legitimate
 * input in textarea elements.
 *
 * @see sanitize_text_field()
 *
 * @since 4.7.0
 *
 * @param string $str String to sanitize.
 * @return string Sanitized string.
 */
function sanitize_textarea_field( $str ) {
	$filtered = _sanitize_text_fields( $str, true );

	/**
	 * Filters a sanitized textarea field string.
	 *
	 * @since 4.7.0
	 *
	 * @param string $filtered The sanitized string.
	 * @param string $str      The string prior to being sanitized.
	 */
	return apply_filters( 'sanitize_textarea_field', $filtered, $str );
}
endif;


if ( ! function_exists( 'wp_doing_ajax' ) ):
function wp_doing_ajax() {
	/**
	 * Filters whether the current request is an Ajax request.
	 *
	 * @since 4.7.0
	 *
	 * @param bool $wp_doing_ajax Whether the current request is an Ajax request.
	 */
	return apply_filters( 'wp_doing_ajax', defined( 'DOING_AJAX' ) && DOING_AJAX );
}
endif;


if ( ! function_exists( 'wp_doing_cron' ) ):
function wp_doing_cron() {
	/**
	 * Filters whether the current request is a WordPress cron request.
	 *
	 * @since 4.8.0
	 *
	 * @param bool $wp_doing_cron Whether the current request is a WordPress cron request.
	 */
	return apply_filters( 'wp_doing_cron', defined( 'DOING_CRON' ) && DOING_CRON );
}
endif;
