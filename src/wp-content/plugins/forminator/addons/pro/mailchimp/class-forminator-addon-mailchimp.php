<?php
/** @noinspection HtmlUnknownTarget */

require_once dirname( __FILE__ ) . '/class-forminator-addon-mailchimp-exception.php';
require_once dirname( __FILE__ ) . '/lib/class-forminator-addon-mailchimp-wp-api.php';

/**
 * Class Forminator_Addon_Mailchimp
 * The class that defines mailchimp addon
 *
 * @since 1.0 Mailchimp Addon
 */
class Forminator_Addon_Mailchimp extends Forminator_Addon_Abstract {

	/**
	 * Mailchimp Addon Instance
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @var self|null
	 */
	private static $_instance = null;

	/**
	 * Mailchimp API instance
	 *
	 * @since 1.0 Mailchimp Addon
	 * @var null
	 */
	private static $api = null;

	/**
	 * @since 1.0 Mailchimp Addon
	 * @var string
	 */
	protected $_slug = 'mailchimp';

	/**
	 * @since 1.0 Mailchimp Addon
	 * @var string
	 */
	protected $_version = FORMINATOR_ADDON_MAILCHIMP_VERSION;

	/**
	 * @since 1.0 Mailchimp Addon
	 * @var string
	 */
	protected $_min_forminator_version = '1.1';

	/**
	 * @since 1.0 Mailchimp Addon
	 * @var string
	 */
	protected $_short_title = 'Mailchimp';

	/**
	 * @since 1.0 Mailchimp Addon
	 * @var string
	 */
	protected $_title = 'Mailchimp';

	/**
	 * @since 1.0 Mailchimp Addon
	 * @var string
	 */
	protected $_url = 'https://premium.wpmudev.org';

	/**
	 * @since 1.0 Mailchimp Addon
	 * @var string
	 */
	protected $_full_path = __FILE__;

	/**
	 * Class name of form settings
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @var string
	 */
	protected $_form_settings = 'Forminator_Addon_Mailchimp_Form_Settings';

	/**
	 * Class name of form hooks
	 *
	 * @since 1.0 Mailchimp Addon
	 * @var string
	 */
	protected $_form_hooks = 'Forminator_Addon_Mailchimp_Form_Hooks';

	/**
	 * Hold account information that currently connected
	 * Will be saved to @see Forminator_Addon_Mailchimp::save_settings_values()
	 *
	 * @since 1.0 Mailchimp Addon
	 * @var array
	 */
	private $_connected_account = array();

	protected $_position = 2;

	/**
	 * Forminator_Addon_Mailchimp constructor.
	 * - Set dynamic translatable text(s) that will be displayed to end-user
	 * - Set dynamic icons and images
	 *
	 * @since 1.0 Mailchimp Addon
	 */
	public function __construct() {
		// late init to allow translation
		$this->_description                = __( 'Make form data as Mailchimp List', Forminator::DOMAIN );
		$this->_activation_error_message   = __( 'Sorry but we failed to activate Mailchimp Integration, don\'t hesitate to contact us', Forminator::DOMAIN );
		$this->_deactivation_error_message = __( 'Sorry but we failed to deactivate Mailchimp Integration, plese try again', Forminator::DOMAIN );

		$this->_update_settings_error_message = __(
			'Sorry, we failed to update settings, please check your form and try again',
			Forminator::DOMAIN
		);

		$this->_icon     = forminator_addon_mailchimp_assets_url() . 'icons/mailchimp.png';
		$this->_icon_x2  = forminator_addon_mailchimp_assets_url() . 'icons/mailchimp@2x.png';
		$this->_image    = forminator_addon_mailchimp_assets_url() . 'img/mailchimp.png';
		$this->_image_x2 = forminator_addon_mailchimp_assets_url() . 'img/mailchimp@2x.png';
	}

