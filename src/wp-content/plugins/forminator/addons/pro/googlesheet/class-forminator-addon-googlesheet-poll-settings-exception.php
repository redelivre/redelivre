<?php

/**
 * Class Forminator_Addon_Googlesheet_Form_Settings_Exception
 * Wrapper of Poll Settings Google Sheets Exception
 *
 * @since 1.6.1
 */
class Forminator_Addon_Googlesheet_Poll_Settings_Exception extends Forminator_Addon_Googlesheet_Exception {

	/**
	 * Holder of input exceptions
	 *
	 * @since 1.6.1
	 * @var array
	 */
	protected $input_exceptions = array();

	/**
	 * Forminator_Addon_Googlesheet_Poll_Settings_Exception constructor.
	 *
	 * Useful if input_id is needed for later.
	 * If no input_id needed, use @see Forminator_Addon_Googlesheet_Exception
	 *
	 * @since 1.6.1
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
	 * @since 1.6.1
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
	 * @since 1.6.1
	 * @return array
	 */
	public function get_input_exceptions() {
		return $this->input_exceptions;
	}

	/**
	 * Check if there is input_exceptions_is_available
	 *
	 * @since 1.6.1
	 * @return bool
	 */
	public function input_exceptions_is_available() {
		return count( $this->input_exceptions ) > 0;
	}

	/**
	 * Check if there is input_exception for $input_id
	 *
	 * @since 1.6.1
	 *
	 * @param $input_id
	 *
	 * @return bool
	 */
	public function input_exception_is_available( $input_id ) {
		return isset( $this->input_exceptions[ $input_id ] );
	}
}
