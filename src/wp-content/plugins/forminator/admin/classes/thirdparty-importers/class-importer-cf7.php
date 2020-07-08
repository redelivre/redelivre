<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Admin_Import_CF7
 *
 * @since 1.11
 */
class Forminator_Admin_Import_CF7 extends Forminator_Import_Mediator {

	/**
	 * Plugin instance
	 * @since  1.11
	 * @access private
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Return the plugin instance
	 *
	 * @since 1.11
	 * @return Forminator
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get label text from CF7 form HTML
	 *
	 *
	 * @since 1.11
	 * @return string field label
	 */
	public function get_label_cf7( $name, $form ) {

		$regex = '/((.*)(?:\s+)?\[(?:.*?)' . $name . '(?:.*?)\](.*))/im';

		if ( preg_match( $regex, $form, $matches ) ) {
			if ( false !== strpos( $matches[2], 'wpcf7cf_group' ) ) {
				$label_html = trim( strip_tags( $matches[2], '<b><strong><em><i><u>' ) );
			} else {
				$label_html = trim( strip_tags( $matches[2], '<b><strong><em><i><span><u>' ) );
			}
			$form   = preg_replace( $regex, '', $form );
			$is_tag = strpos( $label_html, '[' );
			//get label from form html

			if ( isset( $label_html ) && ! empty( $label_html ) && 0 !== $is_tag ) {
				return trim( rtrim( $label_html ) );
			}
		}

		return '';
	}

