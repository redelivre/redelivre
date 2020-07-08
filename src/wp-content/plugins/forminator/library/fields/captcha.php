<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Captcha
 *
 * @since 1.0
 */
class Forminator_Captcha extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'captcha';

	/**
	 * @var string
	 */
	public $type = 'captcha';

	/**
	 * @var int
	 */
	public $position = 16;

	/**
	 * @var array
	 */
	public $options = array();

	/**
	 * @var string
	 */
	public $category = 'standard';

	/**
	 * @var string
	 */
	public $hide_advanced = 'true';

	/**
	 * @var string
	 */
	public $icon = 'sui-icon-recaptcha';

	/**
	 * Forminator_Captcha constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'reCaptcha', Forminator::DOMAIN );
	}

	/**
	 * Field defaults
	 *
	 * @since 1.0
	 * @return array
	 */
	public function defaults() {

		return array(
			'captcha_type'            => __( 'v2_checkbox', Forminator::DOMAIN ),
			'score_threshold'         => __( '0.5', Forminator::DOMAIN ),
			'recaptcha_error_message' => __( 'reCAPTCHA verification failed. Please try again.', Forminator::DOMAIN ),
		);
	}

	/**
	 * Autofill Setting
	 *
	 * @since 1.0.5
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function autofill_settings( $settings = array() ) {
		//Unsupported Autofill
		$autofill_settings = array();

		return $autofill_settings;
	}

	public function is_invisible_recaptcha( $field ) {
		// backward
		$is_invisible = self::get_property( 'invisible_captcha', $field );
		$is_invisible = filter_var( $is_invisible, FILTER_VALIDATE_BOOLEAN );
		if ( ! $is_invisible ) {
			$type = self::get_property( 'captcha_type', $field, '' );
			if ( 'invisible' === $type || 'v3_recaptcha' === $type || 'v2_invisible' === $type ) {
				$is_invisible = true;
			}
		}

		return $is_invisible;
	}

	/**
	 * Field front-end markup
	 *
	 * @since 1.0
	 *
	 * @param $field
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function markup( $field, $settings = array() ) {

		$captcha_type  = self::get_property( 'captcha_type', $field, '' );
		$captcha_theme = self::get_property( 'captcha_theme', $field, 'light' );
		$captcha_size  = self::get_property( 'captcha_size', $field, 'normal' );

		if ( 'v2_checkbox' === $captcha_type ) {
			$key = get_option( 'forminator_captcha_key', '' );
		} elseif ( 'v2_invisible' === $captcha_type ) {
			$key = get_option( 'forminator_v2_invisible_captcha_key', '' );
		} elseif ( 'v3_recaptcha' === $captcha_type ) {
			$key = get_option( 'forminator_v3_captcha_key', '' );
		} else {
			$key = get_option( 'forminator_captcha_key', '' );
		}

		$captcha_class = 'forminator-g-recaptcha';

		if ( $this->is_invisible_recaptcha( $field ) ) {
			$captcha_size   = 'invisible';
			$captcha_class .= ' recaptcha-invisible';
		}

		// dont use .g-recaptcha class as it will rendered automatically when other plugin load recaptcha with default render
		return sprintf( '<div class="%s" data-theme="%s" data-sitekey="%s" data-size="%s"></div>', $captcha_class, $captcha_theme, $key, $captcha_size );
	}


	/**
	 * Mark Captcha unavailable when captcha key not available
	 *
	 * @since 1.0.3
	 *
	 * @param $field
	 *
	 * @return bool
	 */
	public function is_available( $field ) {
		$captcha_type = self::get_property( 'captcha_type', $field, '' );
		if ( 'v2_checkbox' === $captcha_type ) {
			$key = get_option( 'forminator_captcha_key', '' );
		} elseif ( 'v2_invisible' === $captcha_type ) {
			$key = get_option( 'forminator_v2_invisible_captcha_key', '' );
		} elseif ( 'v3_recaptcha' === $captcha_type ) {
			$key = get_option( 'forminator_v3_captcha_key', '' );
		} else {
			$key = get_option( 'forminator_captcha_key', '' );
		}

		if ( ! $key ) {
			return false;
		}

		return true;
	}

	/**
	 * Validate captcha
	 *
	 * @since 1.5.3
	 *
	 * @param array        $field
	 * @param array|string $data
	 *
	 * @return bool
	 */
	public function validate( $field, $data ) {
		$captcha_type = self::get_property( 'captcha_type', $field, '' );
		$score        = '';
		if ( 'v2_checkbox' === $captcha_type ) {
			$secret = get_option( 'forminator_captcha_secret', '' );
		} elseif ( 'v2_invisible' === $captcha_type ) {
			$secret = get_option( 'forminator_v2_invisible_captcha_secret', '' );
		} elseif ( 'v3_recaptcha' === $captcha_type ) {
			$secret = get_option( 'forminator_v3_captcha_secret', '' );
			$score  = self::get_property( 'score_threshold', $field, '' );
		} else {
			$secret = get_option( 'forminator_captcha_secret', '' );
		}
		$element_id    = self::get_property( 'element_id', $field );
		$error_message = self::get_property( 'recaptcha_error_message', $field, '' );

		$recaptcha = new Forminator_Recaptcha( $secret );
		$verify    = $recaptcha->verify( $data, null, $score );
		if ( is_wp_error( $verify ) ) {
			$invalid_captcha_message = ( ! empty( $error_message ) ? $error_message : __( 'reCAPTCHA verification failed. Please try again.', Forminator::DOMAIN ) );

			/**
			 * Filter message displayed for invalid captcha
			 *
			 * @since 1.5.3
			 *
			 * @param string   $invalid_captcha_message
			 * @param string   $element_id
			 * @param array    $field
			 * @param WP_Error $verify
			 */
			$invalid_captcha_message = apply_filters( 'forminator_invalid_captcha_message', $invalid_captcha_message, $element_id, $field, $verify );

			$this->validation_message[ $element_id ] = $invalid_captcha_message;
		}
	}
}
