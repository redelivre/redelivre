<?php

require_once dirname( __FILE__ ) . '/class-forminator-addon-slack-exception.php';
require_once dirname( __FILE__ ) . '/lib/class-forminator-addon-slack-wp-api.php';

/**
 * Class Forminator_Addon_Slack
 * Slack Addon Main Class
 *
 * @since 1.0 Slack Addon
 */
final class Forminator_Addon_Slack extends Forminator_Addon_Abstract {

	/**
	 * @var self|null
	 */
	private static $_instance = null;

	protected $_slug                   = 'slack';
	protected $_version                = FORMINATOR_ADDON_SLACK_VERSION;
	protected $_min_forminator_version = '1.1';
	protected $_short_title            = 'Slack';
	protected $_title                  = 'Slack';
	protected $_url                    = 'https://premium.wpmudev.org';
	protected $_full_path              = __FILE__;

	protected $_form_settings = 'Forminator_Addon_Slack_Form_Settings';
	protected $_form_hooks    = 'Forminator_Addon_Slack_Form_Hooks';

	private $_token = '';

	private $_auth_error_message = '';

	const TARGET_TYPE_PUBLIC_CHANNEL  = 'public_channel';
	const TARGET_TYPE_PRIVATE_CHANNEL = 'private_channel';
	const TARGET_TYPE_DIRECT_MESSAGE  = 'direct_message';

	/**
	 * @var null|Forminator_Addon_Slack_Wp_Api
	 */
	private static $_api = null;

	protected $_poll_settings = 'Forminator_Addon_Slack_Poll_Settings';
	protected $_poll_hooks    = 'Forminator_Addon_Slack_Poll_Hooks';

	protected $_quiz_settings = 'Forminator_Addon_Slack_Quiz_Settings';
	protected $_quiz_hooks    = 'Forminator_Addon_Slack_Quiz_Hooks';

	protected $_position = 4;

	/**
	 * Forminator_Addon_Slack constructor.
	 *
	 * @since 1.0 Slack Addon
	 */
	public function __construct() {
		// late init to allow translation
		$this->_description                = __( 'Get awesome by your form.', Forminator::DOMAIN );
		$this->_activation_error_message   = __( 'Sorry but we failed to activate Slack Integration, don\'t hesitate to contact us', Forminator::DOMAIN );
		$this->_deactivation_error_message = __( 'Sorry but we failed to deactivate Slack Integration, please try again', Forminator::DOMAIN );

		$this->_update_settings_error_message = __(
			'Sorry, we failed to update settings, please check your form and try again',
			Forminator::DOMAIN
		);

		$this->_icon     = forminator_addon_slack_assets_url() . 'icons/slack.png';
		$this->_icon_x2  = forminator_addon_slack_assets_url() . 'icons/slack@2x.png';
		$this->_image    = forminator_addon_slack_assets_url() . 'img/slack.png';
		$this->_image_x2 = forminator_addon_slack_assets_url() . 'img/slack@2x.png';

		add_filter( 'forminator_addon_slack_api_request_headers', array( $this, 'default_filter_api_headers' ), 1, 4 );
	}

	/**
	 * Get Instance
	 *
	 * @since 1.0 Slack Addon
	 * @return self|null
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Override on is_connected
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @return bool
	 */
	public function is_connected() {
		try {
			// check if its active
			if ( ! $this->is_active() ) {
				throw new Forminator_Addon_Slack_Exception( __( 'Slack is not active', Forminator::DOMAIN ) );
			}

			// if user completed api setup
			$is_connected = false;

			$setting_values = $this->get_settings_values();
			// if user completed api setup
			if ( isset( $setting_values['token'] ) && ! empty( $setting_values['token'] ) ) {
				$is_connected = true;
			}
		} catch ( Forminator_Addon_Slack_Exception $e ) {
			$is_connected = false;
		}

		/**
		 * Filter connected status of Slack
		 *
		 * @since 1.0
		 *
		 * @param bool $is_connected
		 */
		$is_connected = apply_filters( 'forminator_addon_slack_is_connected', $is_connected );

		return $is_connected;
	}

