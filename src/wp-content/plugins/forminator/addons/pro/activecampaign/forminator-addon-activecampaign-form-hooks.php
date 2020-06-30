<?php

/**
 * Class Forminator_Addon_Activecampaign_Form_Hooks
 *
 * @since 1.0 Activecampaign Addon
 *
 */
class Forminator_Addon_Activecampaign_Form_Hooks extends Forminator_Addon_Form_Hooks_Abstract {

	/**
	 * Addon instance are auto available form abstract
	 * Its added here for development purpose,
	 * Auto-complete will resolve addon directly to `Activecampaign` instance instead of the abstract
	 * And its public properties can be exposed
	 *
	 * @since 1.0 Activecampaign Addon
	 * @var Forminator_Addon_Activecampaign
	 */
	protected $addon;

	/**
	 * Form Settings Instance
	 *
	 * @since 1.0 Activecampaign Addon
	 * @var Forminator_Addon_Activecampaign_Form_Settings | null
	 */
	protected $form_settings_instance;

	/**
	 * Forminator_Addon_Activecampaign_Form_Hooks constructor.
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
		$this->_submit_form_error_message = __( 'ActiveCampaign failed to process submitted data. Please check your form and try again', Forminator::DOMAIN );
	}

	/**
	 * Save status of request sent and received for each connected Active Campaign Connection
	 *
	 * @since 1.0 Activecampaign Addon
	 * @since 1.7 Add $form_entry_fields
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
		 * Filter ActiveCampaign submitted form data to be processed
		 *
		 * @since 1.2
		 *
		 * @param array                                         $submitted_data
		 * @param int                                           $form_id                current Form ID
		 * @param Forminator_Addon_Activecampaign_Form_Settings $form_settings_instance ActiveCampaign Addon Form Settings instance
		 */
		$submitted_data = apply_filters(
			'forminator_addon_activecampaign_form_submitted_data',
			$submitted_data,
			$form_id,
			$form_settings_instance
		);

		forminator_addon_maybe_log( __METHOD__, $submitted_data );

		$addon_setting_values = $this->form_settings_instance->get_form_settings_values();
		$form_settings        = $this->form_settings_instance->get_form_settings();

		$data = array();

		/**
		 * Fires before sending contact sync to Active Campaign
		 *
		 * @since 1.2
		 *
		 * @param int                                           $form_id                current Form ID
		 * @param array                                         $submitted_data
		 * @param Forminator_Addon_Activecampaign_Form_Settings $form_settings_instance ActiveCampaign Addon Form Settings instance
		 */
		do_action( 'forminator_addon_activecampaign_before_contact_sync', $form_id, $submitted_data, $form_settings_instance );

		foreach ( $addon_setting_values as $key => $addon_setting_value ) {
			// save it on entry field, with name `status-$MULTI_ID`, and value is the return result on sending data to active campaign
			if ( $form_settings_instance->is_multi_form_settings_complete( $key ) ) {
				// exec only on completed connection
				$data[] = array(
					'name'  => 'status-' . $key,
					'value' => $this->get_status_on_contact_sync( $key, $submitted_data, $addon_setting_value, $form_settings, $form_entry_fields ),
				);
			}

		}

		$entry_fields = $data;
		/**
		 * Filter ActiveCampaign entry fields to be saved to entry model
		 *
		 * @since 1.2
		 *
		 * @param array                                         $entry_fields
		 * @param int                                           $form_id                current Form ID
		 * @param array                                         $submitted_data
		 * @param Forminator_Addon_Activecampaign_Form_Settings $form_settings_instance ActiveCampaign Addon Form Settings instance
		 */
		$data = apply_filters(
			'forminator_addon_activecampaign_entry_fields',
			$entry_fields,
			$form_id,
			$submitted_data,
			$form_settings_instance
		);

