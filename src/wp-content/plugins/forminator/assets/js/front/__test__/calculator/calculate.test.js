import FrontCalculator from "../../calculator/front.calculator";

describe('test calculate', () => {
	test('test_simple_calculate', () => {
		var calculator = new FrontCalculator('(1 + 2) - max(3,2) + 2.5');
		var result     = calculator.calculate();
		var expected   = (1 + 2) - Math.max(3, 2) + 2.5;
		expect(result).toBe(expected);
	});

	test('test_calculate_unary', () => {
		var calculator = new FrontCalculator('1+-3');
		var result     = calculator.calculate();
		var expected   = 1 + -3;
		expect(result).toBe(expected);
	});

	test('test_empty_bracket', () => {
		var calculator = new FrontCalculator('1+2+()-3');

		expect(() => {
			calculator.calculate();
		}).toThrowError('Error: Missing calculable subterm. Are there empty brackets?');
	});

	test('test_no_operator_between', () => {
		var calculator = new FrontCalculator('min(3,2)max(4,5)');

		expect(() => {
			calculator.calculate();
		}).toThrowError('Error: Missing operators between parts of the term.');
	});

	test('test_precedence', () => {
		var calculator = new FrontCalculator('1+3*4');
		var result     = calculator.calculate();
		expect(result).toBe(13);
	});

	test('test_case_1', () => {
		var calculator = new FrontCalculator('6.7+4.4');
		var result     = calculator.calculate();

		// @see https://javascript.info/number#imprecise-calculations
		expect(result).toBe(11.100000000000001);
	});
});

