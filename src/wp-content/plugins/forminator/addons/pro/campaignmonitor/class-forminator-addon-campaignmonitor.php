<?php

require_once dirname( __FILE__ ) . '/class-forminator-addon-campaignmonitor-exception.php';
require_once dirname( __FILE__ ) . '/lib/class-forminator-addon-campaignmonitor-wp-api.php';

/**
 * Class Forminator_Addon_Campaignmonitor
 * Campaignmonitor Addon Main Class
 *
 * @since 1.0 Campaignmonitor Addon
 */
final class Forminator_Addon_Campaignmonitor extends Forminator_Addon_Abstract {

	/**
	 * @var self|null
	 */
	private static $_instance = null;

	protected $_slug                   = 'campaignmonitor';
	protected $_version                = FORMINATOR_ADDON_CAMPAIGNMONITOR_VERSION;
	protected $_min_forminator_version = '1.1';
	protected $_short_title            = 'Campaign Monitor';
	protected $_title                  = 'Campaign Monitor';
	protected $_url                    = 'https://premium.wpmudev.org';
	protected $_full_path              = __FILE__;
	protected $_position               = 6;

	protected $_form_settings = 'Forminator_Addon_Campaignmonitor_Form_Settings';
	protected $_form_hooks    = 'Forminator_Addon_Campaignmonitor_Form_Hooks';

	/**
	 * Campaign Monitor API
	 *
	 * @var Forminator_Addon_Campaignmonitor_Wp_Api|null
	 */
	private static $api = null;

	/**
	 * Forminator_Addon_Campaignmonitor constructor.
	 *
	 * @since 1.0 Campaignmonitor Addon
	 */
	public function __construct() {
		// late init to allow translation
		$this->_description                = __( 'Get awesome by your form.', Forminator::DOMAIN );
		$this->_activation_error_message   = __( 'Sorry but we failed to activate Campaign Monitor Integration, don\'t hesitate to contact us', Forminator::DOMAIN );
		$this->_deactivation_error_message = __( 'Sorry but we failed to deactivate Campaign Monitor Integration, please try again', Forminator::DOMAIN );

		$this->_update_settings_error_message = __(
			'Sorry, we failed to update settings, please check your form and try again',
			Forminator::DOMAIN
		);

		$this->_icon     = forminator_addon_campaignmonitor_assets_url() . 'icons/campaignmonitor.png';
		$this->_icon_x2  = forminator_addon_campaignmonitor_assets_url() . 'icons/campaignmonitor@2x.png';
		$this->_image    = forminator_addon_campaignmonitor_assets_url() . 'img/campaignmonitor.png';
		$this->_image_x2 = forminator_addon_campaignmonitor_assets_url() . 'img/campaignmonitor@2x.png';
	}

	/**
	 * Get Instance
	 *
	 * @since 1.0 Campaignmonitor Addon
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
	 * @since 1.0 Campaignmonitor Addon
	 *
	 * @return bool
	 */
	public function is_connected() {
		try {
			// check if its active
			if ( ! $this->is_active() ) {
				throw new Forminator_Addon_Campaignmonitor_Exception( __( 'Campaign Monitor is not active', Forminator::DOMAIN ) );
			}

			// if user completed api setup
			$is_connected = $this->is_api_completed();

		} catch ( Forminator_Addon_Campaignmonitor_Exception $e ) {
			$is_connected = false;
		}

		/**
		 * Filter connected status of Campaign Monitor
		 *
		 * @since 1.3
		 *
		 * @param bool $is_connected
		 */
		$is_connected = apply_filters( 'forminator_addon_campaignmonitor_is_connected', $is_connected );

		return $is_connected;
	}

	/**
	 * Check if Campaignmonitor is connected with current form
	 *
	 * @since 1.0 Campaignmonitor Addon
	 *
	 * @param $form_id
	 *
	 * @return bool
	 */
	public function is_form_connected( $form_id ) {
		try {
			$form_settings_instance = null;
			if ( ! $this->is_connected() ) {
				throw new Forminator_Addon_Campaignmonitor_Exception( __( ' Campaign Monitor is not connected', Forminator::DOMAIN ) );
			}

			$form_settings_instance = $this->get_addon_form_settings( $form_id );
			if ( ! $form_settings_instance instanceof Forminator_Addon_Campaignmonitor_Form_Settings ) {
				throw new Forminator_Addon_Campaignmonitor_Exception( __( 'Invalid Form Settings of Campaign Monitor', Forminator::DOMAIN ) );
			}

			// Mark as active when there is at least one active connection
			if ( false === $form_settings_instance->find_one_active_connection() ) {
				throw new Forminator_Addon_Campaignmonitor_Exception( __( 'No active Campaign Monitor connection found in this form', Forminator::DOMAIN ) );
			}

			$is_form_connected = true;

		} catch ( Forminator_Addon_Campaignmonitor_Exception $e ) {
			$is_form_connected = false;
			forminator_addon_maybe_log( __METHOD__, $e->getMessage() );
		}

		/**
		 * Filter connected status of Campaign Monitor with the form
		 *
		 * @since 1.3
		 *
		 * @param bool                                                $is_form_connected
		 * @param int                                                 $form_id                Current Form ID
		 * @param Forminator_Addon_Campaignmonitor_Form_Settings|null $form_settings_instance Instance of form settings, or null when unavailable
		 *
		 */
		$is_form_connected = apply_filters( 'forminator_addon_campaignmonitor_is_form_connected', $is_form_connected, $form_id, $form_settings_instance );

		return $is_form_connected;
	}

