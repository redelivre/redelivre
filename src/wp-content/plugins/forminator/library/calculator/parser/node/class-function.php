<?php


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
class Forminator_Calculator_Parser_Node_Function extends Forminator_Calculator_Parser_Node_Container {

	/**
	 * @var Forminator_Calculator_Parser_Node_Symbol
	 */
	protected $symbol_node;

	/**
	 * ContainerNode constructor.
	 * Attention: The constructor is differs from the constructor
	 * of the parent class!
	 *
	 * @param Forminator_Calculator_Parser_Node_Abstract[] $child_nodes
	 * @param Forminator_Calculator_Parser_Node_Symbol     $symbol_node
	 *
	 * @throws Forminator_Calculator_Exception
	 */
	public function __construct( $child_nodes, $symbol_node ) {
		parent::__construct( $child_nodes );

		$this->symbol_node = $symbol_node;
	}

	/**
	 * @return Forminator_Calculator_Parser_Node_Symbol
	 */
	public function get_symbol_node() {
		return $this->symbol_node;
	}

}
