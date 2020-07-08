<?php

/**
 * Class Forminator_Addon_Zapier_Quiz_Hooks
 *
 * @since 1.6.2
 *
 */
class Forminator_Addon_Zapier_Quiz_Hooks extends Forminator_Addon_Quiz_Hooks_Abstract {

	/**
	 * Addon instance are auto available form abstract
	 * Its added here for development purpose,
	 * Auto-complete will resolve addon directly to `Zapier` instance instead of the abstract
	 * And its public properties can be exposed
	 *
	 * @since 1.6.2
	 * @var Forminator_Addon_Zapier
	 */
	protected $addon;

	/**
	 * Quiz Settings Instance
	 *
	 * @since 1.6.2
	 * @var Forminator_Addon_Zapier_Quiz_Settings | null
	 */
	protected $quiz_settings_instance;

	/**
	 * Forminator_Addon_Zapier_Quiz_Hooks constructor.
	 *
	 * @since 1.6.2
	 *
	 * @param Forminator_Addon_Abstract $addon
	 * @param                           $quiz_id
	 *
	 * @throws Forminator_Addon_Exception
	 */
	public function __construct( Forminator_Addon_Abstract $addon, $quiz_id ) {
		parent::__construct( $addon, $quiz_id );
		$this->_submit_quiz_error_message = __( 'Zapier failed to process submitted data. Please check your form and try again', Forminator::DOMAIN );
	}

	/**
	 * Save status of request sent and received for each connected zap(s)
	 *
	 * @since 1.6.2
	 *
	 * @param array $submitted_data
	 * @param array $current_entry_fields
	 *
	 * @return array
	 */
	public function add_entry_fields( $submitted_data, $current_entry_fields = array() ) {

		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;

		/**
		 * Filter zapier submitted form data to be processed
		 *
		 * @since 1.6.2
		 *
		 * @param array                                 $submitted_data
		 * @param int                                   $quiz_id                current Form ID
		 * @param Forminator_Addon_Zapier_Quiz_Settings $quiz_settings_instance Zapier Addon Quiz Settings instance
		 */
		$submitted_data = apply_filters(
			'forminator_addon_zapier_quiz_submitted_data',
			$submitted_data,
			$quiz_id,
			$quiz_settings_instance
		);

		forminator_addon_maybe_log( __METHOD__, $submitted_data );

		$addon_setting_values = $this->quiz_settings_instance->get_quiz_settings_values();
		$quiz_settings        = $this->quiz_settings_instance->get_quiz_settings();

		$data = array();

		/**
		 * Fires before sending data to Webhook URL(s)
		 *
		 * @since 1.6.2
		 *
		 * @param int                                   $quiz_id                current Quiz ID
		 * @param array                                 $submitted_data
		 * @param Forminator_Addon_Zapier_Quiz_Settings $quiz_settings_instance Zapier Addon Quiz Settings instance
		 */
		do_action( 'forminator_addon_zapier_quiz_before_post_to_webhook', $quiz_id, $submitted_data, $quiz_settings_instance );

		foreach ( $addon_setting_values as $key => $addon_setting_value ) {
			// save it on entry field, with name `status-$MULTI_ID`, and value is the return result on sending data to zapier
			$data[] = array(
				'name'  => 'status-' . $key,
				'value' => $this->get_status_on_send_data( $key, $submitted_data, $addon_setting_value, $quiz_settings, $current_entry_fields ),
			);
		}

		$entry_fields = $data;
		/**
		 * Filter zapier entry fields to be saved to entry model
		 *
		 * @since 1.6.2
		 *
		 * @param array                                 $entry_fields
		 * @param int                                   $quiz_id                current Quiz ID
		 * @param array                                 $submitted_data
		 * @param Forminator_Addon_Zapier_Quiz_Settings $quiz_settings_instance Zapier Quiz Settings instance
		 */
		$data = apply_filters(
			'forminator_addon_zapier_quiz_entry_fields',
			$entry_fields,
			$quiz_id,
			$submitted_data,
			$quiz_settings_instance
		);

		return $data;

	}

