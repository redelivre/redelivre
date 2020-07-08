<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Front user class for signups
 *
 * @since 1.11
 */
class Forminator_CForm_User_Signups {

	public $meta;
	public $entry;
	public $form;
	public $settings;
	public $submitted_data;
	public $user_data;

	public function __construct( $signup ) {
		//This is for internal variables (i.e. activation key)
		foreach ( $signup as $key => $value ) {
			$this->$key = $value;
		}

		$this->meta     = maybe_unserialize( $signup->meta );
		$this->entry    = new Forminator_Form_Entry_Model( $this->meta['entry_id'] );
		$this->form     = Forminator_Custom_Form_Model::model()->load( $this->meta['form_id'] );
		$this->settings = $this->form->settings;
		// Don't use null coalescing operator for PHP version 5.6.*
		$this->submitted_data = isset( $this->meta['submitted_data'] ) ? $this->meta['submitted_data'] : array();
		$this->user_data      = isset( $this->meta['user_data'] ) ? $this->meta['user_data'] : array();
	}

	public static function get( $key ) {
		if ( ! is_multisite() ) {
			self::create_signups_table();
		}
		global $wpdb;

		$signup = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->signups WHERE activation_key = %s", $key ) );

		if ( empty( $signup ) ) {
			return new WP_Error( 'invalid_key', __( 'Invalid activation key.' ) );
		}
		if ( $signup->active ) {
			return new WP_Error( 'already_active', __( 'The user is already active.' ), $signup );
		}

		return new Forminator_CForm_User_Signups( $signup );
	}

	public function get_activation_method() {

		return ( isset( $this->settings['activation-method'] ) && ! empty( $this->settings['activation-method'] ) )
			? $this->settings['activation-method']
			: '';
	}

	public function set_as_activated() {
		global $wpdb;

		// Remove password for security.
		$this->meta['user_data']['user_pass'] = '';
		$this->meta['submitted_data']         = '';

		$now    = current_time( 'mysql', true );
		$result = $wpdb->update(
			$wpdb->signups,
			array(
				'active'    => 1,
				'activated' => $now,
				'meta'      => serialize( $this->meta ),
			),
			array( 'activation_key' => $this->activation_key )
		);

		return $result;
	}

	/**
	 * Check to exist table
	 *
	 * @param string $table_name
	 *
	 * @return bool
	 */
	public static function table_exists( $table_name ) {
		global $wpdb;

		return (bool) $wpdb->get_results( "SHOW TABLES LIKE '{$table_name}'" );
	}

	/**
	 * Create Signups table
	 */
	public static function create_signups_table() {
		global $wpdb;

		self::add_signups_to_wpdb();

		$table_name = $wpdb->signups;
		if ( self::table_exists( $table_name ) ) {
			$column_exists = $wpdb->query( "SHOW COLUMNS FROM {$table_name} LIKE 'signup_id'" );
			if ( empty( $column_exists ) ) {
				// New primary key for signups.
				$wpdb->query( "ALTER TABLE {$table_name} ADD signup_id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST" );
				$wpdb->query( "ALTER TABLE {$table_name} DROP INDEX domain" );
			}
		}

		self::install_signups();
	}

	private static function install_signups() {
		global $wpdb;

		// Signups is not there and we need it so let's create it
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Use WP's core CREATE TABLE query
		$create_queries = wp_get_db_schema( 'ms_global' );
		if ( ! is_array( $create_queries ) ) {
			$create_queries = explode( ';', $create_queries );
			$create_queries = array_filter( $create_queries );
		}

		// Filter out all the queries except wp_signups
		foreach ( $create_queries as $key => $query ) {
			if ( preg_match( '|CREATE TABLE ([^ ]*)|', $query, $matches ) ) {
				if ( trim( $matches[1], '`' ) !== $wpdb->signups ) {
					unset( $create_queries[ $key ] );
				}
			}
		}

		// Run WordPress's database upgrader
		if ( ! empty( $create_queries ) ) {
			$result = dbDelta( $create_queries );
		}
	}

