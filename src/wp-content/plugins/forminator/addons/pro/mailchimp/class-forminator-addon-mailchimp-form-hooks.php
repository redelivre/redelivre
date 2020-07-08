<?php

/**
 * Class Forminator_Addon_Mailchimp_Form_Hooks
 *
 * Hooks that used by Mailchimp Addon defined here
 *
 * @since 1.0 Mailchimp Addon
 */
class Forminator_Addon_Mailchimp_Form_Hooks extends Forminator_Addon_Form_Hooks_Abstract {

	/**
	 * Addon instance
	 *
	 * @var Forminator_Addon_Mailchimp
	 */
	protected $addon;

	/**
	 * Form settings instance
	 *
	 * @since 1.0 Mailchimp Addon
	 * @var Forminator_Addon_Mailchimp_Form_Settings | null
	 *
	 */
	protected $form_settings_instance;

	/**
	 * Flag of gpdr field checked on submit
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @var bool
	 */
	private $gdpr_is_checked = true;

	/**
	 * Forminator_Addon_Mailchimp_Form_Hooks constructor.
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param Forminator_Addon_Abstract $addon
	 * @param                           $form_id
	 *
	 * @throws Forminator_Addon_Exception
	 */
	public function __construct( Forminator_Addon_Abstract $addon, $form_id ) {
		parent::__construct( $addon, $form_id );
		$this->_submit_form_error_message = __( 'Mailchimp failed to process submitted data. Please check your form and try again', Forminator::DOMAIN );
	}

	/**
	 * Render extra fields after all forms fields rendered
	 *
	 * @since 1.0 Mailchimp Addon
	 */
	public function on_after_render_form_fields() {
		// Render GDPR field if enabled
		if ( Forminator_Addon_Mailchimp::is_enable_gdpr() ) {
			$addon_setting_values = $this->form_settings_instance->get_form_settings_values();
			if ( isset( $addon_setting_values['enable_gdpr'] ) && $addon_setting_values['enable_gdpr'] ) {
				if ( isset( $addon_setting_values['gdpr_text'] ) && ! empty( $addon_setting_values['gdpr_text'] ) ) {
					$this->render_gdpr_field( $addon_setting_values );
				}
			}
		}

		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;

		/**
		 * Fires when mailchimp rendering extra output after connected form fields rendered
		 *
		 * @since 1.1
		 *
		 * @param int                                      $form_id                current Form ID
		 * @param Forminator_Addon_Mailchimp_Form_Settings $form_settings_instance Mailchimp Form settings Instance
		 */
		do_action(
			'forminator_addon_mailchimp_on_after_render_form_fields',
			$form_id,
			$form_settings_instance
		);
	}

	/**
	 * Render GDPR Field - Experimental
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param $addon_setting_values
	 */
	private function render_gdpr_field( $addon_setting_values ) {

		$uniq_id    = uniqid();
		$input_name = 'forminator-addon-mailchimp-gdpr';
		$input_id   = $input_name . '-' . $uniq_id;
		$html       = '<div class="forminator-row"><div id="field-' . $input_id . '" class="forminator-col forminator-col-12"><div class="forminator-field">';
		$html      .= '<div class="forminator-field--label"><label class="forminator-label" id="forminator-label-' . $input_id . '">' . esc_html_e( 'Mailchimp GDPR', Forminator::DOMAIN )
					. '</label></div>';
		// matching checkbox with design
		$form_settings = $this->form_settings_instance->get_form_settings();
		$design_class  = self::get_form_setting_value_as( $form_settings, 'form-style', 'default', 'string' );
		if ( 'clean' === $design_class ) {

			$html .= '<label class="forminator-checkbox">';
			$html .= sprintf(
				'<input id="%s" type="checkbox" name="%s" value="1"> %s',
				$input_id,
				$input_name,
				$addon_setting_values['gdpr_text']
			);
			$html .= '</label>';

		} else {

			$html .= '<div class="forminator-checkbox">';
			$html .= sprintf(
				'<input id="%s" type="checkbox" name="%s" value="1" class="forminator-checkbox--input">',
				$input_id,
				$input_name
			);
			$html .= sprintf(
				'<label for="%s" class="forminator-checkbox--design wpdui-icon wpdui-icon-check" aria-hidden="true"></label>',
				$input_id
			);
			$html .= sprintf(
				'<label for="%s" class="forminator-checkbox--label">%s</label>',
				$input_id,
				$addon_setting_values['gdpr_text']
			);
			$html .= '</div>';

		}
		$html .= '</div></div></div>';

		echo wp_kses_post( $html );// phpcs:ignore Standard.Category.SniffName.ErrorCode
	}

