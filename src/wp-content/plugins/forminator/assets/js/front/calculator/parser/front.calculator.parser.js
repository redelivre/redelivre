import FrontCalculatorParserToken from "./front.calculator.parser.token";
import FrontCalculatorSymbolNumber from "../symbol/front.calculator.symbol.number";
import FrontCalculatorSymbolOpeningBracket from "../symbol/brackets/front.calculator.symbol.opening.bracket";
import FrontCalculatorSymbolClosingBracket from "../symbol/brackets/front.calculator.symbol.closing.bracket";
import FrontCalculatorSymbolFunctionAbstract from "../symbol/abstract/front.calculator.symbol.function.abstract";
import FrontCalculatorSymbolOperatorAbstract from "../symbol/abstract/front.calculator.symbol.operator.abstract";
import FrontCalculatorSymbolSeparator from "../symbol/front.calculator.symbol.separator";
import FrontCalculatorParserNodeSymbol from "./node/front.calculator.parser.node.symbol";
import FrontCalculatorParserNodeContainer from "./node/front.calculator.parser.node.container";
import FrontCalculatorParserNodeFunction from "./node/front.calculator.parser.node.function";

/**
 * The parsers has one important method: parse()
 * It takes an array of tokens as input and
 * returns an array of nodes as output.
 * These nodes are the syntax tree of the term.
 *
 */
export default class FrontCalculatorParser {
	/**
	 *
	 * @param {FrontCalculatorSymbolLoader} symbolLoader
	 */
	constructor(symbolLoader) {

		/**
		 *
		 * @type {FrontCalculatorSymbolLoader}
		 */
		this.symbolLoader = symbolLoader;
	}

	/**
	 * Parses an array with tokens. Returns an array of nodes.
	 * These nodes define a syntax tree.
	 *
	 * @param {FrontCalculatorParserToken[]} tokens
	 *
	 * @returns FrontCalculatorParserNodeContainer
	 */
	parse(tokens) {
		var symbolNodes = this.detectSymbols(tokens);

		var nodes = this.createTreeByBrackets(symbolNodes);

		nodes = this.transformTreeByFunctions(nodes);

		this.checkGrammar(nodes);

		// Wrap the nodes in an array node.
		return new FrontCalculatorParserNodeContainer(nodes);
	}

	/**
	 * Creates a flat array of symbol nodes from tokens.
	 *
	 * @param {FrontCalculatorParserToken[]} tokens
	 * @returns {FrontCalculatorParserNodeSymbol[]}
	 */
	detectSymbols(tokens) {
		var symbolNodes = [];
		var symbol      = null;
		var identifier  = null;

		var expectingOpeningBracket = false; // True if we expect an opening bracket (after a function name)
		var openBracketCounter      = 0;

		for (var i = 0; i < tokens.length; i++) {
			var token = tokens[i];
			var type  = token.type;

			if (FrontCalculatorParserToken.TYPE_WORD === type) {
				identifier = token.value;
				symbol     = this.symbolLoader.find(identifier);

				if (null === symbol) {
					throw ('Error: Detected unknown or invalid string identifier: ' + identifier + '.');
				}
			} else if (type === FrontCalculatorParserToken.TYPE_NUMBER) {
				// Notice: Numbers do not have an identifier
				var symbolNumbers = this.symbolLoader.findSubTypes(FrontCalculatorSymbolNumber);

				if (symbolNumbers.length < 1 || !(symbolNumbers instanceof Array)) {
					throw ('Error: Unavailable number symbol processor.');
				}

				symbol = symbolNumbers[0];
			} else {// Type Token::TYPE_CHARACTER:
				identifier = token.value;
				symbol     = this.symbolLoader.find(identifier);
				if (null === symbol) {
					throw ('Error: Detected unknown or invalid string identifier: ' + identifier + '.');
				}

				if (symbol instanceof FrontCalculatorSymbolOpeningBracket) {
					openBracketCounter++;
				}
				if (symbol instanceof FrontCalculatorSymbolClosingBracket) {
					openBracketCounter--;

					// Make sure there are not too many closing brackets
					if (openBracketCounter < 0) {
						throw ('Error: Found closing bracket that does not have an opening bracket.');
					}
				}

			}

			if (expectingOpeningBracket) {
				if (!(symbol instanceof FrontCalculatorSymbolOpeningBracket)) {
					throw ('Error: Expected opening bracket (after a function) but got something else.');
				}

				expectingOpeningBracket = false;
			} else {
				if (symbol instanceof FrontCalculatorSymbolFunctionAbstract) {
					expectingOpeningBracket = true;
				}
			}

			var symbolNode = new FrontCalculatorParserNodeSymbol(token, symbol);

			symbolNodes.push(symbolNode);
		}

		// Make sure the term does not end with the name of a function but without an opening bracket
		if (expectingOpeningBracket) {
			throw ('Error: Expected opening bracket (after a function) but reached the end of the term');
		}

		// Make sure there are not too many opening brackets
		if (openBracketCounter > 0) {
			throw ('Error: There is at least one opening bracket that does not have a closing bracket');
		}

		return symbolNodes;
	}

