<?php

/**
 * Operator for mathematical multiplication.
 * Example: "1+2" => 3
 *
 * @see     https://en.wikipedia.org/wiki/Multiplication
 *
 */
class Forminator_Calculator_Symbol_Operator_Subtraction extends Forminator_Calculator_Symbol_Operator_Abstract {

	/**
	 * @inheritdoc
	 */
	protected $identifiers = array( '-' );

	/**
	 * @inheritdoc
	 */
	protected $precedence = 100;

	/**
	 * @inheritdoc
	 * Notice: The subtraction operator is unary AND binary!
	 */
	protected $operates_unary = true;

	/**
	 * @inheritdoc
	 */
	public function operate( $left_number, $right_number ) {
		return $left_number - $right_number;
	}

}
