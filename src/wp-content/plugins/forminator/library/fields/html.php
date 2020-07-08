<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Html
 *
 * @since 1.0
 */
class Forminator_Html extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'html';

	/**
	 * @var string
	 */
	public $type = 'html';

	/**
	 * @var int
	 */
	public $position = 17;

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
	public $icon = 'sui-icon-code';

	/**
	 * Forminator_Html constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'HTML', Forminator::DOMAIN );
	}

	/**
	 * Field defaults
	 *
	 * @since 1.0
	 * @return array
	 */
	public function defaults() {
		return array(
			'field_label' => __( 'HTML', Forminator::DOMAIN ),
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

		$html    = '';
		$label   = esc_html( self::get_property( 'field_label', $field ) );
		$id      = self::get_property( 'element_id', $field );
		$form_id = false;

		$html .= '<div class="forminator-field forminator-merge-tags">';

		if ( $label ) {

			$html .= sprintf(
				'<label class="forminator-label">%s</label>',
				$label
			);
		}

			// Check if form_id exist
		if ( isset( $settings['form_id'] ) ) {
			$form_id = $settings['form_id'];
		}

			$html .= forminator_replace_variables(
				self::get_property( 'variations', $field ),
				$form_id
			);

		$html .= '</div>';

		return $html;
	}
}
