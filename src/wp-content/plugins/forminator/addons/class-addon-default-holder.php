<?php


/**
 * Class Forminator_Addon_Default_Holder
 * Placeholder for nonexistent PRO Addon
 *
 * @since 1.1
 */
class Forminator_Addon_Default_Holder extends Forminator_Addon_Abstract {

	private static $_instance = null;

	protected $_slug                   = '';
	protected $_version                = '1.0';
	protected $_min_forminator_version = PHP_INT_MAX; // make it un-activable
	protected $_short_title            = '';
	protected $_title                  = '';
	protected $_url                    = '';
	protected $_image                  = '';
	protected $_image_x2               = '';
	protected $_icon                   = '';
	protected $_icon_x2                = '';
	protected $_full_path              = '';

	/**
	 * Get Instance
	 *
	 * @since 1.1
	 * @return self|null
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();// @codeCoverageIgnore
		}

		return self::$_instance;
	}


	/**
	 * Dynamically set fields form array
	 *
	 * @since 1.1
	 *
	 * @param $properties
	 *
	 * @return $this
	 */
	public function from_array( $properties ) {
		foreach ( $properties as $field => $value ) {
			if ( property_exists( $this, $field ) ) {
				$this->$field = $value;
			}
		}

		return $this;
	}

	/**
	 * Mark non existent addon as not connected always
	 *
	 * @since 1.1
	 * @return bool
	 */
	public function is_connected() {
		return false;
	}

	/**
	 * Mark non existent addon as form not connected always
	 *
	 * @since 1.1
	 *
	 * @param $form_id
	 *
	 * @return bool
	 */
	public function is_form_connected( $form_id ) {
		return false;
	}

	/**
	 * Make this not activable
	 *
	 * @since 1.1
	 * @return bool
	 */
	public function check_is_activable() {
		return false;
	}

	/**
	 * Flag for check if and addon connected to a poll(poll settings such as list id completed)
	 *
	 * Please apply necessary WordPress hook on the inheritance class
	 *
	 * @since   1.6.1
	 *
	 * @param $poll_id
	 *
	 * @return boolean
	 */
	public function is_poll_connected( $poll_id ) {
		return false;
	}
}