	/**
	 * Helper Get form setting value with fixed var type
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param array      $form_settings
	 * @param string     $key
	 * @param            $default
	 * @param string     $type
	 *
	 * @return int|mixed
	 */
	private static function get_form_setting_value_as( $form_settings, $key, $default, $type = 'string' ) {
		if ( ! isset( $form_settings[ $key ] ) ) {
			return self::convert_value_to( $default, $type );
		}

		return self::convert_value_to( $form_settings[ $key ], $type );
	}

	/**
	 * Helper to convert value to expected var type
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param $value
	 * @param $type
	 *
	 * @return array|false|int|mixed|string
	 */
	private static function convert_value_to( $value, $type ) {
		switch ( $type ) {
			case 'array':
				if ( ! is_array( $value ) ) {
					if ( is_scalar( $value ) ) {
						return array( $value );
					} else {
						return (array) $value;
					}
				}

				return $value;
			case 'string':
				if ( ! is_scalar( $value ) ) {
					return wp_json_encode( $value );
				}

				return (string) trim( $value );
			case 'boolean':
				return filter_var( trim( $value ), FILTER_VALIDATE_BOOLEAN );
			case 'int':
				if ( ! is_scalar( $value ) ) {
					return 1;
				}

				return (int) trim( $value );
			default:
				return $value;
		}
	}

	/**
	 * Check GDPR field - Experimental
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param $submitted_data
	 *
	 * @return bool
	 */
	public function on_form_submit( $submitted_data ) {
		$is_success = true;

		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;

		/**
		 * Filter mailchimp submitted form data to be processed
		 *
		 * @since 1.1
		 *
		 * @param array                                    $submitted_data
		 * @param int                                      $form_id                current Form ID
		 * @param Forminator_Addon_Mailchimp_Form_Settings $form_settings_instance Mailchimp API Form Settings instance
		 */
		$submitted_data = apply_filters(
			'forminator_addon_mailchimp_form_submitted_data',
			$submitted_data,
			$form_id,
			$form_settings_instance
		);

		/**
		 * Fires when mailchimp connected form submit data
		 *
		 * Return `true` if success, or **(string) error message** on fail
		 *
		 * @since 1.1
		 *
		 * @param bool                                     $is_success
		 * @param int                                      $form_id                current Form ID
		 * @param array                                    $submitted_data
		 * @param Forminator_Addon_Mailchimp_Form_Settings $form_settings_instance Mailchimp API Form Settings instance
		 */
		$is_success = apply_filters(
			'forminator_addon_mailchimp_on_form_submit_result',
			$is_success,
			$form_id,
			$submitted_data,
			$form_settings_instance
		);

		// process filter
		if ( true !== $is_success ) {
			// only update `_submit_form_error_message` when not empty
			if ( ! empty( $is_success ) ) {
				$this->_submit_form_error_message = (string) $is_success;
			}

			return $is_success;
		}

		// only exec this below when filter return true
		// check is enabled
		if ( ! Forminator_Addon_Mailchimp::is_enable_gdpr() ) {
			return true;
		}

		// Only flag check for gdpr
		if ( ! isset( $submitted_data['forminator-addon-mailchimp-gdpr'] ) || '1' !== $submitted_data['forminator-addon-mailchimp-gdpr'] ) {
			$this->gdpr_is_checked = false;
		}

		return true;
	}

