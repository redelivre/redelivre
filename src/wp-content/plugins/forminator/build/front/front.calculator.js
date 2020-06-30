(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorParser = _interopRequireDefault(require("./parser/front.calculator.parser.tokenizer"));

var _frontCalculatorSymbol = _interopRequireDefault(require("./symbol/front.calculator.symbol.loader"));

var _frontCalculator = _interopRequireDefault(require("./parser/front.calculator.parser"));

var _frontCalculatorSymbol2 = _interopRequireDefault(require("./symbol/front.calculator.symbol.number"));

var _frontCalculatorSymbolConstant = _interopRequireDefault(require("./symbol/abstract/front.calculator.symbol.constant.abstract"));

var _frontCalculatorParserNode = _interopRequireDefault(require("./parser/node/front.calculator.parser.node.symbol"));

var _frontCalculatorSymbolOperator = _interopRequireDefault(require("./symbol/abstract/front.calculator.symbol.operator.abstract"));

var _frontCalculatorSymbol3 = _interopRequireDefault(require("./symbol/front.calculator.symbol.separator"));

var _frontCalculatorParserNode2 = _interopRequireDefault(require("./parser/node/front.calculator.parser.node.function"));

var _frontCalculatorParserNode3 = _interopRequireDefault(require("./parser/node/front.calculator.parser.node.container"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var FrontCalculator =
/*#__PURE__*/
function () {
  /**
   *
   * @param {string} term
   */
  function FrontCalculator(term) {
    _classCallCheck(this, FrontCalculator);

    /**
     *
     * @type {string}
     */
    this.term = term;
    /**
     *
     * @type {FrontCalculatorParserTokenizer}
     */

    this.tokenizer = new _frontCalculatorParser.default(this.term);
    /**
     *
     * @type {FrontCalculatorSymbolLoader}
     */

    this.symbolLoader = new _frontCalculatorSymbol.default();
    /**
     *
     * @type {FrontCalculatorParser}
     */

    this.parser = new _frontCalculator.default(this.symbolLoader);
  }
  /**
   *
   * @returns {FrontCalculatorParserNodeContainer}
   */


  _createClass(FrontCalculator, [{
    key: "parse",
    value: function parse() {
      // reset
      this.tokenizer.input = this.term;
      this.tokenizer.reset();
      var tokens = this.tokenizer.tokenize();

      if (tokens.length === 0) {
        throw 'Error: Empty token of calculator term.';
      }

      var rootNode = this.parser.parse(tokens);

      if (rootNode.isEmpty()) {
        throw 'Error: Empty nodes of calculator tokens.';
      }

      return rootNode;
    }
    /**
     *
     * @returns {number}
     */

  }, {
    key: "calculate",
    value: function calculate() {
      var result = 0;
      var rootNode = this.parse();

      if (false === rootNode) {
        return result;
      }

      return this.calculateNode(rootNode);
    }
    /**
     *Calculates the numeric value / result of a node of
     * any known and calculable type. (For example symbol
     * nodes with a symbol of type separator are not
     * calculable.)
     *
     * @param {FrontCalculatorParserNodeAbstract} node
     *
     * @returns {number}
     */

  }, {
    key: "calculateNode",
    value: function calculateNode(node) {
      if (node instanceof _frontCalculatorParserNode.default) {
        return this.calculateSymbolNode(node);
      } else if (node instanceof _frontCalculatorParserNode2.default) {
        return this.calculateFunctionNode(node);
      } else if (node instanceof _frontCalculatorParserNode3.default) {
        return this.calculateContainerNode(node);
      } else {
        throw 'Error: Cannot calculate node of unknown type "' + node.constructor.name + '"';
      }
    }
    /**
     * This method actually calculates the results of every sub-terms
     * in the syntax tree (which consists of nodes).
     * It can call itself recursively.
     * Attention: $node must not be of type FunctionNode!
     *
     * @param {FrontCalculatorParserNodeContainer} containerNode
     *
     * @returns {number}
     */

  }, {
    key: "calculateContainerNode",
    value: function calculateContainerNode(containerNode) {
      if (containerNode instanceof _frontCalculatorParserNode2.default) {
        throw 'Error: Expected container node but got a function node';
      }

      var result = 0;
      var nodes = containerNode.childNodes;
      var orderedOperatorNodes = this.detectCalculationOrder(nodes); // Actually calculate the term. Iterates over the ordered operators and
      // calculates them, then replaces the parts of the operation by the result.

      for (var i = 0; i < orderedOperatorNodes.length; i++) {
        var operatorNode = orderedOperatorNodes[i].node;
        var index = orderedOperatorNodes[i].index;
        var leftOperand = null;
        var leftOperandIndex = null;
        var nodeIndex = 0;

        while (nodeIndex !== index) {
          if (nodes[nodeIndex] === undefined) {
            nodeIndex++;
            continue;
          }

          leftOperand = nodes[nodeIndex];
          leftOperandIndex = nodeIndex;
          nodeIndex++;
        }

        nodeIndex++;

        while (nodes[nodeIndex] === undefined) {
          nodeIndex++;
        }

        var rightOperand = nodes[nodeIndex];
        var rightOperandIndex = nodeIndex;
        var rightNumber = !isNaN(rightOperand) ? rightOperand : this.calculateNode(rightOperand);
        /**
         * @type {FrontCalculatorSymbolOperatorAbstract}
         */

        var symbol = operatorNode.symbol;

        if (operatorNode.isUnaryOperator) {
          result = symbol.operate(null, rightNumber); // Replace the participating symbols of the operation by the result

          delete nodes[rightOperandIndex]; // `delete` operation only set the value to empty, not `actually` remove it

          nodes[index] = result;
        } else {
          if (leftOperandIndex !== null && leftOperand !== null) {
            var leftNumber = !isNaN(leftOperand) ? leftOperand : this.calculateNode(leftOperand);
            result = symbol.operate(leftNumber, rightNumber); // Replace the participating symbols of the operation by the result

            delete nodes[leftOperandIndex];
            delete nodes[rightOperandIndex];
            nodes[index] = result;
          }
        }
      } //cleanup empty nodes


      nodes = nodes.filter(function (node) {
        return node !== undefined;
      });

      if (nodes.length === 0) {
        throw 'Error: Missing calculable subterm. Are there empty brackets?';
      }

      if (nodes.length > 1) {
        throw 'Error: Missing operators between parts of the term.';
      } // The only remaining element of the $nodes array contains the overall result


      result = nodes.pop(); // If the $nodes array did not contain any operator (but only one node) than
      // the result of this node has to be calculated now

      if (isNaN(result)) {
        return this.calculateNode(result);
      }

      return result;
    }
    /**
     * Returns the numeric value of a function node.
     * @param {FrontCalculatorParserNodeFunction} functionNode
     *
     * @returns {number}
     */

  }, {
    key: "calculateFunctionNode",
    value: function calculateFunctionNode(functionNode) {
      var nodes = functionNode.childNodes;
      var functionArguments = []; // ex : func(1+2,3,4) : 1+2 need to be calculated first

      var argumentChildNodes = [];
      var containerNode = null;

      for (var i = 0; i < nodes.length; i++) {
        var node = nodes[i];

        if (node instanceof _frontCalculatorParserNode.default) {
          if (node.symbol instanceof _frontCalculatorSymbol3.default) {
            containerNode = new _frontCalculatorParserNode3.default(argumentChildNodes);
            functionArguments.push(this.calculateNode(containerNode));
            argumentChildNodes = [];
          } else {
            argumentChildNodes.push(node);
          }
        } else {
          argumentChildNodes.push(node);
        }
      }

      if (argumentChildNodes.length > 0) {
        containerNode = new _frontCalculatorParserNode3.default(argumentChildNodes);
        functionArguments.push(this.calculateNode(containerNode));
      }
      /**
       *
       * @type {FrontCalculatorSymbolFunctionAbstract}
       */


      var symbol = functionNode.symbolNode.symbol;
      return symbol.execute(functionArguments);
    }
    /**
     * Returns the numeric value of a symbol node.
     * Attention: node.symbol must not be of type AbstractOperator!
     *
     * @param {FrontCalculatorParserNodeSymbol} symbolNode
     *
     * @returns {Number}
     */

  }, {
    key: "calculateSymbolNode",
    value: function calculateSymbolNode(symbolNode) {
      var symbol = symbolNode.symbol;
      var number = 0;

      if (symbol instanceof _frontCalculatorSymbol2.default) {
        number = symbolNode.token.value; // Convert string to int or float (depending on the type of the number)
        // If the number has a longer fractional part, it will be cut.

        number = Number(number);
      } else if (symbol instanceof _frontCalculatorSymbolConstant.default) {
        number = symbol.value;
      } else {
        throw 'Error: Found symbol of unexpected type "' + symbol.constructor.name + '", expected number or constant';
      }

      return number;
    }
    /**
     * Detect the calculation order of a given array of nodes.
     * Does only care for the precedence of operators.
     * Does not care for child nodes of container nodes.
     * Returns a new array with ordered symbol nodes
     *
     * @param {FrontCalculatorParserNodeAbstract[]} nodes
     *
     * @return {Array}
     */

  }, {
    key: "detectCalculationOrder",
    value: function detectCalculationOrder(nodes) {
      var operatorNodes = []; // Store all symbol nodes that have a symbol of type abstract operator in an array

      for (var i = 0; i < nodes.length; i++) {
        var node = nodes[i];

        if (node instanceof _frontCalculatorParserNode.default) {
          if (node.symbol instanceof _frontCalculatorSymbolOperator.default) {
            var operatorNode = {
              index: i,
              node: node
            };
            operatorNodes.push(operatorNode);
          }
        }
      }

      operatorNodes.sort(
      /**
       * Returning 1 means $nodeTwo before $nodeOne, returning -1 means $nodeOne before $nodeTwo.
       * @param {Object} operatorNodeOne
       * @param {Object} operatorNodeTwo
       */
      function (operatorNodeOne, operatorNodeTwo) {
        var nodeOne = operatorNodeOne.node;
        var nodeTwo = operatorNodeTwo.node; // First-level precedence of node one

        /**
         *
         * @type {FrontCalculatorSymbolOperatorAbstract}
         */

        var symbolOne = nodeOne.symbol;
        var precedenceOne = 2;

        if (nodeOne.isUnaryOperator) {
          precedenceOne = 3;
        } // First-level precedence of node two

        /**
         *
         * @type {FrontCalculatorSymbolOperatorAbstract}
         */


        var symbolTwo = nodeTwo.symbol;
        var precedenceTwo = 2;

        if (nodeTwo.isUnaryOperator) {
          precedenceTwo = 3;
        } // If the first-level precedence is the same, compare the second-level precedence


        if (precedenceOne === precedenceTwo) {
          precedenceOne = symbolOne.precedence;
          precedenceTwo = symbolTwo.precedence;
        } // If the second-level precedence is the same, we have to ensure that the sorting algorithm does
        // insert the node / token that is left in the term before the node / token that is right.
        // Therefore we cannot return 0 but compare the positions and return 1 / -1.


        if (precedenceOne === precedenceTwo) {
          return nodeOne.token.position < nodeTwo.token.position ? -1 : 1;
        }

        return precedenceOne < precedenceTwo ? 1 : -1;
      });
      return operatorNodes;
    }
  }]);

  return FrontCalculator;
}();

exports.default = FrontCalculator;

if (window['forminatorCalculator'] === undefined) {
  window.forminatorCalculator = function (term) {
    return new FrontCalculator(term);
  };
}

},{"./parser/front.calculator.parser":2,"./parser/front.calculator.parser.tokenizer":4,"./parser/node/front.calculator.parser.node.container":6,"./parser/node/front.calculator.parser.node.function":7,"./parser/node/front.calculator.parser.node.symbol":8,"./symbol/abstract/front.calculator.symbol.constant.abstract":10,"./symbol/abstract/front.calculator.symbol.operator.abstract":12,"./symbol/front.calculator.symbol.loader":16,"./symbol/front.calculator.symbol.number":17,"./symbol/front.calculator.symbol.separator":18}],2:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorParser = _interopRequireDefault(require("./front.calculator.parser.token"));

var _frontCalculatorSymbol = _interopRequireDefault(require("../symbol/front.calculator.symbol.number"));

var _frontCalculatorSymbolOpening = _interopRequireDefault(require("../symbol/brackets/front.calculator.symbol.opening.bracket"));

var _frontCalculatorSymbolClosing = _interopRequireDefault(require("../symbol/brackets/front.calculator.symbol.closing.bracket"));

var _frontCalculatorSymbolFunction = _interopRequireDefault(require("../symbol/abstract/front.calculator.symbol.function.abstract"));

var _frontCalculatorSymbolOperator = _interopRequireDefault(require("../symbol/abstract/front.calculator.symbol.operator.abstract"));

var _frontCalculatorSymbol2 = _interopRequireDefault(require("../symbol/front.calculator.symbol.separator"));

var _frontCalculatorParserNode = _interopRequireDefault(require("./node/front.calculator.parser.node.symbol"));

var _frontCalculatorParserNode2 = _interopRequireDefault(require("./node/front.calculator.parser.node.container"));

var _frontCalculatorParserNode3 = _interopRequireDefault(require("./node/front.calculator.parser.node.function"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

/**
 * The parsers has one important method: parse()
 * It takes an array of tokens as input and
 * returns an array of nodes as output.
 * These nodes are the syntax tree of the term.
 *
 */
var FrontCalculatorParser =
/*#__PURE__*/
function () {
  /**
   *
   * @param {FrontCalculatorSymbolLoader} symbolLoader
   */
  function FrontCalculatorParser(symbolLoader) {
    _classCallCheck(this, FrontCalculatorParser);

    /**
     *
     * @type {FrontCalculatorSymbolLoader}
     */
    this.symbolLoader = symbolLoader;
  }
  /**
   * Parses an array with tokens. Returns an array of nodes.
   * These nodes define a syntax tree.
   *
   * @param {FrontCalculatorParserToken[]} tokens
   *
   * @returns FrontCalculatorParserNodeContainer
   */


  _createClass(FrontCalculatorParser, [{
    key: "parse",
    value: function parse(tokens) {
      var symbolNodes = this.detectSymbols(tokens);
      var nodes = this.createTreeByBrackets(symbolNodes);
      nodes = this.transformTreeByFunctions(nodes);
      this.checkGrammar(nodes); // Wrap the nodes in an array node.

      return new _frontCalculatorParserNode2.default(nodes);
    }
    /**
     * Creates a flat array of symbol nodes from tokens.
     *
     * @param {FrontCalculatorParserToken[]} tokens
     * @returns {FrontCalculatorParserNodeSymbol[]}
     */

  }, {
    key: "detectSymbols",
    value: function detectSymbols(tokens) {
      var symbolNodes = [];
      var symbol = null;
      var identifier = null;
      var expectingOpeningBracket = false; // True if we expect an opening bracket (after a function name)

      var openBracketCounter = 0;

      for (var i = 0; i < tokens.length; i++) {
        var token = tokens[i];
        var type = token.type;

        if (_frontCalculatorParser.default.TYPE_WORD === type) {
          identifier = token.value;
          symbol = this.symbolLoader.find(identifier);

          if (null === symbol) {
            throw 'Error: Detected unknown or invalid string identifier: ' + identifier + '.';
          }
        } else if (type === _frontCalculatorParser.default.TYPE_NUMBER) {
          // Notice: Numbers do not have an identifier
          var symbolNumbers = this.symbolLoader.findSubTypes(_frontCalculatorSymbol.default);

          if (symbolNumbers.length < 1 || !(symbolNumbers instanceof Array)) {
            throw 'Error: Unavailable number symbol processor.';
          }

          symbol = symbolNumbers[0];
        } else {
          // Type Token::TYPE_CHARACTER:
          identifier = token.value;
          symbol = this.symbolLoader.find(identifier);

          if (null === symbol) {
            throw 'Error: Detected unknown or invalid string identifier: ' + identifier + '.';
          }

          if (symbol instanceof _frontCalculatorSymbolOpening.default) {
            openBracketCounter++;
          }

          if (symbol instanceof _frontCalculatorSymbolClosing.default) {
            openBracketCounter--; // Make sure there are not too many closing brackets

            if (openBracketCounter < 0) {
              throw 'Error: Found closing bracket that does not have an opening bracket.';
            }
          }
        }

        if (expectingOpeningBracket) {
          if (!(symbol instanceof _frontCalculatorSymbolOpening.default)) {
            throw 'Error: Expected opening bracket (after a function) but got something else.';
          }

          expectingOpeningBracket = false;
        } else {
          if (symbol instanceof _frontCalculatorSymbolFunction.default) {
            expectingOpeningBracket = true;
          }
        }

        var symbolNode = new _frontCalculatorParserNode.default(token, symbol);
        symbolNodes.push(symbolNode);
      } // Make sure the term does not end with the name of a function but without an opening bracket


      if (expectingOpeningBracket) {
        throw 'Error: Expected opening bracket (after a function) but reached the end of the term';
      } // Make sure there are not too many opening brackets


      if (openBracketCounter > 0) {
        throw 'Error: There is at least one opening bracket that does not have a closing bracket';
      }

      return symbolNodes;
    }
    /**
     * Expects a flat array of symbol nodes and (if possible) transforms
     * it to a tree of nodes. Cares for brackets.
     * Attention: Expects valid brackets!
     * Check the brackets before you call this method.
     *
     * @param {FrontCalculatorParserNodeSymbol[]} symbolNodes
     * @returns {FrontCalculatorParserNodeAbstract[]}
     */

  }, {
    key: "createTreeByBrackets",
    value: function createTreeByBrackets(symbolNodes) {
      var tree = [];
      var nodesInBracket = []; // AbstractSymbol nodes inside level-0-brackets

      var openBracketCounter = 0;

      for (var i = 0; i < symbolNodes.length; i++) {
        var symbolNode = symbolNodes[i];

        if (!(symbolNode instanceof _frontCalculatorParserNode.default)) {
          throw 'Error: Expected symbol node, but got "' + symbolNode.constructor.name + '"';
        }

        if (symbolNode.symbol instanceof _frontCalculatorSymbolOpening.default) {
          openBracketCounter++;

          if (openBracketCounter > 1) {
            nodesInBracket.push(symbolNode);
          }
        } else if (symbolNode.symbol instanceof _frontCalculatorSymbolClosing.default) {
          openBracketCounter--; // Found a closing bracket on level 0

          if (0 === openBracketCounter) {
            var subTree = this.createTreeByBrackets(nodesInBracket); // Subtree can be empty for example if the term looks like this: "()" or "functioname()"
            // But this is okay, we need to allow this so we can call functions without a parameter

            tree.push(new _frontCalculatorParserNode2.default(subTree));
            nodesInBracket = [];
          } else {
            nodesInBracket.push(symbolNode);
          }
        } else {
          if (0 === openBracketCounter) {
            tree.push(symbolNode);
          } else {
            nodesInBracket.push(symbolNode);
          }
        }
      }

      return tree;
    }
    /**
     * Replaces [a SymbolNode that has a symbol of type AbstractFunction,
     * followed by a node of type ContainerNode] by a FunctionNode.
     * Expects the $nodes not including any function nodes (yet).
     *
     * @param {FrontCalculatorParserNodeAbstract[]} nodes
     *
     * @returns {FrontCalculatorParserNodeAbstract[]}
     */

  }, {
    key: "transformTreeByFunctions",
    value: function transformTreeByFunctions(nodes) {
      var transformedNodes = [];
      var functionSymbolNode = null;

      for (var i = 0; i < nodes.length; i++) {
        var node = nodes[i];

        if (node instanceof _frontCalculatorParserNode2.default) {
          var transformedChildNodes = this.transformTreeByFunctions(node.childNodes);

          if (null !== functionSymbolNode) {
            var functionNode = new _frontCalculatorParserNode3.default(transformedChildNodes, functionSymbolNode);
            transformedNodes.push(functionNode);
            functionSymbolNode = null;
          } else {
            // not a function
            node.childNodes = transformedChildNodes;
            transformedNodes.push(node);
          }
        } else if (node instanceof _frontCalculatorParserNode.default) {
          var symbol = node.symbol;

          if (symbol instanceof _frontCalculatorSymbolFunction.default) {
            functionSymbolNode = node;
          } else {
            transformedNodes.push(node);
          }
        } else {
          throw 'Error: Expected array node or symbol node, got "' + node.constructor.name + '"';
        }
      }

      return transformedNodes;
    }
    /**
     * Ensures the tree follows the grammar rules for terms
     *
     * @param {FrontCalculatorParserNodeAbstract[]} nodes
     */

  }, {
    key: "checkGrammar",
    value: function checkGrammar(nodes) {
      // TODO Make sure that separators are only in the child nodes of the array node of a function node
      // (If this happens the calculator will throw an exception)
      for (var i = 0; i < nodes.length; i++) {
        var node = nodes[i];

        if (node instanceof _frontCalculatorParserNode.default) {
          var symbol = node.symbol;

          if (symbol instanceof _frontCalculatorSymbolOperator.default) {
            var posOfRightOperand = i + 1; // Make sure the operator is positioned left of a (potential) operand (=prefix notation).
            // Example term: "-1"

            if (posOfRightOperand >= nodes.length) {
              throw 'Error: Found operator that does not stand before an operand.';
            }

            var posOfLeftOperand = i - 1;
            var leftOperand = null; // Operator is unary if positioned at the beginning of a term

            if (posOfLeftOperand >= 0) {
              leftOperand = nodes[posOfLeftOperand];

              if (leftOperand instanceof _frontCalculatorParserNode.default) {
                if (leftOperand.symbol instanceof _frontCalculatorSymbolOperator.default // example 1`+-`5 : + = operator, - = unary
                || leftOperand.symbol instanceof _frontCalculatorSymbol2.default // example func(1`,-`5) ,= separator, - = unary
                ) {
                    // Operator is unary if positioned right to another operator
                    leftOperand = null;
                  }
              }
            } // If null, the operator is unary


            if (null === leftOperand) {
              if (!symbol.operatesUnary) {
                throw 'Error: Found operator in unary notation that is not unary.';
              } // Remember that this node represents a unary operator


              node.setIsUnaryOperator(true);
            } else {
              if (!symbol.operatesBinary) {
                console.log(symbol);
                throw 'Error: Found operator in binary notation that is not binary.';
              }
            }
          }
        } else {
          this.checkGrammar(node.childNodes);
        }
      }
    }
  }]);

  return FrontCalculatorParser;
}();

exports.default = FrontCalculatorParser;

},{"../symbol/abstract/front.calculator.symbol.function.abstract":11,"../symbol/abstract/front.calculator.symbol.operator.abstract":12,"../symbol/brackets/front.calculator.symbol.closing.bracket":13,"../symbol/brackets/front.calculator.symbol.opening.bracket":14,"../symbol/front.calculator.symbol.number":17,"../symbol/front.calculator.symbol.separator":18,"./front.calculator.parser.token":3,"./node/front.calculator.parser.node.container":6,"./node/front.calculator.parser.node.function":7,"./node/front.calculator.parser.node.symbol":8}],3:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var FrontCalculatorParserToken =
/*#__PURE__*/
function () {
  _createClass(FrontCalculatorParserToken, null, [{
    key: "TYPE_WORD",
    get: function get() {
      return 1;
    }
  }, {
    key: "TYPE_CHAR",
    get: function get() {
      return 2;
    }
  }, {
    key: "TYPE_NUMBER",
    get: function get() {
      return 3;
    }
  }]);

  function FrontCalculatorParserToken(type, value, position) {
    _classCallCheck(this, FrontCalculatorParserToken);

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

  return FrontCalculatorParserToken;
}();

exports.default = FrontCalculatorParserToken;

},{}],4:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorParser = _interopRequireDefault(require("./front.calculator.parser.token"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var FrontCalculatorParserTokenizer =
/*#__PURE__*/
function () {
  function FrontCalculatorParserTokenizer(input) {
    _classCallCheck(this, FrontCalculatorParserTokenizer);

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


  _createClass(FrontCalculatorParserTokenizer, [{
    key: "tokenize",
    value: function tokenize() {
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

  }, {
    key: "readToken",
    value: function readToken() {
      this.stepOverWhitespace();
      var char = this.readCurrent();

      if (null === char) {
        return null;
      }

      var value = null;
      var type = null;

      if (this.isLetter(char)) {
        value = this.readWord();
        type = _frontCalculatorParser.default.TYPE_WORD;
      } else if (this.isDigit(char) || this.isPeriod(char)) {
        value = this.readNumber();
        type = _frontCalculatorParser.default.TYPE_NUMBER;
      } else {
        value = this.readChar();
        type = _frontCalculatorParser.default.TYPE_CHAR;
      }

      return new _frontCalculatorParser.default(type, value, this.currentPosition);
    }
    /**
     * Returns true, if a given character is a letter (a-z and A-Z).
     *
     * @param char
     * @returns {boolean}
     */

  }, {
    key: "isLetter",
    value: function isLetter(char) {
      if (null === char) {
        return false;
      }

      var ascii = char.charCodeAt(0);
      /**
       * ASCII codes: 65 = 'A', 90 = 'Z', 97 = 'a', 122 = 'z'--
       **/

      return ascii >= 65 && ascii <= 90 || ascii >= 97 && ascii <= 122;
    }
    /**
     * Returns true, if a given character is a digit (0-9).
     *
     * @param char
     * @returns {boolean}
     */

  }, {
    key: "isDigit",
    value: function isDigit(char) {
      if (null === char) {
        return false;
      }

      var ascii = char.charCodeAt(0);
      /**
       * ASCII codes: 48 = '0', 57 = '9'
       */

      return ascii >= 48 && ascii <= 57;
    }
    /**
     * Returns true, if a given character is a period ('.').
     *
     * @param char
     * @returns {boolean}
     */

  }, {
    key: "isPeriod",
    value: function isPeriod(char) {
      return '.' === char;
    }
    /**
     * Returns true, if a given character is whitespace.
     * Notice: A null char is not seen as whitespace.
     *
     * @param char
     * @returns {boolean}
     */

  }, {
    key: "isWhitespace",
    value: function isWhitespace(char) {
      return [" ", "\t", "\n"].indexOf(char) >= 0;
    }
  }, {
    key: "stepOverWhitespace",
    value: function stepOverWhitespace() {
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

  }, {
    key: "readWord",
    value: function readWord() {
      var word = '';
      var char = this.readCurrent(); // Try to read the word

      while (null !== char) {
        if (this.isLetter(char)) {
          word += char;
        } else {
          break;
        } // Just move the cursor to the next position


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

  }, {
    key: "readNumber",
    value: function readNumber() {
      var number = '';
      var foundPeriod = false; // Try to read the number.
      // Notice: It does not matter if the number only consists of a single period
      // or if it ends with a period.

      var char = this.readCurrent();

      while (null !== char) {
        if (this.isPeriod(char) || this.isDigit(char)) {
          if (this.isPeriod(char)) {
            if (foundPeriod) {
              throw 'Error: A number cannot have more than one period';
            }

            foundPeriod = true;
          }

          number += char;
        } else {
          break;
        } // read next


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

  }, {
    key: "readChar",
    value: function readChar() {
      var char = this.readCurrent(); // Just move the cursor to the next position

      this.readNext();
      return char;
    }
    /**
     *
     * @returns {String|null}
     */

  }, {
    key: "readCurrent",
    value: function readCurrent() {
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

  }, {
    key: "readNext",
    value: function readNext() {
      this.currentPosition++;
      return this.readCurrent();
    }
    /**
     * Returns true if there is a character at the current position
     *
     * @returns {boolean}
     */

  }, {
    key: "hasCurrent",
    value: function hasCurrent() {
      return this.currentPosition < this.input.length;
    }
  }, {
    key: "reset",
    value: function reset() {
      this.currentPosition = 0;
    }
  }]);

  return FrontCalculatorParserTokenizer;
}();

exports.default = FrontCalculatorParserTokenizer;

},{"./front.calculator.parser.token":3}],5:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var FrontCalculatorParserNodeAbstract = function FrontCalculatorParserNodeAbstract() {
  _classCallCheck(this, FrontCalculatorParserNodeAbstract);
};

exports.default = FrontCalculatorParserNodeAbstract;

},{}],6:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorParserNode = _interopRequireDefault(require("./front.calculator.parser.node.abstract"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

/**
 * A parent node is a container for a (sorted) array of nodes.
 *
 */
var FrontCalculatorParserNodeContainer =
/*#__PURE__*/
function (_FrontCalculatorParse) {
  _inherits(FrontCalculatorParserNodeContainer, _FrontCalculatorParse);

  function FrontCalculatorParserNodeContainer(childNodes) {
    var _this;

    _classCallCheck(this, FrontCalculatorParserNodeContainer);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(FrontCalculatorParserNodeContainer).call(this));
    /**
     *
     * @type {FrontCalculatorParserNodeAbstract[]}
     */

    _this.childNodes = null;

    _this.setChildNodes(childNodes);

    return _this;
  }
  /**
   * Setter for the child nodes.
   * Notice: The number of child nodes can be 0.
   * @param childNodes
   */


  _createClass(FrontCalculatorParserNodeContainer, [{
    key: "setChildNodes",
    value: function setChildNodes(childNodes) {
      childNodes.forEach(function (childNode) {
        if (!(childNode instanceof _frontCalculatorParserNode.default)) {
          throw 'Expected AbstractNode, but got ' + childNode.constructor.name;
        }
      });
      this.childNodes = childNodes;
    }
    /**
     * Returns the number of child nodes in this array node.
     * Does not count the child nodes of the child nodes.
     *
     * @returns {number}
     */

  }, {
    key: "size",
    value: function size() {
      try {
        return this.childNodes.length;
      } catch (e) {
        return 0;
      }
    }
    /**
     * Returns true if the array node does not have any
     * child nodes. This might sound strange but is possible.
     *
     * @returns {boolean}
     */

  }, {
    key: "isEmpty",
    value: function isEmpty() {
      return !this.size();
    }
  }]);

  return FrontCalculatorParserNodeContainer;
}(_frontCalculatorParserNode.default);

exports.default = FrontCalculatorParserNodeContainer;

},{"./front.calculator.parser.node.abstract":5}],7:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorParserNode = _interopRequireDefault(require("./front.calculator.parser.node.container"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

/**
 * A function in a term consists of the name of the function
 * (the symbol of the function) and the brackets that follow
 * the name and everything that is in this brackets (the
 * arguments). A function node combines these two things.
 * It stores its symbol in the $symbolNode property and its
 * arguments in the $childNodes property which is inherited
 * from the ContainerNode class.
 *
 */
var FrontCalculatorParserNodeFunction =
/*#__PURE__*/
function (_FrontCalculatorParse) {
  _inherits(FrontCalculatorParserNodeFunction, _FrontCalculatorParse);

  /**
   * ContainerNode constructor.
   * Attention: The constructor is differs from the constructor
   * of the parent class!
   *
   * @param childNodes
   * @param symbolNode
   */
  function FrontCalculatorParserNodeFunction(childNodes, symbolNode) {
    var _this;

    _classCallCheck(this, FrontCalculatorParserNodeFunction);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(FrontCalculatorParserNodeFunction).call(this, childNodes));
    /**
     *
     * @type {FrontCalculatorParserNodeSymbol}
     */

    _this.symbolNode = symbolNode;
    return _this;
  }

  return FrontCalculatorParserNodeFunction;
}(_frontCalculatorParserNode.default);

exports.default = FrontCalculatorParserNodeFunction;

},{"./front.calculator.parser.node.container":6}],8:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorSymbolOperator = _interopRequireDefault(require("../../symbol/abstract/front.calculator.symbol.operator.abstract"));

var _frontCalculatorParserNode = _interopRequireDefault(require("./front.calculator.parser.node.abstract"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

/**
 * A symbol node is a node in the syntax tree.
 * Leaf nodes do not have any child nodes
 * (parent nodes can have child nodes). A
 * symbol node represents a mathematical symbol.
 * Nodes are created by the parser.
 *
 */
var FrontCalculatorParserNodeSymbol =
/*#__PURE__*/
function (_FrontCalculatorParse) {
  _inherits(FrontCalculatorParserNodeSymbol, _FrontCalculatorParse);

  function FrontCalculatorParserNodeSymbol(token, symbol) {
    var _this;

    _classCallCheck(this, FrontCalculatorParserNodeSymbol);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(FrontCalculatorParserNodeSymbol).call(this));
    /**
     * The token of the node. It contains the value.
     *
     * @type {FrontCalculatorParserToken}
     */

    _this.token = token;
    /**
     * The symbol of the node. It defines the type of the node.
     *
     * @type {FrontCalculatorSymbolAbstract}
     */

    _this.symbol = symbol;
    /**
     * Unary operators need to be treated specially.
     * Therefore a node has to know if it (or to be
     * more precise the symbol of the node)
     * represents a unary operator.
     * Example : -1, -4
     *
     * @type {boolean}
     */

    _this.isUnaryOperator = false;
    return _this;
  }

  _createClass(FrontCalculatorParserNodeSymbol, [{
    key: "setIsUnaryOperator",
    value: function setIsUnaryOperator(isUnaryOperator) {
      if (!(this.symbol instanceof _frontCalculatorSymbolOperator.default)) {
        throw 'Error: Cannot mark node as unary operator, because symbol is not an operator but of type ' + this.symbol.constructor.name;
      }

      this.isUnaryOperator = isUnaryOperator;
    }
  }]);

  return FrontCalculatorParserNodeSymbol;
}(_frontCalculatorParserNode.default);

exports.default = FrontCalculatorParserNodeSymbol;

},{"../../symbol/abstract/front.calculator.symbol.operator.abstract":12,"./front.calculator.parser.node.abstract":5}],9:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var FrontCalculatorSymbolAbstract =
/*#__PURE__*/
function () {
  function FrontCalculatorSymbolAbstract() {
    _classCallCheck(this, FrontCalculatorSymbolAbstract);

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


  _createClass(FrontCalculatorSymbolAbstract, [{
    key: "getIdentifiers",
    value: function getIdentifiers() {
      var lowerIdentifiers = [];
      this.identifiers.forEach(function (identifier) {
        lowerIdentifiers.push(identifier.toLowerCase());
      });
      return lowerIdentifiers;
    }
  }]);

  return FrontCalculatorSymbolAbstract;
}();

exports.default = FrontCalculatorSymbolAbstract;

},{}],10:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorSymbol = _interopRequireDefault(require("./front.calculator.symbol.abstract"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

/**
 * This class is the base class for all symbols that are of the type "constant".
 * We recommend to use names as textual representations for this type of symbol.
 * Please take note of the fact that the precision of PHP float constants
 * (for example M_PI) is based on the "precision" directive in php.ini,
 * which defaults to 14.
 */
var FrontCalculatorSymbolConstantAbstract =
/*#__PURE__*/
function (_FrontCalculatorSymbo) {
  _inherits(FrontCalculatorSymbolConstantAbstract, _FrontCalculatorSymbo);

  function FrontCalculatorSymbolConstantAbstract() {
    var _this;

    _classCallCheck(this, FrontCalculatorSymbolConstantAbstract);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(FrontCalculatorSymbolConstantAbstract).call(this));
    /**
     * This is the value of the constant. We use 0 as an example here,
     * but you are supposed to overwrite this in the concrete constant class.
     * Usually mathematical constants are not integers, however,
     * you are allowed to use an integer in this context.
     *
     * @type {number}
     */

    _this.value = 0;
    return _this;
  }

  return FrontCalculatorSymbolConstantAbstract;
}(_frontCalculatorSymbol.default);

exports.default = FrontCalculatorSymbolConstantAbstract;

},{"./front.calculator.symbol.abstract":9}],11:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorSymbol = _interopRequireDefault(require("./front.calculator.symbol.abstract"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

/**
 * This class is the base class for all symbols that are of the type "function".
 * Typically the textual representation of a function consists of two or more letters.
 */
var FrontCalculatorSymbolFunctionAbstract =
/*#__PURE__*/
function (_FrontCalculatorSymbo) {
  _inherits(FrontCalculatorSymbolFunctionAbstract, _FrontCalculatorSymbo);

  function FrontCalculatorSymbolFunctionAbstract() {
    _classCallCheck(this, FrontCalculatorSymbolFunctionAbstract);

    return _possibleConstructorReturn(this, _getPrototypeOf(FrontCalculatorSymbolFunctionAbstract).call(this));
  }
  /**
   * This method is called when the function is executed. A function can have 0-n parameters.
   * The implementation of this method is responsible to validate the number of arguments.
   * The $arguments array contains these arguments. If the number of arguments is improper,
   * the method has to throw a Exceptions\NumberOfArgumentsException exception.
   * The items of the $arguments array will always be of type int or float. They will never be null.
   * They keys will be integers starting at 0 and representing the positions of the arguments
   * in ascending order.
   * Overwrite this method in the concrete operator class.
   * If this class does NOT return a value of type int or float,
   * an exception will be thrown.
   *
   * @param {int[]|float[]} params
   * @returns {number}
   */


  _createClass(FrontCalculatorSymbolFunctionAbstract, [{
    key: "execute",
    value: function execute(params) {
      return 0.0;
    }
  }]);

  return FrontCalculatorSymbolFunctionAbstract;
}(_frontCalculatorSymbol.default);

exports.default = FrontCalculatorSymbolFunctionAbstract;

},{"./front.calculator.symbol.abstract":9}],12:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorSymbol = _interopRequireDefault(require("./front.calculator.symbol.abstract"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

/**
 * This class is the base class for all symbols that are of the type "(binary) operator".
 * The textual representation of an operator consists of a single char that is not a letter.
 * It is worth noting that a operator has the same power as a function with two parameters.
 * Operators are always binary. To mimic a unary operator you might want to create a function
 * that accepts one parameter.
 */
var FrontCalculatorSymbolOperatorAbstract =
/*#__PURE__*/
function (_FrontCalculatorSymbo) {
  _inherits(FrontCalculatorSymbolOperatorAbstract, _FrontCalculatorSymbo);

  function FrontCalculatorSymbolOperatorAbstract() {
    var _this;

    _classCallCheck(this, FrontCalculatorSymbolOperatorAbstract);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(FrontCalculatorSymbolOperatorAbstract).call(this));
    /**
     * The operator precedence determines which operators to perform first
     * in order to evaluate a given term.
     * You are supposed to overwrite this constant in the concrete constant class.
     * Take a look at other operator classes to see the precedences of the predefined operators.
     * 0: default, > 0: higher, < 0: lower
     *
     * @type {number}
     */

    _this.precedence = 0;
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

    _this.operatesUnary = false;
    /**
     * Usually operators are binary, they operate on two operands (numbers).
     * Notice: Operators can be unary AND binary (but this is a rare case)
     *
     * @type {boolean}
     */

    _this.operatesBinary = true;
    return _this;
  }

  _createClass(FrontCalculatorSymbolOperatorAbstract, [{
    key: "operate",
    value: function operate(leftNumber, rightNumber) {
      return 0.0;
    }
  }]);

  return FrontCalculatorSymbolOperatorAbstract;
}(_frontCalculatorSymbol.default);

exports.default = FrontCalculatorSymbolOperatorAbstract;

},{"./front.calculator.symbol.abstract":9}],13:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorSymbol = _interopRequireDefault(require("../abstract/front.calculator.symbol.abstract"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

var FrontCalculatorSymbolClosingBracket =
/*#__PURE__*/
function (_FrontCalculatorSymbo) {
  _inherits(FrontCalculatorSymbolClosingBracket, _FrontCalculatorSymbo);

  function FrontCalculatorSymbolClosingBracket() {
    var _this;

    _classCallCheck(this, FrontCalculatorSymbolClosingBracket);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(FrontCalculatorSymbolClosingBracket).call(this));
    _this.identifiers = [')'];
    return _this;
  }

  return FrontCalculatorSymbolClosingBracket;
}(_frontCalculatorSymbol.default);

exports.default = FrontCalculatorSymbolClosingBracket;

},{"../abstract/front.calculator.symbol.abstract":9}],14:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorSymbol = _interopRequireDefault(require("../abstract/front.calculator.symbol.abstract"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

var FrontCalculatorSymbolOpeningBracket =
/*#__PURE__*/
function (_FrontCalculatorSymbo) {
  _inherits(FrontCalculatorSymbolOpeningBracket, _FrontCalculatorSymbo);

  function FrontCalculatorSymbolOpeningBracket() {
    var _this;

    _classCallCheck(this, FrontCalculatorSymbolOpeningBracket);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(FrontCalculatorSymbolOpeningBracket).call(this));
    _this.identifiers = ['('];
    return _this;
  }

  return FrontCalculatorSymbolOpeningBracket;
}(_frontCalculatorSymbol.default);

exports.default = FrontCalculatorSymbolOpeningBracket;

},{"../abstract/front.calculator.symbol.abstract":9}],15:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorSymbolConstant = _interopRequireDefault(require("../abstract/front.calculator.symbol.constant.abstract"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

/**
 * Math.PI
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/PI
 */
var FrontCalculatorSymbolConstantPi =
/*#__PURE__*/
function (_FrontCalculatorSymbo) {
  _inherits(FrontCalculatorSymbolConstantPi, _FrontCalculatorSymbo);

  function FrontCalculatorSymbolConstantPi() {
    var _this;

    _classCallCheck(this, FrontCalculatorSymbolConstantPi);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(FrontCalculatorSymbolConstantPi).call(this));
    _this.identifiers = ['pi'];
    _this.value = Math.PI;
    return _this;
  }

  return FrontCalculatorSymbolConstantPi;
}(_frontCalculatorSymbolConstant.default);

exports.default = FrontCalculatorSymbolConstantPi;

},{"../abstract/front.calculator.symbol.constant.abstract":10}],16:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorSymbol = _interopRequireDefault(require("./front.calculator.symbol.number"));

