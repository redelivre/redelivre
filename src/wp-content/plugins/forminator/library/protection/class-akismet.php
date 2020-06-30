<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Forminator Akismet protection
 * Use the akismet api to check for possible spam posted
 *
 * Akismet API: http://akismet.com/development/api/
 */
class Forminator_Akismet extends Forminator_Spam_Protection {


	/**
	 * Plugin instance
	 *
	 * @var null|Forminator_Akismet
	 */
	private static $instance = null;

	/**
	 * Return the plugin instance
	 *
	 * @return Forminator_Akismet
	 *
	 * @since 1.0
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Check if the plugin or setting is enabled
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function is_enabled() {
		// Akismet v3.0+
		if ( is_callable( array( 'Akismet', 'get_api_key' ) ) ) {
			return (bool) Akismet::get_api_key();
		}

		if ( function_exists( 'akismet_get_key' ) ) {
			return (bool) akismet_get_key();
		}

		return false;
	}

	/**
	 * Handle spam protection
	 *
	 * @see _handle_spam_protection
	 *
	 * @since 1.0
	 * @param bool $is_spam - if the data is spam
	 * @param array $posted_params - the posted parameters
	 * @param int $form_id - the form id
	 * @param string $form_type - the form type
	 *
	 * @return bool $is_spam
	 */
	protected function handle_spam_protection( $is_spam, $posted_params, $form_id, $form_type ) {

		$post_data = array(
			'blog' 			=> get_option( 'home' ),
			'user_ip' 		=> Forminator_Geo::get_user_ip(),
			'user_agent' 	=> $_SERVER['HTTP_USER_AGENT'],
			'referrer' 		=> $_SERVER['HTTP_REFERER'],
			'comment_type'	=> $form_type,
			'content'		=> ''
		);
		$has_akismet_data = false;
		foreach ( $posted_params as $param ) {
			if ( isset( $param['name'] ) && isset( $param['value'] ) ) {
				$has_akismet_data 	= true;
				if ( filter_var( $param['value'], FILTER_VALIDATE_EMAIL ) ) {
					$post_data['comment_author_email'] 	= $param['value'];
				}
				if ( is_array( $param['value'] ) ) {
					$post_data['content'] .= "\n\n" . implode( ", ", $param['value'] );
				} else {
					$post_data['content'] .= "\n\n" . $param['value'];
				}

			}
		}

		if ( $has_akismet_data ) {
			if ( is_user_logged_in() ) {
				$current_user 	= wp_get_current_user();
				if ( !empty( $current_user->user_firstname ) ) {
					$user_name 	= $current_user->user_firstname . ' ' . $current_user->user_lastname;
				} elseif ( !empty( $current_user->display_name ) ) {
					$user_name 	= $current_user->display_name;
				} else {
					$user_name 	= $current_user->user_login;
				}

				$post_data['comment_author_email'] 	= $current_user->user_email;
				$post_data['comment_author'] 		= $user_name;
			}
			$post_data['content'] = trim( $post_data['content'] );

			$permalink = get_permalink();
			if ( false !== $permalink ) {
				$post_data['permalink'] = $permalink;
			}

			$ignore = array( 'HTTP_COOKIE', 'HTTP_COOKIE2', 'PHP_AUTH_PW' );

			foreach ( $_SERVER as $key => $value ) {
				if ( ! in_array( $key, (array) $ignore, true ) ) {
					$post_data["$key"] = $value;
				}
			}

			$is_spam = $this->_akismet_check( $post_data, $form_id );
		}

		return $is_spam;
	}

	/**
	 * Check akismet if the data is spam
	 *
	 * @since 1.0
	 * @param array $post_data - the post data
	 * @param int $form_id - the form id
	 *
	 * @return bool
	 */
	private function _akismet_check( $post_data, $form_id ) {
		global $akismet_api_host, $akismet_api_port;
		$is_spam 	= false;
		$query 		= $this->_build_query( $post_data );

		if ( is_callable( array( 'Akismet', 'http_post' ) ) ) { // Akismet v3.0+
			$response = Akismet::http_post( $query, 'comment-check' );
		} else {
			$response = akismet_http_post( $query, $akismet_api_host, '/1.1/comment-check', $akismet_api_port );
		}

		//Response will always be an array of array( $response['headers'], $response['body'] )
		if ( 'true' === $response[1] ) {
			$is_spam = true;
		}

		return apply_filters( 'forminator_akismet_is_spam', $is_spam, $post_data, $form_id );
	}


	/**
	 * Build http query
	 * The default build_query function misses out alot of things
	 *
	 * @since 1.0
	 * @param array $args - the arguments
	 * @param string $key
	 *
	 * @return string
	 */
	private function _build_query( $args, $key = '' ) {
		$sep = '&';
		$ret = array();

		foreach ( (array) $args as $k => $v ) {
			$k = rawurlencode( $k );

			if ( ! empty( $key ) ) {
				$k = $key . '%5B' . $k . '%5D';
			}

			if ( null === $v ) {
				continue;
			} elseif ( false === $v ) {
				$v = '0';
			}

			if ( is_array( $v ) || is_object( $v ) ) {
				array_push( $ret, $this->_build_query( $v, $k ) );
			} else {
				array_push( $ret, $k . '=' . rawurlencode( $v ) );
			}
		}

		return implode( $sep, $ret );
	}
}