	/**
	 * Override settings available,
	 *
	 * @since 1.0 Campaignmonitor Addon
	 * @return bool
	 */
	public function is_settings_available() {
		return true;
	}

	/**
	 * Flag show full log on entries
	 *
	 * @since 1.0 Campaignmonitor Addon
	 * @return bool
	 */
	public static function is_show_full_log() {
		$show_full_log = false;
		if ( defined( 'FORMINATOR_ADDON_CAMPAIGNMONITOR_SHOW_FULL_LOG' ) && FORMINATOR_ADDON_CAMPAIGNMONITOR_SHOW_FULL_LOG ) {
			$show_full_log = true;
		}

		/**
		 * Filter Flag show full log on entries
		 *
		 * @since  1.3
		 *
		 * @params bool $show_full_log
		 */
		$show_full_log = apply_filters( 'forminator_addon_campaignmonitor_show_full_log', $show_full_log );

		return $show_full_log;
	}

	/**
	 * Flag delete subscriber on delete submission
	 *
	 * @since 1.0 Campaignmonitor Addon
	 * @return bool
	 */
	public static function is_enable_delete_subscriber() {
		$delete_subscriber = false;
		if ( defined( 'FORMINATOR_ADDON_CAMPAIGNMONITOR_ENABLE_DELETE_SUBSCRIBER' ) && FORMINATOR_ADDON_CAMPAIGNMONITOR_ENABLE_DELETE_SUBSCRIBER ) {
			$delete_subscriber = true;
		}

		/**
		 * Filter Flag delete subscriber on delete submission
		 *
		 * @since  1.3
		 *
		 * @params bool $delete_subscriber
		 */
		$delete_subscriber = apply_filters( 'forminator_addon_campaignmonitor_enable_delete_subscriber', $delete_subscriber );

		return $delete_subscriber;
	}

	/**
	 * Allow multiple connection on one form
	 *
	 * @since 1.0 Campaignmonitor Addon
	 * @return bool
	 */
	public function is_allow_multi_on_form() {
		return true;
	}

