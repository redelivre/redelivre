import FrontCalculator from "../../calculator/front.calculator";

test('test_exec_round', () => {
	var calculator = new FrontCalculator('round(3.6)');
	var val        = calculator.calculate();

	expect(val).toBe(4);

	calculator = new FrontCalculator('round(3.1)');
	val        = calculator.calculate();

	expect(val).toBe(3);

});
