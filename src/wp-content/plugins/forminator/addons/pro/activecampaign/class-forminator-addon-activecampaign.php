<?php

require_once dirname( __FILE__ ) . '/class-forminator-addon-activecampaign-exception.php';
require_once dirname( __FILE__ ) . '/lib/class-forminator-addon-activecampaign-wp-api.php';

/**
 * Class Forminator_Addon_Activecampaign
 * Activecampaign Addon Main Class
 *
 * @since 1.0 Activecampaign Addon
 */
final class Forminator_Addon_Activecampaign extends Forminator_Addon_Abstract {

	/**
	 * @var self|null
	 */
	private static $_instance = null;

	protected $_slug                   = 'activecampaign';
	protected $_version                = FORMINATOR_ADDON_ACTIVECAMPAIGN_VERSION;
	protected $_min_forminator_version = '1.1';
	protected $_short_title            = 'ActiveCampaign';
	protected $_title                  = 'ActiveCampaign';
	protected $_url                    = 'https://premium.wpmudev.org';
	protected $_full_path              = __FILE__;
	protected $_position               = 8;

	protected $_form_settings = 'Forminator_Addon_Activecampaign_Form_Settings';
	protected $_form_hooks    = 'Forminator_Addon_Activecampaign_Form_Hooks';

	/**
	 * @var Forminator_Addon_Activecampaign_Wp_Api|null
	 */
	private static $api = null;

	public $connected_account = null;

	/**
	 * Forminator_Addon_Activecampaign constructor.
	 *
	 * @since 1.0 Activecampaign Addon
	 */
	public function __construct() {
		// late init to allow translation
		$this->_description                = __( 'Get awesome by your form.', Forminator::DOMAIN );
		$this->_activation_error_message   = __( 'Sorry but we failed to activate Activecampaign Integration, don\'t hesitate to contact us', Forminator::DOMAIN );
		$this->_deactivation_error_message = __( 'Sorry but we failed to deactivate Activecampaign Integration, please try again', Forminator::DOMAIN );

		$this->_update_settings_error_message = __(
			'Sorry, we failed to update settings, please check your form and try again',
			Forminator::DOMAIN
		);

		$this->_icon     = forminator_addon_activecampaign_assets_url() . 'icons/activecampaign.png';
		$this->_icon_x2  = forminator_addon_activecampaign_assets_url() . 'icons/activecampaign@2x.png';
		$this->_image    = forminator_addon_activecampaign_assets_url() . 'img/activecampaign.png';
		$this->_image_x2 = forminator_addon_activecampaign_assets_url() . 'img/activecampaign@2x.png';
	}

	/**
	 * Get Instance
	 *
	 * @since 1.0 Activecampaign Addon
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
	 * @since 1.0 Activecampaign Addon
	 *
	 * @return bool
	 */
	public function is_connected() {
		try {
			// check if its active
			if ( ! $this->is_active() ) {
				throw new Forminator_Addon_Activecampaign_Exception( __( 'ActiveCampaign is not active', Forminator::DOMAIN ) );
			}

			// if user completed api setup
			$is_connected = $this->is_api_completed();

		} catch ( Forminator_Addon_Activecampaign_Exception $e ) {
			$is_connected = false;
		}

		/**
		 * Filter connected status of active campaign
		 *
		 * @since 1.2
		 *
		 * @param bool $is_connected
		 */
		$is_connected = apply_filters( 'forminator_addon_activecampaign_is_connected', $is_connected );

		return $is_connected;
	}

	/**
	 * Check if Activecampaign is connected with current form
	 *
	 * @since 1.0 Activecampaign Addon
	 *
	 * @param $form_id
	 *
	 * @return bool
	 */
	public function is_form_connected( $form_id ) {
		try {
			$form_settings_instance = null;
			if ( ! $this->is_connected() ) {
				throw new Forminator_Addon_Activecampaign_Exception( __( ' ActiveCampaign is not connected', Forminator::DOMAIN ) );
			}

			$form_settings_instance = $this->get_addon_form_settings( $form_id );
			if ( ! $form_settings_instance instanceof Forminator_Addon_Activecampaign_Form_Settings ) {
				throw new Forminator_Addon_Activecampaign_Exception( __( 'Invalid Form Settings of ActiveCampaign', Forminator::DOMAIN ) );
			}

			// Mark as active when there is at least one active connection
			if ( false === $form_settings_instance->find_one_active_connection() ) {
				throw new Forminator_Addon_Activecampaign_Exception( __( 'No active ActiveCampaign connection found in this form', Forminator::DOMAIN ) );
			}

			$is_form_connected = true;

		} catch ( Forminator_Addon_Activecampaign_Exception $e ) {
			$is_form_connected = false;
			forminator_addon_maybe_log( __METHOD__, $e->getMessage() );
		}

		/**
		 * Filter connected status of ActiveCampaign with the form
		 *
		 * @since 1.2
		 *
		 * @param bool                                               $is_form_connected
		 * @param int                                                $form_id                Current Form ID
		 * @param Forminator_Addon_Activecampaign_Form_Settings|null $form_settings_instance Instance of form settings, or null when unavailable
		 *
		 */
		$is_form_connected = apply_filters( 'forminator_addon_activecampaign_is_form_connected', $is_form_connected, $form_id, $form_settings_instance );

		return $is_form_connected;
	}

