<?php

require_once dirname( __FILE__ ) . '/class-forminator-addon-googlesheet-exception.php';

/**
 * Class Forminator_Addon_Googlesheet
 * Google Sheets Addon Main Class
 *
 * @since 1.0 Google Sheets Addon
 */
final class Forminator_Addon_Googlesheet extends Forminator_Addon_Abstract {

	/**
	 * @var self|null
	 */
	private static $_instance = null;

	protected $_slug                   = 'googlesheet';
	protected $_version                = FORMINATOR_ADDON_GOOGLESHEET_VERSION;
	protected $_min_forminator_version = '1.1';
	protected $_short_title            = 'Google Sheets';
	protected $_title                  = 'Google Sheets';
	protected $_url                    = 'https://premium.wpmudev.org';
	protected $_full_path              = __FILE__;

	protected $_form_settings = 'Forminator_Addon_Googlesheet_Form_Settings';
	protected $_form_hooks    = 'Forminator_Addon_Googlesheet_Form_Hooks';

	private $_token = '';

	const MIME_TYPE_GOOGLE_DRIVE_FOLDER = 'application/vnd.google-apps.folder';
	const MIME_TYPE_GOOGLE_SPREADSHEET  = 'application/vnd.google-apps.spreadsheet';

	protected $_poll_settings = 'Forminator_Addon_Googlesheet_Poll_Settings';
	protected $_poll_hooks    = 'Forminator_Addon_Googlesheet_Poll_Hooks';

	protected $_quiz_settings = 'Forminator_Addon_Googlesheet_Quiz_Settings';
	protected $_quiz_hooks    = 'Forminator_Addon_Googlesheet_Quiz_Hooks';

	protected $_position = 3;

	/**
	 * Forminator_Addon_Googlesheet constructor.
	 *
	 * @since 1.0 Google Sheets Addon
	 */
	public function __construct() {
		// late init to allow translation
		$this->_description                = __( 'Get awesome by your form.', Forminator::DOMAIN );
		$this->_activation_error_message   = __( 'Sorry but we failed to activate GoogleSheet Integration, don\'t hesitate to contact us', Forminator::DOMAIN );
		$this->_deactivation_error_message = __( 'Sorry but we failed to deactivate GoogleSheet Integration, please try again', Forminator::DOMAIN );

		$this->_update_settings_error_message = __(
			'Sorry, we failed to update settings, please check your form and try again',
			Forminator::DOMAIN
		);

		$this->_icon     = forminator_addon_googlesheet_assets_url() . 'icons/googlesheet.png';
		$this->_icon_x2  = forminator_addon_googlesheet_assets_url() . 'icons/googlesheet@2x.png';
		$this->_image    = forminator_addon_googlesheet_assets_url() . 'img/googlesheet.png';
		$this->_image_x2 = forminator_addon_googlesheet_assets_url() . 'img/googlesheet@2x.png';
	}

	/**
	 * Get Instance
	 *
	 * @since 1.0 Google Sheets Addon
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
	 * @since 1.0 Google Sheets Addon
	 *
	 * @return bool
	 */
	public function is_connected() {
		try {
			// check if its active
			if ( ! $this->is_active() ) {
				throw new Forminator_Addon_Googlesheet_Exception( __( 'Google Sheets is not active', Forminator::DOMAIN ) );
			}

			$is_connected   = false;
			$setting_values = $this->get_settings_values();
			// if user completed api setup
			if ( isset( $setting_values['token'] ) && ! empty( $setting_values['token'] ) ) {
				$is_connected = true;
			}
		} catch ( Forminator_Addon_Googlesheet_Exception $e ) {
			$is_connected = false;
		}

		/**
		 * Filter connected status of Google Sheets
		 *
		 * @since 1.2
		 *
		 * @param bool $is_connected
		 */
		$is_connected = apply_filters( 'forminator_addon_googlesheet_is_connected', $is_connected );

		return $is_connected;
	}