	/**
	 * Expects a flat array of symbol nodes and (if possible) transforms
	 * it to a tree of nodes. Cares for brackets.
	 * Attention: Expects valid brackets!
	 * Check the brackets before you call this method.
	 *
	 * @param {FrontCalculatorParserNodeSymbol[]} symbolNodes
	 * @returns {FrontCalculatorParserNodeAbstract[]}
	 */
	createTreeByBrackets(symbolNodes) {
		var tree               = [];
		var nodesInBracket     = []; // AbstractSymbol nodes inside level-0-brackets
		var openBracketCounter = 0;

		for (var i = 0; i < symbolNodes.length; i++) {
			var symbolNode = symbolNodes[i];

			if (!(symbolNode instanceof FrontCalculatorParserNodeSymbol)) {
				throw ('Error: Expected symbol node, but got "' + symbolNode.constructor.name + '"');
			}

			if (symbolNode.symbol instanceof FrontCalculatorSymbolOpeningBracket) {
				openBracketCounter++;

				if (openBracketCounter > 1) {
					nodesInBracket.push(symbolNode);
				}
			} else if (symbolNode.symbol instanceof FrontCalculatorSymbolClosingBracket) {
				openBracketCounter--;

				// Found a closing bracket on level 0
				if (0 === openBracketCounter) {
					var subTree = this.createTreeByBrackets(nodesInBracket);

					// Subtree can be empty for example if the term looks like this: "()" or "functioname()"
					// But this is okay, we need to allow this so we can call functions without a parameter
					tree.push(new FrontCalculatorParserNodeContainer(subTree));
					nodesInBracket = [];
				} else {
					nodesInBracket.push(symbolNode);
				}
			} else {
				if (0 === openBracketCounter) {
					tree.push(symbolNode);
				} else {
					nodesInBracket.push(symbolNode);
				}
			}
		}

		return tree;
	}

	/**
	 * Replaces [a SymbolNode that has a symbol of type AbstractFunction,
	 * followed by a node of type ContainerNode] by a FunctionNode.
	 * Expects the $nodes not including any function nodes (yet).
	 *
	 * @param {FrontCalculatorParserNodeAbstract[]} nodes
	 *
	 * @returns {FrontCalculatorParserNodeAbstract[]}
	 */
	transformTreeByFunctions(nodes) {
		var transformedNodes   = [];
		var functionSymbolNode = null;

		for (var i = 0; i < nodes.length; i++) {
			var node = nodes[i];

			if (node instanceof FrontCalculatorParserNodeContainer) {
				var transformedChildNodes = this.transformTreeByFunctions(node.childNodes);

				if (null !== functionSymbolNode) {
					var functionNode = new FrontCalculatorParserNodeFunction(transformedChildNodes, functionSymbolNode);
					transformedNodes.push(functionNode);
					functionSymbolNode = null;
				} else {
					// not a function
					node.childNodes = transformedChildNodes;
					transformedNodes.push(node);
				}
			} else if (node instanceof FrontCalculatorParserNodeSymbol) {
				var symbol = node.symbol;
				if (symbol instanceof FrontCalculatorSymbolFunctionAbstract) {
					functionSymbolNode = node;
				} else {
					transformedNodes.push(node);
				}
			} else {
				throw ('Error: Expected array node or symbol node, got "' + node.constructor.name + '"');
			}

		}

		return transformedNodes;
	}

	/**
	 * Ensures the tree follows the grammar rules for terms
	 *
	 * @param {FrontCalculatorParserNodeAbstract[]} nodes
	 */
	checkGrammar(nodes) {
		// TODO Make sure that separators are only in the child nodes of the array node of a function node
		// (If this happens the calculator will throw an exception)

		for (var i = 0; i < nodes.length; i++) {
			var node = nodes[i];
			if (node instanceof FrontCalculatorParserNodeSymbol) {
				var symbol = node.symbol;

				if (symbol instanceof FrontCalculatorSymbolOperatorAbstract) {
					var posOfRightOperand = i + 1;

					// Make sure the operator is positioned left of a (potential) operand (=prefix notation).
					// Example term: "-1"
					if (posOfRightOperand >= nodes.length) {
						throw ('Error: Found operator that does not stand before an operand.');
					}

					var posOfLeftOperand = i - 1;

					var leftOperand = null;

					// Operator is unary if positioned at the beginning of a term
					if (posOfLeftOperand >= 0) {
						leftOperand = nodes[posOfLeftOperand];

						if (leftOperand instanceof FrontCalculatorParserNodeSymbol) {
							if (leftOperand.symbol instanceof FrontCalculatorSymbolOperatorAbstract  // example 1`+-`5 : + = operator, - = unary
							    || leftOperand.symbol instanceof FrontCalculatorSymbolSeparator // example func(1`,-`5) ,= separator, - = unary
							) {
								// Operator is unary if positioned right to another operator
								leftOperand = null;
							}
						}
					}

					// If null, the operator is unary
					if (null === leftOperand) {
						if (!symbol.operatesUnary) {
							throw ('Error: Found operator in unary notation that is not unary.');
						}

						// Remember that this node represents a unary operator
						node.setIsUnaryOperator(true);
					} else {
						if (!symbol.operatesBinary) {
							console.log(symbol);
							throw ('Error: Found operator in binary notation that is not binary.');
						}
					}
				}
			} else {
				this.checkGrammar(node.childNodes);
			}
		}
	}
}
