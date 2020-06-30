<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Front action for polls
 *
 * @since 1.0
 */
class Forminator_Poll_Front_Action extends Forminator_Front_Action {

	/**
	 * Entry type
	 *
	 * @var string
	 */
	public $entry_type = 'poll';

	/**
	 * Response message
	 *
	 * @var array
	 */
	private static $response = array();

	/**
	 * Handle submit
	 *
	 * @since 1.0
	 * @since 1.1 Refactor $_POST to use `get_post_data`
	 */
	public function handle_submit() {
		$post_data = $this->get_post_data();
		$form_id   = isset( $post_data['form_id'] ) ? sanitize_text_field( $post_data['form_id'] ) : false;
		if ( $form_id ) {

			/**
			 * Action called before full form submit
			 *
			 * @since 1.0.2
			 *
			 * @param int $form_id - the form id
			 */
			do_action( 'forminator_polls_before_handle_submit', $form_id );

			$response = $this->handle_form( $form_id );

			/**
			 * Filter submit response
			 *
			 * @since 1.0.2
			 *
			 * @param array $response - the post response
			 * @param int $form_id - the form id
			 *
			 * @return array $response
			 */
			$response = apply_filters( 'forminator_polls_submit_response', $response, $form_id );

			/**
			 * Action called after full form submit
			 *
			 * @since 1.0.2
			 *
			 * @param int $form_id - the form id
			 * @param array $response - the post response
			 */
			do_action( 'forminator_polls_after_handle_submit', $form_id, $response );

			if ( $response && is_array( $response ) ) {
				if ( isset( $response['url'] ) ) {
					$url = apply_filters( 'forminator_poll_submit_url', $response['url'], $form_id );
					wp_safe_redirect( $url );
					exit;
				} else {
					self::$response = $response;
					add_action( 'forminator_poll_post_message', array( $this, 'form_response_message' ), 10, 1 );
				}

			}
		}
	}

	/**
	 * Update payment amount
	 *
	 * @since 1.7.3
	 */
	public function update_payment_amount() {
		// Update payment amount
	}

	/**
	 * Save entry
	 *
	 * @since 1.6
	 * @return void
	 */
	public function save_entry_preview() {
		if ( $this->validate_ajax( 'forminator_submit_form', 'POST', 'forminator_nonce' ) ) {
			$post_data = $this->get_post_data();
			$form_id   = isset( $post_data['form_id'] ) ? sanitize_text_field( $post_data['form_id'] ) : false;
			if ( $form_id ) {

				/**
				 * Action called before poll ajax
				 *
				 * @since 1.0.2
				 *
				 * @param int $form_id - the form id
				 */
				do_action( 'forminator_polls_before_save_entry', $form_id );

				$response = $this->handle_form( $form_id, true );

				/**
				 * Filter ajax response
				 *
				 * @since 1.0.2
				 *
				 * @param array $response - the post response
				 * @param int $form_id - the form id
				 *
				 * @return array $response
				 */
				$response = apply_filters( 'forminator_polls_ajax_submit_response', $response, $form_id );


				/**
				 * Action called after form ajax
				 *
				 * @since 1.0.2
				 *
				 * @param int $form_id - the form id
				 * @param array $response - the post response
				 */
				do_action( 'forminator_polls_after_save_entry', $form_id, $response );

				if ( $response && is_array( $response ) ) {
					if ( ! $response['success'] ) {
						wp_send_json_error( $response );
					} else {
						wp_send_json_success( $response );
					}
				}
			}
		}
	}

	/**
	 * Save entry
	 *
	 * @since 1.0
	 * @since 1.1 refactor $_POST to `get_post_data`
	 * @return void /json Json response
	 */
	public function save_entry() {
		if ( $this->validate_ajax( 'forminator_submit_form', 'POST', 'forminator_nonce' ) ) {
			$post_data = $this->get_post_data();
			$form_id   = isset( $post_data['form_id'] ) ? sanitize_text_field( $post_data['form_id'] ) : false;
			if ( $form_id ) {

				/**
				 * Action called before poll ajax
				 *
				 * @since 1.0.2
				 *
				 * @param int $form_id - the form id
				 */
				do_action( 'forminator_polls_before_save_entry', $form_id );

				$response = $this->handle_form( $form_id );

				/**
				 * Filter ajax response
				 *
				 * @since 1.0.2
				 *
				 * @param array $response - the post response
				 * @param int $form_id - the form id
				 *
				 * @return array $response
				 */
				$response = apply_filters( 'forminator_polls_ajax_submit_response', $response, $form_id );


				/**
				 * Action called after form ajax
				 *
				 * @since 1.0.2
				 *
				 * @param int $form_id - the form id
				 * @param array $response - the post response
				 */
				do_action( 'forminator_polls_after_save_entry', $form_id, $response );

				if ( $response && is_array( $response ) ) {
					if ( ! $response['success'] ) {
						wp_send_json_error( $response );
					} else {
						wp_send_json_success( $response );
					}
				}
			}
		}
	}

