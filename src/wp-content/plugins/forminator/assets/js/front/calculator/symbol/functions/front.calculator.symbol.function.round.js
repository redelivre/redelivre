import FrontCalculatorSymbolFunctionAbstract from "../abstract/front.calculator.symbol.function.abstract";

/**
 * Math.round() function aka rounds a float.
 * Expects one parameter.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/round
 */
export default class FrontCalculatorSymbolFunctionRound extends FrontCalculatorSymbolFunctionAbstract {
	constructor() {
		super();

		this.identifiers = ['round'];

	}

	execute(params) {
		if (params.length !== 1) {
			throw ('Error: Expected one argument, got ' + params.length);
		}

		return Math.round(params[0]);

	}
}
