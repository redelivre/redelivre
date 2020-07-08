<?php
/**
 * Author: Hoang Ngo
 */

/**
 * Forminator_Form_Field_Model
 *
 * Hold Field as model
 * Heads up! this class use __get and __set magic method, use with care
 *
 * @property string $wrapper_id
 */
class Forminator_Form_Field_Model {
	/**
	 * This should be unique
	 *
	 * @var string
	 */
	public $slug;

	/**
	 * This is parent form ID, optional
	 *
	 * @int
	 */
	public $form_id;

	/**
	 * This contains all the parsed json data from frontend form
	 *
	 * @var array
	 */
	protected $raw = array();

	/**
	 * This contains all form settings for field migration
	 *
	 * @var array
	 */
	protected $form_settings = array();

	/**
	 * Forminator_Form_Field_Model constructor.
	 *
	 * @param $version
	 */
	public function __construct( $settings = null ) {
		if ( ! empty( $settings ) ) {
			$this->form_settings = $settings;
		}
	}

	/**
	 * @since 1.0
	 *
	 * @param $name
	 *
	 * @return mixed|null
	 */
	public function __get( $name ) {
		if ( property_exists( $this, $name ) ) {
			return $this->$name;
		}

		$value = isset( $this->raw[ $name ] ) ? $this->raw[ $name ] : null;
		$value = apply_filters( 'forminator_get_field_' . $this->slug, $value, $this->form_id, $name );

		return $value;
	}

	/**
	 * @since 1.0
	 *
	 * @param $name
	 * @param $value
	 */
	public function __set( $name, $value ) {
		if ( property_exists( $this, $name ) ) {
			$this->$name = $value;

			return;
		}
		$value              = apply_filters( 'forminator_set_field_' . $this->slug, $value, $this->form_id, $name );
		$this->raw[ $name ] = $value;
	}

	/**
	 * To JSON
	 *
	 * @since 1.0
	 * @return string
	 */
	public function to_json() {
		return wp_json_encode( $this->to_array() );
	}

	/**
	 * To array
	 *
	 * @since 1.0
	 * @return array
	 */
	public function to_array() {
		$data = array(
			'id'         => $this->slug,
			'element_id' => $this->slug,
			'form_id'    => $this->form_id,
		);

		return array_merge( $data, $this->raw );
	}

	/**
	 * @since 1.0
	 * @return array
	 */
	public function to_formatted_array() {
		return Forminator_Migration::migrate_field( $this->raw, $this->form_settings );
	}

	/**
	 * @since 1.0
	 * @since 1.5 add `wrapper_id` on the attribute
	 *
	 * @param $data
	 */
	public function import( $data ) {
		if ( empty( $data ) ) {
			return;
		}

		foreach ( $data as $key => $val ) {
			$key        = sanitize_key( $key ); // Attempt ti sanitize key
			$this->$key = $val;
		}

		// Add `wrapper_id` when necessary
		if ( ! isset( $this->wrapper_id ) ) {
			$wrapper_id = '';
			if ( isset( $this->form_id ) && ! empty( $this->form_id ) && false !== stripos( $this->form_id, 'wrapper-' ) ) {
				$wrapper_id = $this->form_id;
			} elseif ( isset( $this->formID ) && ! empty( $this->formID )  // phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
					&& false !== stripos( $this->formID, 'wrapper-' ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar

				// Backward compat formID
				$wrapper_id = $this->formID; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
			}

			if ( ! empty( $wrapper_id ) ) {
				$this->wrapper_id = $wrapper_id;
			}
		}
	}

	/**
	 * Get Field Label For Entry
	 *
	 * @since 1.0.3
	 *
	 * @return string
	 */
	public function get_label_for_entry() {
		$field_type = $this->__get( 'type' );
		$label      = $this->__get( 'field_label' );

		if ( empty( $label ) ) {
			$label = $this->title;
		}

		if ( empty( $label ) ) {
			$label = ucfirst( $field_type );
		}

		return $label;
	}
}
