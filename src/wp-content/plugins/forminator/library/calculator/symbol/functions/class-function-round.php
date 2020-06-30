<?php

/**
 * PHP round() function aka rounds a float.
 * Expects one or three parameters. The first
 * parameter is the value to round, the second
 * is the number of decimal digits to round to.
 * third = PHP_ROUND_HALF_UP
 * It defaults to 0.
 *
 * @see http://php.net/manual/en/ref.math.php
 */
class Forminator_Calculator_Symbol_Function_Round extends Forminator_Calculator_Symbol_Function_Abstract {

	/**
	 * @inheritdoc
	 */
	protected $identifiers = array( 'round' );

	/**
	 * @inheritdoc
	 * @throws Forminator_Calculator_Exception
	 */
	public function execute( $arguments ) {
		if ( count( $arguments ) !== 1 ) {
			throw new Forminator_Calculator_Exception( 'Error: Expected one argument, got ' . count( $arguments ) );
		}

		$number = $arguments[0];

		return round( $number );
	}

}
