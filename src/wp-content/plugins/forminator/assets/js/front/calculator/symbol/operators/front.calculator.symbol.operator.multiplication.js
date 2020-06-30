import FrontCalculatorSymbolOperatorAbstract from "../abstract/front.calculator.symbol.operator.abstract";

/**
 * Operator for mathematical multiplication.
 * Example: "2*3" => 6
 *
 * @see     https://en.wikipedia.org/wiki/Multiplication
 *
 */
export default class FrontCalculatorSymbolOperatorMultiplication extends FrontCalculatorSymbolOperatorAbstract {
	constructor() {
		super();

		this.identifiers = ['*'];

		this.precedence = 200;

	}

	operate(leftNumber, rightNumber) {
		return leftNumber * rightNumber;
	}
}
