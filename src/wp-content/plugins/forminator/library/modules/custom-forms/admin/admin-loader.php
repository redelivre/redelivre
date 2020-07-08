<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Custom_Form_Admin
 *
 * @property Forminator_Custom_Form module
 * @since 1.0
 */
class Forminator_Custom_Form_Admin extends Forminator_Admin_Module {

	/**
	 * Init module admin
	 *
	 * @since 1.0
	 */
	public function init() {
		$this->module       = Forminator_Custom_Form::get_instance();
		$this->page         = 'forminator-cform';
		$this->page_edit    = 'forminator-cform-wizard';
		$this->page_entries = 'forminator-cform-view';
	}

	/**
	 * Include required files
	 *
	 * @since 1.0
	 */
	public function includes() {
		include_once dirname( __FILE__ ) . '/admin-page-new.php';
		include_once dirname( __FILE__ ) . '/admin-page-view.php';
		include_once dirname( __FILE__ ) . '/admin-page-entries.php';
		include_once dirname( __FILE__ ) . '/admin-renderer-entries.php';
	}

	/**
	 * Add module pages to Admin
	 *
	 * @since 1.0
	 */
	public function add_menu_pages() {
		new Forminator_CForm_Page( $this->page, 'custom-form/list', __( 'Forms', Forminator::DOMAIN ), __( 'Forms', Forminator::DOMAIN ), 'forminator' );
		new Forminator_CForm_New_Page( $this->page_edit, 'custom-form/wizard', __( 'Edit Form', Forminator::DOMAIN ), __( 'New Custom Form', Forminator::DOMAIN ), 'forminator' );
		new Forminator_CForm_View_Page( $this->page_entries, 'custom-form/entries', __( 'Submissions:', Forminator::DOMAIN ), __( 'View Custom Form', Forminator::DOMAIN ), 'forminator' );
	}

	/**
	 * Remove necessary pages from menu
	 *
	 * @since 1.0
	 */
	public function hide_menu_pages() {
		remove_submenu_page( 'forminator', $this->page_edit );
		remove_submenu_page( 'forminator', $this->page_entries );
	}