	/**
	 * Handle form action
	 *
	 * @since 1.0
	 *
	 * @param int $form_id
	 * @param bool $preview
	 *
	 * @return bool|array
	 */
	private function handle_form( $form_id, $preview = false ) {
		$poll = Forminator_Poll_Form_Model::model()->load( $form_id );
		if ( is_object( $poll ) ) {

			// disable submissions if not published
			if ( Forminator_Poll_Form_Model::STATUS_PUBLISH !== $poll->status ) {
				return array(
					'message' => __( "Poll submissions disabled.", Forminator::DOMAIN ),
					'notice'  => 'error',
					'success' => false,
				);
			}

			$user_can_vote = $poll->current_user_can_vote();
			$prevent_store = $poll->is_prevent_store();

			// If preview, prevent storing
			if ( $preview ) {
				$prevent_store = true;
			}

			/**
			 * Filter to check if current user can vote
			 *
			 * @since 1.0.2
			 *
			 * @param bool $user_can_vote - if can vote depending on above conditions
			 * @param int $form_id - the form id
			 *
			 * @return bool $user_can_vote - true|false
			 */
			$user_can_vote = apply_filters( 'forminator_poll_handle_form_user_can_vote', $user_can_vote, $form_id );

			$post_data = $this->get_post_data();

			if ( $user_can_vote ) {
				$field_data  = isset( $post_data[ $form_id ] ) ? $post_data[ $form_id ] : false;
				$extra_field = isset( $post_data[ $form_id . '-extra' ] ) ? $post_data[ $form_id . '-extra' ] : false;
				if ( $field_data && ! empty( $field_data ) ) {
					$entry             = new Forminator_Form_Entry_Model();
					$entry->entry_type = $this->entry_type;
					$entry->form_id    = $form_id;
					// get fields labels
					$fields_labels    = $poll->pluck_fields_array( 'title', 'element_id', '1' );
					$field_data_array = array(
						array(
							'name'  => $field_data,
							'value' => isset( $fields_labels[ $field_data ] ) ? $fields_labels[ $field_data ] : '1',
						),
					);
					if ( $poll->is_method_browser_cookie() ) {
						$this->set_vote_browser_cookie( $form_id );
					} else {
						$field_data_array[] = array(
							'name'  => '_forminator_user_ip',
							'value' => Forminator_Geo::get_user_ip(),
						);
					}
					if ( $extra_field && ! empty( $extra_field ) ) {
						$field_data_array[] = array(
							'name'  => 'extra',
							'value' => $extra_field,
						);

						/**
						 * Handle spam protection
						 * Add-ons use this filter to check if content has spam data
						 *
						 * @since 1.0.2
						 *
						 * @param bool false - defauls to false
						 * @param array $field_data_array - the entry data
						 * @param int $form_id - the form id
						 * @param string $form_type - the form type. In this case defaults to 'poll'
						 *
						 * @return bool true|false
						 */
						$is_spam = apply_filters( 'forminator_spam_protection', false, $field_data_array, $form_id, 'poll' );

						$entry->is_spam = $is_spam;
					}

					// If preview, skip integrations
					if ( ! $preview ) {
						//ADDON on_form_submit
						$addon_error = $this->attach_addons_on_poll_submit( $form_id, $poll );

						if ( true !== $addon_error ) {
							$response = array(
								'message' => $addon_error,
								'notice'  => 'error',
								'success' => false,
							);

							return $response;
						}
					}

					if ( $prevent_store || $entry->save() ) {

						/**
						 * Filter saved data before persisted into the database
						 *
						 * @since 1.0.2
						 *
						 * @param array $field_data_array - the entry data
						 * @param int $form_id - the form id
						 *
						 * @return array $field_data_array
						 */
						$field_data_array = apply_filters( 'forminator_polls_submit_field_data', $field_data_array, $form_id );

						/**
						 * Action called before setting fields to database
						 *
						 * @since 1.0.2
						 *
						 * @param Forminator_Form_Entry_Model $entry - the entry model
						 * @param int $form_id - the form id
						 * @param array $field_data_array - the entry data
						 *
						 */
						do_action( 'forminator_polls_submit_before_set_fields', $entry, $form_id, $field_data_array );

						// ADDON add_entry_fields
						$added_data_array = $this->attach_addons_add_entry_fields( $form_id, $poll, $field_data_array );
						$added_data_array = array_merge( $field_data_array, $added_data_array );
						$setting          = $poll->settings;


						$entry->set_fields( $added_data_array );

						//ADDON after_entry_saved
						$this->attach_addons_after_entry_saved( $form_id, $entry );

						// Email
						$forminator_mail_sender = new Forminator_Poll_Front_Mail();
						$forminator_mail_sender->process_mail( $poll, $post_data, $entry );

						if ( isset( $setting['results-behav'] ) && ( 'show_after' === $setting['results-behav'] || 'link_on' === $setting['results-behav'] ) ) {

							$url       = $post_data['_wp_http_referer'];
							$render_id = $post_data['render_id'];
							$url       = add_query_arg(
								array(
									'saved'     => 'true',
									'form_id'   => $form_id,
									'render_id' => $render_id,
								),
								$url
							);

							$url       = apply_filters( 'forminator_poll_submit_url', $url, $form_id );
							$response  = array(
								'form_id' => $form_id,
								'message' => __( 'Your vote has been saved', Forminator::DOMAIN ),
								'notice'  => 'success',
								'success' => true,
							);

							if ( ! isset( $setting['enable-ajax'] ) || empty( $setting['enable-ajax'] ) ) {
								$is_ajax_enabled = false;
							} else {
								$is_ajax_enabled = filter_var( $setting['enable-ajax'], FILTER_VALIDATE_BOOLEAN );
							}

							// Results behav
							$response['results_behav'] = (string) $setting['results-behav'];

							// Votes count
							$response['votes_count'] = filter_var( 'false', FILTER_VALIDATE_BOOLEAN );
							if ( isset( $setting['show-votes-count'] ) ) {
								$response['votes_count'] = filter_var( $setting['show-votes-count'], FILTER_VALIDATE_BOOLEAN );
							}

							// Chart basic colors
							$response['grids_color'] = ( isset( $setting['grid_lines'] ) && ! empty( $setting['grid_lines'] ) ) ? $setting['grid_lines'] : '#E5E5E5';
							$response['labels_color'] = ( isset( $setting['grid_labels'] ) && ! empty( $setting['grid_labels'] ) ) ? $setting['grid_labels'] : '#777771';
							$response['onchart_label'] = ( isset( $setting['onbar_votes'] ) && ! empty( $setting['onbar_votes'] ) ) ? $setting['onbar_votes'] : '#333333';

							// Tooltips
							$response['tooltips_bg'] = ( isset( $setting['tooltips_background'] ) && ! empty( $setting['tooltips_background'] ) ) ? $setting['tooltips_background'] : '#333333';
							$response['tooltips_color'] = ( isset( $setting['tooltips_text'] ) && ! empty( $setting['tooltips_text'] ) ) ? $setting['tooltips_text'] : '#FFFFFF';

							// On chart label text
							$response['votes_text'] = (string) esc_html__( 'vote(s)', Forminator::DOMAIN );

							// View results link
							$response['results_link'] = sprintf(
								'<a href="%s" class="forminator-link">%s</a>',
								esc_url( $url ),
								esc_html__( 'View results', Forminator::DOMAIN )
							);

							if ( $is_ajax_enabled ) {

								// ajax enabled send result data to front end
								$response['chart_data']  = $this->get_chart_data( $poll );

								if ( isset( $setting['enable-votes-limit'] ) && 'true' === $setting['enable-votes-limit'] ) {
									$response['back_button'] = '<button type="button" class="forminator-button forminator-button-back">' . __( 'Back To Poll', Forminator::DOMAIN ) . '</button>';
								}
							} else {

								// its not ajax enabled, send url result to front end
								$response['url'] = $url;
							}

							return $response;
						}

						return array(
							'form_id' => $form_id,
							'message' => __( 'Your vote has been saved', Forminator::DOMAIN ),
							'notice'  => 'success',
							'success' => true,
						);
					}
				} else {
					return array(
						'form_id' => $form_id,
						'message' => __( "You need to select a poll option", Forminator::DOMAIN ),
						'notice'  => 'error',
						'success' => false,
					);
				}
			} else {
				return array(
					'form_id' => $form_id,
					'message' => __( "You have already submitted a vote to this poll", Forminator::DOMAIN ),
					'notice'  => 'notice',
					'success' => false,
				);
			}
		}

		return false;
	}

