<?php

require_once dirname( __FILE__ ) . '/class-forminator-addon-activecampaign-wp-api-exception.php';
require_once dirname( __FILE__ ) . '/class-forminator-addon-activecampaign-wp-api-not-found-exception.php';

/**
 * Class Forminator_Addon_Activecampaign_Wp_Api
 */
class Forminator_Addon_Activecampaign_Wp_Api {

	/**
	 * Activecampaign endpoint of api
	 *
	 * @var string
	 */
	private $_endpoint = '';

	/**
	 * Activecampaign API Key api
	 *
	 * @var string
	 */
	private $_api_key = '';

	/**
	 * Last data sent to activecampaign
	 *
	 * @since 1.0 Activecampaign Addon
	 * @var array
	 */
	private $_last_data_sent = array();

	/**
	 * Last data received from activecampaign
	 *
	 * @since 1.0 Activecampaign Addon
	 * @var array
	 */
	private $_last_data_received = array();

	/**
	 * Last URL requested
	 *
	 * @since 1.0 Activecampaign Addon
	 * @var string
	 */
	private $_last_url_request = '';

	/**
	 * Forminator_Addon_Activecampaign_Wp_Api constructor.
	 *
	 * @since 1.0 Activecampaign Addon
	 *
	 * @param string $_endpoint
	 *
	 * @param string $_api_key
	 *
	 * @throws Forminator_Addon_Activecampaign_Wp_Api_Exception
	 */
	public function __construct( $_endpoint, $_api_key ) {
		//prerequisites
		if ( ! $_endpoint ) {
			throw new Forminator_Addon_Activecampaign_Wp_Api_Exception( __( 'Missing required API URL', Forminator::DOMAIN ) );
		}

		if ( ! $_api_key ) {
			throw new Forminator_Addon_Activecampaign_Wp_Api_Exception( __( 'Missing required API Key', Forminator::DOMAIN ) );
		}

		$this->_endpoint = $_endpoint;
		$this->_api_key  = $_api_key;
	}

	/**
	 * Add custom user agent on request
	 *
	 * @since 1.0 Activecampaign Addon
	 *
	 * @param $user_agent
	 *
	 * @return string
	 */
	public function filter_user_agent( $user_agent ) {
		$user_agent .= ' ForminatorActivecampaign/' . FORMINATOR_ADDON_ACTIVECAMPAIGN_VERSION;

		/**
		 * Filter user agent to be used by activecampaign api
		 *
		 * @since 1.1
		 *
		 * @param string $user_agent current user agent
		 */
		$user_agent = apply_filters( 'forminator_addon_activecampaign_api_user_agent', $user_agent );

		return $user_agent;
	}

	/**
	 * HTTP Request
	 *
	 * @since 1.0 Activecampaign Addon
	 *
	 * @param string $verb
	 * @param        $path
	 * @param array  $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Activecampaign_Wp_Api_Exception
	 * @throws Forminator_Addon_Activecampaign_Wp_Api_Not_Found_Exception
	 */
	private function request( $verb = 'GET', $path, $args = array() ) {
		// Adding extra user agent for wp remote request
		add_filter( 'http_headers_useragent', array( $this, 'filter_user_agent' ) );

		$url = trailingslashit( $this->_endpoint ) . $path;

		/**
		 * Filter activecampaign url to be used on sending api request
		 *
		 * @since 1.1
		 *
		 * @param string $url  full url with scheme
		 * @param string $verb `GET` `POST` `PUT` `DELETE` `PATCH`
		 * @param string $path requested path resource
		 * @param array  $args argument sent to this function
		 */
		$url = apply_filters( 'forminator_addon_activecampaign_api_url', $url, $verb, $path, $args );

		$this->_last_url_request = $url;

		$headers = array(
			'Content-Type' => 'application/x-www-form-urlencoded',
		);

		/**
		 * Filter activecampaign headers to sent on api request
		 *
		 * @since 1.1
		 *
		 * @param array  $headers
		 * @param string $verb `GET` `POST` `PUT` `DELETE` `PATCH`
		 * @param string $path requested path resource
		 * @param array  $args argument sent to this function
		 */
		$headers = apply_filters( 'forminator_addon_activecampaign_api_request_headers', $headers, $verb, $path, $args );

		$_args = array(
			'method'  => $verb,
			'headers' => $headers,
		);

		$request_data = $args;
		/**
		 * Filter activecampaign request data to be used on sending api request
		 *
		 * @since 1.1
		 *
		 * @param array  $request_data it will be `http_build_query`-ed when `GET` or `wp_json_encode`-ed otherwise
		 * @param string $verb         `GET` `POST` `PUT` `DELETE` `PATCH`
		 * @param string $path         requested path resource
		 */
		$args = apply_filters( 'forminator_addon_activecampaign_api_request_data', $request_data, $verb, $path );

		if ( 'GET' === $verb ) {
			$url .= ( '?' . http_build_query( $args ) );
		} else {
			$_args['body'] = $args;
		}

		$this->_last_data_sent = $args;

		$res         = wp_remote_request( $url, $_args );
		$wp_response = $res;

		remove_filter( 'http_headers_useragent', array( $this, 'filter_user_agent' ) );

		if ( is_wp_error( $res ) || ! $res ) {
			forminator_addon_maybe_log( __METHOD__, $res );
			throw new Forminator_Addon_Activecampaign_Wp_Api_Exception(
				__( 'Failed to process request, make sure your API URL and API KEY are correct and your server has internet connection.', Forminator::DOMAIN )
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
					/* translators: ... */
					throw new Forminator_Addon_Activecampaign_Wp_Api_Not_Found_Exception( sprintf( __( 'Failed to processing request : %s', Forminator::DOMAIN ), $msg ) );
				}
				/* translators: ... */
				throw new Forminator_Addon_Activecampaign_Wp_Api_Exception( sprintf( __( 'Failed to processing request : %s', Forminator::DOMAIN ), $msg ) );
			}
		}

