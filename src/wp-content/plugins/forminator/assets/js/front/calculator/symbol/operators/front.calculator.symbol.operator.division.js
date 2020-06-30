import FrontCalculatorSymbolOperatorAbstract from "../abstract/front.calculator.symbol.operator.abstract";

/**
 * Operator for mathematical division.
 * Example: "6/2" => 3, "6/0" => PHP warning
 *
 * @see     https://en.wikipedia.org/wiki/Division_(mathematics)
 *
 */
export default class FrontCalculatorSymbolOperatorDivision extends FrontCalculatorSymbolOperatorAbstract {
	constructor() {
		super();

		this.identifiers = ['/'];

		this.precedence = 200;

	}

	operate(leftNumber, rightNumber) {
		var result = leftNumber / rightNumber;

		// // force to 0
		// if (!isFinite(result)) {
		// 	return 0;
		// }
		return result;
	}
}
