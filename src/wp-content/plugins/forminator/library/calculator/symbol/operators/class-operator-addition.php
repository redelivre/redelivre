<?php

/**
 * Operator for mathematical addition.
 * Example: "1+2" => 3
 *
 * @see     https://en.wikipedia.org/wiki/Addition
 *
 */
class Forminator_Calculator_Symbol_Operator_Addition extends Forminator_Calculator_Symbol_Operator_Abstract {

	/**
	 * @inheritdoc
	 */
	protected $identifiers = array( '+' );

	/**
	 * @inheritdoc
	 */
	protected $precedence = 100;

	/**
	 * @inheritdoc
	 */
	public function operate( $left_number, $right_number ) {
		return $left_number + $right_number;
	}

}
