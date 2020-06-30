import FrontCalculatorSymbolConstantAbstract from "../abstract/front.calculator.symbol.constant.abstract";

/**
 * Math.PI
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/PI
 */
export default class FrontCalculatorSymbolConstantPi extends FrontCalculatorSymbolConstantAbstract {
	constructor() {
		super();

		this.identifiers = ['pi'];

		this.value = Math.PI;
	}
}
