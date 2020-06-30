import FrontCalculatorSymbolOperatorAbstract from "../abstract/front.calculator.symbol.operator.abstract";

/**
 * Operator for mathematical modulo operation.
 * Example: "5%3" => 2
 *
 * @see https://en.wikipedia.org/wiki/Modulo_operation
 *
 */
export default class FrontCalculatorSymbolOperatorModulo extends FrontCalculatorSymbolOperatorAbstract {
	constructor() {
		super();

		this.identifiers = ['%'];

		this.precedence = 200;

	}

	operate(leftNumber, rightNumber) {
		return leftNumber % rightNumber;
	}
}
