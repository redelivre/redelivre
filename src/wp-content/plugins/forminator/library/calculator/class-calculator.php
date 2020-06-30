<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**********
 * Attempt to rewrite
 * https://github.com/chriskonnertz/string-calc
 * License (26-dec-2018) : MIT
 *
 * what changes :
 * - PHP 5.2 Compat
 * - Reduce/Remove extensibility, since it will managed by ours in the future
 * - Remove unnecessary abstract classes to reduce size
 * - wpcs compat
 * - use WP hook as extensibility alternatives
 ***********/


require_once dirname( __FILE__ ) . '/class-exception.php';

// tokenizer
require_once dirname( __FILE__ ) . '/parser/class-token.php';
require_once dirname( __FILE__ ) . '/parser/class-tokenizer.php';

// load symbols
require_once dirname( __FILE__ ) . '/symbol/class-loader.php';

// load nodes
require_once dirname( __FILE__ ) . '/parser/node/abstract-class.php';
require_once dirname( __FILE__ ) . '/parser/node/class-container.php';
require_once dirname( __FILE__ ) . '/parser/node/class-function.php';
require_once dirname( __FILE__ ) . '/parser/node/class-symbol.php';

// parser
require_once dirname( __FILE__ ) . '/parser/class-parser.php';

/**
 * Class Forminator_Calculator
 *
 * @since 1.7
 */
class Forminator_Calculator {

	/**
	 * @var string
	 */
	protected $term;

	/**
	 * @var Forminator_Calculator_Parser_Tokenizer
	 */
	protected $tokenizer;

	/**
	 * @var Forminator_Calculator_Symbol_Loader
	 */
	protected $symbol_loader;

	/**
	 * @var bool
	 */
	protected $is_throwable = false;

	/**
	 * @var Forminator_Calculator_Parser
	 */
	protected $parser;

	public function __construct( $term ) {
		$this->term = $term;

		$this->setup_tokenizer();

		$this->symbol_loader = new Forminator_Calculator_Symbol_Loader();

		$this->setup_parser();

		$this->prepare_term();

	}

	/**
	 * Setup tokenizer
	 *
	 * @uses apply_filters()
	 */
	public function setup_tokenizer() {
		$term      = $this->term;
		$tokenizer = new Forminator_Calculator_Parser_Tokenizer( $term );

		/**
		 * Filter tokenizer to be used on calculator
		 *
		 * @since 1.7
		 *
		 * @param Forminator_Calculator_Parser_Tokenizer $tokenizer
		 * @param string                                 $term
		 *
		 * @return Forminator_Calculator_Parser_Tokenizer
		 */
		$tokenizer = apply_filters( 'forminator_calculator_tokenizer', $tokenizer, $term );

		$this->tokenizer = $tokenizer;
	}

	/**
	 * Setup Parser
	 *
	 * @uses apply_filters()
	 */
	public function setup_parser() {
		$term          = $this->term;
		$symbol_loader = $this->symbol_loader;
		$parser        = new Forminator_Calculator_Parser( $symbol_loader );

		/**
		 * Filter parser to be used on calculator
		 *
		 * @since 1.7
		 *
		 * @param Forminator_Calculator_Parser        $parser
		 * @param Forminator_Calculator_Symbol_Loader $symbol_loader
		 * @param string                              $term
		 *
		 * @return Forminator_Calculator_Parser
		 */
		$parser = apply_filters( 'forminator_calculator_tokenizer', $parser, $symbol_loader, $term );

		$this->parser = $parser;
	}

	public function prepare_term() {
		$term = $this->term;

		/**
		 * Filter term that will be parsed by calculator
		 *
		 * @since 1.7
		 *
		 * @param string $term
		 *
		 * @return string
		 */
		$term = apply_filters( 'forminator_calculator_prepare_term', $term );

		$this->term = $term;
	}

	/**
	 * @return Forminator_Calculator_Parser_Node_Container|boolean
	 * @throws Forminator_Calculator_Exception
	 */
	public function parse() {
		try {
			// reset
			$this->tokenizer->input = $this->term;
			$this->tokenizer->reset();

			$tokens = $this->tokenizer->tokenize();
			if ( count( $tokens ) === 0 ) {
				throw new Forminator_Calculator_Exception( 'Error: Empty token of calculator term.' );
			}

			$root_node = $this->parser->parse( $tokens );
			if ( $root_node->is_empty() ) {
				throw new Forminator_Calculator_Exception( 'Error: Empty nodes of calculator tokens.' );
			}

			return $root_node;
		} catch ( Forminator_Calculator_Exception $e ) {
			// suppress
			forminator_maybe_log( __METHOD__, $e->getMessage(), $e->getTrace() );

			if ( $this->is_throwable ) {
				throw $e;
			}
		}

		return false;
	}