	/**
	 * Setting wizard of Campaign Monitor
	 *
	 * @since 1.0 Campaign Monitor Addon
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
	 * @since 1.0 Campaign Monitor Addon
	 *
	 * @param     $submitted_data
	 * @param int $form_id
	 *
	 * @return array
	 */
	public function setup_api( $submitted_data, $form_id = 0 ) {
		$settings_values = $this->get_settings_values();

		$template         = forminator_addon_campaignmonitor_dir() . 'views/settings/setup-api.php';
		$template_success = forminator_addon_campaignmonitor_dir() . 'views/settings/setup-api-success.php';

		$template_params = array(
			'error_message'   => '',
			'api_key'         => '',
			'client_id'       => '',
			'api_key_error'   => '',
			'client_id_error' => '',
			'client_name'     => '',
		);

		$has_errors   = false;
		$show_success = false;
		$is_submit    = ! empty( $submitted_data );

		foreach ( $template_params as $key => $value ) {
			if ( isset( $submitted_data[ $key ] ) ) {
				$template_params[ $key ] = $submitted_data[ $key ];
			} elseif ( isset( $settings_values[ $key ] ) ) {
				$template_params[ $key ] = $settings_values[ $key ];
			}
		}

		if ( $is_submit ) {
			$api_key   = isset( $submitted_data['api_key'] ) ? $submitted_data['api_key'] : '';
			$client_id = isset( $submitted_data['client_id'] ) ? $submitted_data['client_id'] : '';

			try {
				$api_key = $this->validate_api_key( $api_key );
			} catch ( Forminator_Addon_Campaignmonitor_Exception $e ) {
				$template_params['api_key_error'] = $e->getMessage();
				$has_errors                       = true;
			}

			if ( ! $has_errors ) {
				// validate api
				try {

					$this->validate_api( $api_key );

					$client_details = null;

					if ( ! empty( $client_id ) ) {
						$client_details = $this->validate_client( $api_key, $client_id );
					} else {
						//find first client
						$clients = $this->get_api( $api_key )->get_clients();
						if ( is_array( $clients ) ) {
							if ( isset( $clients[0] ) ) {
								$client = $clients[0];
								if ( isset( $client->ClientID ) ) { //phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
									$client_id      = $client->ClientID; //phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
									$client_details = $this->validate_client( $api_key, $client_id );
								}
							}
						}
					}

					if ( ! isset( $client_details->BasicDetails ) //phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
						|| ! isset( $client_details->BasicDetails->ClientID ) //phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
						|| ! isset( $client_details->BasicDetails->CompanyName ) ) { //phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
						throw new Forminator_Addon_Campaignmonitor_Exception( __( 'Could not find client details, please try again', Forminator::DOMAIN ) );
					}

					$client_name = $client_details->BasicDetails->CompanyName; //phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar

					if ( ! forminator_addon_is_active( $this->_slug ) ) {
						$activated = Forminator_Addon_Loader::get_instance()->activate_addon( $this->_slug );
						if ( ! $activated ) {
							throw new Forminator_Addon_Campaignmonitor_Exception( Forminator_Addon_Loader::get_instance()->get_last_error_message() );
						}
					}

					$settings_values = array(
						'api_key'     => $api_key,
						'client_id'   => $client_id,
						'client_name' => $client_name,
					);
					$this->save_settings_values( $settings_values );

					// no form_id its on global settings
					if ( empty( $form_id ) ) {
						$show_success = true;
					}
				} catch ( Forminator_Addon_Campaignmonitor_Exception $e ) {
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

	/**
	 * Check Api settings Completed
	 *
	 * @since 1.o Campaign Monitor
	 * @return bool
	 */
	public function is_api_completed() {
		$setting_values = $this->get_settings_values();

		// check api_key set up
		return ( isset( $setting_values['api_key'] ) && ! empty( $setting_values['api_key'] ) );
	}

	/**
	 * Get API Instance
	 *
	 * @since 1.0 Campaign Monitor Addon
	 *
	 * @param null $api_key
	 *
	 * @return Forminator_Addon_Campaignmonitor_Wp_Api
	 * @throws Forminator_Addon_Campaignmonitor_Wp_Api_Exception
	 */
	public function get_api( $api_key = null ) {
		if ( is_null( self::$api ) ) {
			if ( is_null( $api_key ) ) {
				$setting_values = $this->get_settings_values();
				$api_key        = '';
				if ( isset( $setting_values['api_key'] ) ) {
					$api_key = $setting_values['api_key'];
				}
			}
			$api       = Forminator_Addon_Campaignmonitor_Wp_Api::get_instance( $api_key );
			self::$api = $api;
		}

		return self::$api;
	}

	/**
	 * Validate API Key
	 *
	 * @since 1.0 Campaign Monitor
	 *
	 * @param string $api_key
	 *
	 * @return string
	 * @throws Forminator_Addon_Campaignmonitor_Exception
	 */
	public function validate_api_key( $api_key ) {
		if ( empty( $api_key ) ) {
			throw new Forminator_Addon_Campaignmonitor_Exception( __( 'Please put a valid Campaign Monitor API Key', Forminator::DOMAIN ) );
		}

		return $api_key;
	}

	/**
	 * Validate API
	 *
	 * @since 1.0 Campaign Monitor Addon
	 *
	 * @param $api_key
	 *
	 * @throws Forminator_Addon_Campaignmonitor_Wp_Api_Exception
	 * @throws Forminator_Addon_Campaignmonitor_Exception
	 */
	public function validate_api( $api_key ) {
		self::$api = null;
		$api       = $this->get_api( $api_key );

		$system_date = $api->get_system_date();

		if ( ! isset( $system_date->SystemDate ) || empty( $system_date->SystemDate ) ) { //phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
			throw new Forminator_Addon_Campaignmonitor_Exception( __( 'Failed to validate API Key.', Forminator::DOMAIN ) );
		}
	}

	/**
	 * Validate Client
	 *
	 * @since 1.0 Campaign Monitor Addon
	 *
	 * @param $api_key
	 * @param $client_id
	 *
	 * @return array|mixed|object
	 * @throws Forminator_Addon_Campaignmonitor_Exception
	 * @throws Forminator_Addon_Campaignmonitor_Wp_Api_Exception
	 * @throws Forminator_Addon_Campaignmonitor_Wp_Api_Not_Found_Exception
	 */
	public function validate_client( $api_key, $client_id ) {
		self::$api = null;
		$api       = $this->get_api( $api_key );

		$client_details = $api->get_client( $client_id );

		return $client_details;

	}

	/**
	 * Get Client ID
	 *
	 * @since 1.0 Campaign Monitor Addon
	 * @return string
	 */
	public function get_client_id() {
		$settings_values = $this->get_settings_values();
		$client_id       = '';
		if ( isset( $settings_values ['client_id'] ) ) {
			$client_id = $settings_values ['client_id'];
		}

		/**
		 * Filter Campaign Monitor client id used
		 *
		 * @since 1.3
		 *
		 * @param string $client_id
		 */
		$client_id = apply_filters( 'forminator_addon_campaignmonitor_client_id', $client_id );

		return $client_id;
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
