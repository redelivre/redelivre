<?php


/**
 * Class Forminator_Addon_Form_Settings_Abstract
 * Any change(s) to this file is subject to:
 * - Properly Written DocBlock! (what is this, why is that, how to be like those, etc, as long as you want!)
 * - Properly Written Changelog!
 *
 * @since 1.1
 */
abstract class Forminator_Addon_Form_Settings_Abstract {

	/**
	 * Current Form ID
	 *
	 * @since 1.1
	 * @var int
	 */
	protected $form_id;

	/**
	 * Current Form Settings
	 *
	 * @since 1.1
	 * @var array
	 */
	protected $form_settings = array();

	/**
	 * Current Form Fields
	 *
	 * @var array
	 */
	protected $form_fields = array();

	/**
	 * Form settings for addon
	 *
	 * @since 1.1
	 * @var array
	 */
	protected $addon_form_settings = array();

	/*********************************** Errors Messages ********************************/
	/**
	 * These error message can be set on the start of addon as default, or dynamically set on each related process
	 *
	 * @example $_activation_error_message can be dynamically set on activate() to display custom error messages when activatation failed
	 *          Default is empty, which will be replaced by forminator default messages
	 *
	 */

	/**
	 * Error Message on update form settings
	 *
	 * @since 1.1
	 * @var string
	 */
	protected $_update_form_settings_error_message = '';
	/*********************************** END Errors Messages ********************************/

	/**
	 * Addon instance
	 *
	 * @since 1.1
	 * @var Forminator_Addon_Abstract
	 */
	protected $addon;


	/**
	 * An addon can be force disconnected from form, if its not met requirement, or data changed externally
	 * example :
	 *  - Mail List deleted on mailchimp server app
	 *  - Fields removed
	 *
	 * @since 1.1
	 * @var bool
	 */
	protected $is_force_form_disconnected = false;

	/**
	 * Reason of Force disonnected
	 *
	 * @since 1.1
	 * @var string
	 */
	protected $force_form_disconnected_reason = '';

	/**
	 * Forminator_Addon_Form_Settings_Abstract constructor.
	 *
	 * @since 1.1
	 *
	 * @param Forminator_Addon_Abstract $addon
	 * @param                           $form_id
	 *
	 * @throws Forminator_Addon_Exception
	 */
	public function __construct( Forminator_Addon_Abstract $addon, $form_id ) {
		$this->addon   = $addon;
		$this->form_id = $form_id;
		$custom_form   = Forminator_Custom_Form_Model::model()->load( $this->form_id );
		if ( ! $custom_form ) {
			/* translators: ... */
			throw new Forminator_Addon_Exception( sprintf( __( 'Form with id %d could not be found', Forminator::DOMAIN ), $this->form_id ) );
		}
		$this->form_fields   = forminator_addon_format_form_fields( $custom_form );
		$this->form_settings = forminator_addon_format_form_settings( $custom_form );
	}


	/**
	 * Meta key that will be used to save addon form setting on WP post_meta
	 *
	 * @since 1.1
	 * @return string
	 */
	final public function get_form_settings_meta_key() {
		return 'forminator_addon_' . $this->addon->get_slug() . '_form_settings';
	}

	/**
	 * Update form settings error Message
	 *
	 * @since 1.1
	 * @return string
	 */
	public function get_update_form_settings_error_message() {
		return $this->_update_form_settings_error_message;
	}

	/**
	 * Override this function if addon need to do something with addon form setting values
	 *
	 * @example transform, load from other storage ?
	 * called when rendering tab on form settings
	 *
	 * @since   1.1
	 *
	 * @param $values
	 *
	 * @return mixed
	 */
	public function before_get_form_settings_values( $values ) {
		return $values;
	}

	/**
	 * Get Form settings value
	 * its already hooked with
	 *
	 * @see   before_get_settings_values
	 *
	 * @since 1.1
	 *
	 * @return array
	 */
	final public function get_form_settings_values() {
		// get single meta key
		$values = get_post_meta( $this->form_id, $this->get_form_settings_meta_key(), true );

		if ( ! $values ) {
			$values = array();
		}

		$addon_slug = $this->addon->get_slug();
		$form_id    = $this->form_id;

		/**
		 * Filter retrived form settings data from db
		 *
		 * @since 1.1
		 *
		 * @param mixed $values
		 * @param int   $form_id current form id
		 */
		$values = apply_filters( 'forminator_addon_' . $addon_slug . '_get_form_settings_values', $values, $form_id );

		return $values;
	}

