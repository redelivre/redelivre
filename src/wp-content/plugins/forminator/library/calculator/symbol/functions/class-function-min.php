<?php

/**
 * PHP min() function. Expects at least one parameter.
 * Example: "min(1,2,3)" => 1, "min(1,-1)" => -1, "min(0,0)" => 0, "min(2)" => 2
 *
 * @see http://php.net/manual/en/ref.math.php
 */
class Forminator_Calculator_Symbol_Function_Min extends Forminator_Calculator_Symbol_Function_Abstract {

	/**
	 * @inheritdoc
	 */
	protected $identifiers = array( 'min' );

	/**
	 * @inheritdoc
	 * @throws Forminator_Calculator_Exception
	 */
	public function execute( $arguments ) {
		if ( count( $arguments ) < 1 ) {
			throw new Forminator_Calculator_Exception( 'Error: Expected at least one argument, none given' );
		}

		$min = min( $arguments );

		return $min;
	}

}