	/**
	 * @return float|int
	 * @throws Forminator_Calculator_Exception
	 */
	public function calculate() {
		$result    = 0;
		$root_node = $this->parse();

		if ( false === $root_node ) {
			return $result;
		}

		try {
			$result = $this->calculate_node( $root_node );
		} catch ( Forminator_Calculator_Exception $e ) {
			// suppress
			forminator_maybe_log( __METHOD__, $e->getMessage(), $e->getTrace() );

			if ( $this->is_throwable ) {
				throw $e;
			}
		}

		return $result;
	}

	/**
	 * Calculates the numeric value / result of a node of
	 * any known and calculable type. (For example symbol
	 * nodes with a symbol of type separator are not
	 * calculable.)
	 *
	 * @param Forminator_Calculator_Parser_Node_Abstract $node
	 *
	 * @return float|int
	 * @throws Forminator_Calculator_Exception
	 */
	protected function calculate_node( $node ) {
		if ( $node instanceof Forminator_Calculator_Parser_Node_Symbol ) {
			/** @var Forminator_Calculator_Parser_Node_Symbol $node */

			return $this->calculate_symbol_node( $node );
		} elseif ( $node instanceof Forminator_Calculator_Parser_Node_Function ) {
			/** @var Forminator_Calculator_Parser_Node_Function $node */

			return $this->calculate_function_node( $node );
		} elseif ( $node instanceof Forminator_Calculator_Parser_Node_Container ) {
			/** @var Forminator_Calculator_Parser_Node_Container $node */

			return $this->calculate_container_node( $node );
		} else {
			throw new Forminator_Calculator_Exception( 'Error: Cannot calculate node of unknown type "' . get_class( $node ) . '"' );// @codeCoverageIgnore
		}
	}

	/**
	 * This method actually calculates the results of every sub-terms
	 * in the syntax tree (which consists of nodes).
	 * It can call itself recursively.
	 * Attention: $node must not be of type FunctionNode!
	 *
	 * @param Forminator_Calculator_Parser_Node_Container $container_node
	 *
	 * @return float|int
	 * @throws Forminator_Calculator_Exception
	 */
	protected function calculate_container_node( $container_node ) {
		if ( $container_node instanceof Forminator_Calculator_Parser_Node_Function ) {
			throw new Forminator_Calculator_Exception( 'Error: Expected container node but got a function node' ); // @codeCoverageIgnore
		}

		$nodes = $container_node->get_child_nodes();

		$ordered_operator_nodes = $this->detect_calculation_order( $nodes );

		// Actually calculate the term. Iterates over the ordered operators and
		// calculates them, then replaces the parts of the operation by the result.
		foreach ( $ordered_operator_nodes as $index => $operator_node ) {
			reset( $nodes );
			while ( key( $nodes ) !== $index ) {
				$left_operand       = current( $nodes );
				$left_operand_index = key( $nodes );
				next( $nodes ); // back to operator cursor
			}

			$right_operand       = next( $nodes );
			$right_operand_index = key( $nodes );
			$right_number        = is_numeric( $right_operand ) ? $right_operand : $this->calculate_node( $right_operand );

			/** @var Forminator_Calculator_Symbol_Operator_Abstract $symbol */
			$symbol = $operator_node->get_symbol();

			if ( $operator_node->is_unary_operator() ) {
				$result = $symbol->operate( null, $right_number );

				// Replace the participating symbols of the operation by the result
				unset( $nodes[ $right_operand_index ] );
				$nodes[ $index ] = $result;
			} else {
				if ( isset( $left_operand_index ) && isset( $left_operand ) ) {
					$left_number = is_numeric( $left_operand ) ? $left_operand : $this->calculate_node( $left_operand );

					$result = $symbol->operate( $left_number, $right_number );

					// Replace the participating symbols of the operation by the result
					unset( $nodes[ $left_operand_index ] );
					unset( $nodes[ $right_operand_index ] );
					$nodes[ $index ] = $result;
				}

			}
		}

		if ( count( $nodes ) === 0 ) {
			throw new Forminator_Calculator_Exception( 'Error: Missing calculable subterm. Are there empty brackets?' );
		}

		if ( count( $nodes ) > 1 ) {
			throw new Forminator_Calculator_Exception( 'Error: Missing operators between parts of the term.' );
		}

		// The only remaining element of the $nodes array contains the overall result
		$result = end( $nodes );

		// If the $nodes array did not contain any operator (but only one node) than
		// the result of this node has to be calculated now
		if ( ! is_numeric( $result ) ) {
			return $this->calculate_node( $result );
		}

		return $result;
	}

