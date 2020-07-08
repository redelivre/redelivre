<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Front user class for custom registration forms
 *
 * @since 1.11
 */
class Forminator_CForm_Front_User_Registration extends Forminator_User {

	private $user_data = array();
	private $mail_sender;

	public function __construct() {
		parent::__construct();

		$this->mail_sender = new Forminator_CForm_Front_Mail();

		if ( is_multisite() ) {
			add_action( 'forminator_cform_user_registration_validation', array( $this, 'multisite_validation' ), 10, 4 );
			add_action( 'forminator_cform_user_registered', array( $this, 'create_site' ), 10, 5 );
		}
		add_filter( 'forminator_custom_registration_form_errors', array( $this, 'submit_errors' ), 11, 3 );
		//Change text of thankyou-message for other activation methods: 'email' && 'manual'
		add_filter( 'forminator_custom_form_thankyou_message', array( $this, 'change_thankyou_message' ), 11, 3 );
		//Change value of a field that is not saved in DB
		add_filter( 'forminator_custom_form_after_render_value', array( $this, 'change_field_value' ), 11, 4 );
	}

	/**
	 * Change submitted data
	 *
	 * @param string $value
	 * @param array $custom_form
	 * @param string $column_name
	 * @param array $data
	 *
	 * @return string
	 */
	public function change_field_value( $value, $custom_form, $column_name, $data ) {
		if ( ! $value
			&& isset( $custom_form->settings['form-type'] )
			&& 'registration' === $custom_form->settings['form-type']
			&& isset( $column_name )
		) {
			$value = isset( $data[ $column_name ] ) ? $data[ $column_name ] : '';
		}

		return $value;
	}

	/**
	 * Check activation method
	 *
	 * @param string $method
	 *
	 * @return bool
	 */
	private function check_activation_method( $method ) {
		return in_array( $method, array( 'email', 'manual' ) );// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
	}

	/**
	 * Show submit_errors
	 *
	 * @param string $submit_errors
	 * @param int $form_id
	 * @param array $field_data_array
	 *
	 * @return bool|string
	 */
	public function submit_errors( $submit_errors, $form_id, $field_data_array ) {
		$custom_form = Forminator_Custom_Form_Model::model()->load( $form_id );
		$settings    = $custom_form->settings;

		$username   = '';
		$user_email = '';
		if ( isset( $settings['registration-username-field'] ) && ! empty( $settings['registration-username-field'] ) ) {
			$username = $this->replace_value( $field_data_array, $settings['registration-username-field'] );
		}
		if ( isset( $settings['registration-email-field'] ) && ! empty( $settings['registration-email-field'] ) ) {
			$user_email = $this->replace_value( $field_data_array, $settings['registration-email-field'] );
		}

		// Additional processing of multisite installs
		if ( is_multisite() ) {
			// Convert username to lowercase
			$username = strtolower( $username );

			$result = wpmu_validate_user_signup( $username, $user_email );
			$errors = $result['errors']->errors;

			// Check if there are any errors
			if ( ! empty( $errors ) ) {
				foreach ( $errors as $type => $error_msgs ) {
					foreach ( $error_msgs as $error_msg ) {
						// Depending on the error type, display a different validation error.
						switch ( $type ) {
							case 'user_name':
							case 'user_email':
								$submit_errors = $error_msg;
								break;
							default:
								break;
						}
					}
				}

				return $submit_errors;
			}
		}

		return true;
	}

	/**
	 * Change submitted data
	 *
	 * @param array $submitted_data
	 * @param string $new_value
	 *
	 * @return array
	 */
	public function change_submitted_data( $submitted_data, $new_value ) {
		foreach ( $submitted_data as $field_key => $field_value ) {
			if ( false !== stripos( $field_key, 'password-' ) ) {
				$submitted_data[ $field_key ] = $new_value;
			}
		}

		return $submitted_data;
	}