	/**
	 * Check if Google Sheets is connected with current form
	 *
	 * @since 1.0 Google Sheets Addon
	 *
	 * @param $form_id
	 *
	 * @return bool
	 */
	public function is_form_connected( $form_id ) {
		try {
			$form_settings_instance = null;
			if ( ! $this->is_connected() ) {
				throw new Forminator_Addon_Googlesheet_Exception( __( 'GoogleSheet is not connected', Forminator::DOMAIN ) );
			}

			$form_settings_instance = $this->get_addon_form_settings( $form_id );
			if ( ! $form_settings_instance instanceof Forminator_Addon_Googlesheet_Form_Settings ) {
				throw new Forminator_Addon_Googlesheet_Exception( __( 'Invalid Form Settings of GoogleSheet', Forminator::DOMAIN ) );
			}

			// Mark as active when there is at least one active connection
			if ( false === $form_settings_instance->find_one_active_connection() ) {
				throw new Forminator_Addon_Googlesheet_Exception( __( 'No active GoogleSheet connection found in this form', Forminator::DOMAIN ) );
			}

			$is_form_connected = true;

		} catch ( Forminator_Addon_Googlesheet_Exception $e ) {
			$is_form_connected = false;
		}

		/**
		 * Filter connected status GoogleSheet with the form
		 *
		 * @since 1.0
		 *
		 * @param bool                                            $is_form_connected
		 * @param int                                             $form_id                Current Form ID
		 * @param Forminator_Addon_Googlesheet_Form_Settings|null $form_settings_instance Instance of form settings, or null when unavailable
		 *
		 */
		$is_form_connected = apply_filters( 'forminator_addon_googlesheet_is_form_connected', $is_form_connected, $form_id, $form_settings_instance );

		return $is_form_connected;
	}

	/**
	 * Override settings available,
	 *
	 * @since 1.0 Google Sheets Addon
	 * @return bool
	 */
	public function is_settings_available() {
		return true;
	}

	/**
	 * Flag show full log on entries
	 *
	 * @since 1.0 Google Sheets Addon
	 * @return bool
	 */
	public static function is_show_full_log() {
		$show_full_log = false;
		if ( defined( 'FORMINATOR_ADDON_GOOGLESHEET_SHOW_FULL_LOG' ) && FORMINATOR_ADDON_GOOGLESHEET_SHOW_FULL_LOG ) {
			$show_full_log = true;
		}

		/**
		 * Filter Flag show full log on entries
		 *
		 * @since  1.2
		 *
		 * @params bool $show_full_log
		 */
		$show_full_log = apply_filters( 'forminator_addon_googlesheet_show_full_log', $show_full_log );

		return $show_full_log;
	}

	/**
	 * Allow multiple connection on one form
	 *
	 * @since 1.0 Google Sheets Addon
	 * @return bool
	 */
	public function is_allow_multi_on_form() {
		return true;
	}

