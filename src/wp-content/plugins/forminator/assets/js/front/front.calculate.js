// the semi-colon before function invocation is a safety net against concatenated
// scripts and/or other plugins which may not be closed properly.
;// noinspection JSUnusedLocalSymbols
(function ($, window, document, undefined) {

	"use strict";

	// undefined is used here as the undefined global variable in ECMAScript 3 is
	// mutable (ie. it can be changed by someone else). undefined isn't really being
	// passed in so we can ensure the value of it is truly undefined. In ES5, undefined
	// can no longer be modified.

	// window and document are passed through as local variables rather than global
	// as this (slightly) quickens the resolution process and can be more efficiently
	// minified (especially when both are regularly referenced in your plugin).

	// Create the defaults once
	var pluginName = "forminatorFrontCalculate",
	    defaults   = {
		    forminatorFields: [],
		    maxExpand: 5,
		    generalMessages: {},
	    };

	// The actual plugin constructor
	function ForminatorFrontCalculate(element, options) {
		this.element = element;
		this.$el     = $(this.element);

		// jQuery has an extend method which merges the contents of two or
		// more objects, storing the result in the first object. The first object
		// is generally empty as we don't want to alter the default options for
		// future instances of the plugin
		this.settings          = $.extend({}, defaults, options);
		this._defaults         = defaults;
		this._name             = pluginName;
		this.calculationFields = [];
		this.currentExpand     = 0;
		this.triggerInputs     = [];
		this.isError           = false;
		this.init();
	}

	// Avoid Plugin.prototype conflicts
	$.extend(ForminatorFrontCalculate.prototype, {
		init: function () {
			var self              = this;

			// find calculation fields
			var calculationInputs = this.$el.find('input.forminator-calculation');

			if (calculationInputs.length > 0) {

				calculationInputs.each(function () {
					self.calculationFields.push({
						$input: $(this),
						formula: $(this).data('formula'),
						name: $(this).attr('name'),
						isHidden: $(this).data('isHidden'),
						precision: $(this).data('precision'),
					});

					// isHidden
					if ($(this).data('isHidden')) {
						$(this).closest('.forminator-col').addClass('forminator-hidden forminator-hidden-option');
						var rowField = $(this).closest('.forminator-row');
						if (rowField.find('> .forminator-col:not(.forminator-hidden)').length === 0) {
							rowField.addClass('forminator-hidden forminator-hidden-option');
						}
					}
				});

				var memoizeTime = this.settings.memoizeTime || 300;

				this.debouncedReCalculateAll = this.debounce(this.recalculateAll, 1000);
				this.memoizeDebounceRender = this.memoize(this.recalculate, memoizeTime);

				this.$el.on('forminator:field:condition:toggled', function (e) {
					self.debouncedReCalculateAll();
				});

				this.parseCalcFieldsFormula();
				this.attachEventToTriggeringFields();
				this.debouncedReCalculateAll();
			}
		},

		// Memoize an expensive function by storing its results.
		memoize: function(func, wait) {
			var memo = {};
			var timeout;
			var slice = Array.prototype.slice;

			return function() {
				var args = slice.call(arguments);

				var later = function() {
					timeout = null;
					memo    = {};
				};

				clearTimeout(timeout);
				timeout = setTimeout(later, wait);

				if (args[0].name in memo) {
					return memo[args[0].name];
				} else {
					return (memo[args[0].name] = func.apply(this, args));
				}
			}
		},

		debounce: function (func, wait, immediate) {
			var timeout;
			return function() {
				var context = this, args = arguments;
				var later = function() {
					timeout = null;
					if (!immediate) func.apply(context, args);
				};
				var callNow = immediate && !timeout;
				clearTimeout(timeout);
				timeout = setTimeout(later, wait);
				if (callNow) func.apply(context, args);
			};
		},

		parseCalcFieldsFormula: function () {
			for (var i = 0; i < this.calculationFields.length; i++) {
				var calcField = this.calculationFields[i];
				var formula   = calcField.formula;

				this.currentExpand = 0;

				// Disable formula expand to allow formula calculation based on conditions
				//formula          = this.maybeExpandCalculationFieldOnFormula(formula);

				calcField.formula = formula;

				this.calculationFields[i] = calcField;
			}
		},

		maybeExpandCalculationFieldOnFormula: function (formula) {

			if (this.currentExpand > this.settings.maxExpand) {
				return formula;
			}

			var joinedFieldTypes      = this.settings.forminatorFields.join('|');
			var incrementFieldPattern = "(" + joinedFieldTypes + ")-\\d+";
			var pattern               = new RegExp('\\{(' + incrementFieldPattern + ')(\\-[A-Za-z-_]+)?\\}', 'g');
			var parsedFormula         = formula;

			var matches;
			var needExpand = false;
			while (matches = pattern.exec(formula)) {
				var fullMatch = matches[0];
				var inputName = matches[1];
				var fieldType = matches[2];

				var replace = fullMatch;

				if (fullMatch === undefined || inputName === undefined || fieldType === undefined) {
					continue;
				}

				if (fieldType === 'calculation') {
					needExpand = true;

					// find input with name, and get formula
					// bracketify
					replace = '(' + this.$el.find('input[name="' + inputName + '"]').data('formula') + ')';
				}

				parsedFormula = parsedFormula.replace(fullMatch, replace);
			}

			if (needExpand) {
				this.currentExpand++;
				parsedFormula = this.maybeExpandCalculationFieldOnFormula(parsedFormula);
			}

			return parsedFormula;
		},

		findTriggerInputs: function (calcField) {
			var formula               = calcField.formula;
			var joinedFieldTypes      = this.settings.forminatorFields.join('|');
			var incrementFieldPattern = "(" + joinedFieldTypes + ")-\\d+";
			var pattern               = new RegExp('\\{(' + incrementFieldPattern + ')(\\-[A-Za-z-_]+)?\\}', 'g');

			var matches;
			while (matches = pattern.exec(formula)) {
				var fullMatch = matches[0];
				var inputName = matches[1];
				var fieldType = matches[2];

				if (fullMatch === undefined || inputName === undefined || fieldType === undefined) {
					continue;
				}

				var formField = this.get_form_field(inputName);

				if (!formField.length) {
					continue;
				}

				var calcFields = formField.data('calcFields');
				if (calcFields === undefined) {
					calcFields = [];
				}

				var calcFieldAlreadyExist = false;

				for (var j = 0; j < calcFields.length; j++) {
					var currentCalcField = calcFields[j];
					if (currentCalcField.name === calcField.name) {
						calcFieldAlreadyExist = true;
						break;
					}
				}

				if (!calcFieldAlreadyExist) {
					calcFields.push(calcField);
				}

				formField.data('calcFields', calcFields);
				this.triggerInputs.push(formField);
			}
		},

		// taken from forminatorFrontCondition
		get_form_field: function (element_id) {
			//find element by suffix -field on id input (default behavior)
			var $element = this.$el.find('#' + element_id + '-field');
			if ($element.length === 0) {
				//find element by its on name (for radio on singlevalue)
				$element = this.$el.find('input[name=' + element_id + ']');
				if ($element.length === 0) {
					// for text area that have uniqid, so we check its name instead
					$element = this.$el.find('textarea[name=' + element_id + ']');
					if ($element.length === 0) {
						//find element by its on name[] (for checkbox on multivalue)
						$element = this.$el.find('input[name="' + element_id + '[]"]');
						if ($element.length === 0) {
							//find element by direct id (for name field mostly)
							//will work for all field with element_id-[somestring]
							$element = this.$el.find('#' + element_id);
						}
					}
				}
			}

			return $element;
		},

		attachEventToTriggeringFields: function () {
			var self = this;
			for (var i = 0; i < this.calculationFields.length; i++) {
				var calcField = this.calculationFields[i];
				this.findTriggerInputs(calcField);
			}

			if (this.triggerInputs.length > 0) {
				var cFields = [];
				for (var j = 0; j < this.triggerInputs.length; j++) {
					var $input = this.triggerInputs[j];
					var inputId = $input.attr('id');

					if (cFields.indexOf(inputId) < 0) {
						$input.on('change.forminatorFrontCalculate, blur', function () {
							var calcFields = $(this).data('calcFields');

							if (calcFields !== undefined && calcFields.length > 0) {
								for (var k = 0; k < calcFields.length; k++) {
									var calcField = calcFields[k];

									if(self.field_is_checkbox($(this)) || self.field_is_radio($(this))) {
										self.recalculate(calcField);
									} else {
										self.memoizeDebounceRender(calcField);
									}
								}
							}
						});

						cFields.push(inputId);
					}
				}
			}
		},

		recalculateAll: function () {
			for (var i = 0; i < this.calculationFields.length; i++) {
				this.recalculate(this.calculationFields[i]);
			}
		},

		recalculate: function (calcField) {
			var $input = calcField.$input;

			this.hideErrorMessage($input);

			var formula = this.maybeReplaceFieldOnFormula(calcField.formula);

			var res     = 0;
			var calc    = new window.forminatorCalculator(formula);

			try {
				res = calc.calculate();
				if (!isFinite(res)) {
					throw ('Infinity calculation result.');
				}
				res = Number.parseFloat(res).toFixed(calcField.precision);
			} catch (e) {
				this.isError = true;
				console.log(e);
				// override error message
				this.displayErrorMessage($input, this.settings.generalMessages.calculation_error);
				res = 0;
			}

			if ($input.val() !== String(res)) {
				$input.val(res).trigger("change");
			}
		},

		maybeReplaceFieldOnFormula: function (formula) {
			var joinedFieldTypes      = this.settings.forminatorFields.join('|');
			var incrementFieldPattern = "(" + joinedFieldTypes + ")-\\d+";
			var pattern               = new RegExp('\\{(' + incrementFieldPattern + ')(\\-[A-Za-z-_]+)?\\}', 'g');
			var parsedFormula         = formula;

			var matches;
			while (matches = pattern.exec(formula)) {
				var fullMatch = matches[0];
				var inputName = matches[1];
				var fieldType = matches[2];

				var replace = fullMatch;

				if (fullMatch === undefined || inputName === undefined || fieldType === undefined) {
					continue;
				}

				if(this.is_hidden(inputName)) {
					replace = 0;
					var quotedOperand = fullMatch.replace(/([\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:])/g, "\\$1");
					var regexp = new RegExp('([\\+\\-\\*\\/]?)[^\\+\\-\\*\\/\\(]*' + quotedOperand + '[^\\)\\+\\-\\*\\/]*([\\+\\-\\*\\/]?)');
					var mt = regexp.exec(formula);
					if (mt) {
						// if operand in multiplication or division set value = 1
						if (mt[1] === '*' || mt[1] === '/' || mt[2] === '*' || mt[2] === '/') {
							replace = 1;
						}
					}
				} else {
					if (fieldType === 'calculation') {
						var calcField = this.get_calculation_field(inputName);

						if (calcField) {
							this.memoizeDebounceRender( calcField );
						}
					}

					replace = this.get_field_value(inputName);
				}

				// bracketify
				replace       = '(' + replace + ')';
				parsedFormula = parsedFormula.replace(fullMatch, replace);
			}

			return parsedFormula;
		},


		get_calculation_field: function (element_id) {
			for (var i = 0; i < this.calculationFields.length; i++) {
				if(this.calculationFields[i].name === element_id) {
					return this.calculationFields[i];
				}
			}

			return false;
		},

		is_hidden: function (element_id) {
			var $element_id = this.get_form_field(element_id),
				$column_field = $element_id.closest('.forminator-col'),
				$row_field = $column_field.closest('.forminator-row')
			;

			if( $row_field.hasClass("forminator-hidden-option") || $column_field.hasClass("forminator-hidden-option") ) {
				return false;
			}

			if( $row_field.hasClass("forminator-hidden") || $column_field.hasClass("forminator-hidden") ) {
				return true;
			}

			return false;
		},

		get_field_value: function (element_id) {
			var $element    = this.get_form_field(element_id);
			var value       = 0;
			var calculation = 0;
			var checked     = null;

			if (this.field_is_radio($element)) {
				checked = $element.filter(":checked");
				if (checked.length) {
					calculation = checked.data('calculation');
					if (calculation !== undefined) {
						value = Number(calculation);
					}
				}
			} else if (this.field_is_checkbox($element)) {
				$element.each(function () {
					if ($(this).is(':checked')) {
						calculation = $(this).data('calculation');
						if (calculation !== undefined) {
							value += Number(calculation);
						}
					}
				});

			} else if (this.field_is_select($element)) {
				checked = $element.find("option").filter(':selected');
				if (checked.length) {
					calculation = checked.data('calculation');
					if (calculation !== undefined) {
						value = Number(calculation);
					}
				}
			} else {
				value = Number($element.val());
			}

			return isNaN(value) ? 0 : value;
		},

		field_is_radio: function ($element) {
			var is_radio = false;
			$element.each(function () {
				if ($(this).attr('type') === 'radio') {
					is_radio = true;
					//break
					return false;
				}
			});

			return is_radio;
		},

		field_is_checkbox: function ($element) {
			var is_checkbox = false;
			$element.each(function () {
				if ($(this).attr('type') === 'checkbox') {
					is_checkbox = true;
					//break
					return false;
				}
			});

			return is_checkbox;
		},

		field_is_select: function ($element) {
			return $element.is('select');
		},

		displayErrorMessage: function ($element, errorMessage) {
			var $field_holder = $element.closest('.forminator-field--inner');

			if ($field_holder.length === 0) {
				$field_holder = $element.closest('.forminator-field');
			}

			var $error_holder = $field_holder.find('.forminator-error-message');

			if ($error_holder.length === 0) {
				$field_holder.append('<span class="forminator-error-message" aria-hidden="true"></span>');
				$error_holder = $field_holder.find('.forminator-error-message');
			}

			$element.attr('aria-invalid', 'true');
			$error_holder.html(errorMessage);
			$field_holder.addClass('forminator-has_error');
		},

		hideErrorMessage: function ($element) {
			var $field_holder = $element.closest('.forminator-field--inner');

			if ($field_holder.length === 0) {
				$field_holder = $element.closest('.forminator-field');
			}

			var $error_holder = $field_holder.find('.forminator-error-message');

			$element.removeAttr('aria-invalid');
			$error_holder.remove();
			$field_holder.removeClass('forminator-has_error');
		}
	});

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function (options) {
		return this.each(function () {
			if (!$.data(this, pluginName)) {
				$.data(this, pluginName, new ForminatorFrontCalculate(this, options));
			}
		});
	};

})(jQuery, window, document);
