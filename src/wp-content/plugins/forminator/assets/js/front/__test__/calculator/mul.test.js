import FrontCalculator from "../../calculator/front.calculator";

test('test_op_mul', () => {
	var calculator = new FrontCalculator('12 * 33');
	var val        = calculator.calculate();

	expect(val).toBe(396);

});