var _frontCalculatorSymbol2 = _interopRequireDefault(require("./front.calculator.symbol.separator"));

var _frontCalculatorSymbolOpening = _interopRequireDefault(require("./brackets/front.calculator.symbol.opening.bracket"));

var _frontCalculatorSymbolClosing = _interopRequireDefault(require("./brackets/front.calculator.symbol.closing.bracket"));

var _frontCalculatorSymbolConstant = _interopRequireDefault(require("./constants/front.calculator.symbol.constant.pi"));

var _frontCalculatorSymbolOperator = _interopRequireDefault(require("./operators/front.calculator.symbol.operator.addition"));

var _frontCalculatorSymbolOperator2 = _interopRequireDefault(require("./operators/front.calculator.symbol.operator.division"));

var _frontCalculatorSymbolOperator3 = _interopRequireDefault(require("./operators/front.calculator.symbol.operator.exponentiation"));

var _frontCalculatorSymbolOperator4 = _interopRequireDefault(require("./operators/front.calculator.symbol.operator.modulo"));

var _frontCalculatorSymbolOperator5 = _interopRequireDefault(require("./operators/front.calculator.symbol.operator.multiplication"));

var _frontCalculatorSymbolOperator6 = _interopRequireDefault(require("./operators/front.calculator.symbol.operator.subtraction"));

