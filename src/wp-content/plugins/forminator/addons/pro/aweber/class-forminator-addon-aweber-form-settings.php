<?php

require_once dirname( __FILE__ ) . '/class-forminator-addon-aweber-form-settings-exception.php';

/**
 * Class Forminator_Addon_Aweber_Form_Settings
 * Handle how form settings displayed and saved
 *
 * @since 1.0 Aweber Addon
 */
class Forminator_Addon_Aweber_Form_Settings extends Forminator_Addon_Form_Settings_Abstract {

	/**
	 * @var Forminator_Addon_Aweber
	 * @since 1.0 Aweber Addon
	 */
	protected $addon;

	/**
	 * Forminator_Addon_Aweber_Form_Settings constructor.
	 *
	 * @since 1.0 Aweber Addon
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
	 * Aweber Form Settings wizard
	 *
	 * @since 1.0 Aweber Addon
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
				'callback'     => array( $this, 'setup_list' ),
				'is_completed' => array( $this, 'setup_list_is_completed' ),
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
	 * @since 1.0 AWeber Addon
	 *
	 * @param $submitted_data
	 *
	 * @return array
	 */
	public function pick_name( $submitted_data ) {
		$template = forminator_addon_aweber_dir() . 'views/form-settings/pick-name.php';

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
					throw new Forminator_Addon_Aweber_Exception( __( 'Please pick valid name' ) );
				}

