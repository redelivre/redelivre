export default class FrontCalculatorSymbolAbstract {
	constructor() {
		/**
		 * Array with the 1-n (exception: the Numbers class may have 0)
		 * unique identifiers (the textual representation of a symbol)
		 * of the symbol. Example: ['/', ':']
		 * Attention: The identifiers are case-sensitive, however,
		 * valid identifiers in a term are always written in lower-case.
		 * Therefore identifiers always have to be written in lower-case!
		 *
		 * @type {String[]}
		 */
		this.identifiers = [];
	}

	/**
	 * Getter for the identifiers of the symbol.
	 * Attention: The identifiers will be lower-cased!
	 * @returns {String[]}
	 */
	getIdentifiers() {
		var lowerIdentifiers = [];

		this.identifiers.forEach(function (identifier) {
			lowerIdentifiers.push(identifier.toLowerCase());
		});

		return lowerIdentifiers;
	}
}
