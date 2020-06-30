import FrontCalculator from "../../calculator/front.calculator";

test('test_op_exp', () => {
	var calculator = new FrontCalculator('2.5 ^ 23');
	var val        = calculator.calculate();

	expect(val).toBe(Math.pow(2.5, 23));

});
