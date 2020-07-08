<?php

require_once dirname( __FILE__ ) . '/class-forminator-addon-trello-form-settings-exception.php';

/**
 * Class Forminator_Addon_Trello_Form_Settings
 * Handle how form settings displayed and saved
 *
 * @since 1.0 Trello Addon
 */
class Forminator_Addon_Trello_Form_Settings extends Forminator_Addon_Form_Settings_Abstract {

	/**
	 * @var Forminator_Addon_Trello
	 * @since 1.0 Trello Addon
	 */
	protected $addon;

	/**
	 * Forminator_Addon_Trello_Form_Settings constructor.
	 *
	 * @since 1.0 Trello Addon
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
	 * Trello Form Settings wizard
	 *
	 * @since 1.0 Trello Addon
	 * @return array
	 */
	public function form_settings_wizards() {
		// numerical array steps
		return array(
			array(
				'callback'     => array( $this, 'setup_name' ),
				'is_completed' => array( $this, 'setup_name_is_completed' ),
			),
			array(
				'callback'     => array( $this, 'setup_board' ),
				'is_completed' => array( $this, 'setup_board_is_completed' ),
			),
			array(
				'callback'     => array( $this, 'setup_list' ),
				'is_completed' => array( $this, 'setup_list_is_completed' ),
			),
			array(
				'callback'     => array( $this, 'setup_card' ),
				'is_completed' => array( $this, 'setup_card_is_completed' ),
			),
		);
	}

	/**
	 * Setup Name
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param $submitted_data
	 *
	 * @return array
	 */
	public function setup_name( $submitted_data ) {
		$template = forminator_addon_trello_dir() . 'views/form-settings/setup-name.php';

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
					throw new Forminator_Addon_Trello_Exception( __( 'Please pick valid name' ) );
				}