	/**
	 * Check if Slack is connected with current form
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param $form_id
	 *
	 * @return bool
	 */
	public function is_form_connected( $form_id ) {
		try {
			$form_settings_instance = null;
			if ( ! $this->is_connected() ) {
				throw new Forminator_Addon_Slack_Exception( __( ' Slack is not connected', Forminator::DOMAIN ) );
			}

			$form_settings_instance = $this->get_addon_form_settings( $form_id );
			if ( ! $form_settings_instance instanceof Forminator_Addon_Slack_Form_Settings ) {
				throw new Forminator_Addon_Slack_Exception( __( 'Invalid Form Settings of Slack', Forminator::DOMAIN ) );
			}

			// Mark as active when there is at least one active connection
			if ( false === $form_settings_instance->find_one_active_connection() ) {
				throw new Forminator_Addon_Slack_Exception( __( 'No active Slack connection found in this form', Forminator::DOMAIN ) );
			}

			$is_form_connected = true;

		} catch ( Forminator_Addon_Slack_Exception $e ) {
			$is_form_connected = false;
		}

		/**
		 * Filter connected status of Slack with the form
		 *
		 * @since 1.0
		 *
		 * @param bool                                      $is_form_connected
		 * @param int                                       $form_id                Current Form ID
		 * @param Forminator_Addon_Slack_Form_Settings|null $form_settings_instance Instance of form settings, or null when unavailable
		 *
		 */
		$is_form_connected = apply_filters( 'forminator_addon_slack_is_form_connected', $is_form_connected, $form_id, $form_settings_instance );

		return $is_form_connected;
	}

	/**
	 * Override settings available,
	 *
	 * @since 1.0 Slack Addon
	 * @return bool
	 */
	public function is_settings_available() {
		return true;
	}

	/**
	 * Flag show full log on entries
	 *
	 * @since 1.0 Slack Addon
	 * @return bool
	 */
	public static function is_show_full_log() {
		$show_full_log = false;
		if ( defined( 'FORMINATOR_ADDON_SLACK_SHOW_FULL_LOG' ) && FORMINATOR_ADDON_SLACK_SHOW_FULL_LOG ) {
			$show_full_log = true;
		}

		/**
		 * Filter Flag show full log on entries
		 *
		 * @since  1.2
		 *
		 * @params bool $show_full_log
		 */
		$show_full_log = apply_filters( 'forminator_addon_slack_show_full_log', $show_full_log );

		return $show_full_log;
	}

	/**
	 * Flag to enable delete chat
	 *
	 * @since 1.0 Slack Addon
	 * @return bool
	 */
	public static function enable_delete_chat() {
		$enable_delete_chat = false;
		if ( defined( 'FORMINATOR_ADDON_SLACK_ENABLE_DELETE_CHAT' ) && FORMINATOR_ADDON_SLACK_ENABLE_DELETE_CHAT ) {
			$enable_delete_chat = true;
		}

		/**
		 * Filter Flag to enable delete chat
		 *
		 * @since  1.4
		 *
		 * @params bool $enable_delete_chat
		 */
		$enable_delete_chat = apply_filters( 'forminator_addon_slack_enable_delete_chat', $enable_delete_chat );

		return $enable_delete_chat;
	}

	/**
	 * Allow multiple connection on one form
	 *
	 * @since 1.0 Slack Addon
	 * @return bool
	 */
	public function is_allow_multi_on_form() {
		return true;
	}

	/**
	 * Settings wizard
	 *
	 * @since 1.0 Slack Addon
	 * @return array
	 */
	public function settings_wizards() {
		return array(
			array(
				'callback'     => array( $this, 'setup_client_id' ),
				'is_completed' => array( $this, 'setup_client_id_is_completed' ),
			),
			array(
				'callback'     => array( $this, 'authorize_access' ),
				'is_completed' => array( $this, 'authorize_access_is_completed' ),
			),
			array(
				'callback'     => array( $this, 'wait_authorize_access' ),
				'is_completed' => array( $this, 'is_authorized' ),
			),
		);
	}

