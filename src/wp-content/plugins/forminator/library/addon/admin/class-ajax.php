<?php
/** @noinspection PhpIncludeInspection */
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Addon_Admin_Ajax
 * Available ajax action for interacting with forminator addons
 *
 * @since 1.1
 */
class Forminator_Addon_Admin_Ajax {

	/**
	 * Default nonce action
	 *
	 * @since 1.1
	 * @var string
	 */
	private static $_nonce_action = 'forminator_addon_action';

	/**
	 * Current Nonce
	 *
	 * @since 1.1
	 * @var string
	 */
	private $_nonce = '';

	/**
	 * Current Instance
	 *
	 * @since 1.1
	 * @var self
	 */
	private static $_instance = null;

	/**
	 * Singleton
	 *
	 * @since 1.1
	 * @return Forminator_Addon_Admin_Ajax
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Semaphore to avoid wp_ajax hook called multiple times
	 *
	 * @var bool
	 */
	private static $is_ajax_hooked = false;

	/**
	 * Define actions and its callback
	 *
	 * @since 1.1
	 * Forminator_Addon_Admin_Ajax constructor.
	 */
	public function __construct() {
		if ( ! self::$is_ajax_hooked ) {
			add_action( 'wp_ajax_forminator_addon_get_addons', array( $this, 'get_addons' ) );
			add_action( 'wp_ajax_forminator_addon_deactivate', array( $this, 'deactivate' ) );
			add_action( 'wp_ajax_forminator_addon_settings', array( $this, 'settings' ) );

			add_action( 'wp_ajax_forminator_addon_get_form_addons', array( $this, 'get_form_addons' ) );
			add_action( 'wp_ajax_forminator_addon_form_settings', array( $this, 'form_settings' ) );
			add_action( 'wp_ajax_forminator_addon_form_deactivate', array( $this, 'form_deactivate' ) );

			add_action( 'wp_ajax_forminator_addon_get_poll_addons', array( $this, 'get_poll_addons' ) );
			add_action( 'wp_ajax_forminator_addon_poll_settings', array( $this, 'poll_settings' ) );
			add_action( 'wp_ajax_forminator_addon_poll_deactivate', array( $this, 'poll_deactivate' ) );

			add_action( 'wp_ajax_forminator_addon_get_quiz_addons', array( $this, 'get_quiz_addons' ) );
			add_action( 'wp_ajax_forminator_addon_quiz_settings', array( $this, 'quiz_settings' ) );
			add_action( 'wp_ajax_forminator_addon_quiz_deactivate', array( $this, 'quiz_deactivate' ) );
			self::$is_ajax_hooked = true;
		}
	}

	/**
	 * Validate Ajax request
	 *
	 * @since 1.1
	 */
	private function validate_ajax() {
		if ( ! forminator_is_user_allowed() || ! check_ajax_referer( self::$_nonce_action, false, false ) ) {
			$this->send_json_errors( __( 'Invalid request, you are not allowed to do that action.', Forminator::DOMAIN ) );
		}
	}


	/**
	 * Deactivate Addon
	 *
	 * @since 1.1
	 */
	public function deactivate() {
		$this->validate_ajax();
		$data  = $this->validate_and_sanitize_fields( array( 'slug' ) );
		$slug  = $data['slug'];
		$addon = forminator_get_addon( $slug );

		forminator_maybe_attach_addon_hook( $addon );

		$deactivated = Forminator_Addon_Loader::get_instance()->deactivate_addon( $slug );
		if ( ! $deactivated ) {
			$this->send_json_errors(
				Forminator_Addon_Loader::get_instance()->get_last_error_message(),
				array(),
				array(
					'notification' => array(
						'type' => 'error',
						'text' => Forminator_Addon_Loader::get_instance()->get_last_error_message(),
					),
				)
			);
		}

		$this->send_json_success(
			__( 'Addon Deactivated', Forminator::DOMAIN ),
			array(
				'notification' => array(
					'type' => 'success',
					'text' => '<strong>' . $addon->get_title() . '</strong> ' . __( 'has been disconnected successfully.' ),
				),
			)
		);
	}// @codeCoverageIgnore

