import FrontCalculatorSymbolFunctionAbstract from "../abstract/front.calculator.symbol.function.abstract";

/**
 * Math.max() function. Expects at least one parameter.
 * Example: "max(1,2,3)" => 3, "max(1,-1)" => 1, "max(0,0)" => 0, "max(2)" => 2
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/max
 */
export default class FrontCalculatorSymbolFunctionMax extends FrontCalculatorSymbolFunctionAbstract {
	constructor() {
		super();

		this.identifiers = ['max'];

	}

	execute(params) {
		if (params.length < 1) {
			throw ('Error: Expected at least one argument, got ' + params.length);
		}

		return Math.max(...params);

	}
}