	/**
	 * Returns the numeric value of a function node.
	 *
	 * @param Forminator_Calculator_Parser_Node_Function $function_node
	 *
	 * @return int|float
	 * @throws Forminator_Calculator_Exception
	 */
	protected function calculate_function_node( $function_node ) {
		$nodes = $function_node->get_child_nodes();

		$arguments            = array(); // ex : func(1+2,3,4) : 1+2 need to be calculated first
		$argument_child_nodes = array();

		foreach ( $nodes as $node ) {
			if ( $node instanceof Forminator_Calculator_Parser_Node_Symbol ) {
				/** @var Forminator_Calculator_Parser_Node_Symbol $node */

				if ( $node->get_symbol() instanceof Forminator_Calculator_Symbol_Separator ) {
					$container_node       = new Forminator_Calculator_Parser_Node_Container( $argument_child_nodes );
					$arguments[]          = $this->calculate_container_node( $container_node );
					$argument_child_nodes = array();
				} else {
					$argument_child_nodes[] = $node;
				}
			} else {
				$argument_child_nodes[] = $node;
			}
		}

		if ( count( $argument_child_nodes ) > 0 ) {
			$container_node = new Forminator_Calculator_Parser_Node_Container( $argument_child_nodes );
			$arguments[]    = $this->calculate_container_node( $container_node );
		}

		/** @var Forminator_Calculator_Symbol_Function_Abstract $symbol */
		$symbol = $function_node->get_symbol_node()->get_symbol();

		$result = $symbol->execute( $arguments );

		return $result;
	}

	/**
	 * Returns the numeric value of a symbol node.
	 * Attention: $node->symbol must not be of type AbstractOperator!
	 *
	 * @param Forminator_Calculator_Parser_Node_Symbol $symbol_node
	 *
	 * @return int|float
	 * @throws Forminator_Calculator_Exception
	 */
	protected function calculate_symbol_node( $symbol_node ) {
		$symbol = $symbol_node->get_symbol();

		if ( $symbol instanceof Forminator_Calculator_Symbol_Number ) {
			$number = $symbol_node->get_token()->value;

			// Convert string to int or float (depending on the type of the number)
			// Attention: The fractional part of a PHP float can only have a limited length.
			// If the number has a longer fractional part, it will be cut.
			$number = 0 + $number;
		} elseif ( $symbol instanceof Forminator_Calculator_Symbol_Constant_Abstract ) {
			/** @var Forminator_Calculator_Symbol_Constant_Abstract $symbol */

			$number = $symbol->get_value();
		} else {
			throw new Forminator_Calculator_Exception( 'Error: Found symbol of unexpected type "' . get_class( $symbol ) . '", expected number or constant' );
		}

		return $number;
	}

	/**
	 * Detect the calculation order of a given array of nodes.
	 * Does only care for the precedence of operators.
	 * Does not care for child nodes of container nodes.
	 * Returns a new array with ordered symbol nodes.
	 *
	 * @param Forminator_Calculator_Parser_Node_Abstract[] $nodes
	 *
	 * @return Forminator_Calculator_Parser_Node_Symbol[]
	 */
	protected function detect_calculation_order( $nodes ) {
		$operator_nodes = array();

		// Store all symbol nodes that have a symbol of type abstract operator in an array
		foreach ( $nodes as $index => $node ) {
			if ( $node instanceof Forminator_Calculator_Parser_Node_Symbol ) {
				if ( $node->get_symbol() instanceof Forminator_Calculator_Symbol_Operator_Abstract ) {
					$operator_nodes[ $index ] = $node;
				}
			}
		}

		// Using Quick-sort algorithm to sort the operators according to their precedence. Keeps the indices.
		uasort( $operator_nodes, array( $this, 'sort_operator_precedence' ) );

		return $operator_nodes;
	}

	/**
	 *
	 * Returning 1 means $nodeTwo before $nodeOne, returning -1 means $nodeOne before $nodeTwo.
	 *
	 * @param Forminator_Calculator_Parser_Node_Symbol $node_one
	 * @param Forminator_Calculator_Parser_Node_Symbol $node_two
	 *
	 * @return int
	 */
	private function sort_operator_precedence( $node_one, $node_two ) {

		// First-level precedence of node one
		/** @var Forminator_Calculator_Symbol_Operator_Abstract $symbol_one */
		$symbol_one     = $node_one->get_symbol();
		$precedence_one = 2;
		if ( $node_one->is_unary_operator() ) {
			$precedence_one = 3;
		}

		// First-level precedence of node two
		/** @var Forminator_Calculator_Symbol_Operator_Abstract $symbol_two */
		$symbol_two     = $node_two->get_symbol();
		$precedence_two = 2;
		if ( $node_two->is_unary_operator() ) {
			$precedence_two = 3;
		}

		// If the first-level precedence is the same, compare the second-level precedence
		if ( $precedence_one === $precedence_two ) {
			$precedence_one = $symbol_one->get_precedence();
			$precedence_two = $symbol_two->get_precedence();
		}

		// If the second-level precedence is the same, we have to ensure that the sorting algorithm does
		// insert the node / token that is left in the term before the node / token that is right.
		// Therefore we cannot return 0 but compare the positions and return 1 / -1.
		if ( $precedence_one === $precedence_two ) {
			return ( $node_one->get_token()->position < $node_two->get_token()->position ) ? - 1 : 1;
		}

		return ( $precedence_one < $precedence_two ) ? 1 : - 1;

	}

	/**
	 * @param $is_throwable
	 */
	public function set_is_throwable( $is_throwable ) {
		$this->is_throwable = $is_throwable;
	}
}
