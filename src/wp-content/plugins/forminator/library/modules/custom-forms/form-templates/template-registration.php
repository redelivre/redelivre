<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Template_Contact_Form
 *
 * @since 1.0
 */
class Forminator_Template_Registration extends Forminator_Template {

	/**
	 * Template defaults
	 *
	 * @since 1.0
	 * @return array
	 */
	public function defaults() {
		return array(
			'id'          => 'registration',
			'name'        => __( 'Registration', Forminator::DOMAIN ),
			'description' => __( "A simple contact form for your users to contact you", Forminator::DOMAIN ),
			'icon'        => 'profile-male',
			'priortiy'    => 5,
		);
	}

	/**
	 * Template fields
	 *
	 * @since 1.0
	 * @return array
	 */
	public function fields() {
		return array(
			array(
				'wrapper_id' => 'wrapper-1511347711918-1669',
				'fields'     => array(
					array(
						'element_id'        => 'text-1',
						'type'              => 'text',
						'cols'              => '12',
						'required'          => 'true',
						'field_label'       => __( 'Username', Forminator::DOMAIN ),
						'placeholder'       => 'Enter username',
					),
				),
			),
			array(
				'wrapper_id' => 'wrapper-1511789211918-1741',
				'fields'     => array(
					array(
						'element_id'        => 'email-1',
						'type'              => 'email',
						'cols'              => '12',
						'required'          => 'true',
						'required_message'  => __( 'This field is required. Please enter email', Forminator::DOMAIN ),
						'field_label'       => __( 'Email', Forminator::DOMAIN ),
						'placeholder'       => __( 'E.g. john@doe.com', Forminator::DOMAIN ),
						'validation'        => 'true',
						'validation_message'=> __( 'This is not a valid email', Forminator::DOMAIN ),
					),
				),
			),
			array(
				'wrapper_id' => 'wrapper-1511347712118-1739',
				'fields'     => array(
					array(
						'element_id'                   => 'password-1',
						'type'                         => 'password',
						'cols'                         => '12',
						'required'                     => 'true',
						'required_message'             => __( 'Your password is required', Forminator::DOMAIN ),
						'field_label'                  => __( 'Password', Forminator::DOMAIN ),
						'placeholder'                  => __( 'Enter your password', Forminator::DOMAIN ),
						'description'                  => '',
						'confirm-password-label'       => __( 'Confirm Password', Forminator::DOMAIN ),
						'confirm-password-placeholder' => __( 'Confirm new password', Forminator::DOMAIN ),
						'strength'                     => 'none',
						'strength_validation_message'  => __( 'Your password doesn\'t meet the minimum strength requirement. We recommend using 8 or more characters with a mix of letters, numbers & symbols.', Forminator::DOMAIN ),
						'validation'                   => 'true',
						'validation_message'           => __( 'Your passwords don\'t match', Forminator::DOMAIN ),
						'required_confirm_message'     => __( 'You must confirm your chosen password', Forminator::DOMAIN ),
					),
				),
			),
		);
	}