	/**
	 * Pass module defaults to JS
	 *
	 * @since 1.0
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function add_js_defaults( $data ) {
		$model = null;
		if ( $this->is_admin_wizard() ) {
			$data['application'] = 'builder';

			if ( ! self::is_edit() ) {
				$data['formNonce'] = wp_create_nonce( 'forminator_save_builder_fields' );
				// Load settings from template
				$template = $this->get_template();
				$name     = '';
				if ( isset( $_GET['name'] ) ) { // WPCS: CSRF ok.
					$name = sanitize_text_field( $_GET['name'] );
				}
				if ( isset( $template->settings['form-type'] ) && in_array( $template->settings['form-type'], array( 'registration', 'login' ) ) ) {
					$notifications = 'registration' === $template->settings['form-type']
						? $this->get_registration_form_notifications( $model, $template )
						: array();
				} else {
					$notifications = $this->get_form_notifications( $model );
				}

				if ( $template ) {
					$data['currentForm'] = array(
						'wrappers'      => $template->fields,
						'settings'      => array_merge(
							array(
								'formName'             => $name,
								'pagination-header'    => 'nav',
								'version'              => FORMINATOR_VERSION,
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
							),
							$template->settings
						),
						'notifications' => $notifications,
					);
				} else {
					$data['currentForm'] = array(
						'fields'   => array(),
						'settings' => array_merge(
							array( 'formName' => $name ),
							array(
								'pagination-header' => 'nav',
								'version'           => FORMINATOR_VERSION,
								'form-padding'      => 'none',
								'form-border'       => 'none',
								'fields-style'      => 'open',
								'form-style'        => 'default',
								'paginationData'    => array(
									'pagination-header-design' => 'show',
									'pagination-header'        => 'nav',
								),
							)
						),
					);
				}
			} else {
				$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null;
				if ( ! is_null( $id ) ) {
					$data['formNonce'] = wp_create_nonce( 'forminator_save_builder_fields' );
					$model             = Forminator_Custom_Form_Model::model()->load( $id );
				}
				$wrappers = array();
				if ( is_object( $model ) ) {
					$wrappers = $model->get_fields_grouped();
				}

				// Load stored record
				$settings = apply_filters( 'forminator_form_settings', $this->get_form_settings( $model ), $model, $data, $this );

				if ( isset( $model->settings['form-type'] ) && 'registration' === $model->settings['form-type'] ) {
					$notifications = $this->get_registration_form_notifications( $model );
				} else {
					$notifications = $this->get_form_notifications( $model );
				}
				$notifications = apply_filters( 'forminator_form_notifications', $notifications, $model, $data, $this );
				$data['currentForm'] = array(
					'wrappers'      => $wrappers,
					'settings'      => array_merge(
						array(
							'pagination-header' => 'nav',
							'paginationData'    => array(
								'pagination-header-design' => 'show',
								'pagination-header'        => 'nav',
							),
						),
						$settings,
						array(
							'form_id'     => $model->id,
							'form_name'   => $model->name,
							'form_status' => $model->status,
						)
					),
					'notifications' => $notifications,
				);
			}
		}

		$data['modules']['custom_form'] = array(
			'templates'     => $this->module->get_templates(),
			'new_form_url'  => menu_page_url( $this->page_edit, false ),
			'form_list_url' => menu_page_url( $this->page, false ),
			'preview_nonce' => wp_create_nonce( 'forminator_popup_preview_cforms' ),
		);

		return apply_filters( 'forminator_form_admin_data', $data, $model, $this );
	}

	/**
	 * Localize module
	 *
	 * @since 1.0
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function add_l10n_strings( $data ) {
		$data['custom_form'] = array(
			'popup_label' => __( 'Choose Form Type', Forminator::DOMAIN ),
		);

		$data['builder'] = array(
			"save" => __( "Save", Forminator::DOMAIN ),
		);

		$data['product'] = array(
			"add_variations" => __( "Add some variations of your product.", Forminator::DOMAIN ),
			"use_list"       => __( "Display in list?", Forminator::DOMAIN ),
			"add_variation"  => __( "Add Variation", Forminator::DOMAIN ),
			"image"          => __( "Image", Forminator::DOMAIN ),
			"name"           => __( "Name", Forminator::DOMAIN ),
			"price"          => __( "Price", Forminator::DOMAIN ),
		);

		$data['appearance'] = array(
			"customize_typography"        => __( "Customize typography", Forminator::DOMAIN ),
			"custom_font_family"          => __( "Enter custom font family name", Forminator::DOMAIN ),
			"custom_font_placeholder"     => __( "E.g. 'Arial', sans-serif", Forminator::DOMAIN ),
			"custom_font_description"     => __( "Type the font family name, as you would in CSS", Forminator::DOMAIN ),
			"font_family"                 => __( "Font family", Forminator::DOMAIN ),
			"font_size"                   => __( "Font size", Forminator::DOMAIN ),
			"font_weight"                 => __( "Font weight", Forminator::DOMAIN ),
			"select_font"                 => __( "Select font", Forminator::DOMAIN ),
			"custom_font"                 => __( "Custom user font", Forminator::DOMAIN ),
			"minutes"                     => __( "minute(s)", Forminator::DOMAIN ),
			"hours"                       => __( "hour(s)", Forminator::DOMAIN ),
			"days"                        => __( "day(s)", Forminator::DOMAIN ),
			"weeks"                       => __( "week(s)", Forminator::DOMAIN ),
			"months"                      => __( "month(s)", Forminator::DOMAIN ),
			"years"                       => __( "year(s)", Forminator::DOMAIN ),
		);

		$data['tab_appearance'] = array(
			"basic_selectors"                => __( "Basic selectors", Forminator::DOMAIN ),
			"advanced_selectors"             => __( "Advanced selectors", Forminator::DOMAIN ),
			"pagination_selectors"           => __( "Pagination selectors", Forminator::DOMAIN ),
		);

		return $data;
	}

	/**
	 * Return template
	 *
	 * @since 1.0
	 * @return Forminator_Template|false
	 */
	private function get_template() {
		if( isset( $_GET['template'] ) )  {
			$id = trim( sanitize_text_field( $_GET['template'] ) );
		} else {
			$id = 'blank';
		}

		foreach ( $this->module->templates as $key => $template ) {
			if ( $template->options['id'] === $id ) {
				return $template;
			}
		}

		return false;
	}

