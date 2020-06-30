<?php

require_once dirname( __FILE__ ) . '/class-wp-zapier-api-exception.php';
require_once dirname( __FILE__ ) . '/class-wp-zapier-api-not-found-exception.php';

/**
 * Class Forminator_Addon_Zapier_Wp_Api
 */
class Forminator_Addon_Zapier_Wp_Api {

	/**
	 * Instances of zapier api
	 * key is md5(_endpoint)
	 *
	 * @var array
	 */
	private static $_instances = array();

	/**
	 * Zapier endpoint of static webhook
	 *
	 * @var string
	 */
	private $_endpoint = '';

	/**
	 * Last data sent to zapier
	 *
	 * @since 1.0 Zapier Addon
	 * @var array
	 */
	private $_last_data_sent = array();

	/**
	 * Last data received from zapier
	 *
	 * @since 1.0 Zapier Addon
	 * @var array
	 */
	private $_last_data_received = array();

	/**
	 * Last URL requested
	 *
	 * @since 1.0 Zapier Addon
	 * @var string
	 */
	private $_last_url_request = '';

	/**
	 * Forminator_Addon_Zapier_Wp_Api constructor.
	 *
	 * @since 1.0 Zapier Addon
	 *
	 * @param $_endpoint
	 *
	 * @throws Forminator_Addon_Zapier_Wp_Api_Exception
	 */
	public function __construct( $_endpoint ) {
		global $wpdb;
		$wpdb->last_error;
		//prerequisites
		if ( ! $_endpoint ) {
			throw new Forminator_Addon_Zapier_Wp_Api_Exception( __( 'Missing required Static Webhook URL', Forminator::DOMAIN ) );
		}

		$this->_endpoint = $_endpoint;
	}

	/**
	 * Get singleton
	 *
	 * @since 1.0 Zapier Addon
	 *
	 * @param string $_endpoint
	 *
	 * @return Forminator_Addon_Zapier_Wp_Api|null
	 * @throws Forminator_Addon_Zapier_Wp_Api_Exception
	 */
	public static function get_instance( $_endpoint ) {
		if ( ! isset( self::$_instances[ md5( $_endpoint ) ] ) ) {
			self::$_instances[ md5( $_endpoint ) ] = new self( $_endpoint );
		}

		return self::$_instances[ md5( $_endpoint ) ];
	}

	/**
	 * Add custom user agent on request
	 *
	 * @since 1.0 Zapier Addon
	 *
	 * @param $user_agent
	 *
	 * @return string
	 */
	public function filter_user_agent( $user_agent ) {
		$user_agent .= ' ForminatorZapier/' . FORMINATOR_ADDON_ZAPIER_VERSION;

		/**
		 * Filter user agent to be used by zapier api
		 *
		 * @since 1.1
		 *
		 * @param string $user_agent current user agent
		 */
		$user_agent = apply_filters( 'forminator_addon_zapier_api_user_agent', $user_agent );

		return $user_agent;
	}

