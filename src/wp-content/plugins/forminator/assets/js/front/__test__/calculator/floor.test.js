import FrontCalculator from "../../calculator/front.calculator";

test('test_exec_floor', () => {
	var calculator = new FrontCalculator('floor(3.4)');

	var val = calculator.calculate();

	expect(val).toBe(3);

});
