<?php

require_once dirname( __FILE__ ) . '/class-forminator-addon-mailchimp-form-settings-exception.php';

/**
 * Class Forminator_Addon_Mailchimp_Form_Settings
 * Form Settings Mailchimp Process
 *
 * @since 1.0 Mailchimp Addon
 */
class Forminator_Addon_Mailchimp_Form_Settings extends Forminator_Addon_Form_Settings_Abstract {

	/**
	 * @since 1.0 Mailchimp Addon
	 * @var Forminator_Addon_Mailchimp
	 */
	protected $addon;

	/**
	 * Forminator_Addon_Mailchimp_Form_Settings constructor.
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param Forminator_Addon_Abstract $addon
	 * @param                           $form_id
	 *
	 * @throws Forminator_Addon_Exception
	 */
	public function __construct( Forminator_Addon_Abstract $addon, $form_id ) {
		parent::__construct( $addon, $form_id );

		$this->_update_form_settings_error_message = __(
			'The update to your settings for this form failed, check the form input and try again.',
			Forminator::DOMAIN
		);
	}

	/**
	 * For settings Wizard steps
	 *
	 * @since 1.0 Mailchimp Addon
	 * @return array
	 */
	public function form_settings_wizards() {
		// already filtered on Abstract
		// numerical array steps
		return array(
			// 0
			array(
				'callback'     => array( $this, 'mail_list_limit' ),
				'is_completed' => array( $this, 'step_mail_list_limit_is_completed' ),
			),
			// 1
			array(
				'callback'     => array( $this, 'choose_mail_list' ),
				'is_completed' => array( $this, 'step_choose_mail_list_is_completed' ),
			),
			// 2
			array(
				'callback'     => array( $this, 'map_fields' ),
				'is_completed' => array( $this, 'step_map_fields_is_completed' ),
			),
		);
	}

	/**
	 * Choose Mail Limit wizard
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param $submitted_data
	 *
	 * @return array
	 */
	public function mail_list_limit( $submitted_data ) {

		$this->addon_form_settings = $this->get_form_settings_values();
		$current_data              = array();

		if ( isset( $submitted_data['mail_list_limit'] ) ) {
			$current_data['mail_list_limit'] = $submitted_data['mail_list_limit'];
		} elseif ( isset( $this->addon_form_settings['mail_list_limit'] ) ) {
			$current_data['mail_list_limit'] = $this->addon_form_settings['mail_list_limit'];
		} else {
			$current_data['mail_list_limit'] = 10;
		}

		forminator_addon_maybe_log( __METHOD__, 'current_data', $current_data );

		$is_submit = ! empty( $submitted_data );

		if ( $is_submit ) {
			forminator_addon_maybe_log( __METHOD__, '$submitted_data', $submitted_data );

			$this->addon_form_settings['mail_list_limit'] = $submitted_data['mail_list_limit'];

			$this->save_form_settings_values( $this->addon_form_settings );
		}
		$buttons = array();
		// add disconnect button if already is_form_connected
		if ( $this->addon->is_form_connected( $this->form_id ) ) {
			$buttons['disconnect']['markup'] = Forminator_Addon_Mailchimp::get_button_markup(
				esc_html__( 'DISCONNECT', Forminator::DOMAIN ),
				'sui-button-ghost sui-tooltip sui-tooltip-top-center forminator-addon-form-disconnect',
				esc_html__( 'Disconnect Mailchimp from this Form.', Forminator::DOMAIN )
			);
		}

		$buttons['next']['markup'] = '<div class="sui-actions-right">' .
									Forminator_Addon_Mailchimp::get_button_markup( esc_html__( 'Next', Forminator::DOMAIN ), 'forminator-addon-next' ) .
									'</div>';

		return array(
			'html'       => '<div class="sui-box-content integration-header"><h3 class="sui-box-title" id="dialogTitle2">' . __( 'Enter list limit', Forminator::DOMAIN ) . '</h3></div>
							<form>
								<div class="sui-form-field">
									<label class="sui-label">' . __( 'List limit', Forminator::DOMAIN ) . '</label>
									<div class="sui-form-field">
										<input name="mail_list_limit"
											placeholder="' . sprintf( __( 'Example: 10', Forminator::DOMAIN ), 'Mailchimp' ) . '"
											value="' . esc_attr( $current_data['mail_list_limit'] ) . '"
											class="sui-form-control" />
											<span class="sui-description">' . __( 'By default list limit will be 10.', Forminator::DOMAIN ) . '</spna>
									
									</div>
								</div>
							</form>',
			'redirect'   => false,
			'buttons'    => $buttons,
			'has_errors' => '',
			'size'       => 'small',
		);
	}

