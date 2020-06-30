<?php

require_once dirname( __FILE__ ) . '/class-wp-slack-api-exception.php';
require_once dirname( __FILE__ ) . '/class-wp-slack-api-not-found-exception.php';

/**
 * Class Forminator_Addon_Slack_Wp_Api
 */
class Forminator_Addon_Slack_Wp_Api {

	const AUTHORIZE_URL = 'https://slack.com/oauth/authorize';

	public static $oauth_scopes
		= array(
			'channels:read',
			'channels:write',
			'chat:write:bot',
			'groups:read',
			'groups:write',
			'users:read',
		);

	/**
	 * Instances of slack api
	 *
	 * @var array
	 */
	private static $_instances = array();

	/**
	 * Slack endpoint
	 *
	 * @var string
	 */
	private $_endpoint = 'https://slack.com/api';

	/**
	 * Last data sent to slack
	 *
	 * @since 1.0 Slack Addon
	 * @var array
	 */
	private $_last_data_sent = array();

	/**
	 * Last data received from slack
	 *
	 * @since 1.0 Slack Addon
	 * @var array
	 */
	private $_last_data_received = array();

	/**
	 * Last URL requested
	 *
	 * @since 1.0 Slack Addon
	 * @var string
	 */
	private $_last_url_request = '';

	private $_token = '';

	/**
	 * Forminator_Addon_Slack_Wp_Api constructor.
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param $_token
	 *
	 * @throws Forminator_Addon_Slack_Wp_Api_Exception
	 */
	public function __construct( $_token ) {
		//prerequisites
		if ( ! $_token ) {
			throw new Forminator_Addon_Slack_Wp_Api_Exception( __( 'Missing required Token', Forminator::DOMAIN ) );
		}

		$this->_token = $_token;
	}

	/**
	 * Get singleton
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param $_token
	 *
	 * @return Forminator_Addon_Slack_Wp_Api|null
	 * @throws Forminator_Addon_Slack_Wp_Api_Exception
	 */
	public static function get_instance( $_token ) {
		if ( ! isset( self::$_instances[ md5( $_token ) ] ) ) {
			self::$_instances[ md5( $_token ) ] = new self( $_token );
		}

		return self::$_instances[ md5( $_token ) ];
	}

	/**
	 * Add custom user agent on request
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param $user_agent
	 *
	 * @return string
	 */
	public function filter_user_agent( $user_agent ) {
		$user_agent .= ' ForminatorSlack/' . FORMINATOR_ADDON_SLACK_VERSION;

		/**
		 * Filter user agent to be used by slack api
		 *
		 * @since 1.1
		 *
		 * @param string $user_agent current user agent
		 */
		$user_agent = apply_filters( 'forminator_addon_slack_api_user_agent', $user_agent );

		return $user_agent;
	}