	/**
	 * Get status on sending data to zapier
	 *
	 * @since 1.6.2
	 *
	 * @param $connection_id
	 * @param $submitted_data
	 * @param $connection_settings
	 * @param $quiz_settings
	 * @param $current_entry_fields
	 *
	 * @return array `is_sent` true means its success send data to zapier, false otherwise
	 */
	private function get_status_on_send_data( $connection_id, $submitted_data, $connection_settings, $quiz_settings, $current_entry_fields ) {
		// initialize as null
		$zapier_api = null;

		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;

		//check required fields
		try {
			if ( ! isset( $connection_settings['webhook_url'] ) ) {
				throw new Forminator_Addon_Zapier_Exception( __( 'Webhook URL is not properly setup', Forminator::DOMAIN ) );
			}

			$endpoint = $connection_settings['webhook_url'];
			/**
			 * Filter Endpoint Webhook URL to send
			 *
			 * @since 1.6.2
			 *
			 * @param string $endpoint
			 * @param int    $quiz_id             current Form ID
			 * @param array  $connection_settings current connection setting, it contains `name` and `webhook_url`
			 */
			$endpoint = apply_filters(
				'forminator_addon_zapier_quiz_endpoint',
				$endpoint,
				$quiz_id,
				$connection_settings
			);

			$zapier_api = $this->addon->get_api( $endpoint );

			$args = $this->build_post_data( $current_entry_fields );

			/**
			 * Filter arguments to passed on to Zapier Webhook API
			 *
			 * @since 1.6.2
			 *
			 * @param array                                 $args
			 * @param int                                   $quiz_id                Current Quiz id
			 * @param string                                $connection_id          ID of current connection
			 * @param array                                 $submitted_data
			 * @param array                                 $connection_settings    current connection setting, contains `name` and `webhook_url`
			 * @param array                                 $quiz_settings          Displayed Quiz settings
			 * @param Forminator_Addon_Zapier_Quiz_Settings $quiz_settings_instance Zapier Quiz Settings instance
			 */
			$args = apply_filters(
				'forminator_addon_zapier_quiz_post_to_webhook_args',
				$args,
				$quiz_id,
				$connection_id,
				$submitted_data,
				$connection_settings,
				$quiz_settings,
				$quiz_settings_instance
			);

			$zapier_api->post_( $args );

			forminator_addon_maybe_log( __METHOD__, 'Success Send Data' );

			return array(
				'is_sent'         => true,
				'connection_name' => $connection_settings['name'],
				'description'     => __( 'Successfully send data to Zapier', Forminator::DOMAIN ),
				'data_sent'       => $zapier_api->get_last_data_sent(),
				'data_received'   => $zapier_api->get_last_data_received(),
				'url_request'     => $zapier_api->get_last_url_request(),
			);

		} catch ( Forminator_Addon_Zapier_Exception $e ) {
			forminator_addon_maybe_log( __METHOD__, 'Failed to Send to Zapier' );

			return array(
				'is_sent'         => false,
				'description'     => $e->getMessage(),
				'connection_name' => $connection_settings['name'],
				'data_sent'       => ( ( $zapier_api instanceof Forminator_Addon_Zapier_Wp_Api ) ? $zapier_api->get_last_data_sent() : array() ),
				'data_received'   => ( ( $zapier_api instanceof Forminator_Addon_Zapier_Wp_Api ) ? $zapier_api->get_last_data_received() : array() ),
				'url_request'     => ( ( $zapier_api instanceof Forminator_Addon_Zapier_Wp_Api ) ? $zapier_api->get_last_url_request() : '' ),
			);
		}
	}

