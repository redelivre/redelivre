<?php

require_once dirname( __FILE__ ) . '/class-wp-mailchimp-api-exception.php';
require_once dirname( __FILE__ ) . '/class-wp-mailchimp-api-not-found-exception.php';

/**
 * Class Forminator_Addon_Mailchimp_Wp_Api
 * Wrapper @see wp_remote_request() to be used to do request to mailchimp server
 *
 * @since 1.0 Mailchimp Addon
 */
class Forminator_Addon_Mailchimp_Wp_Api {

	/**
	 * Mailchimp API instance
	 *
	 * @since 1.0 Mailchimp Addon
	 * @var self|null
	 */
	private static $_instance = null;

	/**
	 * Endpoint of Mailchimp API
	 *
	 * @since 1.0 Mailchimp Addon
	 * @var string
	 */
	private $_endpoint = 'https://{dc}.api.mailchimp.com/3.0/';

	/**
	 * API Key used to send request
	 *
	 * @since 1.0 Mailchimp Addon
	 * @var string
	 */
	private $_api_key = '';

	/**
	 * Last data sent to mailchimp API
	 *
	 * @since 1.0 Mailchimp Addon
	 * @var array
	 */
	private $_last_data_sent = array();

	/**
	 * Last data received from mailchimp API
	 *
	 * @since 1.0 Mailchimp Addon
	 * @var array
	 */
	private $_last_data_received = array();

	/**
	 * Last URL requested
	 *
	 * @since 1.0 Mailchimp Addon
	 * @var string
	 */
	private $_last_url_request = '';

	/**
	 * Forminator_Addon_Mailchimp_Wp_Api constructor.
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param $api_key
	 *
	 * @throws Forminator_Addon_Mailchimp_Wp_Api_Exception
	 */
	public function __construct( $api_key ) {
		//prerequisite
		if ( ! $api_key ) {
			throw new Forminator_Addon_Mailchimp_Wp_Api_Exception( __( 'Missing required API Key', Forminator::DOMAIN ) );
		}

		$this->_api_key = $api_key;

		$exploded    = explode( '-', $this->_api_key );
		$data_center = end( $exploded );

		// endpoint data center are taken from api key
		$this->_endpoint = str_replace( '{dc}', $data_center, $this->_endpoint );
	}

