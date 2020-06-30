<?php

/**
 * Class Forminator_Addon_Trello_Poll_Hooks
 *
 * @since 1.6.1
 *
 */
class Forminator_Addon_Trello_Poll_Hooks extends Forminator_Addon_Poll_Hooks_Abstract {

	/**
	 * Addon instance are auto available form abstract
	 * Its added here for development purpose,
	 * Auto-complete will resolve addon directly to `Trello` instance instead of the abstract
	 * And its public properties can be exposed
	 *
	 * @since 1.6.1
	 * @var Forminator_Addon_Trello
	 */
	protected $addon;

	/**
	 * Form Settings Instance
	 *
	 * @since 1.6.1
	 * @var Forminator_Addon_Trello_Poll_Settings | null
	 */
	protected $poll_settings_instance;

	/**
	 * Forminator_Addon_Trello_Poll_Hooks constructor.
	 *
	 * @since 1.6.1
	 *
	 * @param Forminator_Addon_Abstract $addon
	 * @param                           $poll_id
	 *
	 * @throws Forminator_Addon_Exception
	 */
	public function __construct( Forminator_Addon_Abstract $addon, $poll_id ) {
		parent::__construct( $addon, $poll_id );
		$this->_submit_poll_error_message = __( 'Trello failed to process submitted data. Please check your form and try again', Forminator::DOMAIN );
	}

	/**
	 * Save status of request sent and received for each connected Trello
	 *
	 * @since 1.6.1
	 *
	 * @param array $submitted_data
	 * @param array $current_entry_fields
	 *
	 * @return array
	 */
	public function add_entry_fields( $submitted_data, $current_entry_fields = array() ) {

		$poll_id                = $this->poll_id;
		$poll_settings_instance = $this->poll_settings_instance;

		/**
		 * Filter Trello submitted form data to be processed
		 *
		 * @since 1.6.1
		 *
		 * @param array                                 $submitted_data
		 * @param array                                 $current_entry_fields
		 * @param int                                   $poll_id                current Poll ID
		 * @param Forminator_Addon_Trello_Poll_Settings $poll_settings_instance Trello Addon Poll Settings instance
		 */
		$submitted_data = apply_filters(
			'forminator_addon_trello_poll_submitted_data',
			$submitted_data,
			$current_entry_fields,
			$poll_id,
			$poll_settings_instance
		);

		/**
		 * Filter current form entry fields data to be processed by Trello
		 *
		 * @since 1.6.1
		 *
		 * @param array                                 $current_entry_fields
		 * @param array                                 $submitted_data
		 * @param int                                   $poll_id                current Poll ID
		 * @param Forminator_Addon_Trello_Poll_Settings $poll_settings_instance Trello Addon Poll Settings instance
		 */
		$current_entry_fields = apply_filters(
			'forminator_addon_trello_poll_current_entry_fields',
			$current_entry_fields,
			$submitted_data,
			$poll_id,
			$poll_settings_instance
		);


		$addon_setting_values = $this->poll_settings_instance->get_poll_settings_values();

		$data = array();

		/**
		 * Fires before create card on trello
		 *
		 * @since 1.6.1
		 *
		 * @param int                                   $poll_id                current Poll ID
		 * @param array                                 $submitted_data
		 * @param Forminator_Addon_Trello_Poll_Settings $poll_settings_instance Trello Addon Poll Settings instance
		 */
		do_action( 'forminator_addon_trello_poll_before_create_card', $poll_id, $submitted_data, $poll_settings_instance );

		foreach ( $addon_setting_values as $key => $addon_setting_value ) {
			// save it on entry field, with name `status-$MULTI_ID`, and value is the return result on sending data to trello
			if ( $poll_settings_instance->is_multi_poll_settings_complete( $key ) ) {
				// exec only on completed connection
				$data[] = array(
					'name'  => 'status-' . $key,
					'value' => $this->get_status_on_create_card( $key, $submitted_data, $addon_setting_value, $current_entry_fields ),
				);
			}

		}

		$entry_fields = $data;
		/**
		 * Filter Trello entry fields to be saved to entry model
		 *
		 * @since 1.6.1
		 *
		 * @param array                                 $entry_fields
		 * @param int                                   $poll_id                current Poll ID
		 * @param array                                 $submitted_data
		 * @param array                                 $current_entry_fields
		 * @param Forminator_Addon_Trello_Poll_Settings $poll_settings_instance Trello Addon Poll Settings instance
		 */
		$data = apply_filters(
			'forminator_addon_trello_poll_entry_fields',
			$entry_fields,
			$poll_id,
			$submitted_data,
			$current_entry_fields,
			$poll_settings_instance
		);

		return $data;

	}