	/**
	 * Get addon instance
	 *
	 * @since 1.0 Mailchimp Addon
	 * @return self|null
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Hook before save settings values
	 * to include @see Forminator_Addon_Mailchimp::$_connected_account
	 * for future reference
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param array $values
	 *
	 * @return array
	 */
	public function before_save_settings_values( $values ) {
		forminator_addon_maybe_log( __METHOD__, $values );

		if ( ! empty( $this->_connected_account ) ) {
			$values['connected_account'] = $this->_connected_account;
		}

		return $values;
	}

	/**
	 * Flag for check whether mailchimp addon is connected globally
	 *
	 * @since 1.0 Mailchimp Addon
	 * @return bool
	 */
	public function is_connected() {
		try {
			// check if its active
			if ( ! $this->is_active() ) {
				throw new Forminator_Addon_Mailchimp_Exception( __( 'Mailchimp is not active', Forminator::DOMAIN ) );
			}

			// if user completed settings
			$is_connected = $this->settings_is_complete();

		} catch ( Forminator_Addon_Mailchimp_Exception $e ) {
			$is_connected = false;
		}

		/**
		 * Filter connected status of mailchimp
		 *
		 * @since 1.1
		 *
		 * @param bool $is_connected
		 */
		$is_connected = apply_filters( 'forminator_addon_mailchimp_is_connected', $is_connected );

		return $is_connected;
	}

	/**
	 * Check if user already completed settings
	 *
	 * @since 1.0 Mailchimp Addon
	 * @return bool
	 */
	private function settings_is_complete() {
		$setting_values = $this->get_settings_values();

		// check api_key and connected_account exists and not empty
		return isset( $setting_values['api_key'] ) && $setting_values['api_key'] && isset( $setting_values['connected_account'] ) && ! empty( $setting_values['connected_account'] );
	}

	/**
	 * Flag for check if and addon connected to a form
	 * by default it will check if last step of form settings already completed by user
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param $form_id
	 *
	 * @return bool
	 */
	public function is_form_connected( $form_id ) {

		try {
			// initialize with null
			$form_settings_instance = null;
			if ( ! $this->is_connected() ) {
				throw new Forminator_Addon_Mailchimp_Exception( __( 'Mailchimp addon not connected.', Forminator::DOMAIN ) );
			}

			$form_settings_instance = $this->get_addon_form_settings( $form_id );
			if ( ! $form_settings_instance instanceof Forminator_Addon_Mailchimp_Form_Settings ) {
				throw new Forminator_Addon_Mailchimp_Exception( __( 'Form settings instance is not valid Forminator_Addon_Mailchimp_Form_Settings.', Forminator::DOMAIN ) );
			}
			$wizards = $form_settings_instance->form_settings_wizards();
			//last step is completed
			$last_step             = end( $wizards );
			$last_step_is_complete = call_user_func( $last_step['is_completed'] );
			if ( ! $last_step_is_complete ) {
				throw new Forminator_Addon_Mailchimp_Exception( __( 'Form settings is not yet completed.', Forminator::DOMAIN ) );
			}

			$is_form_connected = true;
		} catch ( Forminator_Addon_Mailchimp_Exception $e ) {
			$is_form_connected = false;

			forminator_addon_maybe_log( __METHOD__, $e->getMessage() );
		}

		/**
		 * Filter connected status of mailchimp with the form
		 *
		 * @since 1.1
		 *
		 * @param bool                                          $is_form_connected
		 * @param int                                           $form_id                Current Form ID
		 * @param Forminator_Addon_Mailchimp_Form_Settings|null $form_settings_instance Instance of form settings, or null when unavailable
		 *
		 */
		$is_form_connected = apply_filters( 'forminator_addon_mailchimp_is_form_connected', $is_form_connected, $form_id, $form_settings_instance );

		return $is_form_connected;

	}

