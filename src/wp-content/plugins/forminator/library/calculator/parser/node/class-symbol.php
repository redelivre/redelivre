<?php


/**
 * A symbol node is a node in the syntax tree.
 * Leaf nodes do not have any child nodes
 * (parent nodes can have child nodes). A
 * symbol node represents a mathematical symbol.
 * Nodes are created by the parser.
 *
 */
class Forminator_Calculator_Parser_Node_Symbol extends Forminator_Calculator_Parser_Node_Abstract {

	/**
	 * The token of the node. It contains the value.
	 *
	 * @var Forminator_Calculator_Parser_Token
	 */
	protected $token;

	/**
	 * The symbol of the node. It defines the type of the node.
	 *
	 * @var Forminator_Calculator_Symbol_Abstract
	 */
	protected $symbol;

	/**
	 * Unary operators need to be treated specially.
	 * Therefore a node has to know if it (or to be
	 * more precise the symbol of the node)
	 * represents a unary operator.
	 * Example : -1, -4
	 *
	 * @var bool
	 */
	protected $is_unary_operator = false;

	/**
	 * SymbolNode constructor.
	 *
	 * @param Forminator_Calculator_Parser_Token    $token
	 * @param Forminator_Calculator_Symbol_Abstract $symbol
	 */
	public function __construct( $token, $symbol ) {

		$this->token  = $token;
		$this->symbol = $symbol;
	}

	/**
	 * Getter for the token
	 *
	 * @return Forminator_Calculator_Parser_Token
	 */
	public function get_token() {
		return $this->token;
	}

	/**
	 * Getter for the symbol
	 *
	 * @return Forminator_Calculator_Symbol_Abstract
	 */
	public function get_symbol() {
		return $this->symbol;
	}

	/**
	 * Setter to remember that the node (or to be more precise the
	 * symbol of the node) represents a unary operator
	 *
	 * @param bool $is_unary_operator
	 *
	 * @throws Forminator_Calculator_Exception
	 */
	public function set_is_unary_operator( $is_unary_operator = true ) {
		if ( ! $this->symbol instanceof Forminator_Calculator_Symbol_Operator_Abstract ) {
			throw new Forminator_Calculator_Exception(
				'Error: Cannot mark node as unary operator, because symbol is not an operator but of type "' .
				gettype( $this->get_symbol() ) . '"'
			);
		}

		$this->is_unary_operator = $is_unary_operator;
	}

	/**
	 * Returns true if the node (or to be more precise the
	 * symbol of the node) represents a unary operator
	 *
	 * @return bool
	 */
	public function is_unary_operator() {
		return $this->is_unary_operator;
	}

}
