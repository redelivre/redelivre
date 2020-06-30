import FrontCalculatorSymbolFunctionAbstract from "../abstract/front.calculator.symbol.function.abstract";

/**
 * Math.ceil() function aka round fractions up.
 * Expects one parameter.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/ceil
 */
export default class FrontCalculatorSymbolFunctionCeil extends FrontCalculatorSymbolFunctionAbstract {
	constructor() {
		super();

		this.identifiers = ['ceil'];

	}

	execute(params) {
		if (params.length !== 1) {
			throw ('Error: Expected one argument, got ' + params.length);
		}

		return Math.ceil(params[0]);

	}
}
