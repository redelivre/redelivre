import FrontCalculatorSymbolOperatorAbstract from "../abstract/front.calculator.symbol.operator.abstract";

/**
 * Operator for mathematical subtraction.
 * Example: "2-1" => 1
 *
 * @see     https://en.wikipedia.org/wiki/Subtraction
 *
 */
export default class FrontCalculatorSymbolOperatorSubtraction extends FrontCalculatorSymbolOperatorAbstract {
	constructor() {
		super();

		this.identifiers = ['-'];

		this.precedence = 100;

		/**
		 * Notice: The subtraction operator is unary AND binary!
		 *
		 * @type {boolean}
		 */
		this.operatesUnary = true;

	}

	operate(leftNumber, rightNumber) {
		return leftNumber - rightNumber;
	}
}