	/**
	 * Insert form data
	 *
	 * @param $id
	 * @param $post_data
	 *
	 * @return array|object
	 */
	public function import_form( $id, $post_data = array() ) {
		$form              = wpcf7_contact_form( intval( $id ) );
		$form_fields       = $form->scan_form_tags();
		$form_html         = $form->prop( 'form' );
		$mail              = $form->prop( 'mail' );
		$mail_2            = $form->prop( 'mail_2' );
		$messages          = $form->prop( 'messages' );
		$data              = array();
		$count             = array();
		$new_fields        = array();
		$settings          = array(
			'pagination-header'    => 'nav',
			'form-border-style'    => 'solid',
			'form-padding'         => '',
			'form-border'          => '',
			'fields-style'         => 'open',
			'validation'           => 'on_submit',
			'form-style'           => 'default',
			'enable-ajax'          => 'true',
			'autoclose'            => 'true',
			'submission-indicator' => 'show',
			'indicator-label'      => __( 'Submitting...', Forminator::DOMAIN ),
			'paginationData'       => array(
				'pagination-header-design' => 'show',
				'pagination-header'        => 'nav',
			),
		);
		$tags              = array();
		$entry_meta        = array();
		$cf7_addons        = ! empty( $post_data['cf7-addons'] ) ? $post_data['cf7-addons'] : array();
		$honeypot          = false;
		$global            = array();
		$field_data        = $this->get_fields_data( $form_fields );
		$entry             = new Forminator_Form_Entry_Model();
		$entry->entry_type = 'custom-forms';
		$wpcf7cf_entries   = array();
		$submit_label      = '';
		$submit_class      = '';
		$autofill          = array();

		// fields import
		if ( is_plugin_active( 'cf7-conditional-fields/contact-form-7-conditional-fields.php' ) && in_array( 'conditional', $cf7_addons, true ) ) {
			$wpcf7cf_entries = CF7CF::getConditions( $id );
		}

		foreach ( $form_fields as $mkey => $field ) {
			$custom_class     = '';
			$form_placeholder = '';
			$default_value    = '';
			$options          = array();
			$condition        = array();
			$blank_options    = array();
			$type             = $this->get_thirdparty_field_type( $field->basetype );

			if ( empty( $type ) ) {
				continue;
			}

			$wrapper = 'wrapper-' . $this->random_wrapper_int() . '-' . $this->random_wrapper_int();

			if ( 'submit' === $field['type'] ) {
				$submit_label = $field->labels[0];
				if ( isset( $field['options'] ) ) {
					$classes = preg_grep( "/^class:/", $field['options'] );

					if ( ! empty( $classes ) ) {
						foreach ( $classes as $class_value ) {
							$exploded = explode( ":", $class_value );

							if ( isset( $exploded[1] ) ) {
								$submit_class .= $exploded[1] . " ";
							}
						}
					}
				}
			} else {
				if ( isset( $count[ $type ] ) && $count[ $type ] > 0 ) {
					$count[ $type ] = $count[ $type ] + 1;
				} else {
					$count[ $type ] = 1;
				}

				if ( ! empty( $field->labels ) && ( 'select' === $type || 'radio' === $type || 'checkbox' === $type ) ) {
					$checked = array();

					if ( isset( $field['options'] ) ) {
						$has_blank  = preg_grep( "/^include_blank/", $field['options'] );
						$has_values = preg_grep( "/^default:/", $field['options'] );

						if ( ! empty( $has_values ) ) {
							$keys            = array_keys( $has_values );
							$explode_default = explode( ':', $has_values[ $keys[0] ] );

							if ( isset( $explode_default[1] ) ) {
								$checked = explode( '_', $explode_default[1] );
							}
						}
					}
					if ( ! empty( $has_blank ) ) {
						$blank_options[] = array(
							'label'   => '---',
							'value'   => '',
							'limit'   => '',
							'default' => ''
						);
					}
					foreach ( $field->labels as $key => $label ) {
						$options[] = array(
							'label'   => esc_html( $label ),
							'value'   => esc_html( $field->values[ $key ] ),
							'limit'   => '',
							'default' => in_array( $key + 1, $checked )
						);
					}
					$options = array_merge( $blank_options, $options );
				}

				if ( 'acceptance' === $field['type'] ) {
					$gdpr = true;
				}

				if ( 'honeypot' === $type && in_array( 'honeypot', $cf7_addons, true ) ) {
					$honeypot = true;
				}

				if ( isset( $field['options'] ) ) {
					$classes = preg_grep( "/^class:/", $field['options'] );

					if ( ! empty( $classes ) ) {
						foreach ( $classes as $class_value ) {
							$exploded = explode( ":", $class_value );

							if ( isset( $exploded[1] ) ) {
								$custom_class .= $exploded[1] . " ";
							}
						}
					}
				}

				if ( isset( $field['options'] ) ) {
					$placeholder = preg_grep( "/^placeholder/", $field['options'] );
					$field_value = ( isset( $field['values'] ) && isset( $field['values'][0] ) ) ? $field['values'][0] : '';

					if ( ! empty( $placeholder ) ) {
						$form_placeholder = $field_value;
					} else {
						$default_value = $field_value;
					}
				}

				$entry_meta[ $field['name'] ] = $type . '-' . $count[ $type ];

				if ( in_array( 'cfdb7', $cf7_addons, true ) && 'upload' === $type ) {
					$entry_meta[ $field['name'] . 'cfdb7_file' ] = $type . '-' . $count[ $type ];
				}

				$condition = $this->import_conditional_field( $wpcf7cf_entries, $form_html, $field, $field_data );

				if ( 'captcha' === $type ) {
					$global['captchaV2'] = true;
					$condition           = array();
					$field_name          = esc_html__( 'reCaptcha', Forminator::DOMAIN );
				} else {
					$field_name = esc_html( $this->get_label_cf7( $field->name, $form_html ) );
				}

				$new_fields[ $mkey ] = array(
					'element_id'    => esc_html( $type . '-' . $count[ $type ] ),
					'placeholder'   => esc_html( $form_placeholder ),
					'type'          => esc_html( $type ),
					'wrapper_id'    => $wrapper,
					'form_id'       => $wrapper,
					'cols'          => 12,
					'required'      => substr( $field['type'], - 1, 1 ) === '*' ? true : false,
					'field_label'   => $field_name,
					'options'       => $options,
					'default'       => esc_html( $default_value ),
					'default_value' => esc_html( $default_value ),
					'custom-class'  => trim( $custom_class ),
					'conditions'    => $condition
				);

				// Handle specific field options
				switch ( $field['basetype'] ) {
					case 'select':
						$new_fields[ $mkey ] = $this->handle_select_field( $field, $new_fields[ $mkey ], $messages );
						break;
					case 'text':
						$new_fields[ $mkey ] = $this->handle_text_field( $field, $new_fields[ $mkey ], $messages );
						break;
					case 'textarea':
						$new_fields[ $mkey ] = $this->handle_text_field( $field, $new_fields[ $mkey ], $messages );
						break;
					case 'number':
						$new_fields[ $mkey ] = $this->handle_number_field( $field, $new_fields[ $mkey ], $messages );
						break;
					case 'file':
						$new_fields[ $mkey ] = $this->handle_upload_field( $field, $new_fields[ $mkey ], $messages );
						break;
					case 'acceptance':
						$new_fields[ $mkey ] = $this->handle_acceptance_field( $field, $new_fields[ $mkey ], $messages );
						break;
					case 'date':
						$new_fields[ $mkey ] = $this->handle_date_field( $field, $new_fields[ $mkey ], $messages );
						break;
					case 'url':
						$new_fields[ $mkey ] = $this->handle_url_field( $field, $new_fields[ $mkey ], $messages );
						break;
					case 'tel':
						$new_fields[ $mkey ] = $this->handle_phone_field( $field, $new_fields[ $mkey ], $messages );
						break;
					case 'email':
						$new_fields[ $mkey ] = $this->handle_email_field( $field, $new_fields[ $mkey ], $messages );
						break;
					case 'captcha':
						$new_fields[ $mkey ] = $this->handle_captcha_field( $field, $new_fields[ $mkey ], $messages );
						break;
					case 'checkbox':
						$new_fields[ $mkey ] = $this->handle_checkbox_field( $field, $new_fields[ $mkey ], $messages );
						break;
					default:
						break;
				}

				$tag_key            = $field['name'];
				$tags["[$tag_key]"] = '{' . $new_fields[ $mkey ]['element_id'] . '}';

				if ( isset( $field['options'] ) ) {
					$has_default_values = preg_grep( "/^default:/", $field['options'] );
					if ( ! empty( $has_default_values ) ) {
						$has_default_values    = array_values( $has_default_values );
						$explode_default_value = explode( ':', $has_default_values[0] );
						if ( isset( $explode_default_value[1] ) ) {
							$default_tag   = $this->replace_default_tags( $explode_default_value[1] );
							$default_field = esc_html( $type . '-' . $count[ $type ] );
							if ( ! empty( $default_tag ) ) {
								$autofill[ $mkey ]['element_id']  = $default_field;
								$autofill[ $mkey ]['provider']    = $default_tag;
								$autofill[ $mkey ]['is_editable'] = 'yes';
							}
						}
					}
				}
			}

		}//endforeach fields

		//admin mail import
		$settings['use-admin-email'] = false;

		if ( isset( $mail['active'] ) && true === $mail['active'] ) {
			$settings['use-admin-email']    = true;
			$settings['admin-email-title']  = $this->replace_invalid_tags( $mail['subject'], $tags );
			$settings['admin-email-editor'] = $this->replace_invalid_tags( $mail['body'], $tags );
			$admin_email_from               = $this->replace_invalid_tags( $mail['sender'], $tags );

			if ( preg_match( '/^([^\<]*)(?:\<([^\>]*)\>)?$/u', $admin_email_from, $matches ) ) {
				$settings['admin-email-from-name']    = isset( $matches[1] ) ? $matches[1] : '';
				$settings['admin-email-from-address'] = isset( $matches[2] ) ? $matches[2] : '';
			}

			$header_pattern     = '/(reply-to|bcc|cc):(.+?)(?= reply-to:| bcc:| cc:|$)/i';
			$additional_headers = trim( preg_replace( '/\s+/', ' ', $mail['additional_headers'] ) );

			if ( preg_match_all( $header_pattern, $additional_headers, $header_matches ) ) {
				$regex_header = array_change_key_case( array_combine( $header_matches[1], $header_matches[2] ), CASE_LOWER );

				if ( ! empty( $regex_header['reply-to'] ) ) {
					$reply_tag                                = $this->replace_invalid_tags( $regex_header['reply-to'], $tags );
					$settings['admin-email-reply-to-address'] = trim( $reply_tag );
				}

				if ( ! empty( $regex_header['bcc'] ) ) {
					$bcc_tag                             = $this->replace_invalid_tags( $regex_header['bcc'], $tags );
					$settings['admin-email-bcc-address'] = explode( ',', trim( $bcc_tag ) );
				}

				if ( ! empty( $regex_header['cc'] ) ) {
					$cc_tag                             = $this->replace_invalid_tags( $regex_header['cc'], $tags );
					$settings['admin-email-cc-address'] = explode( ',', trim( $cc_tag ) );
				}
			}

			$settings['admin-email-recipients'] = explode( " ", $this->replace_invalid_tags( $mail['recipient'], $tags ) );
		}

		//autoresponder import
		$settings['use-user-email'] = false;

		if ( isset( $mail_2['active'] ) && true === $mail_2['active'] ) {
			$settings['use-user-email']    = true;
			$settings['user-email-title']  = $this->replace_invalid_tags( $mail_2['subject'], $tags );
			$settings['user-email-editor'] = $this->replace_invalid_tags( $mail_2['body'], $tags );
			$user_email_from               = $this->replace_invalid_tags( $mail_2['sender'], $tags );

			if ( preg_match( '/^([^\<]*)(?:\<([^\>]*)\>)?$/u', $user_email_from, $matches ) ) {
				$settings['user-email-from-name']    = isset( $matches[1] ) ? $matches[1] : '';
				$settings['user-email-from-address'] = isset( $matches[2] ) ? $matches[2] : '';
			}

			$user_header_pattern     = '/(reply-to|bcc|cc):(.+?)(?= reply-to:| bcc:| cc:|$)/i';
			$user_additional_headers = trim( preg_replace( '/\s+/', ' ', $mail_2['additional_headers'] ) );

			if ( preg_match_all( $user_header_pattern, $user_additional_headers, $user_header_matches ) ) {
				$user_regex_header = array_change_key_case( array_combine( $user_header_matches[1], $user_header_matches[2] ), CASE_LOWER );

				if ( ! empty( $user_regex_header['reply-to'] ) ) {
					$user_reply_tag                          = $this->replace_invalid_tags( $user_regex_header['reply-to'], $tags );
					$settings['user-email-reply-to-address'] = trim( $user_reply_tag );
				}

				if ( ! empty( $user_regex_header['bcc'] ) ) {
					$user_bcc_tag                       = $this->replace_invalid_tags( $user_regex_header['bcc'], $tags );
					$settings['user-email-bcc-address'] = explode( ',', trim( $user_bcc_tag ) );
				}

				if ( ! empty( $user_regex_header['cc'] ) ) {
					$user_cc_tag                       = $this->replace_invalid_tags( $user_regex_header['cc'], $tags );
					$settings['user-email-cc-address'] = explode( ',', trim( $user_cc_tag ) );
				}
			}

			$settings['user-email-recipients'] = explode( " ", $this->replace_invalid_tags( $mail_2['recipient'], $tags ) );
		}
		//form settings basic import
		$settings['formName']                    = esc_html( get_the_title( $id ) );
		$settings['thankyou-message']            = $messages['mail_sent_ok'];
		$settings['custom-invalid-form-message'] = $messages['validation_error'];
		$settings['honeypot']                    = $honeypot;
		$settings['enable-ajax']                 = 'true';
		$settings['validation-inline']           = true;
		$settings['validation']                  = 'on_submit';

		if ( ! empty( $autofill ) ) {
			$settings['use-autofill']    = true;
			$settings['fields-autofill'] = array_values( $autofill );
		}

		// form submit data settings
		$settings['submitData']['custom-submit-text'] = $submit_label;
		$settings['submitData']['custom-class']       = $submit_class;

		if ( is_plugin_active( 'wpcf7-redirect/wpcf7-redirect.php' ) && in_array( 'redirection', $cf7_addons, true ) ) {
			$redirect      = new WPCF7_Redirect();
			$redirect_meta = $redirect->get_fields_values( $id );

			if ( ! empty( $redirect_meta['page_id'] ) || ! empty( $redirect_meta['external_url'] ) ) {
				if ( $redirect_meta['external_url'] && 'on' === $redirect_meta['use_external_url'] ) {
					$redirect_url = $redirect_meta['external_url'];
				} else {
					$redirect_url = get_permalink( $redirect_meta['page_id'] );
				}

				$settings['submission-behaviour'] = 'behaviour-redirect';
				$settings['redirect-url']         = $redirect_url;
				$settings['newtab']               = 'on' === $redirect_meta['open_in_new_tab'] ? 'newtab_thankyou' : 'sametab';
			}
		}
		//for basics generation
		$data['status']           = get_post_status( $id );
		$data['version']          = FORMINATOR_VERSION;
		$data['type']             = 'form';
		$data['data']['fields']   = $new_fields;
		$data['data']['settings'] = $settings;

		$data = apply_filters( 'forminator_cf7_form_import_data', $data );

		$import = $this->try_form_import( $data );

		$this->import_global_settings( $global );

		if ( is_plugin_active( 'flamingo/flamingo.php' ) && in_array( 'flamingo', $cf7_addons, true ) ) {
			$this->import_flamingo( $id, $entry, $import, $entry_meta );
		}

		if ( is_plugin_active( 'contact-form-cfdb7/contact-form-cfdb-7.php' ) && in_array( 'cfdb7', $cf7_addons, true ) ) {
			$this->import_cfdb7( $id, $entry, $import, $entry_meta );
		}

		if ( is_plugin_active( 'contact-form-submissions/contact-form-submissions.php' ) && in_array( 'submissions', $cf7_addons, true ) ) {
			$this->import_submissions( $id, $entry, $import, $entry_meta );
		}

		if ( is_plugin_active( 'advanced-cf7-db/advanced-cf7-db.php' ) && in_array( 'advanced_cf7', $cf7_addons, true ) ) {
			$this->import_advanced_cf7( $id, $entry, $import, $entry_meta );
		}

		return $import;
	}

