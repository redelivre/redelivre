<?php

/**
 * Class Forminator_Addon_Slack_Poll_Hooks
 *
 * @since 1.6.1
 *
 */
class Forminator_Addon_Slack_Poll_Hooks extends Forminator_Addon_Poll_Hooks_Abstract {

	/**
	 * Addon instance are auto available form abstract
	 * Its added here for development purpose,
	 * Auto-complete will resolve addon directly to `Slack` instance instead of the abstract
	 * And its public properties can be exposed
	 *
	 * @since 1.6.1
	 * @var Forminator_Addon_Slack
	 */
	protected $addon;

	/**
	 * Poll Settings Instance
	 *
	 * @since 1.6.1
	 * @var Forminator_Addon_Slack_Poll_Settings | null
	 */
	protected $poll_settings_instance;

	/**
	 * Save status of request sent and received for each connected Slack Connection
	 *
	 * @since 1.6.1
	 *
	 * @param array $submitted_data
	 * @param array $form_entry_fields
	 *
	 * @return array
	 */
	public function add_entry_fields( $submitted_data, $form_entry_fields = array() ) {

		$pol_id                 = $this->poll_id;
		$poll_settings_instance = $this->poll_settings_instance;

		/**
		 * Filter Slack submitted poll data to be processed
		 *
		 * @since 1.6.1
		 *
		 * @param array                                $submitted_data
		 * @param int                                  $pol_id                 current Poll ID
		 * @param Forminator_Addon_Slack_Poll_Settings $poll_settings_instance Slack Addon Poll Settings instance
		 */
		$submitted_data = apply_filters(
			'forminator_addon_slack_poll_submitted_data',
			$submitted_data,
			$pol_id,
			$poll_settings_instance
		);

		$addon_setting_values = $this->poll_settings_instance->get_poll_settings_values();

		$data = array();

		/**
		 * Fires before send message to Slack
		 *
		 * @since 1.6.1
		 *
		 * @param int                                  $pol_id                 current Poll ID
		 * @param array                                $submitted_data
		 * @param Forminator_Addon_Slack_Poll_Settings $poll_settings_instance Slack Addon Poll Settings instance
		 */
		do_action( 'forminator_addon_slack_poll_before_send_message', $pol_id, $submitted_data, $poll_settings_instance );

		foreach ( $addon_setting_values as $key => $addon_setting_value ) {
			// save it on entry field, with name `status-$MULTI_ID`, and value is the return result on sending data to slack
			if ( $poll_settings_instance->is_multi_poll_settings_complete( $key ) ) {
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
		 * @since 1.6.1
		 *
		 * @param array                                $entry_fields
		 * @param int                                  $pol_id                 current Poll ID
		 * @param array                                $submitted_data
		 * @param Forminator_Addon_Slack_Poll_Settings $poll_settings_instance Slack Addon Poll Settings instance
		 */
		$data = apply_filters(
			'forminator_addon_slack_poll_entry_fields',
			$entry_fields,
			$pol_id,
			$submitted_data,
			$poll_settings_instance
		);

		return $data;

	}

	/**
	 * Get status on send message to Slack
	 *
	 * @since 1.6.1
	 *
	 * @param $connection_id
	 * @param $submitted_data
	 * @param $connection_settings
	 * @param $poll_entry_fields
	 *
	 * @return array `is_sent` true means its success send data to Slack, false otherwise
	 */
	private function get_status_on_send_message( $connection_id, $submitted_data, $connection_settings, $poll_entry_fields ) {
		// initialize as null
		$api = null;

		$poll_id                = $this->poll_id;
		$poll_settings_instance = $this->poll_settings_instance;
		$poll_settings          = $this->poll_settings_instance->get_poll_settings();

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
			$text_message = forminator_replace_variables( $text_message );

			// {poll_name_replace}
			$text_message = str_ireplace( '{poll_name}', forminator_get_name_from_model( $this->poll ), $text_message );

			$attachments = $this->get_poll_data_as_attachments( $submitted_data, $poll_entry_fields );

			/**
			 * Filter `attachments` to passed onto API
			 *
			 * @since 1.4
			 *
			 * @param string                               $card_name
			 * @param int                                  $poll_id                Current Poll id
			 * @param string                               $connection_id          ID of current connection
			 * @param array                                $submitted_data
			 * @param array                                $connection_settings    current connection setting, contains options of like `name`, `target_id` etc
			 * @param array                                $poll_entry_fields      default entry fields of form
			 * @param array                                $poll_settings          Displayed Poll settings
			 * @param Forminator_Addon_Slack_Poll_Settings $poll_settings_instance Slack Addon Poll Settings instance
			 */
			$attachments = apply_filters(
				'forminator_addon_slack_poll_message_attachments',
				$attachments,
				$poll_id,
				$connection_id,
				$submitted_data,
				$connection_settings,
				$poll_entry_fields,
				$poll_settings,
				$poll_settings_instance
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
			 * @param int                                  $poll_id                Current Poll id
			 * @param string                               $connection_id          ID of current connection
			 * @param array                                $submitted_data
			 * @param array                                $connection_settings    current connection setting, contains options of like `name`, `target_id` etc
			 * @param array                                $poll_entry_fields      default entry fields of form
			 * @param array                                $poll_settings          Displayed Poll settings
			 * @param Forminator_Addon_Slack_Poll_Settings $poll_settings_instance Slack Addon Poll Settings instance
			 */
			$args = apply_filters(
				'forminator_addon_slack_poll_send_message_args',
				$args,
				$poll_id,
				$connection_id,
				$submitted_data,
				$connection_settings,
				$poll_entry_fields,
				$poll_settings,
				$poll_settings_instance
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
	 * Get Poll Data as attachments
	 *
	 * @since 1.6.1
	 *
	 * @param $submitted_data
	 * @param $poll_entry_fields
	 *
	 * @return array
	 */
	public function get_poll_data_as_attachments( $submitted_data, $poll_entry_fields ) {
		$attachments = array();

		/**
		 * Attachment 1
		 * Answer          Extra
		 */
		$answer_data   = isset( $submitted_data[ $this->poll_id ] ) ? $submitted_data[ $this->poll_id ] : '';
		$extra_field   = isset( $submitted_data[ $this->poll_id . '-extra' ] ) ? $submitted_data[ $this->poll_id . '-extra' ] : '';
		$fields_labels = $this->poll->pluck_fields_array( 'title', 'element_id', '1' );

		$answer = isset( $fields_labels[ $answer_data ] ) ? $fields_labels[ $answer_data ] : $answer_data;
		$extra  = $extra_field;

		$attachment_fields   = array();
		$attachment_fields[] = array(
			'title' => __( 'Vote', Forminator::DOMAIN ),
			'value' => esc_html( $answer ),
			'short' => ! empty( $extra ),
		);

		if ( ! empty( $extra ) ) {
			$attachment_fields[] = array(
				'title' => __( 'Extra', Forminator::DOMAIN ),
				'value' => esc_html( $extra ),
				'short' => true,
			);
		}

		$attachments[] = array(
			'title'  => __( 'Submitted Vote', Forminator::DOMAIN ),
			'fields' => $attachment_fields,
		);

		/**
		 * Attachment 2
		 * Poll Result
		 */
		$attachment_fields = array();
		$fields_array      = $this->poll->get_fields_as_array();
		$map_entries       = Forminator_Form_Entry_Model::map_polls_entries( $this->poll_id, $fields_array );

		// append new answer
		if ( ! $this->poll->is_prevent_store() ) {
			$entries = 0;
			// exists on map entries
			if ( in_array( $answer_data, array_keys( $map_entries ), true ) ) {
				$entries = $map_entries[ $answer_data ];
			}

			$entries ++;
			$map_entries[ $answer_data ] = $entries;

		}

		$fields = $this->poll->get_fields();
		if ( ! is_null( $fields ) ) {
			foreach ( $fields as $field ) {
				$label = addslashes( $field->title );

				$slug    = isset( $field->slug ) ? $field->slug : sanitize_title( $label );
				$entries = 0;
				if ( in_array( $slug, array_keys( $map_entries ), true ) ) {
					$entries = $map_entries[ $slug ];
				}

				$attachment_fields[] = array(
					'title' => $label,
					'value' => $entries,
					'short' => false,
				);
			}

		}

		$attachments[] = array(
			'title'  => __( 'Current Poll Result', Forminator::DOMAIN ),
			'fields' => $attachment_fields,
		);


		return $attachments;
	}


	/**
	 * Slack will add a column on the title/header row
	 * its called `Slack Info` which can be translated on forminator lang
	 *
	 * @since 1.6.1
	 * @return array
	 */
	public function on_export_render_title_row() {

		$export_headers = array(
			'info' => __( 'Slack Info', Forminator::DOMAIN ),
		);

		$poll_id                = $this->poll_id;
		$poll_settings_instance = $this->poll_settings_instance;

		/**
		 * Filter Slack headers on export file
		 *
		 * @since 1.6.1
		 *
		 * @param array                                $export_headers         headers to be displayed on export file
		 * @param int                                  $poll_id                current Poll ID
		 * @param Forminator_Addon_Slack_Poll_Settings $poll_settings_instance Slack Addon Poll Settings instance
		 */
		$export_headers = apply_filters(
			'forminator_addon_slack_poll_export_headers',
			$export_headers,
			$poll_id,
			$poll_settings_instance
		);

		return $export_headers;
	}

	/**
	 * Slack will add a column that give user information whether sending data to Slack successfully or not
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
		 * Filter Slack metadata that previously saved on db to be processed
		 *
		 * @since 1.6.1
		 *
		 * @param array                                $addon_meta_data
		 * @param int                                  $poll_id                current Poll ID
		 * @param Forminator_Addon_Slack_Poll_Settings $poll_settings_instance Slack Addon Poll Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_slack_poll_metadata',
			$addon_meta_data,
			$poll_id,
			$poll_settings_instance
		);

		$export_columns = array(
			'info' => $this->get_from_addon_meta_data( $addon_meta_data, 'description', '' ),
		);

		/**
		 * Filter Slack columns to be displayed on export submissions
		 *
		 * @since 1.6.1
		 *
		 * @param array                                $export_columns         column to be exported
		 * @param int                                  $poll_id                current Poll ID
		 * @param Forminator_Form_Entry_Model          $entry_model            Form Entry Model
		 * @param array                                $addon_meta_data        meta data saved by addon on entry fields
		 * @param Forminator_Addon_Slack_Poll_Settings $poll_settings_instance Slack Addon Poll Settings instance
		 */
		$export_columns = apply_filters(
			'forminator_addon_slack_poll_export_columns',
			$export_columns,
			$poll_id,
			$entry_model,
			$addon_meta_data,
			$poll_settings_instance
		);

		return $export_columns;
	}

}