	/**
	 * Settings wizard
	 *
	 * @since 1.0 Google Sheets Addon
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
	 * @since 1.0 Google Sheets Addon
	 *
	 * @param $submitted_data
	 *
	 * @return array
	 */
	public function setup_client_id( $submitted_data ) {
		$settings_values = $this->get_settings_values();
		$template        = forminator_addon_googlesheet_dir() . 'views/settings/setup-client.php';

		$buttons = array();
		if ( $this->is_connected() ) {
			$buttons['disconnect']     = array(
				'markup' => self::get_button_markup( esc_html__( 'DISCONNECT', Forminator::DOMAIN ), 'sui-button-ghost forminator-addon-disconnect' ),
			);
			$buttons['next']['markup'] = '<div class="sui-actions-right">' .
										Forminator_Addon_Mailchimp::get_button_markup( esc_html__( 'RE-AUTHORIZE', Forminator::DOMAIN ), 'forminator-addon-next' ) .
										'</div>';
		} else {
			$buttons['next']['markup'] = '<div class="sui-actions-right">' .
										Forminator_Addon_Mailchimp::get_button_markup( esc_html__( 'Next', Forminator::DOMAIN ), 'forminator-addon-next' ) .
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

				} catch ( Forminator_Addon_Googlesheet_Exception $e ) {
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
	 * @since 1.0 Google Sheets Addon
	 * @return array
	 * @throws Exception
	 */
	public function authorize_access() {

		$template = forminator_addon_googlesheet_dir() . 'views/settings/authorize.php';

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
	 * @since 1.0 Google Sheets Addon
	 * @return array
	 * @throws Exception
	 */
	public function wait_authorize_access() {
		$template         = forminator_addon_googlesheet_dir() . 'views/settings/wait-authorize.php';
		$template_success = forminator_addon_googlesheet_dir() . 'views/settings/success-authorize.php';

		$buttons = array();

		$is_poll = true;

		$template_params = array(
			'token'    => $this->_token,
			'auth_url' => $this->get_auth_url(),
		);

		if ( $this->_token ) {
			$buttons['close'] = array(
				'markup' => self::get_button_markup( esc_html__( 'Close', Forminator::DOMAIN ), 'sui-button-ghost forminator-addon-close' ),
			);
			$is_poll          = false;

			$template = $template_success;
		}

		return array(
			'html'       => self::get_template( $template, $template_params ),
			'buttons'    => $buttons,
			'is_poll'    => $is_poll,
			'redirect'   => false,
			'has_errors' => false,
		);
	}

	/**
	 * Authorized Callback
	 *
	 * @since 1.0 Google Sheets Addon
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
	 * Get Auth Url
	 *
	 * @return string
	 * @throws Exception
	 */
	public function get_auth_url() {
		$google_client = $this->get_google_client();
		$auth_url      = $google_client->createAuthUrl();

		return $auth_url;
	}

	/**
	 * Get Client ID
	 *
	 * @since 1.0 Google Sheets Addon
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
		$client_id = apply_filters( 'forminator_addon_googlesheet_client_id', $client_id );

		return $client_id;
	}

	/**
	 * Get Client secret
	 *
	 * @since 1.0 Google Sheets Addon
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
		$client_secret = apply_filters( 'forminator_addon_googlesheet_client_secret', $client_secret );

		return $client_secret;
	}

	/**
	 * Get Access Token
	 *
	 * @since 1.0 Google Sheets Addon
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
		 * @param string $token (json encoded)
		 */
		$token = apply_filters( 'forminator_addon_googlesheet_client_access_token', $token );

		return $token;
	}

	/**
	 * Update Access Token
	 *
	 * @since 1.0 Google Sheets Addon
	 *
	 * @param $access_token
	 *
	 * @return string
	 */
	public function update_client_access_token( $access_token ) {
		$settings_values           = $this->get_settings_values();
		$settings_values ['token'] = $access_token;
		$this->save_settings_values( $settings_values );
	}

	/**
	 * Register a page for redirect url of Goolge auth
	 *
	 * @since 1.0 Google Sheets Addon
	 *
	 * @return array
	 */
	public function register_integration_sections() {
		return array(
			'authorize' => array( $this, 'authorize_page_callback' ),
		);
	}


	/**
	 * Google Sheets Authorize Page
	 *
	 * @since 1.0 Google Sheets Addon
	 *
	 * @param $query_args
	 *
	 * @return string
	 */
	public function authorize_page_callback( $query_args ) {
		$settings        = $this->get_settings_values();
		$template        = forminator_addon_googlesheet_dir() . 'views/sections/authorize.php';
		$template_params = array(
			'error_message' => '',
			'is_close'      => false,
		);

		if ( isset( $query_args['code'] ) ) {
			try {
				$google_client = $this->get_google_client();
				$google_client->authenticate( $query_args['code'] );
				$token = $google_client->getAccessToken();
				if ( empty( $token ) ) {
					throw new Forminator_Addon_Googlesheet_Exception( __( 'Failed to get token', Forminator::DOMAIN ) );
				}

				if ( ! $this->is_active() ) {
					$activated = Forminator_Addon_Loader::get_instance()->activate_addon( $this->_slug );
					if ( ! $activated ) {
						$last_message = Forminator_Addon_Loader::get_instance()->get_last_error_message();
						throw new Forminator_Addon_Googlesheet_Exception( $last_message );
					}
				}

				$settings['token'] = $token;
				$this->save_settings_values( $settings );
				$template_params['is_close'] = true;
			} catch ( Exception $e ) {
				// catch all exception
				$template_params['error_message'] = $e->getMessage();
			}
		}

		return self::get_template( $template, $template_params );
	}

	/**
	 * Get Forminator_Google_Client Object
	 *
	 * @since 1.0 Google Sheets Addon
	 * @return Forminator_Google_Client
	 * @throws Exception
	 */
	public function get_google_client() {
		spl_autoload_register( 'forminator_addon_googlesheet_google_api_client_autoload' );
		$redirect_url  = forminator_addon_integration_section_admin_url( $this->_slug, 'authorize', false );
		$client_id     = $this->get_client_id();
		$client_secret = $this->get_client_secret();
		$scopes        = array(
			Forminator_Google_Service_Sheets::SPREADSHEETS,
			Forminator_Google_Service_Sheets::DRIVE,
		);

		$config = new Forminator_Google_Config();
		$config->setLoggerClass( 'Forminator_Addon_Wp_Googlesheet_Client_Logger' );
		$google_client = new Forminator_Google_Client( $config );
		$google_client->setApplicationName( __( 'Forminator Pro', Forminator::DOMAIN ) );
		$google_client->setClientId( $client_id );
		$google_client->setClientSecret( $client_secret );
		$google_client->setScopes( $scopes );
		$google_client->setRedirectUri( $redirect_url );
		$google_client->setAccessType( 'offline' );
		$google_client->setApprovalPrompt( 'force' );

		/**
		 * Filter Google API Client used through out cycle
		 *
		 * @since 1.2
		 *
		 * @param Forminator_Google_Client $google_client
		 * @param string        $client_id
		 * @param string        $client_secret
		 * @param array         $scopes
		 * @param string        $redirect_url
		 */
		$google_client = apply_filters( 'forminator_addon_googlesheet_google_client', $google_client, $client_id, $client_secret, $scopes, $redirect_url );

		return $google_client;
	}

	/**
	 * Before get Setting Values
	 *
	 * @since 1.0 Google Sheets Addon
	 *
	 * @param $values
	 *
	 * @return mixed
	 */
	public function before_get_settings_values( $values ) {
		if ( isset( $values['token'] ) ) {
			$this->_token = $values['token'];
		}

		return $values;
	}

	/** @noinspection PhpUndefinedClassInspection */
	/**
	 * Revoke token on Google before deactivate
	 *
	 * @since 1.0 Google Sheets Addon
	 * @return bool
	 * @throws Forminator_Google_Auth_Exception
	 * @throws Exception
	 */
	public function deactivate() {
		try {
			$google_client = $this->get_google_client();
			$google_client->setAccessToken( $this->get_client_access_token() );
			$revoked = $google_client->revokeToken();

			if ( ! $revoked ) {
				throw new Forminator_Addon_Googlesheet_Exception( __( 'Failed to revoke access token', Forminator::DOMAIN ) );
			}
			$this->_token = '';
		} catch ( Forminator_Addon_Googlesheet_Exception $e ) {
			$this->_deactivation_error_message = $e->getMessage();

			return false;
		}

		return true;
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
				throw new Forminator_Addon_Googlesheet_Exception( __( 'GoogleSheet is not connected', Forminator::DOMAIN ) );
			}

			$poll_settings_instance = $this->get_addon_poll_settings( $poll_id );
			if ( ! $poll_settings_instance instanceof Forminator_Addon_Googlesheet_Poll_Settings ) {
				throw new Forminator_Addon_Googlesheet_Exception( __( 'Invalid Poll Settings of GoogleSheet', Forminator::DOMAIN ) );
			}

			// Mark as active when there is at least one active connection
			if ( false === $poll_settings_instance->find_one_active_connection() ) {
				throw new Forminator_Addon_Googlesheet_Exception( __( 'No active GoogleSheet connection found in this poll', Forminator::DOMAIN ) );
			}

			$is_poll_connected = true;

		} catch ( Forminator_Addon_Googlesheet_Exception $e ) {

			$is_poll_connected = false;
		}

		/**
		 * Filter connected status GoogleSheet with the poll
		 *
		 * @since 1.6.1
		 *
		 * @param bool                                            $is_poll_connected
		 * @param int                                             $poll_id                Current Poll ID
		 * @param Forminator_Addon_Googlesheet_Poll_Settings|null $poll_settings_instance Instance of poll settings, or null when unavailable
		 *
		 */
		$is_poll_connected = apply_filters( 'forminator_addon_googlesheet_is_poll_connected', $is_poll_connected, $poll_id, $poll_settings_instance );

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
	 * @since   1.6.1
	 *
	 * @param $quiz_id
	 *
	 * @return boolean
	 */
	public function is_quiz_connected( $quiz_id ) {
		try {
			$quiz_settings_instance = null;
			if ( ! $this->is_connected() ) {
				throw new Forminator_Addon_Googlesheet_Exception( __( 'GoogleSheet is not connected', Forminator::DOMAIN ) );
			}

			$quiz_settings_instance = $this->get_addon_quiz_settings( $quiz_id );
			if ( ! $quiz_settings_instance instanceof Forminator_Addon_Googlesheet_Quiz_Settings ) {
				throw new Forminator_Addon_Googlesheet_Exception( __( 'Invalid Quiz Settings of GoogleSheet', Forminator::DOMAIN ) );
			}

			// Mark as active when there is at least one active connection
			if ( false === $quiz_settings_instance->find_one_active_connection() ) {
				throw new Forminator_Addon_Googlesheet_Exception( __( 'No active GoogleSheet connection found in this quiz', Forminator::DOMAIN ) );
			}

			$is_quiz_connected = true;

		} catch ( Forminator_Addon_Googlesheet_Exception $e ) {

			$is_quiz_connected = false;
		}

		/**
		 * Filter connected status GoogleSheet with the quiz
		 *
		 * @since 1.6.1
		 *
		 * @param bool                                            $is_quiz_connected
		 * @param int                                             $quiz_id                Current Quiz ID
		 * @param Forminator_Addon_Googlesheet_Quiz_Settings|null $quiz_settings_instance Instance of quiz settings, or null when unavailable
		 *
		 */
		$is_quiz_connected = apply_filters( 'forminator_addon_googlesheet_is_quiz_connected', $is_quiz_connected, $quiz_id, $quiz_settings_instance );

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