	/**
	 * Get Chart data of Poll
	 *
	 * @param Forminator_Poll_Form_Model $poll
	 *
	 * @return array
	 */
	private function get_chart_data( Forminator_Poll_Form_Model $poll ) {

		$chart_colors         = forminator_get_poll_chart_colors( $poll->id );
		$default_chart_colors = $chart_colors;
		$chart_datas          = array();

		$form_settings        = $poll->settings;
		$number_votes_enabled = false; // TO-DO: Remove later. This will be handled through ChartJS function.

		$fields_array = $poll->get_fields_as_array();
		$map_entries  = Forminator_Form_Entry_Model::map_polls_entries( $poll->id, $fields_array );
		$fields       = $poll->get_fields();

		if ( ! is_null( $fields ) ) {

			foreach ( $fields as $field ) {

				// Label
				$label = addslashes( $field->title );

				// Votes
				$slug    = isset( $field->slug ) ? $field->slug : sanitize_title( $label );
				$entries = 0;

				if ( in_array( $slug, array_keys( $map_entries ), true ) ) {
					$entries = $map_entries[ $slug ];
				}

				$color = $field->color;

				if( empty( $color ) ) {
					// Colors
					if ( empty( $chart_colors ) ) {
						$chart_colors = $default_chart_colors;
					}

					$color = array_shift( $chart_colors );
				}

				$chart_datas[] = array(
					(string) $label,
					(int) $entries,
					(string) $color
				);
			}
		}

		return $chart_datas;

	}