	/**
	 * Return with true / false, you may update you setting update message too
	 *
	 * @see   _update_settings_error_message
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param $api_key
	 *
	 * @return bool
	 */
	protected function validate_api_key( $api_key ) {
		if ( empty( $api_key ) ) {
			$this->_update_settings_error_message = __( 'Please add valid Mailchimp API Key.', Forminator::DOMAIN );

			return false;
		}

		try {
			// Check API Key by validating it on get_info request
			$info = $this->get_api( $api_key )->get_info();
			forminator_addon_maybe_log( __METHOD__, $info );

			$this->_connected_account = array(
				'account_id'   => $info->account_id,
				'account_name' => $info->account_name,
				'email'        => $info->email,
			);

		} catch ( Forminator_Addon_Mailchimp_Wp_Api_Exception $e ) {
			$this->_update_settings_error_message = $e->getMessage();

			return false;
		}

		return true;
	}

	/**
	 * Get API Instance
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param null $api_key
	 *
	 * @return Forminator_Addon_Mailchimp_Wp_Api|null
	 * @throws Forminator_Addon_Mailchimp_Wp_Api_Exception
	 */
	public function get_api( $api_key = null ) {
		if ( is_null( self::$api ) ) {
			if ( is_null( $api_key ) ) {
				$api_key = $this->get_api_key();
			}
			$api       = Forminator_Addon_Mailchimp_Wp_Api::get_instance( $api_key );
			self::$api = $api;
		}

		return self::$api;
	}

	/**
	 * Get currently saved api key
	 *
	 * @since 1.0 Mailchimp Addon
	 * @return string|null
	 */
	private function get_api_key() {
		/** @var array $setting_values */
		$setting_values = $this->get_settings_values();
		if ( isset( $setting_values['api_key'] ) ) {
			return $setting_values['api_key'];
		}

		return null;
	}

	/**
	 * Build settings help on settings
	 *
	 * @since 1.0 Mailchimp Addon
	 * @return string
	 */
	public function settings_help() {

		// Display how to get mailchimp API Key by default
		/* translators:  placeholder is URL to get API Key of MailChimp */
		$help = sprintf( __( 'Please get your Mailchimp API key %1$s', Forminator::DOMAIN ), '<a href="https://admin.mailchimp.com/account/api-key-popup" target="_blank">here</a>' );

		$help = '<span class="sui-description" style="margin-top: 20px;">' . $help . '</span>';

		$setting_values = $this->get_settings_values();

		if (
			isset( $setting_values['api_key'] )
			&& $setting_values['api_key']
			&& isset( $setting_values['connected_account'] )
			&& ! empty( $setting_values['connected_account'] )
		) {

			$connected_account = $setting_values['connected_account'];

			// Show currently connected mailchimp account if its already connected
			/* translators:  placeholder is Name and Email of Connected MailChimp Account */
			$help = '<span class="sui-description" style="margin-top: 20px;">' . __( 'Change your API Key or disconnect this Mailchimp Integration below.' ) . '</span>';

		}

		return $help;

	}

	public function settings_description() {

		$description = '';

		/** @var array $setting_values */
		$setting_values = $this->get_settings_values();

		if (
			isset( $setting_values['api_key'] )
			&& $setting_values['api_key']
			&& isset( $setting_values['connected_account'] )
			&& ! empty( $setting_values['connected_account'] )
		) {

			// Show currently connected mailchimp account if its already connected
			/* translators:  placeholder is Name and Email of Connected MailChimp Account */
			$description .= '<span class="sui-description">' . esc_html__( 'Please take a note that changing API Key or disconnect will affect to ALL of your connected forms.', Forminator::DOMAIN ) . '</span>';

		}

		return $description;

	}