	/**
	 * Check submitted_data met requirement to sent to mailchimp
	 * Send if possible, add result to entry fields
	 *
	 * @since 1.0 Mailchimp Addon
	 * @since 1.7 Add $form_entry_fields arg
	 *
	 * @param array $submitted_data
	 * @param array $form_entry_fields
	 *
	 * @return array
	 */
	public function add_entry_fields( $submitted_data, $form_entry_fields = array() ) {

		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;

		/**
		 * Filter mailchimp submitted form data to be processed
		 *
		 * @since 1.1
		 *
		 * @param array                                    $submitted_data
		 * @param int                                      $form_id                current Form ID
		 * @param Forminator_Addon_Mailchimp_Form_Settings $form_settings_instance Mailchimp API Form Settings instance
		 */
		$submitted_data = apply_filters(
			'forminator_addon_mailchimp_form_submitted_data',
			$submitted_data,
			$form_id,
			$form_settings_instance
		);

		forminator_addon_maybe_log( __METHOD__, $submitted_data );

		$addon_setting_values = $this->form_settings_instance->get_form_settings_values();
		// initialize as null
		$mailchimp_api = null;

		//check required fields
		try {
			$mailchimp_api = $this->addon->get_api();

			if ( Forminator_Addon_Mailchimp::is_enable_gdpr() ) {
				// GDPR
				if ( isset( $addon_setting_values['enable_gdpr'] ) && $addon_setting_values['enable_gdpr'] ) {
					if ( isset( $addon_setting_values['gdpr_text'] ) && ! empty( $addon_setting_values['gdpr_text'] ) ) {
						if ( ! $this->gdpr_is_checked ) {
							//GDPR not checked, add error
							throw new Forminator_Addon_Mailchimp_Exception(
								__(
									'Forminator Addon Mailchimp was not sending subscriber to mailchimp as GDPR field is not checked on input',
									Forminator::DOMAIN
								)
							);

						}
					}
				}
			}

			// EMAIL : super required**
			if ( ! isset( $addon_setting_values['fields_map']['EMAIL'] ) ) {
				throw new Forminator_Addon_Mailchimp_Exception(/* translators: ... */
					sprintf( __( 'Required Field %1$s not mapped yet to Forminator Form Field, Please check your Mailchimp Configuration on Form Settings', Forminator::DOMAIN ), 'EMAIL' )
				);
			}

			if ( ! isset( $submitted_data[ $addon_setting_values['fields_map']['EMAIL'] ] ) || empty( $submitted_data[ $addon_setting_values['fields_map']['EMAIL'] ] ) ) {
				throw new Forminator_Addon_Mailchimp_Exception(/* translators: ... */
					sprintf( __( 'Required Field %1$s is not filled by user', Forminator::DOMAIN ), 'EMAIL' )
				);
			}

			$mailchimp_fields_list_request = $this->addon->get_api()->get_list_merge_fields( $addon_setting_values['mail_list_id'], array() );
			forminator_addon_maybe_log( __METHOD__, $mailchimp_fields_list_request );
			$mailchimp_required_fields     = array();
			$mailchimp_required_fields_ids = array();
			$mailchimp_fields_list         = array();
			if ( isset( $mailchimp_fields_list_request->merge_fields ) && is_array( $mailchimp_fields_list_request->merge_fields ) && ! empty( $mailchimp_fields_list_request->merge_fields ) ) {
				$mailchimp_fields_list = $mailchimp_fields_list_request->merge_fields;
			}

			foreach ( $mailchimp_fields_list as $item ) {
				if ( $item->required ) {
					$mailchimp_required_fields []    = $item;
					$mailchimp_required_fields_ids[] = $item->merge_id;
				}
			}

			//check required fields fulfilled
			foreach ( $mailchimp_required_fields as $mailchimp_required_field ) {
				if ( 'address' === $mailchimp_required_field->type ) {
					$address_fields = $this->form_settings_instance->mail_address_fields();
					foreach ( $address_fields as $addr => $address ) {
						if ( ! isset( $addon_setting_values['fields_map'][ $mailchimp_required_field->tag ][ $addr ] ) ) {
							throw new Forminator_Addon_Mailchimp_Exception(/* translators: ... */
								sprintf( __( 'Required Field %1$s not mapped yet to Forminator Form Field, Please check your Mailchimp Configuration on Form Settings', Forminator::DOMAIN ), $mailchimp_required_field->name )
							);
						}

						if ( ! isset( $submitted_data[ $addon_setting_values['fields_map'][ $mailchimp_required_field->tag ][ $addr ] ] )
							|| empty( $submitted_data[ $addon_setting_values['fields_map'][ $mailchimp_required_field->tag ][ $addr ] ] ) ) {
							throw new Forminator_Addon_Mailchimp_Exception(/* translators: ... */
								sprintf( __( 'Required Field %1$s not filled by user', Forminator::DOMAIN ), $mailchimp_required_field->name )
							);
						}
					}
				} else {

					if ( ! isset( $addon_setting_values['fields_map'][ $mailchimp_required_field->tag ] ) ) {
						throw new Forminator_Addon_Mailchimp_Exception(/* translators: ... */
							sprintf( __( 'Required Field %1$s not mapped yet to Forminator Form Field, Please check your Mailchimp Configuration on Form Settings', Forminator::DOMAIN ), $mailchimp_required_field->name )
						);
					}

					$element_id      = $addon_setting_values['fields_map'][ $mailchimp_required_field->tag ];
					$is_calculation  = self::element_is_calculation( $element_id );
					$is_stripe       = self::element_is_stripe( $element_id );
					$has_submit_data = isset( $submitted_data[ $element_id ] ) && ! empty( $submitted_data[ $element_id ] );

					if ( ! $is_calculation && ! $is_stripe && ! $has_submit_data ) {
						throw new Forminator_Addon_Mailchimp_Exception(/* translators: ... */
							sprintf( __( 'Required Field %1$s not filled by user', Forminator::DOMAIN ), $mailchimp_required_field->name )
						);
					}
				}
			}

			// check if user already on the list
			$subscriber_hash = md5( strtolower( trim( $submitted_data[ $addon_setting_values['fields_map']['EMAIL'] ] ) ) );

			$is_double_opt_in_enabled = isset( $addon_setting_values['enable_double_opt_in'] ) && filter_var( $addon_setting_values['enable_double_opt_in'], FILTER_VALIDATE_BOOLEAN ) ? true : false;
			$status                   = 'subscribed';
			if ( $is_double_opt_in_enabled ) {
				$status = 'pending';
			}

			try {
				// keep subscribed if already subscribed
				$member_status_request = $mailchimp_api->get_member( $addon_setting_values['mail_list_id'], $subscriber_hash, array() );
				if ( isset( $member_status_request->status ) && ! empty( $member_status_request->status ) ) {
					if ( 'subscribed' === $member_status_request->status ) {
						// already subscribed, keep it subscribed, just update merge_fields
						$status = 'subscribed';
					}
				}
			} catch ( Forminator_Addon_Mailchimp_Wp_Api_Not_Found_Exception $e ) {
				//Member not yet subscribed, keep going on, mark status based on double-opt-in option
				if ( $is_double_opt_in_enabled ) {
					$status = 'pending';
				}
			}

			$args = array(
				'status'        => $status,
				'status_if_new' => $status,
				'email_address' => strtolower( trim( $submitted_data[ $addon_setting_values['fields_map']['EMAIL'] ] ) ),
			);

			$merge_fields = array();
			foreach ( $mailchimp_fields_list as $item ) {
				// its mapped ?
				if ( 'address' === $item->type ) {
					$address_fields = $this->form_settings_instance->mail_address_fields();
					foreach ( $address_fields as $addr => $address ) {
						if ( isset( $addon_setting_values['fields_map'][ $item->tag ] ) && ! empty( $addon_setting_values['fields_map'][ $item->tag ] ) ) {
							if ( isset( $submitted_data[ $addon_setting_values['fields_map'][ $item->tag ][ $addr ] ] ) && ! empty( $submitted_data[ $addon_setting_values['fields_map'][ $item->tag ][ $addr ] ] ) ) {
								$merge_fields[ $item->tag ][ $addr ] = trim( $submitted_data[ $addon_setting_values['fields_map'][ $item->tag ][ $addr ] ] );
							}
						}
					}
				} else {
					if ( isset( $addon_setting_values['fields_map'][ $item->tag ] ) && ! empty( $addon_setting_values['fields_map'][ $item->tag ] ) ) {
						$element_id = $addon_setting_values['fields_map'][ $item->tag ];
						if ( self::element_is_calculation( $element_id ) ) {
							$meta_value    = self::find_meta_value_from_entry_fields( $element_id, $form_entry_fields );
							$element_value = Forminator_Form_Entry_Model::meta_value_to_string( 'calculation', $meta_value );
						} elseif ( self::element_is_stripe( $element_id ) ) {
							$meta_value    = self::find_meta_value_from_entry_fields( $element_id, $form_entry_fields );
							$element_value = Forminator_Form_Entry_Model::meta_value_to_string( 'stripe', $meta_value );
						} elseif ( isset( $submitted_data[ $element_id ] ) && ! empty( $submitted_data[ $element_id ] ) ) {
							$element_value = trim( $submitted_data[ $element_id ] );
						}

						if ( isset( $element_value ) ) {
							$merge_fields[ $item->tag ] = $element_value;
							unset( $element_value ); // unset for next loop
						}
					}
				}
			}

			forminator_addon_maybe_log( __METHOD__, $mailchimp_fields_list, $addon_setting_values, $submitted_data, $merge_fields );

			if ( ! empty( $merge_fields ) ) {
				$args['merge_fields'] = $merge_fields;
			}

			$mail_list_id = $addon_setting_values['mail_list_id'];

			/**
			 * Filter mail list id to send to Mailchimp API
			 *
			 * Change $mail_list_id that will be send to Mailchimp API,
			 * Any validation required by the mail list should be done.
			 * Else if it's rejected by Mailchimp API, It will only add Request to Log.
			 * Log can be viewed on Entries Page
			 *
			 * @since 1.1
			 *
			 * @param string                                   $mail_list_id
			 * @param int                                      $form_id                current Form ID
			 * @param array                                    $submitted_data         Submitted data
			 * @param Forminator_Addon_Mailchimp_Form_Settings $form_settings_instance Mailchimp Form Settings
			 */
			$mail_list_id = apply_filters(
				'forminator_addon_mailchimp_add_update_member_request_mail_list_id',
				$mail_list_id,
				$form_id,
				$submitted_data,
				$form_settings_instance
			);

			/**
			 * Filter Mailchimp API request arguments
			 *
			 * Request Arguments will be added to request body.
			 * Default args that will be send contains these keys:
			 * - status
			 * - status_if_new
			 * - merge_fields
			 * - email_address
			 * - interests
			 *
			 * @since 1.1
			 *
			 * @param array                                    $args
			 * @param int                                      $form_id                current Form ID
			 * @param array                                    $submitted_data         Submitted data
			 * @param Forminator_Addon_Mailchimp_Form_Settings $form_settings_instance Mailchimp Form Settings
			 */
			$args = apply_filters(
				'forminator_addon_mailchimp_add_update_member_request_args',
				$args,
				$form_id,
				$submitted_data,
				$form_settings_instance
			);

			/**
			 * Fires before Addon send request `add_or_update_member` to Mailchimp API
			 *
			 * If this action throw an error,
			 * then `add_or_update_member` process will be cancelled
			 *
			 * @since 1.1
			 *
			 * @param int                                      $form_id                current Form ID
			 * @param array                                    $submitted_data         Submitted data
			 * @param Forminator_Addon_Mailchimp_Form_Settings $form_settings_instance Mailchimp Form Settings
			 */
			do_action( 'forminator_addon_mailchimp_before_add_update_member', $form_id, $submitted_data, $form_settings_instance );

			$add_member_request = $mailchimp_api->add_or_update_member( $mail_list_id, $subscriber_hash, $args );
			if ( ! isset( $add_member_request->id ) || ! $add_member_request->id ) {
				throw new Forminator_Addon_Mailchimp_Exception(
					__(
						'Failed adding or updating member on Mailchimp list',
						Forminator::DOMAIN
					)
				);
			}

			forminator_addon_maybe_log( __METHOD__, 'Success Add Member' );

			$entry_fields = array(
				array(
					'name'  => 'status',
					'value' => array(
						'is_sent'       => true,
						'description'   => __( 'Successfully added or updated member on Mailchimp list', Forminator::DOMAIN ),
						'data_sent'     => $mailchimp_api->get_last_data_sent(),
						'data_received' => $mailchimp_api->get_last_data_received(),
						'url_request'   => $mailchimp_api->get_last_url_request(),
					),
				),
			);

		} catch ( Forminator_Addon_Mailchimp_Exception $e ) {
			forminator_addon_maybe_log( __METHOD__, 'Failed to Add Member' );

			$entry_fields = array(
				array(
					'name'  => 'status',
					'value' => array(
						'is_sent'       => false,
						'description'   => $e->getMessage(),
						'data_sent'     => ( ( $mailchimp_api instanceof Forminator_Addon_Mailchimp_Wp_Api ) ? $mailchimp_api->get_last_data_sent() : array() ),
						'data_received' => ( ( $mailchimp_api instanceof Forminator_Addon_Mailchimp_Wp_Api ) ? $mailchimp_api->get_last_data_received() : array() ),
						'url_request'   => ( ( $mailchimp_api instanceof Forminator_Addon_Mailchimp_Wp_Api ) ? $mailchimp_api->get_last_url_request() : '' ),
					),
				),
			);
		}

		/**
		 * Filter mailchimp entry fields to be saved to entry model
		 *
		 * @since 1.1
		 *
		 * @param array                                    $entry_fields
		 * @param int                                      $form_id                current Form ID
		 * @param array                                    $submitted_data
		 * @param Forminator_Addon_Mailchimp_Form_Settings $form_settings_instance Mailchimp API Form Settings instance
		 */
		$entry_fields = apply_filters(
			'forminator_addon_mailchimp_entry_fields',
			$entry_fields,
			$form_id,
			$submitted_data,
			$form_settings_instance
		);

		return $entry_fields;
	}