	/**
	 * Response message
	 *
	 * @since 1.0
	 */
	public function form_response_message( $form_id ) {

		if ( empty( $response ) || !is_array( $response ) ) {
			return;
		}

		if ( isset( $response['form_id'] ) && intval( $response['form_id'] ) !== $form_id ) {
			return;
		} ?>

		<p><?php echo $response['message']; // WPCS: XSS ok. ?></p>

	<?php }

	/**
	 * Executor On form submit for attached addons
	 *
	 * @see   Forminator_Addon_Poll_Hooks_Abstract::on_poll_submit()
	 * @since 1.6.1
	 *
	 * @param                              $poll_id
	 * @param Forminator_Poll_Form_Model $poll_model
	 *
	 * @return bool true on success|string error message from addon otherwise
	 */
	private function attach_addons_on_poll_submit( $poll_id, Forminator_Poll_Form_Model $poll_model ) {
		$submitted_data = forminator_addon_format_poll_submitted_data( $_POST, $_FILES );// WPCS: CSRF ok. its already validated before.
		//find is_form_connected
		$connected_addons = forminator_get_addons_instance_connected_with_poll( $poll_id );

		foreach ( $connected_addons as $connected_addon ) {
			try {
				$poll_hooks = $connected_addon->get_addon_poll_hooks( $poll_id );
				if ( $poll_hooks instanceof Forminator_Addon_Poll_Hooks_Abstract ) {
					$addon_return = $poll_hooks->on_poll_submit( $submitted_data );
					if ( true !== $addon_return ) {
						return $poll_hooks->get_submit_poll_error_message();
					}
				}
			} catch ( Exception $e ) {
				forminator_addon_maybe_log( $connected_addon->get_slug(), 'failed to attach_addons_on_poll_submit', $e->getMessage() );
			}

		}

		return true;
	}

