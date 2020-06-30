import FrontCalculatorSymbolFunctionAbstract from "../abstract/front.calculator.symbol.function.abstract";

/**
 * Math.abs() function. Expects one parameter.
 * Example: "abs(2)" => 2, "abs(-2)" => 2, "abs(0)" => 0
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/abs
 */
export default class FrontCalculatorSymbolFunctionAbs extends FrontCalculatorSymbolFunctionAbstract {
	constructor() {
		super();

		this.identifiers = ['abs'];

	}

	execute(params) {
		if (params.length !== 1) {
			throw ('Error: Expected one argument, got ' + params.length);
		}

		var number = params[0];

		return Math.abs(number);
	}
}