	/**
	 * Add new row of Mailchimp Integration on render entry
	 * subentries that included are:
	 * - Sent To Mailchimp : whether Yes/No, addon send data to Mailchimp API
	 * - Info : Additional info when addon tried to send data to Mailchimp API
	 * - Member Status : Member status that received from Mailchimp API after sending request
	 * - Below subentries will be added if full log enabled, @see Forminator_Addon_Mailchimp::is_show_full_log() @see FORMINATOR_ADDON_MAILCHIMP_SHOW_FULL_LOG
	 *      - API URL : URL that wes requested when sending data to Mailchimp
	 *      - Data sent to Mailchimp : json encoded body request that was sent
	 *      - Data received from Mailchimp : json encoded body response that was received
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param Forminator_Form_Entry_Model $entry_model
	 * @param                             $addon_meta_data
	 *
	 * @return array
	 */
	public function on_render_entry( Forminator_Form_Entry_Model $entry_model, $addon_meta_data ) {

		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;

		/**
		 *
		 * Filter mailchimp metadata that previously saved on db to be processed
		 *
		 * @since 1.1
		 *
		 * @param array                                    $addon_meta_data
		 * @param int                                      $form_id                current Form ID
		 * @param Forminator_Form_Entry_Model              $entry_model            Forminator Entry Model
		 * @param Forminator_Addon_Mailchimp_Form_Settings $form_settings_instance Mailchimp API Form Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_mailchimp_metadata',
			$addon_meta_data,
			$form_id,
			$entry_model,
			$form_settings_instance
		);

		$entry_items = $this->format_metadata_for_entry( $entry_model, $addon_meta_data );

		/**
		 * Filter mailchimp row(s) to be displayed on entries page
		 *
		 * @since 1.1
		 *
		 * @param array                                    $entry_items            row(s) to be displayed on entries page
		 * @param int                                      $form_id                current Form ID
		 * @param Forminator_Form_Entry_Model              $entry_model            Form Entry Model
		 * @param array                                    $addon_meta_data        meta data saved by addon on entry fields
		 * @param Forminator_Addon_Mailchimp_Form_Settings $form_settings_instance Mailchimp API Form Settings instance
		 */
		$entry_items = apply_filters(
			'forminator_addon_mailchimp_entry_items',
			$entry_items,
			$form_id,
			$entry_model,
			$addon_meta_data,
			$form_settings_instance
		);

		return $entry_items;
	}

