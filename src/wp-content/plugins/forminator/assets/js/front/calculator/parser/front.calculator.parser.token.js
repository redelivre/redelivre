export default class FrontCalculatorParserToken {
	static get TYPE_WORD() {
		return 1;
	}

	static get TYPE_CHAR() {
		return 2;
	}

	static get TYPE_NUMBER() {
		return 3;
	}

	constructor(type, value, position) {

		/**
		 *
		 * @type {Number}
		 */
		this.type = type;

		/**
		 *
		 * @type {String|Number}
		 */
		this.value = value;

		/**
		 *
		 * @type {Number}
		 */
		this.position = position;
	}
}
