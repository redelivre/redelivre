import FrontCalculator from "../../calculator/front.calculator";

test('test_exec_pi', () => {
	var calculator = new FrontCalculator('pi');

	var val = calculator.calculate();

	expect(val).toBe(Math.PI);
});