	/**
	 * Format metadata saved before to be rendered on entry
	 *
	 * @since 1.1
	 *
	 * @param Forminator_Form_Entry_Model $entry_model
	 * @param                             $addon_meta_data
	 *
	 * @return array
	 */
	private function format_metadata_for_entry( Forminator_Form_Entry_Model $entry_model, $addon_meta_data ) {
		// only process first addon meta datas since we only save one
		// no entry fields was added before
		if ( ! isset( $addon_meta_data[0] ) || ! is_array( $addon_meta_data[0] ) ) {
			return array();
		}

		$addon_meta_data = $addon_meta_data[0];

		// make sure its `status`, because we only add this
		if ( 'status' !== $addon_meta_data['name'] ) {
			return array();
		}

		if ( ! isset( $addon_meta_data['value'] ) || ! is_array( $addon_meta_data['value'] ) ) {
			return array();
		}

		$additional_entry_item = array(
			'label' => __( 'Mailchimp Integration', Forminator::DOMAIN ),
			'value' => '',
		);

		$status      = $addon_meta_data['value'];
		$sub_entries = array();
		if ( isset( $status['is_sent'] ) ) {
			$is_sent       = true === $status['is_sent'] ? __( 'Yes', Forminator::DOMAIN ) : __( 'No', Forminator::DOMAIN );
			$sub_entries[] = array(
				'label' => __( 'Sent To Mailchimp', Forminator::DOMAIN ),
				'value' => $is_sent,
			);
		}

		if ( isset( $status['description'] ) ) {
			$sub_entries[] = array(
				'label' => __( 'Info', Forminator::DOMAIN ),
				'value' => $status['description'],
			);
		}

		if ( isset( $status['data_received'] ) && is_object( $status['data_received'] ) ) {
			$data_received = $status['data_received'];
			if ( isset( $data_received->status ) && ! empty( $data_received->status ) && is_string( $data_received->status ) ) {
				$sub_entries[] = array(
					'label' => __( 'Member Status', Forminator::DOMAIN ),
					'value' => strtoupper( $data_received->status ),
				);
			}
		}

		if ( Forminator_Addon_Mailchimp::is_show_full_log() ) {
			// too long to be added on entry data enable this with `define('FORMINATOR_ADDON_MAILCHIMP_SHOW_FULL_LOG', true)`
			if ( isset( $status['url_request'] ) ) {
				$sub_entries[] = array(
					'label' => __( 'API URL', Forminator::DOMAIN ),
					'value' => $status['url_request'],
				);
			}

			if ( isset( $status['data_sent'] ) ) {
				$sub_entries[] = array(
					'label' => __( 'Data sent to Mailchimp', Forminator::DOMAIN ),
					'value' => '<pre class="sui-code-snippet">' . wp_json_encode( $status['data_sent'], JSON_PRETTY_PRINT ) . '</pre>',
				);
			}

			if ( isset( $status['data_received'] ) ) {
				$sub_entries[] = array(
					'label' => __( 'Data received from Mailchimp', Forminator::DOMAIN ),
					'value' => '<pre class="sui-code-snippet">' . wp_json_encode( $status['data_received'], JSON_PRETTY_PRINT ) . '</pre>',
				);
			}
		}

		$additional_entry_item['sub_entries'] = $sub_entries;

		// return single array
		return array( $additional_entry_item );
	}

