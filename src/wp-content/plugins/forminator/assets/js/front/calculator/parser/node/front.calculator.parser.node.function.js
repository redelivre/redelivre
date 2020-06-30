import FrontCalculatorParserNodeContainer from "./front.calculator.parser.node.container";

/**
 * A function in a term consists of the name of the function
 * (the symbol of the function) and the brackets that follow
 * the name and everything that is in this brackets (the
 * arguments). A function node combines these two things.
 * It stores its symbol in the $symbolNode property and its
 * arguments in the $childNodes property which is inherited
 * from the ContainerNode class.
 *
 */
export default class FrontCalculatorParserNodeFunction extends FrontCalculatorParserNodeContainer {

	/**
	 * ContainerNode constructor.
	 * Attention: The constructor is differs from the constructor
	 * of the parent class!
	 *
	 * @param childNodes
	 * @param symbolNode
	 */
	constructor(childNodes, symbolNode) {
		super(childNodes);

		/**
		 *
		 * @type {FrontCalculatorParserNodeSymbol}
		 */
		this.symbolNode = symbolNode;
	}

}
