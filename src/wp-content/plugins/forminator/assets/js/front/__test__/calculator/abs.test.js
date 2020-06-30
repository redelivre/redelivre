import FrontCalculator from "../../calculator/front.calculator";

test('test_execute_abs', () => {
	var calculator = new FrontCalculator('abs(3.4)');

	var val = calculator.calculate();

	expect(val).toBe(3.4);

	calculator = new FrontCalculator('abs(-9)');

	val = calculator.calculate();

	expect(val).toBe(9)
});
