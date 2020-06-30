import FrontCalculatorParserToken from '../../calculator/parser/front.calculator.parser.token';
import FrontCalculatorParserTokenizer from '../../calculator/parser/front.calculator.parser.tokenizer';

describe('test tokenizer', () => {
	test('test_simple_tokenize', () => {
		var tokenizer = new FrontCalculatorParserTokenizer('(1 + 2) - max(3,2) + 2.5');
		var tokens    = tokenizer.tokenize();

		expect(tokens.length).toEqual(14);

		expect(tokens[0].value).toBe('(');
		expect(tokens[0].type).toBe(FrontCalculatorParserToken.TYPE_CHAR);

		expect(tokens[1].value).toBe('1');
		expect(tokens[1].type).toBe(FrontCalculatorParserToken.TYPE_NUMBER);

		expect(tokens[2].value).toBe('+');
		expect(tokens[3].value).toBe('2');
		expect(tokens[4].value).toBe(')');
		expect(tokens[5].value).toBe('-');

		expect(tokens[6].value).toBe('max');
		expect(tokens[6].type).toBe(FrontCalculatorParserToken.TYPE_WORD);

		expect(tokens[7].value).toBe('(');
		expect(tokens[8].value).toBe('3');
		expect(tokens[9].value).toBe(',');
		expect(tokens[10].value).toBe('2');
		expect(tokens[11].value).toBe(')');
		expect(tokens[12].value).toBe('+');
		expect(tokens[13].value).toBe('2.5');

	});

	test('test_number_double_period', () => {
		var tokenizer = new FrontCalculatorParserTokenizer('2.5.5 + 3');

		expect(() => {
			tokenizer.tokenize();
		}).toThrowError('Error: A number cannot have more than one period');

	});

	test('test_number_period', () => {
		var tokenizer = new FrontCalculatorParserTokenizer('2.55 + 3');
		var tokens    = tokenizer.tokenize();

		var number255Exist = false;
		for (var i = 0; i < tokens.length; i++) {
			var token = tokens[i];
			if ('2.55' === token.value) {
				number255Exist = true;
				break;
			}
		}

		expect(number255Exist).toBe(true);
	});

	test('test_number_period_with_invalid_digit', () => {
		var tokenizer = new FrontCalculatorParserTokenizer('2.z + 3');
		var tokens    = tokenizer.tokenize();

		var number2zExist = false;
		for (var i = 0; i < tokens.length; i++) {
			var token = tokens[i];
			if ('2.z' === token.value) {
				number2zExist = true;
				break;
			}
		}

		expect(number2zExist).toBe(false);
	});
});