	/**
	 * HTTP Request
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param string $verb
	 * @param        $path
	 * @param array  $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Slack_Wp_Api_Exception
	 * @throws Forminator_Addon_Slack_Wp_Api_Not_Found_Exception
	 */
	private function _request( $verb = 'GET', $path, $args = array() ) {
		// Adding extra user agent for wp remote request
		add_filter( 'http_headers_useragent', array( $this, 'filter_user_agent' ) );

		$url = trailingslashit( $this->_endpoint ) . $path;

		/**
		 * Filter slack url to be used on sending api request
		 *
		 * @since 1.1
		 *
		 * @param string $url  full url with scheme
		 * @param string $verb `GET` `POST` `PUT` `DELETE` `PATCH`
		 * @param string $path requested path resource
		 * @param array  $args argument sent to this function
		 */
		$url = apply_filters( 'forminator_addon_slack_api_url', $url, $verb, $path, $args );

		$this->_last_url_request = $url;

		$headers = array(
			'Authorization' => 'Bearer ' . $this->_token,
		);

		if ( 'GET' !== $verb ) {
			$headers['Content-Type'] = 'application/json; charset=utf-8';
		}

		/**
		 * Filter slack headers to sent on api request
		 *
		 * @since 1.1
		 *
		 * @param array  $headers
		 * @param string $verb `GET` `POST` `PUT` `DELETE` `PATCH`
		 * @param string $path requested path resource
		 * @param array  $args argument sent to this function
		 */
		$headers = apply_filters( 'forminator_addon_slack_api_request_headers', $headers, $verb, $path, $args );

		$_args = array(
			'method'  => $verb,
			'headers' => $headers,
		);

		$request_data = $args;
		/**
		 * Filter slack request data to be used on sending api request
		 *
		 * @since 1.1
		 *
		 * @param array  $request_data it will be `http_build_query`-ed when `GET` or `wp_json_encode`-ed otherwise
		 * @param string $verb         `GET` `POST` `PUT` `DELETE` `PATCH`
		 * @param string $path         requested path resource
		 */
		$args = apply_filters( 'forminator_addon_slack_api_request_data', $request_data, $verb, $path );

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
			throw new Forminator_Addon_Slack_Wp_Api_Exception(
				__( 'Failed to process request, make sure your API URL is correct and your server has internet connection.', Forminator::DOMAIN )
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
					throw new Forminator_Addon_Slack_Wp_Api_Not_Found_Exception( sprintf( __( 'Failed to processing request : %s', Forminator::DOMAIN ), $msg ) );
				}
//				/* translators: ... */
				throw new Forminator_Addon_Slack_Wp_Api_Exception( sprintf( __( 'Failed to processing request : %s', Forminator::DOMAIN ), $msg ) );
			}
		}

		$body = wp_remote_retrieve_body( $res );

		// probably silent mode
		if ( ! empty( $body ) ) {
			$res = json_decode( $body );
			if ( isset( $res->ok ) && false === $res->ok ) {
				$msg = '';
				if ( isset( $res->error ) ) {
					$msg = $res->error;
				}
				throw new Forminator_Addon_Slack_Wp_Api_Exception( sprintf( __( 'Failed to processing request : %s', Forminator::DOMAIN ), $msg ) );
			}
		}

		$response = $res;
		/**
		 * Filter slack api response returned to addon
		 *
		 * @since 1.1
		 *
		 * @param mixed          $response    original wp remote request response or decoded body if available
		 * @param string         $body        original content of http response's body
		 * @param array|WP_Error $wp_response original wp remote request response
		 */
		$res = apply_filters( 'forminator_addon_slack_api_response', $response, $body, $wp_response );

		$this->_last_data_received = $res;

		return $res;
	}

	/**
	 * @param       $code
	 * @param       $redirect_uri
	 * @param array $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Slack_Wp_Api_Exception
	 * @throws Forminator_Addon_Slack_Wp_Api_Not_Found_Exception
	 */
	public function get_access_token( $code, $redirect_uri, $args = array() ) {
		$default_args = array(
			'code'         => $code,
			'redirect_uri' => $redirect_uri,
		);
		$args         = array_merge( $default_args, $args );

		return $this->_request(
			'GET',
			'oauth.access',
			$args
		);
	}

	/**
	 * Get Users / members List
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param array $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Slack_Wp_Api_Exception
	 * @throws Forminator_Addon_Slack_Wp_Api_Not_Found_Exception
	 */
	public function get_users_list( $args = array() ) {
		$default_args = array(
			'limit' => 50,
		);
		$args         = array_merge( $default_args, $args );

		return $this->_request(
			'GET',
			'users.list',
			$args
		);
	}

	/**
	 * Get Public Channels List
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param array $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Slack_Wp_Api_Exception
	 * @throws Forminator_Addon_Slack_Wp_Api_Not_Found_Exception
	 */
	public function get_channels_list( $args = array() ) {
		$default_args = array(
			'exclude_archived' => true,
			'exclude_members'  => true,
			'limit'            => 50,
		);
		$args         = array_merge( $default_args, $args );

		return $this->_request(
			'GET',
			'channels.list',
			$args
		);
	}

	/**
	 * Get Private Channels List
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param array $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Slack_Wp_Api_Exception
	 * @throws Forminator_Addon_Slack_Wp_Api_Not_Found_Exception
	 */
	public function get_groups_list( $args = array() ) {
		$default_args = array(
			'exclude_archived' => true,
			'exclude_members'  => true,
		);
		$args         = array_merge( $default_args, $args );

		return $this->_request(
			'GET',
			'groups.list',
			$args
		);
	}

	/**
	 * Send Message
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param       $channel
	 * @param       $text
	 * @param array $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Slack_Wp_Api_Exception
	 * @throws Forminator_Addon_Slack_Wp_Api_Not_Found_Exception
	 */
	public function chat_post_message( $channel, $text, $args = array() ) {
		$default_args = array(
			'channel' => $channel,
			'text'    => $text,
		);
		$args         = array_merge( $default_args, $args );

		return $this->_request(
			'POST',
			'chat.postMessage',
			$args
		);
	}

	/**
	 * Delete Message
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param       $channel
	 * @param       $chat_ts
	 * @param array $args
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Slack_Wp_Api_Exception
	 * @throws Forminator_Addon_Slack_Wp_Api_Not_Found_Exception
	 */
	public function chat_delete( $channel, $chat_ts, $args = array() ) {
		$default_args = array(
			'channel' => $channel,
			'ts'      => $chat_ts,
		);
		$args         = array_merge( $default_args, $args );

		return $this->_request(
			'POST',
			'chat.delete',
			$args
		);
	}

	/**
	 * Get last data sent
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @return array
	 */
	public function get_last_data_sent() {
		return $this->_last_data_sent;
	}

	/**
	 * Get last data received
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @return array
	 */
	public function get_last_data_received() {
		return $this->_last_data_received;
	}

	/**
	 * Get last data received
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @return string
	 */
	public function get_last_url_request() {
		return $this->_last_url_request;
	}
}