	/**
	 * Add signups property to $wpdb object. Used by several MS functions.
	 */
	private static function add_signups_to_wpdb() {
		global $wpdb;
		$wpdb->signups = $wpdb->base_prefix . 'signups';
	}

	public function delete() {
		global $wpdb;

		return $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->signups WHERE activation_key = %s", $this->activation_key ) );
	}

	/**
	 * Delete signup entry by user data
	 *
	 * @param string $user_key
	 * @param string $user_value
	 *
	 * @return int|bool
	 */
	public static function delete_by_user( $user_key, $user_value ) {
		global $wpdb;

		self::add_signups_to_wpdb();

		return $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->signups WHERE $user_key = %s", $user_value ) );
	}

	public static function prep_signups_functionality() {
		if ( ! is_multisite() ) {
			// require MS functions
			require_once ABSPATH . 'wp-includes/ms-functions.php';

			self::create_signups_table();

			// remove filter which checks for Network setting (not active on non-ms install)
			remove_filter( 'option_users_can_register', 'users_can_register_signup_filter' );
		}

		//Update the signup URL
		add_filter( 'wpmu_signup_user_notification_email', array( 'Forminator_CForm_User_Signups', 'modify_signup_user_notification_message' ), 10, 4 );
		add_filter( 'wpmu_signup_blog_notification_email', array( 'Forminator_CForm_User_Signups', 'modify_signup_blog_notification_message' ), 10, 7 );

		//Disable activation email for manual activation method
		add_filter( 'wpmu_signup_user_notification', array( 'Forminator_CForm_User_Signups', 'maybe_suppress_signup_user_notification' ), 10, 3 );
		add_filter( 'wpmu_signup_blog_notification', array( 'Forminator_CForm_User_Signups', 'maybe_suppress_signup_blog_notification' ), 10, 6 );

		add_filter( 'wpmu_signup_user_notification', array( 'Forminator_CForm_User_Signups', 'add_site_name_filter' ) );
		add_filter( 'wpmu_signup_user_notification_subject', array( 'Forminator_CForm_User_Signups', 'remove_site_name_filter' ) );
	}

	public static function get_pending_activations( $activation_key ) {
		// Create table Signups for non-multisite installs
		if ( ! is_multisite() ) {
			self::create_signups_table();
		}
		global $wpdb;

		return $wpdb->get_row( $wpdb->prepare( "SELECT signup_id FROM {$wpdb->signups} WHERE activation_key = %s AND active = 0", $activation_key ) );
	}

	public static function maybe_suppress_signup_user_notification( $user, $user_email, $key ) {
		return self::is_manual_activation( $key ) ? false : $user;
	}

	public static function maybe_suppress_signup_blog_notification( $domain, $path, $title, $user, $user_email, $key ) {
		return self::is_manual_activation( $key ) ? false : $user;
	}

	public static function is_manual_activation( $key ) {
		$signup = self::get( $key );

		return ! is_wp_error( $signup ) && 'manual' === $signup->get_activation_method();
	}

	public static function modify_signup_user_notification_message( $message, $user, $user_email, $key ) {
		$url = add_query_arg(
			array(
				'page' => 'forminator_activation',
				'key'  => $key,
			),
			home_url( '/' )
		);

		return sprintf( $message, esc_url_raw( $url ) );
	}

	public static function modify_signup_blog_notification_message( $message, $domain, $path, $title, $user, $user_email, $key ) {
		$url = add_query_arg(
			array(
				'page' => 'forminator_activation',
				'key'  => $key,
			),
			home_url( '/' )
		);

		return sprintf( $message, esc_url_raw( $url ), esc_url( "http://{$domain}{$path}" ), $key );
	}

	public static function add_site_name_filter( $return ) {
		add_filter( 'site_option_site_name', array( __class__, 'modify_site_name' ) );

		return $return;
	}

