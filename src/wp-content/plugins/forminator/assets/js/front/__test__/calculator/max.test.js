import FrontCalculatorSymbolFunctionMax from "../../calculator/symbol/functions/front.calculator.symbol.function.max";
import FrontCalculator from "../../calculator/front.calculator";

test('test_exec_max', () => {
	var max = new FrontCalculatorSymbolFunctionMax();

	var val = max.execute([6.6, 4.4, 9.8]);

	expect(val).toBe(9.8);

	var calculator = new FrontCalculator('max(3,4.5)');
	val            = calculator.calculate();

	expect(val).toBe(4.5);

});
