<?php

/**
 * Operator for mathematical exponentiation.
 * Example: "3^2" => 9, "-3^2" => -9, "3^-2" equals "3^(-2)"
 *
 * @see     https://en.wikipedia.org/wiki/Exponentiation
 *
 */
class Forminator_Calculator_Symbol_Operator_Exponentiation extends Forminator_Calculator_Symbol_Operator_Abstract {

	/**
	 * @inheritdoc
	 */
	protected $identifiers = array( '^' );

	/**
	 * @inheritdoc
	 */
	protected $precedence = 300;

	/**
	 * @inheritdoc
	 */
	public function operate( $left_number, $right_number ) {
		return pow( $left_number, $right_number );
	}

}
