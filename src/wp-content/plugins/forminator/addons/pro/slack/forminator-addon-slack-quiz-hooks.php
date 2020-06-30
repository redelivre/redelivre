<?php

/**
 * Class Forminator_Addon_Slack_Quiz_Hooks
 *
 * @since 1.6.2
 *
 */
class Forminator_Addon_Slack_Quiz_Hooks extends Forminator_Addon_Quiz_Hooks_Abstract {

	/**
	 * Addon instance are auto available form abstract
	 * Its added here for development purpose,
	 * Auto-complete will resolve addon directly to `Slack` instance instead of the abstract
	 * And its public properties can be exposed
	 *
	 * @since 1.6.2
	 * @var Forminator_Addon_Slack
	 */
	protected $addon;

	/**
	 * Quiz Settings Instance
	 *
	 * @since 1.6.2
	 * @var Forminator_Addon_Slack_Quiz_Settings | null
	 */
	protected $quiz_settings_instance;

	/**
	 * Save status of request sent and received for each connected Slack Connection
	 *
	 * @since 1.6.2
	 *
	 * @param array $submitted_data
	 * @param array $form_entry_fields
	 *
	 * @return array
	 */
	public function add_entry_fields( $submitted_data, $form_entry_fields = array() ) {

		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;

		/**
		 * Filter Slack submitted quiz data to be processed
		 *
		 * @since 1.6.2
		 *
		 * @param array                                $submitted_data
		 * @param int                                  $quiz_id                current Quiz ID
		 * @param Forminator_Addon_Slack_Quiz_Settings $quiz_settings_instance Slack Addon Quiz Settings instance
		 */
		$submitted_data = apply_filters(
			'forminator_addon_slack_quiz_submitted_data',
			$submitted_data,
			$quiz_id,
			$quiz_settings_instance
		);

		$addon_setting_values = $this->quiz_settings_instance->get_quiz_settings_values();

		$data = array();

		/**
		 * Fires before send message to Slack
		 *
		 * @since 1.6.2
		 *
		 * @param int                                  $quiz_id                current Quiz ID
		 * @param array                                $submitted_data
		 * @param Forminator_Addon_Slack_Quiz_Settings $quiz_settings_instance Slack Addon Quiz Settings instance
		 */
		do_action( 'forminator_addon_slack_quiz_before_send_message', $quiz_id, $submitted_data, $quiz_settings_instance );

		foreach ( $addon_setting_values as $key => $addon_setting_value ) {
			// save it on entry field, with name `status-$MULTI_ID`, and value is the return result on sending data to slack
			if ( $quiz_settings_instance->is_multi_quiz_settings_complete( $key ) ) {
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
		 * @since 1.6.2
		 *
		 * @param array                                $entry_fields
		 * @param int                                  $quiz_id                current Quiz ID
		 * @param array                                $submitted_data
		 * @param Forminator_Addon_Slack_Quiz_Settings $quiz_settings_instance Slack Addon Quiz Settings instance
		 */
		$data = apply_filters(
			'forminator_addon_slack_quiz_entry_fields',
			$entry_fields,
			$quiz_id,
			$submitted_data,
			$quiz_settings_instance
		);

		return $data;

	}

	/**
	 * Get status on send message to Slack
	 *
	 * @since 1.6.2
	 *
	 * @param $connection_id
	 * @param $submitted_data
	 * @param $connection_settings
	 * @param $quiz_entry_fields
	 *
	 * @return array `is_sent` true means its success send data to Slack, false otherwise
	 */
	private function get_status_on_send_message( $connection_id, $submitted_data, $connection_settings, $quiz_entry_fields ) {
		// initialize as null
		$api = null;

		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;
		$quiz_settings          = $this->quiz_settings_instance->get_quiz_settings();

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

			// {quiz_name_replace}
			$text_message = str_ireplace( '{quiz_name}', forminator_get_name_from_model( $this->quiz ), $text_message );

			$attachments = $this->get_quiz_data_as_attachments( $submitted_data, $quiz_entry_fields );

			/**
			 * Filter `attachments` to passed onto API
			 *
			 * @since 1.4
			 *
			 * @param string                               $card_name
			 * @param int                                  $quiz_id                Current Quiz id
			 * @param string                               $connection_id          ID of current connection
			 * @param array                                $submitted_data
			 * @param array                                $connection_settings    current connection setting, contains options of like `name`, `target_id` etc
			 * @param array                                $quiz_entry_fields      default entry fields of form
			 * @param array                                $quiz_settings          Displayed Quiz settings
			 * @param Forminator_Addon_Slack_Quiz_Settings $quiz_settings_instance Slack Addon Quiz Settings instance
			 */
			$attachments = apply_filters(
				'forminator_addon_slack_quiz_message_attachments',
				$attachments,
				$quiz_id,
				$connection_id,
				$submitted_data,
				$connection_settings,
				$quiz_entry_fields,
				$quiz_settings,
				$quiz_settings_instance
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
			 * @param int                                  $quiz_id                Current Quiz id
			 * @param string                               $connection_id          ID of current connection
			 * @param array                                $submitted_data
			 * @param array                                $connection_settings    current connection setting, contains options of like `name`, `target_id` etc
			 * @param array                                $quiz_entry_fields      default entry fields of form
			 * @param array                                $quiz_settings          Displayed Quiz settings
			 * @param Forminator_Addon_Slack_Quiz_Settings $quiz_settings_instance Slack Addon Quiz Settings instance
			 */
			$args = apply_filters(
				'forminator_addon_slack_quiz_send_message_args',
				$args,
				$quiz_id,
				$connection_id,
				$submitted_data,
				$connection_settings,
				$quiz_entry_fields,
				$quiz_settings,
				$quiz_settings_instance
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
	 * Get Quiz Data as attachments
	 *
	 * @since 1.6.2
	 *
	 * @param $submitted_data
	 * @param $quiz_entry_fields
	 *
	 * @return array
	 */
	public function get_quiz_data_as_attachments( $submitted_data, $quiz_entry_fields ) {
		$attachments = array();


		/**
		 * Attachment 1
		 * Answers
		 *  - Questions
		 *  - Answer
		 */
		$answers         = array();
		$correct_answers = 0;
		$total_answers   = 0;
		$nowrong_result  = '';
		if ( is_array( $quiz_entry_fields ) && isset( $quiz_entry_fields[0] ) ) {
			$quiz_entry = $quiz_entry_fields[0];
			if ( isset( $quiz_entry['name'] ) && isset( $quiz_entry['value'] ) && 'entry' === $quiz_entry['name'] ) {
				if ( is_array( $quiz_entry['value'] ) ) {

					// KNOWLEDGE
					if ( 'knowledge' === $this->quiz->quiz_type ) {
						foreach ( $quiz_entry['value'] as $data ) {
							$question   = isset( $data['question'] ) ? $data['question'] : '';
							$answer     = isset( $data['answer'] ) ? $data['answer'] : '';
							$is_correct = isset( $data['isCorrect'] ) ? $data['isCorrect'] : false;

							$answers[] = array(
								'question'   => $question,
								'answer'     => $answer,
								'is_correct' => $is_correct,
								'result'     => $is_correct ? __( 'Correct', Forminator::DOMAIN ) : __( 'Incorrect', Forminator::DOMAIN ),
							);
							if ( $is_correct ) {
								$correct_answers ++;
							}
							$total_answers ++;
						}
					} elseif ( 'nowrong' === $this->quiz->quiz_type ) {
						if ( isset( $quiz_entry['value'][0] )
						     && is_array( $quiz_entry['value'][0] )
						     && isset( $quiz_entry['value'][0]['value'] )
						     && is_array( $quiz_entry['value'][0]['value'] ) ) {

							$quiz_entry = $quiz_entry['value'][0]['value'];

							$nowrong_result = ( isset( $quiz_entry['result'] ) && isset( $quiz_entry['result']['title'] ) ) ? $quiz_entry['result']['title'] : '';

							$entry_questions = ( isset( $quiz_entry['answers'] ) && is_array( $quiz_entry['answers'] ) ) ? $quiz_entry['answers'] : array();

							foreach ( $entry_questions as $entry_question ) {
								$question = isset( $entry_question['question'] ) ? $entry_question['question'] : '';
								$answer   = isset( $entry_question['answer'] ) ? $entry_question['answer'] : '';

								$answers[] = array(
									'question'   => $question,
									'answer'     => $answer,
									'result'     => $nowrong_result,
									'is_correct' => true,
								);
							}
						}
					}

				}
			}
		}

		foreach ( $answers as $answer ) {
			$attachment = array(
				'title' => $answer['question'],
			);
			if ( 'knowledge' === $this->quiz->quiz_type ) {
				$attachment['color'] = $answer['is_correct'] ? 'good' : 'danger';
			}

			$attachment_field     = array(
				'title' => '',
				'value' => $answer['answer'],
				'short' => false,
			);
			$attachment['fields'] = array( $attachment_field );

			$attachments[] = $attachment;
		}


		/**
		 * Attachment 2
		 * Result
		 */
		$attachment_fields = array();

		if ( 'knowledge' === $this->quiz->quiz_type ) {
			$attachment_fields[] = array(
				'title' => __( 'Correct Answers', Forminator::DOMAIN ),
				'value' => $correct_answers,
				'short' => true,
			);
			$attachment_fields[] = array(
				'title' => __( 'Total Answers', Forminator::DOMAIN ),
				'value' => $total_answers,
				'short' => true,
			);
		} elseif ( 'nowrong' === $this->quiz->quiz_type ) {
			$attachment_fields[] = array(
				'title' => $nowrong_result,
				'value' => '',
				'short' => false,
			);
		}

		$attachments[] = array(
			'title'  => __( 'Quiz Result', Forminator::DOMAIN ),
			'fields' => $attachment_fields,
			'color'  => 'warning',
		);


		return $attachments;
	}


	/**
	 * Slack will add a column on the title/header row
	 * its called `Slack Info` which can be translated on forminator lang
	 *
	 * @since 1.6.2
	 * @return array
	 */
	public function on_export_render_title_row() {

		$export_headers = array(
			'info' => __( 'Slack Info', Forminator::DOMAIN ),
		);

		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;

		/**
		 * Filter Slack headers on export file
		 *
		 * @since 1.6.2
		 *
		 * @param array                                $export_headers         headers to be displayed on export file
		 * @param int                                  $quiz_id                current Quiz ID
		 * @param Forminator_Addon_Slack_Quiz_Settings $quiz_settings_instance Slack Addon Quiz Settings instance
		 */
		$export_headers = apply_filters(
			'forminator_addon_slack_quiz_export_headers',
			$export_headers,
			$quiz_id,
			$quiz_settings_instance
		);

		return $export_headers;
	}

	/**
	 * Slack will add a column that give user information whether sending data to Slack successfully or not
	 * It will only add one column even its multiple connection, every connection will be separated by comma
	 *
	 * @since 1.6.2
	 *
	 * @param Forminator_Form_Entry_Model $entry_model
	 * @param                             $addon_meta_data
	 *
	 * @return array
	 */
	public function on_export_render_entry( Forminator_Form_Entry_Model $entry_model, $addon_meta_data ) {

		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;

		/**
		 *
		 * Filter Slack metadata that previously saved on db to be processed
		 *
		 * @since 1.6.2
		 *
		 * @param array                                $addon_meta_data
		 * @param int                                  $quiz_id                current Quiz ID
		 * @param Forminator_Addon_Slack_Quiz_Settings $quiz_settings_instance Slack Addon Quiz Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_slack_quiz_metadata',
			$addon_meta_data,
			$quiz_id,
			$quiz_settings_instance
		);

		$export_columns = array(
			'info' => $this->get_from_addon_meta_data( $addon_meta_data, 'description', '' ),
		);

		/**
		 * Filter Slack columns to be displayed on export submissions
		 *
		 * @since 1.6.2
		 *
		 * @param array                                $export_columns         column to be exported
		 * @param int                                  $quiz_id                current Quiz ID
		 * @param Forminator_Form_Entry_Model          $entry_model            Form Entry Model
		 * @param array                                $addon_meta_data        meta data saved by addon on entry fields
		 * @param Forminator_Addon_Slack_Quiz_Settings $quiz_settings_instance Slack Addon Quiz Settings instance
		 */
		$export_columns = apply_filters(
			'forminator_addon_slack_quiz_export_columns',
			$export_columns,
			$quiz_id,
			$entry_model,
			$addon_meta_data,
			$quiz_settings_instance
		);

		return $export_columns;
	}

	/**
	 * It wil add new row on entry table of submission page, with couple of subentries
	 * subentries included are defined in @see Forminator_Addon_Slack_Quiz_Hooks::get_additional_entry_item()
	 *
	 * @since 1.6.1
	 *
	 * @param Forminator_Form_Entry_Model $entry_model
	 * @param                             $addon_meta_data
	 *
	 * @return array
	 */
	public function on_render_entry( Forminator_Form_Entry_Model $entry_model, $addon_meta_data ) {

		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;

		/**
		 *
		 * Filter Slack metadata that previously saved on db to be processed
		 *
		 * @since 1.6.2
		 *
		 * @param array                                $addon_meta_data
		 * @param int                                  $quiz_id                current Quiz ID
		 * @param Forminator_Addon_Slack_Quiz_Settings $quiz_settings_instance Slack Addon Quiz Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_quiz_slack_metadata',
			$addon_meta_data,
			$quiz_id,
			$quiz_settings_instance
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
	 * @since 1.6.1
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
	 * - Integration Name : its defined by user when they adding Slack integration on their quiz
	 * - Sent To Slack : will be Yes/No value, that indicates whether sending data to Slack API was successful
	 * - Info : Text that are generated by addon when building and sending data to Slack @see Forminator_Addon_Slack_Quiz_Hooks::add_entry_fields()
	 * - Below subentries will be added if full log enabled, @see Forminator_Addon_Slack::is_show_full_log() @see FORMINATOR_ADDON_SLACK_SHOW_FULL_LOG
	 *      - API URL : URL that wes requested when sending data to Slack
	 *      - Data sent to Slack : encoded body request that was sent
	 *      - Data received from Slack : json encoded body response that was received
	 *
	 * @param $addon_meta_data
	 *
	 * @since 1.6.1
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

}
