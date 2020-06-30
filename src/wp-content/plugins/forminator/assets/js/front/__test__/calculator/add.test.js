import FrontCalculator from "../../calculator/front.calculator";

test('test_op_add', () => {
	var calculator = new FrontCalculator('2.5 + 4');
	var val        = calculator.calculate();

	expect(val).toBe(6.5);

});