	public function settings_account() {

		$myaccount = '';

		/** @var array $setting_values */
		$setting_values = $this->get_settings_values();

		if (
			isset( $setting_values['api_key'] )
			&& $setting_values['api_key']
			&& isset( $setting_values['connected_account'] )
			&& ! empty( $setting_values['connected_account'] )
		) {

			$connected_account = $setting_values['connected_account'];

			// Show currently connected mailchimp account if its already connected
			$myaccount .= sprintf(
				/* translators:  placeholder is Name and Email of Connected MailChimp Account */
				__( 'Your Mailchimp is connected to %1$s: %2$s.', Forminator::DOMAIN ),
				'<strong>' . $connected_account['account_name'] . '</strong>',
				$connected_account['email']
			);

			$myaccount = '<div class="sui-notice sui-notice-info">
				<p>' . $myaccount . '</p>
			</div>';

		}

		return $myaccount;

	}

	/**
	 * Flag to show full log on entries
	 * By default API request(s) are not shown on submissions page
	 * set @see FORMINATOR_ADDON_MAILCHIMP_SHOW_FULL_LOG to `true` on wp-config.php to show it
	 *
	 * @since 1.0 Mailchimp Addon
	 * @return bool
	 */
	public static function is_show_full_log() {
		if ( defined( 'FORMINATOR_ADDON_MAILCHIMP_SHOW_FULL_LOG' ) && FORMINATOR_ADDON_MAILCHIMP_SHOW_FULL_LOG ) {
			return true;
		}

		return false;
	}

	/**
	 * Flag if delete member on delete entry enabled
	 *
	 * Default is `true`,
	 * which can be changed via `FORMINATOR_ADDON_MAILCHIMP_ENABLE_DELETE_MEMBER` constant
	 *
	 * @return bool
	 */
	public static function is_enable_delete_member() {
		if ( defined( 'FORMINATOR_ADDON_MAILCHIMP_ENABLE_DELETE_MEMBER' ) && FORMINATOR_ADDON_MAILCHIMP_ENABLE_DELETE_MEMBER ) {
			return true;
		}

		return false;
	}

	/**
	 * Flag to show full if GDPR feature enabled
	 * GDPR is experimental feature on 1.0 version of this mailchimp addon
	 * And disabled by default to enable it set @see FORMINATOR_ADDON_MAILCHIMP_ENABLE_GDPR to true in wp-config.php
	 * Please bear in mind that currently its experimental, means not properly and thoroughly tested
	 *
	 * @since 1.0 Mailchimp Addon
	 * @return bool
	 */
	public static function is_enable_gdpr() {
		if ( defined( 'FORMINATOR_ADDON_MAILCHIMP_ENABLE_GDPR' ) && FORMINATOR_ADDON_MAILCHIMP_ENABLE_GDPR ) {
			return true;
		}

		return false;
	}

	/**
	 * Settings wizard
	 *
	 * @since 1.0 Mailchimp Addon
	 * @return array
	 */
	public function settings_wizards() {
		return array(
			array(
				'callback'     => array( $this, 'configure_api_key' ),
				'is_completed' => array( $this, 'settings_is_complete' ),
			),
		);
	}

