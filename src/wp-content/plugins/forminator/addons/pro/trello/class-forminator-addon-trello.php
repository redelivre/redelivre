<?php

require_once dirname( __FILE__ ) . '/class-forminator-addon-trello-exception.php';
require_once dirname( __FILE__ ) . '/lib/class-forminator-addon-trello-wp-api.php';

/**
 * Class Forminator_Addon_Trello
 * Trello Addon Main Class
 *
 * @since 1.0 Trello Addon
 */
final class Forminator_Addon_Trello extends Forminator_Addon_Abstract {

	/**
	 * @var self|null
	 */
	private static $_instance = null;

	protected $_slug                   = 'trello';
	protected $_version                = FORMINATOR_ADDON_TRELLO_VERSION;
	protected $_min_forminator_version = '1.1';
	protected $_short_title            = 'Trello';
	protected $_title                  = 'Trello';
	protected $_url                    = 'https://premium.wpmudev.org';
	protected $_full_path              = __FILE__;

	protected $_form_settings = 'Forminator_Addon_Trello_Form_Settings';
	protected $_form_hooks    = 'Forminator_Addon_Trello_Form_Hooks';

	protected $_position = 5;

	private $_app_key = '770a78f0ec0d8749df82fbbe150d80da';

	private static $api;

	const CARD_DELETE_MODE_DELETE = 'delete';
	const CARD_DELETE_MODE_CLOSED = 'closed';

	private static $card_delete_modes
		= array(
			self::CARD_DELETE_MODE_DELETE,
			self::CARD_DELETE_MODE_CLOSED,
		);

	/**
	 * Connected Account Info
	 *
	 * @var array
	 */
	private $connected_account = array();

	/**
	 * Current Token
	 *
	 * @var string
	 */
	private $_token = '';

	protected $_poll_settings = 'Forminator_Addon_Trello_Poll_Settings';
	protected $_poll_hooks    = 'Forminator_Addon_Trello_Poll_Hooks';

	protected $_quiz_settings = 'Forminator_Addon_Trello_Quiz_Settings';
	protected $_quiz_hooks    = 'Forminator_Addon_Trello_Quiz_Hooks';

	/**
	 * Forminator_Addon_Trello constructor.
	 *
	 * @since 1.0 Trello Addon
	 */
	public function __construct() {
		// late init to allow translation
		$this->_description                = __( 'Get awesome by your form.', Forminator::DOMAIN );
		$this->_activation_error_message   = __( 'Sorry but we failed to activate Trello Integration, don\'t hesitate to contact us', Forminator::DOMAIN );
		$this->_deactivation_error_message = __( 'Sorry but we failed to deactivate Trello Integration, please try again', Forminator::DOMAIN );

		$this->_update_settings_error_message = __(
			'Sorry, we failed to update settings, please check your form and try again',
			Forminator::DOMAIN
		);

		$this->_icon     = forminator_addon_trello_assets_url() . 'icons/trello.png';
		$this->_icon_x2  = forminator_addon_trello_assets_url() . 'icons/trello@2x.png';
		$this->_image    = forminator_addon_trello_assets_url() . 'img/trello.png';
		$this->_image_x2 = forminator_addon_trello_assets_url() . 'img/trello@2x.png';
	}

	/**
	 * Get Instance
	 *
	 * @since 1.0 Trello Addon
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
	 * @since 1.0 Trello Addon
	 *
	 * @return bool
	 */
	public function is_connected() {
		try {
			// check if its active
			if ( ! $this->is_active() ) {
				throw new Forminator_Addon_Trello_Exception( __( 'Trello is not active', Forminator::DOMAIN ) );
			}

			$is_connected   = false;
			$setting_values = $this->get_settings_values();
			// if user completed api setup
			if ( isset( $setting_values['token'] ) && ! empty( $setting_values['token'] ) ) {
				$is_connected = true;
			}
		} catch ( Forminator_Addon_Trello_Exception $e ) {
			$is_connected = false;
		}

		/**
		 * Filter connected status of trello
		 *
		 * @since 1.2
		 *
		 * @param bool $is_connected
		 */
		$is_connected = apply_filters( 'forminator_addon_trello_is_connected', $is_connected );

		return $is_connected;
	}

