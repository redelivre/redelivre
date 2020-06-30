import FrontCalculatorSymbolAbstract from "./abstract/front.calculator.symbol.abstract";

/**
 * This class is a class that represents symbols of type "separator".
 * A separator separates the arguments of a (mathematical) function.
 * Most likely we will only need one concrete "separator" class.
 */
export default class FrontCalculatorSymbolSeparator extends FrontCalculatorSymbolAbstract {
	constructor() {
		super();

		this.identifiers = [','];
	}
}
