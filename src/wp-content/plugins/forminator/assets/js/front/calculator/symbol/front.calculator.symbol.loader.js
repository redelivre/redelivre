import FrontCalculatorSymbolNumber from "./front.calculator.symbol.number";
import FrontCalculatorSymbolSeparator from "./front.calculator.symbol.separator";
import FrontCalculatorSymbolOpeningBracket from "./brackets/front.calculator.symbol.opening.bracket";
import FrontCalculatorSymbolClosingBracket from "./brackets/front.calculator.symbol.closing.bracket";
import FrontCalculatorSymbolConstantPi from "./constants/front.calculator.symbol.constant.pi";
import FrontCalculatorSymbolOperatorAddition from "./operators/front.calculator.symbol.operator.addition";
import FrontCalculatorSymbolOperatorDivision from "./operators/front.calculator.symbol.operator.division";
import FrontCalculatorSymbolOperatorExponentiation from "./operators/front.calculator.symbol.operator.exponentiation";
import FrontCalculatorSymbolOperatorModulo from "./operators/front.calculator.symbol.operator.modulo";
import FrontCalculatorSymbolOperatorMultiplication from "./operators/front.calculator.symbol.operator.multiplication";
import FrontCalculatorSymbolOperatorSubtraction from "./operators/front.calculator.symbol.operator.subtraction";
import FrontCalculatorSymbolFunctionAbs from "./functions/front.calculator.symbol.function.abs";
import FrontCalculatorSymbolFunctionAvg from "./functions/front.calculator.symbol.function.avg";
import FrontCalculatorSymbolFunctionCeil from "./functions/front.calculator.symbol.function.ceil";
import FrontCalculatorSymbolFunctionFloor from "./functions/front.calculator.symbol.function.floor";
import FrontCalculatorSymbolFunctionMax from "./functions/front.calculator.symbol.function.max";
import FrontCalculatorSymbolFunctionMin from "./functions/front.calculator.symbol.function.min";
import FrontCalculatorSymbolFunctionRound from "./functions/front.calculator.symbol.function.round";

export default class FrontCalculatorSymbolLoader {
	constructor() {
		/**
		 *
		 * @type {{FrontCalculatorSymbolOperatorModulo: FrontCalculatorSymbolOperatorModulo, FrontCalculatorSymbolOperatorSubtraction: FrontCalculatorSymbolOperatorSubtraction, FrontCalculatorSymbolOperatorExponentiation: FrontCalculatorSymbolOperatorExponentiation, FrontCalculatorSymbolOperatorAddition: FrontCalculatorSymbolOperatorAddition, FrontCalculatorSymbolClosingBracket: FrontCalculatorSymbolClosingBracket, FrontCalculatorSymbolFunctionMax: FrontCalculatorSymbolFunctionMax, FrontCalculatorSymbolFunctionCeil: FrontCalculatorSymbolFunctionCeil, FrontCalculatorSymbolSeparator: FrontCalculatorSymbolSeparator, FrontCalculatorSymbolOperatorMultiplication: FrontCalculatorSymbolOperatorMultiplication, FrontCalculatorSymbolFunctionAbs: FrontCalculatorSymbolFunctionAbs, FrontCalculatorSymbolFunctionAvg: FrontCalculatorSymbolFunctionAvg, FrontCalculatorSymbolFunctionFloor: FrontCalculatorSymbolFunctionFloor, FrontCalculatorSymbolFunctionMin: FrontCalculatorSymbolFunctionMin, FrontCalculatorSymbolOperatorDivision: FrontCalculatorSymbolOperatorDivision, FrontCalculatorSymbolNumber: FrontCalculatorSymbolNumber, FrontCalculatorSymbolOpeningBracket: FrontCalculatorSymbolOpeningBracket, FrontCalculatorSymbolConstantPi: FrontCalculatorSymbolConstantPi, FrontCalculatorSymbolFunctionRound: FrontCalculatorSymbolFunctionRound}}
		 */
		this.symbols = {
			FrontCalculatorSymbolNumber: new FrontCalculatorSymbolNumber(),

			FrontCalculatorSymbolSeparator: new FrontCalculatorSymbolSeparator(),

			FrontCalculatorSymbolOpeningBracket: new FrontCalculatorSymbolOpeningBracket(),
			FrontCalculatorSymbolClosingBracket: new FrontCalculatorSymbolClosingBracket(),

			FrontCalculatorSymbolConstantPi: new FrontCalculatorSymbolConstantPi(),

			FrontCalculatorSymbolOperatorAddition: new FrontCalculatorSymbolOperatorAddition(),
			FrontCalculatorSymbolOperatorDivision: new FrontCalculatorSymbolOperatorDivision(),
			FrontCalculatorSymbolOperatorExponentiation: new FrontCalculatorSymbolOperatorExponentiation(),
			FrontCalculatorSymbolOperatorModulo: new FrontCalculatorSymbolOperatorModulo(),
			FrontCalculatorSymbolOperatorMultiplication: new FrontCalculatorSymbolOperatorMultiplication(),
			FrontCalculatorSymbolOperatorSubtraction: new FrontCalculatorSymbolOperatorSubtraction(),

			FrontCalculatorSymbolFunctionAbs: new FrontCalculatorSymbolFunctionAbs(),
			FrontCalculatorSymbolFunctionAvg: new FrontCalculatorSymbolFunctionAvg(),
			FrontCalculatorSymbolFunctionCeil: new FrontCalculatorSymbolFunctionCeil(),
			FrontCalculatorSymbolFunctionFloor: new FrontCalculatorSymbolFunctionFloor(),
			FrontCalculatorSymbolFunctionMax: new FrontCalculatorSymbolFunctionMax(),
			FrontCalculatorSymbolFunctionMin: new FrontCalculatorSymbolFunctionMin(),
			FrontCalculatorSymbolFunctionRound: new FrontCalculatorSymbolFunctionRound(),

		};
	}

	/**
	 * Returns the symbol that has the given identifier.
	 * Returns null if none is found.
	 *
	 * @param identifier
	 * @returns {FrontCalculatorSymbolAbstract|null}
	 */
	find(identifier) {
		identifier = identifier.toLowerCase();

		for (var key in this.symbols) {
			if (this.symbols.hasOwnProperty(key)) {
				var symbol = this.symbols[key];
				if (symbol.getIdentifiers().indexOf(identifier) >= 0) {
					return symbol;
				}
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
	 * @param parentTypeName
	 * @returns {FrontCalculatorSymbolAbstract[]}
	 */
	findSubTypes(parentTypeName) {
		var symbols = [];

		for (var key in this.symbols) {
			if (this.symbols.hasOwnProperty(key)) {
				var symbol = this.symbols[key];
				if (symbol instanceof parentTypeName) {
					symbols.push(symbol);
				}
			}

		}

		return symbols;
	}
}
