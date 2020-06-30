<?php

/**
 * Operator for mathematical modulo operation.
 * Example: "5%3" => 2
 *
 * @see https://en.wikipedia.org/wiki/Modulo_operation
 *
 */
class Forminator_Calculator_Symbol_Operator_Modulo extends Forminator_Calculator_Symbol_Operator_Abstract {

	/**
	 * @inheritdoc
	 */
	protected $identifiers = array( '%' );

	/**
	 * @inheritdoc
	 */
	protected $precedence = 200;

	/**
	 * @inheritdoc
	 */
	public function operate( $left_number, $right_number ) {
		return $left_number % $right_number;
	}

}
