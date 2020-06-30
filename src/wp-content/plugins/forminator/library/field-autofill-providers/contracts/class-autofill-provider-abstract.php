<?php

/**
 * Class Forminator_Autofill_Provider_Abstract
 * Abstraction for Autofill Provider
 *
 * @since 1.0.5
 */
abstract class Forminator_Autofill_Provider_Abstract implements Forminator_Autofill_Provider_Interface {

	/**
	 * Autofill Provider Slug
	 *
	 * @since 1.0.5
	 *
	 * @var string
	 */
	protected $_slug = 'default_provider';

	/**
	 * Autofill Provider Nice Name
	 *
	 * @since 1.0.5
	 *
	 * @var string
	 */
	protected $_name = 'Default Autofill';

	/**
	 * Autofill Provider Short Nice Name
	 *
	 * @since 1.0.5
	 *
	 * @var string
	 */
	protected $_short_name = 'Def Auto';

	/**
	 * Attribute Mapper with autofill value provided
	 *
	 * @since 1.0.5
	 *
	 * @var array
	 */
	protected $attributes_map = array();

	/**
	 * Fill the attribute
	 *
	 * @since 1.0.5
	 *
	 * @param $attribute
	 *
	 * @return mixed|string
	 */
	final public function fill( $attribute ) {
		if ( ! $this->is_fillable() ) {
			return '';
		}

		if ( in_array( $attribute, array_keys( $this->attributes_map ), true ) ) {
			$getter = $this->attributes_map[ $attribute ]['value_getter'];
			if ( is_callable( $getter ) ) {
				return call_user_func( $getter );
			}
		}

		return '';
	}

	/**
	 * Get Name of the Provider
	 * Override This to dynamically show name
	 *
	 * @since 1.0.5
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->_name;
	}

	/**
	 * Get Slug of the Provider
	 *
	 * @since 1.0.5
	 *
	 * @return string
	 */
	final public function get_slug() {
		return $this->_slug;
	}

	/**
	 * Get Short Name of the Provider
	 *
	 * @since 1.0.5
	 *
	 * @return string
	 */
	final public function get_short_name() {
		return substr( $this->_short_name, 0, 7 );
	}

	/**
	 * Get Attributes map that provided
	 *
	 * @since 1.0.5
	 *
	 * @return array
	 */
	final public function get_attributes_map() {
		return $this->attributes_map;
	}


	/**
	 * Add hook filter to forminator fields based on @see get_attribute_to_hook
	 *
	 * @since 1.0.5
	 *
	 */
	final protected function hook_to_fields() {
		$hookable_attributes = $this->get_attribute_to_hook();

		foreach ( $hookable_attributes as $field_slug => $hookable_attribute ) {
			add_filter( 'forminator_field_' . $field_slug . '_autofill', array( $this, 'attribute_hook' ), 10, 2 );
		}
	}

	/**
	 * The Filter Executed when Forminator Fields initialized its @see Forminator_Field::get_autofill_setting()
	 *
	 * @since 1.0.5
	 *
	 * @param $providers
	 * @param $field_slug
	 *
	 * @return mixed
	 */
	final public function attribute_hook( $providers, $field_slug ) {
		$hookable_attributes = $this->get_attribute_to_hook();

		if ( empty( $hookable_attributes ) ) {
			return $providers;
		}

		if ( ! isset( $hookable_attributes[ $field_slug ] ) || empty( $hookable_attributes[ $field_slug ] ) ) {
			return $providers;
		}

		foreach ( $hookable_attributes[ $field_slug ] as $hookable_attribute ) {
			// Dedupe
			if ( in_array( $hookable_attribute, $providers, true ) ) {
				continue;
			}
			array_push( $providers, $hookable_attribute );
		}

		return $providers;
	}
}
