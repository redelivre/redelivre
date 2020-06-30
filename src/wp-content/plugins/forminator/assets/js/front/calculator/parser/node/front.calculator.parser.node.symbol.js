import FrontCalculatorSymbolOperatorAbstract from "../../symbol/abstract/front.calculator.symbol.operator.abstract";
import FrontCalculatorParserNodeAbstract from "./front.calculator.parser.node.abstract";

/**
 * A symbol node is a node in the syntax tree.
 * Leaf nodes do not have any child nodes
 * (parent nodes can have child nodes). A
 * symbol node represents a mathematical symbol.
 * Nodes are created by the parser.
 *
 */
export default class FrontCalculatorParserNodeSymbol extends FrontCalculatorParserNodeAbstract {
	constructor(token, symbol) {
		super();

		/**
		 * The token of the node. It contains the value.
		 *
		 * @type {FrontCalculatorParserToken}
		 */
		this.token = token;

		/**
		 * The symbol of the node. It defines the type of the node.
		 *
		 * @type {FrontCalculatorSymbolAbstract}
		 */
		this.symbol = symbol;

		/**
		 * Unary operators need to be treated specially.
		 * Therefore a node has to know if it (or to be
		 * more precise the symbol of the node)
		 * represents a unary operator.
		 * Example : -1, -4
		 *
		 * @type {boolean}
		 */
		this.isUnaryOperator = false;
	}

	setIsUnaryOperator(isUnaryOperator) {
		if (!(this.symbol instanceof FrontCalculatorSymbolOperatorAbstract)) {
			throw 'Error: Cannot mark node as unary operator, because symbol is not an operator but of type ' + this.symbol.constructor.name;
		}

		this.isUnaryOperator = isUnaryOperator;
	}

}
