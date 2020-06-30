<?php

/**
 * This class is the base class for all symbols that are of the type "constant".
 * We recommend to use names as textual representations for this type of symbol.
 * Please take note of the fact that the precision of PHP float constants
 * (for example M_PI) is based on the "precision" directive in php.ini,
 * which defaults to 14.
 */
abstract class Forminator_Calculator_Symbol_Constant_Abstract extends Forminator_Calculator_Symbol_Abstract {

	/**
	 * This is the value of the constant. We use 0 as an example here,
	 * but you are supposed to overwrite this in the concrete constant class.
	 * Usually mathematical constants are not integers, however,
	 * you are allowed to use an integer in this context.
	 *
	 * @var int|float
	 */
	protected $value = 0;

	/**
	 * Getter for the value property.
	 * Typically the value of the constant should be stored in $this->value.
	 * However, in case you want to calculate the value at runtime,
	 * feel free to overwrite this getter method.
	 *
	 * @return int|float
	 */
	public function get_value() {
		return $this->value;
	}

}