	/**
	 * Return Form Settins
	 *
	 * @since 1.1
	 *
	 * @param Forminator_Custom_Form_Model $form
	 *
	 * @return mixed
	 */
	public function get_form_settings( $form ) {
		// If not using the new "submission-behaviour" setting, set it according to the previous settings
		if ( ! isset( $form->settings['submission-behaviour'] ) ) {
			$redirect = ( isset( $form->settings['redirect'] ) && filter_var( $form->settings['redirect'], FILTER_VALIDATE_BOOLEAN ) );
			$thankyou = ( isset( $form->settings['thankyou'] ) && filter_var( $form->settings['thankyou'], FILTER_VALIDATE_BOOLEAN ) );

			if ( ! $redirect && ! $thankyou ) {
				$form->settings['submission-behaviour'] = 'behaviour-thankyou';
			} elseif ( $thankyou ) {
				$form->settings['submission-behaviour'] = 'behaviour-thankyou';
			} elseif ( $redirect ) {
				$form->settings['submission-behaviour'] = 'behaviour-redirect';
			}
		}

		if ( $this->has_stripe_or_paypal( $form ) && $this->is_ajax_submit( $form ) ) {
			if ( isset( $form->settings['submission-behaviour'] ) && "behaviour-thankyou" === $form->settings['submission-behaviour'] ) {
				$form->settings['submission-behaviour'] = 'behaviour-hide';
			}
		}

		return $form->settings;
	}

	/**
	 * Return Form notifications
	 *
	 * @since 1.1
	 *
	 * @param Forminator_Custom_Form_Model|null $form
	 *
	 * @return mixed
	 */
	public function get_form_notifications( $form ) {
		if ( ! isset( $form ) || ! isset( $form->notifications ) ) {
			return array(
				array(
					'slug'             => 'notification-1234-4567',
					'label'            => 'Admin Email',
					'email-recipients' => 'default',
					'recipients'       => get_option( 'admin_email' ),
					'email-subject'    => __( "New Form Entry #{submission_id} for {form_name}", Forminator::DOMAIN ),
					'email-editor'     => __( "You have a new website form submission: <br/> {all_fields} <br/>---<br/> This message was sent from {site_url}.", Forminator::DOMAIN ),
				)
			);
		}

		return $form->notifications;
	}

