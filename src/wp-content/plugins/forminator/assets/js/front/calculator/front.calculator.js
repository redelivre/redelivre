/**********
 * Attempt to rewrite Forminator_Calculator backend
 *
 * @see Forminator_Calculator
 *
 ***********/
import FrontCalculatorParserTokenizer from "./parser/front.calculator.parser.tokenizer";
import FrontCalculatorSymbolLoader from "./symbol/front.calculator.symbol.loader";
import FrontCalculatorParser from "./parser/front.calculator.parser";
import FrontCalculatorSymbolNumber from "./symbol/front.calculator.symbol.number";
import FrontCalculatorSymbolConstantAbstract from "./symbol/abstract/front.calculator.symbol.constant.abstract";
import FrontCalculatorParserNodeSymbol from "./parser/node/front.calculator.parser.node.symbol";
import FrontCalculatorSymbolOperatorAbstract from "./symbol/abstract/front.calculator.symbol.operator.abstract";
import FrontCalculatorSymbolSeparator from "./symbol/front.calculator.symbol.separator";
import FrontCalculatorParserNodeFunction from "./parser/node/front.calculator.parser.node.function";
import FrontCalculatorParserNodeContainer from "./parser/node/front.calculator.parser.node.container";

export default class FrontCalculator {

	/**
	 *
	 * @param {string} term
	 */
	constructor(term) {

		/**
		 *
		 * @type {string}
		 */
		this.term = term;

		/**
		 *
		 * @type {FrontCalculatorParserTokenizer}
		 */
		this.tokenizer = new FrontCalculatorParserTokenizer(this.term);

		/**
		 *
		 * @type {FrontCalculatorSymbolLoader}
		 */
		this.symbolLoader = new FrontCalculatorSymbolLoader();

		/**
		 *
		 * @type {FrontCalculatorParser}
		 */
		this.parser = new FrontCalculatorParser(this.symbolLoader);
	}

	/**
	 *
	 * @returns {FrontCalculatorParserNodeContainer}
	 */
	parse() {
		// reset
		this.tokenizer.input = this.term;
		this.tokenizer.reset();

		var tokens = this.tokenizer.tokenize();
		if (tokens.length === 0) {
			throw ('Error: Empty token of calculator term.');
		}

		var rootNode = this.parser.parse(tokens);
		if (rootNode.isEmpty()) {
			throw ('Error: Empty nodes of calculator tokens.');
		}

		return rootNode;
	}

	/**
	 *
	 * @returns {number}
	 */
	calculate() {
		var result   = 0;
		var rootNode = this.parse();

		if (false === rootNode) {
			return result;
		}

		return this.calculateNode(rootNode);
	}

	/**
	 *Calculates the numeric value / result of a node of
	 * any known and calculable type. (For example symbol
	 * nodes with a symbol of type separator are not
	 * calculable.)
	 *
	 * @param {FrontCalculatorParserNodeAbstract} node
	 *
	 * @returns {number}
	 */
	calculateNode(node) {
		if (node instanceof FrontCalculatorParserNodeSymbol) {
			return this.calculateSymbolNode(node);
		} else if (node instanceof FrontCalculatorParserNodeFunction) {
			return this.calculateFunctionNode(node);
		} else if (node instanceof FrontCalculatorParserNodeContainer) {
			return this.calculateContainerNode(node);
		} else {
			throw ('Error: Cannot calculate node of unknown type "' + node.constructor.name + '"');
		}
	}

	/**
	 * This method actually calculates the results of every sub-terms
	 * in the syntax tree (which consists of nodes).
	 * It can call itself recursively.
	 * Attention: $node must not be of type FunctionNode!
	 *
	 * @param {FrontCalculatorParserNodeContainer} containerNode
	 *
	 * @returns {number}
	 */
	calculateContainerNode(containerNode) {

		if (containerNode instanceof FrontCalculatorParserNodeFunction) {
			throw ('Error: Expected container node but got a function node');
		}

		var result               = 0;
		var nodes                = containerNode.childNodes;
		var orderedOperatorNodes = this.detectCalculationOrder(nodes);

		// Actually calculate the term. Iterates over the ordered operators and
		// calculates them, then replaces the parts of the operation by the result.
		for (var i = 0; i < orderedOperatorNodes.length; i++) {

			var operatorNode = orderedOperatorNodes[i].node;
			var index        = orderedOperatorNodes[i].index;

			var leftOperand      = null;
			var leftOperandIndex = null;

			var nodeIndex = 0;
			while (nodeIndex !== index) {
				if (nodes[nodeIndex] === undefined) {
					nodeIndex++;
					continue;
				}
				leftOperand      = nodes[nodeIndex];
				leftOperandIndex = nodeIndex;
				nodeIndex++;
			}

			nodeIndex++;
			while (nodes[nodeIndex] === undefined) {
				nodeIndex++;
			}

			var rightOperand      = nodes[nodeIndex];
			var rightOperandIndex = nodeIndex;
			var rightNumber       = !isNaN(rightOperand) ? rightOperand : this.calculateNode(rightOperand);

			/**
			 * @type {FrontCalculatorSymbolOperatorAbstract}
			 */
			var symbol = operatorNode.symbol;

			if (operatorNode.isUnaryOperator) {
				result = symbol.operate(null, rightNumber);

				// Replace the participating symbols of the operation by the result
				delete nodes[rightOperandIndex]; // `delete` operation only set the value to empty, not `actually` remove it
				nodes[index] = result;

			} else {
				if (leftOperandIndex !== null && leftOperand !== null) {

					var leftNumber = !isNaN(leftOperand) ? leftOperand : this.calculateNode(leftOperand);

					result = symbol.operate(leftNumber, rightNumber);

					// Replace the participating symbols of the operation by the result
					delete nodes[leftOperandIndex];
					delete nodes[rightOperandIndex];

					nodes[index] = result;

				}

			}

		}

		//cleanup empty nodes
		nodes = nodes.filter(function (node) {
			return node !== undefined;
		});

		if (nodes.length === 0) {
			throw ('Error: Missing calculable subterm. Are there empty brackets?');
		}

		if (nodes.length > 1) {
			throw ('Error: Missing operators between parts of the term.');
		}

		// The only remaining element of the $nodes array contains the overall result
		result = nodes.pop();

		// If the $nodes array did not contain any operator (but only one node) than
		// the result of this node has to be calculated now
		if (isNaN(result)) {
			return this.calculateNode(result);
		}

		return result;

	}

