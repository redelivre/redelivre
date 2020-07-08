<?php

require_once dirname( __FILE__ ) . '/class-forminator-addon-slack-form-settings-exception.php';

/**
 * Class Forminator_Addon_Slack_Form_Settings
 * Handle how form settings displayed and saved
 *
 * @since 1.0 Slack Addon
 */
class Forminator_Addon_Slack_Form_Settings extends Forminator_Addon_Form_Settings_Abstract {

	/**
	 * @var Forminator_Addon_Slack
	 * @since 1.0 Slack Addon
	 */
	protected $addon;

	public $target_types = array();

	/**
	 * Forminator_Addon_Slack_Form_Settings constructor.
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param Forminator_Addon_Abstract $addon
	 * @param                           $form_id
	 *
	 * @throws Forminator_Addon_Exception
	 */
	public function __construct( Forminator_Addon_Abstract $addon, $form_id ) {
		parent::__construct( $addon, $form_id );

		$this->_update_form_settings_error_message = __(
			'The update to your settings for this form failed, check the form input and try again.',
			Forminator::DOMAIN
		);

		$this->target_types = array(
			Forminator_Addon_Slack::TARGET_TYPE_PUBLIC_CHANNEL  => __( 'Public Channel', Forminator::DOMAIN ),
			Forminator_Addon_Slack::TARGET_TYPE_PRIVATE_CHANNEL => __( 'Private Channel', Forminator::DOMAIN ),
			Forminator_Addon_Slack::TARGET_TYPE_DIRECT_MESSAGE  => __( 'Direct Message', Forminator::DOMAIN ),
		);
	}

	/**
	 * Slack Form Settings wizard
	 *
	 * @since 1.0 Slack Addon
	 * @return array
	 */
	public function form_settings_wizards() {
		// numerical array steps
		return array(
			array(
				'callback'     => array( $this, 'pick_name' ),
				'is_completed' => array( $this, 'pick_name_is_completed' ),
			),
			array(
				'callback'     => array( $this, 'select_type' ),
				'is_completed' => array( $this, 'select_type_is_completed' ),
			),
			array(
				'callback'     => array( $this, 'select_target' ),
				'is_completed' => array( $this, 'select_target_is_completed' ),
			),
			array(
				'callback'     => array( $this, 'setup_message' ),
				'is_completed' => array( $this, 'setup_message_is_completed' ),
			),
		);
	}


	/**
	 * Setup Connection Name
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param $submitted_data
	 *
	 * @return array
	 */
	public function pick_name( $submitted_data ) {
		$template = forminator_addon_slack_dir() . 'views/form-settings/pick-name.php';

		$multi_id = $this->generate_multi_id();
		if ( isset( $submitted_data['multi_id'] ) ) {
			$multi_id = $submitted_data['multi_id'];
		}

		$template_params = array(
			'name'       => $this->get_multi_id_form_settings_value( $multi_id, 'name', '' ),
			'name_error' => '',
			'multi_id'   => $multi_id,
		);

		unset( $submitted_data['multi_id'] );

		$is_submit  = ! empty( $submitted_data );
		$has_errors = false;
		if ( $is_submit ) {
			$name                    = isset( $submitted_data['name'] ) ? $submitted_data['name'] : '';
			$template_params['name'] = $name;

			try {
				if ( empty( $name ) ) {
					throw new Forminator_Addon_Slack_Exception( __( 'Please pick valid name' ) );
				}

				$time_added = $this->get_multi_id_form_settings_value( $multi_id, 'time_added', time() );
				$this->save_multi_id_form_setting_values(
					$multi_id,
					array(
						'name'       => $name,
						'time_added' => $time_added,
					)
				);

			} catch ( Forminator_Addon_Slack_Exception $e ) {
				$template_params['name_error'] = $e->getMessage();
				$has_errors                    = true;
			}
		}

		$buttons = array();
		if ( $this->pick_name_is_completed( array( 'multi_id' => $multi_id ) ) ) {
			$buttons['disconnect']['markup'] = Forminator_Addon_Abstract::get_button_markup(
				esc_html__( 'Deactivate', Forminator::DOMAIN ),
				'sui-button-ghost sui-tooltip sui-tooltip-top-center forminator-addon-form-disconnect',
				esc_html__( 'Deactivate this Slack Integration from this Form.', Forminator::DOMAIN )
			);
		}

		$buttons['next']['markup'] = '<div class="sui-actions-right">' .
									Forminator_Addon_Abstract::get_button_markup( esc_html__( 'Next', Forminator::DOMAIN ), 'forminator-addon-next' ) .
									'</div>';

		return array(
			'html'       => Forminator_Addon_Abstract::get_template( $template, $template_params ),
			'buttons'    => $buttons,
			'redirect'   => false,
			'has_errors' => $has_errors,
		);
	}

