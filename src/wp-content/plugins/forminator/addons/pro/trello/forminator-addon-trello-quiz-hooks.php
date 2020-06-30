<?php

/**
 * Class Forminator_Addon_Trello_Quiz_Hooks
 *
 * @since 1.6.2
 *
 */
class Forminator_Addon_Trello_Quiz_Hooks extends Forminator_Addon_Quiz_Hooks_Abstract {

	/**
	 * Addon instance are auto available form abstract
	 * Its added here for development purpose,
	 * Auto-complete will resolve addon directly to `Trello` instance instead of the abstract
	 * And its public properties can be exposed
	 *
	 * @since 1.6.2
	 * @var Forminator_Addon_Trello
	 */
	protected $addon;

	/**
	 * Quiz Settings Instance
	 *
	 * @since 1.6.2
	 * @var Forminator_Addon_Trello_Quiz_Settings | null
	 */
	protected $quiz_settings_instance;

	/**
	 * Forminator_Addon_Trello_Quiz_Hooks constructor.
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
		$this->_submit_quiz_error_message = __( 'Trello failed to process submitted data. Please check your form and try again', Forminator::DOMAIN );
	}

	/**
	 * Save status of request sent and received for each connected Trello
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
		 * Filter Trello submitted form data to be processed
		 *
		 * @since 1.6.2
		 *
		 * @param array                                 $submitted_data
		 * @param array                                 $current_entry_fields
		 * @param int                                   $quiz_id                current Quiz ID
		 * @param Forminator_Addon_Trello_Quiz_Settings $quiz_settings_instance Trello Addon Quiz Settings instance
		 */
		$submitted_data = apply_filters(
			'forminator_addon_trello_quiz_submitted_data',
			$submitted_data,
			$current_entry_fields,
			$quiz_id,
			$quiz_settings_instance
		);

		/**
		 * Filter current form entry fields data to be processed by Trello
		 *
		 * @since 1.6.2
		 *
		 * @param array                                 $current_entry_fields
		 * @param array                                 $submitted_data
		 * @param int                                   $quiz_id                current quiz ID
		 * @param Forminator_Addon_Trello_Quiz_Settings $quiz_settings_instance Trello Addon Quiz Settings instance
		 */
		$current_entry_fields = apply_filters(
			'forminator_addon_trello_quiz_current_entry_fields',
			$current_entry_fields,
			$submitted_data,
			$quiz_id,
			$quiz_settings_instance
		);


		$addon_setting_values = $this->quiz_settings_instance->get_quiz_settings_values();

		$data = array();

		/**
		 * Fires before create card on trello
		 *
		 * @since 1.6.2
		 *
		 * @param int                                   $quiz_id                current Quiz ID
		 * @param array                                 $submitted_data
		 * @param Forminator_Addon_Trello_Quiz_Settings $quiz_settings_instance Trello Addon Quiz Settings instance
		 */
		do_action( 'forminator_addon_trello_quiz_before_create_card', $quiz_id, $submitted_data, $quiz_settings_instance );