	/**
	 * Handle select field specific options
	 *
	 * @since 1.11
	 *
	 * @param $field
	 * @param $options
	 *
	 * @return mixed
	 */
	public function handle_select_field( $field, $options, $messages ) {
		// Check if select field has any options
		if ( isset( $field['options'] ) ) {
			// Check if multiple option enabled
			if ( in_array( 'multiple', $field['options'], true ) ) {
				$options['value_type'] = 'multiselect';
			}
		}

		if ( ! empty( $messages['invalid_required'] ) ) {
			$options['required_message'] = $messages['invalid_required'];
		}

		return $options;
	}

	/**
	 * Handle checkbox field specific options
	 *
	 * @since 1.11
	 *
	 * @param $field
	 * @param $options
	 *
	 * @return mixed
	 */
	public function handle_checkbox_field( $field, $options, $messages ) {
		if ( ! empty( $messages['invalid_required'] ) ) {
			$options['required_message'] = $messages['invalid_required'];
		}

		return $options;
	}

	/**
	 * Handle text field specific options
	 *
	 * @since 1.11
	 *
	 * @param $field
	 * @param $options
	 * @param $messages
	 *
	 * @return mixed
	 */
	public function handle_text_field( $field, $options, $messages ) {
		if ( isset( $field['options'] ) ) {
			$max_length = preg_grep( "/^maxlength:/", $field['options'] );

			if ( ! empty( $max_length ) ) {
				foreach ( $max_length as $length ) {
					$exploded = explode( ":", $length );

					if ( isset( $exploded[1] ) ) {
						$options['limit']      = $exploded[1];
						$options['limit_type'] = "characters";
					}
				}
			}

			if ( ! empty( $messages['invalid_required'] ) ) {
				$options['required_message'] = $messages['invalid_required'];
			}
		}

		return $options;
	}

