<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

//Show/Hide the field Remember Me
add_action( 'forminator_cform_render_fields', array( 'Forminator_CForm_Front_User_Login', 'render_fields' ), 11, 2 );

/**
 * Front user class for custom login forms
 *
 * @since 1.11
 */
class Forminator_CForm_Front_User_Login extends Forminator_User {

	protected $remember_cookie_number;

	protected $remember_cookie_type;

	public function __construct() {
		parent::__construct();

		$this->remember_cookie_number = 14;
		$this->remember_cookie_type = DAY_IN_SECONDS;

		add_filter( 'auth_cookie_expiration', array( $this, 'change_cookie_expiration' ), 10, 3 );
	}

	/**
	 * Change cookie expiration
	 *
	 * @param int $expiration
	 * @param int $user_id
	 * @param bool $remember
	 *
	 * @return int
	 */
	public function change_cookie_expiration( $expiration, $user_id, $remember ) {
		if ( $remember ) {
			$expiration = $this->remember_cookie_number * $this->remember_cookie_type;
		}

		return $expiration;
	}

	/**
	 * Is "Remember Me" submitted?
	 *
	 * @param array $submitted_data
	 *
	 * @return bool
	 */
	private function is_submitted_remember_me( $submitted_data ) {
		$submitted_remember_me = false;
		foreach ( $submitted_data as $field_key => $field_val ) {
			if ( false !== stripos( $field_key, 'checkbox-' ) && 'remember-me' === $field_val[0] ) {
				$submitted_remember_me = true;
				break;
			}
		}

		return $submitted_remember_me;
	}

	/**
	 * Process login
	 *
	 * @param $custom_form
	 * @param $submitted_data
	 * @param Forminator_Form_Entry_Model $entry
	 * @param $field_data_array
	 *
	 * @return array
	 */
	public function process_login( $custom_form, $submitted_data, Forminator_Form_Entry_Model $entry, $field_data_array ) {
		$settings = $custom_form->settings;
		//Field username
		$response = array();
		$username = '';
		if ( isset( $settings['login-username-field'] ) && ! empty( $settings['login-username-field'] ) ) {
			$username = $this->replace_value( $field_data_array, $settings['login-username-field'] );
		}
		$username = apply_filters( 'forminator_custom_form_login_username_before_signon', $username, $custom_form, $submitted_data, $entry );

		//Field password
		$password = '';
		if ( isset( $settings['login-password-field'] ) && ! empty( $settings['login-password-field'] ) ) {
			$password = $this->replace_value( $field_data_array, $settings['login-password-field'] );
		}
		$password = apply_filters( 'forminator_custom_form_login_password_before_signon', $password, $custom_form, $submitted_data, $entry );
		$submitted_remember_me = $this->is_submitted_remember_me( $submitted_data );

		if ( $submitted_remember_me && isset( $settings['remember-me'] ) && 'true' === $settings['remember-me'] ) {
			$remember = true;

			if ( isset( $settings['remember-me-cookie-type'] ) ) {

				switch ( $settings['remember-me-cookie-type'] ) {
					case 'weeks':
						$this->remember_cookie_type = WEEK_IN_SECONDS;
						break;

					case 'months':
						$this->remember_cookie_type = MONTH_IN_SECONDS;
						break;

					case 'years':
						$this->remember_cookie_type = YEAR_IN_SECONDS;
						break;

					case 'days':
					default:
						$this->remember_cookie_type = DAY_IN_SECONDS;
						break;
				}
			} else {
				$this->remember_cookie_type = DAY_IN_SECONDS;
			}

			$this->remember_cookie_number = isset( $settings['remember-me-cookie-number'] ) ? (int)$settings['remember-me-cookie-number'] : $this->remember_cookie_number;

		} else {
			$remember = false;
		}
		$remember = apply_filters( 'forminator_custom_form_login_remember_before_signon', $remember, $custom_form, $entry );

		if ( function_exists( 'wp_defender' ) ) {
			$sign_on = wp_authenticate( $username, $password );
			$token = uniqid();
			// create and store a login token so we can query this user again
			update_user_meta( $sign_on->ID, 'defOTPLoginToken', $token );
			$response['lost_url'] = admin_url( 'admin-ajax.php?action=defRetrieveOTP&token=' . $token . '&nonce=' . wp_create_nonce( 'defRetrieveOTP' ) );
			if ( ! isset( $submitted_data['auth-code'] ) ) {
				if ( ! is_wp_error( $sign_on ) && \WP_Defender\Module\Advanced_Tools\Component\Auth_API::isUserEnableOTP( $sign_on->ID ) ) {
					$response['authentication'] = 'show';
					$response['user']           = $sign_on;

					return $response;
				}
			}
			if ( isset( $submitted_data['auth-code'] ) ) {
				$secret = \WP_Defender\Module\Advanced_Tools\Component\Auth_API::getUserSecret( $sign_on->ID );
				$valid  = \WP_Defender\Module\Advanced_Tools\Component\Auth_API::compare( $secret, $submitted_data['auth-code'] );
				if ( $valid ) {
					$response['authentication'] = 'valid';
				} else {
					$response['authentication'] = 'invalid';
					$response['user']           = $sign_on;

					return $response;
				}
			}
		}
		$user_fields = array(
			'user_login'    => $username,
			'user_password' => $password,
			'remember'      => $remember
		);

		$sign_on = wp_signon( $user_fields );

		$response['authentication'] = '';
		$response['user']           = $sign_on;

		return $response;
	}

