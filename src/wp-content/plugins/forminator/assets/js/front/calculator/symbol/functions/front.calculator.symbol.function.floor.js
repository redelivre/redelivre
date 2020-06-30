import FrontCalculatorSymbolFunctionAbstract from "../abstract/front.calculator.symbol.function.abstract";

/**
 * Math.floor() function aka round fractions down.
 * Expects one parameter.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/floor
 */
export default class FrontCalculatorSymbolFunctionFloor extends FrontCalculatorSymbolFunctionAbstract {
	constructor() {
		super();

		this.identifiers = ['floor'];

	}

	execute(params) {
		if (params.length !== 1) {
			throw ('Error: Expected one argument, got ' + params.length);
		}

		return Math.floor(params[0]);

	}
}
