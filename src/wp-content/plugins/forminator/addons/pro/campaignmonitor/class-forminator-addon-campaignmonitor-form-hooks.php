<?php

/**
 * Class Forminator_Addon_Campaignmonitor_Form_Hooks
 *
 * @since 1.0 Campaignmonitor Addon
 *
 */
class Forminator_Addon_Campaignmonitor_Form_Hooks extends Forminator_Addon_Form_Hooks_Abstract {

	/**
	 * Addon instance are auto available form abstract
	 * Its added here for development purpose,
	 * Auto-complete will resolve addon directly to `Campaignmonitor` instance instead of the abstract
	 * And its public properties can be exposed
	 *
	 * @since 1.0 Campaignmonitor Addon
	 * @var Forminator_Addon_Campaignmonitor
	 */
	protected $addon;

	/**
	 * Form Settings Instance
	 *
	 * @since 1.0 Campaignmonitor Addon
	 * @var Forminator_Addon_Campaignmonitor_Form_Settings | null
	 */
	protected $form_settings_instance;

	/**
	 * Forminator_Addon_Campaignmonitor_Form_Hooks constructor.
	 *
	 * @since 1.0 Campaignmonitor Addon
	 *
	 * @param Forminator_Addon_Abstract $addon
	 * @param                           $form_id
	 *
	 * @throws Forminator_Addon_Exception
	 */
	public function __construct( Forminator_Addon_Abstract $addon, $form_id ) {
		parent::__construct( $addon, $form_id );
		$this->_submit_form_error_message = __( 'Campaign Monitor failed to process submitted data. Please check your form and try again', Forminator::DOMAIN );
	}

	/**
	 * Save status of request sent and received for each connected Campaign Monitor Connection
	 *
	 * @since 1.0 Campaign Monitor Addon
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
		 * Filter Campaign Monitor submitted form data to be processed
		 *
		 * @since 1.3
		 *
		 * @param array                                          $submitted_data
		 * @param int                                            $form_id                current Form ID
		 * @param Forminator_Addon_Campaignmonitor_Form_Settings $form_settings_instance Campaign Monitor Addon Form Settings instance
		 */
		$submitted_data = apply_filters(
			'forminator_addon_campaignmonitor_form_submitted_data',
			$submitted_data,
			$form_id,
			$form_settings_instance
		);

		forminator_addon_maybe_log( __METHOD__, $submitted_data );

		$addon_setting_values = $this->form_settings_instance->get_form_settings_values();
		$form_settings        = $this->form_settings_instance->get_form_settings();

		$data = array();

		/**
		 * Fires before adding subscriber to Campaign Monitor
		 *
		 * @since 1.3
		 *
		 * @param int                                            $form_id                current Form ID
		 * @param array                                          $submitted_data
		 * @param Forminator_Addon_Campaignmonitor_Form_Settings $form_settings_instance Campaign Monitor Addon Form Settings instance
		 */
		do_action( 'forminator_addon_campaignmonitor_before_add_subscriber', $form_id, $submitted_data, $form_settings_instance );

		foreach ( $addon_setting_values as $key => $addon_setting_value ) {
			// save it on entry field, with name `status-$MULTI_ID`, and value is the return result on sending data to campaign monitor
			if ( $form_settings_instance->is_multi_form_settings_complete( $key ) ) {
				// exec only on completed connection
				$data[] = array(
					'name'  => 'status-' . $key,
					'value' => $this->get_status_on_add_subscriber( $key, $submitted_data, $addon_setting_value, $form_settings, $form_entry_fields ),
				);
			}
		}

		$entry_fields = $data;
		/**
		 * Filter Campaign Monitor entry fields to be saved to entry model
		 *
		 * @since 1.3
		 *
		 * @param array                                          $entry_fields
		 * @param int                                            $form_id                current Form ID
		 * @param array                                          $submitted_data
		 * @param Forminator_Addon_Campaignmonitor_Form_Settings $form_settings_instance Campaign Monitor Addon Form Settings instance
		 */
		$data = apply_filters(
			'forminator_addon_campaignmonitor_entry_fields',
			$entry_fields,
			$form_id,
			$submitted_data,
			$form_settings_instance
		);