	/**
	 * Check if Trello is connected with current form
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param $form_id
	 *
	 * @return bool
	 */
	public function is_form_connected( $form_id ) {
		try {
			$form_settings_instance = null;
			if ( ! $this->is_connected() ) {
				throw new Forminator_Addon_Trello_Exception( __( ' Trello is not connected', Forminator::DOMAIN ) );
			}

			$form_settings_instance = $this->get_addon_form_settings( $form_id );
			if ( ! $form_settings_instance instanceof Forminator_Addon_Trello_Form_Settings ) {
				throw new Forminator_Addon_Trello_Exception( __( 'Invalid Form Settings of Trello', Forminator::DOMAIN ) );
			}

			// Mark as active when there is at least one active connection
			if ( false === $form_settings_instance->find_one_active_connection() ) {
				throw new Forminator_Addon_Trello_Exception( __( 'No active Trello connection found in this form', Forminator::DOMAIN ) );
			}

			$is_form_connected = true;

		} catch ( Forminator_Addon_Trello_Exception $e ) {
			$is_form_connected = false;
			forminator_addon_maybe_log( __METHOD__, $e->getMessage() );
		}

		/**
		 * Filter connected status of Trello with the form
		 *
		 * @since 1.2
		 *
		 * @param bool                                       $is_form_connected
		 * @param int                                        $form_id                Current Form ID
		 * @param Forminator_Addon_Trello_Form_Settings|null $form_settings_instance Instance of form settings, or null when unavailable
		 *
		 */
		$is_form_connected = apply_filters( 'forminator_addon_trello_is_form_connected', $is_form_connected, $form_id, $form_settings_instance );

		return $is_form_connected;
	}

	/**
	 * Override settings available,
	 *
	 * @since 1.0 Trello Addon
	 * @return bool
	 */
	public function is_settings_available() {
		return true;
	}

	/**
	 * Flag show full log on entries
	 *
	 * @since 1.0 Trello Addon
	 * @return bool
	 */
	public static function is_show_full_log() {
		$show_full_log = ( defined( 'FORMINATOR_ADDON_TRELLO_SHOW_FULL_LOG' ) && FORMINATOR_ADDON_TRELLO_SHOW_FULL_LOG );

		/**
		 * Filter show full log of Trello
		 *
		 * @since 1.2
		 *
		 * @param bool $show_full_log
		 */
		$show_full_log = apply_filters( 'forminator_addon_trello_show_full_log', $show_full_log );

		return $show_full_log;
	}

	/**
	 * Flag enable delete card before delete entries
	 *
	 * Its disabled by default
	 *
	 * @since 1.0 Trello Addon
	 * @return bool
	 */
	public static function is_enable_delete_card() {
		$enable_delete_card = ( defined( 'FORMINATOR_ADDON_TRELLO_ENABLE_DELETE_CARD' ) && FORMINATOR_ADDON_TRELLO_ENABLE_DELETE_CARD );

		/**
		 * Filter Flag enable delete card before delete entries
		 *
		 * @since  1.2
		 *
		 * @params bool $enable_delete_card
		 */
		$enable_delete_card = apply_filters( 'forminator_addon_trello_delete_card', $enable_delete_card );

		return $enable_delete_card;
	}

	/**
	 * Get Delete mode for card
	 *
	 * acceptable values : 'delete', 'closed'
	 * default is 'delete'
	 *
	 * @see   Forminator_Addon_Trello::is_enable_delete_card()
	 *
	 * @since 1.0 Trello Addon
	 * @return string
	 */
	public static function get_card_delete_mode() {
		$card_delete_mode = self::CARD_DELETE_MODE_DELETE;

		if ( defined( 'FORMINATOR_ADDON_TRELLO_CARD_DELETE_MODE' ) ) {
			$card_delete_mode = FORMINATOR_ADDON_TRELLO_CARD_DELETE_MODE;
		}

		/**
		 * Filter delete mode for card
		 *
		 * @since  1.2
		 *
		 * @params string $card_delete_mode
		 */
		$card_delete_mode = apply_filters( 'forminator_addon_trello_card_delete_mode', $card_delete_mode );

		// fallback to delete
		if ( ! in_array( $card_delete_mode, self::get_card_delete_modes(), true ) ) {
			$card_delete_mode = self::CARD_DELETE_MODE_DELETE;
		}

		return $card_delete_mode;
	}

