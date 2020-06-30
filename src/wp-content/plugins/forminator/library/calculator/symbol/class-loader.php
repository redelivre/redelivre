<?php

require_once dirname( __FILE__ ) . '/abstract/abstract-class.php';
require_once dirname( __FILE__ ) . '/abstract/abstract-class-constant.php';
require_once dirname( __FILE__ ) . '/abstract/abstract-class-function.php';
require_once dirname( __FILE__ ) . '/abstract/abstract-class-operator.php';

require_once dirname( __FILE__ ) . '/class-number.php';
require_once dirname( __FILE__ ) . '/class-separator.php';


require_once dirname( __FILE__ ) . '/brackets/class-opening-bracket.php';
require_once dirname( __FILE__ ) . '/brackets/class-closing-bracket.php';

require_once dirname( __FILE__ ) . '/constants/class-constant-pi.php';

require_once dirname( __FILE__ ) . '/operators/class-operator-addition.php';
require_once dirname( __FILE__ ) . '/operators/class-operator-division.php';
require_once dirname( __FILE__ ) . '/operators/class-operator-exponentiation.php';
require_once dirname( __FILE__ ) . '/operators/class-operator-modulo.php';
require_once dirname( __FILE__ ) . '/operators/class-operator-multiplication.php';
require_once dirname( __FILE__ ) . '/operators/class-operator-subtraction.php';

require_once dirname( __FILE__ ) . '/functions/class-function-abs.php';
require_once dirname( __FILE__ ) . '/functions/class-function-avg.php';
require_once dirname( __FILE__ ) . '/functions/class-function-ceil.php';
require_once dirname( __FILE__ ) . '/functions/class-function-days-between.php';
require_once dirname( __FILE__ ) . '/functions/class-function-floor.php';
require_once dirname( __FILE__ ) . '/functions/class-function-hours-between.php';
require_once dirname( __FILE__ ) . '/functions/class-function-max.php';
require_once dirname( __FILE__ ) . '/functions/class-function-min.php';
require_once dirname( __FILE__ ) . '/functions/class-function-minutes-between.php';
require_once dirname( __FILE__ ) . '/functions/class-function-months-between.php';
require_once dirname( __FILE__ ) . '/functions/class-function-round.php';
require_once dirname( __FILE__ ) . '/functions/class-function-seconds-between.php';
require_once dirname( __FILE__ ) . '/functions/class-function-years-between.php';


/**
 * The symbol container manages an array with all symbol objects.
 *
 */
class Forminator_Calculator_Symbol_Loader {

	/**
	 * Symbol Register
	 *
	 * @var array
	 */
	private $symbol_registry = array(
		'Forminator_Calculator_Symbol_Number',

		'Forminator_Calculator_Symbol_Separator',

		'Forminator_Calculator_Symbol_Opening_Bracket',
		'Forminator_Calculator_Symbol_Closing_Bracket',

		'Forminator_Calculator_Symbol_Constant_Pi',

		'Forminator_Calculator_Symbol_Operator_Addition',
		'Forminator_Calculator_Symbol_Operator_Division',
		'Forminator_Calculator_Symbol_Operator_Exponentiation',
		'Forminator_Calculator_Symbol_Operator_Modulo',
		'Forminator_Calculator_Symbol_Operator_Multiplication',
		'Forminator_Calculator_Symbol_Operator_Subtraction',

		'Forminator_Calculator_Symbol_Function_Abs',
		'Forminator_Calculator_Symbol_Function_Avg',
		'Forminator_Calculator_Symbol_Function_Ceil',
		'Forminator_Calculator_Symbol_Function_Days_Between',
		'Forminator_Calculator_Symbol_Function_Floor',
		'Forminator_Calculator_Symbol_Function_Hours_Between',
		'Forminator_Calculator_Symbol_Function_Max',
		'Forminator_Calculator_Symbol_Function_Min',
		'Forminator_Calculator_Symbol_Function_Minutes_Between',
		'Forminator_Calculator_Symbol_Function_Months_Between',
		'Forminator_Calculator_Symbol_Function_Round',
		'Forminator_Calculator_Symbol_Function_Seconds_Between',
		'Forminator_Calculator_Symbol_Function_Years_Between',
	);
	/**
	 * Array with all available symbols
	 *
	 * @var Forminator_Calculator_Symbol_Abstract[]
	 */
	protected $symbols;

	/**
	 * SymbolManager constructor.
	 */
	public function __construct() {
		$this->prepare();
	}

	/**
	 * Retrieves the list of available symbol classes,
	 * creates objects of these classes and stores them.
	 *
	 * @return void
	 * @throws \LengthException
	 */
	protected function prepare() {
		$symbol_registry = $this->symbol_registry;

		/**
		 * Filtered registered symbols on calculators
		 *
		 * @since 1.7
		 *
		 * @param string[] $symbol_registry
		 *
		 * @return string[]
		 */
		$symbol_registry = apply_filters( 'forminator_calculator_symbol_registry', $symbol_registry );

		foreach ( $symbol_registry as $symbol_class_name ) {
			$symbol = new $symbol_class_name();

			$this->symbols[ $symbol_class_name ] = $symbol;
		}
	}

	/**
	 * Returns the symbol that has the given identifier.
	 * Returns null if none is found.
	 *
	 * @param string $identifier
	 *
	 * @return Forminator_Calculator_Symbol_Abstract|null
	 */
	public function find( $identifier ) {

		// allow strict compare with strtolower
		$identifier = strtolower( $identifier );

		foreach ( $this->symbols as $symbol ) {
			if ( in_array( $identifier, $symbol->get_identifiers(), true ) ) {
				return $symbol;
			}
		}

		return null;
	}

	/**
	 * Returns all symbols that inherit from a given abstract
	 * parent type (class): The parent type has to be an
	 * AbstractSymbol.
	 * Notice: The parent type name will not be validated!
	 *
	 * @param string $parent_type_name
	 *
	 * @return Forminator_Calculator_Symbol_Abstract[]
	 */
	public function find_sub_types( $parent_type_name ) {
		$symbols = array();

		foreach ( $this->symbols as $symbol ) {
			if ( $symbol instanceof $parent_type_name ) {
				$symbols[] = $symbol;
			}
		}

		return $symbols;
	}

}