		foreach ( $addon_setting_values as $key => $addon_setting_value ) {
			// save it on entry field, with name `status-$MULTI_ID`, and value is the return result on sending data to trello
			if ( $quiz_settings_instance->is_multi_quiz_settings_complete( $key ) ) {
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
		 * @since 1.6.2
		 *
		 * @param array                                 $entry_fields
		 * @param int                                   $quiz_id                current Quiz ID
		 * @param array                                 $submitted_data
		 * @param array                                 $current_entry_fields
		 * @param Forminator_Addon_Trello_Quiz_Settings $quiz_settings_instance Trello Addon Quiz Settings instance
		 */
		$data = apply_filters(
			'forminator_addon_trello_quiz_entry_fields',
			$entry_fields,
			$quiz_id,
			$submitted_data,
			$current_entry_fields,
			$quiz_settings_instance
		);

		return $data;

	}

	/**
	 * Get status on create Trello card
	 *
	 * @since 1.6.2
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

		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;

		//check required fields
		try {
			$api  = $this->addon->get_api();
			$args = array();

			$quiz_settings = $this->quiz_settings_instance->get_quiz_settings();

			if ( isset( $connection_settings['list_id'] ) ) {
				$args['idList'] = $connection_settings['list_id'];
			}

			if ( isset( $connection_settings['card_name'] ) ) {
				$card_name = $connection_settings['card_name'];
				// disable all_fields here
				$card_name = forminator_replace_variables( $card_name );
				// {quizname_replace}
				$card_name = str_ireplace( '{quiz_name}', forminator_get_name_from_model( $this->quiz ), $card_name );

				/**
				 * Filter Card Name to passed on to Create Trello Card API
				 *
				 * @since 1.6.2
				 *
				 * @param string                                $card_name
				 * @param int                                   $quiz_id                Current Quiz id
				 * @param string                                $connection_id          ID of current connection
				 * @param array                                 $submitted_data
				 * @param array                                 $connection_settings    current connection setting, contains options of like `name`, `list_id` etc
				 * @param array                                 $current_entry_fields   default entry fields of quiz
				 * @param array                                 $quiz_settings          Displayed Quiz settings
				 * @param Forminator_Addon_Trello_Quiz_Settings $quiz_settings_instance Trello Addon Quiz Settings instance
				 */
				$card_name    = apply_filters(
					'forminator_addon_trello_quiz_card_name',
					$card_name,
					$quiz_id,
					$connection_id,
					$submitted_data,
					$connection_settings,
					$current_entry_fields,
					$quiz_settings,
					$quiz_settings_instance
				);
				$args['name'] = $card_name;

			}