	/**
	 * Add new Column called `Mailchimp Info` on header of export file
	 *
	 * @since 1.0 Mailchimp Addon
	 * @return array
	 */
	public function on_export_render_title_row() {
		$export_headers = array(
			'info' => 'Mailchimp Info',
		);

		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;

		/**
		 * Filter mailchimp headers on export file
		 *
		 * @since 1.1
		 *
		 * @param array                                    $export_headers         headers to be displayed on export file
		 * @param int                                      $form_id                current Form ID
		 * @param Forminator_Addon_Mailchimp_Form_Settings $form_settings_instance Mailchimp API Form Settings instance
		 */
		$export_headers = apply_filters(
			'forminator_addon_mailchimp_export_headers',
			$export_headers,
			$form_id,
			$form_settings_instance
		);

		return $export_headers;
	}

	/**
	 * Add description of status mailchimp addon after form submitted similar with render entry
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param Forminator_Form_Entry_Model $entry_model
	 * @param                             $addon_meta_data
	 *
	 * @return array
	 */
	public function on_export_render_entry( Forminator_Form_Entry_Model $entry_model, $addon_meta_data ) {

		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;

		/**
		 *
		 * Filter mailchimp metadata that previously saved on db to be processed
		 *
		 * @since 1.1
		 *
		 * @param array                                    $addon_meta_data
		 * @param int                                      $form_id                current Form ID
		 * @param Forminator_Addon_Mailchimp_Form_Settings $form_settings_instance Mailchimp API Form Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_mailchimp_metadata',
			$addon_meta_data,
			$form_id,
			$form_settings_instance
		);

		$export_columns = array(
			'info' => $this->get_from_addon_meta_data( $addon_meta_data, 'description', '' ),
		);

		/**
		 * Filter mailchimp columns to be displayed on export submissions
		 *
		 * @since 1.1
		 *
		 * @param array                                    $export_columns         column to be exported
		 * @param int                                      $form_id                current Form ID
		 * @param Forminator_Form_Entry_Model              $entry_model            Form Entry Model
		 * @param array                                    $addon_meta_data        meta data saved by addon on entry fields
		 * @param Forminator_Addon_Mailchimp_Form_Settings $form_settings_instance Mailchimp API Form Settings instance
		 */
		$export_columns = apply_filters(
			'forminator_addon_mailchimp_export_columns',
			$export_columns,
			$form_id,
			$entry_model,
			$addon_meta_data,
			$form_settings_instance
		);

		return $export_columns;
	}

