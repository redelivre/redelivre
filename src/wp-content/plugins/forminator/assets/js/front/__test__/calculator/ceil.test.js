import FrontCalculator from "../../calculator/front.calculator";

test('test_exec_ceil', () => {
	var calculator = new FrontCalculator('ceil(3.4)');

	var val = calculator.calculate();

	expect(val).toBe(4);

	calculator = new FrontCalculator('ceil(1.1)');

	val = calculator.calculate();

	expect(val).toBe(2);

	calculator = new FrontCalculator('ceil(1.000001)');

	val = calculator.calculate();

	expect(val).toBe(2);
});