	/**
	 * Handle number field specific options
	 *
	 * @since 1.11
	 *
	 * @param $field
	 * @param $options
	 * @param $messages
	 *
	 * @return mixed
	 */
	public function handle_number_field( $field, $options, $messages ) {
		if ( isset( $field['options'] ) ) {
			$min = preg_grep( "/^min:/", $field['options'] );
			$max = preg_grep( "/^max:/", $field['options'] );

			if ( ! empty( $min ) ) {
				foreach ( $min as $length ) {
					$exploded = explode( ":", $length );

					if ( isset( $exploded[1] ) ) {
						$options['limit_min'] = $exploded[1];
					}
				}
			}

			if ( ! empty( $max ) ) {
				foreach ( $max as $length ) {
					$exploded = explode( ":", $length );

					if ( isset( $exploded[1] ) ) {
						$options['limit_max'] = $exploded[1];
					}
				}
			}

		}
		if ( ! empty( $messages['invalid_required'] ) ) {
			$options['required_message'] = $messages['invalid_required'];
		}

		return $options;
	}

	/**
	 * Handle GDPR field specific options
	 *
	 * @since 1.11
	 *
	 * @param $field
	 * @param $options
	 * @param $messages
	 *
	 * @return mixed
	 */
	public function handle_acceptance_field( $field, $options, $messages ) {
		// Check if content exists
		if ( isset( $field['content'] ) ) {
			$options['gdpr_description'] = $field['content'];
		}

		if ( ! empty( $messages['accept_terms'] ) ) {
			$options['required_message'] = $messages['accept_terms'];
		}

		return $options;
	}

