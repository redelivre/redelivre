<?php

/**
 * Class Forminator_Addon_Slack_Form_Hooks
 *
 * @since 1.0 Slack Addon
 *
 */
class Forminator_Addon_Slack_Form_Hooks extends Forminator_Addon_Form_Hooks_Abstract {

	/**
	 * Addon instance are auto available form abstract
	 * Its added here for development purpose,
	 * Auto-complete will resolve addon directly to `Slack` instance instead of the abstract
	 * And its public properties can be exposed
	 *
	 * @since 1.0 Slack Addon
	 * @var Forminator_Addon_Slack
	 */
	protected $addon;

	/**
	 * Form Settings Instance
	 *
	 * @since 1.0 Slack Addon
	 * @var Forminator_Addon_Slack_Form_Settings | null
	 */
	protected $form_settings_instance;

	/**
	 * Forminator_Addon_Slack_Form_Hooks constructor.
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
		$this->_submit_form_error_message = __( 'Slack failed to process submitted data. Please check your form and try again', Forminator::DOMAIN );
	}

	/**
	 * Save status of request sent and received for each connected Slack Connection
	 *
	 * @since 1.0 Slack Addon
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
		 * Filter Slack submitted form data to be processed
		 *
		 * @since 1.4
		 *
		 * @param array                                $submitted_data
		 * @param int                                  $form_id                current Form ID
		 * @param Forminator_Addon_Slack_Form_Settings $form_settings_instance Slack Addon Form Settings instance
		 */
		$submitted_data = apply_filters(
			'forminator_addon_slack_form_submitted_data',
			$submitted_data,
			$form_id,
			$form_settings_instance
		);

		$addon_setting_values = $this->form_settings_instance->get_form_settings_values();

		$data = array();

		/**
		 * Fires before send message to Slack
		 *
		 * @since 1.4
		 *
		 * @param int                                  $form_id                current Form ID
		 * @param array                                $submitted_data
		 * @param Forminator_Addon_Slack_Form_Settings $form_settings_instance Slack Addon Form Settings instance
		 */
		do_action( 'forminator_addon_slack_before_send_message', $form_id, $submitted_data, $form_settings_instance );

		foreach ( $addon_setting_values as $key => $addon_setting_value ) {
			// save it on entry field, with name `status-$MULTI_ID`, and value is the return result on sending data to slack
			if ( $form_settings_instance->is_multi_form_settings_complete( $key ) ) {
				// exec only on completed connection
				$data[] = array(
					'name'  => 'status-' . $key,
					'value' => $this->get_status_on_send_message( $key, $submitted_data, $addon_setting_value, $form_entry_fields ),
				);
			}

		}

		$entry_fields = $data;
		/**
		 * Filter Slack entry fields to be saved to entry model
		 *
		 * @since 1.4
		 *
		 * @param array                                $entry_fields
		 * @param int                                  $form_id                current Form ID
		 * @param array                                $submitted_data
		 * @param Forminator_Addon_Slack_Form_Settings $form_settings_instance Slack Addon Form Settings instance
		 */
		$data = apply_filters(
			'forminator_addon_slack_entry_fields',
			$entry_fields,
			$form_id,
			$submitted_data,
			$form_settings_instance
		);