	/**
	 * Helper to get addon meta data with key specified
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param        $addon_meta_data
	 * @param        $key
	 * @param string $default
	 *
	 * @return string
	 */
	private function get_from_addon_meta_data( $addon_meta_data, $key, $default = '' ) {
		// only process first addon meta datas since we only save one
		// no entry fields was added before
		if ( ! isset( $addon_meta_data[0] ) || ! is_array( $addon_meta_data[0] ) ) {
			return $default;
		}

		$addon_meta_data = $addon_meta_data[0];

		// make sure its `status`, because we only add this
		if ( 'status' !== $addon_meta_data['name'] ) {
			return $default;
		}

		if ( ! isset( $addon_meta_data['value'] ) || ! is_array( $addon_meta_data['value'] ) ) {
			return $default;
		}
		$status = $addon_meta_data['value'];
		if ( isset( $status[ $key ] ) ) {
			return $status[ $key ];
		}

		return $default;
	}

	/**
	 * It will delete members on mailchimp list
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param Forminator_Form_Entry_Model $entry_model
	 * @param  array                      $addon_meta_data
	 *
	 * @return bool
	 */
	public function on_before_delete_entry( Forminator_Form_Entry_Model $entry_model, $addon_meta_data ) {
		// attach hook first
		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;

		/**
		 *
		 * Filter mailchimp addon metadata that previously saved on db to be processed
		 *
		 * Although it can be used for all addon.
		 * Please keep in mind that if the addon override this method,
		 * then this filter probably won't be applied.
		 * To be sure please check individual addon documentations.
		 *
		 * @since 1.1
		 *
		 * @param array                                        $addon_meta_data
		 * @param int                                          $form_id                current Form ID
		 * @param Forminator_Form_Entry_Model                  $entry_model            Forminator Entry Model
		 * @param Forminator_Addon_Form_Settings_Abstract|null $form_settings_instance of Addon Form Settings
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_mailchimp_metadata',
			$addon_meta_data,
			$form_id,
			$entry_model,
			$form_settings_instance
		);

		/**
		 * Fires when mailchimp connected form delete a submission
		 *
		 * Although it can be used for all addon.
		 * Please keep in mind that if the addon override this method,
		 * then this action won't be triggered.
		 * To be sure please check individual addon documentations.
		 *
		 * @since 1.1
		 *
		 * @param int                                          $form_id                current Form ID
		 * @param Forminator_Form_Entry_Model                  $entry_model            Forminator Entry Model
		 * @param array                                        $addon_meta_data        addon meta data
		 * @param Forminator_Addon_Form_Settings_Abstract|null $form_settings_instance of Addon Form Settings
		 */
		do_action(
			'forminator_addon_mailchimp_on_before_delete_submission',
			$form_id,
			$entry_model,
			$addon_meta_data,
			$form_settings_instance
		);

		if ( ! Forminator_Addon_Mailchimp::is_enable_delete_member() ) {
			// its disabled, go for it!
			return true;
		}
		$mailchimp_api = null;
		try {
			$delete_member_url = '';
			/**
			 * Filter delete member url to send to mailchimp api
			 */
			$delete_member_url = apply_filters(
				'forminator_addon_mailchimp_delete_member_url',
				$delete_member_url,
				$form_id,
				$addon_meta_data,
				$form_settings_instance
			);

			if ( empty( $delete_member_url ) ) {
				$delete_member_url = self::get_delete_member_url_from_addon_meta_data( $addon_meta_data );
			}

			forminator_addon_maybe_log( __METHOD__, $delete_member_url );

			if ( ! empty( $delete_member_url ) ) {
				$mailchimp_api = $this->addon->get_api();
				$mailchimp_api->delete_( $delete_member_url );
			}

			return true;

		} catch ( Forminator_Addon_Mailchimp_Wp_Api_Not_Found_Exception $e ) {
			// its not found, probably already deleted on mailchimp
			return true;
		} catch ( Forminator_Addon_Mailchimp_Exception $e ) {
			// handle all internal addon exceptions with `Forminator_Addon_Mailchimp_Exception`

			// use wp_error, for future usage it can be returned to page entries
			$wp_error = new WP_Error( 'forminator_addon_mailchimp_delete_member', $e->getMessage() );
			// handle this in addon by self, since page entries cant handle error messages on delete yet
			wp_die(
				esc_html( $wp_error->get_error_message() ),
				esc_html( $this->addon->get_title() ),
				array(
					'response'  => 200,
					'back_link' => true,
				)
			);

			return false;
		}

	}