	/**
	 * Save form settings value
	 * its already hooked with
	 *
	 * @see   before_save_form_settings_values
	 * @since 1.1
	 *
	 * @param $values
	 */
	final public function save_form_settings_values( $values ) {
		$addon_slug = $this->addon->get_slug();
		$form_id    = $this->form_id;

		/**
		 * Filter form settings data to be save to db
		 *
		 * @since 1.1
		 *
		 * @param mixed $values  current form settings values
		 * @param int   $form_id current form id
		 */
		$values = apply_filters( 'forminator_addon_' . $addon_slug . '_save_form_settings_values', $values, $form_id );
		update_post_meta( $this->form_id, $this->get_form_settings_meta_key(), $values );
	}

	/**
	 * Override this function if addon need to do something with addon form setting values
	 *
	 * @example transform, load from other storage ?
	 * called when rendering tab on form settings
	 * @since   1.1
	 *
	 * @param $values
	 *
	 * @return mixed
	 */
	public function before_save_form_settings_values( $values ) {
		return $values;
	}

	/**
	 * Get status of force disconnected from WP post_meta
	 *
	 * @since 1.1
	 * @return bool
	 */
	final public function is_force_form_disconnected() {
		$disconnected = get_post_meta( $this->form_id, 'forminator_addon_' . $this->addon->get_slug() . '_form_disconnect', true );

		if ( ! empty( $disconnected ) && isset( $disconnected['disconnect'] ) && $disconnected['disconnect'] ) {
			$this->is_force_form_disconnected     = true;
			$this->force_form_disconnected_reason = $disconnected['disconnnect_reason'];
		}

		return $this->is_force_form_disconnected;
	}

	/**
	 * Get disconnected reason
	 *
	 * @since 1.1
	 * @return string
	 */
	final public function force_form_disconnected_reason() {
		return $this->force_form_disconnected_reason;
	}

	/**
	 * Force form to be disconnected with addon
	 *
	 * @since 1.1
	 *
	 * @param $reason
	 */
	final public function force_form_disconnect( $reason ) {
		$this->is_force_form_disconnected     = true;
		$this->force_form_disconnected_reason = $reason;

		$this->addon_form_settings = array();

		$this->save_form_settings_values( $this->addon_form_settings );

	}

	/**
	 * Save disconnect reason to WP post_meta
	 *
	 * @since 1.1
	 */
	final public function save_force_form_disconnect_reason() {
		if ( $this->is_force_form_disconnected ) {
			update_post_meta(
				$this->form_id,
				'forminator_addon_' . $this->addon->get_slug() . '_form_disconnect',
				array(
					'disconnect'         => true,
					'disconnnect_reason' => $this->force_form_disconnected_reason,
				)
			);
		}
	}

	/**
	 * Remove disconnect reason form WP post_meta
	 *
	 * @since 1.1
	 */
	final public function remove_saved_force_form_disconnect_reason() {
		delete_post_meta( $this->form_id, 'forminator_addon_' . $this->addon->get_slug() . '_form_disconnect' );
	}

	/**
	 * Get current form settings
	 *
	 * @since 1.1
	 * @return array
	 */
	final public function get_form_settings() {
		return $this->form_settings;
	}

	/**
	 * Get current form fields
	 *
	 * @since 1.1
	 * @return array
	 */
	final public function get_form_fields() {
		return $this->form_fields;
	}

