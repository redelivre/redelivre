<?php

require_once dirname( __FILE__ ) . '/class-wp-trello-api-exception.php';
require_once dirname( __FILE__ ) . '/class-wp-trello-api-not-found-exception.php';

/**
 * Class Forminator_Addon_Trello_Wp_Api
 */
class Forminator_Addon_Trello_Wp_Api {

	/**
	 * Trello API endpoint
	 *
	 * @var string
	 */
	private $_endpoint = 'https://trello.com/1/';

	/**
	 * Trello APP Key
	 *
	 * @var string
	 */
	private $_app_key = '';

	/**
	 * Trello user Token
	 *
	 * @var string
	 */
	private $_token = '';

	/**
	 * Last data sent to trello
	 *
	 * @since 1.0 Trello Addon
	 * @var array
	 */
	private $_last_data_sent = array();

	/**
	 * Last data received from trello
	 *
	 * @since 1.0 Trello Addon
	 * @var array
	 */
	private $_last_data_received = array();

	/**
	 * Last URL requested
	 *
	 * @since 1.0 Trello Addon
	 * @var string
	 */
	private $_last_url_request = '';

	/**
	 * Forminator_Addon_Trello_Wp_Api constructor.
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param string $app_key
	 * @param string $token
	 *
	 * @throws Forminator_Addon_Trello_Wp_Api_Exception
	 */
	public function __construct( $app_key, $token ) {
		//prerequisites
		if ( ! $app_key ) {
			throw new Forminator_Addon_Trello_Wp_Api_Exception( __( 'Missing required APP Key', Forminator::DOMAIN ) );
		}

		if ( ! $token ) {
			throw new Forminator_Addon_Trello_Wp_Api_Exception( __( 'Missing required Token', Forminator::DOMAIN ) );
		}

		$this->_app_key = $app_key;
		$this->_token   = $token;
	}

	/**
	 * Add custom user agent on request
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param $user_agent
	 *
	 * @return string
	 */
	public function filter_user_agent( $user_agent ) {
		$user_agent .= ' ForminatorTrello/' . FORMINATOR_ADDON_TRELLO_VERSION;

		/**
		 * Filter user agent to be used by trello api
		 *
		 * @since 1.1
		 *
		 * @param string $user_agent current user agent
		 */
		$user_agent = apply_filters( 'forminator_addon_trello_api_user_agent', $user_agent );

		return $user_agent;
	}

