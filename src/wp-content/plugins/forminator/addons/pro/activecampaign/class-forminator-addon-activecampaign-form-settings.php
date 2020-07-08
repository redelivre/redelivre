<?php

require_once dirname( __FILE__ ) . '/class-forminator-addon-activecampaign-form-settings-exception.php';

/**
 * Class Forminator_Addon_Activecampaign_Form_Settings
 * Handle how form settings displayed and saved
 *
 * @since 1.0 Activecampaign Addon
 */
class Forminator_Addon_Activecampaign_Form_Settings extends Forminator_Addon_Form_Settings_Abstract {

	/**
	 * @var Forminator_Addon_Activecampaign
	 * @since 1.0 Activecampaign Addon
	 */
	protected $addon;

	/**
	 * @var Forminator_Addon_Activecampaign_CustomField
	 * @since 1.7 Activecampaign Custom Fields
	 */
	protected $custom_fields;

	/**
	 * Forminator_Addon_Activecampaign_Form_Settings constructor.
	 *
	 * @since 1.0 Activecampaign Addon
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
	}

	/**
	 * Activecampaign Form Settings wizard
	 *
	 * @since 1.0 Activecampaign Addon
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
				'callback'     => array( $this, 'select_list' ),
				'is_completed' => array( $this, 'select_list_is_completed' ),
			),
			array(
				'callback'     => array( $this, 'map_fields' ),
				'is_completed' => array( $this, 'map_fields_is_completed' ),
			),
			array(
				'callback'     => array( $this, 'setup_options' ),
				'is_completed' => array( $this, 'setup_options_is_completed' ),
			),
		);
	}

	/**
	 * Setup Connection Name
	 *
	 * @since 1.0 Activecampaign Addon
	 *
	 * @param $submitted_data
	 *
	 * @return array
	 */
	public function pick_name( $submitted_data ) {
		$template = forminator_addon_activecampaign_dir() . 'views/form-settings/pick-name.php';

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
					throw new Forminator_Addon_Activecampaign_Exception( __( 'Please pick valid name' ) );
				}

