<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Password
 *
 * @since 1.0
 */
class Forminator_Password extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'password';

	/**
	 * @var string
	 */
	public $type = 'password';

	/**
	 * @var int
	 */
	public $position = 6;

	/**
	 * @var array
	 */
	public $options = array();

	/**
	 * @var string
	 */
	public $category = 'standard';

	/**
	 * @var bool
	 */
	public $is_input = true;

	/**
	 * @var bool
	 */
	public $has_counter = false;

	/**
	 * @var string
	 */
	public $icon = 'sui-icon-key';

	/**
	 * @var string
	 * @since 1.11
	 */
	public $confirm_prefix = 'confirm';

	/**
	 * Forminator_Text constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'Password', Forminator::DOMAIN );
	}

	/**
	 * Field defaults
	 *
	 * @since 1.0
	 * @return array
	 */
	public function defaults() {
		return array(
			'field_label'                  => __( 'Password', Forminator::DOMAIN ),
			'placeholder'                  => __( 'Enter your password', Forminator::DOMAIN ),
			'confirm-password-label'       => __( 'Confirm Password', Forminator::DOMAIN ),
			'confirm-password-placeholder' => __( 'Confirm new password', Forminator::DOMAIN ),
			'strength'                     => 'none',
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
		$providers = apply_filters( 'forminator_field_' . $this->slug . '_autofill', array(), $this->slug );

		$autofill_settings = array(
			'text' => array(
				'values' => forminator_build_autofill_providers( $providers ),
			),
		);

		return $autofill_settings;
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

		$this->field         = $field;
		$this->form_settings = $settings;

		$this->init_autofill( $settings );

		$html        = '';
		$id          = self::get_property( 'element_id', $field );
		$name        = $id;
		$ariaid      = $id;
		$id          = 'forminator-field-' . $id;
		$required    = self::get_property( 'required', $field, false );
		$ariareq     = 'false';
		$default     = self::get_property( 'default', $field, false );
		$placeholder = $this->sanitize_value( self::get_property( 'placeholder', $field ) );
		$field_type  = trim( self::get_property( 'input_type', $field ) );
		$design      = $this->get_form_style( $settings );
		$label       = self::get_property( 'field_label', $field, '' );
		$description = self::get_property( 'description', $field, '' );
		$limit       = self::get_property( 'limit', $field, 0, 'num' );
		$limit_type  = self::get_property( 'limit_type', $field, '', 'str' );
		$is_confirm  = self::get_property( 'confirm-password', $field, '', 'bool' );

		$autofill_markup = $this->get_element_autofill_markup_attr( self::get_property( 'element_id', $field ), $this->form_settings );

		if ( (bool) $required ) {
			$ariareq = 'true';
		}

		$input_text = array(
			'name'          => $name,
			'value'         => $default,
			'placeholder'   => $placeholder,
			'id'            => $id,
			'class'         => 'forminator-input forminator-name--field',
			'data-required' => $required,
			'type'          => 'password'
		);

		if ( ! empty( $default ) ) {
			$input_text['value'] = $default;
		}

		$input_text = array_merge( $input_text, $autofill_markup );

		$html .= '<div class="forminator-field">';

			$html .= self::create_input(
				$input_text,
				$label,
				'',
				$required,
				$design
			);

		$html .= '</div>';

		// Counter
		if ( ! empty( $description ) || ( ! empty( $limit ) && ! empty( $limit_type ) ) ) {

			$html .= '<div class="forminator-description forminator-description-password">';

				if ( ! empty( $description ) ) {
					$html .= $description;
				}

				if ( ( ! empty( $limit ) && ! empty( $limit_type ) ) ) {
					$html .= sprintf( '<span data-limit="%s" data-type="%s">0 / %s</span>', $limit, $limit_type, $limit );
				}

			$html .= '</div>';

		}

		//Confirm password
		if ( $is_confirm ) {
			$id          = $this->confirm_prefix . '_' . self::get_property( 'element_id', $field );
			$name        = $id;
			$id          = 'forminator-field-' . $id;

			$confirm_password_label       = self::get_property( 'confirm-password-label', $field, '' );
			$confirm_password_placeholder = self::get_property( 'confirm-password-placeholder', $field );
			$confirm_password_description = self::get_property( 'confirm-password-description', $field, '' );

			$confirm_input_text = array(
				'name'          => $name,
				'value'         => $default,
				'placeholder'   => $confirm_password_placeholder,
				'id'            => $id,
				'class'         => 'forminator-input forminator-name--field',
				'data-required' => $required,
				'type'          => 'password'
 			);

			if ( ! empty( $default ) ) {
				$confirm_input_text['value'] = $default;
			}

			$confirm_input_text = array_merge( $confirm_input_text, $autofill_markup );

			//Field 'Confirm password' is inside the separated 'forminator row'
			$html_prev_field = '</div></div>';//because there are '.forminator-row' and '.forminator-col' classes for Password field
			$html .= apply_filters( 'forminator_prev_last_tag_before_conf_password_field_markup', $html_prev_field, $field );

			$html .= '<div class="forminator-row">';
			$cols = 12;
			$html_before_conf_password_field = sprintf( '<div class="forminator-col forminator-col-%s">', $cols );

			$html .= apply_filters( 'forminator_before_conf_password_field_markup', $html_before_conf_password_field );
			$html .= '<div class="forminator-field">';

			$html .= self::create_input(
				$confirm_input_text,
				$confirm_password_label,
				'',
				$required,
				$design
			);

			$html .= '</div>';

			// Counter
			if ( ! empty( $confirm_password_description ) || ( ! empty( $limit ) && ! empty( $limit_type ) ) ) {
				$html .= '<span class="forminator-description">';
				if ( ! empty( $confirm_password_description ) ) {
					$html .= $confirm_password_description;
				}

				if ( ( ! empty( $limit ) && ! empty( $limit_type ) ) ) {
					$html .= sprintf( '<span data-limit="%s" data-type="%s">0 / %s</span>', $limit, $limit_type, $limit );
				}
				$html .= '</span>';
			}
		}

		return apply_filters( 'forminator_field_password_markup', $html, $field );
	}

	/**
	 * Calculate the password score
	 *
	 * @since 1.11
	 *
	 * @param string $password
	 *
	 * @return bool
	 */
	private function get_password_strength( $password = '' ) {
		$symbol_size = 0;
		$strlen      = mb_strlen( $password );

		if ( $strlen < 8 ) {
			return false;
		}

		if ( preg_match( '/[ 0 - 9 ] /', $password ) ) {
			$symbol_size += 10;
		}
		if ( preg_match( '/[ a - z ] /', $password ) ) {
			$symbol_size += 20;
		}
		if ( preg_match( '/[ A - Z ] /', $password ) ) {
			$symbol_size += 20;
		}
		if ( preg_match( '/[^a - zA - Z0 - 9]/', $password ) ) {
			$symbol_size += 30;
		}
		if ( preg_match( '/[=!\-@._*#&$]/', $password ) ) {
			$symbol_size += 30;
		}

		$natLog = log( pow( $symbol_size, $strlen ) );
		$score  = $natLog / log( 2 );

		return $score < 56 ? false : true;
	}

	/**
	 * Return field inline validation rules
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_rules() {
		$field       = $this->field;
		$id          = self::get_property( 'element_id', $field );
		$is_required = $this->is_required( $field );
		$has_limit   = $this->has_limit( $field );
		$rules       = '';
		$is_confirm  = self::get_property( 'confirm-password', $field, '', 'bool' );
		$is_valid    = self::get_property( 'validation', $field, 'bool' );

		$min_password_strength = self::get_property( 'strength', $field );

		if ( ! isset( $field['limit'] ) ) {
			$field['limit'] = 0;
		}

		$rules = '"' . $this->get_id( $field ) . '": {';
		if ( $is_required || $has_limit ) {
			if ( $is_required ) {
				$rules .= '"required": true,';
			}

			if ( $has_limit ) {
				if ( isset( $field['limit_type'] ) && 'characters' === trim( $field['limit_type'] ) ) {
					$rules .= '"maxlength": ' . $field['limit'] . ',';
				} else {
					$rules .= '"maxwords": ' . $field['limit'] . ',';
				}
			}
		}
		//Min password strength
		if ( isset( $min_password_strength ) && '' !== $min_password_strength && 'none' !== $min_password_strength ) {
			$rules .= '"forminatorPasswordStrength": true,';
		}
		$rules .= '},';

		if ( $is_confirm ) {
			$rules .= '"'. $this->confirm_prefix . '_' . $this->get_id( $field ) . '": {';
			if ( $is_required ) {
				$rules .= '"required": true,';
			}
			//If 'Validate' is enabled
			if ( 'true' === $is_valid ) {
				$rules .= '"equalTo": "#forminator-field-' . $this->get_id( $field ) . '",' . "\n";
			}
			$rules .= '},';
		}

		return apply_filters( 'forminator_field_text_validation_rules', $rules, $id, $field );
	}

	/**
	 * Return field inline validation errors
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_messages() {
		$field            = $this->field;
		$id               = self::get_property( 'element_id', $field );
		$is_required      = $this->is_required( $field );
		$has_limit        = $this->has_limit( $field );
		$messages         = '';
		$required_message = self::get_property( 'required_message', $field, '' );
		$is_confirm       = self::get_property( 'confirm-password', $field, '', 'bool' );
		$is_valid         = self::get_property( 'validation', $field, 'bool' );

		$min_password_strength = self::get_property( 'strength', $field );

		$messages .= '"' . $this->get_id( $field ) . '": {';
		if ( $is_required || $has_limit ) {
			if ( $is_required ) {
				$required_error = apply_filters(
					'forminator_text_field_required_validation_message',
					! empty( $required_message ) ? $required_message : __( 'Your password is required', Forminator::DOMAIN ),
					$id,
					$field
				);
				$messages       .= '"required": "' . $required_error . '",' . "\n";
			}

			if ( $has_limit ) {
				if ( isset( $field['limit_type'] ) && 'characters' === trim( $field['limit_type'] ) ) {
					$max_length_error = apply_filters(
						'forminator_text_field_characters_validation_message',
						__( 'You exceeded the allowed amount of characters. Please check again', Forminator::DOMAIN ),
						$id,
						$field
					);
					$messages         .= '"maxlength": "' . $max_length_error . '",' . "\n";
				} else {
					$max_words_error = apply_filters(
						'forminator_text_field_words_validation_message',
						__( 'You exceeded the allowed amount of words. Please check again', Forminator::DOMAIN ),
						$id,
						$field
					);
					$messages        .= '"maxwords": "' . $max_words_error . '",' . "\n";
				}
			}
		}
		//Min password strength
		if ( isset( $min_password_strength ) && '' !== $min_password_strength && 'none' !== $min_password_strength ) {
			$strength_validation_message = self::get_property( 'strength_validation_message', $field, '' );
			$min_strength_error = apply_filters(
				'forminator_text_field_min_password_strength_validation_message',
				! empty( $strength_validation_message ) ? $strength_validation_message : __( 'Your password doesn\'t meet the minimum strength requirement. We recommend using 8 or more characters with a mix of letters, numbers & symbols.', Forminator::DOMAIN ),
				$id,
				$field
			);
			$messages .= '"forminatorPasswordStrength": "' . $min_strength_error . '",' . "\n";
		}
		$messages .= '},';

		if ( $is_confirm ) {
			$required_confirm_message = self::get_property( 'required_confirm_message', $field, '' );

			$messages .= '"'. $this->confirm_prefix . '_' . $this->get_id( $field ) . '": {';
			if ( $is_required ) {
				$required_error = apply_filters(
					'forminator_confirm_password_field_required_validation_message',
					! empty( $required_confirm_message ) ? $required_confirm_message : __( 'You must confirm your chosen password', Forminator::DOMAIN ),
					$id,
					$field
				);

				$messages       .= '"required": "' . $required_error . '",' . "\n";
			}
			//If 'Validate' is enabled
			if( 'true' === $is_valid ) {
				$validation_message_not_match = self::get_property( 'validation_message', $field, '' );
				$not_match_error = apply_filters(
					'forminator_confirm_password_field_not_match_validation_message',
					! empty( $validation_message_not_match ) ? $validation_message_not_match : __( 'Your passwords don\'t match', Forminator::DOMAIN ),
					$id,
					$field
				);
				$messages .= '"equalTo": "' . $not_match_error . '",' . "\n";
			}
			$messages .= '},';
		}

		return $messages;
	}

	/**
	 * Field back-end validation
	 *
	 * @since 1.0
	 *
	 * @param array        $field
	 * @param array|string $data
	 */
	public function validate( $field, $data ) {
		$id                    = self::get_property( 'element_id', $field );
		$min_password_strength = self::get_property( 'strength', $field );
		$is_confirm            = self::get_property( 'confirm-password', $field, '', 'bool' );

		if ( ! isset( $field['limit'] ) ) {
			$field['limit'] = 0;
		}

		if ( $this->is_required( $field ) ) {
			$required_message = self::get_property( 'required_message', $field, '' );
			if ( empty( $data ) ) {
				$this->validation_message[ $id ] = apply_filters(
					'forminator_text_field_required_validation_message',
					( ! empty( $required_message ) ? $required_message : __( 'This field is required. Please enter text', Forminator::DOMAIN ) ),
					$id,
					$field
				);
			}
		}
		if ( $this->has_limit( $field ) ) {
			if ( ( isset( $field['limit_type'] ) && 'characters' === trim( $field['limit_type'] ) ) && ( strlen( $data ) > $field['limit'] ) ) {
				$this->validation_message[ $id ] = apply_filters(
					'forminator_text_field_characters_validation_message',
					__( 'You exceeded the allowed amount of characters. Please check again', Forminator::DOMAIN ),
					$id,
					$field
				);
			} elseif ( ( isset( $field['limit_type'] ) && 'words' === trim( $field['limit_type'] ) ) ) {
				$words = preg_split( "/\s+/", $data );
				if ( is_array( $words ) && count( $words ) > $field['limit'] ) {
					$this->validation_message[ $id ] = apply_filters(
						'forminator_text_field_words_validation_message',
						__( 'You exceeded the allowed amount of words. Please check again', Forminator::DOMAIN ),
						$id,
						$field
					);
				}
			}
		}
		if ( isset( $min_password_strength ) && '' !== $min_password_strength && 'none' !== $min_password_strength ) {
			$strength_validation_message = self::get_property( 'strength_validation_message', $field, '' );
			if ( ! $this->get_password_strength( $data ) ) {
				$this->validation_message[ $id ] = apply_filters(
					'forminator_text_field_min_password_strength_validation_message',
					! empty( $strength_validation_message ) ? $strength_validation_message : __( 'Your password doesn\'t meet the minimum strength requirement. We recommend using 8 or more characters with a mix of letters, numbers & symbols.', Forminator::DOMAIN ),
					$id,
					$field
				);
			}
		}
	}

	/**
	 * Sanitize data
	 *
	 * @since 1.0.2
	 *
	 * @param array        $field
	 * @param array|string $data - the data to be sanitized
	 *
	 * @return array|string $data - the data after sanitization
	 */
	public function sanitize( $field, $data ) {
		$original_data = $data;
		// Sanitize
		$data = forminator_sanitize_field( $data );

		return apply_filters( 'forminator_field_text_sanitize', $data, $field, $original_data );
	}
}