	/**
	 * Handle activation user
	 *
	 * @param array $user_data
	 * @param array $custom_form
	 * @param Forminator_Form_Entry_Model $entry
	 * @param array $submitted_data
	 *
	 * @return bool|void
	 */
	public function handle_user_activation( $user_data, $custom_form, $entry, $submitted_data ) {
		global $wpdb;

		require_once __DIR__ . '/../user/class-forminator-cform-user-signups.php';

		Forminator_CForm_User_Signups::prep_signups_functionality();

		$settings = $custom_form->settings;
		//For password security
		$prepare_user_data              = $user_data;
		$encrypted_password             = self::openssl_encrypt( $user_data['user_pass'] );
		$prepare_user_data['user_pass'] = $encrypted_password;
		$prepare_submitted_data         = $this->change_submitted_data( $submitted_data, $encrypted_password );

		$meta = array(
			'form_id'        => $entry->form_id,
			'entry_id'       => $entry->entry_id,
			'submitted_data' => $prepare_submitted_data,
			'user_data'      => $prepare_user_data,
		);

		//Change default text of notifications for other activation methods: 'email' && 'manual'
		if ( ! empty( $custom_form->notifications ) ) {
			$custom_form->notifications = $this->change_notifications( $settings['activation-method'], $custom_form->notifications );
		}
		//Sending notifications before saving activation_key
		if ( 'email' === $settings['activation-method'] ) {
			$this->mail_sender->process_mail( $custom_form, $submitted_data, $entry );
		}

		$option_create_site = forminator_get_property( $settings, 'site-registration' );
		if ( is_multisite()
			&& isset( $option_create_site )
			&& 'enable' !== $option_create_site
			&& $site_data = $this->get_site_data( $settings, 0, $submitted_data, $user_data )
		) {
			if ( ! has_action( 'after_signup_site', 'wpmu_signup_blog_notification' ) ) {
				add_action( 'after_signup_site', 'wpmu_signup_blog_notification', 10, 7 );
			}

			wpmu_signup_blog( $site_data['domain'], $site_data['path'], $site_data['title'], $user_data['user_login'], $user_data['user_email'], $meta );
		} else {
			$user_data['user_login'] = preg_replace( '/\s+/', '', sanitize_user( $user_data['user_login'], true ) );

			if ( ! has_action( 'after_signup_user', 'wpmu_signup_user_notification' ) ) {
				add_action( 'after_signup_user', 'wpmu_signup_user_notification', 10, 4 );
			}

			wpmu_signup_user( $user_data['user_login'], $user_data['user_email'], $meta );
		}
		$sql            = $wpdb->prepare( "SELECT activation_key FROM {$wpdb->signups} WHERE user_login = %s ORDER BY registered DESC LIMIT 1", $user_data['user_login'] );
		$activation_key = $wpdb->get_var( $sql );

		// used for filtering on activation listing UI
		Forminator_CForm_User_Signups::add_signup_meta( $entry, 'activation_method', $settings['activation-method'] );
		Forminator_CForm_User_Signups::add_signup_meta( $entry, 'activation_key', $activation_key );

		//Sending notifications with {account_approval_link} after saving activation_key
		if ( 'manual' === $settings['activation-method'] ) {
			$this->mail_sender->process_mail( $custom_form, $submitted_data, $entry );
		}

		return true;
	}

	/**
	 * Create user
	 *
	 * @param array $new_user_data
	 * @param array $custom_form
	 * @param Forminator_Form_Entry_Model $entry
	 * @param array $submitted_data
	 * @param bool $is_user_signon
	 *
	 * @return int|string|void|WP_Error
	 */
	public function create_user( $new_user_data, $custom_form, $entry, $submitted_data, $is_user_signon = false ) {
		$new_user_data = apply_filters( 'forminator_custom_form_user_registration_before_insert', $new_user_data, $custom_form, $entry );

		$user_id = wp_insert_user( $new_user_data );
		if ( is_wp_error( $user_id ) ) {

			return __( 'Couldn&#8217;t register you&hellip; please contact us if you continue to have problems.', Forminator::DOMAIN );
		}

		$settings = $custom_form->settings;
		$this->add_user_meta( $user_id, $settings, $submitted_data );

		if ( ! $this->check_activation_method( $settings['activation-method'] ) ) {
			//Sending notification
			$this->mail_sender->process_mail( $custom_form, $submitted_data, $entry );
		}

		if ( isset( $settings['activation-email'] ) && 'default' === $settings['activation-email'] ) {
			$this->forminator_new_user_notification( $user_id, $new_user_data['user_pass'] );
		} else {
			//Send notification to admin
			$this->forminator_new_user_notification( $user_id, '' );
		}

		do_action( 'forminator_cform_user_registered', $user_id, $custom_form, $entry, $new_user_data['user_pass'], $submitted_data );

		if ( ! $is_user_signon && isset( $settings['automatic-login'] ) && ! empty( $settings['automatic-login'] ) ) {
			$this->automatic_login( $user_id );
		}

		return $user_id;
	}