	/**
	 * Build sample data form current fields
	 *
	 * @since 1.6.2
	 *
	 * @param array $quiz_entry_fields
	 *
	 * @return array
	 */
	private function build_post_data( $quiz_entry_fields ) {
		$sample = array();

		$sample['quiz-name'] = forminator_get_name_from_model( $this->quiz );

		$answers         = array();
		$correct_answers = 0;
		$total_answers   = 0;
		$nowrong_result  = '';
		$questions       = $this->quiz->questions;

		if ( is_array( $quiz_entry_fields ) && isset( $quiz_entry_fields[0] ) ) {
			$quiz_entry = $quiz_entry_fields[0];
			if ( isset( $quiz_entry['name'] ) && isset( $quiz_entry['value'] ) && 'entry' === $quiz_entry['name'] ) {
				if ( is_array( $quiz_entry['value'] ) ) {

					// KNOWLEDGE
					if ( 'knowledge' === $this->quiz->quiz_type ) {
						foreach ( $quiz_entry['value'] as $key => $data ) {
							$question_id = ( ( isset( $questions[ $key ] ) && isset( $questions[ $key ]['slug'] ) ) ? $questions[ $key ]['slug'] : uniqid() );
							// bit cleanup
							$question_id = str_replace( 'question-', '', $question_id );

							$question   = isset( $data['question'] ) ? $data['question'] : '';
							$answer     = isset( $data['answer'] ) ? $data['answer'] : '';
							$is_correct = isset( $data['isCorrect'] ) ? $data['isCorrect'] : false;

							$answers[ $question_id ] = array(
								'question'   => $question,
								'answer'     => $answer,
								'is_correct' => $is_correct,
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

							foreach ( $entry_questions as $key => $entry_question ) {
								$question_id = ( ( isset( $questions[ $key ] ) && isset( $questions[ $key ]['slug'] ) ) ? $questions[ $key ]['slug'] : uniqid() );
								// bit cleanup
								$question_id = str_replace( 'question-', '', $question_id );

								$question = isset( $entry_question['question'] ) ? $entry_question['question'] : '';
								$answer   = isset( $entry_question['answer'] ) ? $entry_question['answer'] : '';

								$answers[ $question_id ] = array(
									'question' => $question,
									'answer'   => $answer,
								);
							}
						}
					}
				}
			}
		}

		$sample['answers'] = $answers;
		$result            = array();

		if ( 'knowledge' === $this->quiz->quiz_type ) {
			$result['correct'] = $correct_answers;
			$result['answers'] = $total_answers;

		} elseif ( 'nowrong' === $this->quiz->quiz_type ) {
			$result['result'] = $nowrong_result;
		}

		$sample['result'] = $result;

		// wrap in array as zapier best practices
		return array( $sample );
	}

	/**
	 * Zapier will add a column on the title/header row
	 * its called `Zapier Info` which can be translated on forminator lang
	 *
	 * @since 1.6.2
	 * @return array
	 */
	public function on_export_render_title_row() {

		$export_headers = array(
			'info' => __( 'Zapier Info', Forminator::DOMAIN ),
		);

		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;

		/**
		 * Filter zapier headers on export file
		 *
		 * @since 1.6.2
		 *
		 * @param array                                 $export_headers         headers to be displayed on export file
		 * @param int                                   $quiz_id                current Form ID
		 * @param Forminator_Addon_Zapier_Quiz_Settings $quiz_settings_instance Zapier Quiz Settings instance
		 */
		$export_headers = apply_filters(
			'forminator_addon_zapier_quiz_export_headers',
			$export_headers,
			$quiz_id,
			$quiz_settings_instance
		);

		return $export_headers;
	}

	/**
	 * Zapier will add a column that give user information whether sending data to zapier successfully or not
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
		 * Filter zapier metadata that previously saved on db to be processed
		 *
		 * @since 1.1
		 *
		 * @param array                                 $addon_meta_data
		 * @param int                                   $quiz_id                current Quiz ID
		 * @param Forminator_Addon_Zapier_Quiz_Settings $quiz_settings_instance Zapier Quiz Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_zapier_quiz_metadata',
			$addon_meta_data,
			$quiz_id,
			$quiz_settings_instance
		);

		$export_columns = array(
			'info' => $this->get_from_addon_meta_data( $addon_meta_data, 'description', '' ),
		);

		/**
		 * Filter zapier columns to be displayed on export submissions
		 *
		 * @since 1.6.2
		 *
		 * @param array                                 $export_columns         column to be exported
		 * @param int                                   $quiz_id                current Quiz ID
		 * @param Forminator_Form_Entry_Model           $entry_model            Form Entry Model
		 * @param array                                 $addon_meta_data        meta data saved by addon on entry fields
		 * @param Forminator_Addon_Zapier_Quiz_Settings $quiz_settings_instance Zapier Quiz Settings instance
		 */
		$export_columns = apply_filters(
			'forminator_addon_zapier_quiz_export_columns',
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
	 * subentries included are defined in @see Forminator_Addon_Zapier_Quiz_Hooks::get_additional_entry_item()
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
		 * Filter Zapier metadata that previously saved on db to be processed
		 *
		 * @since 1.6.2
		 *
		 * @param array                                 $addon_meta_data
		 * @param int                                   $quiz_id                current Quiz ID
		 * @param Forminator_Addon_Zapier_Quiz_Settings $quiz_settings_instance Zapier Addon Quiz Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_quiz_zapier_metadata',
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
	 * Loop through addon meta data on multiple Zapier setup(s)
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
	 * - Integration Name : its defined by user when they adding Zapier integration on their quiz
	 * - Sent To Zapier : will be Yes/No value, that indicates whether sending data to Zapier API was successful
	 * - Info : Text that are generated by addon when building and sending data to Zapier @see Forminator_Addon_Zapier_Quiz_Hooks::add_entry_fields()
	 * - Below subentries will be added if full log enabled, @see Forminator_Addon_Zapier::is_show_full_log() @see FORMINATOR_ADDON_ZAPIER_SHOW_FULL_LOG
	 *      - API URL : URL that wes requested when sending data to Zapier
	 *      - Data sent to Zapier : encoded body request that was sent
	 *      - Data received from Zapier : json encoded body response that was received
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
			'label' => __( 'Zapier Integration', Forminator::DOMAIN ),
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
				'label' => __( 'Sent To Zapier', Forminator::DOMAIN ),
				'value' => $is_sent,
			);
		}

