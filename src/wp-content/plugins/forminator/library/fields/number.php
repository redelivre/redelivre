<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Number
 *
 * @since 1.0
 */
class Forminator_Number extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'number';

	/**
	 * @var string
	 */
	public $type = 'number';

	/**
	 * @var int
	 */
	public $position = 8;

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
	public $icon = 'sui-icon-element-number';

	/**
	 * @var bool
	 */
	public $is_calculable = true;

	/**
	 * Forminator_Number constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'Number', Forminator::DOMAIN );
	}

	/**
	 * Field defaults
	 *
	 * @since 1.0
	 * @return array
	 */
	public function defaults() {
		return apply_filters(
			'forminator_number_defaults_settings',
			array(
				'calculations' => 'true',
				'limit_min'    => 1,
				'limit_max'    => 150,
				'field_label'  => __( 'Number', Forminator::DOMAIN ),
				'placeholder'  => __( 'E.g. 10', Forminator::DOMAIN ),
			)
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
			'number' => array(
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
		$min         = 0;
		$max         = '';
		$id          = self::get_property( 'element_id', $field );
		$name        = $id;
		$id          = 'forminator-field-' . $id;
		$required    = self::get_property( 'required', $field, false );
		$ariareq     = 'false';
		$placeholder = $this->sanitize_value( self::get_property( 'placeholder', $field ) );
		$value       = esc_html( self::get_post_data( $name, self::get_property( 'default_value', $field ) ) );
		$label       = esc_html( self::get_property( 'field_label', $field, '' ) );
		$description = esc_html( self::get_property( 'description', $field, '' ) );
		$design      = $this->get_form_style( $settings );
		$min         = esc_html( self::get_property( 'limit_min', $field, false ) );
		$max         = esc_html( self::get_property( 'limit_max', $field, false ) );

		if ( (bool) $required ) {
			$ariareq = 'true';
		}

		// Check if Pre-fill parameter used
		if ( $this->has_prefill( $field ) ) {
			// We have pre-fill parameter, use its value or $value
			$value = $this->get_prefill( $field, $value );
		}

		$number_attr = array(
			'type'          => 'number',
			'name'          => $name,
			'value'         => $value,
			'placeholder'   => $placeholder,
			'id'            => $id,
			'class'         => 'forminator-input forminator-number--field',
			'pattern'       => '^\-?\d*([\.\,]\d+)?',
			'inputmode'     => 'numeric',
			'data-required' => $required,
			'aria-required' => $ariareq,
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
				$design
			);

		$html .= '</div>';

		return apply_filters( 'forminator_field_number_markup', $html, $id, $required, $placeholder, $value );
	}

	/**
	 * Return field inline validation rules
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_rules() {
		$field = $this->field;
		$id    = self::get_property( 'element_id', $field );
		$min   = self::get_property( 'limit_min', $field, false );
		$max   = self::get_property( 'limit_max', $field, false );

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

		return apply_filters( 'forminator_field_number_validation_rules', $rules, $id, $field );
	}

	/**
	 * Return field inline validation errors
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_messages() {
		$field = $this->field;
		$min   = self::get_property( 'limit_min', $field, false );
		$max   = self::get_property( 'limit_max', $field, false );

		$messages = '"' . $this->get_id( $field ) . '": {' . "\n";

		if ( $this->is_required( $field ) ) {
			$required_validation_message = self::get_property( 'required_message', $field, __( 'This field is required. Please enter number', Forminator::DOMAIN ) );
			$required_validation_message = apply_filters(
				'forminator_field_number_required_validation_message',
				$required_validation_message,
				$field
			);
			$messages                   .= '"required": "' . forminator_addcslashes( $required_validation_message ) . '",' . "\n";
		}

		$number_validation_message = apply_filters(
			'forminator_field_number_number_validation_message',
			__( 'This is not valid number', Forminator::DOMAIN ),
			$field
		);
		$messages                 .= '"number": "' . forminator_addcslashes( $number_validation_message ) . '",' . "\n";

		if ( $min ) {
			$min_validation_message = apply_filters(
				'forminator_field_number_min_validation_message',
				__( 'Please enter a value greater than or equal to {0}.', Forminator::DOMAIN ),
				$field
			);
			$messages              .= '"min": "' . forminator_addcslashes( $min_validation_message ) . '",' . "\n";
		}
		if ( $max ) {
			$max_validation_message = apply_filters(
				'forminator_field_number_max_validation_message',
				__( 'Please enter a value less than or equal to {0}.', Forminator::DOMAIN ),
				$field
			);
			$messages              .= '"max": "' . forminator_addcslashes( $max_validation_message ) . '",' . "\n";
		}

		$messages .= '},' . "\n";

		return apply_filters( 'forminator_field_number_validation_message', $messages, $field );
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
		$id  = self::get_property( 'element_id', $field );
		$max = self::get_property( 'limit_max', $field, $data );
		$min = self::get_property( 'limit_min', $field, $data );

		$max     = trim( $max );
		$min     = trim( $min );
		$max_len = strlen( $max );
		$min_len = strlen( $min );

		if ( $this->is_required( $field ) ) {

			if ( empty( $data ) && '0' !== $data ) {
				$require_message                 = self::get_property( 'required_message', $field, '' );
				$required_validation_message     = ! empty( $require_message ) ? $require_message : __( 'This field is required. Please enter number', Forminator::DOMAIN );
				$this->validation_message[ $id ] = apply_filters(
					'forminator_field_number_required_field_validation_message',
					$required_validation_message,
					$id,
					$field,
					$data,
					$this
				);
			}
		} elseif ( ! is_numeric( $data ) && ! empty( $data ) ) {
			$this->validation_message[ $id ] = apply_filters(
				'forminator_field_number_numeric_validation_message',
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
							'forminator_field_number_max_min_validation_message',
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

		return apply_filters( 'forminator_field_number_sanitize', $data, $field, $original_data );
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
		$calculable_value = apply_filters( 'forminator_field_number_calculable_value', $calculable_value, $submitted_data, $field_settings );

		return $calculable_value;
	}
}