	/**
	 * Allow multiple connection on one form
	 *
	 * @since 1.0 Trello Addon
	 * @return bool
	 */
	public function is_allow_multi_on_form() {
		return true;
	}

	/**
	 * Get Trello APP key
	 *
	 * @see   https://trello.com/app-key
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @return string;
	 */
	public function get_app_key() {
		$app_key = $this->_app_key;
		// check override by config constant
		if ( defined( 'FORMINATOR_ADDON_TRELLO_APP_KEY' ) && FORMINATOR_ADDON_TRELLO_APP_KEY ) {
			$app_key = FORMINATOR_ADDON_TRELLO_APP_KEY;
		}

		/**
		 * Filter APP Key used for API request(s) of Trello
		 *
		 * @since 1.2
		 *
		 * @param string $app_key
		 */
		$app_key = apply_filters( 'forminator_addon_trello_app_key', $app_key );

		return $app_key;
	}

	/**
	 * Settings wizard
	 *
	 * @since 1.0 Trello Addon
	 * @return array
	 */
	public function settings_wizards() {
		return array(
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
	 * @since 1.0 Trello Addon
	 * @return array
	 */
	public function authorize_access() {
		$template = forminator_addon_trello_dir() . 'views/settings/authorize.php';

		$buttons = array();

		$template_params = array(
			'connected_account' => $this->connected_account,
			'token'             => $this->_token,
			'auth_url'          => $this->get_auth_url(),
			'is_connected'      => $this->is_connected(),
		);

		return array(
			'html'       => self::get_template( $template, $template_params ),
			'buttons'    => $buttons,
			'redirect'   => false,
			'has_errors' => false,
		);
	}

	/**
	 * Wait Authorize Access wizard
	 *
	 * @since 1.0 Trello Addon
	 * @return array
	 */
	public function wait_authorize_access() {
		$template         = forminator_addon_trello_dir() . 'views/settings/wait-authorize.php';
		$template_success = forminator_addon_trello_dir() . 'views/settings/success-authorize.php';

		$buttons = array();

		$is_poll = true;

		$template_params = array(
			'connected_account' => $this->connected_account,
			'token'             => $this->_token,
			'auth_url'          => $this->get_auth_url(),
		);

		if ( $this->_token ) {
			$is_poll  = false;
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
	 * @since 1.0 Trello Addon
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
	 * Pseudo step
	 *
	 * @since 1.0 Trello Addon
	 * @return bool
	 */
	public function authorize_access_is_completed() {
		return true;
	}

	/**
	 * Get Connected Account
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @return array
	 */
	public function get_connected_account() {
		return $this->connected_account;
	}

	/**
	 * Register a page for redirect url of trello auth
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @return array
	 */
	public function register_integration_sections() {
		return array(
			'authorize' => array( $this, 'authorize_page_callback' ),
		);
	}

	/**
	 * Trello Authorize Page
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param $query_args
	 *
	 * @return string
	 */
	public function authorize_page_callback( $query_args ) {
		$template        = forminator_addon_trello_dir() . 'views/sections/authorize.php';
		$template_params = array(
			'error_message' => '',
			'is_close'      => false,
		);

		if ( isset( $query_args['token'] ) ) {
			try {
				$token     = $query_args['token'];
				$validated = $this->validate_token( $token );
				if ( true !== $validated ) {
					throw new Forminator_Addon_Trello_Exception( $validated );
				}
				if ( ! $this->is_active() ) {
					$activated = Forminator_Addon_Loader::get_instance()->activate_addon( $this->_slug );
					if ( ! $activated ) {
						$last_message = Forminator_Addon_Loader::get_instance()->get_last_error_message();
						throw new Forminator_Addon_Trello_Exception( $last_message );
					}
				}

				$this->save_settings_values( array( 'token' => $token ) );
				$template_params['is_close'] = true;
			} catch ( Forminator_Addon_Trello_Exception $e ) {
				$template_params['error_message'] = $e->getMessage();
			}
		}

		return self::get_template( $template, $template_params );
	}

	/**
	 * Get Trello Auth URL
	 *
	 * @since 1.1 Trello Addon
	 *
	 * @param string $return_url
	 *
	 * @return string
	 */
	public function get_auth_url( $return_url = '' ) {
		$authorize_url = 'https://trello.com/1/authorize/';
		if ( ! $return_url ) {
			$return_url = forminator_addon_integration_section_admin_url( $this->_slug, 'authorize', true );
		}
		$return_url = rawurlencode( $return_url );
		// https://developers.trello.com/page/authorization
		$auth_params = array(
			'callback_method' => 'fragment',
			'return_url'      => $return_url,
			'scope'           => 'read,write,account',
			'expiration'      => 'never',
			'name'            => 'Forminator Pro',
			'key'             => $this->get_app_key(),
			'response_type'   => 'token',
		);

		/**
		 * Filter params used to authorize user
		 *
		 * @since 1.2
		 *
		 * @param array $auth_params
		 */
		$auth_params = apply_filters( 'forminator_addon_trello_authorize_params', $auth_params );

		$authorize_url = add_query_arg( $auth_params, $authorize_url );

		return $authorize_url;
	}

	/**
	 * Validate token with trello API
	 *
	 * using `members/me`
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param $token
	 *
	 * @return bool|string
	 */
	public function validate_token( $token ) {
		try {
			// ensure new instance
			self::$api  = null;
			$api        = $this->get_api( $token );
			$me_request = $api->get_( 'members/me/' );

			if ( ! isset( $me_request->id ) || empty( $me_request->id ) ) {
				throw new Forminator_Addon_Trello_Exception( __( 'Failed to acquire user ID.', Forminator::DOMAIN ) );
			}

			if ( isset( $me_request->url ) ) {
				$this->connected_account['url'] = $me_request->url;
			}

			if ( isset( $me_request->email ) ) {
				$this->connected_account['email'] = $me_request->email;
			}

			$validated = true;

		} catch ( Forminator_Addon_Trello_Exception $e ) {
			$validated = $e->getMessage();
		}

		return $validated;
	}

	/**
	 * @param null $token
	 *
	 * @return Forminator_Addon_Trello_Wp_Api
	 * @throws Forminator_Addon_Trello_Wp_Api_Exception
	 */
	public function get_api( $token = null ) {
		if ( is_null( self::$api ) ) {
			if ( is_null( $token ) ) {
				$setting_values = $this->get_settings_values();
				if ( isset( $setting_values['token'] ) ) {
					$token = $setting_values['token'];
				}
			}
			$api       = new Forminator_Addon_Trello_Wp_Api( $this->get_app_key(), $token );
			self::$api = $api;
		}

		return self::$api;
	}

	/**
	 * Before get Setting Values
	 *
	 * Get `connected_account`
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param $values
	 *
	 * @return mixed
	 */
	public function before_get_settings_values( $values ) {
		forminator_addon_maybe_log( __METHOD__, $values );
		if ( isset( $values['connected_account'] ) && ! empty( $values['connected_account'] ) && is_array( $values['connected_account'] ) ) {
			$this->connected_account = $values['connected_account'];
		}
		if ( isset( $values['token'] ) ) {
			$this->_token = $values['token'];
			forminator_addon_maybe_log( __METHOD__, $this->_token );
		}

		return $values;
	}

	/**
	 * Before save Setting Values
	 *
	 * Append `connected_account`
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param $values
	 *
	 * @return mixed
	 */
	public function before_save_settings_values( $values ) {
		if ( ! empty( $this->connected_account ) && is_array( $this->connected_account ) ) {
			$values['connected_account'] = $this->connected_account;
		} else {
			unset( $values['connected_account'] );
		}

		return $values;
	}

	/**
	 * Revoke token on Trello before deactivate
	 *
	 * @since 1.0 Trello Addon
	 * @return bool
	 */
	public function deactivate() {
		$this->connected_account = array();
		try {
			$api = $this->get_api();
			// revoke token from trello server
			forminator_addon_maybe_log( __METHOD__, $this->_token );
			$api->delete_( 'tokens/' . $this->_token );
		} catch ( Forminator_Addon_Trello_Wp_Api_Exception $e ) {
			// API error, okay to be ignored, can be externally un-authorized
			forminator_addon_maybe_log( __METHOD__, $e->getMessage() );
		} catch ( Forminator_Addon_Trello_Exception $e ) {
			// this will never throws, but here for reference
			// just in case we add more logic on deactivate
			$this->_deactivation_error_message = $e->getMessage();

			return false;
		}

		return true;
	}

	/**
	 * Get available card delete modes
	 *
	 * @since 1.0 Trello Addon
	 * @return array
	 */
	public static function get_card_delete_modes() {
		$card_delete_modes = self::$card_delete_modes;

		/**
		 * Filter available delete modes for cards
		 *
		 * @since 1.2
		 *
		 * @param array $card_delete_modes
		 */
		$card_delete_modes = apply_filters( 'forminator_addon_trello_card_delete_modes', $card_delete_modes );

		return $card_delete_modes;
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
				throw new Forminator_Addon_Trello_Exception( 'Trello is not connected' );
			}

			$poll_settings_instance = $this->get_addon_poll_settings( $poll_id );
			if ( ! $poll_settings_instance instanceof Forminator_Addon_Trello_Poll_Settings ) {
				throw new Forminator_Addon_Trello_Exception( 'Invalid Poll Settings of Trello' );
			}

			// Mark as active when there is at least one active connection
			if ( false === $poll_settings_instance->find_one_active_connection() ) {
				throw new Forminator_Addon_Trello_Exception( 'No active Poll connection found in this poll' );
			}

			$is_poll_connected = true;

		} catch ( Forminator_Addon_Trello_Exception $e ) {

			$is_poll_connected = false;
		}

		/**
		 * Filter connected status Slack with the poll
		 *
		 * @since 1.6.1
		 *
		 * @param bool                                       $is_poll_connected
		 * @param int                                        $poll_id                Current Poll ID
		 * @param Forminator_Addon_Trello_Poll_Settings|null $poll_settings_instance Instance of poll settings, or null when unavailable
		 *
		 */
		$is_poll_connected = apply_filters( 'forminator_addon_trello_is_poll_connected', $is_poll_connected, $poll_id, $poll_settings_instance );

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
				throw new Forminator_Addon_Trello_Exception( 'Trello is not connected' );
			}

			$quiz_settings_instance = $this->get_addon_quiz_settings( $quiz_id );
			if ( ! $quiz_settings_instance instanceof Forminator_Addon_Trello_Quiz_Settings ) {
				throw new Forminator_Addon_Trello_Exception( 'Invalid Quiz Settings of Trello' );
			}

			// Mark as active when there is at least one active connection
			if ( false === $quiz_settings_instance->find_one_active_connection() ) {
				throw new Forminator_Addon_Trello_Exception( 'No active Trello connection found in this quiz' );
			}

			$is_quiz_connected = true;

		} catch ( Forminator_Addon_Trello_Exception $e ) {

			$is_quiz_connected = false;
		}

		/**
		 * Filter connected status Slack with the quiz
		 *
		 * @since 1.6.1
		 *
		 * @param bool                                       $is_quiz_connected
		 * @param int                                        $quiz_id                Current Quiz ID
		 * @param Forminator_Addon_Trello_Quiz_Settings|null $quiz_settings_instance Instance of quiz settings, or null when unavailable
		 *
		 */
		$is_quiz_connected = apply_filters( 'forminator_addon_trello_is_quiz_connected', $is_quiz_connected, $quiz_id, $quiz_settings_instance );

		return $is_quiz_connected;
	}

	/**
	 * Allow multiple connection on one quiz
	 *
	 * @since 1.6.2
	 * @return bool
	 */
	public function is_allow_multi_on_quiz() {
		return true;
	}
}