	/**
	 * Get status on create Trello card
	 *
	 * @since 1.6.1
	 *
	 * @param string $connection_id
	 * @param array  $submitted_data
	 * @param array  $connection_settings
	 * @param array  $current_entry_fields
	 *
	 * @return array `is_sent` true means its success send data to Trello, false otherwise
	 */
	private function get_status_on_create_card( $connection_id, $submitted_data, $connection_settings, $current_entry_fields ) {
		// initialize as null
		$api = null;

		$poll_id                = $this->poll_id;
		$poll_settings_instance = $this->poll_settings_instance;

		//check required fields
		try {
			$api  = $this->addon->get_api();
			$args = array();

			$poll_settings = $this->poll_settings_instance->get_poll_settings();

			if ( isset( $connection_settings['list_id'] ) ) {
				$args['idList'] = $connection_settings['list_id'];
			}

			if ( isset( $connection_settings['card_name'] ) ) {
				$card_name = $connection_settings['card_name'];
				// disable all_fields here
				$card_name = forminator_replace_variables( $card_name );
				// {poll_name_replace}
				$card_name = str_ireplace( '{poll_name}', forminator_get_name_from_model( $this->poll ), $card_name );
				$card_name = str_ireplace( '{poll_answer}', $this->poll_answer_to_plain_text( $submitted_data ), $card_name );
				$card_name = str_ireplace( '{poll_result}', $this->poll_result_to_plain_text( $submitted_data ), $card_name );

				/**
				 * Filter Card Name to passed on to Create Trello Card API
				 *
				 * @since 1.6.1
				 *
				 * @param string                                $card_name
				 * @param int                                   $poll_id                Current Poll id
				 * @param string                                $connection_id          ID of current connection
				 * @param array                                 $submitted_data
				 * @param array                                 $connection_settings    current connection setting, contains options of like `name`, `list_id` etc
				 * @param array                                 $current_entry_fields   default entry fields of poll
				 * @param array                                 $poll_settings          Displayed Poll settings
				 * @param Forminator_Addon_Trello_Poll_Settings $poll_settings_instance Trello Addon Poll Settings instance
				 */
				$card_name    = apply_filters(
					'forminator_addon_trello_poll_card_name',
					$card_name,
					$poll_id,
					$connection_id,
					$submitted_data,
					$connection_settings,
					$current_entry_fields,
					$poll_settings,
					$poll_settings_instance
				);
				$args['name'] = $card_name;

			}

			if ( isset( $connection_settings['card_description'] ) ) {
				$card_description        = $connection_settings['card_description'];
				$poll_name_to_markdown   = $this->poll_name_to_markdown();
				$poll_answer_to_markdown = $this->poll_answer_to_markdown( $submitted_data );
				$poll_result_to_markdown = $this->poll_result_to_markdown( $submitted_data );
				$card_description        = str_ireplace( '{poll_name}', $poll_name_to_markdown, $card_description );
				$card_description        = str_ireplace( '{poll_answer}', $poll_answer_to_markdown, $card_description );
				$card_description        = str_ireplace( '{poll_result}', $poll_result_to_markdown, $card_description );
				$card_description        = forminator_replace_variables( $card_description );

				/**
				 * Filter Card Description to passed on to Create Trello Card API
				 *
				 * @since 1.6.1
				 *
				 * @param string                                $card_description
				 * @param int                                   $poll_id                Current Poll id
				 * @param string                                $connection_id          ID of current connection
				 * @param array                                 $submitted_data
				 * @param array                                 $connection_settings    current connection setting, contains options of like `name`, `list_id` etc
				 * @param array                                 $current_entry_fields   default entry fields of poll
				 * @param array                                 $poll_settings          Displayed Poll settings
				 * @param Forminator_Addon_Trello_Poll_Settings $poll_settings_instance Trello Addon Poll Settings instance
				 */
				$card_description = apply_filters(
					'forminator_addon_trello_poll_card_description',
					$card_description,
					$poll_id,
					$connection_id,
					$submitted_data,
					$connection_settings,
					$current_entry_fields,
					$poll_settings,
					$poll_settings_instance
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
			 * @since 1.6.1
			 *
			 * @param array                                 $args
			 * @param int                                   $poll_id                Current Poll id
			 * @param string                                $connection_id          ID of current connection
			 * @param array                                 $submitted_data
			 * @param array                                 $connection_settings    current connection setting, contains options of like `name`, `list_id` etc
			 * @param array                                 $poll_settings          Displayed Poll settings
			 * @param Forminator_Addon_Trello_Poll_Settings $poll_settings_instance Trello Addon Poll Settings instance
			 */
			$args = apply_filters(
				'forminator_addon_trello_poll_create_card_args',
				$args,
				$poll_id,
				$connection_id,
				$submitted_data,
				$connection_settings,
				$poll_settings,
				$poll_settings_instance
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
	 * Special Replacer `{poll_name}` to markdown with Trello Flavour
	 *
	 * @since 1.6.2
	 * @return string
	 */
	private function poll_name_to_markdown() {

		$poll_name = forminator_get_name_from_model( $this->poll );

		$markdown = "##" . $poll_name . "\n";

		/**
		 * Filter markdown for `poll_answer`
		 *
		 * @since 1.6.1
		 *
		 * @param string $markdown
		 * @param array  $submitted_data Submit data
		 * @param array  $fields_labels  Poll Answers Labels
		 */
		$markdown = apply_filters(
			'forminator_addon_trello_poll_name_markdown',
			$markdown
		);

		return $markdown;
	}

	/**
	 * Special Replacer `{poll_answer}` to markdown with Trello Flavour
	 *
	 * @since 1.6.1
	 *
	 * @param array $submitted_data
	 *
	 * @return string
	 */
	private function poll_answer_to_markdown( $submitted_data ) {

		$answer_data   = isset( $submitted_data[ $this->poll_id ] ) ? $submitted_data[ $this->poll_id ] : '';
		$extra_field   = isset( $submitted_data[ $this->poll_id . '-extra' ] ) ? $submitted_data[ $this->poll_id . '-extra' ] : '';
		$fields_labels = $this->poll->pluck_fields_array( 'title', 'element_id', '1' );

		$answer = isset( $fields_labels[ $answer_data ] ) ? $fields_labels[ $answer_data ] : $answer_data;
		$extra  = $extra_field;

		$markdown = "##" . __( 'Poll Answer', Forminator::DOMAIN ) . "\n";
		$markdown .= '**' . __( 'Vote', Forminator::DOMAIN ) . ':** ' . $answer;
		if ( ! empty( $extra ) ) {
			$markdown .= "\n**" . __( 'Extra', Forminator::DOMAIN ) . ':** ' . $extra;
		}


		/**
		 * Filter markdown for `poll_answer`
		 *
		 * @since 1.6.1
		 *
		 * @param string $markdown
		 * @param array  $submitted_data Submit data
		 * @param array  $fields_labels  Poll Answers Labels
		 */
		$markdown = apply_filters(
			'forminator_addon_trello_poll_answer_markdown',
			$markdown,
			$submitted_data,
			$fields_labels
		);

		return $markdown;
	}

	/**
	 * Special Replacer `{poll_result}` to markdown with Trello Flavour
	 *
	 * @since 1.6.1
	 *
	 * @param array $submitted_data
	 *
	 * @return string
	 */
	private function poll_result_to_markdown( $submitted_data ) {
		$fields_array = $this->poll->get_fields_as_array();
		$map_entries  = Forminator_Form_Entry_Model::map_polls_entries( $this->poll_id, $fields_array );

		// append new answer
		if ( ! $this->poll->is_prevent_store() ) {
			$answer_data = isset( $submitted_data[ $this->poll_id ] ) ? $submitted_data[ $this->poll_id ] : '';

			$entries = 0;
			// exists on map entries
			if ( in_array( $answer_data, array_keys( $map_entries ), true ) ) {
				$entries = $map_entries[ $answer_data ];
			}

			$entries ++;
			$map_entries[ $answer_data ] = $entries;

		}

		$fields = $this->poll->get_fields();

		$markdown = '##' . __( 'Poll Results', Forminator::DOMAIN );
		if ( ! is_null( $fields ) ) {
			foreach ( $fields as $field ) {
				$label = addslashes( $field->title );

				$slug    = isset( $field->slug ) ? $field->slug : sanitize_title( $label );
				$entries = 0;
				if ( in_array( $slug, array_keys( $map_entries ), true ) ) {
					$entries = $map_entries[ $slug ];
				}
				$markdown .= "\n**" . $label . ':** ' . $entries;
			}
		}


		/**
		 * Filter markdown for `poll_result`
		 *
		 * @since 1.6.1
		 *
		 * @param string $markdown
		 * @param array  $fields_array Answers list
		 * @param array  $map_entries  Poll Entries
		 */
		$markdown = apply_filters(
			'forminator_addon_trello_poll_result_markdown',
			$markdown,
			$fields_array,
			$map_entries
		);

		return $markdown;
	}

	/**
	 * Special Replacer `{poll_answer}` to markdown with Trello Flavour
	 *
	 * @since 1.6.2
	 *
	 * @param array $submitted_data
	 *
	 * @return string
	 */
	private function poll_answer_to_plain_text( $submitted_data ) {

		$answer_data   = isset( $submitted_data[ $this->poll_id ] ) ? $submitted_data[ $this->poll_id ] : '';
		$extra_field   = isset( $submitted_data[ $this->poll_id . '-extra' ] ) ? $submitted_data[ $this->poll_id . '-extra' ] : '';
		$fields_labels = $this->poll->pluck_fields_array( 'title', 'element_id', '1' );

		$answer = isset( $fields_labels[ $answer_data ] ) ? $fields_labels[ $answer_data ] : $answer_data;
		$extra  = $extra_field;

		$plain_text = $answer;
		if ( ! empty( $extra ) ) {
			$plain_text .= ', ' . $extra;
		}


		/**
		 * Filter plain text for `poll_answer`
		 *
		 * @since 1.6.2
		 *
		 * @param string $plain_text
		 * @param array  $submitted_data Submit data
		 * @param array  $fields_labels  Poll Answers Labels
		 */
		$plain_text = apply_filters(
			'forminator_addon_trello_poll_answer_plain_text',
			$plain_text,
			$submitted_data,
			$fields_labels
		);

		return $plain_text;
	}

	/**
	 * Special Replacer `{poll_result}` to markdown with Trello Flavour
	 *
	 * @since 1.6.2
	 *
	 * @param array $submitted_data
	 *
	 * @return string
	 */
	private function poll_result_to_plain_text( $submitted_data ) {
		$fields_array = $this->poll->get_fields_as_array();
		$map_entries  = Forminator_Form_Entry_Model::map_polls_entries( $this->poll_id, $fields_array );

		// append new answer
		if ( ! $this->poll->is_prevent_store() ) {
			$answer_data = isset( $submitted_data[ $this->poll_id ] ) ? $submitted_data[ $this->poll_id ] : '';

			$entries = 0;
			// exists on map entries
			if ( in_array( $answer_data, array_keys( $map_entries ), true ) ) {
				$entries = $map_entries[ $answer_data ];
			}

			$entries ++;
			$map_entries[ $answer_data ] = $entries;

		}

		$fields = $this->poll->get_fields();

		$plain_text = '';
		if ( ! is_null( $fields ) ) {
			foreach ( $fields as $field ) {
				$label = addslashes( $field->title );

				$slug    = isset( $field->slug ) ? $field->slug : sanitize_title( $label );
				$entries = 0;
				if ( in_array( $slug, array_keys( $map_entries ), true ) ) {
					$entries = $map_entries[ $slug ];
				}
				$plain_text .= '' . $label . ': ' . $entries . ' ';
			}
		}


		/**
		 * Filter markdown for `poll_result`
		 *
		 * @since 1.6.2
		 *
		 * @param string $markdown
		 * @param array  $fields_array Answers list
		 * @param array  $map_entries  Poll Entries
		 */
		$plain_text = apply_filters(
			'forminator_addon_trello_poll_result_plain_text',
			$plain_text,
			$fields_array,
			$map_entries
		);

		return $plain_text;
	}

	/**
	 * Trello will add a column on the title/header row
	 * its called `Trello Info` which can be translated on forminator lang
	 *
	 * @since 1.6.1
	 * @return array
	 */
	public function on_export_render_title_row() {

		$export_headers = array(
			'info' => __( 'Trello Info', Forminator::DOMAIN ),
		);

		$poll_id                = $this->poll_id;
		$poll_settings_instance = $this->poll_settings_instance;

		/**
		 * Filter Trello headers on export file
		 *
		 * @since 1.6.1
		 *
		 * @param array                                 $export_headers         headers to be displayed on export file
		 * @param int                                   $poll_id                current Poll ID
		 * @param Forminator_Addon_Trello_Poll_Settings $poll_settings_instance Trello Addon Poll Settings instance
		 */
		$export_headers = apply_filters(
			'forminator_addon_trello_poll_export_headers',
			$export_headers,
			$poll_id,
			$poll_settings_instance
		);

		return $export_headers;
	}

	/**
	 * Trello will add a column that give user information whether sending data to Trello successfully or not
	 * It will only add one column even its multiple connection, every connection will be separated by comma
	 *
	 * @since 1.6.1
	 *
	 * @param Forminator_Form_Entry_Model $entry_model
	 * @param                             $addon_meta_data
	 *
	 * @return array
	 */
	public function on_export_render_entry( Forminator_Form_Entry_Model $entry_model, $addon_meta_data ) {

		$poll_id                = $this->poll_id;
		$poll_settings_instance = $this->poll_settings_instance;

		/**
		 *
		 * Filter Trello metadata that previously saved on db to be processed
		 *
		 * @since 1.6.1
		 *
		 * @param array                                 $addon_meta_data
		 * @param int                                   $poll_id                current Poll ID
		 * @param Forminator_Addon_Trello_Poll_Settings $poll_settings_instance Trello Addon Poll Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_trello_poll_metadata',
			$addon_meta_data,
			$poll_id,
			$poll_settings_instance
		);

		$export_columns = array(
			'info' => $this->get_from_addon_meta_data( $addon_meta_data, 'description', '' ),
		);

		/**
		 * Filter Trello columns to be displayed on export submissions
		 *
		 * @since 1.6.1
		 *
		 * @param array                                 $export_columns         column to be exported
		 * @param int                                   $poll_id                current Poll ID
		 * @param Forminator_Form_Entry_Model           $entry_model            Form Entry Model
		 * @param array                                 $addon_meta_data        meta data saved by addon on entry fields
		 * @param Forminator_Addon_Trello_Poll_Settings $poll_settings_instance Trello Addon Poll Settings instance
		 */
		$export_columns = apply_filters(
			'forminator_addon_trello_poll_export_columns',
			$export_columns,
			$poll_id,
			$entry_model,
			$addon_meta_data,
			$poll_settings_instance
		);

		return $export_columns;
	}
}
