<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_GdprCheckbox
 *
 * @since 1.0.5
 */
class Forminator_GdprCheckbox extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'gdprcheckbox';

	/**
	 * @var string
	 */
	public $type = 'gdprcheckbox';

	/**
	 * @var int
	 */
	public $position = 21;

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
	public $icon = 'sui-icon-gdpr';

	/**
	 * Forminator_GdprChecbox constructor.
	 *
	 * @since 1.0.5
	 */

	public function __construct() {
		parent::__construct();

		$this->name = __( 'GDPR Approval', Forminator::DOMAIN );
	}

	/**
	 * Field defaults
	 *
	 * @since 1.0.5
	 * @return array
	 */
	public function defaults() {
		return array(
			'required'         => 'true',
			'field_label'      => 'GDPR',
			'gdpr_description' => __( 'Yes, I agree with the <a href="#">privacy policy</a> and <a href="#">terms and conditions</a>.', Forminator::DOMAIN ),
			'required_message' => __( 'This field is required. Please check it.', Forminator::DOMAIN ),
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

	/**
	 * Field front-end markup
	 *
	 * @since 1.0.5
	 *
	 * @param $field
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function markup( $field, $settings = array() ) {

		$this->field = $field;

		$html        = '';
		$id          = self::get_property( 'element_id', $field );
		$name        = $id;
		$description = wp_kses_post( self::get_property( 'gdpr_description', $field ) );
		$id          = 'forminator-field-' . $id . '-' . uniqid();
		$label       = esc_html( self::get_property( 'field_label', $field ) );

		$html .= '<div class="forminator-field">';

		if ( $label ) {

			$html .= sprintf(
				'<label for="%s" class="forminator-label">%s %s</label>',
				$id,
				$label,
				forminator_get_required_icon()
			);
		}

			$html .= sprintf( '<label for="%s" class="forminator-checkbox">', $id );

				$html .= sprintf(
					'<input type="checkbox" name="%s" value="true" id="%s" data-required="true" aria-required="true" />',
					$name,
					$id
				);

				$html .= '<span aria-hidden="true"></span>';

				$html .= sprintf( '<span>%s</span>', $description );

			$html .= '</label>';

		$html .= '</div>';

		return apply_filters( 'forminator_field_gdprcheckbox_markup', $html, $id, $description );
	}

	/**
	 * Return field inline validation rules
	 *
	 * @since 1.0.5
	 * @return string
	 */
	public function get_validation_rules() {
		$field = $this->field;
		$id    = self::get_property( 'element_id', $field );

		$rules = '"' . $this->get_id( $field ) . '":{"required":true},';

		return apply_filters( 'forminator_field_gdprcheckbox_validation_rules', $rules, $id, $field );
	}

	/**
	 * Return field inline validation errors
	 *
	 * @since 1.0.5
	 * @return string
	 */
	public function get_validation_messages() {
		$messages         = '';
		$field            = $this->field;
		$id               = $this->get_id( $field );
		$required_message = self::get_property( 'required_message', $field, '' );
		$required_message = apply_filters(
			'forminator_gdprcheckbox_field_required_validation_message',
			( ! empty( $required_message ) ? $required_message : __( 'This field is required. Please check it.', Forminator::DOMAIN ) ),
			$id,
			$field
		);
		$messages        .= '"' . $this->get_id( $field ) . '": {"required":"' . forminator_addcslashes( $required_message ) . '"},' . "\n";

		return $messages;
	}

	/**
	 * Field back-end validation
	 *
	 * @since 1.0.5
	 *
	 * @param array        $field
	 * @param array|string $data
	 */
	public function validate( $field, $data ) {
		// value of gdpr checkbox is `string` *true*
		$id = $this->get_id( $field );
		if ( empty( $data ) || 'true' !== $data ) {
			$this->validation_message[ $id ] = apply_filters(
				'forminator_gdprcheckbox_field_required_validation_message',
				__( 'This field is required. Please check it.', Forminator::DOMAIN ),
				$id,
				$field
			);
		}
	}

	/**
	 * Sanitize data
	 *
	 * @since 1.0.5
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

		return apply_filters( 'forminator_field_gdprcheckbox_sanitize', $data, $field, $original_data );
	}
}
