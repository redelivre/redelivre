import FrontCalculatorParserToken from './front.calculator.parser.token';

export default class FrontCalculatorParserTokenizer {
	constructor(input) {

		/**
		 *
		 * @type {String}
		 */
		this.input = input;

		/**
		 * @type {number}
		 */
		this.currentPosition = 0;
	}

	/**
	 *
	 * @returns {FrontCalculatorParserToken[]}
	 */
	tokenize() {
		this.reset();

		var tokens = [];

		var token = this.readToken();
		while (token) {
			tokens.push(token);
			token = this.readToken();
		}

		return tokens;
	}

	/**
	 *
	 * @returns {FrontCalculatorParserToken}
	 */
	readToken() {
		this.stepOverWhitespace();

		var char = this.readCurrent();

		if (null === char) {
			return null;
		}

		var value = null;
		var type  = null;
		if (this.isLetter(char)) {
			value = this.readWord();
			type  = FrontCalculatorParserToken.TYPE_WORD;
		} else if (this.isDigit(char) || this.isPeriod(char)) {
			value = this.readNumber();
			type  = FrontCalculatorParserToken.TYPE_NUMBER;
		} else {
			value = this.readChar();
			type  = FrontCalculatorParserToken.TYPE_CHAR;
		}

		return new FrontCalculatorParserToken(type, value, this.currentPosition);
	}

	/**
	 * Returns true, if a given character is a letter (a-z and A-Z).
	 *
	 * @param char
	 * @returns {boolean}
	 */
	isLetter(char) {
		if (null === char) {
			return false;
		}

		var ascii = char.charCodeAt(0);

		/**
		 * ASCII codes: 65 = 'A', 90 = 'Z', 97 = 'a', 122 = 'z'--
		 **/
		return ((ascii >= 65 && ascii <= 90) || (ascii >= 97 && ascii <= 122));
	}

	/**
	 * Returns true, if a given character is a digit (0-9).
	 *
	 * @param char
	 * @returns {boolean}
	 */
	isDigit(char) {
		if (null === char) {
			return false;
		}

		var ascii = char.charCodeAt(0);

		/**
		 * ASCII codes: 48 = '0', 57 = '9'
		 */
		return (ascii >= 48 && ascii <= 57);
	}

	/**
	 * Returns true, if a given character is a period ('.').
	 *
	 * @param char
	 * @returns {boolean}
	 */
	isPeriod(char) {
		return ('.' === char);
	}

	/**
	 * Returns true, if a given character is whitespace.
	 * Notice: A null char is not seen as whitespace.
	 *
	 * @param char
	 * @returns {boolean}
	 */
	isWhitespace(char) {
		return [" ", "\t", "\n"].indexOf(char) >= 0;
	}

	stepOverWhitespace() {
		while (this.isWhitespace(this.readCurrent())) {
			this.readNext();
		}
	}

	/**
	 * Reads a word. Assumes that the cursor of the input stream
	 * currently is positioned at the beginning of a word.
	 *
	 * @returns {string}
	 */
	readWord() {
		var word = '';

		var char = this.readCurrent();
		// Try to read the word
		while (null !== char) {
			if (this.isLetter(char)) {
				word += char;
			} else {
				break;
			}

			// Just move the cursor to the next position
			char = this.readNext();
		}

		return word;
	}


	/**
	 * Reads a number (as a string). Assumes that the cursor
	 * of the input stream currently is positioned at the
	 * beginning of a number.
	 *
	 * @returns {string}
	 */
	readNumber() {
		var number      = '';
		var foundPeriod = false;

		// Try to read the number.
		// Notice: It does not matter if the number only consists of a single period
		// or if it ends with a period.
		var char = this.readCurrent();
		while (null !== char) {
			if (this.isPeriod(char) || this.isDigit(char)) {
				if (this.isPeriod(char)) {
					if (foundPeriod) {
						throw ('Error: A number cannot have more than one period');
					}

					foundPeriod = true;
				}

				number += char;
			} else {
				break;
			}

			// read next
			char = this.readNext();
		}

		return number;
	}

	/**
	 * Reads a single char. Assumes that the cursor of the input stream
	 * currently is positioned at a char (not on null).
	 *
	 * @returns {String}
	 */
	readChar() {
		var char = this.readCurrent();
		// Just move the cursor to the next position
		this.readNext();

		return char;
	}

	/**
	 *
	 * @returns {String|null}
	 */
	readCurrent() {
		var char = null;
		if (this.hasCurrent()) {
			char = this.input[this.currentPosition];
		}

		return char;
	}

	/**
	 * Move the the cursor to the next position.
	 * Will always move the cursor, even if the end of the string has been passed.
	 *
	 * @returns {String}
	 */
	readNext() {
		this.currentPosition++;
		return this.readCurrent();
	}

	/**
	 * Returns true if there is a character at the current position
	 *
	 * @returns {boolean}
	 */
	hasCurrent() {
		return (this.currentPosition < this.input.length);
	}

	reset() {
		this.currentPosition = 0;
	}
}