	/**
	 * Get / Save settings
	 *
	 * @since 1.1
	 */
	public function settings() {
		$this->validate_ajax();
		$sanitized_post_data = $this->validate_and_sanitize_fields( array( 'slug', 'current_step', 'step' ) );
		$slug                = $sanitized_post_data['slug'];
		$step                = $sanitized_post_data['step'];
		$current_step        = $sanitized_post_data['current_step'];
		$form_id             = 0;
		if ( isset( $sanitized_post_data['form_id'] ) ) {
			$form_id = $sanitized_post_data['form_id'];
			unset( $sanitized_post_data['form_id'] );
		}
		$addon = $this->validate_addon_from_slug( $slug );

		if ( ! $addon->is_settings_available() ) {
			$this->send_json_errors( __( 'This Addon does not have settings available', Forminator::DOMAIN ) );
		}

		forminator_maybe_attach_addon_hook( $addon );

		unset( $sanitized_post_data['slug'] );
		unset( $sanitized_post_data['step'] );
		unset( $sanitized_post_data['current_step'] );

		$wizard = $addon->get_settings_wizard( $sanitized_post_data, $form_id, $current_step, $step );

		$this->send_json_success(
			'',
			$wizard
		);

	}// @codeCoverageIgnore

	/**
	 * Get / Save form settings
	 *
	 * @since 1.1
	 */
	public function form_settings() {
		$this->validate_ajax();
		$sanitized_post_data = $this->validate_and_sanitize_fields( array( 'slug', 'step', 'form_id', 'current_step' ) );
		$slug                = $sanitized_post_data['slug'];
		$step                = $sanitized_post_data['step'];
		$current_step        = $sanitized_post_data['current_step'];
		$form_id             = $sanitized_post_data['form_id'];

		$addon = $this->validate_addon_from_slug( $slug );

		if ( ! $addon->is_form_settings_available( $form_id ) ) {
			$this->send_json_errors( __( 'This Addon does not have form settings available', Forminator::DOMAIN ) );
		}

		forminator_maybe_attach_addon_hook( $addon );

		unset( $sanitized_post_data['slug'] );
		unset( $sanitized_post_data['current_step'] );
		unset( $sanitized_post_data['step'] );
		unset( $sanitized_post_data['form_id'] );

		$wizard = $addon->get_form_settings_wizard( $sanitized_post_data, $form_id, $current_step, $step );

		$this->send_json_success(
			'',
			$wizard
		);

	}// @codeCoverageIgnore

	/**
	 * Disconnect form from addon
	 *
	 * @since 1.1
	 */
	public function form_deactivate() {
		$this->validate_ajax();
		$sanitized_post_data = $this->validate_and_sanitize_fields( array( 'slug', 'form_id' ) );
		$slug                = $sanitized_post_data['slug'];
		$form_id             = $sanitized_post_data['form_id'];

		$addon = $this->validate_addon_from_slug( $slug );

		forminator_maybe_attach_addon_hook( $addon );

		$form_settings = $addon->get_addon_form_settings( $form_id );
		if ( $form_settings instanceof Forminator_Addon_Form_Settings_Abstract ) {
			unset( $sanitized_post_data['slug'] );
			unset( $sanitized_post_data['form_id'] );

			$addon_title = $addon->get_title();

			// handling multi_id
			if ( isset( $sanitized_post_data['multi_id'] ) ) {
				$multi_id_label = '';
				$multi_ids      = $form_settings->get_multi_ids();
				foreach ( $multi_ids as $key => $multi_id ) {
					if ( isset( $multi_id['id'] ) && $multi_id['label'] ) {
						if ( $multi_id['id'] === $sanitized_post_data['multi_id'] ) {
							$multi_id_label = $multi_id['label'];
							break;
						}
					}
				}

				if ( ! empty( $multi_id_label ) ) {
					$addon_title .= ' [' . $multi_id_label . '] ';
				}
			}

			$form_settings->disconnect_form( $sanitized_post_data );

			$this->send_json_success(
				/* translators: ... */
				sprintf( __( 'Successfully disconnected %1$s from this form', Forminator::DOMAIN ), $addon->get_title() ),
				array(
					'notification' => array(
						'type' => 'success',
						'text' => '<strong>' . $addon_title . '</strong> ' . __( 'has been deactivated successfully.' ),
					),
				)
			);
		} else {
			$this->send_json_errors(
				/* translators: ... */
				sprintf( __( 'Failed to disconnect %1$s from this form', Forminator::DOMAIN ), $addon->get_title() ),
				array(),
				array(
					'notification' => array(
						'type' => 'error',
						'text' => '<strong>' . $addon->get_title() . '</strong> ' . __( 'has been failed to deactivate.' ),
					),
				)
			);
		}

	}

