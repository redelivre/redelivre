<?php

require_once dirname( __FILE__ ) . '/class-aweber-oauth.php';
require_once dirname( __FILE__ ) . '/class-wp-aweber-api-exception.php';
require_once dirname( __FILE__ ) . '/class-wp-aweber-api-not-found-exception.php';

/**
 * Class Forminator_Addon_Aweber_Wp_Api
 */
class Forminator_Addon_Aweber_Wp_Api {

	/**
	 * Instances of aweber api
	 *
	 * @var array
	 */
	private static $_instances = array();

	private static $_access_token_url = 'https://auth.aweber.com/1.0/oauth/access_token';
	private static $_api_base_url     = 'https://api.aweber.com/1.0/';

	const OAUTH_VERSION = '1.0';

	private $_application_key    = '';
	private $_application_secret = '';
	private $_oauth_token        = '';
	private $_oauth_token_secret = '';

	/**
	 * Last data sent to aweber
	 *
	 * @since 1.0 Aweber Addon
	 * @var array
	 */
	private $_last_data_sent = array();

	/**
	 * Last data received from aweber
	 *
	 * @since 1.0 Aweber Addon
	 * @var array
	 */
	private $_last_data_received = array();

	/**
	 * Last URL requested
	 *
	 * @since 1.0 Aweber Addon
	 * @var string
	 */
	private $_last_url_request = '';

	/**
	 * Forminator_Addon_Aweber_Wp_Api constructor.
	 *
	 * @since 1.0 Aweber Addon
	 *
	 * @param $_application_key
	 * @param $_application_secret
	 *
	 * @param $_oauth_token
	 * @param $_oauth_token_secret
	 *
	 * @throws Forminator_Addon_Aweber_Wp_Api_Exception
	 */
	public function __construct( $_application_key, $_application_secret, $_oauth_token, $_oauth_token_secret ) {
		//prerequisites
		if ( ! $_application_key || ! $_application_secret || ! $_oauth_token || ! $_oauth_token_secret ) {
			throw new Forminator_Addon_Aweber_Wp_Api_Exception( __( 'Missing required API Credentials', Forminator::DOMAIN ) );
		}

		$this->_application_key    = $_application_key;
		$this->_application_secret = $_application_secret;
		$this->_oauth_token        = $_oauth_token;
		$this->_oauth_token_secret = $_oauth_token_secret;
	}

	/**
	 * Get singleton
	 *
	 * @since 1.0 Aweber Addon
	 *
	 * @param $_application_key
	 * @param $_application_secret
	 *
	 * @param $_oauth_token
	 * @param $_oauth_token_secret
	 *
	 * @return Forminator_Addon_Aweber_Wp_Api|null
	 * @throws Forminator_Addon_Aweber_Wp_Api_Exception
	 */
	public static function get_instance( $_application_key, $_application_secret, $_oauth_token, $_oauth_token_secret ) {
		$args         = func_get_args();
		$args         = implode( '|', $args );
		$instance_key = md5( $args );
		if ( ! isset( self::$_instances[ $instance_key ] ) ) {
			self::$_instances[ $instance_key ] = new self( $_application_key, $_application_secret, $_oauth_token, $_oauth_token_secret );
		}

		return self::$_instances[ $instance_key ];
	}

	/**
	 * Add custom user agent on request
	 *
	 * @since 1.0 Aweber Addon
	 *
	 * @param $user_agent
	 *
	 * @return string
	 */
	public function filter_user_agent( $user_agent ) {
		$user_agent .= ' ForminatorAweber/' . FORMINATOR_ADDON_AWEBER_VERSION;

		/**
		 * Filter user agent to be used by aweber api
		 *
		 * @since 1.1
		 *
		 * @param string $user_agent current user agent
		 */
		$user_agent = apply_filters( 'forminator_addon_aweber_api_user_agent', $user_agent );

		return $user_agent;
	}

