import FrontCalculator from "../../calculator/front.calculator";

describe('test infinite', () => {
	test('test_simple_infinite', () => {
		var calculator = new FrontCalculator('3/0');
		var result     = calculator.calculate();
		expect(result).toBe(Infinity);

	});

});