	/**
	 * HTTP Request
	 *
	 * @since 1.0 Zapier Addon
	 *
	 * @param string $verb
	 * @param        $path
	 * @param array  $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Zapier_Wp_Api_Exception
	 * @throws Forminator_Addon_Zapier_Wp_Api_Not_Found_Exception
	 */
	private function _request( $verb = 'GET', $path, $args = array() ) {
		// Adding extra user agent for wp remote request
		add_filter( 'http_headers_useragent', array( $this, 'filter_user_agent' ) );

		$url = trailingslashit( $this->_endpoint ) . $path;

		/**
		 * Filter zapier url to be used on sending api request
		 *
		 * @since 1.1
		 *
		 * @param string $url  full url with scheme
		 * @param string $verb `GET` `POST` `PUT` `DELETE` `PATCH`
		 * @param string $path requested path resource
		 * @param array  $args argument sent to this function
		 */
		$url = apply_filters( 'forminator_addon_zapier_api_url', $url, $verb, $path, $args );

		$this->_last_url_request = $url;

		$headers = array(
			'Accept'       => 'application/json',
			'Content-Type' => 'application/json',
		);

		/**
		 * Filter zapier headers to sent on api request
		 *
		 * @since 1.1
		 *
		 * @param array  $headers
		 * @param string $verb `GET` `POST` `PUT` `DELETE` `PATCH`
		 * @param string $path requested path resource
		 * @param array  $args argument sent to this function
		 */
		$headers = apply_filters( 'forminator_addon_zapier_api_request_headers', $headers, $verb, $path, $args );

		$_args = array(
			'method'  => $verb,
			'headers' => $headers,
		);

		// X-Hook-Test handler
		if ( isset( $args['is_test'] ) ) {
			if ( true === $args['is_test'] ) {
				// Add `X-Hook-Test` header to avoid execute task on zapier
				$_args['headers']['X-Hook-Test'] = 'true';
			}
			// always unset when exist
			unset( $args['is_test'] );
		}

		$request_data = $args;
		/**
		 * Filter zapier request data to be used on sending api request
		 *
		 * @since 1.1
		 *
		 * @param array  $request_data it will be `http_build_query`-ed when `GET` or `wp_json_encode`-ed otherwise
		 * @param string $verb         `GET` `POST` `PUT` `DELETE` `PATCH`
		 * @param string $path         requested path resource
		 */
		$args = apply_filters( 'forminator_addon_zapier_api_request_data', $request_data, $verb, $path );

		if ( 'GET' === $verb ) {
			$url .= ( '?' . http_build_query( $args ) );
		} else {
			$_args['body'] = wp_json_encode( $args );
		}

		$this->_last_data_sent = $args;

		$res         = wp_remote_request( $url, $_args );
		$wp_response = $res;

		remove_filter( 'http_headers_useragent', array( $this, 'filter_user_agent' ) );

		if ( is_wp_error( $res ) || ! $res ) {
			forminator_addon_maybe_log( __METHOD__, $res );
			throw new Forminator_Addon_Zapier_Wp_Api_Exception(
				__( 'Failed to process request, make sure your Webhook URL is correct and your server has internet connection.', Forminator::DOMAIN )
			);
		}

		if ( isset( $res['response']['code'] ) ) {
			$status_code = $res['response']['code'];
			$msg         = '';
			if ( $status_code > 400 ) {
				if ( isset( $res['response']['message'] ) ) {
					$msg = $res['response']['message'];
				}

				if ( 404 === $status_code ) {
					throw new Forminator_Addon_Zapier_Wp_Api_Not_Found_Exception( sprintf( __( 'Failed to processing request : %s', Forminator::DOMAIN ), $msg ) );
				}
//				/* translators: ... */
				throw new Forminator_Addon_Zapier_Wp_Api_Exception( sprintf( __( 'Failed to processing request : %s', Forminator::DOMAIN ), $msg ) );
			}
		}

		$body = wp_remote_retrieve_body( $res );

		// probably silent mode
		if ( ! empty( $body ) ) {
			$res = json_decode( $body );
		}

		$response = $res;
		/**
		 * Filter zapier api response returned to addon
		 *
		 * @since 1.1
		 *
		 * @param mixed          $response    original wp remote request response or decoded body if available
		 * @param string         $body        original content of http response's body
		 * @param array|WP_Error $wp_response original wp remote request response
		 */
		$res = apply_filters( 'forminator_addon_zapier_api_response', $response, $body, $wp_response );

		$this->_last_data_received = $res;

		forminator_addon_maybe_log( $res );

		return $res;
	}


	/**
	 * Send data to static webhook zapier URL
	 *
	 * @since 1.0 Zapier Addon
	 *
	 * @param $args
	 * add `is_test` => true to add `X-Hook-Test: true`
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Zapier_Wp_Api_Exception
	 * @throws Forminator_Addon_Zapier_Wp_Api_Not_Found_Exception
	 */
	public function post_( $args ) {

		return $this->_request(
			'POST',
			'',
			$args
		);
	}

	/**
	 * Get last data sent
	 *
	 * @since 1.0 Zapier Addon
	 *
	 * @return array
	 */
	public function get_last_data_sent() {
		return $this->_last_data_sent;
	}

	/**
	 * Get last data received
	 *
	 * @since 1.0 Zapier Addon
	 *
	 * @return array
	 */
	public function get_last_data_received() {
		return $this->_last_data_received;
	}

	/**
	 * Get last data received
	 *
	 * @since 1.0 Zapier Addon
	 *
	 * @return string
	 */
	public function get_last_url_request() {
		return $this->_last_url_request;
	}
}