	/**
	 * Override this function to set wizardable settings
	 * Default its and empty array which is indicating that Addon doesnt have settings
	 *
	 * Its multi array, with numerical key, start with `0`
	 * Every step on wizard, will consist at least
	 * - `callback` : when application requesting wizard, Forminator will do `call_user_func` on this value, with these arguments
	 *      - `$submitted_data` : array of submitted data POST-ed by user
	 *      - `$form_id` : current form_id when called on `Form Settings` or 0 when called on Global Settings
	 * - `is_completed` : when application requesting wizard, will check if `Previous Step` `is_completed` by doing `call_user_func` on its value
	 *      this function should return `true` or `false`
	 *
	 * @since 1.1
	 * @return array
	 */
	public function form_settings_wizards() {
		// What this function return should looks like
		$steps = array(
			// First Step / step `0`
			array(
				/**
				 * Value of `callback` will be passed as first argument of `call_user_func`
				 * it does not have to be passed `$this` as reference such as `array( $this, 'sample_setting_first_step' )`,
				 * But its encouraged to passed `$this` because you will be benefited with $this class instance, in case you need to call private function or variable inside it
				 * you can make the value to be `some_function_name` as long `some_function_name` as long it will globally callable which will be checked with `is_callable`
				 * and should be able to accept 2 arguments $submitted_data, $form_id
				 *
				 * This callback should return an array @see Forminator_Addon_Abstract::sample_setting_first_step()
				 *
				 * @see Forminator_Addon_Abstract::sample_setting_first_step()
				 *
				 */
				'callback'     => array( $this, 'sample_setting_first_step' ),
				/**
				 * Before Forminator call the `calback`, Forminator will attempt to run `is_completed` from the previous step
				 * In this case, `is_completed` will be called when Forminator trying to display Settings Wizard for Second Step / step `1`
				 * Like `callback` its value will be passed as first argument of `call_user_func`
				 * and no arguments passed to this function when its called
				 *
				 * @see Forminator_Addon_Abstract::sample_setting_first_step_is_completed()
				 */
				'is_completed' => array( $this, 'sample_setting_first_step_is_completed' ),
			),
		);

		return array();
	}

	/**
	 * Disconnect Form with this addon
	 * Override when needed
	 *
	 * @since 1.1
	 *
	 * @param array $submitted_data
	 */
	public function disconnect_form( $submitted_data ) {
		$this->save_form_settings_values( array() );
	}

	/**
	 * Override this function to retrieve your multiple ids on form settings
	 * Default is the array keys as id and label of form_settins_values
	 *
	 * @return array
	 */
	public function get_multi_ids() {
		$multi_ids = array();
		foreach ( $this->get_form_settings_values() as $key => $value ) {
			$multi_ids[] = array(
				'id'    => $key,
				'label' => $key,
			);
		}

		return $multi_ids;
	}

	/**
	 * Force Close Wizard with message
	 *
	 * @since 1.2
	 *
	 * @param $message
	 *
	 * @return array
	 */
	public function get_force_closed_wizard( $message ) {
		return array(
			'html'         => '',
			'buttons'      => '',
			'is_close'     => true,
			'redirect'     => false,
			'has_errors'   => false,
			'has_back'     => false,
			'notification' => array(
				'type' => 'error',
				'text' => '<strong>' . $this->addon->get_title() . '</strong> ' . $message,
			),
		);
	}

	/**
	 * Append multi settings or replace multi settings
	 *
	 * @since 1.2
	 *
	 * @param      $multi_id
	 * @param      $settings
	 * @param bool $replace
	 */
	public function save_multi_id_form_setting_values( $multi_id, $settings, $replace = false ) {
		$this->addon_form_settings = $this->get_form_settings_values();

		// merge old values if not replace
		if ( isset( $this->addon_form_settings[ $multi_id ] ) && ! $replace ) {
			$current_settings = $this->addon_form_settings[ $multi_id ];
			$settings         = array_merge( $current_settings, $settings );
		}

		$this->addon_form_settings = array_merge(
			$this->addon_form_settings,
			array(
				$multi_id => $settings,
			)
		);
		$this->save_form_settings_values( $this->addon_form_settings );
	}

	/**
	 * Check if multi_id form settings values completed
	 *
	 * Override when needed
	 *
	 * @since 1.2
	 *
	 * @param $multi_id
	 *
	 * @return bool
	 */
	public function is_multi_form_settings_complete( $multi_id ) {
		return false;
	}

