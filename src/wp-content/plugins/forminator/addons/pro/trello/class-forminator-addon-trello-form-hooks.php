<?php

/**
 * Class Forminator_Addon_Trello_Form_Hooks
 *
 * @since 1.0 Trello Addon
 *
 */
class Forminator_Addon_Trello_Form_Hooks extends Forminator_Addon_Form_Hooks_Abstract {

	/**
	 * Addon instance are auto available form abstract
	 * Its added here for development purpose,
	 * Auto-complete will resolve addon directly to `Trello` instance instead of the abstract
	 * And its public properties can be exposed
	 *
	 * @since 1.0 Trello Addon
	 * @var Forminator_Addon_Trello
	 */
	protected $addon;

	/**
	 * Form Settings Instance
	 *
	 * @since 1.0 Trello Addon
	 * @var Forminator_Addon_Trello_Form_Settings | null
	 */
	protected $form_settings_instance;

	/**
	 * Forminator_Addon_Trello_Form_Hooks constructor.
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
		$this->_submit_form_error_message = __( 'Trello failed to process submitted data. Please check your form and try again', Forminator::DOMAIN );
	}

	/**
	 * Save status of request sent and received for each connected Trello
	 *
	 * @since 1.0 Trello Addon
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
		 * Filter Trello submitted form data to be processed
		 *
		 * @since 1.2
		 *
		 * @param array                                 $submitted_data
		 * @param array                                 $form_entry_fields
		 * @param int                                   $form_id                current Form ID
		 * @param Forminator_Addon_Trello_Form_Settings $form_settings_instance Trello Addon Form Settings instance
		 */
		$submitted_data = apply_filters(
			'forminator_addon_trello_form_submitted_data',
			$submitted_data,
			$form_entry_fields,
			$form_id,
			$form_settings_instance
		);

		/**
		 * Filter current form entry fields data to be processed by Trello
		 *
		 * @since 1.2
		 *
		 * @param array                                 $form_entry_fields
		 * @param array                                 $submitted_data
		 * @param int                                   $form_id                current Form ID
		 * @param Forminator_Addon_Trello_Form_Settings $form_settings_instance Trello Addon Form Settings instance
		 */
		$form_entry_fields = apply_filters(
			'forminator_addon_trello_form_entry_fields',
			$form_entry_fields,
			$submitted_data,
			$form_id,
			$form_settings_instance
		);

		forminator_addon_maybe_log( __METHOD__, $submitted_data );

		$addon_setting_values = $this->form_settings_instance->get_form_settings_values();

		$data = array();

		/**
		 * Fires before create card on trello
		 *
		 * @since 1.2
		 *
		 * @param int                                   $form_id                current Form ID
		 * @param array                                 $submitted_data
		 * @param Forminator_Addon_Trello_Form_Settings $form_settings_instance Trello Addon Form Settings instance
		 */
		do_action( 'forminator_addon_trello_before_create_card', $form_id, $submitted_data, $form_settings_instance );

		foreach ( $addon_setting_values as $key => $addon_setting_value ) {
			// save it on entry field, with name `status-$MULTI_ID`, and value is the return result on sending data to trello
			if ( $form_settings_instance->is_multi_form_settings_complete( $key ) ) {
				// exec only on completed connection
				$data[] = array(
					'name'  => 'status-' . $key,
					'value' => $this->get_status_on_create_card( $key, $submitted_data, $addon_setting_value, $form_entry_fields ),
				);
			}
		}

		$entry_fields = $data;
		/**
		 * Filter Trello entry fields to be saved to entry model
		 *
		 * @since 1.2
		 *
		 * @param array                                 $entry_fields
		 * @param int                                   $form_id                current Form ID
		 * @param array                                 $submitted_data
		 * @param array                                 $form_entry_fields
		 * @param Forminator_Addon_Trello_Form_Settings $form_settings_instance Trello Addon Form Settings instance
		 */
		$data = apply_filters(
			'forminator_addon_trello_entry_fields',
			$entry_fields,
			$form_id,
			$submitted_data,
			$form_entry_fields,
			$form_settings_instance
		);