	/**
	 * Wizard of configure_api_key
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param     $submitted_data
	 * @param int $form_id
	 *
	 * @return array
	 */
	public function configure_api_key( $submitted_data, $form_id = 0 ) {
		$error_message         = '';
		$api_key_error_message = '';
		$api_key               = $this->get_api_key();

		// ON Submit
		if ( isset( $submitted_data['api_key'] ) ) {
			$api_key           = $submitted_data['api_key'];
			$api_key_validated = $this->validate_api_key( $api_key );

			/**
			 * Filter validating api key result
			 *
			 * @since 1.1
			 *
			 * @param bool   $api_key_validated
			 * @param string $api_key API Key to be validated
			 */
			$api_key_validated = apply_filters( 'forminator_addon_mailchimp_validate_api_key', $api_key_validated, $api_key );

			if ( ! $api_key_validated ) {
				$api_key_error_message = $this->_update_settings_error_message;
			} else {
				$show_success = true;
				if ( ! forminator_addon_is_active( $this->_slug ) ) {
					$activated = Forminator_Addon_Loader::get_instance()->activate_addon( $this->_slug );
					if ( ! $activated ) {
						$error_message = '<div class="sui-notice sui-notice-error"><p>' . Forminator_Addon_Loader::get_instance()->get_last_error_message() . '</p></div>';
						$show_success  = false;
					} else {
						$this->save_settings_values( array( 'api_key' => $api_key ) );
					}
				} else {
					$this->save_settings_values( array( 'api_key' => $api_key ) );
				}

				if ( $show_success ) {
					if ( ! empty( $form_id ) ) {
						// initiate form settings wizard
						return $this->get_form_settings_wizard( array(), $form_id, 0, 0 );
					}

					return array(
						'html'         => '<div class="integration-header"><h3 class="sui-box-title" id="dialogTitle2">' .
										/* translators: ... */
										sprintf( __( '%1$s Added', Forminator::DOMAIN ), 'Mailchimp' ) .
										'</h3></div>
						<div class="sui-block-content-center">
							<p><small style="color: #666;">' . __( 'You can now go to your forms and assign them to this integration.' ) . '</small></p>
						</div>',
						'buttons'      => array(
							'close' => array(
								'markup' => self::get_button_markup( esc_html__( 'Close', Forminator::DOMAIN ), 'forminator-addon-close' ),
							),
						),
						'redirect'     => false,
						'has_errors'   => false,
						'notification' => array(
							'type' => 'success',
							'text' => '<strong>' . $this->get_title() . '</strong> ' . __( 'is connected successfully.' ),
						),
					);
				}
			}
		}

		$buttons = array();

		$is_edit = false;
		if ( $this->is_connected() ) {
			$is_edit = true;
		}

		if ( $is_edit ) {
			$buttons['disconnect'] = array(
				'markup' => self::get_button_markup( esc_html__( 'Disconnect', Forminator::DOMAIN ), 'sui-button-ghost forminator-addon-disconnect' ),
			);

			$buttons['submit'] = array(
				'markup' => '<div class="sui-actions-right">' .
							self::get_button_markup( esc_html__( 'Save', Forminator::DOMAIN ), 'forminator-addon-connect' ) .
							'</div>',
			);
		} else {
			$buttons['submit'] = array(
				'markup' => self::get_button_markup( esc_html__( 'Connect', Forminator::DOMAIN ), 'forminator-addon-connect' ),
			);
		}

		return array(
			'html'       => '<div class="integration-header">
					<h3 class="sui-box-title" id="dialogTitle2">' .
							/* translators: ... */
							sprintf( __( 'Configure %1$s', Forminator::DOMAIN ), 'Mailchimp' ) .
							'</h3>
					' . $this->settings_help() . '
					' . $error_message . '
				</div>
				<form>
					<div class="sui-form-field ' . ( ! empty( $api_key_error_message ) ? 'sui-form-field-error' : '' ) . '">
						<label class="sui-label">' . __( 'API Key', Forminator::DOMAIN ) . '</label>
						<div class="sui-control-with-icon">
							<input name="api_key"
								placeholder="' .
							/* translators: ... */
							sprintf( __( 'Enter %1$s API Key', Forminator::DOMAIN ), 'Mailchimp' ) .
							'"
								value="' . esc_attr( $api_key ) . '"
								class="sui-form-control" />
							<i class="sui-icon-key" aria-hidden="true"></i>
						</div>
						' . ( ! empty( $api_key_error_message ) ? '<span class="sui-error-message">' . esc_html( $api_key_error_message ) . '</span>' : '' ) . '
						' . $this->settings_description() . '
					</div>
				</form>
				' . $this->settings_account(),
			'buttons'    => $buttons,
			'redirect'   => false,
			'has_errors' => ! empty( $error_message ) || ! empty( $api_key_error_message ),
		);
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