	/**
	 * Get element ID for "Remember Me". There may be several checkboxes in the form.
	 * "Remember Me" is the last form field. Before the submit button.
	 *
	 * @param Forminator_Custom_Form_Model $custom_form
	 *
	 * @return int
	 */
	public static function get_element_id_for_remember_me( $custom_form ) {
		$id = 1;
		$last_id = 0;
		if ( is_object( $custom_form ) ) {
			$fields = $custom_form->get_fields();
			foreach ( $fields as $field ) {
				$field_array = $field->to_formatted_array();
				$field_type  = $field_array['type'];
				if ( 'checkbox' === $field_type ) {
					$last_id = Forminator_Field::get_property( 'element_id', $field_array );
					$last_id = (int) str_replace( 'checkbox-', '', $last_id );
				}
			}
			$id = $last_id + 1;
		}

		return $id;
	}

	/**
	 * Show/Hide the field Remember Me
	 *
	 * @param array $wrappers
	 * @param int $id
	 *
	 * @return array
	 */
	public static function render_fields( $wrappers, $id ) {
		$custom_form = Forminator_Custom_Form_Model::model()->load( $id );

		if ( isset( $custom_form->settings['form-type'] )
			&& 'login' === $custom_form->settings['form-type']
			&& isset( $custom_form->settings['remember-me'] )
			&& 'true' === $custom_form->settings['remember-me']
			&& ! empty( $wrappers )
		) {
			$id = self::get_element_id_for_remember_me( $custom_form );

			if ( isset( $custom_form->settings['remember-me-label'] ) && ! empty( $custom_form->settings['remember-me-label'] ) ) {
				$label = trim( $custom_form->settings['remember-me-label'] );
			} else {
				$label = __( 'Remember Me', Forminator::DOMAIN );
			}

			$new_wrappers = array(
				'wrapper_id' => 'wrapper-1511347711918-2169',
				'fields'     => array(
					array(
						'element_id'   => 'checkbox-'. $id,
						'type'         => 'checkbox',
						'options'      => array(
							array(
								'label'   => $label,
								'value'   => 'remember-me',
								'default' => false
							),
						),
						'cols'         => 12,
						'wrapper_id'   => 'wrapper-8730-999',
						'value_type'   => 'checkbox',
						'field_label'  => '',
						'layout'       => 'vertical',
						'custom-class' => 'remember-me',
					)
				)
			);

			array_push($wrappers, $new_wrappers );
		}

		return $wrappers;
	}
}