	/**
	 * Get Addons list, grouped by connected status
	 *
	 * @since 1.1
	 */
	public function get_addons() {
		$this->validate_ajax();
		/** @noinspection PhpUnusedLocalVariableInspection */
		$addons = forminator_get_registered_addons_grouped_by_connected();

		ob_start();

		/** @noinspection PhpIncludeInspection */
		include_once forminator_plugin_dir() . 'admin/views/integrations/page-content.php';

		$html = ob_get_clean();

		$this->send_json_success(
			'',
			$html
		);

	}

	/**
	 * Get Addons List, grouped by connected status with form
	 *
	 * @since 1.1
	 */
	public function get_form_addons() {
		$this->validate_ajax();
		$sanitized_post_data = $this->validate_and_sanitize_fields( array( 'form_id' ) );
		$form_id             = $sanitized_post_data['form_id'];
		/** @noinspection PhpUnusedLocalVariableInspection */
		$addons = forminator_get_registered_addons_grouped_by_form_connected( $form_id );

		ob_start();

		require_once forminator_plugin_dir() . 'admin/views/integrations/form-content.php';

		$html = ob_get_clean();

		$this->send_json_success(
			'',
			$html
		);
	}

	/**
	 * Generate nonce
	 *
	 * @since 1.1
	 */
	public function generate_nonce() {
		$this->_nonce = wp_create_nonce( self::$_nonce_action );
	}

	/**
	 * Get current generated nonce
	 *
	 * @since 1.1
	 * @return string
	 */
	public function get_nonce() {
		return $this->_nonce;
	}

	/**
	 * Send Json Success to client
	 *
	 * @since 1.1
	 *
	 * @param string $message
	 * @param array  $additional_data
	 */
	private function send_json_success( $message = '', $additional_data = array() ) {
		wp_send_json_success(
			array(
				'message' => $message,
				'data'    => $additional_data,
				'nonce'   => $this->_nonce,
			)
		);
	}// @codeCoverageIgnore

	/**
	 * Send Json Error to client
	 *
	 * @since 1.1
	 *
	 * @param string $message
	 * @param array  $errors
	 * @param array  $additional_data
	 */
	private function send_json_errors( $message = '', $errors = array(), $additional_data = array() ) {
		wp_send_json_error(
			array(
				'message' => $message,
				'errors'  => $errors,
				'data'    => $additional_data,
				'nonce'   => $this->_nonce,
			)
		);
	}// @codeCoverageIgnore