		return $data;

	}

	/**
	 * Get status on create Trello card
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param string $connection_id
	 * @param array  $submitted_data
	 * @param array  $connection_settings
	 * @param array  $form_entry_fields
	 *
	 * @return array `is_sent` true means its success send data to Trello, false otherwise
	 */
	private function get_status_on_create_card( $connection_id, $submitted_data, $connection_settings, $form_entry_fields ) {
		// initialize as null
		$api = null;

		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;

		//check required fields
		try {
			$api  = $this->addon->get_api();
			$args = array();

			$form_settings = $this->form_settings_instance->get_form_settings();

			if ( isset( $connection_settings['list_id'] ) ) {
				$args['idList'] = $connection_settings['list_id'];
			}

			if ( isset( $connection_settings['card_name'] ) ) {
				$card_name = $connection_settings['card_name'];
				// disable all_fields here
				$card_name = str_ireplace( '{all_fields}', '', $card_name );
				$card_name = forminator_addon_replace_custom_vars( $card_name, $submitted_data, $this->custom_form, $form_entry_fields, false );

				/**
				 * Filter Card Name to passed on to Create Trello Card API
				 *
				 * @since 1.2
				 *
				 * @param string                                $card_name
				 * @param int                                   $form_id                Current Form id
				 * @param string                                $connection_id          ID of current connection
				 * @param array                                 $submitted_data
				 * @param array                                 $connection_settings    current connection setting, contains options of like `name`, `list_id` etc
				 * @param array                                 $form_entry_fields      default entry fields of form
				 * @param array                                 $form_settings          Displayed Form settings
				 * @param Forminator_Addon_Trello_Form_Settings $form_settings_instance Trello Addon Form Settings instance
				 */
				$card_name    = apply_filters(
					'forminator_addon_trello_card_name',
					$card_name,
					$form_id,
					$connection_id,
					$submitted_data,
					$connection_settings,
					$form_entry_fields,
					$form_settings,
					$form_settings_instance
				);
				$args['name'] = $card_name;

			}

			if ( isset( $connection_settings['card_description'] ) ) {
				$card_description       = $connection_settings['card_description'];
				$all_fields_to_markdown = $this->all_fields_to_markdown();
				$card_description       = str_ireplace( '{all_fields}', $all_fields_to_markdown, $card_description );
				$card_description       = forminator_addon_replace_custom_vars( $card_description, $submitted_data, $this->custom_form, $form_entry_fields, false );

				/**
				 * Filter Card Description to passed on to Create Trello Card API
				 *
				 * @since 1.2
				 *
				 * @param string                                $card_description
				 * @param int                                   $form_id                Current Form id
				 * @param string                                $connection_id          ID of current connection
				 * @param array                                 $submitted_data
				 * @param array                                 $connection_settings    current connection setting, contains options of like `name`, `list_id` etc
				 * @param array                                 $form_entry_fields      default entry fields of form
				 * @param array                                 $form_settings          Displayed Form settings
				 * @param Forminator_Addon_Trello_Form_Settings $form_settings_instance Trello Addon Form Settings instance
				 */
				$card_description = apply_filters(
					'forminator_addon_trello_card_description',
					$card_description,
					$form_id,
					$connection_id,
					$submitted_data,
					$connection_settings,
					$form_entry_fields,
					$form_settings,
					$form_settings_instance
				);
				$args['desc']     = $card_description;
			}

			if ( isset( $connection_settings['position'] ) ) {
				$args['pos'] = $connection_settings['position'];
			}

			if ( isset( $connection_settings['label_ids'] ) && is_array( $connection_settings['label_ids'] ) ) {
				$args['idLabels'] = implode( ',', $connection_settings['label_ids'] );
			}

			if ( isset( $connection_settings['member_ids'] ) && is_array( $connection_settings['member_ids'] ) ) {
				$args['idMembers'] = implode( ',', $connection_settings['member_ids'] );
			}

			if ( isset( $submitted_data['_wp_http_referer'] ) ) {
				$url_source = home_url( $submitted_data['_wp_http_referer'] );
				if ( wp_http_validate_url( $url_source ) ) {
					$args['urlSource'] = $url_source;
				}
			}

			/**
			 * Filter arguments to passed on to Create Trello Card API
			 *
			 * @since 1.2
			 *
			 * @param array                                 $args
			 * @param int                                   $form_id                Current Form id
			 * @param string                                $connection_id          ID of current connection
			 * @param array                                 $submitted_data
			 * @param array                                 $connection_settings    current connection setting, contains options of like `name`, `list_id` etc
			 * @param array                                 $form_settings          Displayed Form settings
			 * @param Forminator_Addon_Trello_Form_Settings $form_settings_instance Trello Addon Form Settings instance
			 */
			$args = apply_filters(
				'forminator_addon_trello_create_card_args',
				$args,
				$form_id,
				$connection_id,
				$submitted_data,
				$connection_settings,
				$form_settings,
				$form_settings_instance
			);

			$api->create_card( $args );

			forminator_addon_maybe_log( __METHOD__, 'Success Send Data' );

			return array(
				'is_sent'         => true,
				'connection_name' => $connection_settings['name'],
				'description'     => __( 'Successfully send data to Trello', Forminator::DOMAIN ),
				'data_sent'       => $api->get_last_data_sent(),
				'data_received'   => $api->get_last_data_received(),
				'url_request'     => $api->get_last_url_request(),
			);

		} catch ( Forminator_Addon_Trello_Exception $e ) {
			forminator_addon_maybe_log( __METHOD__, 'Failed to Send to Trello' );

			return array(
				'is_sent'         => false,
				'description'     => $e->getMessage(),
				'connection_name' => $connection_settings['name'],
				'data_sent'       => ( ( $api instanceof Forminator_Addon_Trello_Wp_Api ) ? $api->get_last_data_sent() : array() ),
				'data_received'   => ( ( $api instanceof Forminator_Addon_Trello_Wp_Api ) ? $api->get_last_data_received() : array() ),
				'url_request'     => ( ( $api instanceof Forminator_Addon_Trello_Wp_Api ) ? $api->get_last_url_request() : '' ),
			);
		}
	}

	/**
	 * Special Replacer `{all_fields}` to markdown with Trello Flavour
	 *
	 */
	private function all_fields_to_markdown() {
		$form_fields = $this->form_settings_instance->get_form_fields();

		$markdown         = '';
		$post_element_ids = array();
		$field_format     = array();
		foreach ( $form_fields as $form_field ) {
			$element_id  = $form_field['element_id'];
			$field_type  = $form_field['type'];
			$field_label = $form_field['field_label'];

			$post_element_id = $element_id;
			if ( stripos( $field_type, 'postdata' ) !== false ) {
				$post_type       = $form_field['post_type'];
				$category_list   = forminator_post_categories( $post_type );
				$post_element_id = str_ireplace( '-post-title', '', $post_element_id );
				$post_element_id = str_ireplace( '-post-content', '', $post_element_id );
				$post_element_id = str_ireplace( '-post-excerpt', '', $post_element_id );
				if ( ! empty( $category_list ) ) {
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
					$value_format = '[' . __( 'Edit Post', Forminator::DOMAIN ) . ']({' . $post_element_id . '})';
					break;
				case 'url':
					$value_format = '[{' . $element_id . '}]({' . $element_id . '})';
					break;
				case 'upload':
					$value_format = '[{' . $element_id . '}]({' . $element_id . '})';
					break;
				default:
					$value_format = '{' . $element_id . '}';
					break;
			}

			if ( in_array( $field_type, $field_format, true ) ) {

				$value_format = '[' . __( 'Edit Post', Forminator::DOMAIN ) . ']({' . $post_element_id . '})';
			}

			$markdown .= self::get_field_markdown( $field_type, $field_label, $value_format );
		}

		/**
		 * Filter markdown for `all_fields`
		 *
		 * @since 1.2
		 *
		 * @param string $markdown
		 * @param array  $form_fields all fields on form
		 */
		$markdown = apply_filters(
			'forminator_addon_trello_all_fields_markdown',
			$markdown,
			$form_fields
		);

		return $markdown;
	}

	/**
	 * Get Markdown for single field
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param $type
	 * @param $label
	 * @param $value
	 *
	 * @return string
	 */
	public static function get_field_markdown( $type, $label, $value ) {
		$markdown = "**{$label}**: {$value}\n";

		/**
		 * Filter single field markdown used by {all_fields}
		 *
		 * @since 1.2
		 *
		 * @param string $markdown
		 * @param string $type  field type
		 * @param string $label field label
		 * @param string $value field string
		 */
		$markdown = apply_filters(
			'forminator_addon_trello_field_markdown',
			$markdown,
			$type,
			$label,
			$value
		);

		return $markdown;
	}

	/**
	 * It wil add new row on entry table of submission page, with couple of subentries
	 * subentries included are defined in @see Forminator_Addon_Trello_Form_Hooks::get_additional_entry_item()
	 *
	 * @since 1.0 Trello Addon
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
		 * Filter trello metadata that previously saved on db to be processed
		 *
		 * @since 1.2
		 *
		 * @param array                                 $addon_meta_data
		 * @param int                                   $form_id                current Form ID
		 * @param Forminator_Addon_Trello_Form_Settings $form_settings_instance Trello Addon Form Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_trello_metadata',
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
	 * Loop through addon meta data on multiple trello setup(s)
	 *
	 * @since 1.0 Trello Addon
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
	 * - Integration Name : its defined by user when they adding Trello integration on their form
	 * - Sent To Trello : will be Yes/No value, that indicates whether sending data to Trello API was successful
	 * - Info : Text that are generated by addon when building and sending data to Trello @see Forminator_Addon_Trello_Form_Hooks::add_entry_fields()
	 * - Below subentries will be added if full log enabled, @see Forminator_Addon_Trello::is_show_full_log() @see FORMINATOR_ADDON_TRELLO_SHOW_FULL_LOG
	 *      - API URL : URL that wes requested when sending data to Trello
	 *      - Data sent to Trello : encoded body request that was sent
	 *      - Data received from Trello : json encoded body response that was received
	 *
	 * @param $addon_meta_data
	 *
	 * @since 1.0 Trello Addon
	 * @return array
	 */
	private function get_additional_entry_item( $addon_meta_data ) {

		if ( ! isset( $addon_meta_data['value'] ) || ! is_array( $addon_meta_data['value'] ) ) {
			return array();
		}
		$status                = $addon_meta_data['value'];
		$additional_entry_item = array(
			'label' => __( 'Trello Integration', Forminator::DOMAIN ),
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
				'label' => __( 'Sent To Trello', Forminator::DOMAIN ),
				'value' => $is_sent,
			);
		}

		if ( isset( $status['description'] ) ) {
			$sub_entries[] = array(
				'label' => __( 'Info', Forminator::DOMAIN ),
				'value' => $status['description'],
			);
		}

		if ( Forminator_Addon_Trello::is_show_full_log() ) {
			// too long to be added on entry data enable this with `define('FORMINATOR_ADDON_TRELLO_SHOW_FULL_LOG', true)`
			if ( isset( $status['url_request'] ) ) {
				$sub_entries[] = array(
					'label' => __( 'API URL', Forminator::DOMAIN ),
					'value' => $status['url_request'],
				);
			}

			if ( isset( $status['data_sent'] ) ) {
				$sub_entries[] = array(
					'label' => __( 'Data sent to Trello', Forminator::DOMAIN ),
					'value' => '<pre class="sui-code-snippet">' . wp_json_encode( $status['data_sent'], JSON_PRETTY_PRINT ) . '</pre>',
				);
			}

			if ( isset( $status['data_received'] ) ) {
				$sub_entries[] = array(
					'label' => __( 'Data received from Trello', Forminator::DOMAIN ),
					'value' => '<pre class="sui-code-snippet">' . wp_json_encode( $status['data_received'], JSON_PRETTY_PRINT ) . '</pre>',
				);
			}
		}

		$additional_entry_item['sub_entries'] = $sub_entries;

		// return single array
		return $additional_entry_item;
	}

	/**
	 * Trello will add a column on the title/header row
	 * its called `Trello Info` which can be translated on forminator lang
	 *
	 * @since 1.0 Trello Addon
	 * @return array
	 */
	public function on_export_render_title_row() {

		$export_headers = array(
			'info' => __( 'Trello Info', Forminator::DOMAIN ),
		);

		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;

		/**
		 * Filter Trello headers on export file
		 *
		 * @since 1.2
		 *
		 * @param array                                 $export_headers         headers to be displayed on export file
		 * @param int                                   $form_id                current Form ID
		 * @param Forminator_Addon_Trello_Form_Settings $form_settings_instance Trello Addon Form Settings instance
		 */
		$export_headers = apply_filters(
			'forminator_addon_trello_export_headers',
			$export_headers,
			$form_id,
			$form_settings_instance
		);

		return $export_headers;
	}

	/**
	 * Trello will add a column that give user information whether sending data to Trello successfully or not
	 * It will only add one column even its multiple connection, every connection will be separated by comma
	 *
	 * @since 1.0 Trello Addon
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
		 * Filter Trello metadata that previously saved on db to be processed
		 *
		 * @since 1.2
		 *
		 * @param array                                 $addon_meta_data
		 * @param int                                   $form_id                current Form ID
		 * @param Forminator_Addon_Trello_Form_Settings $form_settings_instance Trello Addon Form Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_trello_metadata',
			$addon_meta_data,
			$form_id,
			$form_settings_instance
		);

		$export_columns = array(
			'info' => $this->get_from_addon_meta_data( $addon_meta_data, 'description', '' ),
		);

		/**
		 * Filter Trello columns to be displayed on export submissions
		 *
		 * @since 1.2
		 *
		 * @param array                                 $export_columns         column to be exported
		 * @param int                                   $form_id                current Form ID
		 * @param Forminator_Form_Entry_Model           $entry_model            Form Entry Model
		 * @param array                                 $addon_meta_data        meta data saved by addon on entry fields
		 * @param Forminator_Addon_Trello_Form_Settings $form_settings_instance Trello Addon Form Settings instance
		 */
		$export_columns = apply_filters(
			'forminator_addon_trello_export_columns',
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
	 * @since 1.0 Trello Addon
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
	 * It will delete card on trello list
	 *
	 * @since 1.0 Trello Addon
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
		 * Filter Trello addon metadata that previously saved on db to be processed
		 *
		 * @since 1.1
		 *
		 * @param array                                 $addon_meta_data
		 * @param int                                   $form_id                current Form ID
		 * @param Forminator_Form_Entry_Model           $entry_model            Forminator Entry Model
		 * @param Forminator_Addon_Trello_Form_Settings $form_settings_instance Trello Addon Form Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_trello_metadata',
			$addon_meta_data,
			$form_id,
			$entry_model,
			$form_settings_instance
		);

		/**
		 * Fires when Trello connected form delete a submission
		 *
		 * @since 1.1
		 *
		 * @param int                                   $form_id                current Form ID
		 * @param Forminator_Form_Entry_Model           $entry_model            Forminator Entry Model
		 * @param array                                 $addon_meta_data        addon meta data
		 * @param Forminator_Addon_Trello_Form_Settings $form_settings_instance Trello Addon Form Settings instance
		 */
		do_action(
			'forminator_addon_trello_on_before_delete_submission',
			$form_id,
			$entry_model,
			$addon_meta_data,
			$form_settings_instance
		);

		if ( ! Forminator_Addon_Trello::is_enable_delete_card() ) {
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

					if ( ! isset( $addon_meta_datum_value['data_received'] ) || ! is_object( $addon_meta_datum_value['data_received'] ) ) {
						continue;
					}

					$addon_meta_datum_received = $addon_meta_datum_value['data_received'];

					if ( ! isset( $addon_meta_datum_received->id ) || empty( $addon_meta_datum_received->id ) ) {
						continue;
					}
					/** data received reference
					 *
					 * data_received: {
					 *      "id": "XXXX",
					 * }
					 */
					$card_id = $addon_meta_datum_received->id;
					$this->delete_card( $card_id, $card_delete_mode, $addon_meta_datum );

				}
			}

			//delete mode!
			return true;

		} catch ( Forminator_Addon_Trello_Exception $e ) {
			// handle all internal addon exceptions with `Forminator_Addon_Trello_Exception`

			// use wp_error, for future usage it can be returned to page entries
			$wp_error
				= new WP_Error( 'forminator_addon_trello_delete_card', $e->getMessage() );
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
	 * Delete card hooked
	 *
	 * @since 1.0 Trello Addon
	 *
	 * @param $card_id
	 * @param $card_delete_mode
	 * @param $addon_meta_datum
	 *
	 * @throws Forminator_Addon_Trello_Wp_Api_Exception
	 * @throws Forminator_Addon_Trello_Wp_Api_Not_Found_Exception
	 */
	public function delete_card( $card_id, $card_delete_mode, $addon_meta_datum ) {
		$api                    = $this->addon->get_api();
		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;
		$args                   = array();

		/**
		 * Filter arguments to send to delete/close card Trello API
		 *
		 * @since 1.2
		 *
		 * @param array                                 $args
		 * @param string                                $card_id
		 * @param string                                $card_delete_mode
		 * @param array                                 $addon_meta_datum
		 * @param int                                   $form_id
		 * @param Forminator_Addon_Trello_Form_Settings $form_settings_instance Trello Addon Form Settings instance
		 */
		$args = apply_filters(
			'forminator_addon_trello_delete_card_args',
			$args,
			$card_id,
			$card_delete_mode,
			$addon_meta_datum,
			$form_id,
			$form_settings_instance
		);

		switch ( $card_delete_mode ) {
			case Forminator_Addon_Trello::CARD_DELETE_MODE_DELETE:
				$api->delete_card( $card_id, $args );
				break;
			case Forminator_Addon_Trello::CARD_DELETE_MODE_CLOSED:
				$api->close_card( $card_id, $args );
				break;
			default:
				break;
		}

		/**
		 * Fire when card already deleted or closed
		 *
		 * @since 1.2
		 *
		 * @param array                                 $args                   args sent to Trello API
		 * @param string                                $card_id
		 * @param string                                $card_delete_mode
		 * @param array                                 $addon_meta_datum
		 * @param int                                   $form_id
		 * @param Forminator_Addon_Trello_Form_Settings $form_settings_instance Trello Addon Form Settings instance
		 *
		 */
		do_action(
			'forminator_addon_trello_delete_card',
			$args,
			$card_id,
			$card_delete_mode,
			$addon_meta_datum,
			$form_id,
			$form_settings_instance
		);
	}
}