		return $data;

	}

	/**
	 * Get status on add subscriber to Campaign Monitor
	 *
	 * @since 1.0 Campaign Monitor Addon
	 * @since 1.7 Add $form_entry_fields args
	 *
	 * @param       $connection_id
	 * @param       $submitted_data
	 * @param       $connection_settings
	 * @param       $form_settings
	 * @param array $form_entry_fields
	 *
	 * @return array `is_sent` true means its success send data to ampaign Monitor, false otherwise
	 */
	private function get_status_on_add_subscriber( $connection_id, $submitted_data, $connection_settings, $form_settings, $form_entry_fields = array() ) {
		// initialize as null
		$api = null;

		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;

		//check required fields
		try {
			$api  = $this->addon->get_api();
			$args = array();

			if ( ! isset( $connection_settings['list_id'] ) ) {
				throw new Forminator_Addon_Campaignmonitor_Exception( __( 'List ID not properly setup.', Forminator::DOMAIN ) );
			}

			$list_id = $connection_settings['list_id'];

			$fields_map = $connection_settings['fields_map'];

			$email_element_id = $connection_settings['fields_map']['default_field_email'];
			if ( ! isset( $submitted_data[ $email_element_id ] ) || empty( $submitted_data[ $email_element_id ] ) ) {
				throw new Forminator_Addon_Campaignmonitor_Exception(/* translators: ... */
					sprintf( __( 'Email Address on element %1$s not found or not filled on submitted data.', Forminator::DOMAIN ), $email_element_id )
				);
			}
			$email = $submitted_data[ $email_element_id ];
			$email = strtolower( trim( $email ) );

			// processed
			unset( $fields_map['default_field_email'] );

			$name_element_id = $connection_settings['fields_map']['default_field_name'];
			if ( self::element_is_calculation( $name_element_id ) ) {
				$meta_value    = self::find_meta_value_from_entry_fields( $name_element_id, $form_entry_fields );
				$element_value = Forminator_Form_Entry_Model::meta_value_to_string( 'calculation', $meta_value );
				$name          = $element_value;
			} elseif ( self::element_is_stripe( $name_element_id ) ) {
				$meta_value    = self::find_meta_value_from_entry_fields( $name_element_id, $form_entry_fields );
				$element_value = Forminator_Form_Entry_Model::meta_value_to_string( 'stripe', $meta_value );
				$name          = $element_value;
			} elseif ( ! isset( $submitted_data[ $name_element_id ] ) || empty( $submitted_data[ $name_element_id ] ) ) {
				throw new Forminator_Addon_Campaignmonitor_Exception(/* translators: ... */
					sprintf( __( 'Name on element %1$s not found or not filled on submitted data.', Forminator::DOMAIN ), $name_element_id )
				);
			}

			if ( isset( $name ) ) {
				$args['Name'] = $name;
			} else {
				$args['Name'] = $submitted_data[ $name_element_id ];
			}

			// processed
			unset( $fields_map['default_field_name'] );

			$custom_fields = array();
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
						$custom_fields[] = array(
							'Key'   => $field_id,
							'Value' => $element_value,
						);
						unset( $element_value ); // unset for next loop
					}
				}
			}
			$args['CustomFields'] = $custom_fields;

			if ( isset( $connection_settings['resubscribe'] ) ) {
				$resubscribe         = filter_var( $connection_settings['resubscribe'], FILTER_VALIDATE_BOOLEAN );
				$args['Resubscribe'] = $resubscribe;
			}

			if ( isset( $connection_settings['restart_subscription_based_autoresponders'] ) ) {
				$restart_subscription_based_autoresponders      = filter_var( $connection_settings['restart_subscription_based_autoresponders'], FILTER_VALIDATE_BOOLEAN );
				$args['RestartSubscriptionBasedAutoresponders'] = $restart_subscription_based_autoresponders;
			}

			if ( isset( $connection_settings['consent_to_track'] ) ) {
				$consent_to_track       = $connection_settings['consent_to_track'];
				$args['ConsentToTrack'] = $consent_to_track;
			}

			/**
			 * Filter arguments to passed on to Add Subscriber Campaign Monitor API
			 *
			 * @since 1.3
			 *
			 * @param array                                          $args
			 * @param int                                            $form_id                Current Form id
			 * @param string                                         $connection_id          ID of current connection
			 * @param array                                          $submitted_data
			 * @param array                                          $connection_settings    current connection setting, contains options of like `name`, `list_id` etc
			 * @param array                                          $form_settings          Displayed Form settings
			 * @param Forminator_Addon_Campaignmonitor_Form_Settings $form_settings_instance Campaign Monitor Addon Form Settings instance
			 */
			$args = apply_filters(
				'forminator_addon_campaignmonitor_add_subscriber_args',
				$args,
				$form_id,
				$connection_id,
				$submitted_data,
				$connection_settings,
				$form_settings,
				$form_settings_instance
			);

			$api->add_subscriber( $list_id, $email, $args );

			forminator_addon_maybe_log( __METHOD__, 'Success Send Data' );
			forminator_addon_maybe_log( __METHOD__, $api->get_last_data_received() );

			return array(
				'is_sent'          => true,
				'connection_name'  => $connection_settings['name'],
				'description'      => __( 'Successfully send data to Campaign Monitor', Forminator::DOMAIN ),
				'data_sent'        => $api->get_last_data_sent(),
				'data_received'    => $api->get_last_data_received(),
				'url_request'      => $api->get_last_url_request(),
				'subscriber_email' => $api->get_last_data_received(), // for delete reference
				'list_id'          => $list_id, // for delete reference
			);

		} catch ( Forminator_Addon_Campaignmonitor_Exception $e ) {
			forminator_addon_maybe_log( __METHOD__, 'Failed to Send to Campaign Monitor' );

			return array(
				'is_sent'         => false,
				'description'     => $e->getMessage(),
				'connection_name' => $connection_settings['name'],
				'data_sent'       => ( ( $api instanceof Forminator_Addon_Campaignmonitor_Wp_Api ) ? $api->get_last_data_sent() : array() ),
				'data_received'   => ( ( $api instanceof Forminator_Addon_Campaignmonitor_Wp_Api ) ? $api->get_last_data_received() : array() ),
				'url_request'     => ( ( $api instanceof Forminator_Addon_Campaignmonitor_Wp_Api ) ? $api->get_last_url_request() : '' ),
			);
		}
	}

	/**
	 * It wil add new row on entry table of submission page, with couple of subentries
	 * subentries included are defined in @see Forminator_Addon_Campaignmonitor_Form_Hooks::get_additional_entry_item()
	 *
	 * @since 1.0 Campaign Monitor Addon
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
		 * Filter active metadata that previously saved on db to be processed
		 *
		 * @since 1.3
		 *
		 * @param array                                          $addon_meta_data
		 * @param int                                            $form_id                current Form ID
		 * @param Forminator_Addon_Campaignmonitor_Form_Settings $form_settings_instance Campaign Monitor Addon Form Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_campaignmonitor_metadata',
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
	 * Loop through addon meta data on multiple campaign monitor(s)
	 *
	 * @since 1.0 Campaign Monitor Addon
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
	 * - Integration Name : its defined by user when they adding Campaign Monitor integration on their form
	 * - Sent To Campaign Monitor : will be Yes/No value, that indicates whether sending data to Campaign Monitor was successful
	 * - Info : Text that are generated by addon when building and sending data to Campaign Monitor @see Forminator_Addon_Campaignmonitor_Form_Hooks::add_entry_fields()
	 * - Below subentries will be added if full log enabled, @see Forminator_Addon_Campaignmonitor::is_show_full_log() @see FORMINATOR_ADDON_CAMPAIGNMONITOR_SHOW_FULL_LOG
	 *      - API URL : URL that wes requested when sending data to Campaign Monitor
	 *      - Data sent to Campaign Monitor : encoded body request that was sent
	 *      - Data received from Campaign Monitor : json encoded body response that was received
	 *
	 * @param $addon_meta_data
	 *
	 * @since 1.0 Campaign Monitor Addon
	 * @return array
	 */
	private function get_additional_entry_item( $addon_meta_data ) {

		if ( ! isset( $addon_meta_data['value'] ) || ! is_array( $addon_meta_data['value'] ) ) {
			return array();
		}
		$status                = $addon_meta_data['value'];
		$additional_entry_item = array(
			'label' => __( 'Campaign Monitor Integration', Forminator::DOMAIN ),
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
				'label' => __( 'Sent To Campaign Monitor', Forminator::DOMAIN ),
				'value' => $is_sent,
			);
		}

		if ( isset( $status['description'] ) ) {
			$sub_entries[] = array(
				'label' => __( 'Info', Forminator::DOMAIN ),
				'value' => $status['description'],
			);
		}

		if ( Forminator_Addon_Campaignmonitor::is_show_full_log() ) {
			// too long to be added on entry data enable this with `define('FORMINATOR_ADDON_CAMPAIGNMONITOR_SHOW_FULL_LOG', true)`
			if ( isset( $status['url_request'] ) ) {
				$sub_entries[] = array(
					'label' => __( 'API URL', Forminator::DOMAIN ),
					'value' => $status['url_request'],
				);
			}

			if ( isset( $status['data_sent'] ) ) {
				$sub_entries[] = array(
					'label' => __( 'Data sent to Campaign Monitor', Forminator::DOMAIN ),
					'value' => '<pre class="sui-code-snippet">' . wp_json_encode( $status['data_sent'], JSON_PRETTY_PRINT ) . '</pre>',
				);
			}

			if ( isset( $status['data_received'] ) ) {
				$sub_entries[] = array(
					'label' => __( 'Data received from Campaign Monitor', Forminator::DOMAIN ),
					'value' => '<pre class="sui-code-snippet">' . wp_json_encode( $status['data_received'], JSON_PRETTY_PRINT ) . '</pre>',
				);
			}
		}

		$additional_entry_item['sub_entries'] = $sub_entries;

		// return single array
		return $additional_entry_item;
	}

	/**
	 * Campaign Monitor will add a column on the title/header row
	 * its called `Campaign Monitor Info` which can be translated on forminator lang
	 *
	 * @since 1.0 Campaign Monitor Addon
	 * @return array
	 */
	public function on_export_render_title_row() {

		$export_headers = array(
			'info' => __( 'Campaign Monitor Info', Forminator::DOMAIN ),
		);

		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;

		/**
		 * Filter Campaign Monitor headers on export file
		 *
		 * @since 1.3
		 *
		 * @param array                                          $export_headers         headers to be displayed on export file
		 * @param int                                            $form_id                current Form ID
		 * @param Forminator_Addon_Campaignmonitor_Form_Settings $form_settings_instance Campaign Monitor Addon Form Settings instance
		 */
		$export_headers = apply_filters(
			'forminator_addon_campaignmonitor_export_headers',
			$export_headers,
			$form_id,
			$form_settings_instance
		);

		return $export_headers;
	}

	/**
	 * Campaign Monitor will add a column that give user information whether sending data to Campaign Monitor successfully or not
	 * It will only add one column even its multiple connection, every connection will be separated by comma
	 *
	 * @since 1.0 Campaign Monitor Addon
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
		 * Filter Campaign Monitor metadata that previously saved on db to be processed
		 *
		 * @since 1.3
		 *
		 * @param array                                          $addon_meta_data
		 * @param int                                            $form_id                current Form ID
		 * @param Forminator_Addon_Campaignmonitor_Form_Settings $form_settings_instance Campaign Monitor Addon Form Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_campaignmonitor_metadata',
			$addon_meta_data,
			$form_id,
			$form_settings_instance
		);

		$export_columns = array(
			'info' => $this->get_from_addon_meta_data( $addon_meta_data, 'description', '' ),
		);

		/**
		 * Filter Campaign Monitor columns to be displayed on export submissions
		 *
		 * @since 1.3
		 *
		 * @param array                                          $export_columns         column to be exported
		 * @param int                                            $form_id                current Form ID
		 * @param Forminator_Form_Entry_Model                    $entry_model            Form Entry Model
		 * @param array                                          $addon_meta_data        meta data saved by addon on entry fields
		 * @param Forminator_Addon_Campaignmonitor_Form_Settings $form_settings_instance Campaign Monitor Addon Form Settings instance
		 */
		$export_columns = apply_filters(
			'forminator_addon_campaignmonitor_export_columns',
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
	 * @since 1.0 Campaign Monitor Addon
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
	 * It will delete subscriber on Campaign Monitor from list
	 *
	 * @since 1.0 Campaign Monitor Addon
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
		 * Filter Campaign Monitor addon metadata that previously saved on db to be processed
		 *
		 * @since 1.1
		 *
		 * @param array                                          $addon_meta_data
		 * @param int                                            $form_id                current Form ID
		 * @param Forminator_Form_Entry_Model                    $entry_model            Forminator Entry Model
		 * @param Forminator_Addon_Campaignmonitor_Form_Settings $form_settings_instance Campaign Monitor Addon Form Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_campaignmonitor_metadata',
			$addon_meta_data,
			$form_id,
			$entry_model,
			$form_settings_instance
		);

		/**
		 * Fires when Campaign Monitor connected form delete a submission
		 *
		 * @since 1.1
		 *
		 * @param int                                            $form_id                current Form ID
		 * @param Forminator_Form_Entry_Model                    $entry_model            Forminator Entry Model
		 * @param array                                          $addon_meta_data        addon meta data
		 * @param Forminator_Addon_Campaignmonitor_Form_Settings $form_settings_instance Campaign Monitor Addon Form Settings instance
		 */
		do_action(
			'forminator_addon_campaignmonitor_on_before_delete_submission',
			$form_id,
			$entry_model,
			$addon_meta_data,
			$form_settings_instance
		);

		if ( ! Forminator_Addon_Campaignmonitor::is_enable_delete_subscriber() ) {
			// its disabled, go for it!
			return true;
		}
		$api = null;
		try {
			$subscribers_to_delete = array();

			if ( is_array( $addon_meta_data ) ) {
				foreach ( $addon_meta_data as $addon_meta_datum ) {

					if ( isset( $addon_meta_datum['value'] ) && is_array( $addon_meta_datum['value'] ) ) {
						$addon_meta_datum_value = $addon_meta_datum['value'];
						if ( isset( $addon_meta_datum_value['is_sent'] ) && $addon_meta_datum_value['is_sent'] ) {
							if ( isset( $addon_meta_datum_value['list_id'] ) && ! empty( $addon_meta_datum_value['list_id'] )
								&& isset( $addon_meta_datum_value['subscriber_email'] )
								&& ! empty( $addon_meta_datum_value['subscriber_email'] ) ) {
								$subscribers_to_delete[] = array(
									'list_id' => $addon_meta_datum_value['list_id'],
									'email'   => $addon_meta_datum_value['subscriber_email'],
								);
							}
						}
					}
				}
			}

			/**
			 * Filter subscribers to delete
			 *
			 * @since 1.3
			 *
			 * @param array                                          $subscriber_ids_to_delete
			 * @param int                                            $form_id                current Form ID
			 * @param array                                          $addon_meta_data        addon meta data
			 * @param Forminator_Addon_Campaignmonitor_Form_Settings $form_settings_instance Campaign Monitor Addon Form Settings instance
			 *
			 */
			$subscribers_to_delete = apply_filters(
				'forminator_addon_campaignmonitor_subscribers_to_delete',
				$subscribers_to_delete,
				$form_id,
				$addon_meta_data,
				$form_settings_instance
			);

			if ( ! empty( $subscribers_to_delete ) ) {
				$api = $this->addon->get_api();
				foreach ( $subscribers_to_delete as $subscriber ) {

					if ( isset( $subscriber['list_id'] ) && isset( $subscriber['email'] ) ) {
						$api->delete_subscriber( $subscriber['list_id'], $subscriber['email'] );
					}
				}
			}

			return true;

		} catch ( Forminator_Addon_Campaignmonitor_Exception $e ) {
			// handle all internal addon exceptions with `Forminator_Addon_Campaignmonitor_Exception`

			// use wp_error, for future usage it can be returned to page entries
			$wp_error = new WP_Error( 'forminator_addon_campaignmonitor_delete_subscriber', $e->getMessage() );
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