	/**
	 * Get Registration Form notifications
	 *
	 * @since 1.11
	 *
	 * @param Forminator_Custom_Form_Model|null $form
	 * @param Forminator_Template|null          $template
	 *
	 * @return mixed
	 */
	public function get_registration_form_notifications( $form, $template = null ) {
		if ( ! isset( $form ) || ! isset( $form->notifications ) ) {
			$msg_footer = __( 'This message was sent from {site_url}', Forminator::DOMAIN );
			//For admin
			$message = __( "New user registration on your site {site_url}: <br/><br/> {all_fields} <br/><br/> Click {submission_url} to view the submission.<br/>", Forminator::DOMAIN );
			$message .= "<br/>---<br/>";
			$message .= $msg_footer;

			$message_method_email = $message;

			$message_method_manual = __( "New user registration on your site {site_url}: <br/><br/> {all_fields} <br/><br/> The account is still not activated and needs your approval. To activate this account, click the link below.", Forminator::DOMAIN );
			$message_method_manual .= "<br/>{account_approval_link} <br/><br/>";
			$message_method_manual .= __( "Click {submission_url} to view the submission on your website's dashboard.<br/><br/>", Forminator::DOMAIN );
			$message_method_manual .= $msg_footer;

			$notifications[] = array(
				'slug'             => 'notification-1111-1111',
				'label'            => __( 'Admin Email', Forminator::DOMAIN ),
				'email-recipients' => 'default',
				'recipients'       => get_option( 'admin_email' ),
				'email-subject'    => __( 'New User Registration on {site_url}', Forminator::DOMAIN ),
				'email-editor'     => $message,

				'email-subject-method-email'  => __( 'New User Registration on {site_url}', Forminator::DOMAIN ),
				'email-editor-method-email'   => $message_method_email,
				'email-subject-method-manual' => __( 'New User Registration on {site_url} needs approval.', Forminator::DOMAIN ),
				'email-editor-method-manual'  => $message_method_manual,
			);
			if ( ! is_null( $template )) {
				$email = $this->get_registration_form_customer_email_slug( $template );
			} else {
				$email = $this->get_registration_form_customer_email_slug( $form );
			}
			//For customer
			$message  = __( "Your new account on our site {site_title} is ready to go. Here's your details: <br/><br/> {all_fields} <br/><br/>", Forminator::DOMAIN );
			$message .= sprintf( __( 'Login to your new account <a href="%s">here</a>.', Forminator::DOMAIN ), wp_login_url() );
			$message .= "<br/><br/>---<br/>";
			$message .= $msg_footer;

			$message_method_email = __( "Dear {username} <br/><br/>", Forminator::DOMAIN );
			$message_method_email .= __( 'Thank you for signing up on our website. You are one step away from activating your account. ', Forminator::DOMAIN );
			$message_method_email .= __( "We have sent you another email containing a confirmation link. Please click on that link to activate your account.<br/><br/>", Forminator::DOMAIN );
			$message_method_email .= $msg_footer;

			$message_method_manual = __( "Your new account on {site_title} is under review.<br/>", Forminator::DOMAIN );
			$message_method_manual .= __( "You'll receive another email once the site admin approves your account. You should be able to login into your account after that.", Forminator::DOMAIN );
			$message_method_manual .= "<br/><br/>---<br/>";
			$message_method_manual .= $msg_footer;

			$notifications[] = array(
				'slug'             => 'notification-1111-1112',
				'label'            => __( 'User Confirmation Email', Forminator::DOMAIN ),
				'email-recipients' => 'default',
				'recipients'       => $email,
				'email-subject'    => __( 'Your new account on {site_title}', Forminator::DOMAIN ),
				'email-editor'     => $message,

				'email-subject-method-email'  => __( 'Activate your account on {site_url}', Forminator::DOMAIN ),
				'email-editor-method-email'   => $message_method_email,
				'email-subject-method-manual' => __( 'Your new account on {site_title} is under review.', Forminator::DOMAIN ),
				'email-editor-method-manual'  => $message_method_manual,
			);

			return $notifications;
		}

		return $form->notifications;
	}

	/**
	 * Get customer email as field slug
	 *
	 * @since 1.11
	 *
	 * @param Forminator_Custom_Form_Model|Forminator_Template $form
	 * @param string                                           $default
	 *
	 * @return string
	 */
	public function get_registration_form_customer_email_slug( $form, $default = '{email-1}' ) {
		if ( isset( $form->settings['registration-email-field'] ) && ! empty( $form->settings['registration-email-field'] ) ) {
			$email = $form->settings['registration-email-field'];
			if ( false === strpos( $email, '{' ) ) {
				$email = '{' . $email . '}';
			}

			return $email;
		}

		return $default;
	}

	/*
	 * Check if form has stripe or paypal field
	 *
	 * @since 1.9.3
	 * @return bool
	 */
	public function has_stripe_or_paypal( $form ) {
		$fields = $form->fields;

		foreach ( $fields as $field ) {
			$field = $field->to_formatted_array();
			if ( isset( $field['type'] ) && ( 'stripe' === $field['type'] || 'paypal' === $field['type'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if submit is handled with AJAX
	 *
	 * @since 1.9.3
	 *
	 * @return bool
	 */
	public function is_ajax_submit( $form ) {
		$form_settings  = $form->settings;

		if ( ! isset( $form_settings['enable-ajax'] ) || empty( $form_settings['enable-ajax'] ) ) {
			return false;
		}

		return filter_var( $form_settings['enable-ajax'], FILTER_VALIDATE_BOOLEAN );
	}
}
