import FrontCalculatorSymbolAbstract from "./front.calculator.symbol.abstract";

/**
 * This class is the base class for all symbols that are of the type "(binary) operator".
 * The textual representation of an operator consists of a single char that is not a letter.
 * It is worth noting that a operator has the same power as a function with two parameters.
 * Operators are always binary. To mimic a unary operator you might want to create a function
 * that accepts one parameter.
 */
export default class FrontCalculatorSymbolOperatorAbstract extends FrontCalculatorSymbolAbstract {
	constructor() {
		super();

		/**
		 * The operator precedence determines which operators to perform first
		 * in order to evaluate a given term.
		 * You are supposed to overwrite this constant in the concrete constant class.
		 * Take a look at other operator classes to see the precedences of the predefined operators.
		 * 0: default, > 0: higher, < 0: lower
		 *
		 * @type {number}
		 */
		this.precedence = 0;

		/**
		 * Usually operators are binary, they operate on two operands (numbers).
		 * But some can operate on one operand (number). The operand of a unary
		 * operator is always positioned after the operator (=prefix notation).
		 * Good example: "-1" Bad Example: "1-"
		 * If you want to create a unary operator that operates on the left
		 * operand, you should use a function instead. Functions with one
		 * parameter execute unary operations in functional notation.
		 * Notice: Operators can be unary AND binary (but this is a rare case)
		 *
		 * @type {boolean}
		 */
		this.operatesUnary = false;

		/**
		 * Usually operators are binary, they operate on two operands (numbers).
		 * Notice: Operators can be unary AND binary (but this is a rare case)
		 *
		 * @type {boolean}
		 */
		this.operatesBinary = true;
	}

	operate(leftNumber, rightNumber) {
		return 0.0;
	}
}
