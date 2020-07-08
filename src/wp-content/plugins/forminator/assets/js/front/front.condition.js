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
	var pluginName = "forminatorFrontCondition",
		defaults = {
			fields: {},
			relations: {}
		};

	// The actual plugin constructor
	function ForminatorFrontCondition(element, options, calendar) {
		this.element = element;
		this.$el = $(this.element);

		// jQuery has an extend method which merges the contents of two or
		// more objects, storing the result in the first object. The first object
		// is generally empty as we don't want to alter the default options for
		// future instances of the plugin
		this.settings = $.extend({}, defaults, options);
		this._defaults = defaults;
		this._name = pluginName;
		this.calendar = calendar[0];
		this.init();
	}
	// Avoid Plugin.prototype conflicts
	$.extend(ForminatorFrontCondition.prototype, {
		init: function () {
			var self = this,
				form = this.$el;
			this.add_missing_relations();
			this.$el.find('.forminator-field input, .forminator-field select, .forminator-field textarea').change(function (e) {
				var $element = $(this),
					element_id = $element.closest('.forminator-col').attr('id');
				if (typeof element_id === 'undefined') {
					element_id = $element.attr('id');
				}
				element_id = $.trim( element_id );
				//lookup condition of fields
				if (!self.has_relations(element_id) && !self.has_siblings(element_id)) return false;

				if(!self.has_relations(element_id) && self.has_siblings(element_id)){
					self.trigger_siblings(element_id);
					return false;
				}

				// Check if the field has any relations
				var relations = self.get_relations( element_id );
				// Loop all relations the field have
				relations.forEach(function (relation) {
					var logic = self.get_field_logic(relation),
						action = logic.action,
						rule = logic.rule,
						conditions = logic.conditions, // Conditions rules
						matches = 0 // Number of matches
					;

					conditions.forEach(function (condition) {
						// If rule is applicable save in matches
						if (self.is_applicable_rule(condition, action)) {
							matches++;
						}
					});

					if ((rule === "all" && matches === conditions.length) || (rule === "any" && matches > 0)) {
						var pagination = $element.closest('.forminator-pagination');
						if (relation === 'submit' && typeof pagination !== 'undefined') {
							self.toggle_field(relation, 'show', "valid");
						}
						self.toggle_field(relation, action, "valid");
						if (self.has_relations(relation)){
							if(action === 'hide'){
								self.hide_element(relation, e);
							}else{
								self.show_element(relation, e);
							}
						}
					} else {
						self.toggle_field(relation, action, "invalid");
						if (self.has_relations(relation)){
							if(action === 'show'){
								self.hide_element(relation, e);
							}else{
								self.show_element(relation, e);
							}
						}
					}
				});
			});
			this.$el.find('.forminator-button.forminator-button-back, .forminator-button.forminator-button-next').click(function (e) {
				form.find('.forminator-field input, .forminator-field select, .forminator-field textarea').change();
			});
			// Simulate change
			this.$el.find('.forminator-field input, .forminator-field select, .forminator-field textarea').change();
			this.init_events();
		},

		/**
		 * Register related events
		 *
		 * @since 1.0.3
		 */
		init_events: function () {
			var self = this;
			this.$el.on('forminator.front.condition.restart', function (e) {
				self.on_restart(e);
			});
		},

		/**
		 * Restart conditions
		 *
		 * @since 1.0.3
		 *
		 * @param e
		 */
		on_restart: function (e) {
			// restart condition
			this.$el.find('.forminator-field input, .forminator-field select, .forminator-field textarea').change();
		},

		/**
		 * Add missing relations based on fields.conditions
		 */
		add_missing_relations: function () {
			var self = this;
			var missedRelations = {};
			if (typeof this.settings.fields !== "undefined") {
				var conditionsFields = this.settings.fields;
				Object.keys(conditionsFields).forEach(function (key) {
					var conditions = conditionsFields[key]['conditions'];
					conditions.forEach(function (condition) {
						var relatedField = condition.field;
						if (!self.has_relations(relatedField)) {
							if (typeof missedRelations[relatedField] === 'undefined') {
								missedRelations[relatedField] = [];
							}
							missedRelations[relatedField].push(key);

						}
					});
				});
			}
			Object.keys(missedRelations).forEach(function (relatedField) {
				self.settings.relations[relatedField] = missedRelations[relatedField];
			});
		},

		get_field_logic: function (element_id) {
			if (typeof this.settings.fields[element_id] === "undefined") return [];
			return this.settings.fields[element_id];
		},

		has_relations: function (element_id) {
			return typeof this.settings.relations[element_id] !== "undefined";
		},

		get_relations: function (element_id) {
			if (!this.has_relations(element_id)) return [];

			return this.settings.relations[element_id];
		},

		get_field_value: function (element_id) {
			var $element = this.get_form_field(element_id),
				value = $element.val();

			//check the type of input
			if (this.field_is_radio($element)) {
				value = $element.filter(":checked").val();
			} else if (this.field_is_checkbox($element)) {
				value = [];
				$element.each(function () {
					if ($(this).is(':checked')) {
						value.push($(this).val().toLowerCase());
					}
				});
			}
			if (!value) return "";

			return value;
		},

		get_date_field_value: function(element_id){

			var $element = this.get_form_field(element_id),
				value = $element.val();

			if ( this.field_is_datepicker($element) ){

				//check if formats are accepted
				if( $element.data('format') === 'dd/mm/yy' ){
					value = $element.val().split("/").reverse().join("-");
				}
				var formattedDate = new Date(value);
				value = {'month':this.calendar.months[formattedDate.getMonth()].toLowerCase(), 'day':this.calendar.days[formattedDate.getDay()].toLowerCase() };

			}else{
				var parent 	 = $element.data('parent'),
					year 	 = this.get_form_field_value(parent+'-year'),
					mnth 	 = this.get_form_field_value(parent+'-month'),
					day  	 = this.get_form_field_value(parent+'-day');

				if( year !== "" && mnth !== "" && day !== "" ){
					var formattedDate = new Date(year+'-'+mnth+'-'+day);
					value = {'month':this.calendar.months[formattedDate.getMonth()].toLowerCase(), 'day':this.calendar.days[formattedDate.getDay()].toLowerCase() };
				}

			}

			if (!value) return "";

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

		field_is_datepicker: function ($element) {
			var is_date = false;
			$element.each(function () {
				if ($(this).hasClass('forminator-datepicker')) {
					is_date = true;
					//break
					return false;
				}
			});

			return is_date;
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

		// used in forminatorFrontCalculate
		get_form_field: function (element_id) {
			//find element by suffix -field on id input (default behavior)
			var $element = this.$el.find('#' + element_id + '-field');
			if ($element.length === 0) {
				$element = this.$el.find('.' + element_id + '-payment');
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
								//find element by select name
								$element = this.$el.find('select[name=' + element_id + ']');
								if ($element.length === 0) {
									//find element by direct id (for name field mostly)
									//will work for all field with element_id-[somestring]
									$element = this.$el.find('#' + element_id);
								}
							}
						}
					}
				}
			}

			return $element;
		},

		// Extension of get_form_field to get value
		get_form_field_value: function (element_id) {
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
							//find element by select name
							$element = this.$el.find('select[name=' + element_id + ']');
							if ($element.length === 0) {
								//find element by direct id (for name field mostly)
								//will work for all field with element_id-[somestring]
								$element = this.$el.find('#' + element_id);
							}
						}
					}
				}
			}

			return $element.val();
		},

		is_numeric: function (number) {
			return !isNaN(parseFloat(number)) && isFinite(number);
		},

		is_date_rule: function(operator){

			var dateRules  = ['day_is', 'day_is_not', 'month_is', 'month_is_not'];

			return dateRules.includes( operator );

		},

		has_siblings: function(element){
			element = this.get_form_field(element);
			if( element.data('parent') ) return true;
			return false;

		},

		trigger_siblings: function(element_id){
			var self = this,
				element = self.get_form_field(element_id),
				parent = element.data('parent'),
				siblings = [];

			siblings 	= [parent+'-year', parent+'-month', parent+'-day'];

			$.each(siblings, function( index, sibling ) {
			  	if( element_id !== sibling && self.has_relations(sibling) ){
					self.get_form_field(sibling).trigger('change');
				}
			});

		},

		is_applicable_rule: function (condition, action) {
			if (typeof condition === "undefined") return false;

			if( this.is_date_rule( condition.operator ) ){
				var value1 = this.get_date_field_value(condition.field);
			}else{
				var value1 = this.get_field_value(condition.field);
			}
			var value2 = condition.value,
				operator = condition.operator
			;

			if (action === "show") {
				return this.is_matching(value1, value2, operator) && this.is_hidden(condition.field);
			} else {
				return this.is_matching(value1, value2, operator);
			}
		},

		is_hidden: function (element_id) {
			var $element_id = this.get_form_field(element_id),
				$column_field = $element_id.closest('.forminator-col'),
				$row_field = $column_field.closest('.forminator-row')
			;

			if ( $row_field.hasClass("forminator-hidden-option") ) {
				return true;
			}

			if( $row_field.hasClass("forminator-hidden") ) {
				return false;
			}

			return true;
		},

		is_matching: function (value1, value2, operator) {
			// Match values case
			var isArrayValue = Array.isArray(value1);

			// Match values case
			if (typeof value1 === 'string') {
				value1 = value1.toLowerCase();
			}

			if(typeof value2 === 'string'){
				value2 = value2.toLowerCase();
			}

			switch (operator) {
				case "is":
					if (!isArrayValue) {
						return value1 === value2;
					} else {
						return $.inArray(value2, value1) > -1;
					}
				case "is_not":
					if (!isArrayValue) {
						return value1 !== value2;
					} else {
						return $.inArray(value2, value1) === -1;
					}
				case "is_great":
					// typecasting to integer, with return `NaN` when its literal chars, so `is_numeric` will fail
					value1 = +value1;
					value2 = +value2;
					return this.is_numeric(value1) && this.is_numeric(value2) ? value1 > value2 : false;
				case "is_less":
					value1 = +value1;
					value2 = +value2;
					return this.is_numeric(value1) && this.is_numeric(value2) ? value1 < value2 : false;
				case "contains":
					return this.contains(value1, value2);
				case "starts":
					return value1.startsWith(value2);
				case "ends":
					return value1.endsWith(value2);
				case "month_is":
					return value1.month === value2;
				case "month_is_not":
					return value1.month !== value2;
				case "day_is":
					return value1.day === value2;
				case "day_is_not":
					return value1.day !== value2;
			}

			// Return false if above are not valid
			return false;
		},

		contains: function (field_value, value) {
			return field_value.toLowerCase().indexOf(value) >= 0;
		},

		toggle_field: function (element_id, action, type) {
			var $element_id = this.get_form_field(element_id),
				$column_field = $element_id.closest('.forminator-col'),
				$hidden_upload = $column_field.find('.forminator-input-file-required'),
				$hidden_wp_editor = $column_field.find('.forminator-wp-editor-required'),
				$row_field = $column_field.closest('.forminator-row'),
				$pagination_next_field = this.$el.find('.forminator-pagination-footer').find('.forminator-button-next')
				;
			if( 'submit' === element_id ) {
				var $pagination_field = this.$el.find('.forminator-pagination-footer').find('.forminator-button-submit')
			} else {
				var $pagination_field = this.$el.find('.forminator-pagination-footer').find('#forminator-paypal-submit');
			}

			// Handle show action
			if (action === "show") {
				if (type === "valid") {
					$row_field.removeClass('forminator-hidden');
					$column_field.removeClass('forminator-hidden');
					$pagination_field.removeClass('forminator-hidden');
					$pagination_next_field.removeClass('forminator-hidden');
					if ($hidden_upload.length > 0) {
						$hidden_upload.addClass('do-validate');
					}
					if ($hidden_wp_editor.length > 0) {
						$hidden_wp_editor.addClass('do-validate');
					}
				} else {
					$column_field.addClass('forminator-hidden');
					$pagination_field.addClass('forminator-hidden');
					if ($hidden_upload.length > 0) {
						$hidden_upload.removeClass('do-validate');
					}
					if ($hidden_wp_editor.length > 0) {
						$hidden_wp_editor.removeClass('do-validate');
					}
					if ($row_field.find('> .forminator-col:not(.forminator-hidden)').length === 0) {
						$row_field.addClass('forminator-hidden');
					}
				}
			}

			// Handle hide action
			if (action === "hide") {
				if (type === "valid") {
					$column_field.addClass('forminator-hidden');
					$pagination_field.addClass('forminator-hidden');
					if ($hidden_upload.length > 0) {
						$hidden_upload.removeClass('do-validate');
					}
					if ($hidden_wp_editor.length > 0) {
						$hidden_wp_editor.removeClass('do-validate');
					}
					if ($row_field.find('> .forminator-col:not(.forminator-hidden)').length === 0) {
						$row_field.addClass('forminator-hidden');
					}
				} else {
					$row_field.removeClass('forminator-hidden');
					$column_field.removeClass('forminator-hidden');
					$pagination_field.removeClass('forminator-hidden');
					if ($hidden_upload.length > 0) {
						$hidden_upload.addClass('do-validate');
					}
					if ($hidden_wp_editor.length > 0) {
						$hidden_wp_editor.addClass('do-validate');
					}
				}
			}

			this.$el.trigger('forminator:field:condition:toggled');
		},

		clear_value: function(element_id, e) {
			var $element = this.get_form_field(element_id),
				value = this.get_field_value(element_id)
			;

			// Execute only on human action
			if (e.originalEvent !== undefined) {
				if (this.field_is_radio($element)) {
					$element.data('previous-value', value);
					$element.removeAttr('checked');
				} else if (this.field_is_checkbox($element)) {
					$element.each(function () {
						if($(this).is(':checked')) {
							$(this).data('previous-value', value);
						}
						$(this).removeAttr('checked');
					});
				} else {
					$element.data('previous-value', value);
					$element.val('');
				}
			}
		},

		restore_value: function(element_id, e) {
			var $element = this.get_form_field(element_id),
				value = $element.data('previous-value')
			;

			if(!value) return;

			// Execute only on human action
			if (e.originalEvent !== undefined) {
				if (this.field_is_radio($element)) {
					$element.val([value]);
				} else if (this.field_is_checkbox($element)) {
					$element.each(function () {
						var value = $(this).data('previous-value');

						if (!value) return;

						if (value.indexOf($(this).val()) >= 0) {
							$(this).attr("checked", "checked");
						}
					});
				} else {
					$element.val(value);
				}
			}
		},

		hide_element: function (relation, e){
			var self = this,
				sub_relations = self.get_relations(relation);

			self.clear_value(relation, e);

			sub_relations.forEach(function (sub_relation) {
				self.toggle_field(sub_relation, 'hide', "valid");
				if (self.has_relations(sub_relation)) {
					sub_relations = self.hide_element(sub_relation, e);
				}
			});
		},

		show_element: function (relation, e){
			var self = this,
				sub_relations = self.get_relations(relation);

			this.restore_value(relation, e);

			sub_relations.forEach(function (sub_relation) {
				var logic = self.get_field_logic(sub_relation),
					action = logic.action,
					rule = logic.rule,
					conditions = logic.conditions, // Conditions rules
					matches = 0 // Number of matches
				;

				conditions.forEach(function (condition) {
					// If rule is applicable save in matches
					if (self.is_applicable_rule(condition, action)) {
						matches++;
					}
				});

				if ((rule === "all" && matches === conditions.length) || (rule === "any" && matches > 0)) {
					self.toggle_field(sub_relation, action, "valid");
				}else{
					self.toggle_field(sub_relation, action, "invalid");
				}
				if (self.has_relations(sub_relation)) {
					sub_relations = self.show_element(sub_relation, e);
				}
			});
		},
	});

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function (options, calendar) {
		return this.each(function () {
			if (!$.data(this, pluginName)) {
				$.data(this, pluginName, new ForminatorFrontCondition(this, options, calendar));
			}
		});
	};

})(jQuery, window, document);