	/**
	 * HTTP Request
	 *
	 * @since 1.0 Aweber Addon
	 *
	 * @param string $verb
	 * @param        $url
	 * @param array  $args
	 *
	 * @param array  $headers
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Aweber_Wp_Api_Exception
	 * @throws Forminator_Addon_Aweber_Wp_Api_Not_Found_Exception
	 */
	private function _request( $verb = 'GET', $url, $args = array(), $headers = array() ) {
		// Adding extra user agent for wp remote request
		add_filter( 'http_headers_useragent', array( $this, 'filter_user_agent' ) );

		/**
		 * Filter aweber url to be used on sending api request
		 *
		 * @since 1.1
		 *
		 * @param string $url  full url with scheme
		 * @param string $verb `GET` `POST` `PUT` `DELETE` `PATCH`
		 * @param string $path requested path resource
		 * @param array  $args argument sent to this function
		 */
		$url = apply_filters( 'forminator_addon_aweber_api_url', $url, $verb, $args );

		/**
		 * Filter aweber headers to sent on api request
		 *
		 * @since 1.1
		 *
		 * @param array  $headers
		 * @param string $verb `GET` `POST` `PUT` `DELETE` `PATCH`
		 * @param string $url  full url with scheme
		 * @param array  $args argument sent to this function
		 */
		$headers = apply_filters( 'forminator_addon_aweber_api_request_headers', $headers, $verb, $url, $args );

		$_args = array(
			'method'  => $verb,
			'headers' => $headers,
		);

		$request_data = $args;
		ksort( $request_data );

		/**
		 * Filter aweber request data to be used on sending api request
		 *
		 * @since 1.1
		 *
		 * @param array  $request_data
		 * @param string $verb `GET` `POST` `PUT` `DELETE` `PATCH`
		 * @param string $url  full url with scheme
		 */
		$args = apply_filters( 'forminator_addon_aweber_api_request_data', $request_data, $verb, $url );

		// $oauth_url_params doesnt need to be included
		$this->_last_url_request = $url;

		if ( 'PATCH' === $verb ) {
			$oauth_url_params = $this->get_prepared_request( $verb, $url, array() );
			$url              .= ( '?' . http_build_query( $oauth_url_params ) );
			$_args['body']    = wp_json_encode( $args );
		} else {
			# WARNING: If not being sent as json, non-primitive items in data must be json serialized in GET and POST.
			foreach ( $args as $key => $value ) {
				if ( is_array( $value ) ) {
					$args[ $key ] = wp_json_encode( $value );
				}
			}
			if ( 'POST' === $verb ) {
				$_args['body'] = $this->get_prepared_request( $verb, $url, $args );
			} else {
				$oauth_url_params = $this->get_prepared_request( $verb, $url, $args );
				$url              .= ( '?' . http_build_query( $oauth_url_params ) );
			}
		}

		$this->_last_data_sent = $args;

		/**
		 * Filter aweber wp_remote_request args
		 *
		 * @since 1.1
		 *
		 * @param array $_args
		 */
		$_args = apply_filters( 'forminator_addon_aweber_api_remote_request_args', $_args );

		$res         = wp_remote_request( $url, $_args );
		$wp_response = $res;

		remove_filter( 'http_headers_useragent', array( $this, 'filter_user_agent' ) );

		if ( is_wp_error( $res ) || ! $res ) {
			throw new Forminator_Addon_Aweber_Wp_Api_Exception(
				__( 'Failed to process request, make sure your API URL is correct and your server has internet connection.', Forminator::DOMAIN )
			);
		}

		if ( isset( $res['response']['code'] ) ) {
			$status_code = $res['response']['code'];
			$msg         = '';
			if ( $status_code >= 400 ) {

				if ( isset( $res['response']['message'] ) ) {
					$msg = $res['response']['message'];
				}

				$body_json = wp_remote_retrieve_body( $res );

				$res_json = json_decode( $body_json );
				if ( ! is_null( $res_json ) && is_object( $res_json ) && isset( $res_json->error ) && isset( $res_json->error->message ) ) {
					$msg = $res_json->error->message;
				}

				if ( 404 === $status_code ) {
					throw new Forminator_Addon_Aweber_Wp_Api_Not_Found_Exception( sprintf( __( 'Failed to processing request : %s', Forminator::DOMAIN ), $msg ) );
				}
//				/* translators: ... */
				throw new Forminator_Addon_Aweber_Wp_Api_Exception( sprintf( __( 'Failed to processing request : %s', Forminator::DOMAIN ), $msg ) );
			}
		}

		$body = wp_remote_retrieve_body( $res );

		// probably silent mode
		if ( ! empty( $body ) ) {
			$res = json_decode( $body );
			// fallback to parse args when fail
			if ( empty( $res ) ) {
				$res = wp_parse_args( $body, array() );

				//json-ify to make same format as json response (which is object not array)
				$res = wp_json_encode( $res );
				$res = json_decode( $res );
			}
		}

		$response = $res;
		/**
		 * Filter aweber api response returned to addon
		 *
		 * @since 1.1
		 *
		 * @param mixed          $response    original wp remote request response or decoded body if available
		 * @param string         $body        original content of http response's body
		 * @param array|WP_Error $wp_response original wp remote request response
		 */
		$res = apply_filters( 'forminator_addon_aweber_api_response', $response, $body, $wp_response );

		$this->_last_data_received = $res;

		return $res;
	}


