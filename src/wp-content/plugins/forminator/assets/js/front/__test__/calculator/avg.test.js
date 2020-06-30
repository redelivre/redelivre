import FrontCalculatorSymbolFunctionAvg from "../../calculator/symbol/functions/front.calculator.symbol.function.avg";
import FrontCalculator from "../../calculator/front.calculator";

test('test_exec_avg', () => {
	var avg = new FrontCalculatorSymbolFunctionAvg();

	var val = avg.execute([6.6, 4.4]);

	expect(val).toBe(5.5);

	var calculator = new FrontCalculator('avg(9)');

	val = calculator.calculate();

	expect(val).toBe(9);

	calculator = new FrontCalculator('avg(3,4,9,6)');

	val = calculator.calculate();

	expect(val).toBe(5.5);
});