	/**
	 * Executor to add more entry fields for attached addons
	 *
	 * @see   Forminator_Addon_Poll_Hooks_Abstract::add_entry_fields()
	 *
	 * @since 1.6.1
	 *
	 * @param                              $poll_id
	 * @param Forminator_Poll_Form_Model $poll_form_model
	 * @param array $current_entry_fields
	 *
	 * @return array added fields to entry
	 */
	private function attach_addons_add_entry_fields( $poll_id, Forminator_Poll_Form_Model $poll_form_model, $current_entry_fields ) {
		$additional_fields_data = array();
		$submitted_data         = forminator_addon_format_poll_submitted_data( $_POST, $_FILES );// WPCS: CSRF ok. its already validated before.
		//find is_poll_connected
		$connected_addons = forminator_get_addons_instance_connected_with_poll( $poll_id );

		foreach ( $connected_addons as $connected_addon ) {
			try {
				$poll_hooks = $connected_addon->get_addon_poll_hooks( $poll_id );
				if ( $poll_hooks instanceof Forminator_Addon_Poll_Hooks_Abstract ) {
					$addon_fields = $poll_hooks->add_entry_fields( $submitted_data, $current_entry_fields );
					//reformat additional fields
					$addon_fields           = self::format_addon_additional_fields( $connected_addon, $addon_fields );
					$additional_fields_data = array_merge( $additional_fields_data, $addon_fields );
				}
			} catch ( Exception $e ) {
				forminator_addon_maybe_log( $connected_addon->get_slug(), 'failed to poll add_entry_fields', $e->getMessage() );
			}

		}

		return $additional_fields_data;
	}


	/**
	 * Executor action for attached addons after entry saved on storage
	 *
	 * @see   Forminator_Addon_Poll_Hooks_Abstract::after_entry_saved()
	 *
	 * @since 1.6.1
	 *
	 * @param                             $poll_id
	 * @param Forminator_Form_Entry_Model $entry_model
	 */
	private function attach_addons_after_entry_saved( $poll_id, Forminator_Form_Entry_Model $entry_model ) {
		//find is_form_connected
		$connected_addons = forminator_get_addons_instance_connected_with_poll( $poll_id );

		foreach ( $connected_addons as $connected_addon ) {
			try {
				$poll_hooks = $connected_addon->get_addon_poll_hooks( $poll_id );
				if ( $poll_hooks instanceof Forminator_Addon_Poll_Hooks_Abstract ) {
					$poll_hooks->after_entry_saved( $entry_model );// run and forget
				}
			} catch ( Exception $e ) {
				forminator_addon_maybe_log( $connected_addon->get_slug(), 'failed to poll attach_addons_after_entry_saved', $e->getMessage() );
			}

		}
	}

	/**
	 * Set Browser Cookie when poll submit
	 *
	 * @param $form_id
	 */
	public function set_vote_browser_cookie( $form_id ) {
		$poll        = Forminator_Poll_Form_Model::model()->load( $form_id );
		$settings    = $poll->settings;
		$duration    = 1;
		$expire      = time() + YEAR_IN_SECONDS * $duration;
		$cookie_name = 'poll-cookie-' . md5( $form_id );
		if ( $poll->is_allow_multiple_votes() ) {
			$duration           = ! empty( $settings['vote_limit_input'] ) ? absint( $settings['vote_limit_input'] ) : 1;
			$vote_limit_options = ! empty( $settings['vote_limit_options'] ) ? $settings['vote_limit_options'] : 'Y';
			switch ( $vote_limit_options ) {
				case 'h':
					$expire = time() + HOUR_IN_SECONDS * $duration;
					break;
				case 'd':
					$expire = time() + DAY_IN_SECONDS * $duration;
					break;
				case 'W':
					$expire = time() + WEEK_IN_SECONDS * $duration;
					break;
				case 'M':
					$expire = time() + MONTH_IN_SECONDS * $duration;
					break;
				case 'm':
					$expire = time() + MINUTE_IN_SECONDS * $duration;
					break;
				case 'Y':
					$expire = time() + YEAR_IN_SECONDS * $duration;
					break;
				default:
					$expire = time() + YEAR_IN_SECONDS * $duration;
					break;
			}
		}
		$current_date = date_i18n( 'Y-m-d H:i:s' );
		$secure = ( 'https' === parse_url( wp_login_url(), PHP_URL_SCHEME ) );
		setcookie( $cookie_name, $current_date, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure, true );
	}
}