	/**
	 * Override settings available,
	 *
	 * @since 1.0 Activecampaign Addon
	 * @return bool
	 */
	public function is_settings_available() {
		return true;
	}

	/**
	 * Flag show full log on entries
	 *
	 * @since 1.0 Activecampaign Addon
	 * @return bool
	 */
	public static function is_show_full_log() {
		$show_full_log = false;
		if ( defined( 'FORMINATOR_ADDON_ACTIVECAMPAIGN_SHOW_FULL_LOG' ) && FORMINATOR_ADDON_ACTIVECAMPAIGN_SHOW_FULL_LOG ) {
			$show_full_log = true;
		}

		/**
		 * Filter Flag show full log on entries
		 *
		 * @since  1.2
		 *
		 * @params bool $show_full_log
		 */
		$show_full_log = apply_filters( 'forminator_addon_activecampaign_show_full_log', $show_full_log );

		return $show_full_log;
	}

	/**
	 * Flag enable delete contact before delete entries
	 *
	 * Its disabled by default
	 *
	 * @since 1.0 Activecampaign Addon
	 * @return bool
	 */
	public static function is_enable_delete_contact() {
		$enable_delete_contact = false;
		if ( defined( 'FORMINATOR_ADDON_ACTIVECAMPAIGN_ENABLE_DELETE_CONTACT' ) && FORMINATOR_ADDON_ACTIVECAMPAIGN_ENABLE_DELETE_CONTACT ) {
			$enable_delete_contact = true;
		}

		/**
		 * Filter Flag enable delete contact before delete entries
		 *
		 * @since  1.2
		 *
		 * @params bool $enable_delete_contact
		 */
		$enable_delete_contact = apply_filters( 'forminator_addon_activecampaign_delete_contact', $enable_delete_contact );

		return $enable_delete_contact;
	}

	/**
	 * Allow multiple connection on one form
	 *
	 * @since 1.0 Activecampaign Addon
	 * @return bool
	 */
	public function is_allow_multi_on_form() {
		return true;
	}

	/**
	 * Setting wizard of Active Campaign
	 *
	 * @since 1.0 Activecampaign Addon
	 * @return array
	 */
	public function settings_wizards() {
		return array(
			array(
				'callback'     => array( $this, 'setup_api' ),
				'is_completed' => array( $this, 'is_api_completed' ),
			),
		);
	}


	/**
	 * Setup API Wizard
	 *
	 * @since 1.0 Active Campaign Addon
	 *
	 * @param     $submitted_data
	 *
	 * @param int $form_id
	 *
	 * @return array
	 */
	public function setup_api( $submitted_data, $form_id = 0 ) {
		$settings_values  = $this->get_settings_values();
		$template         = forminator_addon_activecampaign_dir() . 'views/settings/setup-api.php';
		$template_success = forminator_addon_activecampaign_dir() . 'views/settings/setup-api-success.php';
		$template_params  = array(
			'error_message' => '',
			'api_url'       => '',
			'api_url_error' => '',
			'api_key'       => '',
			'api_key_error' => '',
		);
		$has_errors       = false;
		$show_success     = false;
		$is_submit        = ! empty( $submitted_data );

		foreach ( $template_params as $key => $value ) {
			if ( isset( $submitted_data[ $key ] ) ) {
				$template_params[ $key ] = $submitted_data[ $key ];
			} elseif ( isset( $settings_values[ $key ] ) ) {
				$template_params[ $key ] = $settings_values[ $key ];
			}
		}

		if ( $is_submit ) {
			$api_url = isset( $submitted_data['api_url'] ) ? $submitted_data['api_url'] : '';
			$api_key = isset( $submitted_data['api_key'] ) ? $submitted_data['api_key'] : '';

			try {
				$api_url = $this->validate_api_url( $api_url );
			} catch ( Forminator_Addon_Activecampaign_Exception $e ) {
				$template_params['api_url_error'] = $e->getMessage();
				$has_errors                       = true;
			}

			try {
				$api_key = $this->validate_api_key( $api_key );
			} catch ( Forminator_Addon_Activecampaign_Exception $e ) {
				$template_params['api_key_error'] = $e->getMessage();
				$has_errors                       = true;
			}

			if ( ! $has_errors ) {
				// validate api
				try {

					$this->validate_api( $api_url, $api_key );

					if ( ! forminator_addon_is_active( $this->_slug ) ) {
						$activated = Forminator_Addon_Loader::get_instance()->activate_addon( $this->_slug );
						if ( ! $activated ) {
							throw new Forminator_Addon_Activecampaign_Exception( Forminator_Addon_Loader::get_instance()->get_last_error_message() );
						}
					}

					$settings_values = array(
						'api_url' => $api_url,
						'api_key' => $api_key,
					);
					$this->save_settings_values( $settings_values );

					// no form_id its on global settings
					if ( empty( $form_id ) ) {
						$show_success = true;
					}
				} catch ( Forminator_Addon_Activecampaign_Exception $e ) {
					$template_params['error_message'] = $e->getMessage();
					$has_errors                       = true;
				}
			}
		}

		if ( $show_success ) {
			$template = $template_success;
		}

		$buttons = array();

		if ( $show_success ) {
			$buttons['close'] = array(
				'markup' => self::get_button_markup( esc_html__( 'Close', Forminator::DOMAIN ), 'sui-button-ghost forminator-addon-close' ),
			);
		} else {
			if ( $this->is_connected() ) {
				$buttons['disconnect'] = array(
					'markup' => self::get_button_markup( esc_html__( 'Disconnect', Forminator::DOMAIN ), 'sui-button-ghost forminator-addon-disconnect' ),
				);
				$buttons['submit']     = array(
					'markup' => '<div class="sui-actions-right">' .
								self::get_button_markup( esc_html__( 'Save', Forminator::DOMAIN ), 'forminator-addon-connect' ) .
								'</div>',
				);
			} else {
				$buttons['submit'] = array(
					'markup' => '<div class="sui-actions-right">' .
								self::get_button_markup( esc_html__( 'CONNECT', Forminator::DOMAIN ), 'forminator-addon-connect' ) .
								'</div>',
				);
			}
		}

		return array(
			'html'       => self::get_template( $template, $template_params ),
			'buttons'    => $buttons,
			'redirect'   => false,
			'has_errors' => $has_errors,
		);
	}