	/**
	 * Authorize Access wizard
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param $submitted_data
	 *
	 * @return array
	 */
	public function setup_client_id( $submitted_data ) {
		$settings_values = $this->get_settings_values();
		$template        = forminator_addon_slack_dir() . 'views/settings/setup-client.php';

		$buttons = array();
		if ( $this->is_connected() ) {
			$buttons['disconnect']     = array(
				'markup' => self::get_button_markup( esc_html__( 'Disconnect', Forminator::DOMAIN ), 'sui-button-ghost forminator-addon-disconnect' ),
			);
			$buttons['next']['markup'] = '<div class="sui-actions-right">' .
										self::get_button_markup( esc_html__( 'RE-AUTHORIZE', Forminator::DOMAIN ), 'forminator-addon-next' ) .
										'</div>';
		} else {
			$buttons['next']['markup'] = '<div class="sui-actions-right">' .
										self::get_button_markup( esc_html__( 'Next', Forminator::DOMAIN ), 'forminator-addon-next' ) .
										'</div>';
		}

		$template_params = array(
			'token'               => $this->_token,
			'client_id'           => '',
			'client_id_error'     => '',
			'client_secret'       => '',
			'client_secret_error' => '',
			'error_message'       => '',
			'redirect_url'        => forminator_addon_integration_section_admin_url( $this->_slug, 'authorize', false ),
		);

		$has_errors = false;
		$is_submit  = ! empty( $submitted_data );

		foreach ( $template_params as $key => $value ) {
			if ( isset( $submitted_data[ $key ] ) ) {
				$template_params[ $key ] = $submitted_data[ $key ];
			} elseif ( isset( $settings_values[ $key ] ) ) {
				$template_params[ $key ] = $settings_values[ $key ];
			}
		}

		if ( empty( $template_params['client_id'] ) ) {
			$saved_client_id = $this->get_client_id();
			if ( ! empty( $saved_client_id ) ) {
				$template_params['client_id'] = $saved_client_id;
			}
		}

		if ( empty( $template_params['client_secret'] ) ) {
			$saved_client_secret = $this->get_client_secret();

			if ( ! empty( $saved_client_secret ) ) {
				$template_params['client_secret'] = $saved_client_secret;
			}
		}

		if ( $is_submit ) {
			$client_id     = isset( $submitted_data['client_id'] ) ? $submitted_data['client_id'] : '';
			$client_secret = isset( $submitted_data['client_secret'] ) ? $submitted_data['client_secret'] : '';

			if ( empty( $client_id ) ) {
				$template_params['client_id_error'] = __( 'Please input valid Client ID', Forminator::DOMAIN );
				$has_errors                         = true;
			}

			if ( empty( $client_secret ) ) {
				$template_params['client_secret_error'] = __( 'Please input valid Client Secret', Forminator::DOMAIN );
				$has_errors                             = true;
			}

			if ( ! $has_errors ) {
				// validate api
				try {
					if ( $this->get_client_id() !== $client_id || $this->get_client_secret() !== $client_secret ) {
						// reset connection!
						$settings_values = array();
					}
					$settings_values['client_id']     = $client_id;
					$settings_values['client_secret'] = $client_secret;

					$this->save_settings_values( $settings_values );

				} catch ( Forminator_Addon_Slack_Exception $e ) {
					$template_params['error_message'] = $e->getMessage();
					$has_errors                       = true;
				}
			}
		}

		return array(
			'html'       => self::get_template( $template, $template_params ),
			'buttons'    => $buttons,
			'redirect'   => false,
			'has_errors' => $has_errors,
			'size'       => 'normal',
		);
	}