		return $data;

	}

	/**
	 * Get status on contact sync to ActiveCampaign
	 *
	 * @since 1.0 Activecampaign Addon
	 * @since 1.7 Add $form_entry_fields
	 *
	 * @param       $connection_id
	 * @param       $submitted_data
	 * @param       $connection_settings
	 * @param       $form_settings
	 * @param array $form_entry_fields
	 *
	 * @return array `is_sent` true means its success send data to ActiveCampaign, false otherwise
	 */
	private function get_status_on_contact_sync( $connection_id, $submitted_data, $connection_settings, $form_settings, $form_entry_fields ) {
		// initialize as null
		$ac_api = null;

		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;

		//check required fields
		try {
			$ac_api = $this->addon->get_api();
			$args   = array();

			if ( ! isset( $connection_settings['list_id'] ) ) {
				throw new Forminator_Addon_Activecampaign_Exception( __( 'List ID not properly setup.', Forminator::DOMAIN ) );
			}

			$args[ 'p[' . $connection_settings['list_id'] . ']' ] = $connection_settings['list_id'];
			// subscribed
			$args[ 'status[' . $connection_settings['list_id'] . ']' ] = '1';

			$fields_map = $connection_settings['fields_map'];

			$email_element_id = $connection_settings['fields_map']['email'];
			if ( ! isset( $submitted_data[ $email_element_id ] ) || empty( $submitted_data[ $email_element_id ] ) ) {
				throw new Forminator_Addon_Activecampaign_Exception( __( 'Email on element %1$s not found or not filled on submitted data.', Forminator::DOMAIN ) );
			}
			$email         = $submitted_data[ $email_element_id ];
			$email         = strtolower( trim( $email ) );
			$args['email'] = $email;

			// processed
			unset( $fields_map['email'] );

			$common_fields = array(
				'first_name',
				'last_name',
				'phone',
				'orgname',
			);

			foreach ( $common_fields as $common_field ) {
				// not setup
				if ( ! isset( $fields_map[ $common_field ] ) ) {
					continue;
				}

				if ( ! empty( $fields_map[ $common_field ] ) ) {
					$element_id = $fields_map[ $common_field ];

					if ( self::element_is_calculation( $element_id ) ) {
						$meta_value    = self::find_meta_value_from_entry_fields( $element_id, $form_entry_fields );
						$element_value = Forminator_Form_Entry_Model::meta_value_to_string( 'calculation', $meta_value );
					} elseif ( self::element_is_stripe( $element_id ) ) {
						$meta_value    = self::find_meta_value_from_entry_fields( $element_id, $form_entry_fields );
						$element_value = Forminator_Form_Entry_Model::meta_value_to_string( 'stripe', $meta_value );
					} elseif ( isset( $submitted_data[ $element_id ] ) && ! empty( $submitted_data[ $element_id ] ) ) {
						$element_value = $submitted_data[ $element_id ];
						if ( is_array( $element_value ) ) {
							$element_value = implode( ',', $element_value );
						}
					}
					if ( isset( $element_value ) ) {
						$args[ $common_field ] = $element_value;
						unset( $element_value ); // unset for next loop
					}

				}
				// processed
				unset( $fields_map[ $common_field ] );
			}

			// process rest extra fields if available
			foreach ( $fields_map as $field_id => $element_id ) {
				if ( ! empty( $element_id ) ) {
					if ( self::element_is_calculation( $element_id ) ) {
						$meta_value    = self::find_meta_value_from_entry_fields( $element_id, $form_entry_fields );
						$element_value = Forminator_Form_Entry_Model::meta_value_to_string( 'calculation', $meta_value );
					} elseif ( self::element_is_stripe( $element_id ) ) {
						$meta_value    = self::find_meta_value_from_entry_fields( $element_id, $form_entry_fields );
						$element_value = Forminator_Form_Entry_Model::meta_value_to_string( 'stripe', $meta_value );
					} elseif ( isset( $submitted_data[ $element_id ] ) && ! empty( $submitted_data[ $element_id ] ) ) {
						$element_value = $submitted_data[ $element_id ];
						if ( is_array( $element_value ) ) {
							$element_value = implode( ',', $element_value );
						}
					}

					if ( isset( $element_value ) ) {
						$args[ 'field[' . $field_id . ',0]' ] = $element_value;
						unset( $element_value ); // unset for next loop
					}
				}
			}

			// process tags
			if ( isset( $connection_settings['tags'] ) && is_array( $connection_settings['tags'] ) ) {
				$tags = array();
				foreach ( $connection_settings['tags'] as $tag ) {
					if ( stripos( $tag, '{' ) === 0
					     && stripos( $tag, '}' ) === ( strlen( $tag ) - 1 )
					) {
						// translate to value
						$element_id = str_ireplace( '{', '', $tag );
						$element_id = str_ireplace( '}', '', $element_id );
						if ( self::element_is_calculation( $element_id ) ) {
							$meta_value    = self::find_meta_value_from_entry_fields( $element_id, $form_entry_fields );
							$element_value = Forminator_Form_Entry_Model::meta_value_to_string( 'calculation', $meta_value );
						} elseif ( self::element_is_stripe( $element_id ) ) {
							$meta_value    = self::find_meta_value_from_entry_fields( $element_id, $form_entry_fields );
							$element_value = Forminator_Form_Entry_Model::meta_value_to_string( 'stripe', $meta_value );
						} elseif ( isset( $submitted_data[ $element_id ] ) && ! empty( $submitted_data[ $element_id ] ) ) {
							$element_value = $submitted_data[ $element_id ];
							if ( is_array( $element_value ) ) {
								$element_value = implode( ',', $element_value );
							}

						}

						if ( isset( $element_value ) ) {
							$tags[] = $element_value;
							unset( $element_value ); // unset for next loop
						}


					} else {
						$tags[] = $tag;
					}
				}
				if ( ! empty( $tags ) ) {
					$tags         = implode( ',', $tags );
					$args['tags'] = $tags;
				}
			}

			if ( isset( $connection_settings['double_opt_form_id'] ) && ! empty( $connection_settings['double_opt_form_id'] ) ) {
				$args['form'] = $connection_settings['double_opt_form_id'];
			}

			if ( isset( $connection_settings['instantresponders'] ) ) {
				$instant_responders = filter_var( $connection_settings['instantresponders'], FILTER_VALIDATE_BOOLEAN );
				if ( $instant_responders ) {
					$args[ 'instantresponders[' . $connection_settings['list_id'] . ']' ] = '1';
				}
			}

			if ( isset( $connection_settings['lastmessage'] ) ) {
				$last_message = filter_var( $connection_settings['lastmessage'], FILTER_VALIDATE_BOOLEAN );
				if ( $last_message ) {
					$args[ 'lastmessage[' . $connection_settings['list_id'] . ']' ] = '1';
				}
			}

			/**
			 * Filter arguments to passed on to Contact Sync Active Campaign API
			 *
			 * @since 1.2
			 *
			 * @param array                                         $args
			 * @param int                                           $form_id                Current Form id
			 * @param string                                        $connection_id          ID of current connection
			 * @param array                                         $submitted_data
			 * @param array                                         $connection_settings    current connection setting, contains options of like `name`, `list_id` etc
			 * @param array                                         $form_settings          Displayed Form settings
			 * @param Forminator_Addon_Activecampaign_Form_Settings $form_settings_instance ActiveCampaign Addon Form Settings instance
			 */
			$args = apply_filters(
				'forminator_addon_activecampaign_contact_sync_args',
				$args,
				$form_id,
				$connection_id,
				$submitted_data,
				$connection_settings,
				$form_settings,
				$form_settings_instance
			);

			$ac_api->contact_sync( $args );

			forminator_addon_maybe_log( __METHOD__, 'Success Send Data' );

			return array(
				'is_sent'         => true,
				'connection_name' => $connection_settings['name'],
				'description'     => __( 'Successfully send data to ActiveCampaign', Forminator::DOMAIN ),
				'data_sent'       => $ac_api->get_last_data_sent(),
				'data_received'   => $ac_api->get_last_data_received(),
				'url_request'     => $ac_api->get_last_url_request(),
			);

		} catch ( Forminator_Addon_Activecampaign_Exception $e ) {
			forminator_addon_maybe_log( __METHOD__, 'Failed to Send to ActiveCampaign' );

			return array(
				'is_sent'         => false,
				'description'     => $e->getMessage(),
				'connection_name' => $connection_settings['name'],
				'data_sent'       => ( ( $ac_api instanceof Forminator_Addon_Activecampaign_Wp_Api ) ? $ac_api->get_last_data_sent() : array() ),
				'data_received'   => ( ( $ac_api instanceof Forminator_Addon_Activecampaign_Wp_Api ) ? $ac_api->get_last_data_received() : array() ),
				'url_request'     => ( ( $ac_api instanceof Forminator_Addon_Activecampaign_Wp_Api ) ? $ac_api->get_last_url_request() : '' ),
			);
		}
	}

	/**
	 * It wil add new row on entry table of submission page, with couple of subentries
	 * subentries included are defined in @see Forminator_Addon_Activecampaign_Form_Hooks::get_additional_entry_item()
	 *
	 * @since 1.0 Activecampaign Addon
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
		 * Filter active campaign metadata that previously saved on db to be processed
		 *
		 * @since 1.2
		 *
		 * @param array                                         $addon_meta_data
		 * @param int                                           $form_id                current Form ID
		 * @param Forminator_Addon_Activecampaign_Form_Settings $form_settings_instance ActiveCampaign Addon Form Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_activecampaign_metadata',
			$addon_meta_data,
			$form_id,
			$form_settings_instance
		);


		$addon_meta_datas = $addon_meta_data;
		if ( ! isset( $addon_meta_data[0] ) || ! is_array( $addon_meta_data[0] ) ) {
			return array();
		}

		return $this->on_render_entry_multi_connection( $addon_meta_datas );

	}

	/**
	 * Loop through addon meta data on multiple active campaign(s)
	 *
	 * @since 1.0 Activecampaign Addon
	 *
	 * @param $addon_meta_datas
	 *
	 * @return array
	 */
	private function on_render_entry_multi_connection( $addon_meta_datas ) {
		$additional_entry_item = array();
		foreach ( $addon_meta_datas as $addon_meta_data ) {
			$additional_entry_item[] = $this->get_additional_entry_item( $addon_meta_data );
		}

		return $additional_entry_item;

	}

	/**
	 * Format additional entry item as label and value arrays
	 *
	 * - Integration Name : its defined by user when they adding Activecampaign integration on their form
	 * - Sent To Activecampaign : will be Yes/No value, that indicates whether sending data to Activecampaign was successful
	 * - Info : Text that are generated by addon when building and sending data to Activecampaign @see Forminator_Addon_Activecampaign_Form_Hooks::add_entry_fields()
	 * - Below subentries will be added if full log enabled, @see Forminator_Addon_Activecampaign::is_show_full_log() @see FORMINATOR_ADDON_ACTIVECAMPAIGN_SHOW_FULL_LOG
	 *      - API URL : URL that wes requested when sending data to Activecampaign
	 *      - Data sent to Activecampaign : encoded body request that was sent
	 *      - Data received from Activecampaign : json encoded body response that was received
	 *
	 * @param $addon_meta_data
	 *
	 * @since 1.0 Activecampaign Addon
	 * @return array
	 */
	private function get_additional_entry_item( $addon_meta_data ) {

		if ( ! isset( $addon_meta_data['value'] ) || ! is_array( $addon_meta_data['value'] ) ) {
			return array();
		}
		$status                = $addon_meta_data['value'];
		$additional_entry_item = array(
			'label' => __( 'ActiveCampaign Integration', Forminator::DOMAIN ),
			'value' => '',
		);


		$sub_entries = array();
		if ( isset( $status['connection_name'] ) ) {
			$sub_entries[] = array(
				'label' => __( 'Integration Name', Forminator::DOMAIN ),
				'value' => $status['connection_name'],
			);
		}

		if ( isset( $status['is_sent'] ) ) {
			$is_sent       = true === $status['is_sent'] ? __( 'Yes', Forminator::DOMAIN ) : __( 'No', Forminator::DOMAIN );
			$sub_entries[] = array(
				'label' => __( 'Sent To ActiveCampaign', Forminator::DOMAIN ),
				'value' => $is_sent,
			);
		}

		if ( isset( $status['description'] ) ) {
			$sub_entries[] = array(
				'label' => __( 'Info', Forminator::DOMAIN ),
				'value' => $status['description'],
			);
		}

		if ( Forminator_Addon_Activecampaign::is_show_full_log() ) {
			// too long to be added on entry data enable this with `define('FORMINATOR_ADDON_ACTIVECAMPAIGN_SHOW_FULL_LOG', true)`
			if ( isset( $status['url_request'] ) ) {
				$sub_entries[] = array(
					'label' => __( 'API URL', Forminator::DOMAIN ),
					'value' => $status['url_request'],
				);
			}

			if ( isset( $status['data_sent'] ) ) {
				$sub_entries[] = array(
					'label' => __( 'Data sent to ActiveCampaign', Forminator::DOMAIN ),
					'value' => '<pre class="sui-code-snippet">' . wp_json_encode( $status['data_sent'], JSON_PRETTY_PRINT ) . '</pre>',
				);
			}

			if ( isset( $status['data_received'] ) ) {
				$sub_entries[] = array(
					'label' => __( 'Data received from ActiveCampaign', Forminator::DOMAIN ),
					'value' => '<pre class="sui-code-snippet">' . wp_json_encode( $status['data_received'], JSON_PRETTY_PRINT ) . '</pre>',
				);
			}
		}


		$additional_entry_item['sub_entries'] = $sub_entries;

		// return single array
		return $additional_entry_item;
	}

	/**
	 * Activecampaign will add a column on the title/header row
	 * its called `Active Campaign Info` which can be translated on forminator lang
	 *
	 * @since 1.0 Activecampaign Addon
	 * @return array
	 */
	public function on_export_render_title_row() {

		$export_headers = array(
			'info' => __( 'ActiveCampaign Info', Forminator::DOMAIN ),
		);

		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;

		/**
		 * Filter Activecampaign headers on export file
		 *
		 * @since 1.2
		 *
		 * @param array                                         $export_headers         headers to be displayed on export file
		 * @param int                                           $form_id                current Form ID
		 * @param Forminator_Addon_Activecampaign_Form_Settings $form_settings_instance Activecampaign Form Settings instance
		 */
		$export_headers = apply_filters(
			'forminator_addon_activecampaign_export_headers',
			$export_headers,
			$form_id,
			$form_settings_instance
		);

		return $export_headers;
	}

	/**
	 * Activecampaign will add a column that give user information whether sending data to Activecampaign successfully or not
	 * It will only add one column even its multiple connection, every connection will be separated by comma
	 *
	 * @since 1.0 Activecampaign Addon
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
		 * Filter Activecampaign metadata that previously saved on db to be processed
		 *
		 * @since 1.2
		 *
		 * @param array                                         $addon_meta_data
		 * @param int                                           $form_id                current Form ID
		 * @param Forminator_Addon_Activecampaign_Form_Settings $form_settings_instance Activecampaign Form Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_activecampaign_metadata',
			$addon_meta_data,
			$form_id,
			$form_settings_instance
		);

		$export_columns = array(
			'info' => $this->get_from_addon_meta_data( $addon_meta_data, 'description', '' ),
		);

		/**
		 * Filter Activecampaign columns to be displayed on export submissions
		 *
		 * @since 1.2
		 *
		 * @param array                                         $export_columns         column to be exported
		 * @param int                                           $form_id                current Form ID
		 * @param Forminator_Form_Entry_Model                   $entry_model            Form Entry Model
		 * @param array                                         $addon_meta_data        meta data saved by addon on entry fields
		 * @param Forminator_Addon_Activecampaign_Form_Settings $form_settings_instance Activecampaign Form Settings instance
		 */
		$export_columns = apply_filters(
			'forminator_addon_activecampaign_export_columns',
			$export_columns,
			$form_id,
			$entry_model,
			$addon_meta_data,
			$form_settings_instance
		);

		return $export_columns;
	}

	/**
	 * Get Addon meta data, will be recursive if meta data is multiple because of multiple connection added
	 *
	 * @since 1.0 Activecampaign Addon
	 *
	 * @param        $addon_meta_data
	 * @param        $key
	 * @param string $default
	 *
	 * @return string
	 */
	private function get_from_addon_meta_data( $addon_meta_data, $key, $default = '' ) {
		$addon_meta_datas = $addon_meta_data;
		if ( ! isset( $addon_meta_data[0] ) || ! is_array( $addon_meta_data[0] ) ) {
			return $default;
		}

		$addon_meta_data = $addon_meta_data[0];

		// make sure its `status`, because we only add this
		if ( 'status' !== $addon_meta_data['name'] ) {
			if ( stripos( $addon_meta_data['name'], 'status-' ) === 0 ) {
				$meta_data = array();
				foreach ( $addon_meta_datas as $addon_meta_data ) {
					// make it like single value so it will be processed like single meta data
					$addon_meta_data['name'] = 'status';

					// add it on an array for next recursive process
					$meta_data[] = $this->get_from_addon_meta_data( array( $addon_meta_data ), $key, $default );
				}

				return implode( ', ', $meta_data );
			}

			return $default;

		}

		if ( ! isset( $addon_meta_data['value'] ) || ! is_array( $addon_meta_data['value'] ) ) {
			return $default;
		}
		$status = $addon_meta_data['value'];
		if ( isset( $status[ $key ] ) ) {
			$connection_name = '';
			if ( 'connection_name' !== $key ) {
				if ( isset( $status['connection_name'] ) ) {
					$connection_name = '[' . $status['connection_name'] . '] ';
				}
			}

			return $connection_name . $status[ $key ];
		}

		return $default;
	}

	/**
	 * It will delete contact on ActiveCampaign list
	 *
	 * @since 1.0 ActiveCampaign Addon
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
		 * Filter ActiveCampaign addon metadata that previously saved on db to be processed
		 *
		 * @since 1.1
		 *
		 * @param array                                         $addon_meta_data
		 * @param int                                           $form_id                current Form ID
		 * @param Forminator_Form_Entry_Model                   $entry_model            Forminator Entry Model
		 * @param Forminator_Addon_Activecampaign_Form_Settings $form_settings_instance Activecampaign Form Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_activecampaign_metadata',
			$addon_meta_data,
			$form_id,
			$entry_model,
			$form_settings_instance
		);

		/**
		 * Fires when Activecampaign connected form delete a submission
		 *
		 * @since 1.1
		 *
		 * @param int                                           $form_id                current Form ID
		 * @param Forminator_Form_Entry_Model                   $entry_model            Forminator Entry Model
		 * @param array                                         $addon_meta_data        addon meta data
		 * @param Forminator_Addon_Activecampaign_Form_Settings $form_settings_instance Activecampaign Form Settings instance
		 */
		do_action(
			'forminator_addon_activecampaign_on_before_delete_submission',
			$form_id,
			$entry_model,
			$addon_meta_data,
			$form_settings_instance
		);

		if ( ! Forminator_Addon_Activecampaign::is_enable_delete_contact() ) {
			// its disabled, go for it!
			return true;
		}
		$ac_api = null;
		try {
			$subscriber_ids_to_delete = array();

			if ( is_array( $addon_meta_data ) ) {
				foreach ( $addon_meta_data as $addon_meta_datum ) {

					/** data received reference
					 *
					 * data_received: {
					 *      subscriber_id: 1,
					 *      sendlast_should: 0,
					 *      sendlast_did: 0,
					 *      result_code: 1,
					 *      result_message: Contact added,
					 *      result_output: json
					 * }
					 */

					if ( isset( $addon_meta_datum['value'] ) && is_array( $addon_meta_datum['value'] ) ) {
						$addon_meta_datum_value = $addon_meta_datum['value'];
						if ( isset( $addon_meta_datum_value['is_sent'] ) && $addon_meta_datum_value['is_sent'] ) {
							if ( isset( $addon_meta_datum_value['data_received'] ) && is_object( $addon_meta_datum_value['data_received'] ) ) {
								$addon_meta_datum_received = $addon_meta_datum_value['data_received'];
								if ( isset( $addon_meta_datum_received->subscriber_id ) && ! empty( $addon_meta_datum_received->subscriber_id ) ) {
									$subscriber_ids_to_delete [] = $addon_meta_datum_received->subscriber_id;
								}
							}
						}
					}

				}
			}

			/**
			 * Filter subscriber ids to delete
			 *
			 * @since 1.2
			 *
			 * @param array                                         $subscriber_ids_to_delete
			 * @param int                                           $form_id                current Form ID
			 * @param array                                         $addon_meta_data        addon meta data
			 * @param Forminator_Addon_Activecampaign_Form_Settings $form_settings_instance Activecampaign Form Settings instance
			 *
			 */
			$subscriber_ids_to_delete = apply_filters(
				'forminator_addon_activecampaign_subscriber_ids_to_delete',
				$subscriber_ids_to_delete,
				$form_id,
				$addon_meta_data,
				$form_settings_instance
			);

			if ( ! empty( $subscriber_ids_to_delete ) ) {
				$ac_api = $this->addon->get_api();
				foreach ( $subscriber_ids_to_delete as $subscriber_id_to_delete ) {
					$ac_api->contact_delete(
						array(
							'id' => $subscriber_id_to_delete,
						)
					);
				}
			}

			return true;

		} catch ( Forminator_Addon_Activecampaign_Exception $e ) {
			// handle all internal addon exceptions with `Forminator_Addon_Activecampaign_Exception`

			// use wp_error, for future usage it can be returned to page entries
			$wp_error = new WP_Error( 'forminator_addon_activecampaign_delete_contact', $e->getMessage() );
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
}