	/**
	 * Validate required fields, and sanitized post data
	 *
	 * @since 1.1
	 *
	 * @param array $required_fields
	 *
	 * @return mixed
	 */
	private function validate_and_sanitize_fields( $required_fields = array() ) {
		$post_data = $_REQUEST['data']; // phpcs:ignore -- already validated

		//for serialized data or form
		if ( ! is_array( $post_data ) && is_string( $post_data ) ) {
			$post_string = $post_data;
			$post_data   = array();
			wp_parse_str( $post_string, $post_data );
		}

		$errors = array();
		foreach ( $required_fields as $key => $required_field ) {
			if ( ! isset( $post_data[ $required_field ] ) ) {
				/* translators: ... */
				$errors[] = sprintf( __( 'Field %s is required', Forminator::DOMAIN ), $required_field );
				continue;
			}
		}

		if ( ! empty( $errors ) ) {
			$this->send_json_errors( __( 'Please check your form.', Forminator::DOMAIN ), $errors );
		}

		// TODO: sanitize
		foreach ( $post_data as $key => $post_datum ) {
			// sanitize here, every request will sanitized here,
			// so we dont need to sanitize it again on other methods, unless need special treatment
			$post_data[ $key ] = $post_datum;
		}

		return $post_data;
	}


	/**
	 * Validate addon from slug
	 *
	 * @since 1.5.2
	 *
	 * @param $slug
	 *
	 * @return Forminator_Addon_Abstract
	 */
	private function validate_addon_from_slug( $slug ) {
		$addon = forminator_get_addon( $slug );

		if ( ! $addon || ! $addon instanceof Forminator_Addon_Abstract ) {
			$this->send_json_errors(
				__( 'Addon not found', Forminator::DOMAIN ),
				array(),
				array(
					'notification' => array(
						'type' => 'error',
						'text' => '<strong>' . $slug . '</strong> ' . __( 'Integration Not Found' ),
					),
				)
			);
		}

		return $addon;
	}

	/**
	 * Remove instance of Addon Admin Ajax
	 *
	 * @since 1.1
	 */
	public static function remove_instance() {
		if ( ! is_null( self::$_instance ) ) {

			remove_action( 'wp_ajax_forminator_addon_get_addons', array( self::$_instance, 'get_addons' ) );
			remove_action( 'wp_ajax_forminator_addon_settings', array( self::$_instance, 'settings' ) );
			remove_action( 'wp_ajax_forminator_addon_deactivate', array( self::$_instance, 'deactivate' ) );

			remove_action( 'wp_ajax_forminator_addon_get_form_addons', array( self::$_instance, 'get_form_addons' ) );
			remove_action( 'wp_ajax_forminator_addon_form_settings', array( self::$_instance, 'form_settings' ) );
			remove_action( 'wp_ajax_forminator_addon_form_deactivate', array( self::$_instance, 'form_deactivate' ) );

			remove_action( 'wp_ajax_forminator_addon_get_poll_addons', array( self::$_instance, 'get_poll_addons' ) );
			remove_action( 'wp_ajax_forminator_addon_poll_settings', array( self::$_instance, 'poll_settings' ) );
			remove_action( 'wp_ajax_forminator_addon_poll_deactivate', array( self::$_instance, 'poll_deactivate' ) );

			remove_action( 'wp_ajax_forminator_addon_get_quiz_addons', array( self::$_instance, 'get_quiz_addons' ) );
			remove_action( 'wp_ajax_forminator_addon_quiz_settings', array( self::$_instance, 'quiz_settings' ) );
			remove_action( 'wp_ajax_forminator_addon_quiz_deactivate', array( self::$_instance, 'quiz_deactivate' ) );

			self::$is_ajax_hooked = false;
			self::$_instance      = null;
		}
	}

	/**
	 * Get Addons List, grouped by connected status with poll
	 *
	 * @since 1.1
	 */
	public function get_poll_addons() {
		$this->validate_ajax();
		$sanitized_post_data = $this->validate_and_sanitize_fields( array( 'poll_id' ) );
		$poll_id             = $sanitized_post_data['poll_id'];
		/** @noinspection PhpUnusedLocalVariableInspection */
		$addons = forminator_get_registered_addons_grouped_by_poll_connected( $poll_id );

		ob_start();

		require_once forminator_plugin_dir() . 'admin/views/integrations/poll-content.php';

		$html = ob_get_clean();

		$this->send_json_success(
			'',
			$html
		);
	}

