import FrontCalculatorSymbolFunctionMax from "../../calculator/symbol/functions/front.calculator.symbol.function.max";
import FrontCalculator from "../../calculator/front.calculator";
import FrontCalculatorParserNodeContainer from "../../calculator/parser/node/front.calculator.parser.node.container";
import FrontCalculatorParserTokenizer from "../../calculator/parser/front.calculator.parser.tokenizer";
import FrontCalculatorSymbolLoader from "../../calculator/symbol/front.calculator.symbol.loader";
import FrontCalculatorParser from "../../calculator/parser/front.calculator.parser";
import FrontCalculatorParserNodeSymbol from "../../calculator/parser/node/front.calculator.parser.node.symbol";
import FrontCalculatorSymbolNumber from "../../calculator/symbol/front.calculator.symbol.number";
import FrontCalculatorSymbolOperatorAddition from "../../calculator/symbol/operators/front.calculator.symbol.operator.addition";
import FrontCalculatorSymbolOperatorSubtraction from "../../calculator/symbol/operators/front.calculator.symbol.operator.subtraction";
import FrontCalculatorParserNodeFunction from "../../calculator/parser/node/front.calculator.parser.node.function";
import FrontCalculatorSymbolSeparator from "../../calculator/symbol/front.calculator.symbol.separator";