	/**
	 * Handle Date field specific options
	 *
	 * @since 1.11
	 *
	 * @param $field
	 * @param $options
	 * @param $messages
	 *
	 * @return mixed
	 */
	public function handle_date_field( $field, $options, $messages ) {
		$field_value = ( isset( $field['values'] ) && isset( $field['values'][0] ) ) ? $field['values'][0] : '';
		if ( isset( $field['options'] ) ) {
			$min = preg_grep( "/^min:/", $field['options'] );
			$max = preg_grep( "/^max:/", $field['options'] );

			if ( ! empty( $min ) ) {
				foreach ( $min as $length ) {
					$exploded = explode( ":", $length );

					if ( isset( $exploded[1] ) ) {
						$options['min_year'] = date( 'Y', strtotime( $exploded[1] ) );
					}
				}
			}

			if ( ! empty( $max ) ) {
				foreach ( $max as $length ) {
					$exploded = explode( ":", $length );

					if ( isset( $exploded[1] ) ) {
						$options['max_year'] = date( 'Y', strtotime( $exploded[1] ) );
					}
				}
			}
		}
		if ( ! empty( $field_value ) ) {
			$options['default_date'] = 'custom';
			$options['date']         = $field_value;
		}
		if ( ! empty( $messages['invalid_required'] ) ) {
			$options['required_message'] = $messages['invalid_required'];
		}
		$options['field_type']  = 'picker';
		$options['date_format'] = 'dd/mm/yy';

		return $options;
	}

	/**
	 * Handle Phone field specific options
	 *
	 * @since 1.11
	 *
	 * @param $field
	 * @param $options
	 * @param $messages
	 *
	 * @return mixed
	 */
	public function handle_phone_field( $field, $options, $messages ) {
		if ( ! empty( $messages['invalid_required'] ) ) {
			$options['required_message'] = $messages['invalid_required'];
		}

		if ( ! empty( $messages['invalid_tel'] ) ) {
			$options['validation_message'] = $messages['invalid_tel'];
		}

		return $options;
	}

	/**
	 * Handle email field specific options
	 *
	 * @since 1.11
	 *
	 * @param $field
	 * @param $options
	 * @param $messages
	 *
	 * @return mixed
	 */
	public function handle_email_field( $field, $options, $messages ) {
		if ( ! empty( $messages['invalid_required'] ) ) {
			$options['required_message'] = $messages['invalid_required'];
		}

		if ( ! empty( $messages['invalid_email'] ) ) {
			$options['validation_message'] = $messages['invalid_email'];
		}

		return $options;
	}

	/**
	 * Handle Captcha field specific options
	 *
	 * @since 1.11
	 *
	 * @param $field
	 * @param $options
	 * @param $messages
	 *
	 * @return mixed
	 */
	public function handle_captcha_field( $field, $options, $messages ) {
		if ( ! empty( $messages['captcha_not_match'] ) ) {
			$options['recaptcha_error_message'] = $messages['captcha_not_match'];
		}

		return $options;
	}