	/**
	 * Choose Mail wizard
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param $submitted_data
	 *
	 * @return array
	 */
	public function choose_mail_list( $submitted_data ) {

		// already filtered on Forminator_Addon_Abstract::get_wizard
		$this->addon_form_settings = $this->get_form_settings_values();
		$current_data              = array(
			'mail_list_id'         => '',
			'enable_double_opt_in' => '',
			'enable_gdpr'          => '',
			'gdpr_text'            => '',
		);

		foreach ( $current_data as $key => $current_field ) {
			if ( isset( $submitted_data[ $key ] ) ) {
				$current_data[ $key ] = $submitted_data[ $key ];
			} elseif ( isset( $this->addon_form_settings[ $key ] ) ) {
				$current_data[ $key ] = $this->addon_form_settings[ $key ];
			}
		}

		forminator_addon_maybe_log( __METHOD__, 'current_data', $current_data );

		$is_submit = ! empty( $submitted_data );

		$error_message        = '';
		$input_error_messages = array();

		$html_select_mail_list = '';
		$html_field_mail_list  = '';
		$list_array            = array();

		try {
			$api = $this->addon->get_api();
			if ( ! empty( $this->addon_form_settings['mail_list_limit'] ) ) {
				$list_array['count'] = $this->addon_form_settings['mail_list_limit'];
			}
			$request_mail_lists = $api->get_lists( $list_array );

			// Get mailchimp list to be selected, bail on empty
			if ( ! isset( $request_mail_lists->lists ) || empty( $request_mail_lists->lists ) || ! is_array( $request_mail_lists->lists ) ) {
				throw new Forminator_Addon_Mailchimp_Exception( 'Your Mailchimp Lists is empty, please create one.' );
			}
			$mail_lists = $request_mail_lists->lists;

			// build html select for mail list
			$html_select_mail_list  = '<select name="mail_list_id" class="sui-select sui-form-control">';
			$html_select_mail_list .= '<option value="">' . __( 'None', Forminator::DOMAIN ) . '</option>';

			foreach ( $mail_lists as $mail_list ) {
				$html_select_mail_list
					.= '<option value="' . esc_attr( $mail_list->id ) . '" ' . selected(
						$current_data['mail_list_id'],
						$mail_list->id,
						false
					) . '>' . esc_html( $mail_list->name ) . '</option>';
			}
			$html_select_mail_list .= '</select>';

			// logic when user submit mail list
			if ( $is_submit ) {
				forminator_addon_maybe_log( __METHOD__, '$submitted_data', $submitted_data );
				$mail_list_name = $this->get_choosen_mail_list_name( $mail_lists, $submitted_data );
				forminator_addon_maybe_log( __METHOD__, '$mail_list_name', $mail_list_name );
				if ( empty( $mail_list_name ) ) {
					throw new Forminator_Addon_Mailchimp_Form_Settings_Exception( __( 'Please select valid Email List', Forminator::DOMAIN ), 'mail_list_id' );
				}
				$this->addon_form_settings['mail_list_id']   = $submitted_data['mail_list_id'];
				$this->addon_form_settings['mail_list_name'] = $mail_list_name;

				if ( isset( $submitted_data['enable_double_opt_in'] ) ) {
					$this->addon_form_settings['enable_double_opt_in'] = $submitted_data['enable_double_opt_in'];
				} else {
					$this->addon_form_settings['enable_double_opt_in'] = 0;
				}

				// GDPR fields
				if ( Forminator_Addon_Mailchimp::is_enable_gdpr() ) {
					if ( isset( $submitted_data['enable_gdpr'] ) ) {
						$this->addon_form_settings['enable_gdpr'] = $submitted_data['enable_gdpr'];
					} else {
						$this->addon_form_settings['enable_gdpr'] = 0;
					}
					if ( isset( $submitted_data['gdpr_text'] ) ) {
						$this->addon_form_settings['gdpr_text'] = $submitted_data['gdpr_text'];
					}
				}

				$this->save_form_settings_values( $this->addon_form_settings );
			}

			$html_field_mail_list
				= '<div class="sui-form-field">
						<label class="sui-label">' . __( 'Email List', Forminator::DOMAIN ) . '</label>
						' . $html_select_mail_list . '
					</div>';

		} catch ( Forminator_Addon_Mailchimp_Form_Settings_Exception $e ) {
			// send errors with related input
			$input_error_messages = $e->get_input_exceptions();
			if ( isset( $input_error_messages['mail_list_id'] ) ) {
				$html_field_mail_list
					= '<div class="sui-form-field sui-form-field-error">
							<label class="sui-label">' . __( 'Email List', Forminator::DOMAIN ) . '</label>
							' . $html_select_mail_list . '
							<span class="sui-error-message">' . esc_html( $input_error_messages['mail_list_id'] ) . '</span>
						</div>';
			}
		} catch ( Forminator_Addon_Mailchimp_Exception $e ) {
			// send error back to client
			$error_message = '<div class="sui-notice sui-notice-error"><p>' . $e->getMessage() . '</p></div>';
		}

		$buttons = array();
		// add disconnect button if already is_form_connected
		if ( $this->addon->is_form_connected( $this->form_id ) ) {
			$buttons['disconnect']['markup'] = Forminator_Addon_Mailchimp::get_button_markup(
				esc_html__( 'Deactivate', Forminator::DOMAIN ),
				'sui-button-ghost sui-tooltip sui-tooltip-top-center forminator-addon-form-disconnect',
				esc_html__( 'Deactivate Mailchimp from this Form.', Forminator::DOMAIN )
			);
		}

		$buttons['next']['markup'] = '<div class="sui-actions-right">' .
									Forminator_Addon_Mailchimp::get_button_markup( esc_html__( 'Next', Forminator::DOMAIN ), 'forminator-addon-next' ) .
									'</div>';

		$gdpr_fields = '';
		if ( Forminator_Addon_Mailchimp::is_enable_gdpr() ) {
			$gdpr_fields = '<div class="sui-form-field">' .
						'<label class="sui-label">' . __( 'Enable GDPR', Forminator::DOMAIN ) . '</label>
								<input type="checkbox" name="enable_gdpr" value="1" ' . checked( 1, $current_data['enable_double_opt_in'], false ) . '>
							</div>

							<div class="sui-form-field">
								<label class="sui-label">' . __( 'GDPR Text', Forminator::DOMAIN ) . '</label>
								<textarea name="gdpr_text">' . $current_data['gdpr_text'] . '</textarea>
							</div>';
		}

		return array(
			'html'       => '<div class="sui-box-content integration-header"><h3 class="sui-box-title" id="dialogTitle2">' . __( 'Choose your list', Forminator::DOMAIN ) . '</h3>
							<span class="sui-description" style="margin-top: 20px;">' . __( 'Choose the list you want to send form data to.', Forminator::DOMAIN ) . '</span>
							' . $error_message . '</div>
							<form enctype="multipart/form-data">
								' . $html_field_mail_list . '
								<div class="sui-form-field">
									<label class="sui-toggle">
										<input type="checkbox"
										name="enable_double_opt_in"
										id="forminator_addon_mailchimp_enable_double_opt_in"
										value="1" ' . checked( 1, $current_data['enable_double_opt_in'], false ) . '>
										<span class="sui-toggle-slider"></span>
									</label>
									<span class="sui-toggle-label" for="forminator_addon_mailchimp_enable_double_opt_in">' . __( 'Use Double Opt in', Forminator::DOMAIN ) . '</span>
								</div>
								' . $gdpr_fields . '
							</form>',
			'redirect'   => false,
			'buttons'    => $buttons,
			'has_errors' => ( ! empty( $error_message ) || ! empty( $input_error_messages ) ),
			'size'       => 'small',
			'has_back'   => true,
		);

	}

