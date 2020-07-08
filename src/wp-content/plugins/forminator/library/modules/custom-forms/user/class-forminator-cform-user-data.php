<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * User data for registration and login forms
 *
 * @since 1.11
 */
class Forminator_CForm_User_Data {

	public function __construct() {
		if ( is_admin() ) {
			// Handle user popups
			add_action( 'wp_ajax_forminator_approve_user_popup', array( $this, 'approve_user' ) );
			add_action( 'wp_ajax_forminator_delete_unconfirmed_user_popup', array( $this, 'delete_unconfirmed_user' ) );
			// Change submission entries
			add_filter( 'forminator_custom_form_entries_iterator', array( $this, 'change_entries_iterator' ), 11, 2 );
			// Delete user signup
			if ( ! is_multisite() ){
				add_action( 'delete_user', array( $this, 'delete_signup_user' ) );
			}
		} else {
			// Approve user
			add_action( 'wp', array( $this, 'admin_approve_user_by_link' ) );
		}
	}

	/**
	 * Approve user
	 *
	 * @return string JSON
	 */
	public function approve_user() {
		forminator_validate_ajax( 'forminatorCustomFormEntries' );

		if ( isset( $_POST['activation_key'] ) ) {
			$activation_key = sanitize_text_field( $_POST['activation_key'] );// phpcs:ignore Standard.Category.SniffName.ErrorCode

			try {
				require_once __DIR__ . '/class-forminator-cform-user-signups.php';

				$userdata = Forminator_CForm_User_Signups::activate_signup( $activation_key, true );
				if ( is_wp_error( $userdata ) ) {
					throw new Exception( $userdata->get_error_message() );
				}

				wp_send_json_success();

			} catch ( Exception $e ) {
				wp_send_json_error( $e->getMessage() );
			}
		} else {
			wp_send_json_error( __( 'Invalid activation key.', Forminator::DOMAIN ) );
		}
	}

	/**
	 * Delete unconfirmed user
	 *
	 * @return string JSON
	 */
	public function delete_unconfirmed_user() {
		forminator_validate_ajax( 'forminatorCustomFormEntries' );

		if ( isset( $_POST['activation_key'] ) ) {
			$activation_key = sanitize_text_field( $_POST['activation_key'] );// phpcs:ignore Standard.Category.SniffName.ErrorCode

			try {
				require_once __DIR__ . '/class-forminator-cform-user-signups.php';

				$result = Forminator_CForm_User_Signups::delete_signup( $activation_key );

				if ( is_wp_error( $result ) ) {
					throw new Exception( $result->get_error_message() );
				}

				$entry_id = ( isset( $_POST['entry_id'] ) && ! empty( $_POST['entry_id'] ) ) ? sanitize_text_field( $_POST['entry_id'] ) : false;
				if ( ! $entry_id ) {
					wp_send_json_error( __( 'Invalid entry ID.', Forminator::DOMAIN ) );
				}
				$form_id = ( isset( $_POST['form_id'] ) && ! empty( $_POST['form_id'] ) ) ? sanitize_text_field( $_POST['form_id'] ) : false;
				if ( ! $form_id ) {
					wp_send_json_error( __( 'Invalid form ID.', Forminator::DOMAIN ) );
				}

				if ( false === Forminator_Form_Entry_Model::delete_by_entrys( $form_id, $entry_id ) ) {
					wp_send_json_error( __( 'Error! Entry was not deleted.', Forminator::DOMAIN ) );
				}

				wp_send_json_success();

			} catch ( Exception $e ) {
				wp_send_json_error( $e->getMessage() );
			}
		} else {
			wp_send_json_error( __( 'Invalid activation key.', Forminator::DOMAIN ) );
		}
	}

	/**
	 * Change submission entries.
	 *
	 * @param array                       $iterator
	 * @param Forminator_Form_Entry_Model $entry
	 *
	 * @return array
	 */
	public function change_entries_iterator( $iterator, $entry ) {
		$activation_method = $entry->get_meta( 'activation_method', '' );
		//Add entry's iterators for forms with Email and Manual user activation methods
		if ( isset( $activation_method ) && '' !== $activation_method ) {
			$activation_key = $entry->get_meta( 'activation_key', '' );
			if ( false !== $activation_key && '' !== $activation_key ) {
				require_once __DIR__ . '/class-forminator-cform-user-signups.php';

				if ( ! is_null( Forminator_CForm_User_Signups::get_pending_activations( $activation_key ) ) ) {
					$iterator['activation_key'] = $activation_key;
				}
				$iterator['activation_method'] = $activation_method;
			}
		}

		return $iterator;
	}

	/**
	 * Approve user by link
	 */
	public function admin_approve_user_by_link() {
		if ( isset( $_GET['page'] ) && 'forminator_activation' === $_GET['page'] && isset( $_GET['key'] ) ) { // phpcs:ignore Standard.Category.SniffName.ErrorCode
			require_once __DIR__ . '/class-forminator-cform-user-signups.php';

			$activation_key = sanitize_text_field( $_GET['key'] );// phpcs:ignore Standard.Category.SniffName.ErrorCode
			$userdata       = Forminator_CForm_User_Signups::activate_signup( $activation_key, false );
			if ( ! is_wp_error( $userdata ) ) {
				//For Email-activation
				if ( isset( $userdata['redirect_page'] ) ) {
					wp_redirect( get_permalink( $userdata['redirect_page'] ) );
				} elseif (
					current_user_can( 'manage_options' ) &&
					isset( $userdata['form_id'] ) && ! empty( $userdata['form_id'] ) &&
					isset( $userdata['entry_id'] ) && ! empty( $userdata['entry_id'] )
				) {
					wp_redirect( admin_url( 'admin.php?page=forminator-entries&form_type=forminator_forms&form_id=' . $userdata['form_id'] . '&entry_id=' . $userdata['entry_id'] ) );
				}
				exit();
			}
		}
	}

	/**
	 * Delete user signup.
	 *
	 * @param int $user_id
	 */
	public function delete_signup_user( $user_id ) {
		$user = new WP_User( $user_id );
		require_once __DIR__ . '/class-forminator-cform-user-signups.php';

		Forminator_CForm_User_Signups::delete_by_user( 'user_email', $user->user_email );
	}
}
