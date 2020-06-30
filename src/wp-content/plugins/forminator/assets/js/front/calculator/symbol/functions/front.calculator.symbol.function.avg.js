import FrontCalculatorSymbolFunctionAbstract from "../abstract/front.calculator.symbol.function.abstract";

/**
 * Math.abs() function. Expects one parameter.
 * Example: "abs(2)" => 2, "abs(-2)" => 2, "abs(0)" => 0
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/abs
 */
export default class FrontCalculatorSymbolFunctionAvg extends FrontCalculatorSymbolFunctionAbstract {
	constructor() {
		super();

		this.identifiers = ['avg'];

	}

	execute(params) {
		if (params.length < 1) {
			throw ('Error: Expected at least one argument, got ' + params.length);
		}

		var sum = 0.0;
		for (var i = 0; i < params.length; i++) {
			sum += params[i];
		}

		return sum / params.length;
	}
}
