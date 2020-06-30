import FrontCalculatorSymbolOperatorAbstract from "../abstract/front.calculator.symbol.operator.abstract";

/**
 * Operator for mathematical exponentiation.
 * Example: "3^2" => 9, "-3^2" => -9, "3^-2" equals "3^(-2)"
 *
 * @see     https://en.wikipedia.org/wiki/Exponentiation
 * @see     https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/pow
 *
 */
export default class FrontCalculatorSymbolOperatorExponentiation extends FrontCalculatorSymbolOperatorAbstract {
	constructor() {
		super();

		this.identifiers = ['^'];

		this.precedence = 300;

	}

	operate(leftNumber, rightNumber) {
		return Math.pow(leftNumber, rightNumber);
	}
}