	/**
	 * Handle URL field specific options
	 *
	 * @since 1.11
	 *
	 * @param $field
	 * @param $options
	 * @param $messages
	 *
	 * @return mixed
	 */
	public function handle_url_field( $field, $options, $messages ) {
		if ( ! empty( $messages['invalid_required'] ) ) {
			$options['required_message'] = $messages['invalid_required'];
		}

		if ( ! empty( $messages['invalid_url'] ) ) {
			$options['validation_message'] = $messages['invalid_url'];
		}

		return $options;
	}

	/**
	 * Handle upload field specific options
	 *
	 * @since 1.11
	 *
	 * @param $field
	 * @param $options
	 * @param $messages
	 *
	 * @return mixed
	 */
	public function handle_upload_field( $field, $options, $messages ) {
		if ( isset( $field['options'] ) ) {
			$limit_value = preg_grep( "/^limit:/", $field['options'] );
			$types_value = preg_grep( "/^filetypes:/", $field['options'] );

			// Handle size limit options
			if ( ! empty( $limit_value ) ) {
				foreach ( $limit_value as $limit ) {
					$exploded = explode( ":", $limit );

					if ( isset( $exploded[1] ) ) {
						$options['upload-limit'] = $this->convert_limit_to_mb( $exploded[1] );
					}
				}
			}

			// Handle file types
			if ( ! empty( $types_value ) ) {
				foreach ( $types_value as $types_values ) {
					$exploded = explode( ":", $types_values );

					if ( isset( $exploded[1] ) ) {
						$types    = explode( "|", $exploded[1] );
						$filtered = array();

						foreach ( $types as $type ) {
							$filtered[] = $this->filter_filetypes( $type );
						}

						$options['filetypes']    = $filtered;
						$options['custom-files'] = true;
					}
				}
			}
		}

		return $options;
	}

	/**
	 * Convert limit to MB
	 *
	 * @since 1.11
	 *
	 * @param $limit
	 *
	 * @return float|string
	 */
	public function convert_limit_to_mb( $limit ) {
		if ( strpos( $limit, 'mb' ) !== false ) {
			// Limit is already in MB, return value
			return mb_substr( $limit, 0, - 2 );
		}

		if ( strpos( $limit, 'kb' ) !== false ) {
			$limit = mb_substr( $limit, 0, - 2 );

			// Limit is in KB, we need to convert to MB
			return round( $limit / 1024, 2 );
		}

		return round( $limit / 1024 / 1024, 2 );
	}

	/**
	 * Filter file types to WP mime types
	 *
	 * @since 1.11
	 *
	 * @param $file
	 *
	 * @return string
	 */
	public function filter_filetypes( $file ) {
		switch ( $file ) {
			case 'jpg':
				$file = "jpg|jpeg|jpe";
				break;
			case 'jpeg':
				$file = "jpg|jpeg|jpe";
				break;
			case 'mp3':
				$file = "mp3|m4a|m4b";
				break;
			case '3gp':
				$file = "3gp|3gpp";
				break;
			case 'mp4':
				$file = "mp4|m4v";
				break;
			case 'mpeg':
				$file = "mpeg|mpg|mpe";
				break;
			case 'mpg':
				$file = "mpeg|mpg|mpe";
				break;
			case 'mov':
				$file = "mov|qt";
				break;
			case 'tiff':
				$file = "tiff|tif";
				break;
			case 'tif':
				$file = "tiff|tif";
				break;
			default:
				break;
		}

		return $file;
	}

	/**
	 * Import flamingo
	 *
	 * @param $id
	 * @param $entry
	 * @param $import
	 * @param $meta
	 */
	public function import_flamingo( $id, Forminator_Form_Entry_Model $entry, $import, $meta ) {
		$field_data_array = array();

		if ( ! empty( $import ) && 'success' === $import['type'] ) {
			$entry->form_id = $import['id'];
			$slug           = get_post_field( 'post_name', $id );
			$flamingo_data  = Flamingo_Inbound_Message::find( array(
				'posts_per_page' => - 1,
				'channel'        => $slug
			) );
			if ( ! empty( $flamingo_data ) ) {
				foreach ( $flamingo_data as $flamingo ) {
					$created_date = date_i18n( 'Y-m-d H:i:s', strtotime( str_replace( ' @', '', $flamingo->date ) ) );
					if ( $entry->save( $created_date ) ) {
						if ( ! empty( $flamingo->fields ) ) {
							foreach ( $flamingo->fields as $key => $value ) {
								if ( isset( $meta[ $key ] ) ) {
									if ( strpos( $meta[ $key ], 'upload' ) !== false ) {
										$value = array(
											'file' => array(
												'success'   => true,
												'file_url'  => $value,
												'file_path' => '',
											)
										);
									}
									$field_data_array[] = array(
										'name'  => $meta[ $key ],
										'value' => $value,
									);
								}
							}

							if ( ! empty( $flamingo->meta['remote_ip'] ) ) {
								$field_data_array[] = array(
									'name'  => '_forminator_user_ip',
									'value' => $flamingo->meta['remote_ip'],
								);
							}

							$entry->set_fields( $field_data_array, $created_date );
						}
					}
				}
			}
		}
	}