	/**
	 * Step mapping fields on wizard
	 *
	 * @since 1.0 Mailchimp Addon
	 * @since 1.2 Refactor `hasBack` to `has_back`
	 *
	 * @param $submitted_data
	 *
	 * @return array
	 */
	public function map_fields( $submitted_data ) {
		$this->addon_form_settings = $this->get_form_settings_values();
		$is_close                  = false;

		$is_submit             = ! empty( $submitted_data );
		$error_message         = '';
		$html_input_map_fields = '';
		$input_error_messages  = array();

		try {
			// get merge fields
			$mailchimp_fields_list_request = $this->addon->get_api()->get_list_merge_fields( $this->addon_form_settings['mail_list_id'], array() );
			$mailchimp_required_fields     = array();
			$mailchimp_required_fields_ids = array();
			$mailchimp_fields_list         = array();
			if ( isset( $mailchimp_fields_list_request->merge_fields ) && is_array( $mailchimp_fields_list_request->merge_fields ) && ! empty( $mailchimp_fields_list_request->merge_fields ) ) {
				$mailchimp_fields_list = $mailchimp_fields_list_request->merge_fields;
			}

			$current_data = array( 'fields_map' => array() );
			foreach ( $mailchimp_fields_list as $item ) {
				if ( $item->required ) {
					$mailchimp_required_fields []    = $item;
					$mailchimp_required_fields_ids[] = $item->merge_id;
				}
				if ( 'address' === $item->type ) {
					$address_fields = $this->mail_address_fields();
					foreach ( $address_fields as $addr => $address ) {
						$current_data['fields_map'][ $item->tag ][ $addr ] = '';
					}
				} else {
					$current_data['fields_map'][ $item->tag ] = '';
				}
			}

			// find type of email
			$email_fields = array();
			foreach ( $this->form_fields as $form_field ) {
				if ( 'email' === $form_field['type'] ) {
					$email_fields[] = $form_field;
				}
			}

			// EMAIL
			$current_data['fields_map']['EMAIL'] = '';
			if ( isset( $submitted_data['fields_map']['EMAIL'] ) ) {
				$current_data['fields_map']['EMAIL'] = $submitted_data['fields_map']['EMAIL'];
			} elseif ( isset( $this->addon_form_settings['fields_map']['EMAIL'] ) ) {
				$current_data['fields_map']['EMAIL'] = $this->addon_form_settings['fields_map']['EMAIL'];
			}

			foreach ( $current_data['fields_map'] as $key => $current_field ) {
				if ( is_array( $current_field ) ) {
					foreach ( $current_field as $a => $current ) {
						if ( isset( $submitted_data['fields_map'][ $key ][ $a ] ) ) {
							$current_data['fields_map'][ $key ][ $a ] = $submitted_data['fields_map'][ $key ][ $a ];
						} elseif ( isset( $this->addon_form_settings['fields_map'][ $key ] ) ) {
							$current_data['fields_map'][ $key ][ $a ] = $this->addon_form_settings['fields_map'][ $key ][ $a ];
						}
					}
				} else {
					if ( isset( $submitted_data['fields_map'][ $key ] ) ) {
						$current_data['fields_map'][ $key ] = $submitted_data['fields_map'][ $key ];
					} elseif ( isset( $this->addon_form_settings['fields_map'][ $key ] ) ) {
						$current_data['fields_map'][ $key ] = $this->addon_form_settings['fields_map'][ $key ];
					}
				}
			}

			/** Build table map fields input */
			ob_start();
			$this->get_input_map_fields( $email_fields, $mailchimp_fields_list, $current_data );
			$html_input_map_fields = ob_get_clean();

			if ( $is_submit ) {
				$this->step_map_fields_validate( $mailchimp_fields_list, $mailchimp_required_fields, $submitted_data );
				$this->save_form_settings_values( $this->addon_form_settings );
				$is_close = true;
			}
		} catch ( Forminator_Addon_Mailchimp_Form_Settings_Exception $e ) {
			$input_error_messages = $e->get_input_exceptions();
			if ( ! empty( $html_input_map_fields ) ) {
				foreach ( $input_error_messages as $input_id => $message ) {
					if ( is_array( $message ) ) {
						foreach ( $message as $addr => $m ) {
							$html_input_map_fields = str_replace( '{{$error_css_class_' . $input_id . '_' . $addr . '}}', 'sui-form-field-error', $html_input_map_fields );
							$html_input_map_fields = str_replace( '{{$error_message_' . $input_id . '_' . $addr . '}}', '<span class="sui-error-message">' . esc_html( $m ) . '</span>', $html_input_map_fields );
						}
					} else {
						$html_input_map_fields = str_replace( '{{$error_css_class_' . $input_id . '}}', 'sui-form-field-error', $html_input_map_fields );
						$html_input_map_fields = str_replace( '{{$error_message_' . $input_id . '}}', '<span class="sui-error-message">' . esc_html( $message ) . '</span>', $html_input_map_fields );
					}
				}
			}
		} catch ( Forminator_Addon_Mailchimp_Exception $e ) {
			$error_message = '<div class="sui-notice sui-notice-error"><p>' . $e->getMessage() . '</p></div>';
		}

		//cleanup map fields input markup placeholder
		if ( ! empty( $html_input_map_fields ) ) {
			$replaced_html_input_map_fields = $html_input_map_fields;
			$replaced_html_input_map_fields = preg_replace( '/\{\{\$error_css_class_(.+)\}\}/', '', $replaced_html_input_map_fields );
			$replaced_html_input_map_fields = preg_replace( '/\{\{\$error_message_(.+)\}\}/', '', $replaced_html_input_map_fields );
			if ( ! is_null( $replaced_html_input_map_fields ) ) {
				$html_input_map_fields = $replaced_html_input_map_fields;
			}
		}

		$buttons = array();
		if ( $this->addon->is_form_connected( $this->form_id ) ) {
			$buttons['disconnect']['markup'] = Forminator_Addon_Mailchimp::get_button_markup(
				esc_html__( 'Deactivate', Forminator::DOMAIN ),
				'sui-button-ghost sui-tooltip sui-tooltip-top-center forminator-addon-form-disconnect',
				esc_html__( 'Deactivate Mailchimp from this Form.', Forminator::DOMAIN )
			);
		}

		$buttons['next']['markup'] = '<div class="sui-actions-right">' .
									Forminator_Addon_Mailchimp::get_button_markup( esc_html__( 'Save', Forminator::DOMAIN ), 'sui-button-primary forminator-addon-finish' ) .
									'</div>';

		$notification = array();

		if ( $is_submit && empty( $error_message ) && empty( $input_error_messages ) ) {
			$notification = array(
				'type' => 'success',
				'text' => '<strong>' . $this->addon->get_title() . '</strong> ' . __( 'is activated successfully.' ),
			);
		}

		return array(
			'html'         => '<div class="sui-box-content integration-header"><h3 class="sui-box-title" id="dialogTitle2">' . __( 'Assign Fields', Forminator::DOMAIN ) . '</h3>
							<span class="sui-description" style="margin-top: 20px;">' . __( 'Lastly, match up your form fields with your campaign fields to make sure we\'re sending data to the right place.', Forminator::DOMAIN ) . '</span>
							' . $error_message . '</div>
							<form enctype="multipart/form-data">
								' . $html_input_map_fields . '
							</form>',
			'redirect'     => false,
			'is_close'     => $is_close,
			'buttons'      => $buttons,
			'has_errors'   => ! empty( $error_message ) || ! empty( $input_error_messages ),
			'notification' => $notification,
			'size'         => 'normal',
			'has_back'     => true,
		);
	}

	/**
	 * Get input of Map Fields
	 * its table with html select options as input
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param $email_fields
	 * @param $mailchimp_fields_list
	 * @param $current_data
	 */
	private function get_input_map_fields( $email_fields, $mailchimp_fields_list, $current_data ) {
		?>
		<table class="sui-table">
			<thead>
			<tr>
				<th>Mailchimp Field</th>
				<th>Forminator Field</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td>Email Address <span class="integrations-required-field">*</span></td>
				<td>
					<div class="sui-form-field {{$error_css_class_EMAIL}}">
						<select class="sui-select" name="fields_map[EMAIL]">
							<?php if ( empty( $email_fields ) ) { ?>
								<option value=""><?php esc_html_e( 'None', Forminator::DOMAIN ); ?></option>
							<?php } else { ?>
								<?php foreach ( $email_fields as $email_field ) { ?>
									<option value="<?php echo esc_attr( $email_field['element_id'] ); ?>"
										<?php selected( $current_data['fields_map']['EMAIL'], $email_field['element_id'] ); ?>>
										<?php echo esc_html( $email_field['field_label'] . ' | ' . $email_field['element_id'] ); ?>
									</option>
								<?php } ?>
							<?php } ?>
						</select>
						{{$error_message_EMAIL}}
					</div>
				</td>
			</tr>
			<?php
			foreach ( $mailchimp_fields_list as $item ) {
				$require_sign = $item->required ? '<span class="integrations-required-field">*</span>' : '';
				if ( 'address' === $item->type ) {
					$address_fields = $this->mail_address_fields();
					foreach ( $address_fields as $addr => $address ) {
						$address_require_sign = 'addr2' === $addr ? '' : $require_sign;
						?>
						<tr>
							<td><?php echo esc_html( $item->name . ' - ' . $address ); ?> <?php echo esc_html( $address_require_sign ); ?></td>
							<td>
								<div class="sui-form-field {{$error_css_class_<?php echo esc_attr( $item->tag ); ?>_<?php echo esc_attr( $addr ); ?>}}">
									<select class="sui-select" name="fields_map[<?php echo esc_attr( $item->tag ); ?>][<?php echo esc_attr( $addr ); ?>]">
										<option value=""><?php esc_html_e( 'None', Forminator::DOMAIN ); ?></option>
										<?php foreach ( $this->form_fields as $form_field ) { ?>
											<option value="<?php echo esc_attr( $form_field['element_id'] ); ?>"
												<?php selected( $current_data['fields_map'][ $item->tag ][ $addr ], $form_field['element_id'] ); ?>>
												<?php echo esc_html( $form_field['field_label'] . ' | ' . $form_field['element_id'] ); ?>
											</option>
										<?php } ?>
									</select>
									{{$error_message_<?php echo esc_attr( $item->tag ); ?>_<?php echo esc_attr( $addr ); ?>}}
								</div>
							</td>
						</tr>
						<?php
					}
				} else {
					?>
					<tr>
						<td><?php echo esc_html( $item->name ); ?> <?php echo esc_html( $require_sign ); ?></td>
						<td>
							<div class="sui-form-field {{$error_css_class_<?php echo esc_attr( $item->tag ); ?>}}">
								<select class="sui-select" name="fields_map[<?php echo esc_attr( $item->tag ); ?>]">
									<option value=""><?php esc_html_e( 'None', Forminator::DOMAIN ); ?></option>
									<?php foreach ( $this->form_fields as $form_field ) { ?>
										<option value="<?php echo esc_attr( $form_field['element_id'] ); ?>"
											<?php selected( $current_data['fields_map'][ $item->tag ], $form_field['element_id'] ); ?>>
											<?php echo esc_html( $form_field['field_label'] . ' | ' . $form_field['element_id'] ); ?>
										</option>
									<?php } ?>
								</select>
								{{$error_message_<?php echo esc_attr( $item->tag ); ?>}}
							</div>
						</td>
					</tr>
					<?php
				}
			}
			?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Get mail List Name of submitted data
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param $mail_lists
	 * @param $submitted_data
	 *
	 * @return string
	 */
	private function get_choosen_mail_list_name( $mail_lists, $submitted_data ) {
		$mail_list_id = isset( $submitted_data['mail_list_id'] ) ? $submitted_data['mail_list_id'] : 0;

		$mail_list_name = '';
		foreach ( $mail_lists as $mail_list ) {
			if ( $mail_list_id === $mail_list->id ) {
				$mail_list_name = $mail_list->name;
				break;
			}
		}

		return $mail_list_name;
	}

	/**
	 * Validate submitted data by user as expected by merge field on mailchimp mail list
	 *
	 * @since 1.0 Mailchimp Addon
	 *
	 * @param $mailchimp_fields_list
	 * @param $mailchimp_required_fields
	 * @param $post_data
	 *
	 * @return array current addon form settings
	 * @throws Forminator_Addon_Mailchimp_Exception
	 * @throws Forminator_Addon_Mailchimp_Form_Settings_Exception
	 */
	public function step_map_fields_validate( $mailchimp_fields_list, $mailchimp_required_fields, $post_data ) {

		$forminator_field_element_ids = array();
		$address_value                = array();
		foreach ( $this->form_fields as $form_field ) {
			$forminator_field_element_ids[] = $form_field['element_id'];
		}

		//map mailchimp maped with tag as its key
		$tag_maped_mailchimp_fields = array();
		foreach ( $mailchimp_fields_list as $item ) {
			$tag_maped_mailchimp_fields[ $item->tag ] = $item;
		}

		if ( ! isset( $post_data['fields_map'] ) ) {
			$this->_update_form_settings_error_message = 'Please assign fields.';
			throw new Forminator_Addon_Mailchimp_Exception( $this->_update_form_settings_error_message );
		}
		$post_data = $post_data['fields_map'];

		if ( ! isset( $this->addon_form_settings['fields_map'] ) ) {
			$this->addon_form_settings['fields_map'] = array();
		}

		// set fields_map from post_data for reuse
		foreach ( $post_data as $mailchimp_field_tag => $forminator_field_id ) {
			$this->addon_form_settings['fields_map'][ $mailchimp_field_tag ] = $post_data[ $mailchimp_field_tag ];
		}

		$input_exceptions = new Forminator_Addon_Mailchimp_Form_Settings_Exception();
		// EMAIL : super required**
		if ( ! isset( $post_data['EMAIL'] ) || empty( $post_data['EMAIL'] ) ) {
			$this->_update_form_settings_error_message = __( 'Please choose valid Forminator field for email address.', Forminator::DOMAIN );
			$input_exceptions->add_input_exception( $this->_update_form_settings_error_message, 'EMAIL' );
		}

		//check required fields fulfilled
		foreach ( $mailchimp_required_fields as $mailchimp_required_field ) {
			if ( 'address' === $mailchimp_required_field->type ) {
				$address_fields = $this->mail_address_fields();
				foreach ( $address_fields as $addr => $address ) {
					if ( 'addr2' !== $addr ) {
						if ( ! isset( $post_data[ $mailchimp_required_field->tag ][ $addr ] ) || empty( $post_data[ $mailchimp_required_field->tag ][ $addr ] ) ) {
							$this->_update_form_settings_error_message =
								/* translators: ... */
								sprintf( __( '%s is required by Mailchimp, please choose valid Forminator field.', Forminator::DOMAIN ), $mailchimp_required_field->name );
							$input_exceptions->add_sub_input_exception( $this->_update_form_settings_error_message, $mailchimp_required_field->tag, $addr );
						}
					}
				}
			}
			if ( ! isset( $post_data[ $mailchimp_required_field->tag ] ) || empty( $post_data[ $mailchimp_required_field->tag ] ) ) {
				$this->_update_form_settings_error_message =
					/* translators: ... */
					sprintf( __( '%s is required by Mailchimp, please choose valid Forminator field.', Forminator::DOMAIN ), $mailchimp_required_field->name );
				$input_exceptions->add_input_exception( $this->_update_form_settings_error_message, $mailchimp_required_field->tag );
			}
		}

		// Check availibility on forminator field
		foreach ( $this->addon_form_settings['fields_map'] as $mailchimp_field_tag => $forminator_field_id ) {
			if ( empty( $forminator_field_id ) ) {
				continue;
			}
			if ( is_array( $forminator_field_id ) ) {
				foreach ( $forminator_field_id as $addr => $field_id ) {
					if ( ! empty( $field_id ) ) {
						$address_value[ $mailchimp_field_tag ][ $addr ] = $field_id;
					}
				}
				foreach ( $forminator_field_id as $addr => $field_id ) {
					if ( 'addr2' === $addr ) {
						continue;
					}
					if ( ! empty( $address_value ) && ! in_array( $field_id, $forminator_field_element_ids, true ) ) {
						$mailchimp_field      = $tag_maped_mailchimp_fields[ $mailchimp_field_tag ];
						$mailchimp_field_name = $mailchimp_field->name;

						$this->_update_form_settings_error_message =
							/* translators: ... */
							sprintf( __( 'Please choose valid Forminator field for %s.', Forminator::DOMAIN ), $mailchimp_field_name );
						$input_exceptions->add_sub_input_exception( $this->_update_form_settings_error_message, $mailchimp_field_tag, $addr );
					}
				}
			}
			if ( ! is_array( $forminator_field_id ) && ! in_array( $forminator_field_id, $forminator_field_element_ids, true ) ) {
				if ( 'EMAIL' === $mailchimp_field_tag ) {
					$mailchimp_field_name = __( 'Email Address', Forminator::DOMAIN );
				} else {
					$mailchimp_field      = $tag_maped_mailchimp_fields[ $mailchimp_field_tag ];
					$mailchimp_field_name = $mailchimp_field->name;
				}

				$this->_update_form_settings_error_message =
					/* translators: ... */
					sprintf( __( 'Please choose valid Forminator field for %s.', Forminator::DOMAIN ), $mailchimp_field_name );
				$input_exceptions->add_input_exception( $this->_update_form_settings_error_message, $mailchimp_field_tag );
			}
		}

		if ( $input_exceptions->input_exceptions_is_available() ) {
			throw $input_exceptions;
		}

		return $this->addon_form_settings;
	}

	/**
	 * Check if map fields is completed
	 *
	 * @since 1.0 Mailchimp Addon
	 * @return bool
	 */
	public function step_map_fields_is_completed() {
		$this->addon_form_settings = $this->get_form_settings_values();
		if ( ! $this->step_choose_mail_list_is_completed() ) {

			return false;
		}

		if ( empty( $this->addon_form_settings['fields_map'] ) ) {

			return false;
		}

		if ( ! is_array( $this->addon_form_settings['fields_map'] ) ) {
			return false;
		}

		if ( count( $this->addon_form_settings['fields_map'] ) < 1 ) {

			return false;
		}

		/**
		 * TODO: check if saved fields_map still valid, by request merge_fields on mailchimp
		 * Easy achieved but will add overhead on site
		 * force_form_disconnect();
		 * save_force_form_disconnect_reason();
		 */

		return true;

	}

	/**
	 * Check if mail list already selected completed
	 *
	 * @since 1.0 Mailchimp Addon
	 * @return bool
	 */
	public function step_choose_mail_list_is_completed() {
		$this->addon_form_settings = $this->get_form_settings_values();
		if ( ! isset( $this->addon_form_settings['mail_list_id'] ) ) {
			// preliminary value
			$this->addon_form_settings['mail_list_id'] = 0;

			return false;
		}

		if ( empty( $this->addon_form_settings['mail_list_id'] ) ) {
			return false;
		}

		/**
		 * TODO: check if saved mail list id still valid, by request info on mailchimp
		 * Easy achieved but will add overhead on site
		 * force_form_disconnect();
		 * save_force_form_disconnect_reason();
		 */

		return true;
	}

	/**
	 * Check if list limit is completed
	 *
	 * @since 1.0 Mailchimp Addon
	 * @return bool
	 */
	public function step_mail_list_limit_is_completed() {

		$this->addon_form_settings = $this->get_form_settings_values();
		if ( ! isset( $this->addon_form_settings['mail_list_limit'] ) ) {
			// preliminary value
			$this->addon_form_settings['mail_list_limit'] = 10;

			return true;
		}

		/**
		 * TODO: check if saved mail list limit still valid, by request info on mailchimp
		 * Easy achieved but will add overhead on site
		 * force_form_disconnect();
		 * save_force_form_disconnect_reason();
		 */

		return true;

	}

}
