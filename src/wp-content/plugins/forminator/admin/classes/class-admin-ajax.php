<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Admin_AJAX
 *
 * @since 1.0
 */
class Forminator_Admin_AJAX {

	/**
	 * Forminator_Admin_AJAX constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		// Handle close welcome box
		add_action( 'wp_ajax_forminator_dismiss_welcome', array( $this, 'dismiss_welcome' ) );
		add_action( 'wp_ajax_nopriv_forminator_dismiss_welcome', array( $this, 'dismiss_welcome' ) );

		// Handle load google fonts
		add_action( 'wp_ajax_forminator_load_google_fonts', array( $this, 'load_google_fonts' ) );

		// Handle load reCaptcha preview
		add_action( 'wp_ajax_forminator_load_recaptcha_preview', array( $this, 'load_recaptcha_preview' ) );

		// Handle save settings
		add_action( 'wp_ajax_forminator_save_builder', array( $this, 'save_builder' ) );
		add_action( 'wp_ajax_forminator_save_poll', array( $this, 'save_poll_form' ) );
		add_action( 'wp_ajax_forminator_save_quiz_nowrong', array( $this, 'save_quiz' ) );
		add_action( 'wp_ajax_forminator_save_quiz_knowledge', array( $this, 'save_quiz' ) );
		add_action( 'wp_ajax_forminator_save_login', array( $this, 'save_login' ) );
		add_action( 'wp_ajax_forminator_save_register', array( $this, 'save_register' ) );

		// Handle settings popups
		add_action( 'wp_ajax_forminator_load_captcha_popup', array( $this, 'load_captcha' ) );
		add_action( 'wp_ajax_forminator_save_captcha_popup', array( $this, 'save_captcha' ) );

		add_action( 'wp_ajax_forminator_load_currency_popup', array( $this, 'load_currency' ) );
		add_action( 'wp_ajax_forminator_save_currency_popup', array( $this, 'save_currency' ) );

		add_action( 'wp_ajax_forminator_load_pagination_entries_popup', array( $this, 'load_pagination_entries' ) );
		add_action( 'wp_ajax_forminator_save_pagination_entries_popup', array( $this, 'save_pagination_entries' ) );

		add_action( 'wp_ajax_forminator_load_pagination_listings_popup', array( $this, 'load_pagination_listings' ) );
		add_action( 'wp_ajax_forminator_save_pagination_listings_popup', array( $this, 'save_pagination_listings' ) );

		add_action( 'wp_ajax_forminator_load_email_settings_popup', array( $this, 'load_email_form' ) );

		add_action( 'wp_ajax_forminator_load_uninstall_settings_popup', array( $this, 'load_uninstall_form' ) );
		add_action( 'wp_ajax_forminator_save_uninstall_settings_popup', array( $this, 'save_uninstall_form' ) );

		add_action( 'wp_ajax_forminator_load_preview_cforms_popup', array( $this, 'preview_custom_forms' ) );
		add_action( 'wp_ajax_forminator_load_preview_polls_popup', array( $this, 'preview_polls' ) );
		add_action( 'wp_ajax_forminator_load_preview_quizzes_popup', array( $this, 'preview_quizzes' ) );

		// Handle exports popup
		add_action( 'wp_ajax_forminator_load_exports_popup', array( $this, 'load_exports' ) );
		add_action( 'wp_ajax_forminator_clear_exports_popup', array( $this, 'clear_exports' ) );

		// Handle search user email
		add_action( 'wp_ajax_forminator_builder_search_emails', array( $this, 'search_emails' ) );

		add_action( 'wp_ajax_forminator_load_privacy_settings_popup', array( $this, 'load_privacy_settings' ) );
		add_action( 'wp_ajax_forminator_save_privacy_settings_popup', array( $this, 'save_privacy_settings' ) );

		add_action( 'wp_ajax_forminator_load_export_custom_form_popup', array( $this, 'load_export_custom_form' ) );
		add_action( 'wp_ajax_forminator_load_import_custom_form_popup', array( $this, 'load_import_custom_form' ) );
		add_action( 'wp_ajax_forminator_save_import_custom_form_popup', array( $this, 'save_import_custom_form' ) );

		add_action( "wp_ajax_forminator_load_import_custom_form_cf7_popup", array( $this, "load_import_custom_form_cf7" ) );
		add_action( "wp_ajax_forminator_save_import_custom_form_cf7_popup", array( $this, "save_import_custom_form_cf7" ) );

		add_action( "wp_ajax_forminator_load_import_custom_form_ninja_popup", array( $this, "load_import_custom_form_ninja" ) );
		add_action( "wp_ajax_forminator_save_import_custom_form_ninja_popup", array( $this, "save_import_custom_form_ninja" ) );

		add_action( "wp_ajax_forminator_load_import_custom_form_gravity_popup", array( $this, "load_import_custom_form_gravity" ) );
		add_action( "wp_ajax_forminator_save_import_custom_form_gravity_popup", array( $this, "save_import_custom_form_gravity" ) );

		add_action( "wp_ajax_forminator_load_export_poll_popup", array( $this, "load_export_poll" ) );
		add_action( "wp_ajax_forminator_load_import_poll_popup", array( $this, "load_import_poll" ) );
		add_action( "wp_ajax_forminator_save_import_poll_popup", array( $this, "save_import_poll" ) );

		add_action( 'wp_ajax_forminator_delete_poll_submissions', array( $this, 'delete_poll_submissions' ) );

		add_action( 'wp_ajax_forminator_load_export_quiz_popup', array( $this, 'load_export_quiz' ) );
		add_action( 'wp_ajax_forminator_load_import_quiz_popup', array( $this, 'load_import_quiz' ) );
		add_action( 'wp_ajax_forminator_save_import_quiz_popup', array( $this, 'save_import_quiz' ) );

		add_action( 'wp_ajax_forminator_save_accessibility_settings_popup', array( $this, 'save_accessibility_settings' ) );

		add_action( 'wp_ajax_forminator_validate_calculation_formula', array( $this, 'validate_calculation_formula' ) );
		add_action( 'wp_ajax_forminator_save_dashboard_settings_popup', array( $this, 'save_dashboard_settings' ) );

		add_action( 'wp_ajax_forminator_stripe_settings_modal', array( $this, 'stripe_settings_modal' ) );
		add_action( 'wp_ajax_forminator_stripe_update_page', array( $this, 'stripe_update_page' ) );
		add_action( 'wp_ajax_forminator_disconnect_stripe', array( $this, 'stripe_disconnect' ) );

		add_action( 'wp_ajax_forminator_paypal_settings_modal', array( $this, 'paypal_settings_modal' ) );
		add_action( 'wp_ajax_forminator_paypal_update_page', array( $this, 'paypal_update_page' ) );
		add_action( 'wp_ajax_forminator_disconnect_paypal', array( $this, 'paypal_disconnect' ) );

		add_action( 'wp_ajax_forminator_save_payments_settings_popup', array( $this, 'save_payments' ) );
		add_action( 'wp_ajax_forminator_dismiss_notification', array( $this, 'dismiss_notice' ) );

		add_action( 'wp_ajax_forminator_later_notification', array( $this, 'later_notice' ) );
	}

	/**
	 * Save quizzes
	 *
	 * @since 1.0
	 * @since 1.1 change $_POST to `get_post_data`
	 */
	public function save_quiz() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		forminator_validate_ajax( 'forminator_save_quiz' );

		$submitted_data = $this->get_post_data();

		$quiz_data = array();
		if ( isset( $submitted_data['data'] ) ) {
			$quiz_data = $submitted_data['data'];
			$quiz_data = json_decode( stripslashes( $quiz_data ), true );
		}

		$questions = array();
		$results   = array();
		$settings  = array();
		$msg_count = false;
		$id      = isset( $submitted_data['form_id'] ) ? $submitted_data['form_id'] : null;
		$id      = intval( $id );
		$title   = isset( $submitted_data['quiz_title'] ) ? sanitize_text_field( $submitted_data['quiz_title'] ) : sanitize_text_field( $submitted_data['formName'] );
		$status  = isset( $submitted_data['status'] ) ? sanitize_text_field( $submitted_data['status'] ) : '';
		$version = isset( $submitted_data['version'] ) ? sanitize_text_field( $submitted_data['version'] ) : '1.0';
		$action  = false;

		if ( is_null( $id ) || $id <= 0 ) {
			$form_model = new Forminator_Quiz_Form_Model();
			$action     = 'create';

			if ( empty( $status ) ) {
				$status = Forminator_Poll_Form_Model::STATUS_PUBLISH;
			}
		} else {
			$form_model = Forminator_Quiz_Form_Model::model()->load( $id );
			$action     = 'update';

			if ( ! is_object( $form_model ) ) {
				wp_send_json_error( __( "Quiz model doesn't exist", Forminator::DOMAIN ) );
			}

			if ( empty( $status ) ) {
				$status = $form_model->status;
			}

			//we need to empty fields cause we will send new data
			$form_model->clear_fields();
		}

		$action  = isset( $submitted_data['action'] ) ? $submitted_data['action'] : '';

		// Detect action
		$form_model->quiz_type = 'knowledge';
		if ( 'forminator_save_quiz_nowrong' === $action ) {
			$form_model->quiz_type = 'nowrong';
		}

		// Check if results exist
		if ( isset( $quiz_data['results'] ) && is_array( $quiz_data['results'] ) ) {
			$results = $quiz_data['results'];
			foreach ( $quiz_data['results'] as $key => $result ) {
				$description = '';
				if ( isset( $result['description'] ) ) {
					$description = $result['description'];
				}
				$results[ $key ]['description'] = $description;
			}

			$form_model->results = $results;
		}

		// Check if answers exist
		if ( isset( $quiz_data['questions'] ) ) {
			$questions = forminator_sanitize_field( $quiz_data['questions'] );
		}

		// Check if questions exist
		if ( isset( $questions ) ) {
			foreach ( $questions as &$question ) {
				$question['type'] = $form_model->quiz_type;
				if ( ! isset( $question['slug'] ) || empty( $question['slug'] ) ) {
					$question['slug'] = uniqid();
				}
			}
		}

		$form_model->set_var_in_array( 'name', 'formName', $submitted_data );

		// Handle quiz questions
		$form_model->questions = $questions;

		if ( isset( $quiz_data['settings']['msg_count'] ) ) {
			$msg_count = $quiz_data['settings']['msg_count']; //Backup, we allow html here
		}

		if ( isset( $quiz_data['settings'] ) ) {
			// Sanitize settings
			$settings = forminator_sanitize_field( $quiz_data['settings'] );
		}

