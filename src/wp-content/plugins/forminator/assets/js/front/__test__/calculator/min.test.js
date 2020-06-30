import FrontCalculator from "../../calculator/front.calculator";

test('test_exec_min', () => {
	var calculator = new FrontCalculator('min(3+4,5)');
	var val        = calculator.calculate();

	expect(val).toBe(5);

	calculator = new FrontCalculator('min(3,1+1)');
	val        = calculator.calculate();

	expect(val).toBe(2);

});
