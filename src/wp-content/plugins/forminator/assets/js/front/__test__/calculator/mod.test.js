import FrontCalculator from "../../calculator/front.calculator";

test('test_op_mod', () => {
	var calculator = new FrontCalculator('10 % 3');
	var val        = calculator.calculate();

	expect(val).toBe(1);

});
