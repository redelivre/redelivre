<?php

/**
 * Interface Forminator_Autofill_Provider_Interface
 *
 * @since 1.0.5
 */
interface Forminator_Autofill_Provider_Interface {

	/**
	 * Get instance of the autofill provider
	 *
	 * @since 1.0.5
	 *
	 * @return self
	 */
	public static function get_instance();

	/**
	 * Check if autofill provider can be enabled
	 *
	 * @since   1.0.5
	 *
	 * @example check settings
	 *          when its false, it wont show up on select autofill value of form setting
	 *
	 * @return bool
	 */
	public function is_enabled();

	/**
	 * Check if its fillable
	 *
	 * @since   1.0.5
	 *
	 * @example when wp_get_curent_user failed, then it shouldn't be fillable
	 *
	 * @return bool
	 */
	public function is_fillable();

	/**
	 * @param $attribute
	 *
	 * @since 1.0.5
	 *
	 * @return mixed
	 */
	public function fill( $attribute );

	/**
	 * Init your fillable data here, like feching data from your server or database, etc
	 *
	 * @since 1.0.5
	 */
	public function init();

	/**
	 * Get Name of the Provider
	 *
	 * @since 1.0.5
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Get Short Name of the Provider
	 *
	 * @since 1.0.5
	 *
	 * @return string
	 */
	public function get_short_name();

	/**
	 * Get Slug of the Provider
	 *
	 * @since 1.0.5
	 *
	 * @return string
	 */
	public function get_slug();

	/**
	 * Get Attributes map that provided
	 *
	 * @since 1.0.5
	 *
	 * @return string
	 */
	public function get_attributes_map();

	/**
	 * Define what field to be hooked and what attribute will be used as auto fill provider
	 *
	 * @since   1.0.5
	 *
	 * @example {
	 *  'FIELD_TYPE_TO_HOOK' => [
	 *          'PROVIDER_SLUG.ATTRIBUTE_PROVIDER_KEY'
	 *              ],
	 *   'text' => [
	 *          // you can add multiple here
	 *          // or you can add other provider too simply by knowing its slug and attribute key
	 *          'simple.simple_text',
	 *              ],
	 *    'number' => [
	 *          'simple.simple_number',
	 *              ]
	 *
	 *
	 * ...}
	 * @return array
	 */
	public function get_attribute_to_hook();

}