		if ( isset( $status['description'] ) ) {
			$sub_entries[] = array(
				'label' => __( 'Info', Forminator::DOMAIN ),
				'value' => $status['description'],
			);
		}

		if ( Forminator_Addon_Zapier::is_show_full_log() ) {
			// too long to be added on entry data enable this with `define('FORMINATOR_ADDON_ZAPIER_SHOW_FULL_LOG', true)`
			if ( isset( $status['url_request'] ) ) {
				$sub_entries[] = array(
					'label' => __( 'API URL', Forminator::DOMAIN ),
					'value' => $status['url_request'],
				);
			}

			if ( isset( $status['data_sent'] ) ) {
				$sub_entries[] = array(
					'label' => __( 'Data sent to Zapier', Forminator::DOMAIN ),
					'value' => '<pre class="sui-code-snippet">' . wp_json_encode( $status['data_sent'], JSON_PRETTY_PRINT ) . '</pre>',
				);
			}

			if ( isset( $status['data_received'] ) ) {
				$sub_entries[] = array(
					'label' => __( 'Data received from Zapier', Forminator::DOMAIN ),
					'value' => '<pre class="sui-code-snippet">' . wp_json_encode( $status['data_received'], JSON_PRETTY_PRINT ) . '</pre>',
				);
			}
		}

		$additional_entry_item['sub_entries'] = $sub_entries;

		// return single array
		return $additional_entry_item;
	}
}