	/**
	 * Get valid addon meta data
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param array $addon_meta_data
	 *
	 * @return array
	 */
	public static function get_valid_addon_meta_data_value( $addon_meta_data ) {
		// preliminary check of addon_meta_data
		if ( ! isset( $addon_meta_data[0] ) || ! is_array( $addon_meta_data[0] ) ) {
			return array();
		}

		$addon_meta_data = $addon_meta_data[0];

		// make sure its `status`, because we only add this
		if ( 'status' !== $addon_meta_data['name'] ) {
			return array();
		}
		if ( ! isset( $addon_meta_data['value'] ) || ! is_array( $addon_meta_data['value'] ) ) {
			return array();
		}

		return $addon_meta_data['value'];
	}

	/**
	 * Get DELETE member url form saved addon meta data
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param $addon_meta_data
	 *
	 * @return string
	 */
	public static function get_delete_member_url_from_addon_meta_data( $addon_meta_data ) {

		// delete links available on data_received of mailchimp
		/** == Addon meta data reference ==*/
		//[
		//  {
		//	  "name": "status",
		//    "value": {
		//	  "is_sent": true,
		//      "description": "Successfully added or updated member on Mailchimp list",
		//      "data_sent": {
		//          ...
		//	  },
		//      "data_received": {
		//		  "id": "XXXXXXX",
		//        ...
		//        "list_id": "XXXXXXX",
		//        "_links": [
		//          {
		//	          "rel": "upsert",
		//            "href": "https:\/\/us9.api.mailchimp.com\/3.0\/lists\/XXXXXXX\/members\/XXXXXXX",
		//            "method": "PUT",
		//            "targetSchema": "https:\/\/us9.api.mailchimp.com\/schema\/3.0\/Definitions\/Lists\/Members\/Response.json",
		//            "schema": "https:\/\/us9.api.mailchimp.com\/schema\/3.0\/Definitions\/Lists\/Members\/PUT.json"
		//          },
		//          {
		//	          "rel": "delete",
		//            "href": "https:\/\/us9.api.mailchimp.com\/3.0\/lists\/XXXXXXX\/members\/XXXXXXX",
		//            "method": "DELETE"
		//          },
		//          ...
		//        ]
		//      },
		//      "url_request": "https:\/\/us9.api.mailchimp.com\/3.0\/lists\/XXXX\/members\/XXXXXXX"
		//    }
		//  }
		//]
		/** == Addon meta data reference ==*/

		$delete_member_url = '';

		$meta_data_value = self::get_valid_addon_meta_data_value( $addon_meta_data );
		if ( empty( $meta_data_value ) ) {
			// probably this entry added before connected to mailchimp, mark it as okay to delete entry
			return '';
		}

		if ( isset( $meta_data_value['is_sent'] ) && ! $meta_data_value['is_sent'] ) {
			// its not sent to mailchimp so it won't have delete member uri
			return '';
		}

		if ( ! isset( $meta_data_value['data_received'] ) || ! is_object( $meta_data_value['data_received'] ) ) {
			// something is happened on addon meta data
			return '';
		}

		$data_received = $meta_data_value['data_received'];

		if ( ! isset( $data_received->_links ) || ! is_array( $data_received->_links ) ) {
			// something is happened on addon meta data
			return '';
		}

		foreach ( $data_received->_links as $link ) {
			if ( ! isset( $link->rel ) || ! isset( $link->method ) || ! isset( $link->href ) ) {
				continue;
			}
			if ( 'delete' === $link->rel && 'DELETE' === $link->method ) {
				$delete_member_url = $link->href;
			}
		}

		return $delete_member_url;
	}
}
