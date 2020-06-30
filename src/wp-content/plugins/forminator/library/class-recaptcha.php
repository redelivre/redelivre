<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Recaptcha
 *
 * Handle ReCaptcha verification
 *
 * @since 1.5.3
 */
class Forminator_Recaptcha {

	/**
	 * @var string
	 * @since 1.5.3
	 */
	private $secret_key = '';

	/**
	 * Forminator_Recaptcha constructor.
	 *
	 * @since 1.5.3
	 *
	 * @param $secret_key
	 */
	public function __construct( $secret_key ) {
		$this->secret_key = $secret_key;
	}

	/**
	 * Verify recaptcha
	 *
	 * @since 1.5.3
	 *
	 * @param        $user_response
	 * @param null   $remote_ip
	 * @param string $score
	 *
	 * @return bool|WP_Error (true on success, WP_Error on fail)
	 */
	public function verify( $user_response, $remote_ip = null, $score = '' ) {

		$url = $this->get_verify_endpoint();

		$args = array(
			'method' => 'POST',
			'body'   => array(
				'secret'   => $this->secret_key,
				'response' => $user_response,
				'remoteip' => $remote_ip ? $remote_ip : Forminator_Geo::get_user_ip(),
			),
		);


		$res = wp_remote_request( $url, $args );

		if ( is_wp_error( $res ) ) {
			forminator_maybe_log( __METHOD__, $res );

			return $res;
		}

		$body = wp_remote_retrieve_body( $res );
		if ( empty( $body ) ) {
			$error = new WP_Error( 'recaptcha_empty_response', 'Empty response', array( $res ) );
			forminator_maybe_log( __METHOD__, $error );

			return $error;
		}

		$json = json_decode( $body, true );
		if ( empty( $json ) ) {
			$error = new WP_Error( 'recaptcha_failed_decode', 'Fail to decode', array( $body ) );
			forminator_maybe_log( __METHOD__, $error );

			return $error;
		}

		if( ! empty( $score ) && ! empty( $json['score'] ) && floatval( $json['score'] )  < floatval( $score ) ) {
			$error = new WP_Error( 'recaptcha_failed_score', 'Score is lower than expected.', array( $body ) );
			forminator_maybe_log( __METHOD__, $error );

			return $error;
		}

		// success verify
		if ( isset( $json['success'] ) && true === $json['success'] ) {
			return true;
		}

		// read error
		$error = new WP_Error( 'recaptcha_failed_verify', 'Fail to verify', array( $json ) );
//		forminator_maybe_log( __METHOD__, $error );

		return $error;
	}

	/**
	 * Get Recaptcha endpoint to verify user response
	 *
	 * @since 1.5.3
	 *
	 * @return string
	 */
	private function get_verify_endpoint() {
		$endpoint = 'https://www.google.com/recaptcha/api/siteverify';

		/**
		 * Filter endpoint to be used for verify recaptcha
		 *
		 * @since 1.5.3
		 *
		 * @param string $endpoint
		 *
		 * @return string
		 */
		$endpoint = apply_filters( 'forminator_recaptcha_verify_endpoint', $endpoint );

		return $endpoint;
	}
}