	/**
	 * Template settings
	 *
	 * @since 1.0
	 * @return array
	 */
	public function settings() {
		global $wp_rewrite;

		$login_url = is_null( $wp_rewrite ) ? site_url( 'wp-login.php', 'login' ) : wp_login_url();
		$pages     = $this->get_pages();

		return array(
			"form-type"                        => "registration",
			"submission-behaviour"             => "behaviour-thankyou",
			"thankyou-message"                 => sprintf( __( 'Account registration successful. Click <a href="%s">here</a> to login to your account.', Forminator::DOMAIN ), $login_url ),
			"email-thankyou-message"           => __( 'Account registration successful. Please check your email inbox to activate your new account.', Forminator::DOMAIN ),
			"manual-thankyou-message"          => __( 'Account registration successful. A website admin must approve your account before you can log in. You’ll receive an email when your account is activated.', Forminator::DOMAIN ),
			'submitData'                       => array(
				"custom-submit-text"          => __( "Register", Forminator::DOMAIN ),
				"custom-invalid-form-message" => __( "Error: Your form is not valid, please fix the errors!", Forminator::DOMAIN ),
			),
			'enable-ajax'                      => 'true',
			'validation-inline'                => true,
			'fields-style'                     => 'open',
			"form-expire"                      => 'no_expire',
			'use-admin-email'                  => 'true',
			// Main container
			'form-padding-top'                 => '0',
			'form-padding-right'               => '0',
			'form-padding-bottom'              => '0',
			'form-padding-left'                => '0',
			'form-border-width'                => '0',
			'form-border-style'                => 'none',
			'form-border-radius'               => '0',
			// Typography - Label
			'cform-label-font-family'          => 'Roboto',
			'cform-label-custom-family'        => '',
			'cform-label-font-size'            => '12',
			'cform-label-font-weight'          => 'bold',
			// Typography - Section Title
			'cform-title-font-family'          => 'Roboto',
			'cform-title-custom-family'        => '',
			'cform-title-font-size'            => '45',
			'cform-title-font-weight'          => 'normal',
			'cform-title-text-align'           => 'left',
			// Typography - Section Subtitle
			'cform-subtitle-font-family'       => 'Roboto',
			'cform-subtitle-custom-font'       => '',
			'cform-subtitle-font-size'         => '18',
			'cform-subtitle-font-weight'       => 'normal',
			'cform-subtitle-text-align'        => 'left',
			// Typography - Input & Textarea
			'cform-input-font-family'          => 'Roboto',
			'cform-input-custom-font'          => '',
			'cform-input-font-size'            => '16',
			'cform-input-font-weight'          => 'normal',
			// Typography - Radio & Checkbox
			'cform-radio-font-family'          => 'Roboto',
			'cform-radio-custom-font'          => '',
			'cform-radio-font-size'            => '14',
			'cform-radio-font-weight'          => 'normal',
			// Typography - Select
			'cform-select-font-family'         => 'Roboto',
			'cform-select-custom-family'       => '',
			'cform-select-font-size'           => '16',
			'cform-select-font-weight'         => 'normal',
			// Typography - Multi Select
			'cform-multiselect-font-family'    => 'Roboto',
			'cform-multiselect-custom-font'    => '',
			'cform-multiselect-font-size'      => '16',
			'cform-multiselect-font-weight'    => 'normal',
			// Typography - Dropdown
			'cform-dropdown-font-family'       => 'Roboto',
			'cform-dropdown-custom-font'       => '',
			'cform-dropdown-font-size'         => '16',
			'cform-dropdown-font-weight'       => 'normal',
			// Typography - Calendar
			'cform-calendar-font-family'       => 'Roboto',
			'cform-calendar-custom-font'       => '',
			'cform-calendar-font-size'         => '13',
			'cform-calendar-font-weight'       => 'normal',
			// Typography - Buttons
			'cform-button-font-family'         => 'Roboto',
			'cform-button-custom-font'         => '',
			'cform-button-font-size'           => '14',
			'cform-button-font-weight'         => '500',
			// Typography - Timeline
			'cform-timeline-font-family'       => 'Roboto',
			'cform-timeline-custom-font'       => '',
			'cform-timeline-font-size'         => '12',
			'cform-timeline-font-weight'       => 'normal',
			// Typography - Pagination
			'cform-pagination-font-family'     => '',
			'cform-pagination-custom-font'     => '',
			'cform-pagination-font-size'       => '16',
			'cform-pagination-font-weight'     => 'normal',
			'payment_require_ssl'              => 'true,',
			'submission-file'                  => 'delete',
			'options'                          => array(),
			// Site Registration
			'site-registration'                => 'enable',
			'site-registration-name-field'     => 'text-1',
			'site-registration-title-field'    => 'text-1',
			'site-registration-role-field'     => 'administrator',
			// Activation Method
			'activation-method'                => 'default',
			'activation-email'                 => 'default',
			// Default Meta Keys
			'registration-username-field'      => 'text-1',
			'registration-email-field'         => 'email-1',
			'registration-password-field'      => 'password-1',
			'registration-role-field'          => 'subscriber',
			//Redirected page for Email-activation method
			'confirmation-page'                => ! empty( $pages ) ? $pages[0]->ID : '',
			// Additional settings
			'automatic-login'                  => false,
			'hide-registration-form'           => true,
			'hidden-registration-form-message' => '',
			'autoclose'                        => false,
		);
	}

	/**
	 * Return published pages
	 *
	 * @since 1.11
	 *
	 * @return mixed
	 */
	private function get_pages() {
		$args = array(
			'sort_order' => 'DESC',
			'sort_column' => 'ID',
			'hierarchical' => 1,
			'exclude' => '',
			'include' => '',
			'meta_key' => '',
			'meta_value' => '',
			'authors' => '',
			'child_of' => 0,
			'parent' => -1,
			'exclude_tree' => '',
			'number' => '',
			'offset' => 0,
			'post_type' => 'page',
			'post_status' => 'publish'
		);

		$pages = get_pages($args);

		return $pages;
	}
}