				$time_added = $this->get_multi_id_form_settings_value( $multi_id, 'time_added', time() );
				$this->save_multi_id_form_setting_values(
					$multi_id,
					array(
						'name'       => $name,
						'time_added' => $time_added,
					)
				);

			} catch ( Forminator_Addon_Activecampaign_Exception $e ) {
				$template_params['name_error'] = $e->getMessage();
				$has_errors                    = true;
			}
		}

		$buttons = array();
		if ( $this->pick_name_is_completed( array( 'multi_id' => $multi_id ) ) ) {
			$buttons['disconnect']['markup'] = Forminator_Addon_Abstract::get_button_markup(
				esc_html__( 'Deactivate', Forminator::DOMAIN ),
				'sui-button-ghost sui-tooltip sui-tooltip-top-center forminator-addon-form-disconnect',
				esc_html__( 'Deactivate this ActiveCampaign Integration from this Form.', Forminator::DOMAIN )
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
	 * @since 1.0 Activecampaign Addon
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
	 * Setup Contact List
	 *
	 * @since 1.0 Activecampaign Addon
	 *
	 * @param $submitted_data
	 *
	 * @return array
	 */
	public function select_list( $submitted_data ) {
		$template = forminator_addon_activecampaign_dir() . 'views/form-settings/select-list.php';

		if ( ! isset( $submitted_data['multi_id'] ) ) {
			return $this->get_force_closed_wizard( __( 'Please pick valid connection', Forminator::DOMAIN ) );
		}

		$multi_id = $submitted_data['multi_id'];
		unset( $submitted_data['multi_id'] );

		$template_params = array(
			'list_id'       => $this->get_multi_id_form_settings_value( $multi_id, 'list_id', '' ),
			'list_id_error' => '',
			'multi_id'      => $multi_id,
			'error_message' => '',
			'lists'         => array(),
		);

		$is_submit  = ! empty( $submitted_data );
		$has_errors = false;

		$lists         = array();
		$custom_fields = array();

		try {

			$ac_api        = $this->addon->get_api();
			$lists_request = $ac_api->get_lists();
			foreach ( $lists_request as $key => $data ) {
				if ( isset( $data->id ) && isset( $data->name ) ) {
					$lists[ $data->id ] = $data->name;
					if ( isset( $data->fields ) ) {
						$custom_fields[ $data->id ] = $data->fields;
					}
				}
			}

			if ( empty( $lists ) ) {
				throw new Forminator_Addon_Activecampaign_Exception( __( 'No lists found on your ActiveCampaign account. Please create one.', Forminator::DOMAIN ) );
			}

			$template_params['lists'] = $lists;

		} catch ( Forminator_Addon_Activecampaign_Exception $e ) {
			$template_params['error_message'] = $e->getMessage();
			$has_errors                       = true;
		}

		if ( $is_submit ) {
			$list_id                    = isset( $submitted_data['list_id'] ) ? $submitted_data['list_id'] : '';
			$template_params['list_id'] = $list_id;

			try {
				if ( empty( $list_id ) ) {
					throw new Forminator_Addon_Activecampaign_Exception( __( 'Please pick valid list' ) );
				}

				// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				if ( ! in_array( $list_id, array_keys( $lists ) ) ) {
					throw new Forminator_Addon_Activecampaign_Exception( __( 'Please pick valid list' ) );
				}

				if ( ! empty( $custom_fields ) ) {
					$this->custom_fields = $custom_fields[ $list_id ];
				}

				$list_name = $lists[ $list_id ];

				$this->save_multi_id_form_setting_values(
					$multi_id,
					array(
						'list_id'   => $list_id,
						'list_name' => $list_name,
					)
				);

			} catch ( Forminator_Addon_Activecampaign_Exception $e ) {
				$template_params['list_id_error'] = $e->getMessage();
				$has_errors                       = true;
			}
		}

		$buttons = array();
		if ( $this->pick_name_is_completed( array( 'multi_id' => $multi_id ) ) ) {
			$buttons['disconnect']['markup'] = Forminator_Addon_Abstract::get_button_markup(
				esc_html__( 'Deactivate', Forminator::DOMAIN ),
				'sui-button-ghost sui-tooltip sui-tooltip-top-center forminator-addon-form-disconnect',
				esc_html__( 'Deactivate this ActiveCampaign Integration from this Form.', Forminator::DOMAIN )
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
	 * Check if select contact list completed
	 *
	 * @since 1.0 Activecampaign Addon
	 *
	 * @param $submitted_data
	 *
	 * @return bool
	 */
	public function select_list_is_completed( $submitted_data ) {
		$multi_id = '';
		if ( isset( $submitted_data['multi_id'] ) ) {
			$multi_id = $submitted_data['multi_id'];
		}

		if ( empty( $multi_id ) ) {
			return false;
		}

		$list_id = $this->get_multi_id_form_settings_value( $multi_id, 'list_id', '' );

		if ( empty( $list_id ) ) {
			return false;
		}

		return true;
	}


	/**
	 * Setup fields map
	 *
	 * @since 1.0 Activecampaign Addon
	 *
	 * @param $submitted_data
	 *
	 * @return array
	 */
	public function map_fields( $submitted_data ) {
		$template = forminator_addon_activecampaign_dir() . 'views/form-settings/map-fields.php';

		if ( ! isset( $submitted_data['multi_id'] ) ) {
			return $this->get_force_closed_wizard( __( 'Please pick valid connection', Forminator::DOMAIN ) );
		}

		$multi_id = $submitted_data['multi_id'];
		unset( $submitted_data['multi_id'] );

		// find type of email
		$email_fields                 = array();
		$forminator_field_element_ids = array();
		foreach ( $this->form_fields as $form_field ) {
			// collect element ids
			$forminator_field_element_ids[] = $form_field['element_id'];
			if ( 'email' === $form_field['type'] ) {
				$email_fields[] = $form_field;
			}
		}

		$template_params = array(
			'fields_map'    => $this->get_multi_id_form_settings_value( $multi_id, 'fields_map', array() ),
			'multi_id'      => $multi_id,
			'error_message' => '',
			'fields'        => array(),
			'form_fields'   => $this->form_fields,
			'email_fields'  => $email_fields,
		);

		$is_submit  = ! empty( $submitted_data );
		$has_errors = false;

		$fields = array(
			'email'      => __( 'Email Address', Forminator::DOMAIN ),
			'first_name' => __( 'First Name', Forminator::DOMAIN ),
			'last_name'  => __( 'Last Name', Forminator::DOMAIN ),
			'phone'      => __( 'Phone', Forminator::DOMAIN ),
			'orgname'    => __( 'Organization Name', Forminator::DOMAIN ),
		);

		$list_id = $this->get_multi_id_form_settings_value( $multi_id, 'list_id', 0 );

		try {

			$ac_api      = $this->addon->get_api();
			$list_detail = $ac_api->get_list( $list_id );

			//get global fields assigned to the form as well as explecit field
			if ( ! empty( $this->custom_fields ) && is_array( $this->custom_fields ) ) {
				foreach ( $this->custom_fields as $field ) {
					$fields[ $field->id ] = $field->title;
				}
			}

			$template_params['fields'] = $fields;

		} catch ( Forminator_Addon_Activecampaign_Exception $e ) {
			$template_params['error_message'] = $e->getMessage();
			$has_errors                       = true;
		}

		if ( $is_submit ) {
			$fields_map                    = isset( $submitted_data['fields_map'] ) ? $submitted_data['fields_map'] : array();
			$template_params['fields_map'] = $fields_map;

			try {
				if ( empty( $fields_map ) ) {
					throw new Forminator_Addon_Activecampaign_Exception( __( 'Please assign fields.', Forminator::DOMAIN ) );
				}

				$input_exceptions = new Forminator_Addon_Activecampaign_Form_Settings_Exception();
				if ( ! isset( $fields_map['email'] ) || empty( $fields_map['email'] ) ) {
					$input_exceptions->add_input_exception( 'Please assign field for Email Address', 'email_error' );
				}

				$fields_map_to_save = array();
				foreach ( $fields as $key => $title ) {
					if ( isset( $fields_map[ $key ] ) && ! empty( $fields_map[ $key ] ) ) {
						$element_id = $fields_map[ $key ];
						if ( ! in_array( $element_id, $forminator_field_element_ids, true ) ) {
							$input_exceptions->add_input_exception(
								/* translators: %s: title */
								sprintf( __( 'Please assign valid field for %s', Forminator::DOMAIN ), $title ),
								$key . '_error'
							);
							continue;
						}

						$fields_map_to_save[ $key ] = $fields_map[ $key ];
					}
				}

				if ( $input_exceptions->input_exceptions_is_available() ) {
					throw $input_exceptions;
				}

				$this->save_multi_id_form_setting_values( $multi_id, array( 'fields_map' => $fields_map ) );

			} catch ( Forminator_Addon_Activecampaign_Form_Settings_Exception $e ) {
				$template_params = array_merge( $template_params, $e->get_input_exceptions() );
				$has_errors      = true;
			} catch ( Forminator_Addon_Activecampaign_Exception $e ) {
				$template_params['error_message'] = $e->getMessage();
				$has_errors                       = true;
			}
		}

		$buttons = array();
		if ( $this->pick_name_is_completed( array( 'multi_id' => $multi_id ) ) ) {
			$buttons['disconnect']['markup'] = Forminator_Addon_Abstract::get_button_markup(
				esc_html__( 'Deactivate', Forminator::DOMAIN ),
				'sui-button-ghost sui-tooltip sui-tooltip-top-center forminator-addon-form-disconnect',
				esc_html__( 'Deactivate this ActiveCampaign Integration from this Form.', Forminator::DOMAIN )
			);
		}

		$buttons['next']['markup'] = '<div class="sui-actions-right">' .
									Forminator_Addon_Abstract::get_button_markup( esc_html__( 'Next', Forminator::DOMAIN ), 'forminator-addon-next' ) .
									'</div>';

		return array(
			'html'       => Forminator_Addon_Abstract::get_template( $template, $template_params ),
			'buttons'    => $buttons,
			'size'       => 'normal',
			'redirect'   => false,
			'has_errors' => $has_errors,
			'has_back'   => true,
		);
	}

	/**
	 * Check if fields mapped
	 *
	 * @since 1.0 Activecampaign Addon
	 *
	 * @param $submitted_data
	 *
	 * @return bool
	 */
	public function map_fields_is_completed( $submitted_data ) {
		$multi_id = '';
		if ( isset( $submitted_data['multi_id'] ) ) {
			$multi_id = $submitted_data['multi_id'];
		}

		if ( empty( $multi_id ) ) {
			return false;
		}

		$fields_map = $this->get_multi_id_form_settings_value( $multi_id, 'fields_map', array() );

		if ( empty( $fields_map ) || ! is_array( $fields_map ) || count( $fields_map ) < 1 ) {
			return false;
		}

		if ( ! isset( $fields_map['email'] ) || empty( $fields_map['email'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Setup options
	 *
	 * Contains :
	 * - Double opt-in form,
	 * - tags,
	 * - instant-responder,
	 * - send last broadcast
	 *
	 * @since 1.0 Activecampaign Addon
	 *
	 * @param $submitted_data
	 *
	 * @return array
	 */
	public function setup_options( $submitted_data ) {
		$template = forminator_addon_activecampaign_dir() . 'views/form-settings/setup-options.php';

		if ( ! isset( $submitted_data['multi_id'] ) ) {
			return $this->get_force_closed_wizard( __( 'Please pick valid connection', Forminator::DOMAIN ) );
		}

		$multi_id = $submitted_data['multi_id'];
		unset( $submitted_data['multi_id'] );

		$forminator_form_element_ids = array();
		foreach ( $this->form_fields as $field ) {
			$forminator_form_element_ids[ $field['element_id'] ] = $field;
		}

		$template_params = array(
			'multi_id'             => $multi_id,
			'error_message'        => '',
			'forms'                => array(),
			'double_opt_form_id'   => $this->get_multi_id_form_settings_value( $multi_id, 'double_opt_form_id', '' ),
			'instantresponders'    => $this->get_multi_id_form_settings_value( $multi_id, 'instantresponders', 0 ),
			'lastmessage'          => $this->get_multi_id_form_settings_value( $multi_id, 'lastmessage', 0 ),
			'tags_fields'          => array(),
			'tags_selected_fields' => array(),
		);

		$saved_tags = $this->get_multi_id_form_settings_value( $multi_id, 'tags', array() );

		if ( isset( $submitted_data['tags'] ) && is_array( $submitted_data['tags'] ) ) {
			$saved_tags = $submitted_data['tags'];

		}
		$tag_selected_fields = array();
		foreach ( $saved_tags as $key => $saved_tag ) {
			// using form data
			if ( stripos( $saved_tag, '{' ) === 0
				&& stripos( $saved_tag, '}' ) === ( strlen( $saved_tag ) - 1 )
			) {
				$element_id = str_ireplace( '{', '', $saved_tag );
				$element_id = str_ireplace( '}', '', $element_id );
				if ( in_array( $element_id, array_keys( $forminator_form_element_ids ), true ) ) {
					$forminator_form_element_ids[ $element_id ]['field_label'] = $forminator_form_element_ids[ $element_id ]['field_label'] .
																				' | ' . $forminator_form_element_ids[ $element_id ]['element_id'];
					$forminator_form_element_ids[ $element_id ]['element_id']  = '{' . $forminator_form_element_ids[ $element_id ]['element_id'] . '}';

					$tag_selected_fields[] = $forminator_form_element_ids[ $element_id ];
					// let this go, its already selected.
					unset( $forminator_form_element_ids[ $element_id ] );
				} else {
					// no more exist on element ids let it go
					unset( $saved_tags[ $key ] );
				}
			} else { // free form type
				$tag_selected_fields[] = array(
					'element_id'  => $saved_tag,
					'field_label' => $saved_tag,
				);
			}
		}

		$template_params['tags_fields']          = $forminator_form_element_ids;
		$template_params['tags_selected_fields'] = $tag_selected_fields;

		$is_submit    = ! empty( $submitted_data );
		$has_errors   = false;
		$notification = array();
		$is_close     = false;

		$forms = array();
		try {
			$api           = $this->addon->get_api();
			$forms_request = $api->get_forms();

			foreach ( $forms_request as $data ) {
				if ( isset( $data->id ) && isset( $data->name ) ) {
					$forms[ $data->id ] = $data->name;
				}
			}
		} catch ( Forminator_Addon_Activecampaign_Exception $e ) {
			$forms = array();
		}

		$template_params['forms'] = $forms;

		if ( $is_submit ) {
			$double_opt_form_id = isset( $submitted_data['double_opt_form_id'] ) ? $submitted_data['double_opt_form_id'] : '';
			$instantresponders  = isset( $submitted_data['instantresponders'] ) ? (int) $submitted_data['instantresponders'] : 0;
			$lastmessage        = isset( $submitted_data['lastmessage'] ) ? (int) $submitted_data['lastmessage'] : 0;

			try {
				$input_exceptions = new Forminator_Addon_Activecampaign_Form_Settings_Exception();

				// possible different type intended
				// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				if ( ! empty( $double_opt_form_id ) && ! in_array( $double_opt_form_id, array_keys( $forms ) ) ) {
					$input_exceptions->add_input_exception( __( 'Please pick valid ActiveCampaign Form', Forminator::DOMAIN ), 'double_opt_form_id_error' );
				}

				if ( $input_exceptions->input_exceptions_is_available() ) {
					throw $input_exceptions;
				}

				$this->save_multi_id_form_setting_values(
					$multi_id,
					array(
						'tags'               => $saved_tags,
						'double_opt_form_id' => $double_opt_form_id,
						'instantresponders'  => $instantresponders,
						'lastmessage'        => $lastmessage,
					)
				);

				$notification = array(
					'type' => 'success',
					'text' => '<strong>' . $this->addon->get_title() . '</strong> ' . __( 'Successfully connected to your form' ),
				);
				$is_close     = true;

			} catch ( Forminator_Addon_Activecampaign_Form_Settings_Exception $e ) {
				$template_params = array_merge( $template_params, $e->get_input_exceptions() );
				$has_errors      = true;
			} catch ( Forminator_Addon_Activecampaign_Exception $e ) {
				$template_params['error_message'] = $e->getMessage();
				$has_errors                       = true;
			}
		}

		$buttons = array();
		if ( $this->pick_name_is_completed( array( 'multi_id' => $multi_id ) ) ) {
			$buttons['disconnect']['markup'] = Forminator_Addon_Abstract::get_button_markup(
				esc_html__( 'Deactivate', Forminator::DOMAIN ),
				'sui-button-ghost sui-tooltip sui-tooltip-top-center forminator-addon-form-disconnect',
				esc_html__( 'Deactivate this ActiveCampaign Integration from this Form.', Forminator::DOMAIN )
			);
		}

		$buttons['next']['markup'] = '<div class="sui-actions-right">' .
									Forminator_Addon_Abstract::get_button_markup( esc_html__( 'Save', Forminator::DOMAIN ), 'sui-button-primary forminator-addon-finish' ) .
									'</div>';

		return array(
			'html'         => Forminator_Addon_Abstract::get_template( $template, $template_params ),
			'buttons'      => $buttons,
			'size'         => 'normal',
			'redirect'     => false,
			'has_errors'   => $has_errors,
			'has_back'     => true,
			'notification' => $notification,
			'is_close'     => $is_close,
		);
	}

	/**
	 * Check if setup options completed
	 *
	 * @since 1.0 Activecampaign Addon
	 *
	 * @param $submitted_data
	 *
	 * @return bool
	 */
	public function setup_options_is_completed( $submitted_data ) {
		// all settings here are optional, so it can be marked as completed
		return true;
	}

	/**
	 * Generate multi id for multiple connection
	 *
	 * @since 1.0 Activecampaign Addon
	 * @return string
	 */
	public function generate_multi_id() {
		return uniqid( 'activecampaign_', true );
	}


	/**
	 * Override how multi connection displayed
	 *
	 * @since 1.0 Activecampaign Addon
	 * @return array
	 */
	public function get_multi_ids() {
		$multi_ids            = array();
		$form_settings_values = $this->get_form_settings_values();
		foreach ( $form_settings_values as $key => $value ) {
			// apply some sorting if applicable
			$multi_ids[] = array(
				'id'    => $key,
				// use name that was added by user on creating connection
				'label' => isset( $value['name'] ) ? $value['name'] : $key,
			);
		}

		/**
		 * Filter labels of multi_id on integrations tab
		 *
		 * @since 1.2
		 *
		 * @param array $multi_ids
		 * @param array $form_settings_values
		 */
		$multi_ids = apply_filters( 'forminator_addon_activecampaign_multi_id_labels', $multi_ids, $form_settings_values );

		return $multi_ids;
	}

	/**
	 * Disconnect a connection from current form
	 *
	 * @since 1.0 Activecampaign Addon
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

	/**
	 * Check if multi_id form settings values completed
	 *
	 * @since 1.0 Active Campaign Added
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
		if ( ! $this->select_list_is_completed( $data ) ) {
			return false;
		}

		if ( ! $this->map_fields_is_completed( $data ) ) {
			return false;
		}

		if ( ! $this->setup_options_is_completed( $data ) ) {
			return false;
		}

		return true;
	}
}
