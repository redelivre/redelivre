import FrontCalculatorParserNodeAbstract from "./front.calculator.parser.node.abstract";

/**
 * A parent node is a container for a (sorted) array of nodes.
 *
 */
export default class FrontCalculatorParserNodeContainer extends FrontCalculatorParserNodeAbstract {
	constructor(childNodes) {
		super();

		/**
		 *
		 * @type {FrontCalculatorParserNodeAbstract[]}
		 */
		this.childNodes = null;

		this.setChildNodes(childNodes);
	}

	/**
	 * Setter for the child nodes.
	 * Notice: The number of child nodes can be 0.
	 * @param childNodes
	 */
	setChildNodes(childNodes) {
		childNodes.forEach(function (childNode) {
			if (!(childNode instanceof FrontCalculatorParserNodeAbstract)) {
				throw 'Expected AbstractNode, but got ' + childNode.constructor.name;
			}
		});

		this.childNodes = childNodes;
	}

	/**
	 * Returns the number of child nodes in this array node.
	 * Does not count the child nodes of the child nodes.
	 *
	 * @returns {number}
	 */
	size() {
		try {
			return this.childNodes.length;
		} catch (e) {
			return 0;
		}
	}

	/**
	 * Returns true if the array node does not have any
	 * child nodes. This might sound strange but is possible.
	 *
	 * @returns {boolean}
	 */
	isEmpty() {
		return !this.size();
	}

}
