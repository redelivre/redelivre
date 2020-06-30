<?php

/**
 * PHP ceil() function aka round fractions up.
 * Expects one parameter.
 *
 * @see http://php.net/manual/en/ref.math.php
 */
class Forminator_Calculator_Symbol_Function_Ceil extends Forminator_Calculator_Symbol_Function_Abstract {

	/**
	 * @inheritdoc
	 */
	protected $identifiers = array( 'ceil' );

	/**
	 * @inheritdoc
	 * @throws Forminator_Calculator_Exception
	 */
	public function execute( $arguments ) {
		if ( 1 !== count( $arguments ) ) {
			throw new Forminator_Calculator_Exception( 'Error: Expected one argument, got ' . count( $arguments ) );
		}

		$number = $arguments[0];

		return ceil( $number );
	}

}
