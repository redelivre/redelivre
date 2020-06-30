import FrontCalculatorSymbolLoader from "../../calculator/symbol/front.calculator.symbol.loader";
import FrontCalculatorSymbolNumber from "../../calculator/symbol/front.calculator.symbol.number";

test('symbolLoader.findsubTypes()', () => {
	var symbolLoader = new FrontCalculatorSymbolLoader();

	var symbols = symbolLoader.findSubTypes(FrontCalculatorSymbolNumber);

	expect(symbols.length).toBe(1);

	symbols = () => {
		return symbolLoader.findSubTypes('FrontCalculatorSymbolNumber');
	};

	expect(symbols).toThrow(TypeError);
});
