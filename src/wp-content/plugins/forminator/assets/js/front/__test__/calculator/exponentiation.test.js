import FrontCalculatorSymbolOperatorExponentiation from "../../calculator/symbol/operators/front.calculator.symbol.operator.exponentiation";

test('7^2 = 49', () => {
	var pow = new FrontCalculatorSymbolOperatorExponentiation();

	var val = pow.operate(7, 2);

	expect(val).toBe(49)
});
