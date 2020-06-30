<?php

/** @noinspection PhpUndefinedClassInspection */
class Forminator_Autofill_Simple extends Forminator_Autofill_Provider_Abstract {


	protected $_slug       = 'simple';
	protected $_name       = 'Simple';
	protected $_short_name = 'Simple';

	private $my_simple_data;

	/**
	 * @var Forminator_Autofill_Provider_Interface|Forminator_Autofill_Simple|null
	 */
	private static $_instance = null;


	/**
	 * @return Forminator_Autofill_Provider_Interface|Forminator_Autofill_Simple|null
	 */
	public static function get_instance() {
		if ( is_null(self::$_instance) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		$attributes_map = array(
			'simple_attribute_text'   => array(
				'name'         => __( 'Text', Forminator::DOMAIN ),
				'value_getter' => array( $this, 'get_value_simple_text' ),
			),
			'simple_attribute_number' => array(
				'name'         => __( 'Number', Forminator::DOMAIN ),
				'value_getter' => array( $this, 'get_value_simple_number' ),
			),
		);

		$this->attributes_map = $attributes_map;

		// Call this to Start attaching your autofill provider to Forminator field
		$this->hook_to_fields();

	}

	/**
	 * Define what field to be hooked and what attribute will be used as auto fill provider
	 *
	 * @example [
	 *  'FIELD_TYPE_TO_HOOK' => [
	 *          'PROVIDER_SLUG.ATTRIBUTE_PROVIDER_KEY'
	 *              ],
	 *   'text' => [
	 *          'simple.simple_text',
	 *              ],
	 *    'number' => [
	 *          'simple.simple_number',
	 *              ]
	 *
	 *
	 * ];
	 * @return array
	 */
	public function get_attribute_to_hook() {
		return array(
			'text'   => array(
				// you can add multiple here
				// or you can add other provider too! simply by knowing its slug and attribute key
				'simple.simple_attribute_text',
				'simple.simple_attribute_number',
			),
			'number' => array(
				// you can add multiple here
				'simple.simple_attribute_number',
			),

		);
	}


	/**
	 * Init your fillable data here, like feching data from your server or database, etc
	 */
	public function init() {
		$this->my_simple_data = array(
			'simple_text'   => 'I am text',
			'simple_number' => 300,
		);
	}

	/**
	 * Check if autofill provider can be enabled
	 *
	 * @example check settings or domain
	 *          when its false, it wont show up on select autofill value of form setting
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return true;
	}


	/**
	 * Check if its fillable
	 *
	 * @example when when get data from server failed, then it shouldn't be fillable
	 *
	 * @return bool
	 */
	public function is_fillable() {
		if ( ! empty( $this->my_simple_data ) ) {
			return true;
		}

		return false;
	}

	public function get_value_simple_text() {
		return $this->my_simple_data['simple_text'];
	}

	public function get_value_simple_number() {
		return $this->my_simple_data['simple_number'];
	}
}