	/**
	 * Get Oauth Request data of AWeber that need to be send on API Request
	 *
	 * @since 1.0 Aweber Addon
	 * @return array
	 */
	public function get_oauth_request_data() {
		$timestamp          = time();
		$oauth_request_data = array(
			'oauth_token'            => $this->get_oauth_token(),
			'oauth_consumer_key'     => $this->_application_key,
			'oauth_version'          => self::OAUTH_VERSION,
			'oauth_timestamp'        => $timestamp,
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_nonce'            => Forminator_Addon_Aweber_Oauth::generate_oauth_nonce( $timestamp ),
		);

		/**
		 * Filter required Oauth Request data of AWeber that need to be send on API Request
		 *
		 * @since 1.3
		 *
		 * @param array $oauth_request_data default oauth request data
		 * @param int   $timestamp          current timestamp for future reference
		 */
		$oauth_request_data = apply_filters( 'forminator_addon_aweber_oauth_request_data', $oauth_request_data, $timestamp );

		return $oauth_request_data;
	}

	/**
	 * Sign Aweber API request
	 *
	 * @since 1.0 Aweber Addon
	 *
	 * @param $method
	 * @param $url
	 * @param $data
	 *
	 * @return mixed
	 */
	public function get_signed_request( $method, $url, $data ) {
		$application_secret = $this->_application_secret;
		$oauth_token_secret = $this->get_oauth_token_secret();

		$base                    = Forminator_Addon_Aweber_Oauth::create_signature_base( $method, $url, $data );
		$key                     = Forminator_Addon_Aweber_Oauth::create_signature_key( $application_secret, $oauth_token_secret );
		$data['oauth_signature'] = Forminator_Addon_Aweber_Oauth::create_signature( $base, $key );
		$signed_request          = $data;

		/**
		 * Filter signed request
		 *
		 * @since 1.3
		 *
		 * @param array  $signed_request
		 * @param string $method
		 * @param string $url
		 * @param array  $data
		 * @param string $application_secret
		 * @param string $oauth_token_secret
		 */
		$signed_request = apply_filters( 'forminator_addon_aweber_oauth_signed_request', $signed_request, $method, $url, $data, $application_secret, $oauth_token_secret );

		return $signed_request;
	}

	/**
	 * prepare Request
	 *
	 * @since 1.0 Aweber Addon
	 *
	 * @param mixed $method HTTP method
	 * @param mixed $url    URL for the request
	 * @param mixed $data   The data to generate oauth data and be signed
	 *
	 * @return array
	 */
	public function get_prepared_request( $method, $url, $data ) {
		$oauth_data            = $this->get_oauth_request_data();
		$data                  = array_merge( $data, $oauth_data );
		$data                  = $this->get_signed_request( $method, $url, $data );
		$prepared_request_data = $data;

		/**
		 * Filter prepared request data, Oauth data added and signed
		 *
		 * @since 1.3
		 *
		 * @param array  $prepared_request_data
		 * @param string $method
		 * @param string $url
		 * @param array  $data
		 */
		$prepared_request_data = apply_filters( 'forminator_addon_aweber_oauth_prepared_request', $prepared_request_data, $method, $url, $data );


		return $prepared_request_data;
	}

