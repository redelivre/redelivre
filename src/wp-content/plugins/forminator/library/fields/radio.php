<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_SingleValue
 *
 * @property  array field
 * @since 1.0
 */
class Forminator_Radio extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'radio';

	/**
	 * @var string
	 */
	public $type = 'radio';

	/**
	 * @var int
	 */
	public $position = 9;

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
	public $icon = 'sui-icon-element-radio';

	public $is_calculable = true;

	/**
	 * Forminator_SingleValue constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'Radio', Forminator::DOMAIN );
	}

	/**
	 * Field defaults
	 *
	 * @since 1.0
	 * @return array
	 */
	public function defaults() {
		return array(
			'value_type'  => 'radio',
			'field_label' => __( 'Radio', Forminator::DOMAIN ),
			'layout'      => 'vertical',
			'options'     => array(
				array(
					'label' => __( 'Option 1', Forminator::DOMAIN ),
					'value' => 'one',
				),
				array(
					'label' => __( 'Option 2', Forminator::DOMAIN ),
					'value' => 'two',
				),
			),
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
			'select' => array(
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

		$this->field = $field;

		$i           = 1;
		$html        = '';
		$id          = self::get_property( 'element_id', $field );
		$name        = $id;
		$id          = 'forminator-field-' . $id;
		$required    = self::get_property( 'required', $field, false );
		$ariareq     = 'false';
		$options     = self::get_property( 'options', $field, array() );
		$value_type  = trim( $field['value_type'] ? $field['value_type'] : "multiselect" );
		$post_value  = self::get_post_data( $name, false );
		$description = self::get_property( 'description', $field, '' );
		$label       = self::get_property( 'field_label', $field, '' );
		$class      = ( 'horizontal' === self::get_property( 'layout', $field, '' ) ) ? 'forminator-radio forminator-radio-inline' : 'forminator-radio';
		$design      = $this->get_form_style( $settings );
		$calc_enabled = self::get_property( 'calculations', $field, false, 'bool' );

		$uniq_id = uniqid();

		if ( (bool) $required ) {
			$ariareq = 'true';
		}

		$html .= '<fieldset class="forminator-field" role="radiogroup">';

			if ( $label ) {
				if ( $required ) {
					$html .= sprintf( '<label class="forminator-label">%s %s</label>', $label, forminator_get_required_icon() );
				} else {
					$html .= sprintf( '<label class="forminator-label">%s</label>', $label );
				}
			}

			foreach ( $options as $option ) {

				$input_id          = $id . '-' . $i . '-' . $uniq_id;
				$value             = $option['value'] ? $option['value'] : $option['label'];
				$option_default    = isset( $option['default'] ) ? filter_var( $option['default'], FILTER_VALIDATE_BOOLEAN ) : false;
				$selected          = ( $value === $post_value || $option_default ) ? 'checked="checked"' : '';
				$calculation_value = $calc_enabled && isset( $option['calculation'] ) ? $option['calculation'] : 0.0;

                // Check if Pre-fill parameter used
                if( $this->has_prefill( $field ) ) {
                    // We have pre-fill parameter, use its value or $value
                    $prefill = $this->get_prefill( $field, false );

                    if( $prefill === $value ) {
                        $option_default = true;
                    }
                }

				$selected = $option_default ? 'checked="checked"' : '';

				$html .= '<label for="' . $input_id . '" class="' . $class . '">';

					$html .= sprintf(
						'<input type="radio" name="%s" value="%s" id="%s" data-calculation="%s" %s />',
						$name,
						$value,
						$input_id,
						$calculation_value,
						$selected
					);

					$html .= '<span aria-hidden="true"></span>';

					$html .= sprintf(
						'<span>%s</span>',
						$option['label']
					);

				$html .= '</label>';

				$i ++;

			}

			$html .= self::get_description( $description );

		$html .= '</fieldset>';

		return apply_filters( 'forminator_field_single_markup', $html, $id, $required, $options, $value_type );
	}

	/**
	 * Return field inline validation rules
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_rules() {
		$rules       = '';
		$field       = $this->field;
		$id          = self::get_property( 'element_id', $field );
		$is_required = $this->is_required( $field );

		if ( $is_required ) {
			$rules .= '"' . $this->get_id( $field ) . '": "required",';
		}

		return apply_filters( 'forminator_field_single_validation_rules', $rules, $id, $field );
	}

	/**
	 * Return field inline validation errors
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_messages() {
		$messages    = '';
		$field       = $this->field;
		$id          = self::get_property( 'element_id', $field );
		$is_required = $this->is_required( $field );


		if ( $is_required ) {
			$required_message = self::get_property( 'required_message', $field, '' );
			$required_message = apply_filters(
				'forminator_single_field_required_validation_message',
				( ! empty( $required_message ) ? $required_message : __( 'This field is required. Please select a value', Forminator::DOMAIN ) ),
				$id,
				$field
			);
			$messages         .= '"' . $this->get_id( $field ) . '": "' . forminator_addcslashes( $required_message ) . '",' . "\n";
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
		if ( $this->is_required( $field ) ) {
			$id               = self::get_property( 'element_id', $field );
			$required_message = self::get_property( 'required_message', $field, '' );
			if ( empty( $data ) && '0' !== $data ) {
				$this->validation_message[ $id ] = apply_filters(
					'forminator_single_field_required_validation_message',
					( ! empty( $required_message ) ? $required_message : __( 'This field is required. Please select a value', Forminator::DOMAIN ) ),
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

		return apply_filters( 'forminator_field_single_sanitize', $data, $field, $original_data );
	}

	/**
	 * Internal Calculable value
	 *
	 * @since 1.7
	 *
	 *
	 * @param $submitted_data
	 * @param $field_settings
	 *
	 * @return float|string
	 */
	private function calculable_value( $submitted_data, $field_settings ) {
		$enabled = self::get_property( 'calculations', $field_settings, false, 'bool' );
		if ( ! $enabled ) {
			return self::FIELD_NOT_CALCULABLE;
		}

		$sums = 0.0;

		$options = self::get_property( 'options', $field_settings, array() );

		// process as array
		$submitted_data = array( $submitted_data );

		if ( ! is_array( $submitted_data ) ) {
			return $sums;
		}

		foreach ( $options as $option ) {
			$option_value      = isset( $option['value'] ) ? $option['value'] : ( isset( $option['label'] ) ? $option['label'] : '' );
			$calculation_value = isset( $option['calculation'] ) ? $option['calculation'] : 0.0;

			// strict array compare disabled to allow non-coercion type compare
			if ( in_array( $option_value, $submitted_data ) ) {// phpcs:ignore
				// this one is selected
				$sums += floatval( $calculation_value );
			}

		}

		return floatval( $sums );
	}

	/**
	 * @since 1.7
	 * @inheritdoc
	 */
	public function get_calculable_value( $submitted_data, $field_settings ) {
		$calculable_value = $this->calculable_value( $submitted_data, $field_settings );
		/**
		 * Filter formula being used on calculable value on radio field
		 *
		 * @since 1.7
		 *
		 * @param float $calculable_value
		 * @param array $submitted_data
		 * @param array $field_settings
		 *
		 * @return string|int|float
		 */
		$calculable_value = apply_filters( "forminator_field_radio_calculable_value", $calculable_value, $submitted_data, $field_settings );

		return $calculable_value;
	}
}
