<?php

/**
 * Class Forminator_Addon_Aweber_Oauth
 * Helpers for OAuth
 */
class Forminator_Addon_Aweber_Oauth {

	/**
	 * Create Signature Base
	 *
	 * @since 1.0
	 *
	 * @param mixed $method String name of HTTP method, such as "GET"
	 * @param mixed $url    URL where this request will go
	 * @param mixed $data   Array of params for this request. This should
	 *                      include ALL oauth properties except for the signature.
	 *
	 * @return string
	 */
	public static function create_signature_base( $method, $url, $data ) {
		$request_method = $method;
		$request_url    = $url;
		$request_data   = $data;

		$method = rawurlencode( strtoupper( $method ) );
		$query  = wp_parse_url( $url, PHP_URL_QUERY );
		if ( $query ) {
			$parts = explode( '?', $url, 2 );
			$url   = array_shift( $parts );
			$items = explode( '&', $query );
			foreach ( $items as $item ) {
				list( $key, $value ) = explode( '=', $item );
				$data[ rawurldecode( $key ) ] = rawurldecode( $value );
			}
		}
		$url  = rawurlencode( $url );
		$data = rawurlencode( self::collapse_data_for_signature( $data ) );

		$signature_base = $method . '&' . $url . '&' . $data;

		/**
		 * Filter Signature Base that will be used to sign AWeber request
		 *
		 * @since 1.3
		 *
		 * @param string $signature_base
		 * @param string $request_method
		 * @param string $request_url
		 * @param array  $request_data
		 */
		$signature_base = apply_filters( 'forminator_addon_aweber_oauth_signature_base', $signature_base, $request_method, $request_url, $request_data );

		return $signature_base;
	}

	/**
	 * collapse data for signature
	 *
	 * @since 1.0
	 *
	 * Turns an array of request data into a string, as used by the oauth
	 * signature
	 *
	 * @param mixed $data
	 *
	 * @return string
	 */
	public static function collapse_data_for_signature( $data ) {
		ksort( $data );
		$collapse = '';
		foreach ( $data as $key => $val ) {
			if ( ! empty( $collapse ) ) {
				$collapse .= '&';
			}
			$collapse .= $key . '=' . rawurlencode( $val );
		}

		return $collapse;
	}

	/**
	 * Creates a signature on the signature base and the signature key
	 *
	 * @since  1.0
	 *
	 * @param mixed $base Base string of data to sign
	 * @param mixed $key  Key to sign the data with
	 *
	 * @access public
	 * @return string   The signature
	 */
	public static function create_signature( $base, $key ) {

		$signature = base64_encode( hash_hmac( 'sha1', $base, $key, true ) );//phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

		/**
		 * Signature that will be used on AWeber oauth_signature
		 *
		 * @since 1.3
		 *
		 * @param string $signature
		 * @param string $base
		 * @param string $key
		 */
		$signature = apply_filters( 'forminator_addon_aweber_oauth_signature', $signature, $base, $key );

		return $signature;
	}

	/**
	 * Creates a key that will be used to sign our signature.  Signatures
	 * are signed with the consumerSecret for this consumer application and
	 * the token secret of the user that the application is acting on behalf
	 * of.
	 *
	 * @since 1.0
	 *
	 * @param $application_key
	 * @param $oauth_token_secret
	 *
	 * @return string
	 */
	public static function create_signature_key( $application_key, $oauth_token_secret ) {
		$signature_key = $application_key . '&' . $oauth_token_secret;

		/**
		 * Signature that will be used on AWeber oauth_signature
		 *
		 * @since 1.3
		 *
		 * @param string $signature_key
		 * @param string $application_key
		 * @param string $oauth_token_secret
		 */
		$signature_key = apply_filters( 'forminator_addon_aweber_oauth_signature_key', $signature_key, $application_key, $oauth_token_secret );

		return $signature_key;
	}

	/**
	 * Generate oauth_nonce
	 *
	 * @since 1.0
	 *
	 * @param int $timestamp
	 *
	 * @return string
	 */
	public static function generate_oauth_nonce( $timestamp = 0 ) {
		if ( ! $timestamp ) {
			$timestamp = time();
		}

		$oauth_nonce = md5( $timestamp . '-' . wp_rand( 10000, 99999 ) . '-' . uniqid() );

		/**
		 * Filter required oauth_nonce data of AWeber that need to be send on API Request
		 *
		 * @since 1.3
		 *
		 * @param string $oauth_nonce
		 * @param int    $timestamp current timestamp for future reference
		 */
		$oauth_nonce = apply_filters( 'forminator_addon_aweber_oauth_nonce', $oauth_nonce, $timestamp );


		return $oauth_nonce;
	}
}