		// Sanitize admin email message
		if ( isset( $quiz_data['settings']['admin-email-editor'] ) ) {
			$settings['admin-email-editor'] = $quiz_data['settings']['admin-email-editor'];
		}

		// Sanitize quiz description
		if ( isset( $quiz_data['settings']['quiz_description'] ) ) {
			$settings['quiz_description'] = $quiz_data['settings']['quiz_description'];
		}

		if ( isset( $quiz_data['settings']['social-share-message'] ) ) {
			$settings['social-share-message'] = forminator_sanitize_textarea( $quiz_data['settings']['social-share-message'] );
		}

		// Update with backuped version
		if ( $msg_count ) {
			$settings['msg_count'] = $msg_count;
		}

		// version
		$settings['version'] = $version;

		$form_model->settings = $settings;

		$quiz_data['formName'] = $title;

		// status
		$form_model->status = $status;

		// Save data
		$id = $form_model->save();

		$type = $form_model->quiz_type;

		/**
		 * Action called after quiz saved to database
		 *
		 * @since 1.11
		 *
		 * @param int    $id - quiz id
		 * @param string $type - quiz type
		 * @param string $status - quiz status
		 * @param array  $questions - quiz questions
		 * @param array  $results - quiz results
		 *
		 */
		do_action( 'forminator_poll_action_' . $action, $id, $type, $status, $questions, $results );