	/**
	 * Check a pending activation for the specified user_login or user_email.
	 *
	 * @param string $key user_login or user_email.
	 * @param string $value
	 *
	 * @return bool
	 */
	public function pending_activation_exists( $key, $value ) {
		global $wpdb;

		require_once __DIR__ . '/../user/class-forminator-cform-user-signups.php';

		$table_name = $wpdb->prefix . 'signups';

		if ( Forminator_CForm_User_Signups::table_exists( $table_name ) && in_array( $key, array( 'user_login', 'user_email' ) ) ) {// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			if ( 'user_login' === $key ) {
				$value = preg_replace( '/\s+/', '', sanitize_user( $value, true ) );
			}

			$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE active=0 AND {$key}=%s", $value ) );
			if ( ! is_null( $result ) ) {
				$diff = current_time( 'timestamp', true ) - mysql2date( 'U', $result->registered );// phpcs:ignore
				// If registered more than two days ago, cancel registration and delete this signup.
				if ( $diff > 2 * DAY_IN_SECONDS ) {
					return (bool) $wpdb->delete( $table_name, array( $key => $value ) );
				} else {
					return true;
				}
			}
		}

		return false;
	}

	public function validate( $custom_form, $submitted_data, $field_data_array, $is_approve = false ) {
		$settings = $custom_form->settings;
		//Field username
		$username = '';
		if ( isset( $settings['registration-username-field'] ) && ! empty( $settings['registration-username-field'] ) ) {
			$username = $this->replace_value( $field_data_array, $settings['registration-username-field'] );
		}
		$validate = $this->validate_username( $username );
		if ( ! $validate['result'] ) {

			return $validate['message'];
		}
		// Username is valid, but has already pending activation
		if ( ! is_multisite() && $this->pending_activation_exists( 'user_login', $username ) ) {

			return __( 'That username is currently reserved but may be available in a couple of days', Forminator::DOMAIN );
		}

		//Field user email
		$user_email = '';
		if ( isset( $settings['registration-email-field'] ) && ! empty( $settings['registration-email-field'] ) ) {
			$user_email = $this->replace_value( $field_data_array, $settings['registration-email-field'] );
		}
		$validate = $this->validate_email( $user_email );
		if ( ! $validate['result'] ) {

			return $validate['message'];
		}
		// Email is valid, but has already pending activation
		if ( ! is_multisite() && $this->pending_activation_exists( 'user_email', $user_email ) ) {

			return __( 'That email address has already been used. Please check your inbox for an activation email. It will become available in a couple of days if you do nothing.', Forminator::DOMAIN );
		}

		//Multisite validation
		$validate = apply_filters( 'forminator_cform_user_registration_validation', $validate, $custom_form, $submitted_data, $is_approve );
		if ( ! $validate['result'] ) {

			return $validate['message'];
		}

		//Field password
		$password = '';
		if ( isset( $settings['registration-password-field'] ) && ! empty( $settings['registration-password-field'] ) ) {
			if ( 'auto' === $settings['registration-password-field'] ) {
				$password = wp_generate_password();
			} else {
				$password = $this->replace_value( $field_data_array, $settings['registration-password-field'] );
			}
		} else {
			foreach ( $field_data_array as $key => $field_arr ) {
				if ( false !== stripos( $field_arr['name'], 'password-' ) ) {
					$password = $field_arr['value'];
					break;
				}
			}
		}

		$new_user_data = array(
			'user_login' => $username,
			'user_pass'  => $password,
			'user_email' => $user_email,
		);

		//Field first name
		if ( isset( $settings['registration-first-name-field'] ) && ! empty( $settings['registration-first-name-field'] ) ) {
			$new_user_data['first_name'] = $this->replace_value( $field_data_array, $settings['registration-first-name-field'] );
		}

		//Field last name
		if ( isset( $settings['registration-last-name-field'] ) && ! empty( $settings['registration-last-name-field'] ) ) {
			$new_user_data['last_name'] = $this->replace_value( $field_data_array, $settings['registration-last-name-field'] );
		}

		//Field website
		if ( isset( $settings['registration-website-field'] ) && ! empty( $settings['registration-website-field'] ) ) {
			$new_user_data['user_url'] = $this->replace_value( $field_data_array, $settings['registration-website-field'] );
		}

		//Field user role
		if ( isset( $settings['registration-role-field'] ) && ! empty( $settings['registration-role-field'] ) ) {
			$new_user_data['role'] = $settings['registration-role-field'];
		}

		return $new_user_data;
	}

	/**
	 * Process validation
	 *
	 * @param Forminator_Custom_Form_Model $custom_form
	 * @param array $submitted_data
	 * @param array $field_data_array
	 *
	 * @return array|mixed
	 */
	public function process_validation( $custom_form, $submitted_data, $field_data_array ) {
		$user_data = $this->validate( $custom_form, $submitted_data, $field_data_array );
		if ( ! is_array( $user_data ) ) {

			return $user_data;
		}

		$this->user_data = $user_data;

		return true;
	}

	/**
	 * Process registration
	 *
	 * @param Forminator_Custom_Form_Model $custom_form
	 * @param array $submitted_data
	 * @param Forminator_Form_Entry_Model $entry
	 *
	 * @return array|mixed
	 */
	public function process_registration( $custom_form, $submitted_data, Forminator_Form_Entry_Model $entry ) {
		$settings      = $custom_form->settings;
		$new_user_data = $this->user_data;

		if ( isset( $settings['activation-method'] ) && ! empty( $settings['activation-method'] ) ) {
			if ( $this->check_activation_method( $settings['activation-method'] ) ) {
				$activation = $this->handle_user_activation( $new_user_data, $custom_form, $entry, $submitted_data );
				if ( true !== $activation ) {
					return $activation;
				}
			} else {
				$user_id = $this->create_user( $new_user_data, $custom_form, $entry, $submitted_data );
				if ( is_int( $user_id ) ) {
					$new_user_data['user_id'] = $user_id;
				} else {
					return $user_id;
				}
			}
		}

		return $new_user_data;
	}

	/**
	 * Validation for email fields
	 *
	 * @param string $email
	 *
	 * @return array
	 */
	private function validate_email( $email ) {
		$data = array(
			'result'  => true,
			'message' => '',
		);

		if ( $email ) {
			if ( ! is_email( $email ) ) {
				$data['result']  = false;
				$data['message'] = __( 'This email address is not valid.', Forminator::DOMAIN );

				return $data;
			}

			// Throws an error if the email is already registered
			if ( email_exists( $email ) ) {
				$data['result']  = false;
				$data['message'] = __( 'This email address is already registered.', Forminator::DOMAIN );

				return $data;
			}
		} else {
			$data['result']  = false;
			$data['message'] = __( 'The email address can not be empty.', Forminator::DOMAIN );

			return $data;
		}

		return $data;
	}

	/**
	 * Validation for username fields
	 *
	 * @param string $username
	 *
	 * @return array
	 */
	private function validate_username( $username ) {
		$data = array(
			'result'  => true,
			'message' => '',
		);
		if ( $username ) {
			// Throws an error if the username contains invalid characters
			if ( ! validate_username( $username ) ) {
				$data['result']  = false;
				$data['message'] = __( 'This username is invalid because it uses illegal characters. Please enter a valid username.', Forminator::DOMAIN );

				return $data;
			}

			// Throws an error if the username already exists
			if ( username_exists( $username ) ) {
				$data['result']  = false;
				$data['message'] = __( 'This username is already registered.', Forminator::DOMAIN );

				return $data;
			}
		} else {
			$data['result']  = false;
			$data['message'] = __( 'The username can not be empty.', Forminator::DOMAIN );

			return $data;
		}

		return $data;
	}

	private function automatic_login( $user_id ) {
		wp_clear_auth_cookie();
		wp_set_auth_cookie( $user_id );
		wp_set_current_user( $user_id );
	}

	/**
	 * Validation for multi site
	 *
	 * @param array $validate
	 * @param Forminator_Custom_Form_Model $custom_form
	 * @param array $submitted_data
	 * @param bool $is_approve
	 *
	 * @return array
	 */
	public function multisite_validation( $validate, $custom_form, $submitted_data, $is_approve ) {
		$data    = array(
			'result'  => true,
			'message' => '',
		);
		$setting = $custom_form->settings;

		// Make sure option 'Site registration' is set
		$option_create_site = forminator_get_property( $setting, 'site-registration' );
		if ( $is_approve || ! $option_create_site || ( isset( $option_create_site ) && 'enable' !== $option_create_site ) ) {

			return $data;
		}

		$blog_data    = $this->replace_site_data( $setting, $submitted_data );
		$blog_address = $blog_data['address'];
		$blog_title   = $blog_data['title'];

		// get validation result for multi-site fields
		$validation_result = wpmu_validate_blog_signup( $blog_address, $blog_title, wp_get_current_user() );

		// Site address validation
		if ( isset( $blog_address ) && ! empty( $blog_address ) ) {
			$error_msg = isset( $validation_result['errors']->errors['blogname'][0] ) ? $validation_result['errors']->errors['blogname'][0] : false;

			if ( false !== $error_msg ) {
				$data['result']  = false;
				$data['message'] = $error_msg;

				return $data;
			}
		}
		// Site title validation
		if ( isset( $blog_title ) && ! empty( $blog_title ) ) {
			$error_msg = isset( $validation_result['errors']->errors['blog_title'][0] ) ? $validation_result['errors']->errors['blog_title'][0] : false;

			if ( false !== $error_msg ) {
				$data['result']  = false;
				$data['message'] = $error_msg;

				return $data;
			}
		}

		return $data;
	}

	/**
	 * Create site
	 *
	 * @param int $user_id
	 * @param Forminator_Custom_Form_Model $custom_form
	 * @param Forminator_Form_Entry_Model $entry
	 * @param string $password
	 * @param array $submitted_data
	 *
	 * @return bool|int
	 */
	public function create_site( $user_id, $custom_form, $entry, $password, $submitted_data ) {
		global $current_site;

		$setting = $custom_form->settings;

		// Is option 'Site registration' enabled?
		$option_create_site = forminator_get_property( $setting, 'site-registration' );
		if ( ! $option_create_site || ( isset( $option_create_site ) && 'enable' !== $option_create_site ) ) {

			return false;
		}

		$site_data = $this->get_site_data( $setting, $user_id, $submitted_data );
		if ( ! $site_data ) {

			return false;
		}

		/**
		 * Allows modifications to the new site meta
		 *
		 * @param array An array of new site arguments (ex. if the site is public => 1)
		 * @param array $custom_form The Form Object to filter through
		 * @param array $entry The Entry Object to filter through
		 * @param int $user_id Filer through the ID of the user who creates the site
		 */
		$site_meta = apply_filters( 'forminator_cform_user_registration_new_site_meta', array( 'public' => 1 ), $custom_form, $entry, $user_id );
		$blog_id   = wpmu_create_blog( $site_data['domain'], $site_data['path'], $site_data['title'], $user_id, $site_meta, $current_site->id );

		if ( is_wp_error( $blog_id ) ) {

			return false;
		}

		if ( ! is_super_admin( $user_id ) && get_user_option( 'primary_blog', $user_id ) === $current_site->blog_id ) {
			update_user_option( $user_id, 'primary_blog', $blog_id, true );
		}

		$site_role = forminator_get_property( $setting, 'site-registration-role-field' );
		if ( $site_role ) {
			$user = new WP_User( $user_id, null, $blog_id );
			$user->set_role( $site_role );
		}

		$root_role = forminator_get_property( $setting, 'registration-role-field' );
		// If no root role, remove user from current site
		if ( ! $root_role || ( isset( $root_role ) && 'notCreate' === $root_role ) ) {
			remove_user_from_blog( $user_id );
		} else {
			// update their role on current site
			$user = new WP_User( $user_id );
			$user->set_role( $root_role );
		}

		// Send a notification if a new site was added
		wpmu_welcome_notification( $blog_id, $user_id, $password, $site_data['title'], array( 'public' => 1 ) );

		do_action( 'forminator_cform_site_created', $blog_id, $user_id, $entry, $custom_form, $password );

		return $blog_id;
	}

	/**
	 * Get user data
	 *
	 * @param bool|int $user_id
	 * @param array    $prepare_user_data
	 *
	 * @return bool|array
	 */
	private function get_user_data( $user_id = false, $prepare_user_data = array() ) {
		if ( ! $user_id ) {
			if ( ! empty( $prepare_user_data ) ) {
				$user_login = $prepare_user_data['user_login'];
				$user_email = $prepare_user_data['user_email'];
				$user_pass  = $prepare_user_data['user_pass'];
			}
		} else {
			$user       = new WP_User( $user_id );
			$user_login = $user->get( 'user_login' );
			$user_email = $user->get( 'user_email' );
			$user_pass  = $user->get( 'user_pass' );
		}

		if ( empty( $user_login ) || empty( $user_email ) ) {
			return false;
		}

		return array(
			'user_login' => $user_login,
			'user_email' => $user_email,
			'password'   => $user_pass,
		);
	}

	/**
	 * Replace site data
	 *
	 * @param array $setting
	 * @param array $submitted_data
	 *
	 * @return array
	 */
	private function replace_site_data( $setting, $submitted_data ) {
		$blog_address = '';
		$address      = forminator_get_property( $setting, 'site-registration-name-field' );
		if ( isset( $submitted_data[ $address ] ) && ! empty( $submitted_data[ $address ] ) ) {
			$address = $submitted_data[ $address ];
		}
		if ( ! preg_match( '/(--)/', $address ) && preg_match( '|^([a-zA-Z0-9-])+$|', $address ) ) {
			$blog_address = strtolower( $address );
		}

		$blog_title = forminator_get_property( $setting, 'site-registration-title-field' );
		if ( isset( $submitted_data[ $blog_title ] ) && ! empty( $submitted_data[ $blog_title ] ) ) {
			$blog_title = $submitted_data[ $blog_title ];
		}

		return array(
			'address' => $blog_address,
			'title'   => $blog_title,
		);
	}

	/**
	 * Get site data
	 *
	 * @param array $setting
	 * @param int $user_id
	 * @param array $submitted_data
	 * @param array $prepare_user_data
	 *
	 * @return array
	 */
	public function get_site_data( $setting, $user_id, $submitted_data, $prepare_user_data = array() ) {
		global $current_site;

		$user_data = $this->get_user_data( $user_id, $prepare_user_data );
		$blog_data = $this->replace_site_data( $setting, $submitted_data );

		if ( empty( $blog_data['address'] ) || empty( $user_data['user_email'] ) || ! is_email( $user_data['user_email'] ) ) {
			return array();
		}

		if ( is_subdomain_install() ) {
			$blog_domain = $blog_data['address'] . '.' . preg_replace( '|^www\.|', '', $current_site->domain );
			$path        = $current_site->path;
		} else {
			$blog_domain = $current_site->domain;
			$path        = trailingslashit( $current_site->path ) . $blog_data['address'] . '/';
		}

		return array(
			'domain' => $blog_domain,
			'path'   => $path,
			'title'  => $blog_data['title'],
			'email'  => $user_data['user_email'],
		);
	}

	/**
	 * Get custom user meta
	 *
	 * @param array $setting
	 *
	 * @return array
	 */
	public function get_custom_user_meta( $setting ) {
		$meta = array();

		if ( empty( $setting['options'] ) ) {
			return $meta;
		}

		foreach ( $setting['options'] as $meta_item ) {
			list( $meta_key, $meta_value, $custom_meta_key ) = array_pad( array_values( $meta_item ), 3, false );

			$meta_key          = $custom_meta_key ? $custom_meta_key : $meta_key;
			$meta[ $meta_key ] = $meta_value;
		}

		return $meta;
	}

	/**
	 * Add user meta
	 *
	 * @param int $user_id
	 * @param array $setting
	 * @param array $submitted_data
	 *
	 * @return void
	 */
	public function add_user_meta( $user_id, $setting, $submitted_data ) {
		$custom_meta = $this->get_custom_user_meta( $setting );

		if ( ! is_array( $custom_meta ) || empty( $custom_meta ) ) {
			return;
		}

		foreach ( $custom_meta as $meta_key => $meta_value ) {
			// Skip empty meta items
			if ( ! $meta_key || ! $meta_value ) {
				continue;
			}
			if ( strpos( $meta_value, '{' ) !== false ) {
				$meta_value = forminator_replace_form_data( $meta_value, $submitted_data );
				$meta_value = forminator_replace_variables( $meta_value, $setting['form_id'] );
			}

			update_user_meta( $user_id, $meta_key, $meta_value );
		}
	}

	/**
	 * Change notifications
	 *
	 * @param string $activation_method
	 * @param array $notifications
	 *
	 * @return array
	 */
	private function change_notifications( $activation_method, $notifications ) {
		foreach ( $notifications as $key => $notification ) {
			if ( isset( $notifications[ $key ][ 'email-subject-method-' . $activation_method ] ) ) {
				$notifications[ $key ]['email-subject'] = $notifications[ $key ][ 'email-subject-method-' . $activation_method ];
			}
			if ( isset( $notifications[ $key ][ 'email-editor-method-' . $activation_method ] ) ) {
				$notifications[ $key ]['email-editor'] = $notifications[ $key ][ 'email-editor-method-' . $activation_method ];
			}
		}

		return $notifications;
	}

	/**
	 * Change 'Thank you' message
	 *
	 * @param string $message
	 * @param array $submitted_data
	 * @param Forminator_Custom_Form_Model $custom_form
	 *
	 * @return string
	 */
	public function change_thankyou_message( $message, $submitted_data, $custom_form ) {
		$settings = $custom_form->settings;
		if ( isset( $settings['activation-method'] )
			&& ! empty( $settings['activation-method'] )
			&& $this->check_activation_method( $settings['activation-method'] )
			&& isset( $settings[ $settings['activation-method'] . '-thankyou-message' ] )
		) {
			$message = $settings[ $settings['activation-method'] . '-thankyou-message' ];
		}

		return $message;
	}

	/**
	 * Get the set password url for the specified user.
	 *
	 * @global wpdb         $wpdb      WordPress database object for queries.
	 * @global PasswordHash $wp_hasher Portable PHP password hashing framework instance.
	 *
	 * @param WP_User $user
	 *
	 * @return string
	 */
	public function get_set_password_url( $user ) {
		global $wpdb, $wp_hasher;

		// Generate a random password reset key.
		$key = wp_generate_password( 20, false );

		/** This action is documented in wp-login.php */
		do_action( 'retrieve_password_key', $user->user_login, $key );

		// Hashes the plain-text key.
		if ( empty( $wp_hasher ) ) {
			require_once ABSPATH . WPINC . '/class-phpass.php';
			$wp_hasher = new PasswordHash( 8, true );
		}
		$hashed = time() . ':' . $wp_hasher->HashPassword( $key );

		// Inserts the hashed key into the database.
		$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );

		return network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' );
	}

	/**
	 * Overrides wp_new_user_notification to email login credentials to a newly-registered user.
	 *
	 * @param int    $user_id        User ID.
	 * @param string $plaintext_pass The password being sent to the user.
	 * @param string $notify         Optional. Type of notification that should happen. Accepts 'admin' or an empty
	 *                               string (admin only), 'user', or 'both' (admin and user). Default empty.
	 */
	public function forminator_new_user_notification( $user_id, $plaintext_pass = '', $notify = '' ) {
		$user = get_userdata( $user_id );

		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		$switched_locale = switch_to_locale( get_locale() );

		$message  = sprintf( __( 'New user registration on your site %s:', Forminator::DOMAIN ), $blogname ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s', Forminator::DOMAIN ), $user->user_login ) . "\r\n\r\n";
		$message .= sprintf( __( 'Email: %s', Forminator::DOMAIN ), $user->user_email ) . "\r\n";

		$result = @wp_mail( get_option( 'admin_email' ), sprintf( __( '[%s] New User Registration', Forminator::DOMAIN ), $blogname ), $message );

		if ( $switched_locale ) {
			restore_previous_locale();
		}

		if ( 'admin' === $notify || ( empty( $plaintext_pass ) && empty( $notify ) ) ) {
			return;
		}

		$switched_locale = switch_to_locale( get_user_locale( $user ) );

		$message = sprintf( __( 'Username: %s', Forminator::DOMAIN ), $user->user_login ) . "\r\n\r\n";

		if ( empty( $plaintext_pass ) ) {
			$message .= __( 'To set your password, visit the following address:', Forminator::DOMAIN ) . "\r\n\r\n";
			$message .= '<' . $this->get_set_password_url( $user ) . ">\r\n\r\n";
		} else {
			$message .= sprintf( __( 'Password: %s', Forminator::DOMAIN ), $plaintext_pass ) . "\r\n\r\n";
		}

		$message .= wp_login_url() . "\r\n";

		$result = wp_mail( $user->user_email, sprintf( __( '[%s] Your username and password info', Forminator::DOMAIN ), $blogname ), $message );

		if ( $switched_locale ) {
			restore_previous_locale();
		}
	}

	/**
	 * Change custom form.
	 *
	 * @param Forminator_Custom_Form_Model $custom_form
	 *
	 * @return Forminator_Custom_Form_Model
	 */
	public function change_custom_form( $custom_form ) {
		$custom_form->notifications = array();

		return $custom_form;
	}
}