	public function is_api_completed() {
		$setting_values = $this->get_settings_values();

		// check api_key and and api_url set up
		return isset( $setting_values['api_key'] ) && $setting_values['api_key'] && isset( $setting_values['api_url'] ) && ! empty( $setting_values['api_url'] );
	}

	/**
	 * Validate API URL
	 *
	 * @since 1.0 Active Campaign
	 *
	 * @param string $api_url
	 *
	 * @return string
	 * @throws Forminator_Addon_Activecampaign_Exception
	 */
	public function validate_api_url( $api_url ) {
		if ( empty( $api_url ) ) {
			throw new Forminator_Addon_Activecampaign_Exception( __( 'Please put a valid ActiveCampaign API URL', Forminator::DOMAIN ) );
		}

		$api_url = wp_http_validate_url( $api_url );
		if ( false === $api_url ) {
			throw new Forminator_Addon_Activecampaign_Exception( __( 'Please put a valid ActiveCampaign API URL', Forminator::DOMAIN ) );
		}

		return $api_url;
	}

	/**
	 * Validate API Key
	 *
	 * @since 1.0 Active Campaign
	 *
	 * @param string $api_key
	 *
	 * @return string
	 * @throws Forminator_Addon_Activecampaign_Exception
	 */
	public function validate_api_key( $api_key ) {
		if ( empty( $api_key ) ) {
			throw new Forminator_Addon_Activecampaign_Exception( __( 'Please put a valid ActiveCampaign API Key', Forminator::DOMAIN ) );
		}

		return $api_key;
	}

	/**
	 * Validate API
	 *
	 * @since 1.0 Active Campaign Addon
	 *
	 * @param $api_url
	 * @param $api_key
	 *
	 * @throws Forminator_Addon_Activecampaign_Wp_Api_Exception
	 * @throws Forminator_Addon_Activecampaign_Exception
	 */
	public function validate_api( $api_url, $api_key ) {
		self::$api = null;
		$api       = $this->get_api( $api_url, $api_key );

		$account_request = $api->get_account();

		if ( ! isset( $account_request->account ) || empty( $account_request->account ) ) {
			throw new Forminator_Addon_Activecampaign_Exception( __( 'Failed to get ActiveCampaign account info.', Forminator::DOMAIN ) );
		}

		$this->connected_account = $account_request->account;
	}

	/**
	 * Get API Instance
	 *
	 * @since 1.0 Active Campaign Addon
	 *
	 * @param null $api_url
	 * @param null $api_key
	 *
	 * @return Forminator_Addon_Activecampaign_Wp_Api
	 * @throws Forminator_Addon_Activecampaign_Wp_Api_Exception
	 */
	public function get_api( $api_url = null, $api_key = null ) {
		if ( is_null( self::$api ) ) {
			if ( is_null( $api_key ) || is_null( $api_url ) ) {
				$setting_values = $this->get_settings_values();
				$api_key        = '';
				$api_url        = '';
				if ( isset( $setting_values['api_url'] ) ) {
					$api_url = $setting_values['api_url'];
				}

				if ( isset( $setting_values['api_key'] ) ) {
					$api_key = $setting_values['api_key'];
				}
			}
			$api       = new Forminator_Addon_Activecampaign_Wp_Api( $api_url, $api_key );
			self::$api = $api;
		}

		return self::$api;
	}

	public function before_save_settings_values( $values ) {
		if ( ! empty( $this->connected_account ) ) {
			$values['connected_account'] = $this->connected_account;
		}

		return $values;
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
		return false;
	}
}