	/**
	 * Get / Save poll settings
	 *
	 * @since 1.6.1
	 */
	public function poll_settings() {
		$this->validate_ajax();
		$sanitized_post_data = $this->validate_and_sanitize_fields( array( 'slug', 'step', 'poll_id', 'current_step' ) );
		$slug                = $sanitized_post_data['slug'];
		$step                = $sanitized_post_data['step'];
		$current_step        = $sanitized_post_data['current_step'];
		$poll_id             = $sanitized_post_data['poll_id'];

		$addon = $this->validate_addon_from_slug( $slug );

		if ( ! $addon->is_poll_settings_available( $poll_id ) ) {
			$this->send_json_errors( __( 'This Addon does not have poll settings available', Forminator::DOMAIN ) );
		}

		forminator_maybe_attach_addon_hook( $addon );

		unset( $sanitized_post_data['slug'] );
		unset( $sanitized_post_data['current_step'] );
		unset( $sanitized_post_data['step'] );
		unset( $sanitized_post_data['poll_id'] );

		$wizard = $addon->get_poll_settings_wizard( $sanitized_post_data, $poll_id, $current_step, $step );

		$this->send_json_success(
			'',
			$wizard
		);

	}// @codeCoverageIgnore

	/**
	 * Disconnect poll from addon
	 *
	 * @since 1.6.1
	 */
	public function poll_deactivate() {
		$this->validate_ajax();
		$sanitized_post_data = $this->validate_and_sanitize_fields( array( 'slug', 'poll_id' ) );
		$slug                = $sanitized_post_data['slug'];
		$poll_id             = $sanitized_post_data['poll_id'];

		$addon = $this->validate_addon_from_slug( $slug );

		forminator_maybe_attach_addon_hook( $addon );

		$poll_settings = $addon->get_addon_poll_settings( $poll_id );
		if ( $poll_settings instanceof Forminator_Addon_Poll_Settings_Abstract ) {
			unset( $sanitized_post_data['slug'] );
			unset( $sanitized_post_data['poll_id'] );

			$addon_title = $addon->get_title();

			// handling multi_id
			if ( isset( $sanitized_post_data['multi_id'] ) ) {
				$multi_id_label = '';
				$multi_ids      = $poll_settings->get_multi_ids();
				foreach ( $multi_ids as $key => $multi_id ) {
					if ( isset( $multi_id['id'] ) && $multi_id['label'] ) {
						if ( $multi_id['id'] === $sanitized_post_data['multi_id'] ) {
							$multi_id_label = $multi_id['label'];
							break;
						}
					}
				}

				if ( ! empty( $multi_id_label ) ) {
					$addon_title .= ' [' . $multi_id_label . '] ';
				}
			}

			$poll_settings->disconnect_poll( $sanitized_post_data );

			$this->send_json_success(
				/* translators: ... */
				sprintf( __( 'Successfully disconnected %1$s from this poll', Forminator::DOMAIN ), $addon->get_title() ),
				array(
					'notification' => array(
						'type' => 'success',
						'text' => '<strong>' . $addon_title . '</strong> ' . __( 'Successfully disconnected from this poll' ),
					),
				)
			);
		} else {
			$this->send_json_errors(
				/* translators: ... */
				sprintf( __( 'Failed to disconnect %1$s from this poll', Forminator::DOMAIN ), $addon->get_title() ),
				array(),
				array(
					'notification' => array(
						'type' => 'error',
						'text' => '<strong>' . $addon->get_title() . '</strong> ' . __( 'Failed to disconnected from this poll' ),
					),
				)
			);
		}

	}

	/**
	 * Get Addons List, grouped by connected status with quiz
	 *
	 * @since 1.6.2
	 */
	public function get_quiz_addons() {
		$this->validate_ajax();
		$sanitized_post_data = $this->validate_and_sanitize_fields( array( 'quiz_id' ) );
		$quiz_id             = $sanitized_post_data['quiz_id'];
		/** @noinspection PhpUnusedLocalVariableInspection */
		$addons = forminator_get_registered_addons_grouped_by_quiz_connected( $quiz_id );

		ob_start();

		require_once forminator_plugin_dir() . 'admin/views/integrations/quiz-content.php';

		$html = ob_get_clean();

		$this->send_json_success(
			'',
			$html
		);
	}

