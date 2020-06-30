import FrontCalculator from "../../calculator/front.calculator";

test('test_op_sub', () => {
	var calculator = new FrontCalculator('12 - 33.5');
	var val        = calculator.calculate();

	expect(val).toBe(-21.5);

});
