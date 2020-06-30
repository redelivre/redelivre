<?php

/**
 * Forminator_Quiz_Front_Mail
 *
 * @since 1.6.2
 */
class Forminator_Quiz_Front_Mail extends Forminator_Mail {

	protected $message_vars;

	/**
	 * Default content type
	 *
	 * @var string
	 */
	protected $content_type = 'text/html; charset=UTF-8';

	/**
	 * Initialize the mail
	 *
	 * @param array $post_vars - post variables
	 */
	public function init( $post_vars ) {
		$user_email  = false;
		$user_name   = '';
		$user_login  = '';
		$embed_id    = $post_vars['page_id'];
		$embed_title = get_the_title( $embed_id );
		$embed_url   = forminator_get_current_url();
		$site_url    = site_url();

		//Check if user is logged in
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$user_email   = $current_user->user_email;
			if ( ! empty( $current_user->user_firstname ) ) {
				$user_name = $current_user->user_firstname . ' ' . $current_user->user_lastname;
			} elseif ( ! empty( $current_user->display_name ) ) {
				$user_name = $current_user->display_name;
			} else {
				$user_name = $current_user->display_name;
			}
			$user_login = $current_user->user_login;
		}

		//Set up mail variables
		$message_vars       = forminator_set_message_vars( $embed_id, $embed_title, $embed_url, $user_name, $user_email, $user_login, $site_url );
		$this->message_vars = $message_vars;

	}

	/**
	 * Process mail
	 *
	 * @since 1.6.2
	 *
	 * @param Forminator_Quiz_Form_Model  $quiz
	 * @param array                       $data
	 * @param Forminator_Form_Entry_Model $entry
	 */
	public function process_mail( $quiz, $data, Forminator_Form_Entry_Model $entry ) {
		forminator_maybe_log( __METHOD__ );

		$setting = $quiz->settings;

		if ( ! isset( $data['current_url'] ) || empty( $data['current_url'] ) ) {
			$data['current_url'] = forminator_get_current_url();
		}

		/**
		 * Message data filter
		 *
		 * @since 1.6.2
		 *
		 * @param array                       $data - the post data
		 * @param Forminator_Quiz_Form_Model  $quiz - the quiz model
		 * @param Forminator_Form_Entry_Model $entry
		 *
		 *
		 * @return array $data
		 */
		$data = apply_filters( 'forminator_quiz_mail_data', $data, $quiz, $entry );

		/**
		 * Action called before mail is sent
		 *
		 * @param Forminator_Quiz_Front_Mail  $this - the current mail class
		 * @param Forminator_Quiz_Form_Model  $quiz - the current quiz
		 * @param array                       $data - current data
		 * @param Forminator_Form_Entry_Model $entry
		 */
		do_action( 'forminator_quiz_mail_before_send_mail', $this, $quiz, $data, $entry );

		//Process admin mail
		if ( $this->is_send_admin_mail( $setting ) ) {
			$this->init( $_POST ); // WPCS: CSRF OK
			$recipients = $this->get_admin_email_recipients( $data, $quiz, $entry );

			if ( ! empty( $recipients ) ) {
				$subject = $setting['admin-email-title'];
				$subject = forminator_replace_variables( $subject, $quiz->id, $data['current_url'] );
				$subject = forminator_replace_quiz_form_data( $subject, $quiz, $data, $entry );

				/**
				 * Quiz admin mail subject filter
				 *
				 * @since 1.6.2
				 *
				 * @param string                     $subject
				 * @param Forminator_Quiz_Form_Model $quiz the current quiz modal
				 *
				 * @return string $subject
				 */
				$subject = apply_filters( 'forminator_quiz_mail_admin_subject', $subject, $quiz, $data, $entry, $this );


				$message = $setting['admin-email-editor'];
				$message = forminator_replace_variables( $message, $quiz->id, $data['current_url'] );
				$message = forminator_replace_quiz_form_data( $message, $quiz, $data, $entry );

				/**
				 * Quiz admin mail message filter
				 *
				 * @since 1.6.2
				 *
				 * @param string                     $message
				 * @param Forminator_Quiz_Form_Model $quiz the current quiz
				 * @param array                      $data
				 * @param Forminator_Quiz_Front_Mail $this
				 *
				 * @return string $message
				 */
				$message = apply_filters( 'forminator_quiz_mail_admin_message', $message, $quiz, $data, $entry, $this );

				$from_name = $this->sender_name;
				if ( isset( $setting['admin-email-from-name'] ) && ! empty( $setting['admin-email-from-name'] ) ) {
					$setting_from_name = $setting['admin-email-from-name'];
					$setting_from_name = forminator_replace_variables( $setting_from_name, $quiz->id, $data['current_url'] );
					$setting_from_name = forminator_replace_quiz_form_data( $setting_from_name, $quiz, $data, $entry );

					if ( ! empty( $setting_from_name ) ) {
						$from_name = $setting_from_name;
					}
				}
				/**
				 * Filter `From` name of mail that send to admin
				 *
				 * @since 1.6.2
				 *
				 * @param string                      $from_name
				 * @param Forminator_Quiz_Form_Model  $quiz  current quiz Model
				 * @param array                       $data  POST data
				 * @param Forminator_Form_Entry_Model $entry entry model
				 * @param Forminator_Quiz_Front_Mail  $this  mail class
				 */
				$from_name = apply_filters( 'forminator_quiz_mail_admin_from_name', $from_name, $quiz, $data, $entry, $this );

				$from_email = $this->sender_email;
				if ( isset( $setting['admin-email-from-address'] ) && ! empty( $setting['admin-email-from-address'] ) ) {
					$setting_from_address = $setting['admin-email-from-address'];
					$setting_from_address = forminator_replace_variables( $setting_from_address, $quiz->id, $data['current_url'] );
					$setting_from_address = forminator_replace_quiz_form_data( $setting_from_address, $quiz, $data, $entry );

					if ( is_email( $setting_from_address ) ) {
						$from_email = $setting_from_address;
					}
				}
				/**
				 * Filter `From` email address of mail that send to admin
				 *
				 * @since 1.6.2
				 *
				 * @param string                      $from_email
				 * @param Forminator_Quiz_Form_Model  $quiz  current quiz Model
				 * @param array                       $data  POST data
				 * @param Forminator_Form_Entry_Model $entry entry model
				 * @param Forminator_Quiz_Front_Mail  $this  mail class
				 */
				$from_email = apply_filters( 'forminator_quiz_mail_admin_from_email', $from_email, $quiz, $data, $entry, $this );

				$reply_to_address = '';
				if ( isset( $setting['admin-email-reply-to-address'] ) && ! empty( $setting['admin-email-reply-to-address'] ) ) {
					$setting_reply_to_address = $setting['admin-email-reply-to-address'];
					$setting_reply_to_address = forminator_replace_variables( $setting_reply_to_address, $quiz->id, $data['current_url'] );
					$setting_reply_to_address = forminator_replace_quiz_form_data( $setting_reply_to_address, $quiz, $data, $entry );

					if ( is_email( $setting_reply_to_address ) ) {
						$reply_to_address = $setting_reply_to_address;
					}
				}

				/**
				 * Filter `Reply To` email address of mail that send to admin
				 *
				 * @since 1.6.2
				 *
				 * @param string                      $reply_to_address
				 * @param Forminator_Quiz_Form_Model  $quiz  current quiz Model
				 * @param array                       $data  POST data
				 * @param Forminator_Form_Entry_Model $entry entry model
				 * @param Forminator_Quiz_Front_Mail  $this  mail class
				 */
				$reply_to_address = apply_filters( 'forminator_quiz_mail_admin_reply_to', $reply_to_address, $quiz, $data, $entry, $this );

				$cc_addresses = array();
				if ( isset( $setting['admin-email-cc-address'] ) && ! empty( $setting['admin-email-cc-address'] ) && is_array( $setting['admin-email-cc-address'] ) ) {
					$setting_cc_addresses = $setting['admin-email-cc-address'];

					foreach ( $setting_cc_addresses as $key => $setting_cc_address ) {
						$setting_cc_address = forminator_replace_variables( $setting_cc_address, $quiz->id, $data['current_url'] );
						$setting_cc_address = forminator_replace_quiz_form_data( $setting_cc_address, $quiz, $data, $entry );
						if ( is_email( $setting_cc_address ) ) {
							$cc_addresses[] = $setting_cc_address;
						}
					}
				}
				/**
				 * Filter `CC` email addresses of mail that send to admin
				 *
				 * @since 1.6.2
				 *
				 * @param array                       $cc_addresses
				 * @param Forminator_Quiz_Form_Model  $quiz  current quiz Model
				 * @param array                       $data  POST data
				 * @param Forminator_Form_Entry_Model $entry entry model
				 * @param Forminator_Quiz_Front_Mail  $this  mail class
				 */
				$cc_addresses = apply_filters( 'forminator_quiz_mail_admin_cc_addresses', $cc_addresses, $quiz, $data, $entry, $this );

				$bcc_addresses = array();
				if ( isset( $setting['admin-email-bcc-address'] ) && ! empty( $setting['admin-email-bcc-address'] ) && is_array( $setting['admin-email-bcc-address'] ) ) {
					$setting_bcc_addresses = $setting['admin-email-bcc-address'];

					foreach ( $setting_bcc_addresses as $key => $setting_bcc_address ) {
						$setting_bcc_address = forminator_replace_variables( $setting_bcc_address, $quiz->id, $data['current_url'] );
						$setting_bcc_address = forminator_replace_quiz_form_data( $setting_bcc_address, $quiz, $data, $entry );
						if ( is_email( $setting_bcc_address ) ) {
							$bcc_addresses[] = $setting_bcc_address;
						}
					}
				}
				/**
				 * Filter `BCC` email addresses of mail that send to admin
				 *
				 * @since 1.6.2
				 *
				 * @param array                       $bcc_addresses
				 * @param Forminator_Quiz_Form_Model  $quiz  current quiz Model
				 * @param array                       $data  POST data
				 * @param Forminator_Form_Entry_Model $entry entry model
				 * @param Forminator_Quiz_Front_Mail  $this  mail class
				 */
				$bcc_addresses = apply_filters( 'forminator_quiz_mail_admin_bcc_addresses', $bcc_addresses, $quiz, $data, $entry, $this );

				$content_type = $this->content_type;
				/**
				 * Filter `Content-Type` of mail that send to admin
				 *
				 * @since 1.6.2
				 *
				 * @param string                      $content_type
				 * @param Forminator_Quiz_Form_Model  $quiz  current quiz Model
				 * @param array                       $data  POST data
				 * @param Forminator_Form_Entry_Model $entry entry model
				 * @param Forminator_Quiz_Front_Mail  $this  mail class
				 */
				$content_type = apply_filters( 'forminator_quiz_mail_admin_content_type', $content_type, $quiz, $data, $entry, $this );

				$headers = array();

				// only change From header if these two are valid
				if ( ! empty( $from_name ) && ! empty( $from_email ) ) {
					$headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
				}

				if ( ! empty( $reply_to_address ) ) {
					$headers[] = 'Reply-To: ' . $reply_to_address;
				}

				if ( ! empty( $cc_addresses ) && is_array( $cc_addresses ) ) {
					$headers[] = 'Cc: ' . implode( ', ', $cc_addresses );
				}

				if ( ! empty( $bcc_addresses ) && is_array( $bcc_addresses ) ) {
					$headers[] = 'BCc: ' . implode( ', ', $bcc_addresses );
				}

				if ( ! empty( $content_type ) ) {
					$headers[] = 'Content-Type: ' . $content_type;
				}

				/**
				 * Filter headers of mail that send to admin
				 *
				 * @since 1.6.2
				 *
				 * @param array                       $headers
				 * @param Forminator_Quiz_Form_Model  $quiz  current quiz Model
				 * @param array                       $data  POST data
				 * @param Forminator_Form_Entry_Model $entry entry model
				 * @param Forminator_Quiz_Front_Mail  $this  mail class
				 */
				$headers = apply_filters( 'forminator_quiz_mail_admin_headers', $headers, $quiz, $data, $entry, $this );

				$this->set_headers( $headers );

				$this->set_subject( $subject );
				$this->set_recipients( $recipients );
				$this->set_message_with_vars( $this->message_vars, $message );
				$this->send_multiple();

				/**
				 * Action called after admin mail sent
				 *
				 * @param Forminator_Quiz_Front_Mail  $this       the mail class
				 * @param Forminator_Quiz_Form_Model  $quiz       the current quiz
				 * @param array                       $data       - current data
				 * @param Forminator_Form_Entry_Model $entry      - saved entry
				 * @param array                       $recipients - array or recipients
				 */
				do_action( 'forminator_quiz_mail_admin_sent', $this, $quiz, $data, $entry, $recipients );
			}
		}


		/**
		 * Action called after mail is sent
		 *
		 * @param Forminator_Quiz_Front_Mail $this mail class
		 * @param Forminator_Quiz_Form_Model $quiz current quiz
		 * @param array                      $data current data
		 */
		do_action( 'forminator_quiz_mail_after_send_mail', $this, $quiz, $data );
	}

	/**
	 * Check if all conditions are met to send admin email
	 *
	 * @since 1.6.2
	 *
	 * @param array $setting - the quiz settings
	 *
	 * @return bool
	 */
	public function is_send_admin_mail( $setting ) {
		if ( isset( $setting['use-admin-email'] ) && ! empty( $setting['use-admin-email'] ) ) {
			if ( filter_var( $setting['use-admin-email'], FILTER_VALIDATE_BOOLEAN ) ) {
				if ( isset( $setting['admin-email-title'] ) && isset( $setting['admin-email-editor'] ) ) {
					return true;
				}
			}
		}

		return false;
	}


	/**
	 * Get Recipients of admin emails
	 *
	 * @since 1.6.2
	 *
	 * @param array                       $data
	 * @param Forminator_Quiz_Form_Model  $quiz
	 * @param Forminator_Form_Entry_Model $entry
	 *
	 * @return array
	 */
	public function get_admin_email_recipients( $data, $quiz, $entry ) {
		$setting = $quiz->settings;
		$email   = array();
		if ( isset( $setting['admin-email-recipients'] ) && ! empty( $setting['admin-email-recipients'] ) ) {
			if ( is_array( $setting['admin-email-recipients'] ) ) {
				$email = $setting['admin-email-recipients'];
			}
		}

		return apply_filters( 'forminator_quiz_get_admin_email_recipients', $email, $setting, $data, $quiz, $entry );
	}

}