	/**
	 * Get / Save quiz settings
	 *
	 * @since 1.6.2
	 */
	public function quiz_settings() {
		$this->validate_ajax();
		$sanitized_post_data = $this->validate_and_sanitize_fields( array( 'slug', 'step', 'quiz_id', 'current_step' ) );
		$slug                = $sanitized_post_data['slug'];
		$step                = $sanitized_post_data['step'];
		$current_step        = $sanitized_post_data['current_step'];
		$quiz_id             = $sanitized_post_data['quiz_id'];

		$addon = $this->validate_addon_from_slug( $slug );

		if ( ! $addon->is_quiz_settings_available( $quiz_id ) ) {
			$this->send_json_errors( __( 'This Addon does not have quiz settings available', Forminator::DOMAIN ) );
		}

		forminator_maybe_attach_addon_hook( $addon );

		unset( $sanitized_post_data['slug'] );
		unset( $sanitized_post_data['current_step'] );
		unset( $sanitized_post_data['step'] );
		unset( $sanitized_post_data['quiz_id'] );

		$wizard = $addon->get_quiz_settings_wizard( $sanitized_post_data, $quiz_id, $current_step, $step );

		$this->send_json_success(
			'',
			$wizard
		);

	}// @codeCoverageIgnore

	/**
	 * Disconnect quiz from addon
	 *
	 * @since 1.6.2
	 */
	public function quiz_deactivate() {
		$this->validate_ajax();
		$sanitized_post_data = $this->validate_and_sanitize_fields( array( 'slug', 'quiz_id' ) );
		$slug                = $sanitized_post_data['slug'];
		$quiz_id             = $sanitized_post_data['quiz_id'];

		$addon = $this->validate_addon_from_slug( $slug );

		forminator_maybe_attach_addon_hook( $addon );

		$quiz_settings = $addon->get_addon_quiz_settings( $quiz_id );
		if ( $quiz_settings instanceof Forminator_Addon_Quiz_Settings_Abstract ) {
			unset( $sanitized_post_data['slug'] );
			unset( $sanitized_post_data['quiz_id'] );

			$addon_title = $addon->get_title();

			// handling multi_id
			if ( isset( $sanitized_post_data['multi_id'] ) ) {
				$multi_id_label = '';
				$multi_ids      = $quiz_settings->get_multi_ids();
				foreach ( $multi_ids as $key => $multi_id ) {
					if ( isset( $multi_id['id'] ) && $multi_id['label'] ) {
						if ( $multi_id['id'] === $sanitized_post_data['multi_id'] ) {
							$multi_id_label = $multi_id['label'];
							break;
						}
					}
				}

				if ( ! empty( $multi_id_label ) ) {
					$addon_title .= ' [' . $multi_id_label . '] ';
				}
			}

			$quiz_settings->disconnect_quiz( $sanitized_post_data );

			$this->send_json_success(
				/* translators: ... */
				sprintf( __( 'Successfully disconnected %1$s from this quiz', Forminator::DOMAIN ), $addon->get_title() ),
				array(
					'notification' => array(
						'type' => 'success',
						'text' => '<strong>' . $addon_title . '</strong> ' . __( 'Successfully disconnected from this quiz' ),
					),
				)
			);
		} else {
			$this->send_json_errors(
				/* translators: ... */
				sprintf( __( 'Failed to disconnect %1$s from this quiz', Forminator::DOMAIN ), $addon->get_title() ),
				array(),
				array(
					'notification' => array(
						'type' => 'error',
						'text' => '<strong>' . $addon->get_title() . '</strong> ' . __( 'Failed to disconnected from this quiz' ),
					),
				)
			);
		}

	}

}