	public static function remove_site_name_filter( $return ) {
		remove_filter( 'site_option_site_name', array( __class__, 'modify_site_name' ) );

		return $return;
	}

	public static function modify_site_name( $site_name ) {
		if ( ! $site_name ) {
			$site_name = get_site_option( 'blogname' );
		}

		return $site_name;
	}

	/**
	 * Add meta of a user sign-up
	 *
	 * @param Forminator_Form_Entry_Model $entry
	 * @param string $meta_key
	 * @param string $meta_value
	 *
	 * @return bool
	 */
	public static function add_signup_meta( $entry, $meta_key, $meta_value ) {
		$entry->set_fields(
			array(
				array(
					'name'  => $meta_key,
					'value' => $meta_value,
				),
			)
		);
	}

	/**
	 * Activate signup
	 *
	 * @param string $key
	 * @param bool $is_user_signon
	 *
	 * @return array|Forminator_CForm_User_Signups|WP_Error
	 */
	public static function activate_signup( $key, $is_user_signon ) {
		global $wpdb, $current_site;

		$blog_id = is_object( $current_site ) ? $current_site->id : false;
		$signup  = self::get( $key );
		if ( is_wp_error( $signup ) ) {
			return $signup;
		}

		$user_id = username_exists( $signup->user_data['user_login'] );
		if ( $user_id ) {
			//User already exists
			$signup->set_as_activated();

			return new WP_Error( 'user_already_exists', __( 'That username is already activated.', Forminator::DOMAIN ), $signup );
		}

		if ( email_exists( $signup->user_data['user_email'] ) ) {
			//Email already exists
			return new WP_Error( 'email_already_exists', __( 'Sorry, that email address is already used!', Forminator::DOMAIN ), $signup );
		}

		if ( is_multisite() ) {
			remove_action( 'forminator_cform_user_registered', array( 'Forminator_CForm_Front_User_Registration', 'create_site' ) );
		}

		$password = Forminator_CForm_Front_User_Registration::openssl_decrypt( $signup->user_data['user_pass'] );

		$forminator_user_registration = new Forminator_CForm_Front_User_Registration();
		$user_data                    = $signup->user_data;
		$user_data['user_pass']       = $password;
		if ( ! is_array( $user_data ) ) {

			return new WP_Error( 'create_user', $user_data );
		}
		//For decrypted password
		$signup->submitted_data = $forminator_user_registration->change_submitted_data( $signup->submitted_data, $password );

		$user_id = $forminator_user_registration->create_user( $user_data, $signup->form, $signup->entry, $signup->submitted_data, $is_user_signon );
		if ( ! $user_id ) {
			return new WP_Error( 'create_user', __( 'Could not create user', Forminator::DOMAIN ), $signup );
		}

		$signup->set_as_activated();

		do_action( 'forminator_activate_user', $user_id, $signup->meta );

		if ( is_multisite() ) {
			$option_create_site = forminator_get_property( $signup->settings, 'site-registration' );
			if ( isset( $option_create_site ) && 'enable' === $option_create_site ) {
				$forminator_user_registration->create_site( $user_id, $signup->form, $signup->entry, $password, $signup->submitted_data );
			}
		}

		$result = array(
			'user_id'  => $user_id,
			'blog_id'  => $blog_id,
			'form_id'  => $signup->form->id,
			'entry_id' => $signup->entry->entry_id,
		);
		//Redirected page for Email-activation method
		if ( isset( $signup->settings['activation-method'], $signup->settings['confirmation-page'] )
			&& 'email' === $signup->settings['activation-method']
			&& ! empty( $signup->settings['confirmation-page'] )
		) {
			$result['redirect_page'] = $signup->settings['confirmation-page'];
		}

		return $result;
	}

	public static function delete_signup( $key ) {
		$signup = self::get( $key );
		if ( is_wp_error( $signup ) ) {
			return $signup;
		}

		do_action( 'forminator_cform_userregistration_delete_signup', $signup );

		return $signup->delete();
	}
}