	/**
	 * Get multi Setting value of multi_id
	 *
	 * Override when needed
	 *
	 * @since 1.2
	 *
	 * @param        $multi_id
	 * @param        $key
	 * @param mixed  $default
	 *
	 * @return mixed|string
	 */
	public function get_multi_id_form_settings_value( $multi_id, $key, $default = '' ) {
		$this->addon_form_settings = $this->get_form_settings_values();
		if ( isset( $this->addon_form_settings[ $multi_id ] ) ) {
			$multi_settings = $this->addon_form_settings[ $multi_id ];
			if ( isset( $multi_settings[ $key ] ) ) {
				return $multi_settings[ $key ];
			}

			return $default;
		}

		return $default;
	}

	/**
	 * Find one active connection on current form
	 *
	 * Override when needed
	 *
	 * @since 1.2
	 *
	 * @return bool|array false on no connection, or settings on available
	 */
	public function find_one_active_connection() {
		$addon_form_settings = $this->get_form_settings_values();

		foreach ( $addon_form_settings as $multi_id => $addon_form_setting ) {
			if ( true === $this->is_multi_form_settings_complete( $multi_id ) ) {
				return $addon_form_setting;
			}
		}

		return false;
	}

	/**
	 * Override this function to generate your multiple id on form settings
	 * Default is the uniqid
	 *
	 * @since 1.2
	 * @return string
	 */
	public function generate_multi_id() {
		return uniqid( '', true );
	}

	/**
	 * Get form settings data to export
	 *
	 * Default is from post_meta, override when needed
	 *
	 * @since 1.4
	 *
	 * @return array
	 */
	public function to_exportable_data() {
		$addon_slug    = $this->addon->get_slug();
		$form_settings = $this->get_form_settings_values();
		if ( empty( $form_settings ) ) {
			$exportable_data = array();
		} else {
			$exportable_data = array(
				'form_settings' => $form_settings,
				'version'       => $this->addon->get_version(),
			);
		}

		$form_id = $this->form_id;

		/**
		 * Filter Form settings that will be exported when requested
		 *
		 * @since 1.4
		 *
		 * @param array $exportable_data
		 * @param int   $form_id
		 */
		$exportable_data = apply_filters( "forminator_addon_{$addon_slug}_form_settings_to_exportable_data", $exportable_data, $form_id );

		return $exportable_data;
	}

	/**
	 * Executed when form settings imported
	 *
	 * default is save imported data to post_meta, override when needed
	 *
	 * @since 1.4
	 *
	 * @param $import_data
	 */
	public function import_data( $import_data ) {
		$addon_slug = $this->addon->get_slug();
		$form_id    = $this->form_id;

		$import_data = apply_filters( "forminator_addon_{$addon_slug}_form_settings_import_data", $import_data, $form_id );

		/**
		 * Executed when importing form settings of this addon
		 *
		 * @since 1.4
		 *
		 * @param array                                        $exportable_data
		 * @param int                                          $form_id
		 * @param Forminator_Addon_Form_Settings_Abstract|null $form_settings_instance
		 */
		do_action( "forminator_addon_{$addon_slug}_on_import_form_settings_data", $form_id, $import_data );

		try {
			// pre-basic-validation
			if ( empty( $import_data ) ) {
				throw new Forminator_Addon_Exception( 'import_data_empty' );
			}

			if ( ! isset( $import_data['form_settings'] ) ) {
				throw new Forminator_Addon_Exception( 'import_data_no_form_settings' );
			}

			if ( empty( $import_data['form_settings'] ) ) {
				throw new Forminator_Addon_Exception( 'import_data_form_settings_empty' );
			}

			if ( ! isset( $import_data['version'] ) ) {
				throw new Forminator_Addon_Exception( 'import_data_no_version' );
			}
			$this->save_form_settings_values( $import_data['form_settings'] );

		} catch ( Forminator_Addon_Exception $e ) {
			forminator_addon_maybe_log( $e->getMessage() );
			//do nothing
		}

	}

	/**
	 * Mailchimp Address type fields array
	 *
	 * @since 1.0 Mailchimp Addon
	 * @return array
	 */
	public function mail_address_fields() {

		$address = array(
			'addr1'   => 'Address 1',
			'addr2'   => 'Address 2',
			'city'    => 'City',
			'state'   => 'State',
			'zip'     => 'Zip',
			'country' => 'Country',
		);

		return $address;
	}
}