	/**
	 * Returns the numeric value of a function node.
	 * @param {FrontCalculatorParserNodeFunction} functionNode
	 *
	 * @returns {number}
	 */
	calculateFunctionNode(functionNode) {
		var nodes = functionNode.childNodes;

		var functionArguments  = []; // ex : func(1+2,3,4) : 1+2 need to be calculated first
		var argumentChildNodes = [];
		var containerNode      = null;

		for (var i = 0; i < nodes.length; i++) {
			var node = nodes[i];
			if (node instanceof FrontCalculatorParserNodeSymbol) {
				if (node.symbol instanceof FrontCalculatorSymbolSeparator) {
					containerNode = new FrontCalculatorParserNodeContainer(argumentChildNodes);
					functionArguments.push(this.calculateNode(containerNode));
					argumentChildNodes = [];
				} else {
					argumentChildNodes.push(node);
				}
			} else {
				argumentChildNodes.push(node);
			}
		}

		if (argumentChildNodes.length > 0) {
			containerNode = new FrontCalculatorParserNodeContainer(argumentChildNodes);
			functionArguments.push(this.calculateNode(containerNode));
		}

		/**
		 *
		 * @type {FrontCalculatorSymbolFunctionAbstract}
		 */
		var symbol = functionNode.symbolNode.symbol;

		return symbol.execute(functionArguments);
	}

	/**
	 * Returns the numeric value of a symbol node.
	 * Attention: node.symbol must not be of type AbstractOperator!
	 *
	 * @param {FrontCalculatorParserNodeSymbol} symbolNode
	 *
	 * @returns {Number}
	 */
	calculateSymbolNode(symbolNode) {
		var symbol = symbolNode.symbol;
		var number = 0;

		if (symbol instanceof FrontCalculatorSymbolNumber) {
			number = symbolNode.token.value;

			// Convert string to int or float (depending on the type of the number)
			// If the number has a longer fractional part, it will be cut.
			number = Number(number);
		} else if (symbol instanceof FrontCalculatorSymbolConstantAbstract) {

			number = symbol.value;
		} else {
			throw ('Error: Found symbol of unexpected type "' + symbol.constructor.name + '", expected number or constant');
		}

		return number;
	}

	/**
	 * Detect the calculation order of a given array of nodes.
	 * Does only care for the precedence of operators.
	 * Does not care for child nodes of container nodes.
	 * Returns a new array with ordered symbol nodes
	 *
	 * @param {FrontCalculatorParserNodeAbstract[]} nodes
	 *
	 * @return {Array}
	 */
	detectCalculationOrder(nodes) {
		var operatorNodes = [];

		// Store all symbol nodes that have a symbol of type abstract operator in an array
		for (var i = 0; i < nodes.length; i++) {
			var node = nodes[i];
			if (node instanceof FrontCalculatorParserNodeSymbol) {
				if (node.symbol instanceof FrontCalculatorSymbolOperatorAbstract) {
					var operatorNode = {index: i, node: node};
					operatorNodes.push(operatorNode);
				}
			}
		}

		operatorNodes.sort(
			/**
			 * Returning 1 means $nodeTwo before $nodeOne, returning -1 means $nodeOne before $nodeTwo.
			 * @param {Object} operatorNodeOne
			 * @param {Object} operatorNodeTwo
			 */
			function (operatorNodeOne, operatorNodeTwo) {
				var nodeOne = operatorNodeOne.node;
				var nodeTwo = operatorNodeTwo.node;

				// First-level precedence of node one
				/**
				 *
				 * @type {FrontCalculatorSymbolOperatorAbstract}
				 */
				var symbolOne     = nodeOne.symbol;
				var precedenceOne = 2;
				if (nodeOne.isUnaryOperator) {
					precedenceOne = 3;
				}

				// First-level precedence of node two
				/**
				 *
				 * @type {FrontCalculatorSymbolOperatorAbstract}
				 */
				var symbolTwo     = nodeTwo.symbol;
				var precedenceTwo = 2;
				if (nodeTwo.isUnaryOperator) {
					precedenceTwo = 3;
				}

				// If the first-level precedence is the same, compare the second-level precedence
				if (precedenceOne === precedenceTwo) {
					precedenceOne = symbolOne.precedence;
					precedenceTwo = symbolTwo.precedence;
				}

				// If the second-level precedence is the same, we have to ensure that the sorting algorithm does
				// insert the node / token that is left in the term before the node / token that is right.
				// Therefore we cannot return 0 but compare the positions and return 1 / -1.
				if (precedenceOne === precedenceTwo) {
					return (nodeOne.token.position < nodeTwo.token.position) ? -1 : 1;
				}

				return (precedenceOne < precedenceTwo) ? 1 : -1;
			}
		);

		return operatorNodes;
	}
}

if (window['forminatorCalculator'] === undefined) {
	window.forminatorCalculator = function (term) {
		return new FrontCalculator(term);
	}
}
