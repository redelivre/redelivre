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
	var pluginName = "forminatorFrontDatePicker",
		defaults = {};

	// The actual plugin constructor
	function ForminatorFrontDatePicker(element, options) {
		this.element = element;
		this.$el = $(this.element);

		// jQuery has an extend method which merges the contents of two or
		// more objects, storing the result in the first object. The first object
		// is generally empty as we don't want to alter the default options for
		// future instances of the plugin
		this.settings = $.extend({}, defaults, options);
		this._defaults = defaults;
		this._name = pluginName;
		this.init();
	}

	// Avoid Plugin.prototype conflicts
	$.extend(ForminatorFrontDatePicker.prototype, {
		init: function () {
			var self = this,
				dateFormat = this.$el.data('format'),
				restrictType = this.$el.data('restrict-type'),
				restrict = this.$el.data('restrict'),
				restrictedDays = this.$el.data('restrict'),
				minYear = this.$el.data('start-year'),
				maxYear = this.$el.data('end-year');

			//possible restrict only one
			if (!isNaN(parseFloat(restrictedDays)) && isFinite(restrictedDays)) {
				restrictedDays = [restrictedDays.toString()];
			} else {
				restrictedDays = restrict.split(',');
			}

			if (!minYear) {
				minYear = "c-95";
			}
			if (!maxYear) {
				maxYear = "c+95";
			}
			var disabledWeekDays = function (current_date) {
				if (restrictType === "week") {
					return self.restrict_week(restrictedDays, current_date);
				} else {
					return self.restrict_custom(restrictedDays, current_date);
				}
			};

			var parent = this.$el.closest('.forminator-custom-form'),
				add_class = "forminator-calendar";

			if ( parent.hasClass('forminator-design--default') ) {
				add_class = "forminator-calendar--default";
			} else if ( parent.hasClass('forminator-design--material') ) {
				add_class = "forminator-calendar--material";
			} else if ( parent.hasClass('forminator-design--flat') ) {
				add_class = "forminator-calendar--flat";
			} else if ( parent.hasClass('forminator-design--bold') ) {
				add_class = "forminator-calendar--bold";
			}


			this.$el.datepicker({
				"beforeShow": function (input, inst) {

					// Remove all Hustle UI related classes
					( inst.dpDiv ).removeClass( function( index, css ) {
						return ( css.match ( /\bhustle-\S+/g ) || []).join( ' ' );
					});

					// Remove all Forminator UI related classes
					( inst.dpDiv ).removeClass( function( index, css ) {
						return ( css.match ( /\bforminator-\S+/g ) || []).join( ' ' );
					});

					( inst.dpDiv ).addClass( 'forminator-custom-form-' + parent.data( 'form-id' ) + ' ' + add_class );

				},
				"beforeShowDay": disabledWeekDays,
				"monthNames": datepickerLang.monthNames,
				"monthNamesShort": datepickerLang.monthNamesShort,
				"dayNames": datepickerLang.dayNames,
				"dayNamesShort": datepickerLang.dayNamesShort,
				"dayNamesMin": datepickerLang.dayNamesMin,
				"changeMonth": true,
				"changeYear": true,
				"dateFormat": dateFormat,
				"yearRange": minYear + ":" + maxYear,
				"minDate": new Date(minYear, 0, 1),
				"maxDate": new Date(maxYear, 11, 31),
				"onClose": function () {
					//Called when the datepicker is closed, whether or not a date is selected
					$(this).valid();
				}
			});
		},
		restrict_week: function (restrictedDays, date) {
			var day = date.getDay();

			if (restrictedDays.indexOf(day.toString()) !== -1) {
				return [false, "disabledDate"]
			} else {
				return [true, "enabledDate"]
			}
		},

		restrict_custom: function (restrictedDays, date) {
			var month = [];
			month[0] = "January";
			month[1] = "February";
			month[2] = "March";
			month[3] = "April";
			month[4] = "May";
			month[5] = "June";
			month[6] = "July";
			month[7] = "August";
			month[8] = "September";
			month[9] = "October";
			month[10] = "November";
			month[11] = "December";

			var custom_month = date.getMonth(),
				custom_day = date.getDate(),
				custom_year = date.getFullYear(),
				custom_date = custom_day + " " + month[custom_month] + " " + custom_year
			;

			if (restrictedDays.indexOf(custom_date) !== -1) {
				return [false, "disabledDate"]
			} else {
				return [true, "enabledDate"]
			}
		}

	});

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function (options) {
		return this.each(function () {
			if (!$.data(this, pluginName)) {
				$.data(this, pluginName, new ForminatorFrontDatePicker(this, options));
			}
		});
	};

})(jQuery, window, document);
