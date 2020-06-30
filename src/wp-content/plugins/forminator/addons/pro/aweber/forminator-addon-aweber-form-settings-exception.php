<?php

/**
 * Class Forminator_Addon_Aweber_Form_Settings_Exception
 * Wrapper of Form Settings Aweber Exception
 *
 * @since 1.0 Aweber Addon
 */
class Forminator_Addon_Aweber_Form_Settings_Exception extends Forminator_Addon_Aweber_Exception {

	/**
	 * Holder of input exceptions
	 *
	 * @since 1.0 Aweber Addon
	 * @var array
	 */
	protected $input_exceptions = array();

	/**
	 * Forminator_Addon_Aweber_Form_Settings_Exception constructor.
	 *
	 * Useful if input_id is needed for later.
	 * If no input_id needed, use @see Forminator_Addon_Aweber_Exception
	 *
	 * @since 1.0 Aweber Addon
	 *
	 * @param string $message
	 * @param string $input_id
	 */
	public function __construct( $message = '', $input_id = '' ) {
		parent::__construct( $message, 0 );
		if ( ! empty( $input_id ) ) {
			$this->add_input_exception( $message, $input_id );
		}
	}

	/**
	 * Set exception message for an input
	 *
	 * @since 1.0 Aweber Addon
	 *
	 * @param $message
	 * @param $input_id
	 */
	public function add_input_exception( $message, $input_id ) {
		$this->input_exceptions[ $input_id ] = $message;
	}

	/**
	 * Get all input exceptions
	 *
	 * @since 1.0 Aweber Addon
	 * @return array
	 */
	public function get_input_exceptions() {
		return $this->input_exceptions;
	}

	/**
	 * Check if there is input_exceptions_is_available
	 *
	 * @since 1.0 Aweber Addon
	 * @return bool
	 */
	public function input_exceptions_is_available() {
		return count( $this->input_exceptions ) > 0;
	}
}
