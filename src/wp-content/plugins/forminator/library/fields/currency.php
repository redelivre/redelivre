<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Currency
 *
 * @since 1.7
 */
class Forminator_Currency extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'currency';

	/**
	 * @var string
	 */
	public $type = 'currency';

	/**
	 * @var int
	 */
	public $position = 22;

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
	 * @var string
	 */
	public $icon = 'sui-icon-currency';

	/**
	 * @var bool
	 */
	public $is_calculable = true;

	/**
	 * Forminator_Currency constructor.
	 *
	 * @since 1.7
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'Currency', Forminator::DOMAIN );
	}

	/**
	 * Field defaults
	 *
	 * @since 1.7
	 * @return array
	 */
	public function defaults() {

		return apply_filters(
			'forminator_currency_defaults_settings',
			array(
				'calculations' => 'true',
				'limit_min'    => 1,
				'limit_max'    => 150,
				'currency'     => 'USD',
				'field_label'  => __( 'Currency', Forminator::DOMAIN ),
				'placeholder'  => __( 'E.g. 10', Forminator::DOMAIN ),
			)
		);
	}

	/**
	 * Autofill Setting
	 *
	 * @since 1.7
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function autofill_settings( $settings = array() ) {
		$providers = apply_filters( 'forminator_field_' . $this->slug . '_autofill', array(), $this->slug );

		$autofill_settings = array(
			'currency' => array(
				'values' => forminator_build_autofill_providers( $providers ),
			),
		);

		return $autofill_settings;
	}

	/**
	 * Create decimals pattern from decimals number
	 *
	 * @since 1.7
	 * @param integer $decimals
	 * @return mixed
	 */
	private function create_step_string( $decimals = 2 ) {
		$step = 1;

		if ( ! empty( $decimals ) ) {
			for ( $i = 1; $i < $decimals; $i++ ) {
				$step = '0' . $step;
			}

			$step = '0.' . $step;
		}

		return $step;
	}

	/**
	 * Field front-end markup
	 *
	 * @since 1.7
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
		$min         = 0;
		$max         = '';
		$id          = self::get_property( 'element_id', $field );
		$name        = $id;
		$id          = 'forminator-field-' . $id;
		$required    = self::get_property( 'required', $field, false );
		$placeholder = $this->sanitize_value( self::get_property( 'placeholder', $field ) );
		$value       = esc_html( self::get_post_data( $name, self::get_property( 'default_value', $field ) ) );
		$label       = esc_html( self::get_property( 'field_label', $field, '' ) );
		$description = esc_html( self::get_property( 'description', $field, '' ) );
		$design      = $this->get_form_style( $settings );
		$min         = esc_html( self::get_property( 'limit_min', $field, false ) );
		$max         = esc_html( self::get_property( 'limit_max', $field, false ) );
		$currency    = self::get_property( 'currency', $field, 'USD' );
		$precision   = self::get_property( 'precision', $field, 2 );
		$step        = $this->create_step_string( $precision );

		// Check if Pre-fill parameter used
		if ( $this->has_prefill( $field ) ) {
			// We have pre-fill parameter, use its value or $value
			$value = $this->get_prefill( $field, $value );
		}

		$number_attr = array(
			'type'          => 'number',
			'name'          => $name,
			'step'          => $step,
			'value'         => $value,
			'placeholder'   => $placeholder,
			'id'            => $id,
			'class'         => 'forminator-input forminator-currency',
			'data-required' => $required,
			'data-decimals' => $precision,
			'aria-required' => $required,
		);

		if ( false !== $min && is_numeric( $min ) ) {
			$number_attr['min'] = $min;
		}

		if ( false !== $max && is_numeric( $max ) ) {
			$number_attr['max'] = $max;
		}

		$autofill_markup = $this->get_element_autofill_markup_attr( self::get_property( 'element_id', $field ), $this->form_settings );
		$number_attr     = array_merge( $number_attr, $autofill_markup );

		$html .= '<div class="forminator-field">';

			$html .= self::create_input(
				$number_attr,
				$label,
				$description,
				$required,
				$design,
				array(
					'<div class="forminator-input-with-suffix">',
					sprintf( '<span class="forminator-suffix">%s</span></div>', $currency ),
					'',
				)
			);

		$html .= '</div>';

		return apply_filters( 'forminator_field_currency_markup', $html, $id, $required, $placeholder, $value );
	}

	/**
	 * Return field inline validation rules
	 *
	 * @since 1.7
	 * @return string
	 */
	public function get_validation_rules() {
		$field = $this->field;
		$id    = self::get_property( 'element_id', $field );
		$min   = esc_html( self::get_property( 'limit_min', $field, false ) );
		$max   = esc_html( self::get_property( 'limit_max', $field, false ) );

		$rules = '"' . $this->get_id( $field ) . '": {';

		if ( $this->is_required( $field ) ) {
			$rules .= '"required": true,';
		}

		$rules .= '"number": true,';

		if ( false !== $min && is_numeric( $min ) ) {
			$rules .= '"min": ' . $min . ',';
		}
		if ( false !== $max && is_numeric( $max ) ) {
			$rules .= '"max": ' . $max . ',';
		}

		$rules .= '},';

		return apply_filters( 'forminator_field_currency_validation_rules', $rules, $id, $field );
	}

	/**
	 * Return field inline validation errors
	 *
	 * @since 1.7
	 * @return string
	 */
	public function get_validation_messages() {
		$field = $this->field;
		$min   = esc_html( self::get_property( 'limit_min', $field, false ) );
		$max   = esc_html( self::get_property( 'limit_max', $field, false ) );

		$messages = '"' . $this->get_id( $field ) . '": {' . "\n";

		if ( $this->is_required( $field ) ) {
			$required_validation_message = self::get_property( 'required_message', $field, __( 'This field is required. Please enter number', Forminator::DOMAIN ) );
			$required_validation_message = apply_filters(
				'forminator_field_currency_required_validation_message',
				$required_validation_message,
				$field
			);
			$messages                   .= '"required": "' . forminator_addcslashes( $required_validation_message ) . '",' . "\n";
		}

		$number_validation_message = apply_filters(
			'forminator_field_currency_number_validation_message',
			__( 'This is not valid number', Forminator::DOMAIN ),
			$field
		);
		$messages                 .= '"number": "' . forminator_addcslashes( $number_validation_message ) . '",' . "\n";

		if ( $min ) {
			$min_validation_message = apply_filters(
				'forminator_field_currency_min_validation_message',
				__( 'Please enter a value greater than or equal to {0}.', Forminator::DOMAIN ),
				$field
			);
			$messages              .= '"min": "' . forminator_addcslashes( $min_validation_message ) . '",' . "\n";
		}
		if ( $max ) {
			$max_validation_message = apply_filters(
				'forminator_field_currency_max_validation_message',
				__( 'Please enter a value less than or equal to {0}.', Forminator::DOMAIN ),
				$field
			);
			$messages              .= '"max": "' . forminator_addcslashes( $max_validation_message ) . '",' . "\n";
		}

		$messages .= '},' . "\n";

		return apply_filters( 'forminator_field_currency_validation_message', $messages, $field );
	}

	/**
	 * Field back-end validation
	 *
	 * @since 1.7
	 *
	 * @param array        $field
	 * @param array|string $data
	 */
	public function validate( $field, $data ) {
		$id  = self::get_property( 'element_id', $field );
		$max = self::get_property( 'limit_max', $field, $data );
		$min = self::get_property( 'limit_min', $field, $data );
		$max     = trim( $max );
		$min     = trim( $min );
		$max_len = strlen( $max );
		$min_len = strlen( $min );

		$max_len = strlen( $max );
		$min_len = strlen( $min );

		if ( $this->is_required( $field ) ) {

			if ( empty( $data ) && '0' !== $data ) {
				$required_validation_message     = self::get_property( 'required_message', $field, __( 'This field is required. Please enter number', Forminator::DOMAIN ) );
				$this->validation_message[ $id ] = apply_filters(
					'forminator_field_currency_required_field_validation_message',
					$required_validation_message,
					$id,
					$field,
					$data,
					$this
				);
			}
		}

		if ( ! is_numeric( $data ) && ! empty( $data ) ) {
			$this->validation_message[ $id ] = apply_filters(
				'forminator_field_currency_numeric_validation_message',
				__( 'Only numbers allowed', Forminator::DOMAIN ),
				$id,
				$field,
				$data,
				$this
			);
		} else {
			if ( ! empty( $data ) ) {
				$data = intval( $data );
				$min  = intval( $min );
				$max  = intval( $max );
				//Note : do not compare max or min if that settings field is blank string ( not zero )
				if ( ( $min_len !== 0 && $data < $min ) || ( $max_len !== 0 && $data > $max ) ) {
					$this->validation_message[ $id ] = sprintf(
						apply_filters(
							'forminator_field_currency_max_min_validation_message',
							/* translators: ... */
							__( 'The number should be less than %1$d and greater than %2$d', Forminator::DOMAIN ),
							$id,
							$field,
							$data
						),
						$max,
						$min
					);
				}
			}
		}
	}

	/**
	 * Sanitize data
	 *
	 * @since 1.7
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

		return apply_filters( 'forminator_field_currency_sanitize', $data, $field, $original_data );
	}

	/**
	 * Internal calculable value
	 *
	 * @since 1.7
	 *
	 * @param array|mixed $submitted_data
	 * @param array       $field_settings
	 *
	 * @return float
	 */
	private function calculable_value( $submitted_data, $field_settings ) {
		$enabled = self::get_property( 'calculations', $field_settings, false, 'bool' );
		if ( ! $enabled ) {
			return self::FIELD_NOT_CALCULABLE;
		}

		return floatval( $submitted_data );
	}

	/**
	 * @since 1.7
	 * @inheritdoc
	 */
	public function get_calculable_value( $submitted_data, $field_settings ) {
		$calculable_value = $this->calculable_value( $submitted_data, $field_settings );
		/**
		 * Filter formula being used on calculable value on number field
		 *
		 * @since 1.7
		 *
		 * @param float $calculable_value
		 * @param array $submitted_data
		 * @param array $field_settings
		 *
		 * @return string|int|float
		 */
		$calculable_value = apply_filters( 'forminator_field_currency_calculable_value', $calculable_value, $submitted_data, $field_settings );

		return $calculable_value;
	}
}