				$time_added = $this->get_multi_id_form_settings_value( $multi_id, 'time_added', time() );
				$this->save_multi_id_form_setting_values(
					$multi_id,
					array(
						'name'       => $name,
						'time_added' => $time_added,
					)
				);

			} catch ( Forminator_Addon_Aweber_Exception $e ) {
				$template_params['name_error'] = $e->getMessage();
				$has_errors                    = true;
			}
		}

		$buttons = array();
		if ( $this->pick_name_is_completed( array( 'multi_id' => $multi_id ) ) ) {
			$buttons['disconnect']['markup'] = Forminator_Addon_Abstract::get_button_markup(
				esc_html__( 'Deactivate', Forminator::DOMAIN ),
				'sui-button-ghost sui-tooltip sui-tooltip-top-center forminator-addon-form-disconnect',
				esc_html__( 'Deactivate this AWeber Integration from this Form.', Forminator::DOMAIN )
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
	 * @since 1.0 AWeber Addon
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
	 * Setup List
	 *
	 * @since 1.0 AWeber Addon
	 *
	 * @param $submitted_data
	 *
	 * @return array
	 */
	public function setup_list( $submitted_data ) {
		$template = forminator_addon_aweber_dir() . 'views/form-settings/setup-list.php';

		if ( ! isset( $submitted_data['multi_id'] ) ) {
			return $this->get_force_closed_wizard( __( 'Please pick valid connection', Forminator::DOMAIN ) );
		}

		$multi_id = $submitted_data['multi_id'];
		unset( $submitted_data['multi_id'] );

		$template_params = array(
			'list_id'       => $this->get_multi_id_form_settings_value( $multi_id, 'list_id', '' ),
			'list_name'     => $this->get_multi_id_form_settings_value( $multi_id, 'list_name', '' ),
			'list_id_error' => '',
			'multi_id'      => $multi_id,
			'error_message' => '',
		);

		$is_submit  = ! empty( $submitted_data );
		$has_errors = false;

		$lists = array();

		try {

			$api           = $this->addon->get_api();
			$lists_request = $api->get_account_lists( $this->addon->get_account_id() );

			if ( ! is_object( $lists_request ) || ! isset( $lists_request->entries ) || ! is_array( $lists_request->entries ) || empty( $lists_request->entries ) ) {
				throw new Forminator_Addon_Aweber_Exception( __( 'No lists found on your AWeber. Please create one.', Forminator::DOMAIN ) );
			}

			foreach ( $lists_request->entries as $entry ) {
				$lists[ $entry->id ] = $entry->name;
			}

			$template_params['lists'] = $lists;

		} catch ( Forminator_Addon_Aweber_Exception $e ) {
			$template_params['error_message'] = $e->getMessage();
			$has_errors                       = true;
		}

		if ( $is_submit ) {
			$list_id                    = isset( $submitted_data['list_id'] ) ? $submitted_data['list_id'] : '';
			$template_params['list_id'] = $list_id;

			try {

				if ( empty( $list_id ) ) {
					throw new Forminator_Addon_Aweber_Exception( __( 'Please pick valid list' ) );
				}

				// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				if ( ! in_array( $list_id, array_keys( $lists ) ) ) {
					throw new Forminator_Addon_Aweber_Exception( __( 'Please pick valid list' ) );
				}

				$list_name = $lists[ $list_id ];

				$this->save_multi_id_form_setting_values(
					$multi_id,
					array(
						'list_id'   => $list_id,
						'list_name' => $list_name,
					)
				);

			} catch ( Forminator_Addon_Aweber_Exception $e ) {
				$template_params['list_id_error'] = $e->getMessage();
				$has_errors                       = true;
			}
		}

		$buttons = array();
		if ( $this->pick_name_is_completed( array( 'multi_id' => $multi_id ) ) ) {
			$buttons['disconnect']['markup'] = Forminator_Addon_Abstract::get_button_markup(
				esc_html__( 'Deactivate', Forminator::DOMAIN ),
				'sui-button-ghost sui-tooltip sui-tooltip-top-center forminator-addon-form-disconnect',
				esc_html__( 'Deactivate this AWeber Integration from this Form.', Forminator::DOMAIN )
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
	 * Check if setup list completed
	 *
	 * @since 1.0 AWeber Addon
	 *
	 * @param $submitted_data
	 *
	 * @return bool
	 */
	public function setup_list_is_completed( $submitted_data ) {
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
	 * @since 1.0 AWeber Addon
	 *
	 * @param $submitted_data
	 *
	 * @return array
	 */
	public function map_fields( $submitted_data ) {
		$template = forminator_addon_aweber_dir() . 'views/form-settings/map-fields.php';

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
			'default_field_email' => __( 'Email Address', Forminator::DOMAIN ),
			'default_field_name'  => __( 'Name', Forminator::DOMAIN ),
		);

		$list_id = $this->get_multi_id_form_settings_value( $multi_id, 'list_id', 0 );

		try {

			$api                        = $this->addon->get_api();
			$list_custom_fields_request = $api->get_account_list_custom_fields( $this->addon->get_account_id(), $list_id );

			if ( ! is_object( $list_custom_fields_request ) || ! isset( $list_custom_fields_request->entries ) || ! is_array( $list_custom_fields_request->entries ) ) {
				throw new Forminator_Addon_Aweber_Exception( __( 'Failed to get Custom Fields on the list.', Forminator::DOMAIN ) );
			}

			foreach ( $list_custom_fields_request->entries as $entry ) {
				$fields[ $entry->id ] = $entry->name;
			}

			$template_params['fields'] = $fields;

		} catch ( Forminator_Addon_Aweber_Exception $e ) {
			$template_params['error_message'] = $e->getMessage();
			$has_errors                       = true;
		}

		if ( $is_submit ) {
			$fields_map                    = isset( $submitted_data['fields_map'] ) ? $submitted_data['fields_map'] : array();
			$template_params['fields_map'] = $fields_map;

			try {
				if ( empty( $fields_map ) ) {
					throw new Forminator_Addon_Aweber_Exception( __( 'Please assign fields.', Forminator::DOMAIN ) );
				}

				$input_exceptions = new Forminator_Addon_Aweber_Form_Settings_Exception();
				if ( ! isset( $fields_map['default_field_email'] ) || empty( $fields_map['default_field_email'] ) ) {
					$input_exceptions->add_input_exception( 'Please assign field for Email Address', 'default_field_email_error' );
				}

				$fields_map_to_save = array();
				foreach ( $fields as $key => $title ) {
					if ( isset( $fields_map[ $key ] ) && ! empty( $fields_map[ $key ] ) ) {
						$element_id = $fields_map[ $key ];
						if ( ! in_array( $element_id, $forminator_field_element_ids, true ) ) {
							$input_exceptions->add_input_exception(/* translators: ... */
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

				$this->save_multi_id_form_setting_values(
					$multi_id,
					array(
						'fields_map'    => $fields_map,
						'fields_mapper' => $fields,
					)
				);

			} catch ( Forminator_Addon_Aweber_Form_Settings_Exception $e ) {
				$template_params = array_merge( $template_params, $e->get_input_exceptions() );
				$has_errors      = true;
			} catch ( Forminator_Addon_Aweber_Exception $e ) {
				$template_params['error_message'] = $e->getMessage();
				$has_errors                       = true;
			}
		}

		$buttons = array();
		if ( $this->pick_name_is_completed( array( 'multi_id' => $multi_id ) ) ) {
			$buttons['disconnect']['markup'] = Forminator_Addon_Abstract::get_button_markup(
				esc_html__( 'Deactivate', Forminator::DOMAIN ),
				'sui-button-ghost sui-tooltip sui-tooltip-top-center forminator-addon-form-disconnect',
				esc_html__( 'Deactivate this AWeber Integration from this Form.', Forminator::DOMAIN )
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
	 * @since 1.0 AWeber Addon
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

		$fields_map    = $this->get_multi_id_form_settings_value( $multi_id, 'fields_map', array() );
		$fields_mapper = $this->get_multi_id_form_settings_value( $multi_id, 'fields_mapper', array() );

		if ( empty( $fields_map ) || ! is_array( $fields_map ) || count( $fields_map ) < 1 ) {
			return false;
		}
		if ( empty( $fields_mapper ) || ! is_array( $fields_mapper ) || count( $fields_mapper ) < 1 ) {
			return false;
		}

		if ( ! isset( $fields_map['default_field_email'] ) || empty( $fields_map['default_field_email'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Setup options
	 *
	 * Contains :
	 * - ad_tracking
	 * - misc_notes
	 * - tags
	 *
	 * @since 1.0 Campaign Monitor Addon
	 *
	 * @param $submitted_data
	 *
	 * @return array
	 */
	public function setup_options( $submitted_data ) {
		$template = forminator_addon_aweber_dir() . 'views/form-settings/setup-options.php';

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
			'ad_tracking'          => $this->get_multi_id_form_settings_value( $multi_id, 'ad_tracking', 'FORMINATOR {form_name}' ),
			'fields'               => $this->form_fields,
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

		if ( $is_submit ) {
			$ad_tracking = isset( $submitted_data['ad_tracking'] ) ? $submitted_data['ad_tracking'] : '';

			try {

				$this->save_multi_id_form_setting_values(
					$multi_id,
					array(
						'ad_tracking' => $ad_tracking,
						'tags'        => $saved_tags,
					)
				);

				$notification = array(
					'type' => 'success',
					'text' => '<strong>' . $this->addon->get_title() . '</strong> ' . __( 'Successfully connected to your form' ),
				);
				$is_close     = true;

			} catch ( Forminator_Addon_Aweber_Form_Settings_Exception $e ) {
				$template_params = array_merge( $template_params, $e->get_input_exceptions() );
				$has_errors      = true;
			} catch ( Forminator_Addon_Aweber_Exception $e ) {
				$template_params['error_message'] = $e->getMessage();
				$has_errors                       = true;
			}
		}

		$buttons = array();
		if ( $this->pick_name_is_completed( array( 'multi_id' => $multi_id ) ) ) {
			$buttons['disconnect']['markup'] = Forminator_Addon_Abstract::get_button_markup(
				esc_html__( 'Deactivate', Forminator::DOMAIN ),
				'sui-button-ghost sui-tooltip sui-tooltip-top-center forminator-addon-form-disconnect',
				esc_html__( 'Deactivate this Campaign Monitor Integration from this Form.', Forminator::DOMAIN )
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
	 * @since 1.0 AWeber Addon
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
	 * Check if multi_id form settings values completed
	 *
	 * @since 1.0 Aweber Added
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
		if ( ! $this->setup_list_is_completed( $data ) ) {
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

	/**
	 * Generate multi id for multiple connection
	 *
	 * @since 1.0 Aweber Addon
	 * @return string
	 */
	public function generate_multi_id() {
		return uniqid( 'aweber_', true );
	}


	/**
	 * Override how multi connection displayed
	 *
	 * @since 1.0 Aweber Addon
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
	 * @since 1.0 Aweber Addon
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