	/**
	 * Get singleton
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param null $api_key
	 *
	 * @return Forminator_Addon_Mailchimp_Wp_Api|null
	 * @throws Forminator_Addon_Mailchimp_Wp_Api_Exception
	 */
	public static function get_instance( $api_key = null ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $api_key );
		}

		return self::$_instance;
	}

	/**
	 * Add extra info on user agent header used to send request
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param $user_agent
	 *
	 * @return string
	 */
	public function filter_user_agent( $user_agent ) {

		$user_agent .= ' ForminatorMailChimp/' . FORMINATOR_ADDON_MAILCHIMP_VERSION;

		/**
		 * Filter user agent to be used by mailchimp api
		 *
		 * @since 1.1
		 *
		 * @param string $user_agent current user agent
		 */
		$user_agent = apply_filters( 'forminator_addon_mailchimp_api_user_agent', $user_agent );

		return $user_agent;
	}

	/**
	 * HTTP Request
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param string $verb
	 * @param        $path
	 * @param array  $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Mailchimp_Wp_Api_Exception
	 * @throws Forminator_Addon_Mailchimp_Wp_Api_Not_Found_Exception
	 */
	private function _request( $verb = 'GET', $path, $args = array() ) {
		// Adding extra user agent for wp remote request
		add_filter( 'http_headers_useragent', array( $this, 'filter_user_agent' ) );
		$url = trailingslashit( $this->_endpoint ) . $path;


		/**
		 * Filter mailchimp url to be used on sending api request
		 *
		 * @since 1.1
		 *
		 * @param string $url  full url with scheme
		 * @param string $verb `GET` `POST` `PUT` `DELETE` `PATCH`
		 * @param string $path requested path resource
		 * @param array  $args argument sent to this function
		 */
		$url = apply_filters( 'forminator_addon_mailchimp_api_url', $url, $verb, $path, $args );

		$this->_last_url_request = $url;

		$headers = array(
			'Authorization' => 'ForminatorMailChimp ' . $this->_api_key,
		);

		/**
		 * Filter mailchimp headers to sent on api request
		 *
		 * @since 1.1
		 *
		 * @param array  $headers
		 * @param string $verb `GET` `POST` `PUT` `DELETE` `PATCH`
		 * @param string $path requested path resource
		 * @param array  $args argument sent to this function
		 */
		$headers = apply_filters( 'forminator_addon_mailchimp_api_request_headers', $headers, $verb, $path, $args );

		$_args = array(
			'method'  => $verb,
			'headers' => $headers,
		);

		$request_data = $args;
		/**
		 * Filter mailchimp request data to be used on sending api request
		 *
		 * @since 1.1
		 *
		 * @param array  $request_data it will be `http_build_query`-ed when `GET` or `wp_json_encode`-ed otherwise
		 * @param string $verb         `GET` `POST` `PUT` `DELETE` `PATCH`
		 * @param string $path         requested path resource
		 */
		$args = apply_filters( 'forminator_addon_mailchimp_api_request_data', $request_data, $verb, $path );

		if ( 'GET' === $verb ) {
			$url .= ( '?' . http_build_query( $args ) );
		} else {
			$_args['body'] = wp_json_encode( $args );
		}

		$this->_last_data_sent = $args;

		$res = wp_remote_request( $url, $_args );

		remove_filter( 'http_headers_useragent', array( $this, 'filter_user_agent' ) );

		if ( is_wp_error( $res ) || ! $res ) {
			forminator_addon_maybe_log( __METHOD__, $res );
			throw new Forminator_Addon_Mailchimp_Wp_Api_Exception( __( 'Failed to process request, make sure API KEY is correct and your server has internet connection.', Forminator::DOMAIN ) );
		}

		$body = wp_remote_retrieve_body( $res );

		$response = null;

		// DELETE probably won't receiving contents on success
		if ( 'DELETE' !== $verb ) {
			// Got no response from API
			if ( empty( $body ) ) {
				forminator_addon_maybe_log( __METHOD__, $res );
				throw new Forminator_Addon_Mailchimp_Wp_Api_Exception( __( 'Failed to process request, make sure API KEY is correct and your server has internet connection.',
				Forminator::DOMAIN ) );
			}
		}

		if ( ! empty( $body ) ) {
			$response = json_decode( $body );

			// check response status from API
			if ( isset( $response->status ) ) {
				if ( $response->status >= 400 ) {
					forminator_addon_maybe_log( __METHOD__, $response );
					$msg = '';
					if ( isset( $response->detail ) ) {
						// if exist, error detail is given by mailchimp here
						$msg = $response->detail;
					}
					$this->_last_data_received = $response;
					if ( 404 === $response->status ) {
						throw new Forminator_Addon_Mailchimp_Wp_Api_Not_Found_Exception( sprintf( __( 'Failed to processing request : %s', Forminator::DOMAIN ), $msg ) );
					}
					/* translators: ... */
					throw new Forminator_Addon_Mailchimp_Wp_Api_Exception( sprintf( __( 'Failed to processing request : %s', Forminator::DOMAIN ), $msg ) );
				}
			}

			// Probably response is failed to be json decoded
			if ( is_null( $response ) ) {
				$this->_last_data_received = $body;
				forminator_addon_maybe_log( __METHOD__, $res );
				/* translators: ... */
				throw new Forminator_Addon_Mailchimp_Wp_Api_Exception( sprintf( __( 'Failed to processing request : %s', Forminator::DOMAIN ), json_last_error_msg() ) );
			}
		}

		$wp_response = $res;

		// in case not receving json decoded body use $wp_response
		if ( is_null( $response ) ) {
			$response = $wp_response;
		}
		/**
		 * Filter mailchimp api response returned to addon
		 *
		 * @since 1.1
		 *
		 * @param mixed          $response
		 * @param string         $body        original content of http response's body
		 * @param array|WP_Error $wp_response original wp remote request response
		 */
		$response = apply_filters( 'forminator_addon_mailchimp_api_response', $response, $body, $wp_response );

		$this->_last_data_received = $response;

		return $response;
	}

	/**
	 * Get User Info for the current API KEY
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param $fields
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Mailchimp_Wp_Api_Exception
	 */
	public function get_info( $fields = array() ) {
		if ( empty( $fields ) ) {
			$fields = array( 'account_id', 'account_name', 'email' );
		}

		return $this->_request(
			'GET',
			'',
			array(
				'fields' => implode( ',', $fields ),
			)
		);
	}

	/**
	 * Get Mailchimp Lists
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Mailchimp_Wp_Api_Exception
	 */
	public function get_lists( $args ) {
		$default_args = array(
			'fields'     => implode( ',', array( 'lists.id', 'lists.name' ) ),
			'count'      => 10,
			'sort_field' => 'date_created',
			'sort_dir'   => 'DESC',
		);

		$args = array_merge( $default_args, $args );

		return $this->_request(
			'GET',
			'lists',
			$args
		);
	}

	/**
	 * Get List of merge fields
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param $list_id
	 * @param $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Mailchimp_Wp_Api_Exception
	 */
	public function get_list_merge_fields( $list_id, $args ) {
		$default_args = array(
			'fields'     => implode( ',', array( 'merge_fields.merge_id', 'merge_fields.tag', 'merge_fields.name', 'merge_fields.type', 'merge_fields.required' ) ),
			'count'      => 10,
			'sort_field' => 'display_order',
			'sort_dir'   => 'ASC',
		);

		$args = array_merge( $default_args, $args );

		return $this->_request(
			'GET',
			'lists/' . $list_id . '/merge-fields/',
			$args
		);
	}

	/**
	 * Add new Merge Field To List
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param $list_id
	 * @param $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Mailchimp_Wp_Api_Exception
	 */
	public function add_merge_field_to_list( $list_id, $args ) {
		$available_types = array(
			'text',
			'number',
			'address',
			'phone',
			'date',
			'url',
			'image',
			'url',
			'radio',
			'dropdown',
			'birthday',
			'zip',
		);

		$default_args = array(
			'type' => 'text',
		);

		$args = array_merge( $default_args, $args );
		if ( ! in_array( $args['type'], $available_types, true ) ) {
			throw new Forminator_Addon_Mailchimp_Wp_Api_Exception( __( 'Invalid Field Type', Forminator::DOMAIN ) );
		}

		return $this->_request(
			'POST',
			'lists/' . $list_id . '/merge-fields/',
			$args
		);
	}

	/**
	 * Get Created categories withing a list
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param $list_id
	 * @param $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Mailchimp_Wp_Api_Exception
	 */
	public function get_list_categories( $list_id, $args ) {
		$default_args = array(
			'fields'     => implode( ',', array( 'categories.id', 'categories.title', 'categories.type' ) ),
			'count'      => 10,
			'sort_field' => 'date_created',
			'sort_dir'   => 'DESC',
		);

		$args = array_merge( $default_args, $args );

		return $this->_request(
			'GET',
			'lists/' . $list_id . '/interest-categories',
			$args
		);
	}

	/**
	 * Get Created Interest Groups within a category
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param $list_id
	 * @param $category_id
	 * @param $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Mailchimp_Wp_Api_Exception
	 */
	public function get_category_interests( $list_id, $category_id, $args ) {
		$default_args = array(
			'fields'     => implode( ',', array( 'interests.id', 'interests.name' ) ),
			'count'      => 10,
			'sort_field' => 'display_order',
			'sort_dir'   => 'ASC',
		);

		$args = array_merge( $default_args, $args );

		return $this->_request(
			'GET',
			'lists/' . $list_id . '/interest-categories/' . $category_id . '/interests',
			$args
		);
	}

	/**
	 * Get detail of member
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param        $list_id
	 * @param string $subscriber_hash The MD5 hash of the lowercase version of the list member’s email address.
	 * @param        $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Mailchimp_Wp_Api_Exception
	 * @throws Forminator_Addon_Mailchimp_Wp_Api_Not_Found_Exception
	 */
	public function get_member( $list_id, $subscriber_hash, $args ) {
		$default_args = array(
			'fields' => implode( ',', array( 'id', 'email_address', 'status' ) ),
		);

		$args = array_merge( $default_args, $args );


		return $this->_request(
			'GET',
			'lists/' . $list_id . '/members/' . $subscriber_hash,
			$args
		);
	}

	/**
	 * Add Member to list
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param $list_id
	 * @param $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Mailchimp_Wp_Api_Exception
	 */
	public function add_member_to_list( $list_id, $args ) {
		$default_args = array(
			'status'       => 'pending',
			'merge_fields' => array(),
			'interests'    => array(),
		);

		$args = array_merge( $default_args, $args );

		if ( ! isset( $args['email_address'] ) ) {
			throw new Forminator_Addon_Mailchimp_Wp_Api_Exception( __( 'email_address are required for mailchimp', Forminator::DOMAIN ) );
		}

		return $this->_request(
			'POST',
			'lists/' . $list_id . '/members/',
			$args
		);
	}

	/**
	 * Add member if not available, or update member if exist
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param $list_id
	 * @param $subscriber_hash
	 * @param $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Mailchimp_Wp_Api_Exception
	 */
	public function add_or_update_member( $list_id, $subscriber_hash, $args ) {
		$default_args = array(
			'status_if_new' => 'pending',
			'status'        => 'pending',
			'merge_fields'  => array(),
			'interests'     => array(),
		);

		$args = array_merge( $default_args, $args );
		if ( ! isset( $args['email_address'] ) ) {
			throw new Forminator_Addon_Mailchimp_Wp_Api_Exception( __( 'email_address are required for adding member to mailchimp list', Forminator::DOMAIN ) );
		}

		if ( empty( $args['merge_fields'] ) ) {
			unset( $args['merge_fields'] );
		} else {
			$args['merge_fields'] = (object) $args['merge_fields'];
		}

		if ( empty( $args['interests'] ) ) {
			unset( $args['interests'] );
		} else {
			$args['interests'] = (object) $args['interests'];
		}

		if ( empty( $args['interests'] ) ) {
			unset( $args['interests'] );
		}

		return $this->_request(
			'PUT',
			'lists/' . $list_id . '/members/' . $subscriber_hash,
			$args
		);
	}

	/**
	 * Get detail of member
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param        $list_id
	 * @param string $subscriber_hash The MD5 hash of the lowercase version of the list member’s email address.
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Mailchimp_Wp_Api_Exception
	 * @throws Forminator_Addon_Mailchimp_Wp_Api_Not_Found_Exception
	 */
	public function delete_member( $list_id, $subscriber_hash ) {
		return $this->_request(
			'DELETE',
			'lists/' . $list_id . '/members/' . $subscriber_hash,
			array()
		);
	}

	/**
	 * Send `DELETE` request to URL
	 *
	 * Useful to interact with mailchimp schema _links.
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param string $url href from _links response which will be converted to path.
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Mailchimp_Wp_Api_Exception
	 * @throws Forminator_Addon_Mailchimp_Wp_Api_Not_Found_Exception
	 */
	public function delete_( $url ) {
		$path = str_ireplace( $this->_endpoint, '', $url );

		return $this->_request( 'DELETE', $path );
	}

	/**
	 * Get last data sent
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @return array
	 */
	public function get_last_data_sent() {
		return $this->_last_data_sent;
	}

	/**
	 * Get last data received
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @return array
	 */
	public function get_last_data_received() {
		return $this->_last_data_received;
	}

	/**
	 * Get last data received
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @return string
	 */
	public function get_last_url_request() {
		return $this->_last_url_request;
	}

	/**
	 * Get current endpoint to send to Malchimp
	 *
	 * @since 1.0 Mailchimp Addon
	 * @return string
	 */
	public function get_endpoint() {
		return $this->_endpoint;
	}

}