var _frontCalculatorSymbolFunction = _interopRequireDefault(require("./functions/front.calculator.symbol.function.abs"));

var _frontCalculatorSymbolFunction2 = _interopRequireDefault(require("./functions/front.calculator.symbol.function.avg"));

var _frontCalculatorSymbolFunction3 = _interopRequireDefault(require("./functions/front.calculator.symbol.function.ceil"));

var _frontCalculatorSymbolFunction4 = _interopRequireDefault(require("./functions/front.calculator.symbol.function.floor"));

var _frontCalculatorSymbolFunction5 = _interopRequireDefault(require("./functions/front.calculator.symbol.function.max"));

var _frontCalculatorSymbolFunction6 = _interopRequireDefault(require("./functions/front.calculator.symbol.function.min"));

var _frontCalculatorSymbolFunction7 = _interopRequireDefault(require("./functions/front.calculator.symbol.function.round"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var FrontCalculatorSymbolLoader =
/*#__PURE__*/
function () {
  function FrontCalculatorSymbolLoader() {
    _classCallCheck(this, FrontCalculatorSymbolLoader);

    /**
     *
     * @type {{FrontCalculatorSymbolOperatorModulo: FrontCalculatorSymbolOperatorModulo, FrontCalculatorSymbolOperatorSubtraction: FrontCalculatorSymbolOperatorSubtraction, FrontCalculatorSymbolOperatorExponentiation: FrontCalculatorSymbolOperatorExponentiation, FrontCalculatorSymbolOperatorAddition: FrontCalculatorSymbolOperatorAddition, FrontCalculatorSymbolClosingBracket: FrontCalculatorSymbolClosingBracket, FrontCalculatorSymbolFunctionMax: FrontCalculatorSymbolFunctionMax, FrontCalculatorSymbolFunctionCeil: FrontCalculatorSymbolFunctionCeil, FrontCalculatorSymbolSeparator: FrontCalculatorSymbolSeparator, FrontCalculatorSymbolOperatorMultiplication: FrontCalculatorSymbolOperatorMultiplication, FrontCalculatorSymbolFunctionAbs: FrontCalculatorSymbolFunctionAbs, FrontCalculatorSymbolFunctionAvg: FrontCalculatorSymbolFunctionAvg, FrontCalculatorSymbolFunctionFloor: FrontCalculatorSymbolFunctionFloor, FrontCalculatorSymbolFunctionMin: FrontCalculatorSymbolFunctionMin, FrontCalculatorSymbolOperatorDivision: FrontCalculatorSymbolOperatorDivision, FrontCalculatorSymbolNumber: FrontCalculatorSymbolNumber, FrontCalculatorSymbolOpeningBracket: FrontCalculatorSymbolOpeningBracket, FrontCalculatorSymbolConstantPi: FrontCalculatorSymbolConstantPi, FrontCalculatorSymbolFunctionRound: FrontCalculatorSymbolFunctionRound}}
     */
    this.symbols = {
      FrontCalculatorSymbolNumber: new _frontCalculatorSymbol.default(),
      FrontCalculatorSymbolSeparator: new _frontCalculatorSymbol2.default(),
      FrontCalculatorSymbolOpeningBracket: new _frontCalculatorSymbolOpening.default(),
      FrontCalculatorSymbolClosingBracket: new _frontCalculatorSymbolClosing.default(),
      FrontCalculatorSymbolConstantPi: new _frontCalculatorSymbolConstant.default(),
      FrontCalculatorSymbolOperatorAddition: new _frontCalculatorSymbolOperator.default(),
      FrontCalculatorSymbolOperatorDivision: new _frontCalculatorSymbolOperator2.default(),
      FrontCalculatorSymbolOperatorExponentiation: new _frontCalculatorSymbolOperator3.default(),
      FrontCalculatorSymbolOperatorModulo: new _frontCalculatorSymbolOperator4.default(),
      FrontCalculatorSymbolOperatorMultiplication: new _frontCalculatorSymbolOperator5.default(),
      FrontCalculatorSymbolOperatorSubtraction: new _frontCalculatorSymbolOperator6.default(),
      FrontCalculatorSymbolFunctionAbs: new _frontCalculatorSymbolFunction.default(),
      FrontCalculatorSymbolFunctionAvg: new _frontCalculatorSymbolFunction2.default(),
      FrontCalculatorSymbolFunctionCeil: new _frontCalculatorSymbolFunction3.default(),
      FrontCalculatorSymbolFunctionFloor: new _frontCalculatorSymbolFunction4.default(),
      FrontCalculatorSymbolFunctionMax: new _frontCalculatorSymbolFunction5.default(),
      FrontCalculatorSymbolFunctionMin: new _frontCalculatorSymbolFunction6.default(),
      FrontCalculatorSymbolFunctionRound: new _frontCalculatorSymbolFunction7.default()
    };
  }
  /**
   * Returns the symbol that has the given identifier.
   * Returns null if none is found.
   *
   * @param identifier
   * @returns {FrontCalculatorSymbolAbstract|null}
   */


  _createClass(FrontCalculatorSymbolLoader, [{
    key: "find",
    value: function find(identifier) {
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

  }, {
    key: "findSubTypes",
    value: function findSubTypes(parentTypeName) {
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
  }]);

  return FrontCalculatorSymbolLoader;
}();

exports.default = FrontCalculatorSymbolLoader;

},{"./brackets/front.calculator.symbol.closing.bracket":13,"./brackets/front.calculator.symbol.opening.bracket":14,"./constants/front.calculator.symbol.constant.pi":15,"./front.calculator.symbol.number":17,"./front.calculator.symbol.separator":18,"./functions/front.calculator.symbol.function.abs":19,"./functions/front.calculator.symbol.function.avg":20,"./functions/front.calculator.symbol.function.ceil":21,"./functions/front.calculator.symbol.function.floor":22,"./functions/front.calculator.symbol.function.max":23,"./functions/front.calculator.symbol.function.min":24,"./functions/front.calculator.symbol.function.round":25,"./operators/front.calculator.symbol.operator.addition":26,"./operators/front.calculator.symbol.operator.division":27,"./operators/front.calculator.symbol.operator.exponentiation":28,"./operators/front.calculator.symbol.operator.modulo":29,"./operators/front.calculator.symbol.operator.multiplication":30,"./operators/front.calculator.symbol.operator.subtraction":31}],17:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorSymbol = _interopRequireDefault(require("./abstract/front.calculator.symbol.abstract"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

/**
 * This class is the class that represents symbols of type "number".
 * Numbers are completely handled by the tokenizer/parser so there is no need to
 * create more than this concrete, empty number class that does not specify
 * a textual representation of numbers (numbers always consist of digits
 * and may include a single dot).
 */
var FrontCalculatorSymbolNumber =
/*#__PURE__*/
function (_FrontCalculatorSymbo) {
  _inherits(FrontCalculatorSymbolNumber, _FrontCalculatorSymbo);

  function FrontCalculatorSymbolNumber() {
    _classCallCheck(this, FrontCalculatorSymbolNumber);

    return _possibleConstructorReturn(this, _getPrototypeOf(FrontCalculatorSymbolNumber).call(this));
  }

  return FrontCalculatorSymbolNumber;
}(_frontCalculatorSymbol.default);

exports.default = FrontCalculatorSymbolNumber;

},{"./abstract/front.calculator.symbol.abstract":9}],18:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorSymbol = _interopRequireDefault(require("./abstract/front.calculator.symbol.abstract"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

/**
 * This class is a class that represents symbols of type "separator".
 * A separator separates the arguments of a (mathematical) function.
 * Most likely we will only need one concrete "separator" class.
 */
var FrontCalculatorSymbolSeparator =
/*#__PURE__*/
function (_FrontCalculatorSymbo) {
  _inherits(FrontCalculatorSymbolSeparator, _FrontCalculatorSymbo);

  function FrontCalculatorSymbolSeparator() {
    var _this;

    _classCallCheck(this, FrontCalculatorSymbolSeparator);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(FrontCalculatorSymbolSeparator).call(this));
    _this.identifiers = [','];
    return _this;
  }

  return FrontCalculatorSymbolSeparator;
}(_frontCalculatorSymbol.default);

exports.default = FrontCalculatorSymbolSeparator;

},{"./abstract/front.calculator.symbol.abstract":9}],19:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorSymbolFunction = _interopRequireDefault(require("../abstract/front.calculator.symbol.function.abstract"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

/**
 * Math.abs() function. Expects one parameter.
 * Example: "abs(2)" => 2, "abs(-2)" => 2, "abs(0)" => 0
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/abs
 */
var FrontCalculatorSymbolFunctionAbs =
/*#__PURE__*/
function (_FrontCalculatorSymbo) {
  _inherits(FrontCalculatorSymbolFunctionAbs, _FrontCalculatorSymbo);

  function FrontCalculatorSymbolFunctionAbs() {
    var _this;

    _classCallCheck(this, FrontCalculatorSymbolFunctionAbs);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(FrontCalculatorSymbolFunctionAbs).call(this));
    _this.identifiers = ['abs'];
    return _this;
  }

  _createClass(FrontCalculatorSymbolFunctionAbs, [{
    key: "execute",
    value: function execute(params) {
      if (params.length !== 1) {
        throw 'Error: Expected one argument, got ' + params.length;
      }

      var number = params[0];
      return Math.abs(number);
    }
  }]);

  return FrontCalculatorSymbolFunctionAbs;
}(_frontCalculatorSymbolFunction.default);

exports.default = FrontCalculatorSymbolFunctionAbs;

},{"../abstract/front.calculator.symbol.function.abstract":11}],20:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorSymbolFunction = _interopRequireDefault(require("../abstract/front.calculator.symbol.function.abstract"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

/**
 * Math.abs() function. Expects one parameter.
 * Example: "abs(2)" => 2, "abs(-2)" => 2, "abs(0)" => 0
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/abs
 */
var FrontCalculatorSymbolFunctionAvg =
/*#__PURE__*/
function (_FrontCalculatorSymbo) {
  _inherits(FrontCalculatorSymbolFunctionAvg, _FrontCalculatorSymbo);

  function FrontCalculatorSymbolFunctionAvg() {
    var _this;

    _classCallCheck(this, FrontCalculatorSymbolFunctionAvg);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(FrontCalculatorSymbolFunctionAvg).call(this));
    _this.identifiers = ['avg'];
    return _this;
  }

  _createClass(FrontCalculatorSymbolFunctionAvg, [{
    key: "execute",
    value: function execute(params) {
      if (params.length < 1) {
        throw 'Error: Expected at least one argument, got ' + params.length;
      }

      var sum = 0.0;

      for (var i = 0; i < params.length; i++) {
        sum += params[i];
      }

      return sum / params.length;
    }
  }]);

  return FrontCalculatorSymbolFunctionAvg;
}(_frontCalculatorSymbolFunction.default);

exports.default = FrontCalculatorSymbolFunctionAvg;

},{"../abstract/front.calculator.symbol.function.abstract":11}],21:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorSymbolFunction = _interopRequireDefault(require("../abstract/front.calculator.symbol.function.abstract"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

/**
 * Math.ceil() function aka round fractions up.
 * Expects one parameter.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/ceil
 */
var FrontCalculatorSymbolFunctionCeil =
/*#__PURE__*/
function (_FrontCalculatorSymbo) {
  _inherits(FrontCalculatorSymbolFunctionCeil, _FrontCalculatorSymbo);

  function FrontCalculatorSymbolFunctionCeil() {
    var _this;

    _classCallCheck(this, FrontCalculatorSymbolFunctionCeil);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(FrontCalculatorSymbolFunctionCeil).call(this));
    _this.identifiers = ['ceil'];
    return _this;
  }

  _createClass(FrontCalculatorSymbolFunctionCeil, [{
    key: "execute",
    value: function execute(params) {
      if (params.length !== 1) {
        throw 'Error: Expected one argument, got ' + params.length;
      }

      return Math.ceil(params[0]);
    }
  }]);

  return FrontCalculatorSymbolFunctionCeil;
}(_frontCalculatorSymbolFunction.default);

exports.default = FrontCalculatorSymbolFunctionCeil;

},{"../abstract/front.calculator.symbol.function.abstract":11}],22:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorSymbolFunction = _interopRequireDefault(require("../abstract/front.calculator.symbol.function.abstract"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

/**
 * Math.floor() function aka round fractions down.
 * Expects one parameter.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/floor
 */
var FrontCalculatorSymbolFunctionFloor =
/*#__PURE__*/
function (_FrontCalculatorSymbo) {
  _inherits(FrontCalculatorSymbolFunctionFloor, _FrontCalculatorSymbo);

  function FrontCalculatorSymbolFunctionFloor() {
    var _this;

    _classCallCheck(this, FrontCalculatorSymbolFunctionFloor);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(FrontCalculatorSymbolFunctionFloor).call(this));
    _this.identifiers = ['floor'];
    return _this;
  }

  _createClass(FrontCalculatorSymbolFunctionFloor, [{
    key: "execute",
    value: function execute(params) {
      if (params.length !== 1) {
        throw 'Error: Expected one argument, got ' + params.length;
      }

      return Math.floor(params[0]);
    }
  }]);

  return FrontCalculatorSymbolFunctionFloor;
}(_frontCalculatorSymbolFunction.default);

exports.default = FrontCalculatorSymbolFunctionFloor;

},{"../abstract/front.calculator.symbol.function.abstract":11}],23:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorSymbolFunction = _interopRequireDefault(require("../abstract/front.calculator.symbol.function.abstract"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance"); }

function _iterableToArray(iter) { if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === "[object Arguments]") return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = new Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

/**
 * Math.max() function. Expects at least one parameter.
 * Example: "max(1,2,3)" => 3, "max(1,-1)" => 1, "max(0,0)" => 0, "max(2)" => 2
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/max
 */
var FrontCalculatorSymbolFunctionMax =
/*#__PURE__*/
function (_FrontCalculatorSymbo) {
  _inherits(FrontCalculatorSymbolFunctionMax, _FrontCalculatorSymbo);

  function FrontCalculatorSymbolFunctionMax() {
    var _this;

    _classCallCheck(this, FrontCalculatorSymbolFunctionMax);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(FrontCalculatorSymbolFunctionMax).call(this));
    _this.identifiers = ['max'];
    return _this;
  }

  _createClass(FrontCalculatorSymbolFunctionMax, [{
    key: "execute",
    value: function execute(params) {
      if (params.length < 1) {
        throw 'Error: Expected at least one argument, got ' + params.length;
      }

      return Math.max.apply(Math, _toConsumableArray(params));
    }
  }]);

  return FrontCalculatorSymbolFunctionMax;
}(_frontCalculatorSymbolFunction.default);

exports.default = FrontCalculatorSymbolFunctionMax;

},{"../abstract/front.calculator.symbol.function.abstract":11}],24:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorSymbolFunction = _interopRequireDefault(require("../abstract/front.calculator.symbol.function.abstract"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance"); }

function _iterableToArray(iter) { if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === "[object Arguments]") return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = new Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

/**
 * Math.min() function. Expects at least one parameter.
 * Example: "min(1,2,3)" => 1, "min(1,-1)" => -1, "min(0,0)" => 0, "min(2)" => 2
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/min
 */
var FrontCalculatorSymbolFunctionMin =
/*#__PURE__*/
function (_FrontCalculatorSymbo) {
  _inherits(FrontCalculatorSymbolFunctionMin, _FrontCalculatorSymbo);

  function FrontCalculatorSymbolFunctionMin() {
    var _this;

    _classCallCheck(this, FrontCalculatorSymbolFunctionMin);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(FrontCalculatorSymbolFunctionMin).call(this));
    _this.identifiers = ['min'];
    return _this;
  }

  _createClass(FrontCalculatorSymbolFunctionMin, [{
    key: "execute",
    value: function execute(params) {
      if (params.length < 1) {
        throw 'Error: Expected at least one argument, got ' + params.length;
      }

      return Math.min.apply(Math, _toConsumableArray(params));
    }
  }]);

  return FrontCalculatorSymbolFunctionMin;
}(_frontCalculatorSymbolFunction.default);

exports.default = FrontCalculatorSymbolFunctionMin;

},{"../abstract/front.calculator.symbol.function.abstract":11}],25:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorSymbolFunction = _interopRequireDefault(require("../abstract/front.calculator.symbol.function.abstract"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

/**
 * Math.round() function aka rounds a float.
 * Expects one parameter.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/round
 */
var FrontCalculatorSymbolFunctionRound =
/*#__PURE__*/
function (_FrontCalculatorSymbo) {
  _inherits(FrontCalculatorSymbolFunctionRound, _FrontCalculatorSymbo);

  function FrontCalculatorSymbolFunctionRound() {
    var _this;

    _classCallCheck(this, FrontCalculatorSymbolFunctionRound);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(FrontCalculatorSymbolFunctionRound).call(this));
    _this.identifiers = ['round'];
    return _this;
  }

  _createClass(FrontCalculatorSymbolFunctionRound, [{
    key: "execute",
    value: function execute(params) {
      if (params.length !== 1) {
        throw 'Error: Expected one argument, got ' + params.length;
      }

      return Math.round(params[0]);
    }
  }]);

  return FrontCalculatorSymbolFunctionRound;
}(_frontCalculatorSymbolFunction.default);

exports.default = FrontCalculatorSymbolFunctionRound;

},{"../abstract/front.calculator.symbol.function.abstract":11}],26:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorSymbolOperator = _interopRequireDefault(require("../abstract/front.calculator.symbol.operator.abstract"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

/**
 * Operator for mathematical addition.
 * Example: "1+2" => 3
 *
 * @see     https://en.wikipedia.org/wiki/Addition
 *
 */
var FrontCalculatorSymbolOperatorAddition =
/*#__PURE__*/
function (_FrontCalculatorSymbo) {
  _inherits(FrontCalculatorSymbolOperatorAddition, _FrontCalculatorSymbo);

  function FrontCalculatorSymbolOperatorAddition() {
    var _this;

    _classCallCheck(this, FrontCalculatorSymbolOperatorAddition);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(FrontCalculatorSymbolOperatorAddition).call(this));
    _this.identifiers = ['+'];
    _this.precedence = 100;
    return _this;
  }

  _createClass(FrontCalculatorSymbolOperatorAddition, [{
    key: "operate",
    value: function operate(leftNumber, rightNumber) {
      return leftNumber + rightNumber;
    }
  }]);

  return FrontCalculatorSymbolOperatorAddition;
}(_frontCalculatorSymbolOperator.default);

exports.default = FrontCalculatorSymbolOperatorAddition;

},{"../abstract/front.calculator.symbol.operator.abstract":12}],27:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorSymbolOperator = _interopRequireDefault(require("../abstract/front.calculator.symbol.operator.abstract"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

/**
 * Operator for mathematical division.
 * Example: "6/2" => 3, "6/0" => PHP warning
 *
 * @see     https://en.wikipedia.org/wiki/Division_(mathematics)
 *
 */
var FrontCalculatorSymbolOperatorDivision =
/*#__PURE__*/
function (_FrontCalculatorSymbo) {
  _inherits(FrontCalculatorSymbolOperatorDivision, _FrontCalculatorSymbo);

  function FrontCalculatorSymbolOperatorDivision() {
    var _this;

    _classCallCheck(this, FrontCalculatorSymbolOperatorDivision);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(FrontCalculatorSymbolOperatorDivision).call(this));
    _this.identifiers = ['/'];
    _this.precedence = 200;
    return _this;
  }

  _createClass(FrontCalculatorSymbolOperatorDivision, [{
    key: "operate",
    value: function operate(leftNumber, rightNumber) {
      var result = leftNumber / rightNumber; // // force to 0
      // if (!isFinite(result)) {
      // 	return 0;
      // }

      return result;
    }
  }]);

  return FrontCalculatorSymbolOperatorDivision;
}(_frontCalculatorSymbolOperator.default);

exports.default = FrontCalculatorSymbolOperatorDivision;

},{"../abstract/front.calculator.symbol.operator.abstract":12}],28:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorSymbolOperator = _interopRequireDefault(require("../abstract/front.calculator.symbol.operator.abstract"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

/**
 * Operator for mathematical exponentiation.
 * Example: "3^2" => 9, "-3^2" => -9, "3^-2" equals "3^(-2)"
 *
 * @see     https://en.wikipedia.org/wiki/Exponentiation
 * @see     https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/pow
 *
 */
var FrontCalculatorSymbolOperatorExponentiation =
/*#__PURE__*/
function (_FrontCalculatorSymbo) {
  _inherits(FrontCalculatorSymbolOperatorExponentiation, _FrontCalculatorSymbo);

  function FrontCalculatorSymbolOperatorExponentiation() {
    var _this;

    _classCallCheck(this, FrontCalculatorSymbolOperatorExponentiation);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(FrontCalculatorSymbolOperatorExponentiation).call(this));
    _this.identifiers = ['^'];
    _this.precedence = 300;
    return _this;
  }

  _createClass(FrontCalculatorSymbolOperatorExponentiation, [{
    key: "operate",
    value: function operate(leftNumber, rightNumber) {
      return Math.pow(leftNumber, rightNumber);
    }
  }]);

  return FrontCalculatorSymbolOperatorExponentiation;
}(_frontCalculatorSymbolOperator.default);

exports.default = FrontCalculatorSymbolOperatorExponentiation;

},{"../abstract/front.calculator.symbol.operator.abstract":12}],29:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorSymbolOperator = _interopRequireDefault(require("../abstract/front.calculator.symbol.operator.abstract"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

/**
 * Operator for mathematical modulo operation.
 * Example: "5%3" => 2
 *
 * @see https://en.wikipedia.org/wiki/Modulo_operation
 *
 */
var FrontCalculatorSymbolOperatorModulo =
/*#__PURE__*/
function (_FrontCalculatorSymbo) {
  _inherits(FrontCalculatorSymbolOperatorModulo, _FrontCalculatorSymbo);

  function FrontCalculatorSymbolOperatorModulo() {
    var _this;

    _classCallCheck(this, FrontCalculatorSymbolOperatorModulo);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(FrontCalculatorSymbolOperatorModulo).call(this));
    _this.identifiers = ['%'];
    _this.precedence = 200;
    return _this;
  }

  _createClass(FrontCalculatorSymbolOperatorModulo, [{
    key: "operate",
    value: function operate(leftNumber, rightNumber) {
      return leftNumber % rightNumber;
    }
  }]);

  return FrontCalculatorSymbolOperatorModulo;
}(_frontCalculatorSymbolOperator.default);

exports.default = FrontCalculatorSymbolOperatorModulo;

},{"../abstract/front.calculator.symbol.operator.abstract":12}],30:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorSymbolOperator = _interopRequireDefault(require("../abstract/front.calculator.symbol.operator.abstract"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

/**
 * Operator for mathematical multiplication.
 * Example: "2*3" => 6
 *
 * @see     https://en.wikipedia.org/wiki/Multiplication
 *
 */
var FrontCalculatorSymbolOperatorMultiplication =
/*#__PURE__*/
function (_FrontCalculatorSymbo) {
  _inherits(FrontCalculatorSymbolOperatorMultiplication, _FrontCalculatorSymbo);

  function FrontCalculatorSymbolOperatorMultiplication() {
    var _this;

    _classCallCheck(this, FrontCalculatorSymbolOperatorMultiplication);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(FrontCalculatorSymbolOperatorMultiplication).call(this));
    _this.identifiers = ['*'];
    _this.precedence = 200;
    return _this;
  }

  _createClass(FrontCalculatorSymbolOperatorMultiplication, [{
    key: "operate",
    value: function operate(leftNumber, rightNumber) {
      return leftNumber * rightNumber;
    }
  }]);

  return FrontCalculatorSymbolOperatorMultiplication;
}(_frontCalculatorSymbolOperator.default);

exports.default = FrontCalculatorSymbolOperatorMultiplication;

},{"../abstract/front.calculator.symbol.operator.abstract":12}],31:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _frontCalculatorSymbolOperator = _interopRequireDefault(require("../abstract/front.calculator.symbol.operator.abstract"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

/**
 * Operator for mathematical subtraction.
 * Example: "2-1" => 1
 *
 * @see     https://en.wikipedia.org/wiki/Subtraction
 *
 */
var FrontCalculatorSymbolOperatorSubtraction =
/*#__PURE__*/
function (_FrontCalculatorSymbo) {
  _inherits(FrontCalculatorSymbolOperatorSubtraction, _FrontCalculatorSymbo);

  function FrontCalculatorSymbolOperatorSubtraction() {
    var _this;

    _classCallCheck(this, FrontCalculatorSymbolOperatorSubtraction);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(FrontCalculatorSymbolOperatorSubtraction).call(this));
    _this.identifiers = ['-'];
    _this.precedence = 100;
    /**
     * Notice: The subtraction operator is unary AND binary!
     *
     * @type {boolean}
     */

    _this.operatesUnary = true;
    return _this;
  }

  _createClass(FrontCalculatorSymbolOperatorSubtraction, [{
    key: "operate",
    value: function operate(leftNumber, rightNumber) {
      return leftNumber - rightNumber;
    }
  }]);

  return FrontCalculatorSymbolOperatorSubtraction;
}(_frontCalculatorSymbolOperator.default);

exports.default = FrontCalculatorSymbolOperatorSubtraction;

},{"../abstract/front.calculator.symbol.operator.abstract":12}]},{},[1]);