	/**
	 * Get Access Token
	 *
	 * @since 1.0 Aweber Addon
	 *
	 * @param       $oauth_verifier
	 * @param array $args
	 *
	 * @return object contains oauth_token and oauth_token_secret
	 *
	 * @throws Forminator_Addon_Aweber_Wp_Api_Exception
	 * @throws Forminator_Addon_Aweber_Wp_Api_Not_Found_Exception
	 */
	public function get_access_token( $oauth_verifier, $args = array() ) {
		$default_args = array(
			'oauth_verifier' => $oauth_verifier,
		);

		$args = array_merge( $default_args, $args );

		$access_tokens = $this->_request( 'POST', self::$_access_token_url, $args );

		if ( ! is_object( $access_tokens ) ) {
			throw new Forminator_Addon_Aweber_Wp_Api_Exception( __( 'Invalid access token', Forminator::DOMAIN ) );
		}

		if ( ! isset( $access_tokens->oauth_token_secret ) || ! isset( $access_tokens->oauth_token ) ) {
			throw new Forminator_Addon_Aweber_Wp_Api_Exception( __( 'Invalid access token', Forminator::DOMAIN ) );
		}

		return $access_tokens;
	}

	/**
	 * Get related accounts
	 *
	 * @since 1.0 Aweber Addon
	 *
	 * @param array $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Aweber_Wp_Api_Exception
	 * @throws Forminator_Addon_Aweber_Wp_Api_Not_Found_Exception
	 */
	public function get_accounts( $args = array() ) {
		$default_args = array();
		$args         = array_merge( $default_args, $args );

		return $this->_request( 'GET', $this->get_api_url( 'accounts' ), $args );
	}

	/**
	 * Get lists on an account
	 *
	 * @since 1.0 Aweber Addon
	 *
	 * @param       $account_id
	 * @param array $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Aweber_Wp_Api_Exception
	 * @throws Forminator_Addon_Aweber_Wp_Api_Not_Found_Exception
	 */
	public function get_account_lists( $account_id, $args = array() ) {
		$default_args = array();
		$args         = array_merge( $default_args, $args );

		return $this->_request( 'GET', $this->get_api_url( 'accounts/' . rawurlencode( trim( $account_id ) ) . '/lists' ), $args );
	}

	/**
	 * Get Custom Fields on the list
	 *
	 * @since 1.0 Aweber Addon
	 *
	 * @param       $account_id
	 * @param       $list_id
	 * @param array $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Aweber_Wp_Api_Exception
	 * @throws Forminator_Addon_Aweber_Wp_Api_Not_Found_Exception
	 */
	public function get_account_list_custom_fields( $account_id, $list_id, $args = array() ) {
		$default_args = array();
		$args         = array_merge( $default_args, $args );

		return $this->_request(
			'GET',
			$this->get_api_url(
				'accounts/' .
				rawurlencode( trim( $account_id ) ) .
				'/lists/' .
				rawurlencode( trim( $list_id ) ) . '/custom_fields'
			),
			$args
		);
	}

	/**
	 * Add subscriber to account list
	 *
	 * @since 1.0 Aweber Addon
	 *
	 * @param       $account_id
	 * @param       $list_id
	 * @param array $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Aweber_Wp_Api_Exception
	 * @throws Forminator_Addon_Aweber_Wp_Api_Not_Found_Exception
	 */
	public function add_account_list_subscriber( $account_id, $list_id, $args = array() ) {
		$default_args = array(
			'ws.op' => 'create',
			'email' => '',
		);
		$args         = array_merge( $default_args, $args );

		if ( empty( $args['email'] ) ) {
			throw new Forminator_Addon_Aweber_Wp_Api_Exception( __( 'Email is required on add AWeber subscriber.', Forminator::DOMAIN ) );
		}

		return $this->_request(
			'POST',
			$this->get_api_url(
				'accounts/' .
				rawurlencode( trim( $account_id ) ) .
				'/lists/' .
				rawurlencode( trim( $list_id ) ) . '/subscribers'
			),
			$args
		);
	}

