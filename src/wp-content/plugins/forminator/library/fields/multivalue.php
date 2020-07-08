<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_MultiValue
 *
 * @since 1.0
 */
class Forminator_MultiValue extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'checkbox';

	/**
	 * @var string
	 */
	public $type = 'checkbox';

	/**
	 * @var int
	 */
	public $position = 10;

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
	public $icon = 'sui-icon-element-checkbox';

	public $is_calculable = true;

	/**
	 * Forminator_MultiValue constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'Checkbox', Forminator::DOMAIN );
	}

	/**
	 * Field defaults
	 *
	 * @since 1.0
	 * @return array
	 */
	public function defaults() {
		return array(
			'value_type'  => 'checkbox',
			'field_label' => __( 'Checkbox', Forminator::DOMAIN ),
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
			'checkbox' => array(
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
		$ariaid      = $id;
		$id          = 'forminator-field-' . $id;
		$uniq_id     = uniqid();
		$post_value  = self::get_post_data( $name, self::FIELD_PROPERTY_VALUE_NOT_EXIST );
		$name        = $name . '[]';
		$required    = self::get_property( 'required', $field, false );
		$options     = self::get_property( 'options', $field, array() );
		$value_type  = trim( isset( $field['value_type'] ) ? $field['value_type'] : "multiselect" );
		$description = esc_html( self::get_property( 'description', $field, '' ) );
		$label       = esc_html( self::get_property( 'field_label', $field, '' ) );
		$class       = ( 'horizontal' === self::get_property( 'layout', $field, '' ) ) ? 'forminator-checkbox forminator-checkbox-inline' : 'forminator-checkbox';
		$design      = $this->get_form_style( $settings );

		$calc_enabled = self::get_property( 'calculations', $field, false, 'bool' );

		$html .= '<div class="forminator-field">';

		if ( $label ) {
			if ( $required ) {
				$html .= sprintf( '<label class="forminator-label">%s %s</label>', $label, forminator_get_required_icon() );
			} else {
				$html .= sprintf( '<label class="forminator-label">%s</label>', $label );
			}
		}

		foreach ( $options as $option ) {
			$value             = $option['value'] ? esc_html( $option['value'] ) : esc_html( $option['label'] );
			$input_id          = $id . '-' . $i . '-' . $uniq_id;
			$option_default    = isset( $option['default'] ) ? filter_var( $option['default'], FILTER_VALIDATE_BOOLEAN ) : false;
			$calculation_value = $calc_enabled && isset( $option['calculation'] ) ? $option['calculation'] : 0.0;

			$selected = false;

			// Check if Pre-fill parameter used
			if ( $this->has_prefill( $field ) ) {
				// We have pre-fill parameter, use its value or $value
				$prefill        = $this->get_prefill( $field, false );
				$prefill_values = explode( ',', $prefill );

				if ( in_array( $value, $prefill_values ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
					$option_default = true;
				}
			}

			if ( self::FIELD_PROPERTY_VALUE_NOT_EXIST !== $post_value ) {
				if ( is_array( $post_value ) ) {
					$selected = in_array( $value, $post_value );// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				}
			} else {
				$selected = $option_default;
			}

			$selected = $selected ? 'checked="checked"' : '';

			$html .= sprintf( '<label for="%s" class="' . $class . '">', $input_id );

				$html .= sprintf(
					'<input type="checkbox" name="%s" value="%s" id="%s" data-calculation="%s" %s />',
					$name,
					$value,
					$input_id,
					$calculation_value,
					$selected
				);

				$html .= '<span aria-hidden="true"></span>';
				$html .= sprintf( '<span>%s</span>', esc_html( $option['label'] ) );

			$html .= '</label>';

			$i ++;
		}

			$html .= self::get_description( $description );

		$html .= '</div>';

		return apply_filters( 'forminator_field_multiple_markup', $html, $id, $required, $options, $value_type );
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
			$rules .= '"' . $this->get_id( $field ) . '[]": "required",';
		}

		return apply_filters( 'forminator_field_multiple_validation_rules', $rules, $id, $field );
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
			$required_message = self::get_property( 'required_message', $field, __( 'This field is required. Please select a value', Forminator::DOMAIN ) );
			$required_message = apply_filters(
				'forminator_multi_field_required_validation_message',
				$required_message,
				$id,
				$field
			);
			$messages        .= '"' . $this->get_id( $field ) . '[]": "' . forminator_addcslashes( $required_message ) . '",' . "\n";
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
			$required_message = self::get_property( 'required_message', $field, __( 'This field is required. Please select a value', Forminator::DOMAIN ) );
			if ( empty( $data ) ) {
				$this->validation_message[ $id ] = apply_filters(
					'forminator_multi_field_required_validation_message',
					$required_message,
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

		return apply_filters( 'forminator_field_multi_sanitize', $data, $field, $original_data );
	}

	/**
	 * Internal calculable value
	 *
	 * @since 1.7
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

		if ( ! is_array( $submitted_data ) ) {
			return $sums;
		}

		foreach ( $options as $option ) {
			$option_value      = ( isset( $option['value'] ) && ! empty( $option['value'] ) ) ? $option['value'] : ( isset( $option['label'] ) ? $option['label'] : '' );
			$calculation_value = isset( $option['calculation'] ) ? $option['calculation'] : 0.0;

			forminator_maybe_log( __METHOD__, $option_value, $submitted_data );

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
		 * Filter formula being used on calculable value on multi-value / checkbox field
		 *
		 * @since 1.7
		 *
		 * @param float $calculable_value
		 * @param array $submitted_data
		 * @param array $field_settings
		 *
		 * @return string|int|float
		 */
		$calculable_value = apply_filters( 'forminator_field_multi_calculable_value', $calculable_value, $submitted_data, $field_settings );

		return $calculable_value;
	}
}
