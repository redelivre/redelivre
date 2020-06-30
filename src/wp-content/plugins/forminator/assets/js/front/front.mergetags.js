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
	var pluginName = "forminatorFrontMergeTags",
	    defaults   = {
		    forminatorFields: [],
	    };

	// The actual plugin constructor
	function forminatorFrontMergeTags(element, options) {
		this.element = element;
		this.$el     = $(this.element);

		// jQuery has an extend method which merges the contents of two or
		// more objects, storing the result in the first object. The first object
		// is generally empty as we don't want to alter the default options for
		// future instances of the plugin
		this.settings          = $.extend({}, defaults, options);
		this._defaults         = defaults;
		this._name             = pluginName;
		this.formFields        = [];
		this.init();
	}

	// Avoid Plugin.prototype conflicts
	$.extend(forminatorFrontMergeTags.prototype, {
		init: function () {
			var self = this;
			var fields = this.$el.find('.forminator-merge-tags');

			if (fields.length > 0) {
				fields.each(function () {
					self.formFields.push({
						$input: $(this),
						value: $(this).html(),
					});
				});
			}

			this.replaceAll();
			this.attachEvents();
		},

		attachEvents: function () {
			var self = this;

			this.$el.find(
				'input.forminator-input, .forminator-select, .forminator-checkbox, .forminator-radio, .forminator-input-file, .forminator-select2'
			).each(function () {
				$(this).on('change', function () {
					self.replaceAll();
				});
			});
		},

		replaceAll: function () {
			for (var i = 0; i < this.formFields.length; i++) {
				this.replace(this.formFields[i]);
			}
		},

		replace: function ( field ) {
			var $input = field.$input;
			var res = this.maybeReplaceValue(field.value);

			$input.html(res);
		},

		maybeReplaceValue: function (value) {
			var joinedFieldTypes      = this.settings.forminatorFields.join('|');
			var incrementFieldPattern = "(" + joinedFieldTypes + ")-\\d+";
			var pattern               = new RegExp('\\{(' + incrementFieldPattern + ')(\\-[A-Za-z-_]+)?\\}', 'g');
			var parsedValue           = value;

			var matches;
			while (matches = pattern.exec(value)) {
				var fullMatch = matches[0];
				var inputName = fullMatch.replace('{', '').replace('}', '');
				var fieldType = matches[2];

				var replace = fullMatch;

				if (fullMatch === undefined || inputName === undefined || fieldType === undefined) {
					continue;
				}

				replace = this.get_field_value(inputName);

				// bracketify
				replace       = replace;
				parsedValue = parsedValue.replace(fullMatch, replace);
			}

			return parsedValue;
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

		get_field_value: function (element_id) {
			var $element    = this.get_form_field(element_id);
			var value       = '';
			var checked     = null;

			if (this.field_is_radio($element)) {
				checked = $element.filter(":checked");

				if (checked.length) {
					value = checked.val();
				}
			} else if (this.field_is_checkbox($element)) {
				$element.each(function () {
					if ($(this).is(':checked')) {
						if(value !== "") {
							value += ', ';
						}

						value += $(this).val();
					}
				});

			} else if (this.field_is_select($element)) {
				checked = $element.find("option").filter(':selected');
				if (checked.length) {
					value = checked.val();
				}
			} else if (this.field_is_upload($element)) {
				value = $element.val().split('\\').pop();
			} else {
				value = $element.val();
			}

			return value;
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

		field_is_upload: function ($element) {
			if ($element.attr('type') === 'file') {
				return true;
			}

			return false;
		},

		field_is_select: function ($element) {
			return $element.is('select');
		},
	});

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function (options) {
		return this.each(function () {
			if (!$.data(this, pluginName)) {
				$.data(this, pluginName, new forminatorFrontMergeTags(this, options));
			}
		});
	};

})(jQuery, window, document);