	/**
	 * HTTP Request
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param string $verb
	 * @param        $path
	 * @param array  $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Trello_Wp_Api_Exception
	 * @throws Forminator_Addon_Trello_Wp_Api_Not_Found_Exception
	 */
	private function _request( $verb = 'GET', $path, $args = array() ) {
		// Adding extra user agent for wp remote request
		add_filter( 'http_headers_useragent', array( $this, 'filter_user_agent' ) );

		$url = trailingslashit( $this->_endpoint ) . $path;

		/**
		 * Filter trello url to be used on sending api request
		 *
		 * @since 1.1
		 *
		 * @param string $url  full url with scheme
		 * @param string $verb `GET` `POST` `PUT` `DELETE` `PATCH`
		 * @param string $path requested path resource
		 * @param array  $args argument sent to this function
		 */
		$url = apply_filters( 'forminator_addon_trello_api_url', $url, $verb, $path, $args );

		$this->_last_url_request = $url;

		$headers = array();

		/**
		 * Filter trello headers to sent on api request
		 *
		 * @since 1.1
		 *
		 * @param array  $headers
		 * @param string $verb `GET` `POST` `PUT` `DELETE` `PATCH`
		 * @param string $path requested path resource
		 * @param array  $args argument sent to this function
		 */
		$headers = apply_filters( 'forminator_addon_trello_api_request_headers', $headers, $verb, $path, $args );

		$_args = array(
			'method'  => $verb,
			'headers' => $headers,
		);

		$request_data = $args;
		/**
		 * Filter trello request data to be used on sending api request
		 *
		 * @since 1.1
		 *
		 * @param array  $request_data it will be `http_build_query`-ed when `GET` or `wp_json_encode`-ed otherwise
		 * @param string $verb         `GET` `POST` `PUT` `DELETE` `PATCH`
		 * @param string $path         requested path resource
		 */
		$args = apply_filters( 'forminator_addon_trello_api_request_data', $request_data, $verb, $path );

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
			throw new Forminator_Addon_Trello_Wp_Api_Exception(
				__( 'Failed to process request, make sure you authorized Trello and your server has internet connection.', Forminator::DOMAIN )
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
					throw new Forminator_Addon_Trello_Wp_Api_Not_Found_Exception( sprintf( __( 'Failed to processing request : %s', Forminator::DOMAIN ), $msg ) );
				}
//				/* translators: ... */
				throw new Forminator_Addon_Trello_Wp_Api_Exception( sprintf( __( 'Failed to processing request : %s', Forminator::DOMAIN ), $msg ) );
			}
		}

		$body = wp_remote_retrieve_body( $res );

		// probably silent mode
		if ( ! empty( $body ) ) {
			$res = json_decode( $body );
			forminator_addon_maybe_log( __METHOD__, $res );
		}

		$response = $res;
		/**
		 * Filter trello api response returned to addon
		 *
		 * @since 1.1
		 *
		 * @param mixed          $response    original wp remote request response or decoded body if available
		 * @param string         $body        original content of http response's body
		 * @param array|WP_Error $wp_response original wp remote request response
		 */
		$res = apply_filters( 'forminator_addon_trello_api_response', $response, $body, $wp_response );

		$this->_last_data_received = $res;

		forminator_addon_maybe_log( $res );

		return $res;
	}


	/**
	 * Send POST Request
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param string $path
	 * @param array  $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Trello_Wp_Api_Exception
	 * @throws Forminator_Addon_Trello_Wp_Api_Not_Found_Exception
	 */
	public function post_( $path, $args = array() ) {
		$default_args = array(
			'key'   => $this->_app_key,
			'token' => $this->_token,
		);

		$args = array_merge( $default_args, $args );

		return $this->_request(
			'POST',
			$path,
			$args
		);
	}

	/**
	 * Send GET Request
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param string $path
	 * @param array  $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Trello_Wp_Api_Exception
	 * @throws Forminator_Addon_Trello_Wp_Api_Not_Found_Exception
	 */
	public function get_( $path, $args = array() ) {
		$default_args = array(
			'key'   => $this->_app_key,
			'token' => $this->_token,
		);

		$args = array_merge( $default_args, $args );

		return $this->_request(
			'GET',
			$path,
			$args
		);
	}

	/**
	 * Send PUT Request
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param string $path
	 * @param array  $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Trello_Wp_Api_Exception
	 * @throws Forminator_Addon_Trello_Wp_Api_Not_Found_Exception
	 */
	public function put_( $path, $args = array() ) {
		$default_args = array(
			'key'   => $this->_app_key,
			'token' => $this->_token,
		);

		$args = array_merge( $default_args, $args );

		return $this->_request(
			'PUT',
			$path,
			$args
		);
	}

	/**
	 * Send DELETE Request
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param string $path
	 * @param array  $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Trello_Wp_Api_Exception
	 * @throws Forminator_Addon_Trello_Wp_Api_Not_Found_Exception
	 */
	public function delete_( $path, $args = array() ) {
		$default_args = array(
			'key'   => $this->_app_key,
			'token' => $this->_token,
		);

		$args = array_merge( $default_args, $args );

		return $this->_request(
			'DELETE',
			$path,
			$args
		);
	}

	/**
	 * Get Boards
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param array $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Trello_Wp_Api_Exception
	 * @throws Forminator_Addon_Trello_Wp_Api_Not_Found_Exception
	 */
	public function get_boards( $args = array() ) {
		$default_args = array();
		$args         = array_merge( $default_args, $args );

		return $this->get_( 'members/me/boards', $args );
	}

	/**
	 * Get List
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param string $board_id
	 * @param array  $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Trello_Wp_Api_Exception
	 * @throws Forminator_Addon_Trello_Wp_Api_Not_Found_Exception
	 */
	public function get_board_lists( $board_id, $args = array() ) {
		$default_args = array();
		$args         = array_merge( $default_args, $args );

		return $this->get_( 'boards/' . trim( $board_id ) . '/lists', $args );
	}

	/**
	 * Get Members
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param string $board_id
	 * @param array  $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Trello_Wp_Api_Exception
	 * @throws Forminator_Addon_Trello_Wp_Api_Not_Found_Exception
	 */
	public function get_board_members( $board_id, $args = array() ) {
		$default_args = array();
		$args         = array_merge( $default_args, $args );

		return $this->get_( 'boards/' . trim( $board_id ) . '/members', $args );
	}

	/**
	 * Get Members
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param array $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Trello_Wp_Api_Exception
	 * @throws Forminator_Addon_Trello_Wp_Api_Not_Found_Exception
	 */
	public function create_card( $args = array() ) {
		$default_args = array(
			'name' => __( 'Forminator Trello Card', Forminator::DOMAIN ),
			'pos'  => 'bottom',
		);
		$args         = array_merge( $default_args, $args );

		if ( ! isset( $args['idList'] ) ) {
			throw new Forminator_Addon_Trello_Wp_Api_Exception( __( 'idList Required to create a Trello Card', Forminator::DOMAIN ) );
		}

		return $this->post_( 'cards', $args );
	}

	/**
	 * Delete Card (not reversible)
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param       $card_id
	 * @param array $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Trello_Wp_Api_Exception
	 * @throws Forminator_Addon_Trello_Wp_Api_Not_Found_Exception
	 */
	public function delete_card( $card_id, $args = array() ) {
		$default_args = array();
		$args         = array_merge( $default_args, $args );

		return $this->delete_( 'cards/' . trim( $card_id ), $args );
	}

	/**
	 * Close card shortcut
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param       $card_id
	 * @param array $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Trello_Wp_Api_Exception
	 * @throws Forminator_Addon_Trello_Wp_Api_Not_Found_Exception
	 */
	public function close_card( $card_id, $args = array() ) {
		$default_args = array(
			'closed' => true,
		);
		$args         = array_merge( $default_args, $args );

		return $this->update_card( $card_id, $args );
	}

	/**
	 * Update Card
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param       $card_id
	 * @param array $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Trello_Wp_Api_Exception
	 * @throws Forminator_Addon_Trello_Wp_Api_Not_Found_Exception
	 */
	public function update_card( $card_id, $args = array() ) {
		$default_args = array();
		$args         = array_merge( $default_args, $args );

		return $this->put_( 'cards/' . trim( $card_id ), $args );
	}

	/**
	 * Get Labels
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param string $board_id
	 * @param array  $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Trello_Wp_Api_Exception
	 * @throws Forminator_Addon_Trello_Wp_Api_Not_Found_Exception
	 */
	public function get_board_labels( $board_id, $args = array() ) {
		$default_args = array();
		$args         = array_merge( $default_args, $args );

		return $this->get_( 'boards/' . trim( $board_id ) . '/labels', $args );
	}

	/**
	 * Get last data sent
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @return array
	 */
	public function get_last_data_sent() {
		return $this->_last_data_sent;
	}

	/**
	 * Get last data received
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @return array
	 */
	public function get_last_data_received() {
		return $this->_last_data_received;
	}

	/**
	 * Get last data received
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @return string
	 */
	public function get_last_url_request() {
		return $this->_last_url_request;
	}
}
