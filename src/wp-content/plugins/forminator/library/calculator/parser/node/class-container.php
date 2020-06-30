<?php

/**
 * A parent node is a container for a (sorted) array of nodes.
 *
 */
class Forminator_Calculator_Parser_Node_Container extends Forminator_Calculator_Parser_Node_Abstract {

	/**
	 * Array of (sorted) child nodes
	 * Notice: The number of child nodes can be 0.
	 *
	 * @var Forminator_Calculator_Parser_Node_Abstract[]
	 */
	protected $child_nodes;

	/**
	 * ContainerNode constructor.
	 *
	 * @param Forminator_Calculator_Parser_Node_Abstract[] $child_nodes
	 *
	 * @throws Forminator_Calculator_Exception
	 */
	public function __construct( $child_nodes ) {
		$this->set_child_nodes( $child_nodes );
	}

	/**
	 * Setter for the child nodes.
	 * Notice: The number of child nodes can be 0.
	 *
	 * @param Forminator_Calculator_Parser_Node_Abstract[] $child_nodes
	 *
	 * @throws Forminator_Calculator_Exception
	 */
	public function set_child_nodes( $child_nodes ) {
		// Ensure integrity of $nodes array
		foreach ( $child_nodes as $child_node ) {
			if ( ! $child_node instanceof Forminator_Calculator_Parser_Node_Abstract ) {
				throw new Forminator_Calculator_Exception(
					'Error: Expected AbstractNode, but got "' . gettype( $child_node ) . '"'
				);
			}
		}

		$this->child_nodes = $child_nodes;
	}

	/**
	 * Returns the number of child nodes in this array node.
	 * Does not count the child nodes of the child nodes.
	 *
	 * @return int
	 */
	public function size() {
		return count( $this->child_nodes );
	}

	/**
	 * Returns true if the array node does not have any
	 * child nodes. This might sound strange but is possible.
	 *
	 * @return bool
	 */
	public function is_empty() {
		return ( $this->size() === 0 );
	}

	/**
	 * Getter for the child nodes
	 *
	 * @return Forminator_Calculator_Parser_Node_Abstract[]
	 */
	public function get_child_nodes() {
		return $this->child_nodes;
	}

}