		$body = wp_remote_retrieve_body( $res );

		// probably silent mode
		if ( ! empty( $body ) ) {
			$res = json_decode( $body );

			// auto validate
			if ( ! empty( $res ) ) {
				if ( ! isset( $res->result_code ) || 1 !== $res->result_code ) {
					$message = '';
					if ( isset( $res->result_message ) && ! empty( $res->result_message ) ) {
						$message = ' ' . $res->result_message;
					}
					/* translators: ... */
					throw new Forminator_Addon_Activecampaign_Wp_Api_Exception( sprintf( __( 'Failed to get ActiveCampaign data.%1$s', Forminator::DOMAIN ), $message ) );
				}
			}
		}

		$response = $res;
		/**
		 * Filter activecampaign api response returned to addon
		 *
		 * @since 1.1
		 *
		 * @param mixed          $response    original wp remote request response or decoded body if available
		 * @param string         $body        original content of http response's body
		 * @param array|WP_Error $wp_response original wp remote request response
		 */
		$res = apply_filters( 'forminator_addon_activecampaign_api_response', $response, $body, $wp_response );

		$this->_last_data_received = $res;

		forminator_addon_maybe_log( $res );

		return $res;
	}


	/**
	 * Send data to activecampaign API URL
	 *
	 * @since 1.0 Activecampaign Addon
	 *
	 * @param $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Activecampaign_Wp_Api_Exception
	 * @throws Forminator_Addon_Activecampaign_Wp_Api_Not_Found_Exception
	 */
	public function post_( $args ) {

		return $this->request(
			'POST',
			'',
			$args
		);
	}

	/**
	 * Get Account Detail
	 *
	 * @since 1.0 Activecampaign Addon
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Activecampaign_Wp_Api_Exception
	 * @throws Forminator_Addon_Activecampaign_Wp_Api_Not_Found_Exception
	 */
	public function get_account() {

		return $this->request(
			'GET',
			'/admin/api.php',
			array(
				'api_action' => 'account_view',
				'api_key'    => $this->_api_key,
				'api_output' => 'json',
			)
		);
	}

	/**
	 * Get Lists
	 *
	 * @since 1.0 Activecampaign Addon
	 *
	 * @param array $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Activecampaign_Wp_Api_Exception
	 * @throws Forminator_Addon_Activecampaign_Wp_Api_Not_Found_Exception
	 */
	public function get_lists( $args = array() ) {

		$default_args = array(
			'api_action'    => 'list_list',
			'api_key'       => $this->_api_key,
			'api_output'    => 'json',
			'ids'           => 'all',
			'global_fields' => 1,
			'full'          => 1,
		);

		$args = array_merge( $default_args, $args );

		$request_data = $this->request(
			'GET',
			'/admin/api.php',
			$args
		);

		return self::get_collection_from_request_result( $request_data );
	}

	/**
	 * Get List Detail
	 *
	 * @param       $id
	 * @param array $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Activecampaign_Wp_Api_Exception
	 * @throws Forminator_Addon_Activecampaign_Wp_Api_Not_Found_Exception
	 */
	public function get_list( $id, $args = array() ) {
		$default_args = array(
			'api_action' => 'list_view',
			'api_key'    => $this->_api_key,
			'api_output' => 'json',
			'id'         => $id,
		);

		$args = array_merge( $default_args, $args );

		return $this->request(
			'GET',
			'/admin/api.php',
			$args
		);
	}

	/**
	 * Get created Forms
	 *
	 * @param array $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Activecampaign_Wp_Api_Exception
	 * @throws Forminator_Addon_Activecampaign_Wp_Api_Not_Found_Exception
	 */
	public function get_forms( $args = array() ) {
		$default_args = array(
			'api_action' => 'form_getforms',
			'api_key'    => $this->_api_key,
			'api_output' => 'json',
		);

		$args = array_merge( $default_args, $args );

		$request_data = $this->request(
			'GET',
			'/admin/api.php',
			$args
		);

		return self::get_collection_from_request_result( $request_data );
	}

	/**
	 * Sync Contact
	 *
	 * Add or edit a contact based on their email address.
	 * Instead of calling contact_view to check if the contact exists, and then calling contact_add or
	 * contact_edit, you can make just one call and include only the information you want added or updated.
	 *
	 * @param array $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Activecampaign_Wp_Api_Exception
	 * @throws Forminator_Addon_Activecampaign_Wp_Api_Not_Found_Exception
	 */
	public function contact_sync( $args = array() ) {
		$query_args = array(
			'api_action' => 'contact_sync',
			'api_key'    => $this->_api_key,
			'api_output' => 'json',
		);

		$default_args = array(
			'email' => '',
		);

		$args = array_merge( $default_args, $args );

		if ( empty( $args['email'] ) ) {
			throw new Forminator_Addon_Activecampaign_Wp_Api_Exception( __( 'Required email parameter not set', Forminator::DOMAIN ) );
		}

		return $this->request(
			'POST',
			'/admin/api.php' . ( '?' . http_build_query( $query_args ) ),
			$args
		);

	}

	/**
	 * Delete Contact
	 *
	 * Allows you to delete an existing contact from the ActiveCampaign system.
	 *
	 * @param array $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Activecampaign_Wp_Api_Exception
	 * @throws Forminator_Addon_Activecampaign_Wp_Api_Not_Found_Exception
	 */
	public function contact_delete( $args = array() ) {
		$default_args = array(
			'api_action' => 'contact_delete',
			'api_key'    => $this->_api_key,
			'api_output' => 'json',
			'id'         => '',
		);

		$args = array_merge( $default_args, $args );

		if ( empty( $args['id'] ) ) {
			throw new Forminator_Addon_Activecampaign_Wp_Api_Exception( __( 'Required id parameter not set for contact_delete.', Forminator::DOMAIN ) );
		}

		return $this->request(
			'GET',
			'/admin/api.php',
			$args
		);

	}

	/**
	 * Get last data sent
	 *
	 * @since 1.0 Activecampaign Addon
	 *
	 * @return array
	 */
	public function get_last_data_sent() {
		return $this->_last_data_sent;
	}

	/**
	 * Get last data received
	 *
	 * @since 1.0 Activecampaign Addon
	 *
	 * @return array
	 */
	public function get_last_data_received() {
		return $this->_last_data_received;
	}

	/**
	 * Get last data received
	 *
	 * @since 1.0 Activecampaign Addon
	 *
	 * @return string
	 */
	public function get_last_url_request() {
		return $this->_last_url_request;
	}

	/**
	 * Get data collection form request result
	 *
	 * @param $request_data
	 *
	 * @return array
	 */
	public static function get_collection_from_request_result( $request_data ) {
		$collection   = array();
		$request_data = (array) $request_data;
		foreach ( $request_data as $key => $data ) {
			/**
			 * result_code    1
			 * result_message    Success: Something is returned
			 * result_output    json
			 */
			if ( stripos( $key, 'result_' ) !== false ) {
				continue;
			}

			$collection[ $key ] = $data;
		}

		return $collection;
	}
}
