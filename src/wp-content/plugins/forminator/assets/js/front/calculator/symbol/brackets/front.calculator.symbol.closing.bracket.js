import FrontCalculatorSymbolAbstract from "../abstract/front.calculator.symbol.abstract";

export default class FrontCalculatorSymbolClosingBracket extends FrontCalculatorSymbolAbstract {
	constructor() {
		super();

		this.identifiers = [')'];
	}
}