	/**
	 * Setup client id is complete
	 *
	 * @param $submitted_data
	 *
	 * @return bool
	 */
	public function setup_client_id_is_completed( $submitted_data ) {
		$client_id     = $this->get_client_id();
		$client_secret = $this->get_client_secret();

		if ( ! empty( $client_id ) && ! empty( $client_secret ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Authorize Access wizard
	 *
	 * @since 1.0 Slack Addon
	 * @return array
	 */
	public function authorize_access() {

		$template = forminator_addon_slack_dir() . 'views/settings/authorize.php';

		$buttons = array();
		if ( $this->is_connected() ) {
			$buttons['disconnect'] = array(
				'markup' => self::get_button_markup( esc_html__( 'DISCONNECT', Forminator::DOMAIN ), 'sui-button-ghost forminator-addon-disconnect' ),
			);
		}

		$template_params = array(
			'auth_url' => $this->get_auth_url(),
			'token'    => $this->_token,
		);

		return array(
			'html'       => self::get_template( $template, $template_params ),
			'buttons'    => $buttons,
			'redirect'   => false,
			'has_errors' => false,
		);
	}

	public function authorize_access_is_completed() {
		return true;
	}

	/**
	 * Wait Authorize Access wizard
	 *
	 * @since 1.0 Slack Addon
	 * @return array
	 */
	public function wait_authorize_access() {
		$template         = forminator_addon_slack_dir() . 'views/settings/wait-authorize.php';
		$template_success = forminator_addon_slack_dir() . 'views/settings/success-authorize.php';
		$template_error   = forminator_addon_slack_dir() . 'views/settings/error-authorize.php';

		$buttons = array();

		$is_poll = true;

		$template_params = array(
			'token'    => $this->_token,
			'auth_url' => $this->get_auth_url(),
		);

		$has_errors = false;

		if ( $this->_token ) {
			$buttons['close'] = array(
				'markup' => self::get_button_markup( esc_html__( 'Close', Forminator::DOMAIN ), 'sui-button-ghost forminator-addon-close' ),
			);
			$is_poll          = false;

			$template = $template_success;
		} elseif ( $this->_auth_error_message ) {
			$template_params['error_message'] = $this->_auth_error_message;
			$is_poll                          = false;
			$has_errors                       = true;

			$setting_values = $this->get_settings_values();
			// reset err msg
			if ( $this->_auth_error_message ) {
				unset( $setting_values['auth_error_message'] );
				$this->save_settings_values( $setting_values );
				$this->_auth_error_message = '';
			}

			$template = $template_error;
		}

		return array(
			'html'       => self::get_template( $template, $template_params ),
			'buttons'    => $buttons,
			'is_poll'    => $is_poll,
			'redirect'   => false,
			'has_errors' => $has_errors,
		);
	}

	/**
	 * Authorized Callback
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param $submitted_data
	 *
	 * @return bool
	 */
	public function is_authorized( $submitted_data ) {
		$setting_values = $this->get_settings_values();

		// check api_key and and api_url set up
		return isset( $setting_values['token'] ) && ! empty( $setting_values['token'] );
	}

	/**
	 * Get Client ID
	 *
	 * @since 1.0 Slack Addon
	 * @return string
	 */
	public function get_client_id() {
		$settings_values = $this->get_settings_values();
		$client_id       = '';
		if ( isset( $settings_values ['client_id'] ) ) {
			$client_id = $settings_values ['client_id'];
		}

		/**
		 * Filter client id used
		 *
		 * @since 1.2
		 *
		 * @param string $client_id
		 */
		$client_id = apply_filters( 'forminator_addon_slack_client_id', $client_id );

		return $client_id;
	}

	/**
	 * Get Client secret
	 *
	 * @since 1.0 Slack Addon
	 * @return string
	 */
	public function get_client_secret() {
		$settings_values = $this->get_settings_values();
		$client_secret   = '';
		if ( isset( $settings_values ['client_secret'] ) ) {
			$client_secret = $settings_values ['client_secret'];
		}

		/**
		 * Filter client secret used
		 *
		 * @since 1.2
		 *
		 * @param string $client_secret
		 */
		$client_secret = apply_filters( 'forminator_addon_slack_client_secret', $client_secret );

		return $client_secret;
	}

	/**
	 * Get Access Token
	 *
	 * @since 1.0 Slack Addon
	 * @return string
	 */
	public function get_client_access_token() {
		$settings_values = $this->get_settings_values();
		$token           = '';
		if ( isset( $settings_values ['token'] ) ) {
			$token = $settings_values ['token'];
		}

		/**
		 * Filter access_token used
		 *
		 * @since 1.2
		 *
		 * @param string $token
		 */
		$token = apply_filters( 'forminator_addon_slack_client_access_token', $token );

		return $token;
	}

	/**
	 * Register a page for redirect url of Slack auth
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @return array
	 */
	public function register_integration_sections() {
		return array(
			'authorize' => array( $this, 'authorize_page_callback' ),
		);
	}

	/**
	 * Get Auth Url
	 *
	 * @return string
	 */
	public function get_auth_url() {
		$base_authorize_url = Forminator_Addon_Slack_Wp_Api::AUTHORIZE_URL;
		$client_id          = $this->get_client_id();
		$redirect_url       = rawurlencode( forminator_addon_integration_section_admin_url( $this->_slug, 'authorize', false ) );
		$scopes             = Forminator_Addon_Slack_Wp_Api::$oauth_scopes;

		/**
		 * Filter OAuth Scopes
		 *
		 * @since 1.3
		 *
		 * @param array $scopes
		 */
		$scopes = apply_filters( 'forminator_addon_slack_oauth_scopes', $scopes );

		$auth_url = add_query_arg(
			array(
				'client_id'    => $client_id,
				'scope'        => implode( ',', $scopes ),
				'redirect_uri' => $redirect_url,
			),
			$base_authorize_url
		);

		/**
		 * Filter Slack Auth Url
		 *
		 * @since 1.3
		 *
		 * @param string $auth_url
		 * @param string $base_authorize_url
		 * @param string $client_id
		 * @param array  $scopes
		 * @param string $redirect_url
		 */
		$auth_url = apply_filters( 'forminator_addon_slack_auth_url', $auth_url, $base_authorize_url, $client_id, $scopes, $redirect_url );

		return $auth_url;
	}

	/**
	 * Slack Authorize Page
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param $query_args
	 *
	 * @return string
	 */
	public function authorize_page_callback( $query_args ) {
		$settings        = $this->get_settings_values();
		$template        = forminator_addon_slack_dir() . 'views/sections/authorize.php';
		$template_params = array(
			'error_message' => '',
			'is_close'      => false,
		);

		if ( isset( $query_args['code'] ) ) {
			try {
				$code  = $query_args['code'];
				$token = '';

				// prefer new instance
				$api           = Forminator_Addon_Slack_Wp_Api::get_instance( uniqid() );
				$redirect_uri  = forminator_addon_integration_section_admin_url( $this->_slug, 'authorize', false );
				$token_request = $api->get_access_token( $code, $redirect_uri );

				if ( isset( $token_request->access_token ) ) {
					$token = $token_request->access_token;
				}

				if ( empty( $token ) ) {
					throw new Forminator_Addon_Slack_Exception( __( 'Failed to get token', Forminator::DOMAIN ) );
				}

				if ( ! $this->is_active() ) {
					$activated = Forminator_Addon_Loader::get_instance()->activate_addon( $this->_slug );
					if ( ! $activated ) {
						$last_message = Forminator_Addon_Loader::get_instance()->get_last_error_message();
						throw new Forminator_Addon_Slack_Exception( $last_message );
					}
				}

				$settings['token'] = $token;
				$this->save_settings_values( $settings );
				$template_params['is_close'] = true;
			} catch ( Exception $e ) {
				// catch all exception
				$template_params['error_message'] = $e->getMessage();
			}
		} else {
			$template_params['error_message'] = __( 'Failed to get authorization code.', Forminator::DOMAIN );
			// todo : translate $query_args[error]
			$settings['auth_error_message'] = $template_params['error_message'];
			$this->save_settings_values( $settings );
			$template_params['is_close'] = true;
		}

		return self::get_template( $template, $template_params );
	}

	/**
	 * Get API Instance
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param null|string $access_token
	 *
	 * @return Forminator_Addon_Slack_Wp_Api|null
	 * @throws Forminator_Addon_Slack_Wp_Api_Exception
	 */
	public function get_api( $access_token = null ) {
		if ( is_null( self::$_api ) ) {
			if ( is_null( $access_token ) ) {
				$access_token = $this->get_client_access_token();
			}

			$api        = Forminator_Addon_Slack_Wp_Api::get_instance( $access_token );
			self::$_api = $api;
		}

		return self::$_api;
	}

	/**
	 * Before get Setting Values
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param $values
	 *
	 * @return mixed
	 */
	public function before_get_settings_values( $values ) {
		if ( isset( $values['token'] ) ) {
			$this->_token = $values['token'];
		}

		if ( isset( $values['auth_error_message'] ) ) {
			$this->_auth_error_message = $values['auth_error_message'];
		}

		return $values;
	}

	/**
	 * Default filter for header
	 *
	 * its add / change Authorization header
	 * - on get access token it uses Basic realm of encoded client id and secret
	 * - on web API request it uses Bearer realm of access token which default of @see Forminator_Addon_Slack_Wp_Api
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param $headers
	 * @param $verb
	 * @param $path
	 * @param $args
	 *
	 * @return array
	 */
	public function default_filter_api_headers( $headers, $verb, $path, $args ) {
		if ( false !== stripos( $path, 'oauth.access' ) ) {
			$encoded_auth             = base64_encode( $this->get_client_id() . ':' . $this->get_client_secret() ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			$headers['Authorization'] = 'Basic ' . $encoded_auth;
			unset( $headers['Content-Type'] );
		}

		return $headers;
	}

	/**
	 * Flag for check if and addon connected to a poll(poll settings such as list id completed)
	 *
	 * Please apply necessary WordPress hook on the inheritance class
	 *
	 * @since   1.6.1
	 *
	 * @param $poll_id
	 *
	 * @return boolean
	 */
	public function is_poll_connected( $poll_id ) {
		try {
			$poll_settings_instance = null;
			if ( ! $this->is_connected() ) {
				throw new Forminator_Addon_Slack_Exception( 'Slack is not connected' );
			}

			$poll_settings_instance = $this->get_addon_poll_settings( $poll_id );
			if ( ! $poll_settings_instance instanceof Forminator_Addon_Slack_Poll_Settings ) {
				throw new Forminator_Addon_Slack_Exception( 'Invalid Poll Settings of Slack' );
			}

			// Mark as active when there is at least one active connection
			if ( false === $poll_settings_instance->find_one_active_connection() ) {
				throw new Forminator_Addon_Slack_Exception( 'No active Slack connection found in this poll' );
			}

			$is_poll_connected = true;

		} catch ( Forminator_Addon_Slack_Exception $e ) {

			$is_poll_connected = false;
		}

		/**
		 * Filter connected status Slack with the poll
		 *
		 * @since 1.6.1
		 *
		 * @param bool                                      $is_poll_connected
		 * @param int                                       $poll_id                Current Poll ID
		 * @param Forminator_Addon_Slack_Poll_Settings|null $poll_settings_instance Instance of poll settings, or null when unavailable
		 *
		 */
		$is_poll_connected = apply_filters( 'forminator_addon_slack_is_poll_connected', $is_poll_connected, $poll_id, $poll_settings_instance );

		return $is_poll_connected;
	}

	/**
	 * Allow multiple connection on one poll
	 *
	 * @since 1.6.1
	 * @return bool
	 */
	public function is_allow_multi_on_poll() {
		return true;
	}

	/**
	 * Flag for check if and addon connected to a quiz(quiz settings such as list id completed)
	 *
	 * Please apply necessary WordPress hook on the inheritance class
	 *
	 * @since   1.6.2
	 *
	 * @param $quiz_id
	 *
	 * @return boolean
	 */
	public function is_quiz_connected( $quiz_id ) {
		try {
			$quiz_settings_instance = null;
			if ( ! $this->is_connected() ) {
				throw new Forminator_Addon_Slack_Exception( 'Slack is not connected' );
			}

			$quiz_settings_instance = $this->get_addon_quiz_settings( $quiz_id );
			if ( ! $quiz_settings_instance instanceof Forminator_Addon_Slack_Quiz_Settings ) {
				throw new Forminator_Addon_Slack_Exception( 'Invalid Quiz Settings of Slack' );
			}

			// Mark as active when there is at least one active connection
			if ( false === $quiz_settings_instance->find_one_active_connection() ) {
				throw new Forminator_Addon_Slack_Exception( 'No active Slack connection found in this quiz' );
			}

			$is_quiz_connected = true;

		} catch ( Forminator_Addon_Slack_Exception $e ) {

			$is_quiz_connected = false;
		}

		/**
		 * Filter connected status Slack with the quiz
		 *
		 * @since 1.6.1
		 *
		 * @param bool                                      $is_quiz_connected
		 * @param int                                       $quiz_id                Current Quiz ID
		 * @param Forminator_Addon_Slack_Quiz_Settings|null $quiz_settings_instance Instance of Quiz settings, or null when unavailable
		 *
		 */
		$is_quiz_connected = apply_filters( 'forminator_addon_slack_is_quiz_connected', $is_quiz_connected, $quiz_id, $quiz_settings_instance );

		return $is_quiz_connected;
	}

	/**
	 * Allow multiple connection on one quiz
	 *
	 * @since 1.6.1
	 * @return bool
	 */
	public function is_allow_multi_on_quiz() {
		return true;
	}
}