	/**
	 * Import cfdb7
	 *
	 * @param $id
	 * @param $entry
	 * @param $import
	 * @param $meta
	 */
	public function import_cfdb7( $id, Forminator_Form_Entry_Model $entry, $import, $meta ) {
		global $wpdb;
		$field_data_array = array();

		if ( ! empty( $import ) && 'success' === $import['type'] ) {
			$entry->form_id = $import['id'];
			$table_name     = $wpdb->prefix . 'db7_forms';
			$sql            = "SELECT `form_value`,`form_date` FROM {$table_name} WHERE `form_post_id` = %d";
			$form_data      = $wpdb->get_results( $wpdb->prepare( $sql, $id ) );
			if ( ! empty( $form_data ) ) {
				foreach ( $form_data as $form_value ) {
					$data_value = maybe_unserialize( $form_value->form_value );
					$data_date  = date_i18n( 'Y-m-d H:i:s', strtotime( $form_value->form_date ) );
					if ( $entry->save( $data_date ) && ! empty( $data_value ) ) {
						foreach ( $data_value as $key => $value ) {
							if ( isset( $meta[ $key ] ) ) {
								if ( strpos( $meta[ $key ], 'upload' ) !== false ) {
									$upload_dir    = wp_upload_dir();
									$cfdb7_dir_url = $upload_dir['baseurl'] . '/cfdb7_uploads';
									$file_url      = ! empty( $value ) ? $cfdb7_dir_url . '/' . $value : '';
									$value         = array(
										'file' => array(
											'success'   => true,
											'file_url'  => $file_url,
											'file_path' => '',
										)
									);
								}

								$field_data_array[] = array(
									'name'  => $meta[ $key ],
									'value' => $value,
								);
							}
						}

						$entry->set_fields( $field_data_array, $data_date );
					}
				}
			}
		}
	}

	/**
	 * Import submission
	 *
	 * @param $id
	 * @param Forminator_Form_Entry_Model $entry
	 * @param $import
	 * @param $meta
	 */
	public function import_submissions( $id, Forminator_Form_Entry_Model $entry, $import, $meta ) {
		$field_data_array = array();

		if ( ! empty( $import ) && 'success' === $import['type'] ) {
			$entry->form_id   = $import['id'];
			$submissions_data = get_posts( array(
				'posts_per_page' => - 1,
				'post_type'      => 'wpcf7s',
				'meta_key'       => 'form_id',
				'meta_value'     => (int) $id
			) );

			if ( ! empty( $submissions_data ) ) {
				foreach ( $submissions_data as $submissions ) {
					$data_date = date_i18n( 'Y-m-d H:i:s', strtotime( $submissions->post_date ) );
					if ( $entry->save( $data_date ) ) {
						if ( ! empty( $meta ) ) {
							foreach ( $meta as $key => $value ) {
								if ( $value ) {
									$meta_key   = 'wpcf7s_posted-' . $key;
									$meta_value = get_post_meta( $submissions->ID, $meta_key, true );
									$data_value = $meta_value;
									if ( strpos( $meta[ $key ], 'upload' ) !== false ) {
										$meta_value               = get_post_meta( $submissions->ID, 'wpcf7s_file-' . $key, true );
										$contact_form_submissions = new WPCF7Submissions();
										$wpcf7s_url               = $contact_form_submissions->get_wpcf7s_url();
										$file_url                 = ! empty( $meta_value ) ? $wpcf7s_url . '/' . $submissions->ID . '/' . $meta_value : '';
										$data_value               = array(
											'file' => array(
												'success'   => true,
												'file_url'  => $file_url,
												'file_path' => '',
											)
										);
									}

									$field_data_array[] = array(
										'name'  => $value,
										'value' => $data_value,
									);
								}
							}

							$entry->set_fields( $field_data_array, $data_date );
						}
					}
				}
			}
		}
	}

	/**
	 * Field data
	 *
	 * @param $form_fields
	 *
	 * @return array
	 */
	public function get_fields_data( $form_fields ) {
		$data  = array();
		$count = array();

		if ( ! empty( $form_fields ) ) {
			foreach ( $form_fields as $field ) {
				$type = $this->get_thirdparty_field_type( $field->basetype );

				if ( isset( $count[ $type ] ) && $count[ $type ] > 0 ) {
					$count[ $type ] = $count[ $type ] + 1;
				} else {
					$count[ $type ] = 1;
				}

				$data[ $field['name'] ] = $type . '-' . $count[ $type ];
			}
		}

		return $data;
	}

	/**
	 * Import global settings
	 *
	 * @param $setting
	 */
	public function import_global_settings( $setting ) {
		if ( ! empty( $setting ) ) {
			if ( isset( $setting['captchaV2'] ) ) {
				if ( class_exists( 'WPCF7_RECAPTCHA' ) ) {
					$cf7_captcha        = WPCF7_RECAPTCHA::get_instance();
					$cf7_captcha_key    = $cf7_captcha->is_active() ? $cf7_captcha->get_sitekey() : '';
					$cf7_captcha_secret = $cf7_captcha->is_active() ? $cf7_captcha->get_secret( $cf7_captcha_key ) : '';
					if ( $cf7_captcha_key && $cf7_captcha_secret ) {
						update_option( 'forminator_captcha_key', $cf7_captcha_key );
						update_option( 'forminator_captcha_secret', $cf7_captcha_secret );
					}
				}
			}
		}
	}

