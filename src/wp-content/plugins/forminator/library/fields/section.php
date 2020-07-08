<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Section
 *
 * @since 1.0
 */
class Forminator_Section extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'section';

	/**
	 * @var string
	 */
	public $type = 'section';

	/**
	 * @var int
	 */
	public $position = 20;

	/**
	 * @var string
	 */
	public $options = array();

	/**
	 * @var string
	 */
	public $category = 'standard';

	/**
	 * @var string
	 */
	public $icon = 'sui-icon-inlinecss';

	/**
	 * Forminator_Section constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'Section', Forminator::DOMAIN );
	}

	/**
	 * Field defaults
	 *
	 * @since 1.0
	 * @return array
	 */
	public function defaults() {
		return array(
			'section_title'              => __( 'Form Section', Forminator::DOMAIN ),
			'cform-section-border-style' => 'none',
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
	 * @since 1.0
	 *
	 * @param $field
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function markup( $field, $settings = array() ) {

		$this->field = $field;

		$html         = '';
		$id           = self::get_property( 'element_id', $field );
		$name         = $id;
		$id           = $id . '-field';
		$required     = self::get_property( 'required', $field, false );
		$title        = esc_html( self::get_property( 'section_title', $field ) );
		$subtitle     = esc_html( self::get_property( 'section_subtitle', $field ) );
		$type         = self::get_property( 'section_type', $field );
		$border       = self::get_property( 'section_border', $field, 'none' );
		$border_width = self::get_property( 'cform-section-border-width', $field, 1 );
		$border_color = self::get_property( 'cform-section-border-color', $field, 1 );

		$html .= '<div class="forminator-field">';

		if ( ! empty( $title ) ) {
			$title = $this->sanitize_output( $title );
			$html .= sprintf( '<h2 class="forminator-title">%s</h2>', $title );
		}

		if ( ! empty( $subtitle ) ) {
			$subtitle = $this->sanitize_output( $subtitle );
			$html    .= sprintf( '<h3 class="forminator-subtitle">%s</h3>', $subtitle );
		}

		if ( 'none' !== $border ) {

			$border_width = self::get_property( 'cform-section-border-width', $field, 1 );
			$border_color = self::get_property( 'cform-section-border-color', $field, 1 );

			$html .= sprintf(
				'<hr class="forminator-border" style="border: %s %s %s;" />',
				$border_width . 'px',
				$border,
				$border_color
			);
		}

		$html .= '</div>';

		return apply_filters( 'forminator_field_section_markup', $html, $field );
	}

	/**
	 * Return sanitized form data
	 *
	 * @since 1.0
	 *
	 * @param $content
	 *
	 * @return mixed
	 */
	public function sanitize_output( $content ) {
		return htmlentities( $content, ENT_QUOTES );
	}
}