		return $data;

	}

	/**
	 * Get status on send message to Slack
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param $connection_id
	 * @param $submitted_data
	 * @param $connection_settings
	 * @param $form_entry_fields
	 *
	 * @return array `is_sent` true means its success send data to Slack, false otherwise
	 */
	private function get_status_on_send_message( $connection_id, $submitted_data, $connection_settings, $form_entry_fields ) {
		// initialize as null
		$api = null;

		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;
		$form_settings          = $this->form_settings_instance->get_form_settings();

		//check required fields
		try {
			$api  = $this->addon->get_api();
			$args = array();

			if ( ! isset( $connection_settings['target_id'] ) ) {
				throw new Forminator_Addon_Slack_Exception( __( 'Target ID not properly setup.', Forminator::DOMAIN ) );
			}

			if ( ! isset( $connection_settings['message'] ) ) {
				throw new Forminator_Addon_Slack_Exception( __( 'Message not properly setup.', Forminator::DOMAIN ) );
			}
			$text_message = $connection_settings['message'];
			$text_message = forminator_addon_replace_custom_vars( $text_message, $submitted_data, $this->custom_form, $form_entry_fields, false );

			$attachments = $this->get_form_fields_as_attachments( $submitted_data, $form_entry_fields );

			/**
			 * Filter `attachments` to passed onto API
			 *
			 * @since 1.4
			 *
			 * @param string                               $card_name
			 * @param int                                  $form_id                Current Form id
			 * @param string                               $connection_id          ID of current connection
			 * @param array                                $submitted_data
			 * @param array                                $connection_settings    current connection setting, contains options of like `name`, `target_id` etc
			 * @param array                                $form_entry_fields      default entry fields of form
			 * @param array                                $form_settings          Displayed Form settings
			 * @param Forminator_Addon_Slack_Form_Settings $form_settings_instance Slack Addon Form Settings instance
			 */
			$attachments = apply_filters(
				'forminator_addon_slack_message_attachments',
				$attachments,
				$form_id,
				$connection_id,
				$submitted_data,
				$connection_settings,
				$form_entry_fields,
				$form_settings,
				$form_settings_instance
			);

			if ( ! empty( $attachments ) ) {
				$args['attachments'] = $attachments;
			}

			$args['mrkdwn'] = true;
			/**
			 * Filter arguments to passed on to Send Message Slack API
			 *
			 * @since 1.3
			 *
			 * @param array                                $args
			 * @param int                                  $form_id                Current Form id
			 * @param string                               $connection_id          ID of current connection
			 * @param array                                $submitted_data
			 * @param array                                $connection_settings    current connection setting, contains options of like `name`, `target_id` etc
			 * @param array                                $form_entry_fields      default entry fields of form
			 * @param array                                $form_settings          Displayed Form settings
			 * @param Forminator_Addon_Slack_Form_Settings $form_settings_instance Slack Addon Form Settings instance
			 */
			$args = apply_filters(
				'forminator_addon_slack_send_message_args',
				$args,
				$form_id,
				$connection_id,
				$submitted_data,
				$connection_settings,
				$form_entry_fields,
				$form_settings,
				$form_settings_instance
			);

			$post_message_request = $api->chat_post_message( $connection_settings['target_id'], $text_message, $args );

			$ts = '';
			if ( is_object( $post_message_request ) && isset( $post_message_request->ts ) ) {
				$ts = (string) $post_message_request->ts;
			}

			return array(
				'is_sent'         => true,
				'connection_name' => $connection_settings['name'],
				'description'     => __( 'Successfully send data to Slack', Forminator::DOMAIN ),
				'data_sent'       => $api->get_last_data_sent(),
				'data_received'   => $api->get_last_data_received(),
				'url_request'     => $api->get_last_url_request(),
				'ts'              => $ts, // for delete reference
				'target_id'       => $connection_settings['target_id'], // for delete reference
			);

		} catch ( Forminator_Addon_Slack_Exception $e ) {
			$addon_entry_fields = array(
				'is_sent'         => false,
				'description'     => $e->getMessage(),
				'connection_name' => $connection_settings['name'],
				'data_sent'       => ( ( $api instanceof Forminator_Addon_Slack_Wp_Api ) ? $api->get_last_data_sent() : array() ),
				'data_received'   => ( ( $api instanceof Forminator_Addon_Slack_Wp_Api ) ? $api->get_last_data_received() : array() ),
				'url_request'     => ( ( $api instanceof Forminator_Addon_Slack_Wp_Api ) ? $api->get_last_url_request() : '' ),
				'ts'              => '', // for delete reference,
				'target_id'       => '', // for delete reference,
			);

			return $addon_entry_fields;
		}
	}

	/**
	 * Get All Form Fields as attachments
	 *
	 * @since 1.0 Slack Addon
	 *
	 * @param $submitted_data
	 * @param $form_entry_fields
	 *
	 * @return array
	 */
	public function get_form_fields_as_attachments( $submitted_data, $form_entry_fields ) {
		$attachments                   = array();
		$all_fields_attachments        = array();
		$all_fields_attachments_fields = array();
		$form_fields                   = $this->form_settings_instance->get_form_fields();
		$field_format                  = array();
		$post_element_ids = array();
		foreach ( $form_fields as $form_field ) {
			$element_id  = $form_field['element_id'];
			$field_type  = $form_field['type'];
			$field_label = $form_field['field_label'];

			$post_element_id = $element_id;
			if ( stripos( $field_type, 'postdata' ) !== false ) {
				$post_type  = $form_field['post_type'];
				$category_list = forminator_post_categories( $post_type );
				$post_element_id = str_ireplace( '-post-title', '', $post_element_id );
				$post_element_id = str_ireplace( '-post-content', '', $post_element_id );
				$post_element_id = str_ireplace( '-post-excerpt', '', $post_element_id );
				if( ! empty ( $category_list ) ) {
					foreach ( $category_list as $category ) {
						$post_element_id = str_ireplace( '-' . $category['value'], '', $post_element_id );
						$field_format[]  = 'postdata-' . $category['value'];
					}
				}
				$post_element_id = str_ireplace( '-post-image', '', $post_element_id );

				// only add postdata as single
				if ( in_array( $post_element_id, $post_element_ids, true ) ) {
					continue;
				}
				$post_element_ids[] = $post_element_id;
			}

			switch ( $field_type ) {
				case 'postdata-post-title':
				case 'postdata-post-content':
				case 'postdata-post-excerpt':
				case 'postdata-post-image':
					$field_value                     = '{' . $post_element_id . '}';
					$field_value                     = forminator_addon_replace_custom_vars( $field_value, $submitted_data, $this->custom_form, $form_entry_fields, false );
					$all_fields_attachments_fields[] = array(
						'title' => $field_label,
						'value' => ( empty( $field_value ) ? '-' : $field_value ),
						'short' => false,
					);

					break;
				default:
					$field_value                     = '{' . $element_id . '}';
					$field_value                     = forminator_addon_replace_custom_vars( $field_value, $submitted_data, $this->custom_form, $form_entry_fields, false );
					$all_fields_attachments_fields[] = array(
						'title' => $field_label,
						'value' => ( empty( $field_value ) ? '-' : $field_value ),
						'short' => false,
					);
					break;
			}

			if( in_array( $field_type, $field_format, true ) ) {

				$field_value                     = '{' . $post_element_id . '}';
				$field_value                     = forminator_addon_replace_custom_vars( $field_value, $submitted_data, $this->custom_form, $form_entry_fields, false );
				$all_fields_attachments_fields[] = array(
					'title' => $field_label,
					'value' => ( empty( $field_value ) ? '-' : $field_value ),
					'short' => false,
				);
			}
		}

		$all_fields_attachments['fields'] = $all_fields_attachments_fields;
		$attachments[]                    = $all_fields_attachments;

		return $attachments;
	}


	/**
	 * It wil add new row on entry table of submission page, with couple of subentries
	 * subentries included are defined in @see Forminator_Addon_Slack_Form_Hooks::get_additional_entry_item()
	 *
	 * @since 1.0 Slack Addon
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
		 * Filter Slack metadata that previously saved on db to be processed
		 *
		 * @since 1.4
		 *
		 * @param array                                $addon_meta_data
		 * @param int                                  $form_id                current Form ID
		 * @param Forminator_Addon_Slack_Form_Settings $form_settings_instance Slack Addon Form Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_slack_metadata',
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
	 * Loop through addon meta data on multiple Slack setup(s)
	 *
	 * @since 1.0 Slack Addon
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
	 * - Integration Name : its defined by user when they adding Slack integration on their form
	 * - Sent To Slack : will be Yes/No value, that indicates whether sending data to Slack API was successful
	 * - Info : Text that are generated by addon when building and sending data to Slack @see Forminator_Addon_Slack_Form_Hooks::add_entry_fields()
	 * - Below subentries will be added if full log enabled, @see Forminator_Addon_Slack::is_show_full_log() @see FORMINATOR_ADDON_SLACK_SHOW_FULL_LOG
	 *      - API URL : URL that wes requested when sending data to Slack
	 *      - Data sent to Slack : encoded body request that was sent
	 *      - Data received from Slack : json encoded body response that was received
	 *
	 * @param $addon_meta_data
	 *
	 * @since 1.0 Slack Addon
	 * @return array
	 */
	private function get_additional_entry_item( $addon_meta_data ) {

		if ( ! isset( $addon_meta_data['value'] ) || ! is_array( $addon_meta_data['value'] ) ) {
			return array();
		}
		$status                = $addon_meta_data['value'];
		$additional_entry_item = array(
			'label' => __( 'Slack Integration', Forminator::DOMAIN ),
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
				'label' => __( 'Sent To Slack', Forminator::DOMAIN ),
				'value' => $is_sent,
			);
		}

		if ( isset( $status['description'] ) ) {
			$sub_entries[] = array(
				'label' => __( 'Info', Forminator::DOMAIN ),
				'value' => $status['description'],
			);
		}

		if ( Forminator_Addon_Slack::is_show_full_log() ) {
			// too long to be added on entry data enable this with `define('FORMINATOR_ADDON_SLACK_SHOW_FULL_LOG', true)`
			if ( isset( $status['url_request'] ) ) {
				$sub_entries[] = array(
					'label' => __( 'API URL', Forminator::DOMAIN ),
					'value' => $status['url_request'],
				);
			}

			if ( isset( $status['data_sent'] ) ) {
				$sub_entries[] = array(
					'label' => __( 'Data sent to Slack', Forminator::DOMAIN ),
					'value' => '<pre class="sui-code-snippet">' . wp_json_encode( $status['data_sent'], JSON_PRETTY_PRINT ) . '</pre>',
				);
			}

			if ( isset( $status['data_received'] ) ) {
				$sub_entries[] = array(
					'label' => __( 'Data received from Slack', Forminator::DOMAIN ),
					'value' => '<pre class="sui-code-snippet">' . wp_json_encode( $status['data_received'], JSON_PRETTY_PRINT ) . '</pre>',
				);
			}
		}


		$additional_entry_item['sub_entries'] = $sub_entries;

		// return single array
		return $additional_entry_item;
	}

	/**
	 * Slack will add a column on the title/header row
	 * its called `Slack Info` which can be translated on forminator lang
	 *
	 * @since 1.0 Slack Addon
	 * @return array
	 */
	public function on_export_render_title_row() {

		$export_headers = array(
			'info' => __( 'Slack Info', Forminator::DOMAIN ),
		);

		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;

		/**
		 * Filter Slack headers on export file
		 *
		 * @since 1.2
		 *
		 * @param array                                $export_headers         headers to be displayed on export file
		 * @param int                                  $form_id                current Form ID
		 * @param Forminator_Addon_Slack_Form_Settings $form_settings_instance Slack Addon Form Settings instance
		 */
		$export_headers = apply_filters(
			'forminator_addon_slack_export_headers',
			$export_headers,
			$form_id,
			$form_settings_instance
		);

		return $export_headers;
	}

	/**
	 * Slack will add a column that give user information whether sending data to Slack successfully or not
	 * It will only add one column even its multiple connection, every connection will be separated by comma
	 *
	 * @since 1.0 Slack Addon
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
		 * Filter Slack metadata that previously saved on db to be processed
		 *
		 * @since 1.4
		 *
		 * @param array                                $addon_meta_data
		 * @param int                                  $form_id                current Form ID
		 * @param Forminator_Addon_Slack_Form_Settings $form_settings_instance Slack Addon Form Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_slack_metadata',
			$addon_meta_data,
			$form_id,
			$form_settings_instance
		);

		$export_columns = array(
			'info' => $this->get_from_addon_meta_data( $addon_meta_data, 'description', '' ),
		);

		/**
		 * Filter Slack columns to be displayed on export submissions
		 *
		 * @since 1.4
		 *
		 * @param array                                $export_columns         column to be exported
		 * @param int                                  $form_id                current Form ID
		 * @param Forminator_Form_Entry_Model          $entry_model            Form Entry Model
		 * @param array                                $addon_meta_data        meta data saved by addon on entry fields
		 * @param Forminator_Addon_Slack_Form_Settings $form_settings_instance Slack Addon Form Settings instance
		 */
		$export_columns = apply_filters(
			'forminator_addon_slack_export_columns',
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
	 * @since 1.0 Slack Addon
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
	 * It will delete sent chat
	 *
	 * @since 1.0 Slack Addon
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
		 * Filter Slack addon metadata that previously saved on db to be processed
		 *
		 * @since 1.4
		 *
		 * @param array                                $addon_meta_data
		 * @param int                                  $form_id                current Form ID
		 * @param Forminator_Form_Entry_Model          $entry_model            Forminator Entry Model
		 * @param Forminator_Addon_Slack_Form_Settings $form_settings_instance Slack Addon Form Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_slack_metadata',
			$addon_meta_data,
			$form_id,
			$entry_model,
			$form_settings_instance
		);

		/**
		 * Fires when Slack connected form delete a submission
		 *
		 * @since 1.1
		 *
		 * @param int                                  $form_id                current Form ID
		 * @param Forminator_Form_Entry_Model          $entry_model            Forminator Entry Model
		 * @param array                                $addon_meta_data        addon meta data
		 * @param Forminator_Addon_Slack_Form_Settings $form_settings_instance Slack Addon Form Settings instance
		 */
		do_action(
			'forminator_addon_slack_on_before_delete_submission',
			$form_id,
			$entry_model,
			$addon_meta_data,
			$form_settings_instance
		);

		if ( ! Forminator_Addon_Slack::enable_delete_chat() ) {
			// its disabled, go for it!
			return true;
		}

		try {
			if ( is_array( $addon_meta_data ) ) {
				$card_delete_mode = Forminator_Addon_Trello::get_card_delete_mode();

				foreach ( $addon_meta_data as $addon_meta_datum ) {

					// basic data validation
					if ( ! isset( $addon_meta_datum['value'] ) || ! is_array( $addon_meta_datum['value'] ) ) {
						continue;
					}

					$addon_meta_datum_value = $addon_meta_datum['value'];
					if ( ! isset( $addon_meta_datum_value['is_sent'] ) || ! $addon_meta_datum_value['is_sent'] ) {
						continue;
					}

					if ( ! isset( $addon_meta_datum_value['ts'] ) || empty( $addon_meta_datum_value['ts'] ) ) {
						continue;
					}

					if ( ! isset( $addon_meta_datum_value['target_id'] ) || empty( $addon_meta_datum_value['target_id'] ) ) {
						continue;
					}

					$chat_ts    = $addon_meta_datum_value['ts'];
					$channel_id = $addon_meta_datum_value['target_id'];

					$api = $this->addon->get_api();
					$api->chat_delete( $channel_id, $chat_ts );

				}
			}

			//delete mode!
			return true;

		} catch ( Forminator_Addon_Slack_Exception $e ) {
			// handle all internal addon exceptions with `Forminator_Addon_Slack_Exception`

			// use wp_error, for future usage it can be returned to page entries
			$wp_error
				= new WP_Error( 'forminator_addon_slack_delete_chat', $e->getMessage() );
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