	/**
	 * Check if pick name step completed
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param $submitted_data
	 *
	 * @return bool
	 */
	public function pick_name_is_completed( $submitted_data ) {
		$multi_id = '';
		if ( isset( $submitted_data['multi_id'] ) ) {
			$multi_id = $submitted_data['multi_id'];
		}

		if ( empty( $multi_id ) ) {
			return false;
		}

		$name = $this->get_multi_id_form_settings_value( $multi_id, 'name', '' );

		if ( empty( $name ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Select Message Type
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param $submitted_data
	 *
	 * @return array
	 */
	public function select_type( $submitted_data ) {
		$template = forminator_addon_slack_dir() . 'views/form-settings/select-type.php';

		if ( ! isset( $submitted_data['multi_id'] ) ) {
			return $this->get_force_closed_wizard( __( 'Please pick valid connection', Forminator::DOMAIN ) );
		}

		$multi_id = $submitted_data['multi_id'];
		unset( $submitted_data['multi_id'] );

		$template_params = array(
			'type'          => $this->get_multi_id_form_settings_value( $multi_id, 'type', '' ),
			'type_error'    => '',
			'multi_id'      => $multi_id,
			'error_message' => '',
		);

		$is_submit  = ! empty( $submitted_data );
		$has_errors = false;

		$types                    = $this->target_types;
		$template_params['types'] = $types;

		if ( $is_submit ) {
			$type                    = isset( $submitted_data['type'] ) ? $submitted_data['type'] : '';
			$template_params['type'] = $type;

			try {

				if ( empty( $type ) ) {
					throw new Forminator_Addon_Slack_Exception( __( 'Please pick valid type' ) );
				}

				if ( ! in_array( $type, array_keys( $types ), true ) ) {
					throw new Forminator_Addon_Slack_Exception( __( 'Please pick valid type' ) );
				}

				$this->save_multi_id_form_setting_values(
					$multi_id,
					array(
						'type' => $type,
					)
				);

			} catch ( Forminator_Addon_Slack_Exception $e ) {
				$template_params['type_error'] = $e->getMessage();
				$has_errors                    = true;
			}
		}

		$buttons = array();
		if ( $this->pick_name_is_completed( array( 'multi_id' => $multi_id ) ) ) {
			$buttons['disconnect']['markup'] = Forminator_Addon_Abstract::get_button_markup(
				esc_html__( 'Deactivate', Forminator::DOMAIN ),
				'sui-button-ghost sui-tooltip sui-tooltip-top-center forminator-addon-form-disconnect',
				esc_html__( 'Deactivate this Slack Integration from this Form.', Forminator::DOMAIN )
			);
		}

		$buttons['next']['markup'] = '<div class="sui-actions-right">' .
									Forminator_Addon_Abstract::get_button_markup( esc_html__( 'Next', Forminator::DOMAIN ), 'forminator-addon-next' ) .
									'</div>';

		return array(
			'html'       => Forminator_Addon_Abstract::get_template( $template, $template_params ),
			'buttons'    => $buttons,
			'redirect'   => false,
			'has_errors' => $has_errors,
			'has_back'   => true,
		);
	}

	/**
	 * Check if select type completed
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param $submitted_data
	 *
	 * @return bool
	 */
	public function select_type_is_completed( $submitted_data ) {
		$multi_id = '';
		if ( isset( $submitted_data['multi_id'] ) ) {
			$multi_id = $submitted_data['multi_id'];
		}

		if ( empty( $multi_id ) ) {
			return false;
		}

		$type = $this->get_multi_id_form_settings_value( $multi_id, 'type', '' );

		if ( empty( $type ) ) {
			return false;
		}

		$types = $this->target_types;
		if ( ! in_array( $type, array_keys( $types ), true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Select Target
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param $submitted_data
	 *
	 * @return array
	 */
	public function select_target( $submitted_data ) {
		$template = forminator_addon_slack_dir() . 'views/form-settings/select-target.php';
		if ( ! isset( $submitted_data['multi_id'] ) ) {
			return $this->get_force_closed_wizard( __( 'Please pick valid connection', Forminator::DOMAIN ) );
		}

		$multi_id = $submitted_data['multi_id'];
		unset( $submitted_data['multi_id'] );

		$template_params = array(
			'target_id'       => $this->get_multi_id_form_settings_value( $multi_id, 'target_id', '' ),
			'target_id_error' => '',
			'multi_id'        => $multi_id,
			'error_message'   => '',
			'targets'         => array(),
			'help_message'    => '',
		);

		$type = $this->get_multi_id_form_settings_value( $multi_id, 'type', '' );
		switch ( $type ) {
			case Forminator_Addon_Slack::TARGET_TYPE_PRIVATE_CHANNEL:
				$func_get_targets                = 'get_groups_list';
				$key_to_walk                     = 'groups';
				$template_params['help_message'] = __( 'Select which Slack private group / channel this feed will post a message to.', Forminator::DOMAIN );
				break;
			case Forminator_Addon_Slack::TARGET_TYPE_DIRECT_MESSAGE:
				$func_get_targets                = 'get_users_list';
				$key_to_walk                     = 'members';
				$template_params['help_message'] = __( 'Select which Slack user this feed will post a message to.', Forminator::DOMAIN );
				break;
			default:
				$func_get_targets                = 'get_channels_list';
				$key_to_walk                     = 'channels';
				$template_params['help_message'] = __( 'Select which Slack channel this feed will post a message to.', Forminator::DOMAIN );
				break;
		}

		$is_submit  = ! empty( $submitted_data );
		$has_errors = false;

		$targets = array();

		try {

			$api             = $this->addon->get_api();
			$targets_request = call_user_func( array( $api, $func_get_targets ) );
			if ( ! is_object( $targets_request ) || ! isset( $targets_request->$key_to_walk ) || ! is_array( $targets_request->$key_to_walk ) || empty( $targets_request->$key_to_walk ) ) {
				throw new Forminator_Addon_Slack_Exception( __( 'No target found on your selected target type.', Forminator::DOMAIN ) );
			}

			foreach ( $targets_request->$key_to_walk as $value ) {
				$targets[ $value->id ] = $value->name;
			}

			$template_params['targets'] = $targets;

		} catch ( Forminator_Addon_Slack_Exception $e ) {
			$template_params['error_message'] = $e->getMessage();
			$has_errors                       = true;
		}

		if ( $is_submit ) {
			$target_id                    = isset( $submitted_data['target_id'] ) ? $submitted_data['target_id'] : '';
			$template_params['target_id'] = $target_id;

			try {

				if ( empty( $target_id ) ) {
					throw new Forminator_Addon_Slack_Exception( __( 'Please pick valid target' ) );
				}

				if ( ! in_array( $target_id, array_keys( $targets ), true ) ) {
					throw new Forminator_Addon_Slack_Exception( __( 'Please pick valid target' ) );
				}

				$target_name = $targets[ $target_id ];

				$this->save_multi_id_form_setting_values(
					$multi_id,
					array(
						'target_id'   => $target_id,
						'target_name' => $target_name,
					)
				);

			} catch ( Forminator_Addon_Slack_Exception $e ) {
				$template_params['target_id_error'] = $e->getMessage();
				$has_errors                         = true;
			}
		}

		$buttons = array();
		if ( $this->pick_name_is_completed( array( 'multi_id' => $multi_id ) ) ) {
			$buttons['disconnect']['markup'] = Forminator_Addon_Abstract::get_button_markup(
				esc_html__( 'Deactivate', Forminator::DOMAIN ),
				'sui-button-ghost sui-tooltip sui-tooltip-top-center forminator-addon-form-disconnect',
				esc_html__( 'Deactivate this Slack Integration from this Form.', Forminator::DOMAIN )
			);
		}

		$buttons['next']['markup'] = '<div class="sui-actions-right">' .
									Forminator_Addon_Abstract::get_button_markup( esc_html__( 'Next', Forminator::DOMAIN ), 'forminator-addon-next' ) .
									'</div>';

		return array(
			'html'       => Forminator_Addon_Abstract::get_template( $template, $template_params ),
			'buttons'    => $buttons,
			'redirect'   => false,
			'has_errors' => $has_errors,
			'has_back'   => true,
		);

	}

	/**
	 * Check if select target completed
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param $submitted_data
	 *
	 * @return bool
	 */
	public function select_target_is_completed( $submitted_data ) {
		$multi_id = '';
		if ( isset( $submitted_data['multi_id'] ) ) {
			$multi_id = $submitted_data['multi_id'];
		}

		if ( empty( $multi_id ) ) {
			return false;
		}

		$target_id = $this->get_multi_id_form_settings_value( $multi_id, 'target_id', '' );

		if ( empty( $target_id ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Setup Message
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param $submitted_data
	 *
	 * @return array
	 */
	public function setup_message( $submitted_data ) {
		$template = forminator_addon_slack_dir() . 'views/form-settings/setup-message.php';
		if ( ! isset( $submitted_data['multi_id'] ) ) {
			return $this->get_force_closed_wizard( __( 'Please pick valid connection', Forminator::DOMAIN ) );
		}

		$multi_id = $submitted_data['multi_id'];
		unset( $submitted_data['multi_id'] );

		$template_params = array(
			'message'       => $this->get_multi_id_form_settings_value( $multi_id, 'message', 'New submission from *{form_name}*' ),
			'message_error' => '',
			'multi_id'      => $multi_id,
			'error_message' => '',
			'fields'        => $this->form_fields,
		);

		$is_submit    = ! empty( $submitted_data );
		$has_errors   = false;
		$notification = array();
		$is_close     = false;

		if ( $is_submit ) {
			$message                    = isset( $submitted_data['message'] ) ? $submitted_data['message'] : '';
			$template_params['message'] = $message;

			try {

				if ( empty( $message ) ) {
					throw new Forminator_Addon_Slack_Exception( __( 'Please add a message' ) );
				}

				$this->save_multi_id_form_setting_values(
					$multi_id,
					array(
						'message' => $message,
					)
				);

				$notification = array(
					'type' => 'success',
					'text' => '<strong>' . $this->addon->get_title() . '</strong> ' . __( 'Successfully connected to your form' ),
				);
				$is_close     = true;

			} catch ( Forminator_Addon_Slack_Exception $e ) {
				$template_params['message_error'] = $e->getMessage();
				$has_errors                       = true;
			}
		}

		$buttons = array();
		if ( $this->pick_name_is_completed( array( 'multi_id' => $multi_id ) ) ) {
			$buttons['disconnect']['markup'] = Forminator_Addon_Abstract::get_button_markup(
				esc_html__( 'Deactivate', Forminator::DOMAIN ),
				'sui-button-ghost sui-tooltip sui-tooltip-top-center forminator-addon-form-disconnect',
				esc_html__( 'Deactivate this Slack Integration from this Form.', Forminator::DOMAIN )
			);
		}

		$buttons['next']['markup'] = '<div class="sui-actions-right">' .
									Forminator_Addon_Abstract::get_button_markup( esc_html__( 'CONNECT', Forminator::DOMAIN ), 'forminator-addon-next' ) .
									'</div>';

		return array(
			'html'         => Forminator_Addon_Abstract::get_template( $template, $template_params ),
			'buttons'      => $buttons,
			'redirect'     => false,
			'has_errors'   => $has_errors,
			'has_back'     => true,
			'notification' => $notification,
			'is_close'     => $is_close,
			'size'         => 'normal',
		);

	}

	/**
	 * Check if setup message completed
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param $submitted_data
	 *
	 * @return bool
	 */
	public function setup_message_is_completed( $submitted_data ) {
		$multi_id = '';
		if ( isset( $submitted_data['multi_id'] ) ) {
			$multi_id = $submitted_data['multi_id'];
		}

		if ( empty( $multi_id ) ) {
			return false;
		}

		$message = $this->get_multi_id_form_settings_value( $multi_id, 'message', '' );

		if ( empty( $message ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if multi_id form settings values completed
	 *
	 * @since 1.0 Slack Added
	 *
	 * @param $multi_id
	 *
	 * @return bool
	 */
	public function is_multi_form_settings_complete( $multi_id ) {
		$data = array( 'multi_id' => $multi_id );

		if ( ! $this->pick_name_is_completed( $data ) ) {
			return false;
		}

		if ( ! $this->select_type_is_completed( $data ) ) {
			return false;
		}
		if ( ! $this->select_target_is_completed( $data ) ) {
			return false;
		}
		if ( ! $this->setup_message_is_completed( $data ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Generate multi id for multiple connection
	 *
	 * @since 1.0 Slack Addon
	 * @return string
	 */
	public function generate_multi_id() {
		return uniqid( 'slack_', true );
	}


	/**
	 * Override how multi connection displayed
	 *
	 * @since 1.0 Slack Addon
	 * @return array
	 */
	public function get_multi_ids() {
		$multi_ids = array();
		foreach ( $this->get_form_settings_values() as $key => $value ) {
			$multi_ids[] = array(
				'id'    => $key,
				// use name that was added by user on creating connection
				'label' => isset( $value['name'] ) ? $value['name'] : $key,
			);
		}

		return $multi_ids;
	}

	/**
	 * Disconnect a connection from current form
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param array $submitted_data
	 */
	public function disconnect_form( $submitted_data ) {
		// only execute if multi_id provided on submitted data
		if ( isset( $submitted_data['multi_id'] ) && ! empty( $submitted_data['multi_id'] ) ) {
			$addon_form_settings = $this->get_form_settings_values();
			unset( $addon_form_settings[ $submitted_data['multi_id'] ] );
			$this->save_form_settings_values( $addon_form_settings );
		}
	}
}