	/**
	 * Import Advanced CF7
	 *
	 * @param $id
	 * @param Forminator_Form_Entry_Model $entry
	 * @param $import
	 * @param $meta
	 */
	public function import_advanced_cf7( $id, Forminator_Form_Entry_Model $entry, $import, $meta ) {
		global $wpdb;
		$field_data_array = array();

		if ( ! empty( $import ) && 'success' === $import['type'] ) {
			$entry->form_id      = $import['id'];
			$cf7d_entry_order_by = '`data_id` DESC';
			$table_name          = VSZ_CF7_DATA_ENTRY_TABLE_NAME;
			$query               = "SELECT * FROM {$table_name} WHERE cf7_id = %d AND data_id IN(
							SELECT * FROM (
								SELECT data_id FROM {$table_name} WHERE 1 = 1 AND cf7_id = %d
									GROUP BY `data_id` ORDER BY %s
								)
							temp_table)
							ORDER BY %s";
			$data                = $wpdb->get_results( $wpdb->prepare( $query, $id, $id, $cf7d_entry_order_by, $cf7d_entry_order_by ) );
			$submissions_data    = vsz_cf7_sortdata( $data );
			if ( ! empty( $submissions_data ) ) {
				foreach ( $submissions_data as $submissions ) {
					$data_date = date_i18n( 'Y-m-d H:i:s', strtotime( $submissions['submit_time'] ) );
					if ( $entry->save( $data_date ) ) {
						if ( ! empty( $meta ) ) {
							foreach ( $meta as $key => $value ) {
								if ( isset( $submissions[ $key ] ) ) {
									$data_value = $submissions[ $key ];
									if ( strpos( $value, 'upload' ) !== false ) {
										$data_value = array(
											'file' => array(
												'success'   => true,
												'file_url'  => $submissions[ $key ],
												'file_path' => '',
											)
										);
									}
									if ( strpos( $value, 'checkbox' ) !== false ||
									     strpos( $value, 'select' ) !== false ) {
										$data_value = explode( '<br />', nl2br( $submissions[ $key ] ) );
										$data_value = array_map( 'trim', $data_value );
									}
									$field_data_array[] = array(
										'name'  => $value,
										'value' => $data_value,
									);
								}
							}

							$entry->set_fields( $field_data_array, $data_date );
						}
					}
				}
			}
		}
	}

	/**
	 * Import Condition
	 *
	 * @param $wpcf7cf_entries
	 * @param $form_html
	 * @param $field
	 * @param $field_data
	 *
	 * @return array
	 */
	public function import_conditional_field( $wpcf7cf_entries, $form_html, $field, $field_data ) {
		$condition = array();

		if ( ! empty( $wpcf7cf_entries ) ) {
			foreach ( $wpcf7cf_entries as $wpcf7cf ) {
				$then_field   = $wpcf7cf['then_field'];
				$hide_pattern = '~<div data-id="' . $then_field . '"[^>]*>(.*?)</div>~si';

				if ( preg_match_all( $hide_pattern, $form_html, $matches ) ) {
					if ( ! empty( $matches ) && isset( $matches[1][0] ) ) {
						if ( preg_match( '/' . $field['name'] . '/', $matches[1][0], $matches ) ) {
							if ( ! empty( $wpcf7cf['and_rules'] ) ) {
								foreach ( $wpcf7cf['and_rules'] as $rule ) {
									$if_field    = $field_data[ $rule['if_field'] ];
									$is_rule     = 'equals' === $rule['operator'] ? 'is' : 'is_not';
									$condition[] = array(
										'element_id' => $if_field,
										'rule'       => $is_rule,
										'value'      => $rule['if_value'],
									);
								}
							}
						}
					}
				}

				$inline_pattern = '~<span data-id="' . $then_field . '"[^>]*>(.*?)</span>~si';

				if ( preg_match_all( $inline_pattern, $form_html, $matches ) ) {
					if ( ! empty( $matches ) && isset( $matches[1][0] ) ) {
						if ( preg_match( '/' . $field['name'] . '/', $matches[1][0], $matches ) ) {
							if ( ! empty( $wpcf7cf['and_rules'] ) ) {
								foreach ( $wpcf7cf['and_rules'] as $rule ) {
									$if_field    = $field_data[ $rule['if_field'] ];
									$is_rule     = 'equals' === $rule['operator'] ? 'is' : 'is_not';
									$condition[] = array(
										'element_id' => $if_field,
										'rule'       => $is_rule,
										'value'      => $rule['if_value'],
									);
								}
							}
						}
					}
				}
			}
		}

		return $condition;
	}
}