			if ( isset( $connection_settings['card_description'] ) ) {
				$card_description         = $connection_settings['card_description'];
				$quiz_answers_to_markdown = $this->quiz_answers_to_markdown( $current_entry_fields );
				$quiz_result_to_markdown  = $this->quiz_result_to_markdown( $current_entry_fields );
				$card_description         = str_ireplace( '{quiz_answer}', $quiz_answers_to_markdown, $card_description );
				$card_description         = str_ireplace( '{quiz_result}', $quiz_result_to_markdown, $card_description );
				$card_description         = forminator_replace_variables( $card_description );

				/**
				 * Filter Card Description to passed on to Create Trello Card API
				 *
				 * @since 1.6.2
				 *
				 * @param string                                $card_description
				 * @param int                                   $quiz_id                Current Quiz id
				 * @param string                                $connection_id          ID of current connection
				 * @param array                                 $submitted_data
				 * @param array                                 $connection_settings    current connection setting, contains options of like `name`, `list_id` etc
				 * @param array                                 $current_entry_fields   default entry fields of quiz
				 * @param array                                 $quiz_settings          Displayed Quiz settings
				 * @param Forminator_Addon_Trello_Quiz_Settings $quiz_settings_instance Trello Addon Quiz Settings instance
				 */
				$card_description = apply_filters(
					'forminator_addon_trello_quiz_card_description',
					$card_description,
					$quiz_id,
					$connection_id,
					$submitted_data,
					$connection_settings,
					$current_entry_fields,
					$quiz_settings,
					$quiz_settings_instance
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
			 * @since 1.6.2
			 *
			 * @param array                                 $args
			 * @param int                                   $quiz_id                Current Quiz id
			 * @param string                                $connection_id          ID of current connection
			 * @param array                                 $submitted_data
			 * @param array                                 $connection_settings    current connection setting, contains options of like `name`, `list_id` etc
			 * @param array                                 $quiz_settings          Displayed Quiz settings
			 * @param Forminator_Addon_Trello_Quiz_Settings $quiz_settings_instance Trello Addon Quiz Settings instance
			 */
			$args = apply_filters(
				'forminator_addon_trello_quiz_create_card_args',
				$args,
				$quiz_id,
				$connection_id,
				$submitted_data,
				$connection_settings,
				$quiz_settings,
				$quiz_settings_instance
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
	 * Special Replacer `{quiz_answer}` to markdown with Trello Flavour
	 *
	 * @param array $quiz_entry_fields
	 *
	 * @return string
	 */
	private function quiz_answers_to_markdown( $quiz_entry_fields ) {
		$markdown = '';

		if ( is_array( $quiz_entry_fields ) && isset( $quiz_entry_fields[0] ) ) {
			$quiz_entry = $quiz_entry_fields[0];
			if ( isset( $quiz_entry['name'] ) && isset( $quiz_entry['value'] ) && 'entry' === $quiz_entry['name'] ) {
				if ( is_array( $quiz_entry['value'] ) ) {
					if ( 'knowledge' === $this->quiz->quiz_type ) {
						foreach ( $quiz_entry['value'] as $data ) {
							$question   = isset( $data['question'] ) ? $data['question'] : '';
							$answer     = isset( $data['answer'] ) ? $data['answer'] : '';
							$is_correct = isset( $data['isCorrect'] ) ? $data['isCorrect'] : false;

							$markdown .= "###" . $question . "\n";
							$markdown .= $answer . "\n";
							$markdown .= __( 'Correct : ', Forminator::DOMAIN )
							             . '**' . ( $is_correct ? __( 'Yes', Forminator::DOMAIN ) : __( 'No', Forminator::DOMAIN ) ) . '**'
							             . "\n";
						}

					} elseif ( 'nowrong' === $this->quiz->quiz_type ) {
						if ( isset( $quiz_entry['value'][0] )
						     && is_array( $quiz_entry['value'][0] )
						     && isset( $quiz_entry['value'][0]['value'] )
						     && is_array( $quiz_entry['value'][0]['value'] ) ) {

							$quiz_entry = $quiz_entry['value'][0]['value'];

							$entry_questions = ( isset( $quiz_entry['answers'] ) && is_array( $quiz_entry['answers'] ) ) ? $quiz_entry['answers'] : array();

							foreach ( $entry_questions as $entry_question ) {
								$question = isset( $entry_question['question'] ) ? $entry_question['question'] : '';
								$answer   = isset( $entry_question['answer'] ) ? $entry_question['answer'] : '';

								$markdown .= "###" . $question . "\n";
								$markdown .= $answer . "\n";
							}
						}
					}
				}
			}
		}

		/**
		 * Filter markdown for `quiz_answer`
		 *
		 * @since 1.6.2
		 *
		 * @param string $markdown
		 * @param array  $quiz_entry_fields Entry Fields
		 */
		$markdown = apply_filters(
			'forminator_addon_trello_quiz_answer_markdown',
			$markdown,
			$quiz_entry_fields
		);

		return $markdown;
	}

	/**
	 * Special Replacer `{quiz_result}` to markdown with Trello Flavour
	 *
	 * @since 1.6.2
	 *
	 * @param array $quiz_entry_fields
	 *
	 * @return string
	 */
	private function quiz_result_to_markdown( $quiz_entry_fields ) {
		$markdown = '';

		if ( is_array( $quiz_entry_fields ) && isset( $quiz_entry_fields[0] ) ) {
			$quiz_entry = $quiz_entry_fields[0];
			if ( isset( $quiz_entry['name'] ) && isset( $quiz_entry['value'] ) && 'entry' === $quiz_entry['name'] ) {
				if ( is_array( $quiz_entry['value'] ) ) {
					if ( 'knowledge' === $this->quiz->quiz_type ) {
						$total_correct = 0;
						$total_answers = 0;
						foreach ( $quiz_entry['value'] as $data ) {
							$is_correct = isset( $data['isCorrect'] ) ? $data['isCorrect'] : false;
							if ( $is_correct ) {
								$total_correct ++;
							}
							$total_answers ++;
						}

						$markdown .= '##' . __( 'Quiz Result', Forminator::DOMAIN ) . "\n";
						$markdown .= __( 'Correct Answers : ', Forminator::DOMAIN )
						             . '**' . $total_correct . '**'
						             . "\n";
						$markdown .= __( 'Total Answers : ', Forminator::DOMAIN )
						             . '**' . $total_answers . '**'
						             . "\n";

					} elseif ( 'nowrong' === $this->quiz->quiz_type ) {
						if ( isset( $quiz_entry['value'][0] )
						     && is_array( $quiz_entry['value'][0] )
						     && isset( $quiz_entry['value'][0]['value'] )
						     && is_array( $quiz_entry['value'][0]['value'] ) ) {

							$quiz_entry     = $quiz_entry['value'][0]['value'];
							$nowrong_result = ( isset( $quiz_entry['result'] ) && isset( $quiz_entry['result']['title'] ) ) ? $quiz_entry['result']['title'] : '';

							$markdown .= '##' . __( 'Quiz Result', Forminator::DOMAIN ) . "\n";
							$markdown .= '**' . $nowrong_result . '**'
							             . "\n";

						}
					}
				}
			}
		}

		/**
		 * Filter markdown for `quiz_result`
		 *
		 * @since 1.6.2
		 *
		 * @param string $markdown
		 * @param array  $quiz_entry_fields Entry Fields
		 */
		$markdown = apply_filters(
			'forminator_addon_trello_quiz_result_markdown',
			$markdown,
			$quiz_entry_fields
		);

		return $markdown;
	}

	/**
	 * Trello will add a column on the title/header row
	 * its called `Trello Info` which can be translated on forminator lang
	 *
	 * @since 1.6.2
	 * @return array
	 */
	public function on_export_render_title_row() {

		$export_headers = array(
			'info' => __( 'Trello Info', Forminator::DOMAIN ),
		);

		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;

		/**
		 * Filter Trello headers on export file
		 *
		 * @since 1.6.2
		 *
		 * @param array                                 $export_headers         headers to be displayed on export file
		 * @param int                                   $quiz_id                current Quiz ID
		 * @param Forminator_Addon_Trello_Quiz_Settings $quiz_settings_instance Trello Addon Quiz Settings instance
		 */
		$export_headers = apply_filters(
			'forminator_addon_trello_quiz_export_headers',
			$export_headers,
			$quiz_id,
			$quiz_settings_instance
		);

		return $export_headers;
	}

	/**
	 * Trello will add a column that give user information whether sending data to Trello successfully or not
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
		 * Filter Trello metadata that previously saved on db to be processed
		 *
		 * @since 1.6.2
		 *
		 * @param array                                 $addon_meta_data
		 * @param int                                   $quiz_id                current Quiz ID
		 * @param Forminator_Addon_Trello_Quiz_Settings $quiz_settings_instance Trello Addon Quiz Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_trello_quiz_metadata',
			$addon_meta_data,
			$quiz_id,
			$quiz_settings_instance
		);

		$export_columns = array(
			'info' => $this->get_from_addon_meta_data( $addon_meta_data, 'description', '' ),
		);

		/**
		 * Filter Trello columns to be displayed on export submissions
		 *
		 * @since 1.6.2
		 *
		 * @param array                                 $export_columns         column to be exported
		 * @param int                                   $quiz_id                current Quiz ID
		 * @param Forminator_Form_Entry_Model           $entry_model            Form Entry Model
		 * @param array                                 $addon_meta_data        meta data saved by addon on entry fields
		 * @param Forminator_Addon_Trello_Quiz_Settings $quiz_settings_instance Trello Addon Quiz Settings instance
		 */
		$export_columns = apply_filters(
			'forminator_addon_trello_quiz_export_columns',
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
	 * subentries included are defined in @see Forminator_Addon_Trello_Quiz_Hooks::get_additional_entry_item()
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
		 * Filter Trello metadata that previously saved on db to be processed
		 *
		 * @since 1.6.2
		 *
		 * @param array                                $addon_meta_data
		 * @param int                                  $quiz_id                current Quiz ID
		 * @param Forminator_Addon_Trello_Quiz_Settings $quiz_settings_instance Trello Addon Quiz Settings instance
		 */
		$addon_meta_data = apply_filters(
			'forminator_addon_quiz_trello_metadata',
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
	 * Loop through addon meta data on multiple Trello setup(s)
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
	 * - Integration Name : its defined by user when they adding Trello integration on their quiz
	 * - Sent To Trello : will be Yes/No value, that indicates whether sending data to Trello API was successful
	 * - Info : Text that are generated by addon when building and sending data to Trello @see Forminator_Addon_Trello_Quiz_Hooks::add_entry_fields()
	 * - Below subentries will be added if full log enabled, @see Forminator_Addon_Trello::is_show_full_log() @see FORMINATOR_ADDON_TRELLO_SHOW_FULL_LOG
	 *      - API URL : URL that wes requested when sending data to Trello
	 *      - Data sent to Trello : encoded body request that was sent
	 *      - Data received from Trello : json encoded body response that was received
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
}
