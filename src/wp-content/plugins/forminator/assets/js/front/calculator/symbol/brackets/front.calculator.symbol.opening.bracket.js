import FrontCalculatorSymbolAbstract from "../abstract/front.calculator.symbol.abstract";

export default class FrontCalculatorSymbolOpeningBracket extends FrontCalculatorSymbolAbstract {
	constructor() {
		super();

		this.identifiers = ['('];
	}
}