	/**
	 * Update subscriber to account list
	 *
	 * @since 1.0 Aweber Addon
	 *
	 * @param       $account_id
	 * @param       $list_id
	 * @param       $subscriber_id
	 * @param array $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Aweber_Wp_Api_Exception
	 * @throws Forminator_Addon_Aweber_Wp_Api_Not_Found_Exception
	 */
	public function update_account_list_subscriber( $account_id, $list_id, $subscriber_id, $args = array() ) {
		$default_args = array(
			'email' => '',
		);
		$args         = array_merge( $default_args, $args );

		if ( empty( $args['email'] ) ) {
			throw new Forminator_Addon_Aweber_Wp_Api_Exception( __( 'Email is required on update AWeber subscriber.', Forminator::DOMAIN ) );
		}

		return $this->_request(
			'PATCH',
			$this->get_api_url(
				'accounts/' .
				rawurlencode( trim( $account_id ) ) .
				'/lists/' .
				rawurlencode( trim( $list_id ) ) .
				'/subscribers/' .
				rawurlencode( trim( $subscriber_id ) )
			),
			$args,
			array(
				'Content-Type' => 'application/json',
			)
		);
	}

	/**
	 * GET subscriber on account list
	 *
	 * @since 1.0 Aweber Addon
	 *
	 * @param       $account_id
	 * @param       $list_id
	 * @param array $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Aweber_Wp_Api_Exception
	 * @throws Forminator_Addon_Aweber_Wp_Api_Not_Found_Exception
	 */
	public function find_account_list_subscriber( $account_id, $list_id, $args = array() ) {
		$default_args = array(
			'ws.op' => 'find',
		);
		$args         = array_merge( $default_args, $args );

		return $this->_request(
			'GET',
			$this->get_api_url(
				'accounts/' .
				rawurlencode( trim( $account_id ) ) .
				'/lists/' .
				rawurlencode( trim( $list_id ) ) . '/subscribers'
			),
			$args
		);
	}

	/**
	 * Get url for get access token
	 *
	 * @since 1.0 Aweber Addon
	 * @return string
	 */
	public function get_access_token_url() {
		$access_token_url = self::$_access_token_url;

		/**
		 * Filter access_token_url
		 *
		 * @since 1.3
		 *
		 * @param string $access_token_url
		 */
		$access_token_url = apply_filters( 'forminator_addon_aweber_oauth_access_token_url', $access_token_url );

		return $access_token_url;
	}

	/**
	 * Get API URL
	 *
	 * @param $path
	 *
	 * @return string
	 */
	public function get_api_url( $path ) {
		$api_base_url = self::$_api_base_url;
		$api_url      = trailingslashit( $api_base_url ) . $path;

		/**
		 * Filter api_url to send request
		 *
		 * @since 1.3
		 *
		 * @param string $api_url
		 * @param string $api_base_url
		 * @param string $path
		 */
		$api_url = apply_filters( 'forminator_addon_aweber_oauth_api_url', $api_url, $api_base_url, $path );

		return $api_url;
	}

	/**
	 * Get Oauth Token
	 *
	 * @since 1.0 Aweber Addon
	 * @return string
	 */
	public function get_oauth_token() {
		return $this->_oauth_token;
	}

	/**
	 * Get Oauth Token Secret
	 *
	 * @since 1.0 Aweber Addon
	 * @return string
	 */
	public function get_oauth_token_secret() {
		return $this->_oauth_token_secret;
	}

	/**
	 * Get last data sent
	 *
	 * @since 1.0 Aweber Addon
	 *
	 * @return array
	 */
	public function get_last_data_sent() {
		return $this->_last_data_sent;
	}

	/**
	 * Get last data received
	 *
	 * @since 1.0 Aweber Addon
	 *
	 * @return array
	 */
	public function get_last_data_received() {
		return $this->_last_data_received;
	}

	/**
	 * Get last data received
	 *
	 * @since 1.0 Aweber Addon
	 *
	 * @return string
	 */
	public function get_last_url_request() {
		return $this->_last_url_request;
	}

}
