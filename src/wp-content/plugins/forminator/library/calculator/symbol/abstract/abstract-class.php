<?php

abstract class Forminator_Calculator_Symbol_Abstract {

	/**
	 * Array with the 1-n (exception: the Numbers class may have 0)
	 * unique identifiers (the textual representation of a symbol)
	 * of the symbol. Example: ['/', ':']
	 * Attention: The identifiers are case-sensitive, however,
	 * valid identifiers in a term are always written in lower-case.
	 * Therefore identifiers always have to be written in lower-case!
	 *
	 * @var string[]
	 */
	protected $identifiers = array();

	/**
	 * Getter for the identifiers of the symbol.
	 * Attention: The identifiers will be lower-cased!
	 *
	 * @return string[]
	 */
	final public function get_identifiers() {
		// Lower-case all identifiers to make it easier to find duplicate identifiers
		$identifiers = array_map( 'strtolower', $this->identifiers );

		return $identifiers;
	}

}
