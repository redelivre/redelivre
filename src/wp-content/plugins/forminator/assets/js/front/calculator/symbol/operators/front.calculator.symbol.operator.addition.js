import FrontCalculatorSymbolOperatorAbstract from "../abstract/front.calculator.symbol.operator.abstract";

/**
 * Operator for mathematical addition.
 * Example: "1+2" => 3
 *
 * @see     https://en.wikipedia.org/wiki/Addition
 *
 */
export default class FrontCalculatorSymbolOperatorAddition extends FrontCalculatorSymbolOperatorAbstract {
	constructor() {
		super();

		this.identifiers = ['+'];

		this.precedence = 100;

	}

	operate(leftNumber, rightNumber) {
		return leftNumber + rightNumber;
	}
}