		wp_send_json_success( $id );
	}

	/**
	 * Save poll
	 *
	 * @since 1.0
	 * @since 1.1 change $_POST to `get_post_data`
	 */
	public function save_poll_form() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		forminator_validate_ajax( 'forminator_save_poll' );

		$submitted_data = $this->get_post_data();
		$poll_data      = array();
		if ( isset( $submitted_data['data'] ) ) {
			$poll_data = $submitted_data['data'];
			$poll_data = json_decode( stripslashes( $poll_data ), true );
		}

		$answers  = array();
		$settings = array();
		$id       = isset( $submitted_data['form_id'] ) ? $submitted_data['form_id'] : null;
		$id       = intval( $id );
		$status   = isset( $submitted_data['status'] ) ? sanitize_text_field( $submitted_data['status'] ) : '';
		$version  = isset( $submitted_data['version'] ) ? sanitize_text_field( $submitted_data['version'] ) : '1.0';
		$action   = false;

		if ( is_null( $id ) || $id <= 0 ) {
			$form_model = new Forminator_Poll_Form_Model();
			$action     = 'create';

			if ( empty( $status ) ) {
				$status = Forminator_Poll_Form_Model::STATUS_PUBLISH;
			}
		} else {
			$form_model = Forminator_Poll_Form_Model::model()->load( $id );
			$action     = 'update';

			if ( ! is_object( $form_model ) ) {
				wp_send_json_error( __( "Poll model doesn't exist", Forminator::DOMAIN ) );
			}

			if ( empty( $status ) ) {
				$status = $form_model->status;
			}

			//we need to empty fields cause we will send new data
			$form_model->clear_fields();
		}

		$form_model->set_var_in_array( 'name', 'formName', $submitted_data );

		// Check if answers exist
		if ( isset( $poll_data['answers'] ) ) {
			$answers = forminator_sanitize_field( $poll_data['answers'] );
		}

		if ( isset( $poll_data['settings'] ) ) {
			// Sanitize settings
			$settings = forminator_sanitize_field( $poll_data['settings'] );
		}

		// Sanitize admin email message
		if ( isset( $poll_data['settings']['admin-email-editor'] ) ) {
			$settings['admin-email-editor'] = $poll_data['settings']['admin-email-editor'];
		}

		// version
		$settings['version'] = $version;

		$form_model->settings = $settings;

		foreach ( $answers as $answer ) {
			$field_model  = new Forminator_Form_Field_Model();
			$answer['id'] = $answer['element_id'];
			$field_model->import( $answer );
			$field_model->slug = $answer['element_id'];
			$form_model->add_field( $field_model );
		}

		// status
		$form_model->status = $status;

		// Save data
		$id = $form_model->save();

		/**
		* Action called after poll saved to database
		*
		* @since 1.11
		*
		* @param int    $id - poll id
		* @param string $status - poll status
		* @param array  $answers - poll answers
		* @param array  $settings - poll settings
		*
		*/
		do_action( 'forminator_poll_action_' . $action, $id, $status, $answers, $settings );

		// add privacy settings to global option
		$override_privacy = false;
		if ( isset( $settings['enable-ip-address-retention'] ) ) {
			$override_privacy = filter_var( $settings['enable-ip-address-retention'], FILTER_VALIDATE_BOOLEAN );
		}
		$retention_number = null;
		$retention_unit   = null;
		if ( $override_privacy ) {
			$retention_number = 0;
			$retention_unit   = 'days';
			if ( isset( $settings['ip-address-retention-number'] ) ) {
				$retention_number = (int) $settings['ip-address-retention-number'];
			}
			if ( isset( $settings['ip-address-retention-unit'] ) ) {
				$retention_unit = $settings['ip-address-retention-unit'];
			}
		}

		forminator_update_poll_ip_address_retention( $id, $retention_number, $retention_unit );

		wp_send_json_success( $id );
	}

	/**
	 * Save custom form fields
	 *
	 * @since 1.2
	 */
	public function save_builder() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		forminator_validate_ajax( 'forminator_save_builder_fields' );

		$submitted_data = $this->get_post_data();
		$form_data      = $submitted_data['data'];
		$form_data      = json_decode( stripslashes( $form_data ), true );
		$fields         = array();
		$notifications  = array();
		$id      = isset( $submitted_data['form_id'] ) ? $submitted_data['form_id'] : null;
		$id      = intval( $id );
		$title   = sanitize_text_field( $submitted_data['formName'] );
		$status  = isset( $submitted_data['status'] ) ? sanitize_text_field( $submitted_data['status'] ) : '';
		$version = isset( $submitted_data['version'] ) ? sanitize_text_field( $submitted_data['version'] ) : '1.0';
		$action  = false;

		if ( is_null( $id ) || $id <= 0 ) {
			$form_model = new Forminator_Custom_Form_Model();
			$action     = 'create';

			if ( empty( $status ) ) {
				$status = Forminator_Custom_Form_Model::STATUS_PUBLISH;
			}
		} else {
			$form_model = Forminator_Custom_Form_Model::model()->load( $id );
			$action     = 'update';

			if ( ! is_object( $form_model ) ) {
				wp_send_json_error( __( "Form model doesn't exist", Forminator::DOMAIN ) );
			}

			if ( empty( $status ) ) {
				$status = $form_model->status;
			}

			//we need to empty fields cause we will send new data
			$form_model->clear_fields();
		}

		$form_model->set_var_in_array( 'name', 'formName', $submitted_data, 'forminator_sanitize_field' );

		// Build the fields
		if ( isset( $form_data ) ) {
			$fields = $form_data['wrappers'];
			unset( $form_data['wrappers'] );
		}

		foreach ( $fields as $row ) {
			foreach ( $row['fields'] as $f ) {
				$field          = new Forminator_Form_Field_Model();
				$field->form_id = $row['wrapper_id'];
				$field->slug    = $f['element_id'];
				unset( $f['element_id'] );
				$field->import( $f );
				$form_model->add_field( $field );
			}
		}

		// Sanitize settings
		$settings = forminator_sanitize_field( $form_data['settings'] );
		$settings = apply_filters( 'forminator_builder_data_settings_before_saving', $settings, $form_data['settings'] );

		// Sanitize custom css
		if ( isset( $form_data['settings']['custom_css'] ) ) {
			$settings['custom_css'] = sanitize_textarea_field( $form_data['settings']['custom_css'] );
		}

		// Sanitize thank you message
		if ( isset( $form_data['settings']['thankyou-message'] ) ) {
			$settings['thankyou-message'] = $form_data['settings']['thankyou-message'];
		}

		// Sanitize user email message
		if ( isset( $form_data['settings']['user-email-editor'] ) ) {
			$settings['user-email-editor'] = $form_data['settings']['user-email-editor'];
		}

		// Sanitize admin email message
		if ( isset( $form_data['settings']['admin-email-editor'] ) ) {
			$settings['admin-email-editor'] = $form_data['settings']['admin-email-editor'];
		}

		if ( isset( $form_data['notifications'] ) ) {
			$notifications = forminator_sanitize_field( $form_data['notifications'] );

			$count = 0;
			foreach( $notifications as $notification ) {
				if( isset( $notification['email-editor'] ) ) {
					$notifications[ $count ]['email-editor'] = $form_data['notifications'][ $count ]['email-editor'];
				}
				$count++;
			}
		}

		$form_model->set_var_in_array( 'name', 'formName', $submitted_data );

		// Handle quiz questions
		$form_model->notifications = $notifications;

		$settings['formName'] = $title;

		$settings['version']  = $version;
		$form_model->settings = $settings;

		// status
		$form_model->status = $status;

		// Save data
		$id = $form_model->save();

		/**
		 * Action called after form saved to database
		 *
		 * @since 1.11
		 *
		 * @param int    $id - form id
		 * @param string $title - form title
		 * @param string $status - form status
		 * @param array  $fields - form fields
		 * @param array  $settings - form settings
		 *
		 */
		do_action( 'forminator_custom_form_action_' . $action, $id, $title, $status, $fields, $settings );

		// add privacy settings to global option
		$override_privacy = false;
		if ( isset( $settings['enable-submissions-retention'] ) ) {
			$override_privacy = filter_var( $settings['enable-submissions-retention'], FILTER_VALIDATE_BOOLEAN );
		}
		$retention_number = null;
		$retention_unit   = null;
		if ( $override_privacy ) {
			$retention_number = 0;
			$retention_unit   = 'days';
			if ( isset( $settings['submissions-retention-number'] ) ) {
				$retention_number = (int) $settings['submissions-retention-number'];
			}
			if ( isset( $settings['submissions-retention-unit'] ) ) {
				$retention_unit = $settings['submissions-retention-unit'];
			}
		}

		forminator_update_form_submissions_retention( $id, $retention_number, $retention_unit );

		wp_send_json_success( $id );
	}

	/**
	 * Save custom form settings
	 *
	 * @since 1.2
	 */
	public function save_builder_settings() {
		_deprecated_function( 'save_builder_settings', '1.6', 'save_builder' );
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		forminator_validate_ajax( 'forminator_save_builder_fields' );

		$submitted_data = $this->get_post_data();
		$fields         = array();
		$id             = isset( $submitted_data['form_id'] ) ? $submitted_data['form_id'] : null;
		$id             = intval( $id );
		$title          = sanitize_text_field( $submitted_data['formName'] );
		$status         = isset( $submitted_data['status'] ) ? sanitize_text_field( $submitted_data['status'] ) : '';
		$version        = isset( $submitted_data['version'] ) ? sanitize_text_field( $submitted_data['version'] ) : '1.0';

		if ( is_null( $id ) || $id <= 0 ) {
			$form_model = new Forminator_Custom_Form_Model();

			if ( empty( $status ) ) {
				$status = Forminator_Custom_Form_Model::STATUS_PUBLISH;
			}

		} else {
			$form_model = Forminator_Custom_Form_Model::model()->load( $id );

			if ( ! is_object( $form_model ) ) {
				wp_send_json_error( __( "Form model doesn't exist", Forminator::DOMAIN ) );
			}
			if ( empty( $status ) ) {
				$status = $form_model->status;
			}
		}
		$form_model->set_var_in_array( 'name', 'formName', $submitted_data, 'forminator_sanitize_field' );

		// Sanitize settings
		$settings = forminator_sanitize_field( $submitted_data['data'] );

		// Sanitize custom css
		if ( isset( $submitted_data['data']['custom_css'] ) ) {
			$settings['custom_css'] = sanitize_textarea_field( $submitted_data['data']['custom_css'] );
		}

		// Sanitize thank you message
		if ( isset( $submitted_data['data']['thankyou-message'] ) ) {
			$settings['thankyou-message'] = $submitted_data['data']['thankyou-message'];
		}

		// Sanitize user email message
		if ( isset( $submitted_data['data']['user-email-editor'] ) ) {
			$settings['user-email-editor'] = $submitted_data['data']['user-email-editor'];
		}

		// Sanitize admin email message
		if ( isset( $submitted_data['data']['admin-email-editor'] ) ) {
			$settings['admin-email-editor'] = $submitted_data['data']['admin-email-editor'];
		}

		$settings['formName'] = $title;
		$settings['version']  = $version;
		$form_model->settings = $settings;

		// status
		$form_model->status = $status;

		// Save data
		$id = $form_model->save();

		// add privacy settings to global option
		$override_privacy = false;
		if ( isset( $settings['enable-submissions-retention'] ) ) {
			$override_privacy = filter_var( $settings['enable-submissions-retention'], FILTER_VALIDATE_BOOLEAN );
		}
		$retention_number = null;
		$retention_unit   = null;
		if ( $override_privacy ) {
			$retention_number = 0;
			$retention_unit   = 'days';
			if ( isset( $settings['submissions-retention-number'] ) ) {
				$retention_number = (int) $settings['submissions-retention-number'];
			}
			if ( isset( $settings['submissions-retention-unit'] ) ) {
				$retention_unit = $settings['submissions-retention-unit'];
			}
		}

		forminator_update_form_submissions_retention( $id, $retention_number, $retention_unit );

		wp_send_json_success( $id );
	}

	/**
	 * Load existing custom field keys
	 *
	 * @since 1.0
	 * @return string JSON
	 */
	public function load_existing_cfields() {

		forminator_validate_ajax( 'forminator_load_existing_cfields' );

		$keys = array();
		$html = '';

		foreach ( $keys as $key ) {
			$html .= "<option value='$key'>$key</option>";
		}

		wp_send_json_success( $html );
	}

	/**
	 * Dismiss welcome message
	 *
	 * @since 1.0
	 */
	public function dismiss_welcome() {
		forminator_validate_ajax( 'forminator_dismiss_welcome' );
		update_option( 'forminator_welcome_dismissed', true );
		wp_send_json_success();
	}

	/**
	 * Load Google Fonts
	 *
	 * @since 1.0
	 */
	public function load_fonts() {
		forminator_validate_ajax( 'forminator_load_fonts' );
		_deprecated_function( 'load_fonts', '1.0.5', 'load_google_fonts' );
		wp_send_json_error( array() );
	}


	/**
	 * Load google fonts
	 *
	 * @since 1.0.5
	 */
	public function load_google_fonts() {
		forminator_validate_ajax( 'forminator_load_google_fonts' );

		$is_object = isset( $_POST['data']['isObject'] ) ? sanitize_text_field( $_POST['data']['isObject'] ) : false;// phpcs:ignore -- by forminator_validate_ajax

		$fonts = forminator_get_font_families( $is_object );
		wp_send_json_success( $fonts );
	}

	/**
	 * Load reCaptcha settings
	 *
	 * @since 1.0
	 */
	public function load_captcha() {
		// Validate nonce
		forminator_validate_ajax( 'forminator_popup_captcha' );

		$html = forminator_template( 'settings/popup/edit-captcha-content' );

		wp_send_json_success( $html );
	}

	/**
	 * Save reCaptcha popup data
	 *
	 * @since 1.0
	 */
	public function save_captcha() {
		// Validate nonce
		forminator_validate_ajax( 'forminator_save_popup_captcha' );

		update_option( "forminator_captcha_key", sanitize_text_field( $_POST['v2_captcha_key'] ) );
		update_option( "forminator_captcha_secret", sanitize_text_field( $_POST['v2_captcha_secret'] ) );

		update_option( "forminator_v2_invisible_captcha_key", sanitize_text_field( $_POST['v2_invisible_captcha_key'] ) );
		update_option( "forminator_v2_invisible_captcha_secret", sanitize_text_field( $_POST['v2_invisible_captcha_secret'] ) );

		update_option( "forminator_v3_captcha_key", sanitize_text_field( $_POST['v3_captcha_key'] ) );
		update_option( "forminator_v3_captcha_secret", sanitize_text_field( $_POST['v3_captcha_secret'] ) );

		update_option( "forminator_captcha_language", sanitize_text_field( $_POST['captcha_language'] ) );

		wp_send_json_success();
	}

	/**
	 * Load currency modal
	 *
	 * @since 1.0
	 */
	public function load_currency() {
		forminator_validate_ajax( 'forminator_popup_currency' );

		$html = forminator_template( 'settings/popup/edit-currency-content' );

		wp_send_json_success( $html );
	}

	/**
	 * Save reCaptcha popup data
	 *
	 * @since 1.0
	 */
	public function save_currency() {
		// Validate nonce
		forminator_validate_ajax( 'forminator_save_popup_currency' );

		update_option( "forminator_currency", sanitize_text_field( $_POST['currency'] ) );

		wp_send_json_success();
	}

	/**
	 * Load entries pagination modal
	 *
	 * @since 1.0.2
	 */
	public function load_pagination_entries() {
		// Validate nonce
		forminator_validate_ajax( 'forminator_popup_pagination_entries' );

		$html = forminator_template( 'settings/popup/edit-pagination-entries-content' );

		wp_send_json_success( $html );
	}

	/*
	 * Load reCaptcha preview
	 *
	 * @since 1.5.4
	 */
	public function load_recaptcha_preview() {
		$site_language = get_locale();
		$language      = get_option( 'forminator_captcha_language', '' );
		$language      = ! empty( $language ) ? $language : $site_language;

		$captcha = sanitize_text_field( $_POST['captcha'] );// phpcs:ignore -- data without nonce verification

		if ( 'v2-invisible' === $captcha ) {
			$captcha_key  = get_option( 'forminator_v2_invisible_captcha_key', '' );
			$captcha_size = 'invisible';
			$onload       = 'forminator_render_admin_captcha_v2_invisible';
		} elseif ( 'v3' === $captcha ) {
			$captcha_key  = get_option( 'forminator_v3_captcha_key', '' );
			$captcha_size = 'invisible';
			$onload       = 'forminator_render_admin_captcha_v3';
		} else {
			$captcha_key  = get_option( 'forminator_captcha_key', '' );
			$captcha_size = 'normal';
			$onload       = 'forminator_render_admin_captcha_v2';
		}
		$html = '';

		if ( ! empty( $captcha_key ) ) {
			// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
			$html .= '<script src="https://www.google.com/recaptcha/api.js?hl=' . $language . '&render=explicit&onload=' . $onload . '" async defer></script>';
			$html .= '<div class="forminator-g-recaptcha-' . $captcha . '" data-sitekey="' . $captcha_key . '" data-theme="light" data-size="' . $captcha_size . '"></div>';

		} else {
			$html .= '<div class="sui-notice">';
			$html .= '<p>' . esc_html__( 'You have to first save your credentials to load the reCAPTCHA . ', Forminator::DOMAIN ) . '</p>';
			$html .= '</div>';
		}

		wp_send_json_success( $html );
	}

	/**
	 * Load listings pagination modal
	 *
	 * @since 1.0.2
	 */
	public function load_pagination_listings() {
		// Validate nonce
		forminator_validate_ajax( 'forminator_popup_pagination_listings' );

		$html = forminator_template( 'settings/popup/edit-pagination-listings-content' );

		wp_send_json_success( $html );
	}

	/**
	 * Save listings pagination popup data
	 *
	 * @since 1.0.2
	 */
	public function save_pagination_listings() {
		// Validate nonce
		forminator_validate_ajax( 'forminator_save_popup_pagination_listings' );

		$pagination = intval( sanitize_text_field( $_POST['pagination_listings'] ) );

		if ( 0 < $pagination ) {

			update_option( 'forminator_pagination_listings', $pagination );
			wp_send_json_success();

		} else {

			wp_send_json_error( __( 'Limit per page can not be less than one.', Forminator::DOMAIN ) );

		}

	}

	/**
	 * Load the email settings form
	 *
	 * @since 1.1
	 */
	public function load_email_form() {
		// Validate nonce
		forminator_validate_ajax( 'forminator_load_popup_email_settings' );

		$html = forminator_template( 'settings/popup/edit-email-content' );

		wp_send_json_success( $html );
	}

	/**
	 * Load the uninstall form
	 *
	 * @since 1.0.2
	 */
	public function load_uninstall_form() {
		// Validate nonce
		forminator_validate_ajax( 'forminator_popup_uninstall_form' );

		$html = forminator_template( 'settings/popup/edit-uninstall-content' );

		wp_send_json_success( $html );
	}


	/**
	 * Save listings pagination popup data
	 *
	 * @since 1.0.2
	 */
	public function save_uninstall_form() {
		// Validate nonce
		forminator_validate_ajax( 'forminator_save_popup_uninstall_settings' );

		$delete_uninstall = isset( $_POST['delete_uninstall'] ) ? sanitize_text_field( $_POST['delete_uninstall'] ) : false;
		$delete_uninstall = filter_var( $delete_uninstall, FILTER_VALIDATE_BOOLEAN );

		update_option( 'forminator_uninstall_clear_data', $delete_uninstall );
		wp_send_json_success();

	}

	/**
	 * Preview custom forms
	 *
	 * @since 1.0
	 */
	public function preview_custom_forms() {
		// Validate nonce
		forminator_validate_ajax( 'forminator_popup_preview_cforms' );

		$preview_data = false;
		$form_id      = false;

		if ( isset( $_POST['id'] ) ) {
			$form_id = intval( $_POST['id'] );
		}

		// Check if preview data set
		if ( isset( $_POST['data'] ) && ! empty( $_POST['data'] ) ) {
			$data = $_POST['data']; // WPCS: CSRF ok by forminator_validate_ajax.

			if ( ! is_array( $data ) ) {
				$data = json_decode( stripslashes( $data ), true );
			}
			$preview_data = forminator_data_to_model_form( $data );// phpcs:ignore -- by forminator_validate_ajax
		}

		$html = forminator_form_preview( $form_id, true, $preview_data );

		wp_send_json_success( $html );
	}

	/**
	 * Preview polls
	 *
	 * @since 1.0
	 */
	public function preview_polls() {
		// Validate nonce
		forminator_validate_ajax( 'forminator_popup_preview_polls' );

		$preview_data = false;
		// force -1 for preview
		$form_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : -1;// phpcs:ignore -- by forminator_validate_ajax

		if ( isset( $_POST['id'] ) ) {
			$form_id = intval( $_POST['id'] );
		}

		// Check if preview data set
		// Check if preview data set
		if ( isset( $_POST['data'] ) && ! empty( $_POST['data'] ) ) {
			$data = $_POST['data'];

			if ( ! is_array( $data ) ) {
				$data = json_decode( stripslashes( $data ), true );
			}
			$preview_data = forminator_data_to_model_poll( $data );// phpcs:ignore -- by forminator_validate_ajax
		}

		$html = forminator_poll_preview( $form_id, true, $preview_data );

		wp_send_json_success( $html );
	}

	/**
	 * Preview quizzes
	 *
	 * @since 1.0
	 */
	public function preview_quizzes() {
		// Validate nonce
		forminator_validate_ajax( 'forminator_popup_preview_quizzes' );

		// force -1 for preview
		$form_id = - 1;

		if ( isset( $_POST['id'] ) ) {
			$form_id = intval( $_POST['id'] );
		}

		// Check if preview data set
		if ( isset( $_POST['data'] ) && ! empty( $_POST['data'] ) ) {
			$preview_data = forminator_data_to_model_quiz( $_POST['data'] );
		}

		$html = forminator_quiz_preview( $form_id, true, $preview_data );

		wp_send_json_success( $html );
	}

	/**
	 * Load list of exports
	 *
	 * @since 1.0
	 */
	public function load_exports() {
		// Validate nonce
		forminator_validate_ajax( 'forminator_load_exports' );

		$form_id = isset( $_POST['id'] ) && $_POST['id'] >= 0 ? intval( $_POST['id'] ) : false;

		if ( $form_id ) {
			$args = array(
				'form_id' => $form_id,
			);
			$html = forminator_template( 'settings/popup/exports-content', $args );
			wp_send_json_success( $html );
		} else {
			wp_send_json_error( __( 'Not valid module ID provided.', Forminator::DOMAIN ) );
		}
	}

	/**
	 * Clear list of exports
	 *
	 * @since 1.0
	 */
	public function clear_exports() {
		// Validate nonce
		forminator_validate_ajax( "forminator_clear_exports" );

		$form_id = isset( $_POST['id'] ) && $_POST['id'] >= 0 ? intval( $_POST['id'] ) : false;

		if ( ! $form_id ) {
			wp_send_json_error( __( 'No ID was provided.', Forminator::DOMAIN ) );
		}

		$was_cleared = delete_export_logs( $form_id );

		if ( $was_cleared ) {
			wp_send_json_success( __( 'Exports cleared.', Forminator::DOMAIN ) );
		} else {
			wp_send_json_error( __( "Exports couldn't be cleared.", Forminator::DOMAIN ) );
		}
	}

	/**
	 * Search Emails
	 *
	 * @since 1.0.3
	 * @since 1.1 change $_POST to `get_post_data`
	 */
	public function search_emails() {
		forminator_validate_ajax( 'forminator_search_emails' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array() );
		}

		$submitted_data = $this->get_post_data();

		//TODO : add ajax validate here and js admin too
		$admin_email  = ( ( isset( $submitted_data['admin_email'] ) && $submitted_data['admin_email'] ) ? true : false );
		$search_email = ( ( isset( $submitted_data['q'] ) && $submitted_data['q'] ) ? sanitize_text_field( $submitted_data['q'] ) : false );

		// return admin_email when requested
		if ( $admin_email ) {
			wp_send_json_success( get_option( 'admin_email' ) );
		}

		if ( ! $search_email ) {
			wp_send_json_success( array() );
		}

		$args = array(
			'search'  => '*' . $search_email . '*',
			'number'  => 10,
			'orderby' => 'user_login',
			'order'   => 'ASC',
		);

		/**
		 * Filter args to be passed on to get_users
		 *
		 * @see   get_users()
		 *
		 * @since 1.2
		 *
		 * @param array  $args
		 * @param string $search_email string to search
		 */
		$args = apply_filters( 'forminator_builder_search_emails_args', $args, $search_email );

		$users = get_users( $args );
		$data  = array();
		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {
				$data[] = array(
					'id'           => $user->user_email,
					'text'         => $user->user_email,
					'display_name' => $user->display_name,
				);
			}
		}

		/**
		 * Filter returned data when builder search emails
		 *
		 * @since 1.2
		 *
		 * @param array  $data
		 * @param array  $users        search result of get_users
		 * @param array  $args         current query args passed to get_users
		 * @param string $search_email string to search
		 */
		$data = apply_filters( 'forminator_builder_search_emails_data', $data, $users, $args, $search_email );

		wp_send_json_success( $data );
	}

	/**
	 * Get $_POST data
	 *
	 * @since 1.1
	 *
	 * @param string $nonce_action       action to validate
	 * @param array  $sanitize_callbacks {
	 *                                   custom sanitize options, its assoc array
	 *                                   'field_name_1' => 'function_to_call_1' function will called with `call_user_func_array`,
	 *                                   'field_name_2' => 'function_to_call_2',
	 *                                   }
	 *
	 * @return array
	 */
	protected function get_post_data( $nonce_action = '', $sanitize_callbacks = array() ) {
		// do nonce / caps check when requested
		if ( ! empty( $nonce_action ) ) {
			// it will wp_send_json_error
			forminator_validate_ajax( $nonce_action );
		}

		// TODO : mark this as phpcs comply after checking usages of this function
		$post_data = $_POST;// phpcs:ignore -- by forminator_validate_ajax

		// do some sanitize
		foreach ( $sanitize_callbacks as $field => $sanitize_func ) {
			if ( isset( $post_data[ $field ] ) ) {
				if ( is_callable( $sanitize_func ) ) {
					$post_data[ $field ] = call_user_func_array( array( $sanitize_func ), array( $post_data[ $field ] ) );
				}
			}
		}

		// do some validation

		return $post_data;
	}

	/*
	 * Load Privacy Settings
	 *
	 * @since 1.0.6
	 */
	public function load_privacy_settings() {
		// Validate nonce
		forminator_validate_ajax( 'forminator_popup_privacy_settings' );

		$html = forminator_template( 'settings/popup/edit-privacy-settings' );

		wp_send_json_success( $html );
	}

	/**
	 * Save Privacy Settings
	 *
	 * @since 1.0.6
	 */
	public function save_privacy_settings() {
		// Validate nonce
		forminator_validate_ajax( 'forminator_save_privacy_settings' );
		$post_data = $_POST;// phpcs:ignore -- by forminator_validate_ajax

		/**
		 * CUSTOM FORMS
		 */
		// Account Erasure Requests
		if ( isset( $post_data['erase_form_submissions'] ) ) {
			$post_data['erase_form_submissions']           = sanitize_text_field( $post_data['erase_form_submissions'] );
			$enable_erasure_request_erase_form_submissions = filter_var( $post_data['erase_form_submissions'], FILTER_VALIDATE_BOOLEAN );
			update_option( 'forminator_enable_erasure_request_erase_form_submissions', $enable_erasure_request_erase_form_submissions );
		}
		// Account Erasure Requests

		// Submissions Retention
		$cform_retain_forever = filter_var( $post_data['retain_submission_forever'], FILTER_VALIDATE_BOOLEAN );
		update_option( 'retain_submission_forever', $cform_retain_forever );
		if ( $cform_retain_forever ) {
			$post_data['submissions_retention_number'] = 0;
		}
		if ( isset( $post_data['submissions_retention_number'] ) ) {
			$post_data['submissions_retention_number'] = sanitize_text_field( $post_data['submissions_retention_number'] );
			$post_data['submissions_retention_unit']   = sanitize_text_field( $post_data['submissions_retention_unit'] );
			$submissions_retention_number              = intval( $post_data['submissions_retention_number'] );
			if ( $submissions_retention_number < 0 ) {
				$submissions_retention_number = 0;
			}
			update_option( 'forminator_retain_submissions_interval_number', $submissions_retention_number );
		}
		update_option( 'forminator_retain_submissions_interval_unit', $post_data['submissions_retention_unit'] );
		// Submissions Retention

		// IP Retention
		$cform_retain_ip_forever = filter_var( $post_data['retain_ip_forever'], FILTER_VALIDATE_BOOLEAN );
		update_option( 'retain_ip_forever', $cform_retain_ip_forever );
		if ( $cform_retain_ip_forever ) {
			$post_data['cform_retention_ip_number'] = 0;
		}
		if ( isset( $post_data['cform_retention_ip_number'] ) ) {
			$post_data['cform_retention_ip_number'] = sanitize_text_field( $post_data['cform_retention_ip_number'] );
			$post_data['cform_retention_ip_unit']   = sanitize_text_field( $post_data['cform_retention_ip_unit'] );
			$cform_ip_retention_number              = intval( $post_data['cform_retention_ip_number'] );
			if ( $cform_ip_retention_number < 0 ) {
				$cform_ip_retention_number = 0;
			}
			update_option( 'forminator_retain_ip_interval_number', $cform_ip_retention_number );
		}
		update_option( 'forminator_retain_ip_interval_unit', $post_data['cform_retention_ip_unit'] );
		// IP Retention

		/**
		 * POLLS
		 */
		// Submissions Retention
		$poll_retain_submissions_forever = filter_var( $post_data['poll_retain_submission_forever'], FILTER_VALIDATE_BOOLEAN );
		update_option( 'poll_retain_submission_forever', $poll_retain_submissions_forever );
		if ( $poll_retain_submissions_forever ) {
			$post_data['poll_submissions_retention_number'] = 0;
		}
		// Polls
		if ( isset( $post_data['poll_submissions_retention_number'] ) ) {
			$post_data['poll_submissions_retention_number'] = sanitize_text_field( $post_data['poll_submissions_retention_number'] );
			$post_data['poll_submissions_retention_unit']   = sanitize_text_field( $post_data['poll_submissions_retention_unit'] );
			$poll_submissions_retention_number              = intval( $post_data['poll_submissions_retention_number'] );
			if ( $poll_submissions_retention_number < 0 ) {
				$poll_submissions_retention_number = 0;
			}
			update_option( 'forminator_retain_poll_submissions_interval_number', $poll_submissions_retention_number );
		}
		update_option( 'forminator_retain_poll_submissions_interval_unit', $post_data['poll_submissions_retention_unit'] );
		// Submissions Retention

		// IP Retention
		$poll_retain_ip_forever = filter_var( $post_data['retain_poll_forever'], FILTER_VALIDATE_BOOLEAN );
		update_option( 'retain_poll_forever', $poll_retain_ip_forever );
		if ( $poll_retain_ip_forever ) {
			$post_data['votes_retention_number'] = 0;
		}
		if ( isset( $post_data['votes_retention_number'] ) ) {
			$post_data['votes_retention_number'] = sanitize_text_field( $post_data['votes_retention_number'] );
			$post_data['votes_retention_unit']   = sanitize_text_field( $post_data['votes_retention_unit'] );
			$votes_retention_number              = intval( $post_data['votes_retention_number'] );
			if ( $votes_retention_number < 0 ) {
				$votes_retention_number = 0;
			}
			update_option( 'forminator_retain_votes_interval_number', $votes_retention_number );
		}
		update_option( 'forminator_retain_votes_interval_unit', $post_data['votes_retention_unit'] );
		// IP Retention

		/**
		 * QUIZ
		 */
		// Submissions Retention
		$quiz_retain_submissions_forever = filter_var( $post_data['quiz_retain_submission_forever'], FILTER_VALIDATE_BOOLEAN );
		update_option( 'quiz_retain_submission_forever', $quiz_retain_submissions_forever );
		if ( $quiz_retain_submissions_forever ) {
			$post_data['quiz_submissions_retention_number'] = 0;
		}
		if ( isset( $post_data['quiz_submissions_retention_number'] ) ) {
			$post_data['quiz_submissions_retention_number'] = sanitize_text_field( $post_data['quiz_submissions_retention_number'] );
			$post_data['quiz_submissions_retention_unit']   = sanitize_text_field( $post_data['quiz_submissions_retention_unit'] );
			$quiz_submissions_retention_number              = intval( $post_data['quiz_submissions_retention_number'] );
			if ( $quiz_submissions_retention_number < 0 ) {
				$quiz_submissions_retention_number = 0;
			}
			update_option( 'forminator_retain_quiz_submissions_interval_number', $quiz_submissions_retention_number );
		}
		update_option( 'forminator_retain_quiz_submissions_interval_unit', $post_data['quiz_submissions_retention_unit'] );
		// Submissions Retention

		wp_send_json_success();
	}

	/**
	 * Load Export Custom Form Popup
	 *
	 * @since 1.5
	 */
	public function load_export_custom_form() {
		if ( ! Forminator::is_import_export_feature_enabled() ) {
			wp_send_json_success( '' );
		}
		// Validate nonce
		forminator_validate_ajax( 'forminator_popup_export_cform' );

		$html = forminator_template( 'custom-form/popup/export' );

		wp_send_json_success( $html );
	}

	/**
	 * Load Import Custom Form Popup
	 *
	 * @since 1.5
	 */
	public function load_import_custom_form() {
		if ( ! Forminator::is_import_export_feature_enabled() ) {
			wp_send_json_success( '' );
		}
		// Validate nonce
		forminator_validate_ajax( 'forminator_popup_import_cform' );

		$html = forminator_template( 'custom-form/popup/import' );

		wp_send_json_success( $html );
	}	

	/**
	 * Execute Import Form
	 *
	 * @since 1.5
	 */
	public function save_import_custom_form() {
		if ( ! Forminator::is_import_export_feature_enabled() ) {
			wp_send_json_error( __( 'Import Export Feature disabled.', Forminator::DOMAIN ) );
		}
		// Validate nonce
		forminator_validate_ajax( 'forminator_save_import_custom_form' );

		$post_data  = $this->get_post_data();
		$importable = isset( $post_data['importable'] ) ? $post_data['importable'] : '';// wpcs: CSRF ok
		$importable = trim( $importable );
		$importable = wp_unslash( $importable );

		$import_data = json_decode( $importable, true );

		//hook custom data here
		$import_data = apply_filters( 'forminator_form_import_data', $import_data );

		try {
			if ( empty( $importable ) ) {
				throw new Exception( __( 'Import text can not be empty.', Forminator::DOMAIN ) );
			}

			if ( empty( $import_data ) || ! is_array( $import_data ) ) {
				throw new Exception( __( 'Oops, looks like we found an issue. Import text can not include whitespace or special characters.', Forminator::DOMAIN ) );
			}

			if ( ! isset( $import_data['type'] ) || 'form' !== $import_data['type'] ) {
				throw new Exception( __( 'Oops, looks like we found an issue. Import text can not include whitespace or special characters.', Forminator::DOMAIN ) );
			}

			$model = Forminator_Custom_Form_Model::create_from_import_data( $import_data, 'Forminator_Custom_Form_Model' );

			if ( is_wp_error( $model ) ) {
				throw new Exception( $model->get_error_message() );
			}

			if ( ! $model instanceof Forminator_Custom_Form_Model ) {
				throw new Exception( __( 'Failed to import form, please make sure import text is valid, and try again.', Forminator::DOMAIN ) );
			}

			$return_url = admin_url( 'admin.php?page=forminator-cform' );

			wp_send_json_success(
				array(
					'id'  => $model->id,
					'url' => $return_url,
				)
			);

		} catch ( Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}


	/**
	 * Get instance of thirdparty importer class
	 *
	 * @since 1.5
	 */
	public function importers( $type ) {

		$class = ''; 

		switch ( $type ) {

			case 'cf7':
				if( class_exists( 'Forminator_Admin_Import_CF7' ) )
					$class = new Forminator_Admin_Import_CF7();
				break;
			case 'ninja':
				if( class_exists( 'Forminator_Admin_Import_Ninja' ) )
	    			$class = new Forminator_Admin_Import_Ninja();
				break;			
			case 'gravity':
				if( class_exists( 'Forminator_Admin_Import_Gravity' ) )
	    			return new Forminator_Admin_Import_Gravity();
				break;
		}

		return $class;
	}


	/**
	 * Load Import Custom Form Popup
	 *
	 * @since 1.5
	 */
	public function load_import_custom_form_cf7() {
		if ( ! Forminator::is_import_export_feature_enabled() || ! forminator_is_import_plugin_enabled('cf7') ) {
			wp_send_json_success( '' );
		}
		// Validate nonce
		forminator_validate_ajax( "forminator_popup_import_cform_cf7" );

		$html = forminator_template( 'custom-form/popup/import-cf7' );

		wp_send_json_success( $html );
	}


	/**
	 * Execute Contact Form 7 Import Form
	 *
	 * @since 1.5
	 */
	public function save_import_custom_form_cf7() {
		global $wpdb, $wpcf7_shortcode_manager;

		if ( ! Forminator::is_import_export_feature_enabled() || ! forminator_is_import_plugin_enabled( 'cf7' ) ) {
			wp_send_json_error( __( 'Import Export Feature disabled.', Forminator::DOMAIN ) );
		}
		// Validate nonce
		forminator_validate_ajax( "forminator_save_import_custom_form_cf7" );

		$post_data  = $this->get_post_data();
		$importable = ( isset( $post_data['cf7_forms'] ) ? $post_data['cf7_forms'] : '' );// wpcs: CSRF ok
		$importer   = ( ! empty ( $this->importers( 'cf7' ) ) ? $this->importers( 'cf7' ) : '' );
		if ( ! empty( $importer ) ) :
			if ( ! empty( $importable ) ) {
				if ( 'specific' === $importable ) {
					$forms = isset( $post_data['cf7-form-id'] ) ? $post_data['cf7-form-id'] : array();
				} else {
					$forms = forminator_list_thirdparty_contact_forms( 'cf7' );
				}
				if ( ! empty( $forms ) ) {
					foreach ( $forms as $key => $value ) {
						$values   = 'specific' === $importable ? $value : $value->ID;
						$imported = $importer->import_form( $values, $post_data );

						if ( 'fail' === $imported['type'] ) {

							$error = $imported['message'];
						}
					}
					if ( ! empty( $error ) ) {
						wp_send_json_error( $error );
					}

					wp_send_json_success( $imported );
				}
			} else {
				wp_send_json_error( __( 'Can\'t find form to import', Forminator::DOMAIN ) );
			}
		endif;

		wp_send_json_error( __( 'Could not import the forms. Check if the selected form plugin is active', Forminator::DOMAIN ) );

	}


	/**
	 * Load Import Custom Form Popup
	 *
	 * @since 1.5
	 */
	public function load_import_custom_form_ninja() {
		if ( ! Forminator::is_import_export_feature_enabled() || ! forminator_is_import_plugin_enabled('ninjaforms') ) {
			wp_send_json_success( '' );
		}		
		// Validate nonce
		forminator_validate_ajax( "forminator_popup_import_cform_ninjaforms" );

		$html = forminator_template( 'custom-form/popup/import-ninjaforms' );

		wp_send_json_success( $html );
	}

	/**
	 * Execute Ninjaforms Import Form Save
	 *
	 * @since 1.5
	 */
	public function save_import_custom_form_ninja() {

		if ( ! Forminator::is_import_export_feature_enabled() || ! forminator_is_import_plugin_enabled('ninjaforms') ) {
			wp_send_json_error( __( 'Import Export Feature disabled.', Forminator::DOMAIN ) );
		}
		// Validate nonce
		forminator_validate_ajax( "forminator_save_import_custom_form_ninja" );

		$post_data  = $this->get_post_data();
		$importable = isset( $post_data['ninjaforms'] ) ? $post_data['ninjaforms'] : '';// wpcs: CSRF ok
		$importer 	= ( ! empty ( $this->importers( 'ninja' ) ) ? $this->importers( 'ninja' ) : '' );

		if( ! empty( $importer ) ):
			if( 'all' !== $importable && '' !== $importable ){

				$importable  = absint( $importable );
				$imported = $importer->import_form( $importable );

				if( 'fail' === $imported['type'] ){

					wp_send_json_error( $imported['message'] );
				}

				wp_send_json_success( $imported );
				
			}elseif( '' !== $importable ){

				$forms = forminator_list_thirdparty_contact_forms( 'ninjaforms' );

				foreach ($forms as $key => $value) {

					$imported = $importer->import_form( $value->get_id() );

					if( 'fail' === $imported['type'] ){

						$error = $imported['message'];
					}
				}

				if( !empty( $error ) ){
					wp_send_json_error( $error );	
				}

				wp_send_json_success( $imported );
			}
		endif;

		wp_send_json_error( __( 'Could not import the forms. Check if the selected form plugin is active', Forminator::DOMAIN ) );

	}	

	/**
	 * Load Import Custom Form Popup
	 *
	 * @since 1.5
	 */
	public function load_import_custom_form_gravity() {
		if ( ! Forminator::is_import_export_feature_enabled() || ! forminator_is_import_plugin_enabled('gravityforms') ) {
			wp_send_json_success( '' );
		}		
		// Validate nonce
		forminator_validate_ajax( "forminator_popup_import_cform_gravityforms" );

		$html = forminator_template( 'custom-form/popup/import-gravityforms' );

		wp_send_json_success( $html );
	}

	/**
	 * Execute Ninjaforms Import Form Save
	 *
	 * @since 1.5
	 */
	public function save_import_custom_form_gravity() {

		if ( ! Forminator::is_import_export_feature_enabled() || ! forminator_is_import_plugin_enabled('gravityforms') ) {
			wp_send_json_error( __( 'Import Export Feature disabled.', Forminator::DOMAIN ) );
		}
		// Validate nonce
		forminator_validate_ajax( "forminator_save_import_custom_form_gravity" );

			$post_data  = $this->get_post_data();
		$importable = isset( $post_data['gravityforms'] ) ? $post_data['gravityforms'] : '';// wpcs: CSRF ok
		$importer 	= ( ! empty ( $this->importers( 'gravity' ) ) ? $this->importers( 'gravity' ) : '' );

		if( ! empty( $importer ) ):
			if( 'all' !== $importable && '' !== $importable ){

				$importable  = absint( $importable );
				$imported = $importer->import_form( $importable );

				if( 'fail' === $imported['type'] ){

					wp_send_json_error( $imported['message'] );
				}

				wp_send_json_success( $imported );
				
			}elseif( '' !== $importable ){

				$forms = forminator_list_thirdparty_contact_forms( 'gravityforms' );

				foreach ($forms as $key => $value) {

					$imported = $importer->import_form( $value['id'] );

					if( 'fail' === $imported['type'] ){

						$error = $imported['message'];
					}
				}

				if( !empty( $error ) ){
					wp_send_json_error( $error );	
				}

				wp_send_json_success( $imported );
			}
		endif;

	}

	/**
	 * Load Export Poll Popup
	 *
	 * @since 1.5
	 */
	public function load_export_poll() {
		if ( ! Forminator::is_import_export_feature_enabled() ) {
			wp_send_json_success( '' );
		}
		// Validate nonce
		forminator_validate_ajax( 'forminator_popup_export_poll' );

		$html = forminator_template( 'poll/popup/export' );

		wp_send_json_success( $html );
	}

	/**
	 * Load Import Poll Popup
	 *
	 * @since 1.5
	 */
	public function load_import_poll() {
		if ( ! Forminator::is_import_export_feature_enabled() ) {
			wp_send_json_success( '' );
		}
		// Validate nonce
		forminator_validate_ajax( 'forminator_popup_import_poll' );

		$html = forminator_template( 'poll/popup/import' );

		wp_send_json_success( $html );
	}

	/**
	 * Execute Import Poll
	 *
	 * @since 1.5
	 */
	public function save_import_poll() {
		if ( ! Forminator::is_import_export_feature_enabled() ) {
			wp_send_json_error( __( 'Import Export Feature disabled.', Forminator::DOMAIN ) );
		}
		// Validate nonce
		forminator_validate_ajax( 'forminator_save_import_poll' );

		$post_data  = $this->get_post_data();
		$importable = isset( $post_data['importable'] ) ? $post_data['importable'] : '';// wpcs: CSRF ok
		$importable = trim( $importable );
		$importable = wp_unslash( $importable );

		try {
			if ( empty( $importable ) ) {
				throw new Exception( __( 'Import text can not be empty.', Forminator::DOMAIN ) );
			}

			$import_data = json_decode( $importable, true );

			if ( empty( $import_data ) || ! is_array( $import_data ) ) {
				throw new Exception( __( 'Oops, looks like we found an issue. Import text can not include whitespace or special characters.', Forminator::DOMAIN ) );
			}

			if ( ! isset( $import_data['type'] ) || 'poll' !== $import_data['type'] ) {
				throw new Exception( __( 'Oops, looks like we found an issue. Import text can not include whitespace or special characters.', Forminator::DOMAIN ) );
			}

			$model = Forminator_Poll_Form_Model::create_from_import_data( $import_data, 'Forminator_Poll_Form_Model' );

			if ( is_wp_error( $model ) ) {
				throw new Exception( $model->get_error_message() );
			}

			if ( ! $model instanceof Forminator_Poll_Form_Model ) {
				throw new Exception( __( 'Failed to import poll, please make sure import text is valid, and try again.', Forminator::DOMAIN ) );
			}

			$return_url = admin_url( 'admin.php?page=forminator-poll' );

			wp_send_json_success(
				array(
					'id'  => $model->id,
					'url' => $return_url,
				)
			);

		} catch ( Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	/**
	 * Load Export Quiz Popup
	 *
	 * @since 1.5
	 */
	public function load_export_quiz() {
		if ( ! Forminator::is_import_export_feature_enabled() ) {
			wp_send_json_success( '' );
		}
		// Validate nonce
		forminator_validate_ajax( 'forminator_popup_export_quiz' );

		$html = forminator_template( 'quiz/popup/export' );

		wp_send_json_success( $html );
	}

	/**
	 * Load Import Quiz Popup
	 *
	 * @since 1.5
	 */
	public function load_import_quiz() {
		if ( ! Forminator::is_import_export_feature_enabled() ) {
			wp_send_json_success( '' );
		}
		// Validate nonce
		forminator_validate_ajax( 'forminator_popup_import_quiz' );

		$html = forminator_template( 'quiz/popup/import' );

		wp_send_json_success( $html );
	}

	/**
	 * Execute Import Quiz
	 *
	 * @since 1.5
	 */
	public function save_import_quiz() {
		if ( ! Forminator::is_import_export_feature_enabled() ) {
			wp_send_json_error( __( 'Import Export Feature disabled.', Forminator::DOMAIN ) );
		}
		// Validate nonce
		forminator_validate_ajax( 'forminator_save_import_quiz' );

		$post_data  = $this->get_post_data();
		$importable = isset( $post_data['importable'] ) ? $post_data['importable'] : '';// wpcs: CSRF ok
		$importable = trim( $importable );
		$importable = wp_unslash( $importable );

		try {
			if ( empty( $importable ) ) {
				throw new Exception( __( 'Import text can not be empty.', Forminator::DOMAIN ) );
			}

			$import_data = json_decode( $importable, true );

			if ( empty( $import_data ) || ! is_array( $import_data ) ) {
				throw new Exception( __( 'Oops, looks like we found an issue. Import text can not include whitespace or special characters.', Forminator::DOMAIN ) );
			}

			if ( ! isset( $import_data['type'] ) || 'quiz' !== $import_data['type'] ) {
				throw new Exception( __( 'Oops, looks like we found an issue. Import text can not include whitespace or special characters.', Forminator::DOMAIN ) );
			}

			/** @var Forminator_Quiz_Form_Model|WP_Error $model */
			$model = Forminator_Quiz_Form_Model::create_from_import_data( $import_data, 'Forminator_Quiz_Form_Model' );

			if ( is_wp_error( $model ) ) {
				throw new Exception( $model->get_error_message() );
			}

			if ( ! $model instanceof Forminator_Quiz_Form_Model ) {
				throw new Exception( __( 'Failed to import quiz, please make sure import text is valid, and try again.', Forminator::DOMAIN ) );
			}

			$return_url = admin_url( 'admin.php?page=forminator-quiz' );

			wp_send_json_success(
				array(
					'id'  => $model->id,
					'url' => $return_url,
				)
			);

		} catch ( Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	/**
	 * Save pagination data
	 *
	 * @since 1.6
	 */
	public function save_pagination() {
		// Validate nonce
		forminator_validate_ajax( 'forminator_save_popup_pagination' );

		$pagination         = intval( sanitize_text_field( $_POST['pagination_entries'] ) );
		$pagination_listing = intval( sanitize_text_field( $_POST['pagination_listings'] ) );

		if ( 1 > $pagination || 1 > $pagination_listing ) {
			wp_send_json_error( __( 'Limit per page can not be less than one.', Forminator::DOMAIN ) );
		}

		update_option( 'forminator_pagination_entries', $pagination );
		update_option( 'forminator_pagination_listings', $pagination_listing );
		wp_send_json_success();

	}

	/**
	 * Save accessibility_settings
	 *
	 * @since 1.6.1
	 */
	public function save_accessibility_settings() {
		// Validate nonce
		forminator_validate_ajax( 'forminator_save_accessibility_settings' );

		$enable_accessibility = isset( $_POST['enable_accessibility'] ) ? $_POST['enable_accessibility'] : false;
		$enable_accessibility = filter_var( $enable_accessibility, FILTER_VALIDATE_BOOLEAN );

		update_option( 'forminator_enable_accessibility', $enable_accessibility );
		wp_send_json_success();
	}

	/**
	 * Save dashboard
	 *
	 * @since 1.6.3
	 */
	public function save_dashboard_settings() {
		// Validate nonce
		forminator_validate_ajax( 'forminator_save_dashboard_settings' );

		$dashboard_settings = forminator_get_dashboard_settings();
		$widgets            = array( 'forms', 'polls', 'quizzes' );

		$num_recents = isset( $_POST['num_recent'] ) ? $_POST['num_recent'] : array();// phpcs:ignore -- by forminator_validate_ajax
		$publisheds  = isset( $_POST['published'] ) ? $_POST['published'] : array();// phpcs:ignore -- by forminator_validate_ajax
		$drafts      = isset( $_POST['draft'] ) ? $_POST['draft'] : array();// phpcs:ignore -- by forminator_validate_ajax

		// value based settings
		foreach ( $num_recents as $widget => $value ) {
			if ( ! isset( $dashboard_settings[ $widget ] ) ) {
				$dashboard_settings[ $widget ] = array();
			}
			$value = intval( $value );
			// at least 0
			if ( $value >= 0 ) {
				$dashboard_settings[ $widget ]['num_recent'] = intval( $value );
			}
		}

		// bool based settings aka checkboxes
		foreach ( $widgets as $widget ) {
			if ( ! isset( $dashboard_settings[ $widget ] ) ) {
				$dashboard_settings[ $widget ] = array();
			}

			// default enabled, handle when not exist = false
			if ( ! isset( $publisheds[ $widget ] ) ) {
				$dashboard_settings[ $widget ]['published'] = false;
			}
			if ( ! isset( $drafts[ $widget ] ) ) {
				$dashboard_settings[ $widget ]['draft'] = false;
			}
		}

		update_option( 'forminator_dashboard_settings', $dashboard_settings );
		update_option( "forminator_sender_email_address", sanitize_text_field( $_POST['sender_email'] ) );
		update_option( "forminator_sender_name", sanitize_text_field( $_POST['sender_name'] ) );

		$pagination         = intval( sanitize_text_field( $_POST['pagination_entries'] ) );
		$pagination_listing = intval( sanitize_text_field( $_POST['pagination_listings'] ) );


		if ( 1 > $pagination || 1 > $pagination_listing ) {
			wp_send_json_error( __( 'Limit per page can not be less than one.', Forminator::DOMAIN ) );
		}

		update_option( 'forminator_pagination_entries', $pagination );
		update_option( 'forminator_pagination_listings', $pagination_listing );

		wp_send_json_success();
	}

	/**
	 * Validate Calculation Formula
	 *
	 * @since 1.7
	 */
	public function validate_calculation_formula() {

		// Validate nonce
		forminator_validate_ajax( 'forminator_validate_calculation_formula' );

		try {
			$formula = isset( $_POST['formula'] ) ? $_POST['formula'] : '';// phpcs:ignore -- by forminator_validate_ajax

			$formula    = forminator_calculator_maybe_dummify_fields_on_formula( $formula );
			$calculator = new Forminator_Calculator( $formula );
			// handle throw
			$calculator->set_is_throwable( true );
			$calculator->parse();

			wp_send_json_success( __( 'Calculation formula validated successfully.', Forminator::DOMAIN ) );

		} catch ( Forminator_Calculator_Exception $e ) {
			wp_send_json_error( __( 'Invalid calculation formula. Please check again.', Forminator::DOMAIN ) );
		}
	}

	/**
	 * Disconnect stripe
	 *
	 * @since 1.7
	 */
	public function stripe_disconnect() {
		// Validate nonce
		forminator_validate_ajax( 'forminatorSettingsRequest' );

		if ( class_exists( 'Forminator_Gateway_Stripe' ) ) {
			Forminator_Gateway_Stripe::store_settings( array() );
		}
		$data['notification'] = array(
			'type'     => 'success',
			'text'     => __( 'Stripe account disconnected successfully.', Forminator::DOMAIN ),
			'duration' => '4000',
		);
		$file                 = forminator_plugin_dir() . 'admin/views/settings/payments/section-stripe.php';

		ob_start();
		/** @noinspection PhpIncludeInspection */
		include $file;
		$data['html'] = ob_get_clean();

		wp_send_json_success( $data );
	}

	/**
	 * Disconnect PayPal
	 *
	 * @since 1.7
	 */
	public function paypal_disconnect() {
		// Validate nonce
		forminator_validate_ajax( 'forminatorSettingsRequest' );

		if ( class_exists( 'Forminator_PayPal_Express' ) ) {
			Forminator_PayPal_Express::store_settings( array() );
		}
		$data['notification'] = array(
			'type'     => 'success',
			'text'     => __( 'PayPal account disconnected successfully.', Forminator::DOMAIN ),
			'duration' => '4000',
		);
		$file                 = forminator_plugin_dir() . 'admin/views/settings/payments/section-paypal.php';

		ob_start();
		/** @noinspection PhpIncludeInspection */
		include $file;
		$data['html'] = ob_get_clean();

		wp_send_json_success( $data );
	}

	/**
	 * Handle stripe settings
	 *
	 * @since 1.7
	 */
	public function stripe_update_page() {
		// Validate nonce
		forminator_validate_ajax( 'forminator_stripe_settings_modal' );

		$file = forminator_plugin_dir() . 'admin/views/settings/payments/section-stripe.php';

		ob_start();
		/** @noinspection PhpIncludeInspection */
		include $file;
		$html = ob_get_clean();

		wp_send_json_success( $html );
	}

	/**
	 * Handle PayPal settings
	 *
	 * @since 1.7
	 */
	public function paypal_update_page() {
		// Validate nonce
		forminator_validate_ajax( 'forminator_paypal_settings_modal' );

		$file = forminator_plugin_dir() . 'admin/views/settings/payments/section-paypal.php';

		ob_start();
		/** @noinspection PhpIncludeInspection */
		include $file;
		$html = ob_get_clean();

		wp_send_json_success( $html );
	}

	/**
	 * Handle stripe settings
	 *
	 * @since 1.7
	 */
	public function stripe_settings_modal() {
		if ( ! class_exists( 'Forminator_Gateway_Stripe' ) ) {
			return false;
		}

		// Validate nonce
		forminator_validate_ajax( 'forminator_stripe_settings_modal' );

		$data = array();

		$post_data          = $_POST;// phpcs:ignore -- by forminator_validate_ajax
		$is_connect_request = isset( $post_data['connect'] ) ? filter_var( $post_data['connect'] ) : false;
		$template_vars      = array();
		try {
			$stripe = new Forminator_Gateway_Stripe();

			$test_key         = isset( $post_data['test_key'] ) ? $post_data['test_key'] : $stripe->get_test_key();// WPCS: CSRF ok by forminator_validate_ajax.
			$test_secret      = isset( $post_data['test_secret'] ) ? $post_data['test_secret'] : $stripe->get_test_secret();// WPCS: CSRF ok by forminator_validate_ajax.
			$live_key         = isset( $post_data['live_key'] ) ? $post_data['live_key'] : $stripe->get_live_key();// WPCS: CSRF ok by forminator_validate_ajax.
			$live_secret      = isset( $post_data['live_secret'] ) ? $post_data['live_secret'] : $stripe->get_live_secret();// WPCS: CSRF ok by forminator_validate_ajax.
			$default_currency = $stripe->get_default_currency();

			$template_vars['test_key']    = $test_key;
			$template_vars['test_secret'] = $test_secret;
			$template_vars['live_key']    = $live_key;
			$template_vars['live_secret'] = $live_secret;

			if ( ! empty( $is_connect_request ) ) {
				if ( empty( $test_key ) ) {
					throw new Forminator_Gateway_Exception(
						'',
						Forminator_Gateway_Stripe::EMPTY_TEST_KEY_EXCEPTION
					);
				}
				if ( empty( $test_secret ) ) {
					throw new Forminator_Gateway_Exception(
						'',
						Forminator_Gateway_Stripe::EMPTY_TEST_SECRET_EXCEPTION
					);
				}

				Forminator_Gateway_Stripe::validate_keys( $test_key, $test_secret, Forminator_Gateway_Stripe::INVALID_TEST_SECRET_EXCEPTION );

				if ( empty( $live_key ) ) {
					throw new Forminator_Gateway_Exception(
						'',
						Forminator_Gateway_Stripe::EMPTY_LIVE_KEY_EXCEPTION
					);
				}
				if ( empty( $live_secret ) ) {
					throw new Forminator_Gateway_Exception(
						'',
						Forminator_Gateway_Stripe::EMPTY_LIVE_SECRET_EXCEPTION
					);
				}

				Forminator_Gateway_Stripe::validate_keys( $live_key, $live_secret, Forminator_Gateway_Stripe::INVALID_LIVE_SECRET_EXCEPTION );

				Forminator_Gateway_Stripe::store_settings(
					array(
						'test_key'         => $test_key,
						'test_secret'      => $test_secret,
						'live_key'         => $live_key,
						'live_secret'      => $live_secret,
						'default_currency' => $default_currency,
					)
				);

				$data['notification'] = array(
					'type'     => 'success',
					'text'     => __( 'Stripe account connected successfully. You can now add the Stripe field to your forms and start collecting payments.', Forminator::DOMAIN ),
					'duration' => '4000',
				);

			}
		} catch ( Forminator_Gateway_Exception $e ) {
			forminator_maybe_log( __METHOD__, $e->getMessage(), $e->getTrace() );
			$template_vars['error_message'] = $e->getMessage();

			if ( Forminator_Gateway_Stripe::EMPTY_TEST_KEY_EXCEPTION === $e->getCode() ) {
				$template_vars['test_key_error'] = __( 'Please input test publishable key' );
			}
			if ( Forminator_Gateway_Stripe::EMPTY_TEST_SECRET_EXCEPTION === $e->getCode() ) {
				$template_vars['test_secret_error'] = __( 'Please input test secret key' );
			}
			if ( Forminator_Gateway_Stripe::EMPTY_LIVE_KEY_EXCEPTION === $e->getCode() ) {
				$template_vars['live_key_error'] = __( 'Please input live publishable key' );
			}
			if ( Forminator_Gateway_Stripe::EMPTY_LIVE_SECRET_EXCEPTION === $e->getCode() ) {
				$template_vars['live_secret_error'] = __( 'Please input live secret key' );
			}
			if ( Forminator_Gateway_Stripe::INVALID_TEST_SECRET_EXCEPTION === $e->getCode() ) {
				$template_vars['test_secret_error'] = __( 'You\'ve entered an invalid test secret key' );
			}
			if ( Forminator_Gateway_Stripe::INVALID_LIVE_SECRET_EXCEPTION === $e->getCode() ) {
				$template_vars['live_secret_error'] = __( 'You\'ve entered an invalid live secret key' );
			}
			if ( Forminator_Gateway_Stripe::INVALID_TEST_KEY_EXCEPTION === $e->getCode() ) {
				$template_vars['test_key_error'] = __( 'You\'ve entered an invalid test publishable key' );
			}
			if ( Forminator_Gateway_Stripe::INVALID_LIVE_KEY_EXCEPTION === $e->getCode() ) {
				$template_vars['live_key_error'] = __( 'You\'ve entered an invalid live publishable key' );
			}
		}

		ob_start();
		/** @noinspection PhpIncludeInspection */
		include forminator_plugin_dir() . 'admin/views/settings/payments/stripe.php';
		$html = ob_get_clean();

		$data['html'] = $html;

		$data['buttons'] = array();

		$data['buttons']['connect']['markup'] = '<div class="sui-actions-right">' .
													'<button class="sui-button forminator-stripe-connect" type="button" data-nonce="' . wp_create_nonce( 'forminator_stripe_settings_modal' ) . '">' .
														'<span class="sui-loading-text">' . esc_html__( 'Connect', Forminator::DOMAIN ) . '</span>' .
														'<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>' .
													'</button>' .
												'</div>';

		wp_send_json_success( $data );
	}

	/**
	 * Handle PayPal settings
	 *
	 * @since 1.7.1
	 */
	public function paypal_settings_modal() {
		// Validate nonce
		forminator_validate_ajax( 'forminator_paypal_settings_modal' );

		$data = array();

		$post_data          = $_POST;// phpcs:ignore -- by forminator_validate_ajax
		$is_connect_request = isset( $post_data['connect'] ) ? filter_var( $post_data['connect'] ) : false;
		$template_vars      = array();

		try {
			$paypal = new Forminator_PayPal_Express();

			$sandbox_id       = isset( $post_data['sandbox_id'] ) ? $post_data['sandbox_id'] : $paypal->get_sandbox_id();// WPCS: CSRF ok by forminator_validate_ajax.
			$sandbox_secret   = isset( $post_data['sandbox_secret'] ) ? $post_data['sandbox_secret'] : $paypal->get_sandbox_secret();// WPCS: CSRF ok by forminator_validate_ajax.
			$live_id          = isset( $post_data['live_id'] ) ? $post_data['live_id'] : $paypal->get_live_id();// WPCS: CSRF ok by forminator_validate_ajax.
			$live_secret      = isset( $post_data['live_secret'] ) ? $post_data['live_secret'] : $paypal->get_live_secret();// WPCS: CSRF ok by forminator_validate_ajax.
			$default_currency = $paypal->get_default_currency();

			$template_vars['sandbox_id']     = $sandbox_id;
			$template_vars['sandbox_secret'] = $sandbox_secret;
			$template_vars['live_id']        = $live_id;
			$template_vars['live_secret']    = $live_secret;

			if ( ! empty( $is_connect_request ) ) {
				if ( empty( $sandbox_id ) ) {
					throw new Forminator_Gateway_Exception(
						'',
						Forminator_PayPal_Express::EMPTY_SANDBOX_ID_EXCEPTION
					);
				}
				if ( empty( $sandbox_secret ) ) {
					throw new Forminator_Gateway_Exception(
						'',
						Forminator_PayPal_Express::EMPTY_SANDBOX_SECRET_EXCEPTION
					);
				}

				Forminator_PayPal_Express::validate_id( 'sandbox', $sandbox_id, $sandbox_secret, Forminator_PayPal_Express::INVALID_SANDBOX_SECRET_EXCEPTION );

				if ( empty( $live_id ) ) {
					throw new Forminator_Gateway_Exception(
						'',
						Forminator_PayPal_Express::EMPTY_LIVE_ID_EXCEPTION
					);
				}
				if ( empty( $live_secret ) ) {
					throw new Forminator_Gateway_Exception(
						'',
						Forminator_PayPal_Express::EMPTY_LIVE_SECRET_EXCEPTION
					);
				}

				Forminator_PayPal_Express::validate_id( 'live', $live_id, $live_secret, Forminator_PayPal_Express::INVALID_LIVE_SECRET_EXCEPTION );

				Forminator_PayPal_Express::store_settings(
					array(
						'sandbox_id'     => $sandbox_id,
						'sandbox_secret' => $sandbox_secret,
						'live_id'        => $live_id,
						'live_secret'    => $live_secret,
						'currency'       => $default_currency,
					)
				);

				$data['notification'] = array(
					'type'     => 'success',
					'text'     => __( 'PayPal account connected successfully. You can now add the PayPal field to your forms and start collecting payments.', Forminator::DOMAIN ),
					'duration' => '4000',
				);

			}
		} catch ( Forminator_Gateway_Exception $e ) {
			forminator_maybe_log( __METHOD__, $e->getMessage(), $e->getTrace() );
			$template_vars['error_message'] = $e->getMessage();

			if ( Forminator_PayPal_Express::EMPTY_SANDBOX_ID_EXCEPTION === $e->getCode() ) {
				$template_vars['sandbox_id_error'] = __( 'Please input sandbox client id' );
			}
			if ( Forminator_PayPal_Express::EMPTY_SANDBOX_SECRET_EXCEPTION === $e->getCode() ) {
				$template_vars['sandbox_secret_error'] = __( 'Please input sandbox secret key' );
			}
			if ( Forminator_PayPal_Express::EMPTY_LIVE_ID_EXCEPTION === $e->getCode() ) {
				$template_vars['live_id_error'] = __( 'Please input live client id' );
			}
			if ( Forminator_PayPal_Express::EMPTY_LIVE_SECRET_EXCEPTION === $e->getCode() ) {
				$template_vars['live_secret_error'] = __( 'Please input live secret key' );
			}
			if ( Forminator_PayPal_Express::INVALID_SANDBOX_SECRET_EXCEPTION === $e->getCode() ) {
				$template_vars['sandbox_secret_error'] = __( 'You\'ve entered an invalid sandbox secret key' );
			}
			if ( Forminator_PayPal_Express::INVALID_LIVE_SECRET_EXCEPTION === $e->getCode() ) {
				$template_vars['live_secret_error'] = __( 'You\'ve entered an invalid live secret key' );
			}
			if ( Forminator_PayPal_Express::INVALID_SANDBOX_ID_EXCEPTION === $e->getCode() ) {
				$template_vars['sandbox_id_error'] = __( 'You\'ve entered an invalid sandbox client id' );
			}
			if ( Forminator_PayPal_Express::INVALID_LIVE_ID_EXCEPTION === $e->getCode() ) {
				$template_vars['live_id_error'] = __( 'You\'ve entered an invalid live client id' );
			}
		}

		ob_start();
		/** @noinspection PhpIncludeInspection */
		include forminator_plugin_dir() . 'admin/views/settings/payments/paypal.php';
		$html = ob_get_clean();

		$data['html'] = $html;

		$data['buttons'] = array();

		$data['buttons']['connect']['markup'] = '<div class="sui-actions-right">' .
												'<button class="sui-button forminator-paypal-connect" type="button" data-nonce="' . wp_create_nonce( 'forminator_paypal_settings_modal' ) . '">' .
												'<span class="sui-loading-text">' . esc_html__( 'Connect', Forminator::DOMAIN ) . '</span>' .
												'<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>' .
												'</button>' .
												'</div>';

		wp_send_json_success( $data );
	}

	/**
	 * Dismiss notice
	 *
	 * @since 1.9
	 */
	public function dismiss_notice() {
		forminator_validate_ajax( 'forminator_dismiss_notification' );

		$notification_name = filter_input( INPUT_POST, 'prop', FILTER_SANITIZE_STRING );

		update_option( $notification_name, true );

		wp_send_json_success();
	}

	/**
	 * Dismiss notice
	 *
	 * @since 1.9
	 */
	public function later_notice() {
		forminator_validate_ajax( "forminator_dismiss_notification" );

		$notification_name = filter_input( INPUT_POST, 'prop', FILTER_SANITIZE_STRING );
		$form_id = filter_input( INPUT_POST, 'form_id', FILTER_SANITIZE_NUMBER_INT );

		update_post_meta( $form_id, $notification_name, true );

		wp_send_json_success();
	}

	/**
	 * Save general payments settings
	 *
	 * @since 1.7
	 */
	public function save_payments() {
		forminator_validate_ajax( 'forminator_save_payments_settings' );

		// stripe
		if ( isset( $_POST['stripe-default-currency'] ) && ! empty( $_POST['stripe-default-currency'] ) ) {
			$default_currency = sanitize_text_field( $_POST['stripe-default-currency'] );

			try {
				$stripe = new Forminator_Gateway_Stripe();

				$test_key    = $stripe->get_test_key();
				$test_secret = $stripe->get_test_secret();
				$live_key    = $stripe->get_live_key();
				$live_secret = $stripe->get_live_secret();

				Forminator_Gateway_Stripe::store_settings(
					array(
						'test_key'         => $test_key,
						'test_secret'      => $test_secret,
						'live_key'         => $live_key,
						'live_secret'      => $live_secret,
						'default_currency' => $default_currency,
					)
				);

			} catch ( Forminator_Gateway_Exception $e ) {
				wp_send_json_error( $e->getMessage() );
			}
		}

		// paypal
		if ( isset( $_POST['paypal-default-currency'] ) && ! empty( $_POST['paypal-default-currency'] ) ) {
			$default_currency = sanitize_text_field( $_POST['paypal-default-currency'] );

			try {
				$paypal = new Forminator_PayPal_Express();

				$sandbox_id     = $paypal->get_sandbox_id();
				$sandbox_secret = $paypal->get_sandbox_secret();
				$live_id        = $paypal->get_live_id();
				$live_secret    = $paypal->get_live_secret();

				Forminator_PayPal_Express::store_settings(
					array(
						'sandbox_id'     => $sandbox_id,
						'sandbox_secret' => $sandbox_secret,
						'live_id'        => $live_id,
						'live_secret'    => $live_secret,
						'currency'       => $default_currency,
					)
				);

			} catch ( Forminator_Gateway_Exception $e ) {
				wp_send_json_error( $e->getMessage() );
			}
		}

		wp_send_json_success();

	}

	/**
	 * Delete all poll submission
	 *
	 * @since 1.7.2
	 */
	public function delete_poll_submissions() {
		forminator_validate_ajax( 'forminatorPollEntries' );

		if ( ! empty( $_POST['id'] ) ) {
			$form_id = intval( $_POST['id'] );

			Forminator_Form_Entry_Model::delete_by_form( $form_id );

			$file = forminator_plugin_dir() . 'admin/views/poll/entries/content-none.php';

			ob_start();
			/** @noinspection PhpIncludeInspection */
			include $file;
			$html = ob_get_clean();

			$data['html']         = $html;
			$data['notification'] = array(
				'type'     => 'success',
				'text'     => __( 'All the submissions deleted successfully.', Forminator::DOMAIN ),
				'duration' => '4000',
			);
			wp_send_json_success( $data );
		} else {
			$data['notification'] = array(
				'type'     => 'error',
				'text'     => __( 'Submission delete failed.', Forminator::DOMAIN ),
				'duration' => '4000',
			);
			wp_send_json_error( $data );
		}
	}
}
