import FrontCalculatorSymbolAbstract from "./front.calculator.symbol.abstract";

/**
 * This class is the base class for all symbols that are of the type "constant".
 * We recommend to use names as textual representations for this type of symbol.
 * Please take note of the fact that the precision of PHP float constants
 * (for example M_PI) is based on the "precision" directive in php.ini,
 * which defaults to 14.
 */
export default class FrontCalculatorSymbolConstantAbstract extends FrontCalculatorSymbolAbstract {
	constructor() {
		super();

		/**
		 * This is the value of the constant. We use 0 as an example here,
		 * but you are supposed to overwrite this in the concrete constant class.
		 * Usually mathematical constants are not integers, however,
		 * you are allowed to use an integer in this context.
		 *
		 * @type {number}
		 */
		this.value = 0;
	}
}