describe('parser', () => {
	test('test_simple_parse', () => {
		var calculator    = new FrontCalculator('(1 + 2) - max(3,2) + 2.5');
		var nodeContainer = calculator.parse();

		expect(nodeContainer).toBeInstanceOf(FrontCalculatorParserNodeContainer);


		var childNodes = nodeContainer.childNodes;

		expect(childNodes.length).toBe(5);


		/**
		 * (1+2) sub-part
		 */
		expect(childNodes[0]).toBeInstanceOf(FrontCalculatorParserNodeContainer);
		/**
		 *
		 * @type {FrontCalculatorParserNodeContainer}
		 */
		var node           = childNodes[0];
		var partChildNodes = node.childNodes;
		expect(partChildNodes.length).toBe(3);


		expect(partChildNodes[0]).toBeInstanceOf(FrontCalculatorParserNodeSymbol);
		/**
		 *
		 * @type {FrontCalculatorParserNodeSymbol}
		 */
		node = partChildNodes[0];
		expect(node.symbol).toBeInstanceOf(FrontCalculatorSymbolNumber);
		expect(node.token.value).toBe('1');


		expect(partChildNodes[1]).toBeInstanceOf(FrontCalculatorParserNodeSymbol);
		/**
		 *
		 * @type {FrontCalculatorParserNodeSymbol}
		 */
		node = partChildNodes[1];
		expect(node.symbol).toBeInstanceOf(FrontCalculatorSymbolOperatorAddition);


		expect(partChildNodes[2]).toBeInstanceOf(FrontCalculatorParserNodeSymbol);
		/**
		 *
		 * @type {FrontCalculatorParserNodeSymbol}
		 */
		node = partChildNodes[2];
		expect(node.symbol).toBeInstanceOf(FrontCalculatorSymbolNumber);
		expect(node.token.value).toBe('2');


		/**
		 *  -
		 */
		expect(childNodes[1]).toBeInstanceOf(FrontCalculatorParserNodeSymbol);
		/**
		 *
		 * @type {FrontCalculatorParserNodeSymbol}
		 */
		node = childNodes[1];
		expect(node.symbol).toBeInstanceOf(FrontCalculatorSymbolOperatorSubtraction);



		/**
		 * max(3,2) sub-part
		 */
		expect(childNodes[2]).toBeInstanceOf(FrontCalculatorParserNodeFunction);
		/**
		 *
		 * @type {FrontCalculatorParserNodeFunction}
		 */
		node = childNodes[2];
		expect(node.symbolNode.symbol).toBeInstanceOf(FrontCalculatorSymbolFunctionMax);
		partChildNodes = node.childNodes;
		expect(partChildNodes.length).toBe(3);

		/**
		 *
		 * @type {FrontCalculatorParserNodeSymbol}
		 */
		node = partChildNodes[0];
		expect(node.symbol).toBeInstanceOf(FrontCalculatorSymbolNumber);
		expect(node.token.value).toBe('3');

		node = partChildNodes[1];
		expect(node.symbol).toBeInstanceOf(FrontCalculatorSymbolSeparator);
		expect(node.token.value).toBe(',');

		node = partChildNodes[2];
		expect(node.symbol).toBeInstanceOf(FrontCalculatorSymbolNumber);
		expect(node.token.value).toBe('2');



		/**
		 * +
		 */
		expect(childNodes[3]).toBeInstanceOf(FrontCalculatorParserNodeSymbol);
		/**
		 *
		 * @type {FrontCalculatorParserNodeSymbol}
		 */
		node = childNodes[3];
		expect(node.symbol).toBeInstanceOf(FrontCalculatorSymbolOperatorAddition);
		expect(node.token.value).toBe('+');



		/**
		 * 2.5
		 */
		expect(childNodes[4]).toBeInstanceOf(FrontCalculatorParserNodeSymbol);
		/**
		 *
		 * @type {FrontCalculatorParserNodeSymbol}
		 */
		node = childNodes[4];
		expect(node.symbol).toBeInstanceOf(FrontCalculatorSymbolNumber);
		expect(node.token.value).toBe('2.5');
	});

	test('test_unknown_parser_for_symbol', () => {
		var term = '1 & 2'; // `&` is unknown for identifier

		var tokenizer = new FrontCalculatorParserTokenizer(term);
		var tokens    = tokenizer.tokenize();

		var symbolLoader = new FrontCalculatorSymbolLoader();
		var parser       = new FrontCalculatorParser(symbolLoader);

		var parse = () => {
			parser.parse(tokens);
		};

		expect(parse).toThrowError('Error: Detected unknown or invalid string identifier: &.');

	});

	test('test_closing_bracket_without_closing_bracket', () => {
		var term = '1+2)';

		var tokenizer = new FrontCalculatorParserTokenizer(term);
		var tokens    = tokenizer.tokenize();

		var symbolLoader = new FrontCalculatorSymbolLoader();
		var parser       = new FrontCalculatorParser(symbolLoader);

		var parse = () => {
			parser.parse(tokens);
		};

		expect(parse).toThrowError('Error: Found closing bracket that does not have an opening bracket.');

	});

	test('test_opening_bracket_without_closing_bracket', () => {
		var term = '(1+2';

		var tokenizer = new FrontCalculatorParserTokenizer(term);
		var tokens    = tokenizer.tokenize();

		var symbolLoader = new FrontCalculatorSymbolLoader();
		var parser       = new FrontCalculatorParser(symbolLoader);

		var parse = () => {
			parser.parse(tokens);
		};

		expect(parse).toThrowError('Error: There is at least one opening bracket that does not have a closing bracket');

	});

	test('test_function_without_bracket', () => {
		var term = 'max3,2)';

		var tokenizer = new FrontCalculatorParserTokenizer(term);
		var tokens    = tokenizer.tokenize();

		var symbolLoader = new FrontCalculatorSymbolLoader();
		var parser       = new FrontCalculatorParser(symbolLoader);

		var parse = () => {
			parser.parse(tokens);
		};

		expect(parse).toThrowError('Error: Expected opening bracket (after a function) but got something else.');


		// without arg and on last token
		term = 'min';

		tokenizer = new FrontCalculatorParserTokenizer(term);
		tokens    = tokenizer.tokenize();
		parser    = new FrontCalculatorParser(symbolLoader);

		expect(() => {
			parser.parse(tokens);
		}).toThrowError('Error: Expected opening bracket (after a function) but reached the end of the term');

	});

	test('test_no_operand_after_operator', () => {
		var term = '2+3-';

		var tokenizer = new FrontCalculatorParserTokenizer(term);
		var tokens    = tokenizer.tokenize();

		var symbolLoader = new FrontCalculatorSymbolLoader();
		var parser       = new FrontCalculatorParser(symbolLoader);

		var parse = () => {
			parser.parse(tokens);
		};

		expect(parse).toThrowError('Error: Found operator that does not stand before an operand.');

	});

	test('test_operator_non_unary', () => {
		var term = '2++3';// `+` is not unary

		var tokenizer = new FrontCalculatorParserTokenizer(term);
		var tokens    = tokenizer.tokenize();

		var symbolLoader = new FrontCalculatorSymbolLoader();
		var parser       = new FrontCalculatorParser(symbolLoader);

		var parse = () => {
			parser.parse(tokens);
		};

		expect(parse).toThrowError('Error: Found operator in unary notation that is not unary.');

	});


});
