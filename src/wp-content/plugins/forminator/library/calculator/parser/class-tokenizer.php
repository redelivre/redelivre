<?php

/**
 * Class Forminator_Calculator_Tokenizer
 *
 */
class Forminator_Calculator_Parser_Tokenizer {

	/**
	 * @var string
	 */
	public $input = '';

	/**
	 * @var int
	 */
	public $current_position = 0;

	public function __construct( $input = null ) {
		$this->input = $input;
	}

	/**
	 * @return Forminator_Calculator_Parser_Token[]
	 * @throws Forminator_Calculator_Exception
	 */
	public function tokenize() {
		$this->reset();

		$tokens = array();

		$token = $this->read_token();
		while ( $token ) {
			$tokens[] = $token;
			$token    = $this->read_token();
		}

		return $tokens;
	}

	/**
	 * @return Forminator_Calculator_Parser_Token|null
	 * @throws Forminator_Calculator_Exception
	 */
	protected function read_token() {
		$this->step_over_whitespace();

		$char = $this->read_current();

		if ( null === $char ) {
			return null;
		}

		if ( $this->is_letter( $char ) ) {
			$value = $this->read_word();
			$type  = Forminator_Calculator_Parser_Token::TYPE_WORD;
		} elseif ( $this->is_digit( $char ) || $this->is_period( $char ) ) {
			$value = $this->read_number();
			$type  = Forminator_Calculator_Parser_Token::TYPE_NUMBER;
		} else {
			$value = $this->read_char();
			$type  = Forminator_Calculator_Parser_Token::TYPE_CHAR;
		}

		$token = new Forminator_Calculator_Parser_Token( $type, $value, $this->get_current_position() );

		return $token;
	}

	/**
	 * Returns true, if a given character is a letter (a-z and A-Z).
	 *
	 * @param string $char A single character
	 *
	 * @return bool
	 */
	protected function is_letter( $char ) {
		if ( null === $char ) {
			return false;
		}

		// Notice: ord(null) will return 0.
		// ord() does not work with utf-8 characters.
		$ascii = ord( $char );

		/**
		 * ASCII codes: 65 = 'A', 90 = 'Z', 97 = 'a', 122 = 'z'--
		 **/

		return ( ( $ascii >= 65 && $ascii <= 90 ) || ( $ascii >= 97 && $ascii <= 122 ) );
	}

	/**
	 * Returns true, if a given character is a digit (0-9).
	 *
	 * @param string|null $char A single character
	 *
	 * @return bool
	 */
	protected function is_digit( $char ) {
		if ( null === $char ) {
			return false;
		}

		// Notice: ord(null) will return 0.
		// ord() does not work with utf-8 characters.
		$ascii = ord( $char );

		/**
		 * ASCII codes: 48 = '0', 57 = '9'
		 */
		return ( $ascii >= 48 && $ascii <= 57 );
	}

	/**
	 * Returns true, if a given character is a period ('.').
	 *
	 * @param string|null $char A single character
	 *
	 * @return bool
	 */
	protected function is_period( $char ) {
		return ( '.' === $char );
	}

	/**
	 * Returns true, if a given character is whitespace.
	 * Notice: A null char is not seen as whitespace.
	 *
	 * @var string|null $char
	 * @return bool
	 */
	protected function is_whitespace( $char ) {
		return in_array( $char, array( " ", "\t", "\n" ), true );
	}

	/**
	 * Moves the pointer to the next char that is not whitespace.
	 * Might be a null char, might not move the pointer at all.
	 *
	 * @return void
	 */
	protected function step_over_whitespace() {
		while ( $this->is_whitespace( $this->read_current() ) ) {
			$this->read_next();
		}
	}

	/**
	 * Reads a word. Assumes that the cursor of the input stream
	 * currently is positioned at the beginning of a word.
	 *
	 * @return string
	 */
	protected function read_word() {
		$word = '';

		$char = $this->read_current();
		// Try to read the word
		while ( null !== $char ) {
			if ( $this->is_letter( $char ) ) {
				$word .= $char;
			} else {
				break;
			}

			// Just move the cursor to the next position
			$char = $this->read_next();
		}

		return $word;
	}

	/**
	 * Reads a number (as a string). Assumes that the cursor
	 * of the input stream currently is positioned at the
	 * beginning of a number.
	 *
	 * @return string
	 * @throws Forminator_Calculator_Exception
	 */
	protected function read_number() {
		$number       = '';
		$found_period = false;

		// Try to read the number.
		// Notice: It does not matter if the number only consists of a single period
		// or if it ends with a period.
		$char = $this->read_current();
		while ( null !== $char ) {
			if ( $this->is_period( $char ) || $this->is_digit( $char ) ) {
				if ( $this->is_period( $char ) ) {
					if ( $found_period ) {
						throw new Forminator_Calculator_Exception( 'Error: A number cannot have more than one period' );
					}

					$found_period = true;
				}

				$number .= $char;
			} else {
				break;
			}

			// read next
			$char = $this->read_next();
		}

		return $number;
	}

	/**
	 * Reads a single char. Assumes that the cursor of the input stream
	 * currently is positioned at a char (not on null).
	 *
	 * @return string
	 */
	protected function read_char() {
		$char = $this->read_current();
		// Just move the cursor to the next position
		$this->read_next();

		return $char;
	}

	/**
	 * Move the the cursor to the next position.
	 * Will always move the cursor, even if the end of the string has been passed.
	 *
	 * @return string|null
	 */
	public function read_next() {
		$this->current_position ++;

		return $this->read_current();
	}

	/**
	 * Returns the current character.
	 *
	 * @return string|null
	 */
	public function read_current() {
		if ( $this->has_current() ) {
			$char = $this->input[ $this->current_position ];
		} else {
			$char = null;
		}

		return $char;
	}

	/**
	 * Returns true if there is a character at the current position
	 *
	 * @return bool
	 */
	public function has_current() {
		return ( $this->current_position < strlen( $this->input ) );
	}

	/**
	 * Resets the position of the cursor to the beginning of the string.
	 *
	 * @return void
	 */
	public function reset() {
		$this->current_position = 0;
	}

	/**
	 * Getter for the cursor position
	 *
	 * @return int
	 */
	public function get_current_position() {
		return $this->current_position;
	}
}