				$time_added = $this->get_multi_id_form_settings_value( $multi_id, 'time_added', time() );
				$this->save_multi_id_form_setting_values(
					$multi_id,
					array(
						'name'       => $name,
						'time_added' => $time_added,
					)
				);

			} catch ( Forminator_Addon_Trello_Exception $e ) {
				$template_params['name_error'] = $e->getMessage();
				$has_errors                    = true;
			}
		}

		$buttons = array();
		if ( $this->setup_name_is_completed( array( 'multi_id' => $multi_id ) ) ) {
			$buttons['disconnect']['markup'] = Forminator_Addon_Abstract::get_button_markup(
				esc_html__( 'Deactivate', Forminator::DOMAIN ),
				'sui-button-ghost sui-tooltip sui-tooltip-top-center forminator-addon-form-disconnect',
				esc_html__( 'Deactivate this Trello Integration from this Form.', Forminator::DOMAIN )
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
	 * Check if setup name is completed
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param $submitted_data
	 *
	 * @return bool
	 */
	public function setup_name_is_completed( $submitted_data ) {
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
	 * Setup Board
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param $submitted_data
	 *
	 * @return array
	 */
	public function setup_board( $submitted_data ) {
		$template = forminator_addon_trello_dir() . 'views/form-settings/setup-board.php';

		if ( ! isset( $submitted_data['multi_id'] ) ) {
			return $this->get_force_closed_wizard( __( 'Please pick valid connection', Forminator::DOMAIN ) );
		}

		$multi_id = $submitted_data['multi_id'];
		unset( $submitted_data['multi_id'] );

		$template_params = array(
			'board_id'       => $this->get_multi_id_form_settings_value( $multi_id, 'board_id', '' ),
			'board_id_error' => '',
			'multi_id'       => $multi_id,
			'error_message'  => '',
			'boards'         => array(),
		);

		$is_submit  = ! empty( $submitted_data );
		$has_errors = false;

		$boards = array();

		try {

			$api            = $this->addon->get_api();
			$boards_request = $api->get_boards();

			foreach ( $boards_request as $key => $data ) {
				if ( isset( $data->id ) && isset( $data->name ) ) {
					$boards[ $data->id ] = $data->name;
				}
			}

			if ( empty( $boards ) ) {
				throw new Forminator_Addon_Trello_Exception( __( 'No board found on your Trello account. Please create one.', Forminator::DOMAIN ) );
			}

			$template_params['boards'] = $boards;

		} catch ( Forminator_Addon_Trello_Exception $e ) {
			$template_params['error_message'] = $e->getMessage();
			$has_errors                       = true;
		}

		if ( $is_submit ) {
			$board_id                    = isset( $submitted_data['board_id'] ) ? $submitted_data['board_id'] : '';
			$template_params['board_id'] = $board_id;

			try {
				if ( empty( $board_id ) ) {
					throw new Forminator_Addon_Trello_Exception( __( 'Please pick valid board.' ) );
				}

				// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				if ( ! in_array( $board_id, array_keys( $boards ) ) ) {
					throw new Forminator_Addon_Trello_Exception( __( 'Please pick valid board.' ) );
				}

				$board_name = $boards[ $board_id ];

				$this->save_multi_id_form_setting_values(
					$multi_id,
					array(
						'board_id'   => $board_id,
						'board_name' => $board_name,
					)
				);

			} catch ( Forminator_Addon_Trello_Exception $e ) {
				$template_params['board_id_error'] = $e->getMessage();
				$has_errors                        = true;
			}
		}

		$buttons = array();
		if ( $this->setup_name_is_completed( array( 'multi_id' => $multi_id ) ) ) {
			$buttons['disconnect']['markup'] = Forminator_Addon_Abstract::get_button_markup(
				esc_html__( 'Deactivate', Forminator::DOMAIN ),
				'sui-button-ghost sui-tooltip sui-tooltip-top-center forminator-addon-form-disconnect',
				esc_html__( 'Deactivate this Trello Integration from this Form.', Forminator::DOMAIN )
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
	 * Check if setup board is completed
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param $submitted_data
	 *
	 * @return bool
	 */
	public function setup_board_is_completed( $submitted_data ) {
		$multi_id = '';
		if ( isset( $submitted_data['multi_id'] ) ) {
			$multi_id = $submitted_data['multi_id'];
		}

		if ( empty( $multi_id ) ) {
			return false;
		}

		$board_id = $this->get_multi_id_form_settings_value( $multi_id, 'board_id', '' );

		if ( empty( $board_id ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Setup List on Board
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param $submitted_data
	 *
	 * @return array
	 */
	public function setup_list( $submitted_data ) {
		$template = forminator_addon_trello_dir() . 'views/form-settings/setup-list.php';

		if ( ! isset( $submitted_data['multi_id'] ) ) {
			return $this->get_force_closed_wizard( __( 'Please pick valid connection', Forminator::DOMAIN ) );
		}

		$multi_id = $submitted_data['multi_id'];
		unset( $submitted_data['multi_id'] );

		//todo: validate this, step wizard back if needed
		$board_name = $this->get_multi_id_form_settings_value( $multi_id, 'board_name', '' );
		$board_id   = $this->get_multi_id_form_settings_value( $multi_id, 'board_id', '' );

		$template_params = array(
			'list_id'       => $this->get_multi_id_form_settings_value( $multi_id, 'list_id', '' ),
			'list_id_error' => '',
			'board_name'    => $board_name,
			'multi_id'      => $multi_id,
			'error_message' => '',
			'lists'         => array(),
		);

		$is_submit  = ! empty( $submitted_data );
		$has_errors = false;

		$lists = array();

		try {

			$api          = $this->addon->get_api();
			$list_request = $api->get_board_lists( $board_id );

			foreach ( $list_request as $key => $data ) {
				if ( isset( $data->id ) && isset( $data->name ) ) {
					$lists[ $data->id ] = $data->name;
				}
			}

			if ( empty( $lists ) ) {
				/* translators: ... */
				throw new Forminator_Addon_Trello_Exception( sprintf( __( 'No list found on Trello Board of %1$s. Please create one.', Forminator::DOMAIN ), $board_name ) );
			}

			$template_params['lists'] = $lists;

		} catch ( Forminator_Addon_Trello_Exception $e ) {
			$template_params['error_message'] = $e->getMessage();
			$has_errors                       = true;
		}

		if ( $is_submit ) {
			$list_id                    = isset( $submitted_data['list_id'] ) ? $submitted_data['list_id'] : '';
			$template_params['list_id'] = $list_id;

			try {
				if ( empty( $list_id ) ) {
					throw new Forminator_Addon_Trello_Exception( __( 'Please pick valid list.' ) );
				}

				// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				if ( ! in_array( $list_id, array_keys( $lists ) ) ) {
					throw new Forminator_Addon_Trello_Exception( __( 'Please pick valid list.' ) );
				}

				$list_name = $lists[ $list_id ];

				$this->save_multi_id_form_setting_values(
					$multi_id,
					array(
						'list_id'   => $list_id,
						'list_name' => $list_name,
					)
				);

			} catch ( Forminator_Addon_Trello_Exception $e ) {
				$template_params['list_id_error'] = $e->getMessage();
				$has_errors                       = true;
			}
		}

		$buttons = array();
		if ( $this->setup_name_is_completed( array( 'multi_id' => $multi_id ) ) ) {
			$buttons['disconnect']['markup'] = Forminator_Addon_Abstract::get_button_markup(
				esc_html__( 'Deactivate', Forminator::DOMAIN ),
				'sui-button-ghost sui-tooltip sui-tooltip-top-center forminator-addon-form-disconnect',
				esc_html__( 'Deactivate this Trello Integration from this Form.', Forminator::DOMAIN )
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
	 * Check if setup list is completed
	 *
	 * @since 1.0 Trello Addon
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
	 * Setup Card
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param $submitted_data
	 *
	 * @return array
	 */
	public function setup_card( $submitted_data ) {
		$template = forminator_addon_trello_dir() . 'views/form-settings/setup-card.php';

		if ( ! isset( $submitted_data['multi_id'] ) ) {
			return $this->get_force_closed_wizard( __( 'Please pick valid connection', Forminator::DOMAIN ) );
		}

		$multi_id = $submitted_data['multi_id'];
		unset( $submitted_data['multi_id'] );

		$positions = array(
			'top'    => __( 'Top', Forminator::DOMAIN ),
			'bottom' => __( 'Bottom', Forminator::DOMAIN ),
		);

		//todo: validate this, step wizard back if needed
		$board_id = $this->get_multi_id_form_settings_value( $multi_id, 'board_id', '' );

		$template_params = array(
			'card_name'              => $this->get_multi_id_form_settings_value( $multi_id, 'card_name', 'New submission from {form_name}' ),
			'card_name_error'        => '',
			'card_description'       => $this->get_multi_id_form_settings_value( $multi_id, 'card_description', '{all_fields}' ),
			'card_description_error' => '',
			'position'               => $this->get_multi_id_form_settings_value( $multi_id, 'position', 'bottom' ),
			'position_error'         => '',
			'positions'              => $positions,
			'label_ids'              => $this->get_multi_id_form_settings_value( $multi_id, 'label_ids', array() ),
			'label_ids_error'        => '',
			'labels'                 => array(),
			'member_ids'             => $this->get_multi_id_form_settings_value( $multi_id, 'member_ids', array() ),
			'member_ids_error'       => '',
			'members'                => array(),
			'error_message'          => '',
			'fields'                 => $this->form_fields,
			'multi_id'               => $multi_id,
			'list_name'              => $this->get_multi_id_form_settings_value( $multi_id, 'list_name', '' ),
		);

		$is_submit    = ! empty( $submitted_data );
		$has_errors   = false;
		$is_close     = false;
		$notification = array();

		$labels  = array();
		$members = array();

		try {
			// get available labels
			$api            = $this->addon->get_api();
			$labels_request = $api->get_board_labels( $board_id );

			foreach ( $labels_request as $data ) {
				if ( isset( $data->id ) ) {
					$name = $data->color;
					if ( isset( $data->name ) && ! empty( $data->name ) ) {
						$name = $data->name;
					}
					$labels[ $data->id ] = array(
						'name'  => $name,
						'color' => $data->color,
					);
				}
			}

			// get available members
			$members_request = $api->get_board_members( $board_id );

			foreach ( $members_request as $data ) {
				if ( isset( $data->id ) && isset( $data->username ) ) {
					$display_name = $data->username;
					// its from API var
					// phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
					if ( isset( $data->fullName ) && ! empty( $data->fullName ) ) {
						// its from API var
						// phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
						$display_name = $data->fullName;
					}
					$members[ $data->id ] = $display_name;
				}
			}
		} catch ( Forminator_Addon_Trello_Exception $e ) {
			$template_params['error_message'] = $e->getMessage();
			$has_errors                       = true;
		}

		$template_params['labels']  = $labels;
		$template_params['members'] = $members;

		if ( $is_submit ) {
			$card_name                    = isset( $submitted_data['card_name'] ) ? trim( $submitted_data['card_name'] ) : '';
			$template_params['card_name'] = $card_name;

			$card_description                    = isset( $submitted_data['card_description'] ) ? trim( $submitted_data['card_description'] ) : '';
			$template_params['card_description'] = $card_description;

			$position                    = isset( $submitted_data['position'] ) ? $submitted_data['position'] : '';
			$template_params['position'] = $position;

			$label_ids                    = isset( $submitted_data['label_ids'] ) ? $submitted_data['label_ids'] : array();
			$template_params['label_ids'] = $label_ids;

			$member_ids                    = isset( $submitted_data['member_ids'] ) ? $submitted_data['member_ids'] : array();
			$template_params['member_ids'] = $member_ids;

			try {
				$input_exceptions = new Forminator_Addon_Trello_Form_Settings_Exception();

				if ( empty( $card_name ) ) {
					$input_exceptions->add_input_exception( 'Please specify card name.', 'card_name_error' );
				}

				if ( empty( $card_description ) ) {
					$input_exceptions->add_input_exception( 'Please specify card description.', 'card_description_error' );
				}

				if ( empty( $position ) ) {
					$input_exceptions->add_input_exception( 'Please specify position.', 'position_error' );
				}

				if ( ! in_array( $position, array_keys( $positions ), true ) ) {
					$input_exceptions->add_input_exception( 'Please pick valid position.', 'position_error' );
				}

				// optional label
				if ( ! empty( $label_ids ) && is_array( $label_ids ) ) {
					$labels_keys = array_keys( $labels );
					foreach ( $label_ids as $label_id ) {
						// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
						if ( ! in_array( $label_id, $labels_keys ) ) {
							$input_exceptions->add_input_exception( 'Please pick valid label.', 'label_ids_error' );
						}
					}
				} else {
					$label_ids = array();
				}

				// optional member
				if ( ! empty( $member_ids ) && is_array( $member_ids ) ) {
					$members_keys = array_keys( $members );
					foreach ( $member_ids as $member_id ) {
						// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
						if ( ! in_array( $member_id, $members_keys ) ) {
							$input_exceptions->add_input_exception( 'Please pick valid member.', 'member_ids_error' );
						}
					}
				} else {
					$member_ids = array();
				}

				if ( $input_exceptions->input_exceptions_is_available() ) {
					throw $input_exceptions;
				}

				$this->save_multi_id_form_setting_values(
					$multi_id,
					array(
						'card_name'        => $card_name,
						'card_description' => $card_description,
						'position'         => $position,
						'label_ids'        => $label_ids,
						'member_ids'       => $member_ids,
					)
				);

				$notification = array(
					'type' => 'success',
					'text' => '<strong>' . $this->addon->get_title() . '</strong> ' . __( 'Successfully connected to your form' ),
				);
				$is_close     = true;

			} catch ( Forminator_Addon_Trello_Form_Settings_Exception $e ) {
				$template_params = array_merge( $template_params, $e->get_input_exceptions() );
				$has_errors      = true;
			} catch ( Forminator_Addon_Trello_Exception $e ) {
				$template_params['error_message'] = $e->getMessage();
				$has_errors                       = true;
			}
		}

		$buttons = array();
		if ( $this->setup_name_is_completed( array( 'multi_id' => $multi_id ) ) ) {
			$buttons['disconnect']['markup'] = Forminator_Addon_Abstract::get_button_markup(
				esc_html__( 'Deactivate', Forminator::DOMAIN ),
				'sui-button-ghost sui-tooltip sui-tooltip-top-center forminator-addon-form-disconnect',
				esc_html__( 'Deactivate this Trello Integration from this Form.', Forminator::DOMAIN )
			);
		}

		$buttons['next']['markup'] = '<div class="sui-actions-right">' .
									Forminator_Addon_Abstract::get_button_markup( esc_html__( 'Save', Forminator::DOMAIN ), 'sui-button-primary forminator-addon-finish' ) .
									'</div>';

		return array(
			'html'         => Forminator_Addon_Abstract::get_template( $template, $template_params ),
			'buttons'      => $buttons,
			'redirect'     => false,
			'has_errors'   => $has_errors,
			'has_back'     => true,
			'size'         => 'normal',
			'is_close'     => $is_close,
			'notification' => $notification,
		);
	}

	/**
	 * Check if card completed
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param $submitted_data
	 *
	 * @return bool
	 */
	public function setup_card_is_completed( $submitted_data ) {
		$multi_id = '';
		if ( isset( $submitted_data['multi_id'] ) ) {
			$multi_id = $submitted_data['multi_id'];
		}

		if ( empty( $multi_id ) ) {
			return false;
		}

		$card_name = $this->get_multi_id_form_settings_value( $multi_id, 'card_name', '' );
		$card_name = trim( $card_name );
		if ( empty( $card_name ) ) {
			return false;
		}
		$card_desc = $this->get_multi_id_form_settings_value( $multi_id, 'card_description', '' );
		$card_desc = trim( $card_desc );
		if ( empty( $card_desc ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Generate multi id for multiple connection
	 *
	 * @since 1.0 Trello Addon
	 * @return string
	 */
	public function generate_multi_id() {
		return uniqid( 'trello_', true );
	}


	/**
	 * Override how multi connection displayed
	 *
	 * @since 1.0 Trello Addon
	 * @return array
	 */
	public function get_multi_ids() {
		$multi_ids            = array();
		$form_settings_values = $this->get_form_settings_values();
		foreach ( $form_settings_values as $key => $value ) {
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
		$multi_ids = apply_filters( 'forminator_addon_trello_multi_id_labels', $multi_ids, $form_settings_values );

		return $multi_ids;
	}

	/**
	 * Disconnect a connection from current form
	 *
	 * @since 1.0 Trello Addon
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
	 * @since 1.0 Trello Addon
	 *
	 * @param $multi_id
	 *
	 * @return bool
	 */
	public function is_multi_form_settings_complete( $multi_id ) {
		$data = array( 'multi_id' => $multi_id );

		if ( ! $this->setup_name_is_completed( $data ) ) {
			return false;
		}

		if ( ! $this->setup_board_is_completed( $data ) ) {
			return false;
		}

		if ( ! $this->setup_list_is_completed( $data ) ) {
			return false;
		}

		if ( ! $this->setup_card_is_completed( $data ) ) {
			return false;
		}

		return true;
	}
}
