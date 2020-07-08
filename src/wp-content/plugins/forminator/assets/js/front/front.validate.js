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
	var pluginName = "forminatorFrontValidate",
		defaults   = {
			rules: {},
			messages: {}
		};

	// The actual plugin constructor
	function ForminatorFrontValidate(element, options) {
		this.element = element;
		this.$el     = $(this.element);

		// jQuery has an extend method which merges the contents of two or
		// more objects, storing the result in the first object. The first object
		// is generally empty as we don't want to alter the default options for
		// future instances of the plugin
		this.settings  = $.extend({}, defaults, options);
		this._defaults = defaults;
		this._name     = pluginName;
		this.init();
	}

	// Avoid Plugin.prototype conflicts
	$.extend( ForminatorFrontValidate.prototype, {

		init: function () {

			var self      = this;
			var submitted = false;
			var $form     = this.$el;

			$( this.element ).validate({

				// add support for hidden required fields (uploads, wp_editor) when required
				ignore: ":hidden:not(.do-validate)",

				errorPlacement: function (error, element) {
					$form.trigger('validation:error');
				},

				showErrors: function(errorMap, errorList) {

					if( submitted && errorList.length > 0 ) {

						$form.find( '.forminator-response-message' ).html( '<ul></ul>' );

						jQuery.each( errorList, function( key, error ) {
							$form.find( '.forminator-response-message ul' ).append( '<li>' + error.message + '</li>' );
						});

						$form.find( '.forminator-response-message' )
							.removeAttr( 'aria-hidden' )
							.prop( 'tabindex', '-1' )
							.addClass( 'forminator-accessible' )
							;
					}

					submitted = false;

					this.defaultShowErrors();

					$form.trigger('validation:showError', errorList);
				},

				invalidHandler: function(form, validator){
					submitted = true;
					$form.trigger('validation:invalid');
				},

				onfocusout: function ( element ) {

					//datepicker will be validated when its closed
					if ( $( element ).hasClass('hasDatepicker') === false ) {
						$( element ).valid();
					}
					$( element ).trigger('validation:focusout');
				},

				highlight: function (element, errorClass, message) {

					var holder      = $( element );
					var holderField = holder.closest( '.forminator-field' );
					var holderDate  = holder.closest( '.forminator-date-input' );
					var holderTime  = holder.closest( '.forminator-timepicker' );
					var holderError = '';
					var getColumn   = false;
					var getError    = false;
					var getDesc     = false;

					var errorMessage = this.errorMap[element.name];
					var errorMarkup  = '<span class="forminator-error-message" aria-hidden="true"></span>';

					if ( holderDate.length > 0 ) {

						getColumn = holderDate.parent();
						getError  = getColumn.find( '.forminator-error-message[data-error-field="' + holder.data( 'field' ) + '"]' );
						getDesc   = getColumn.find( '.forminator-description' );

						errorMarkup = '<span class="forminator-error-message" data-error-field="' + holder.data( 'field' ) + '" aria-hidden="true"></span>';

						if ( 0 === getError.length ) {

							if ( 'day' === holder.data( 'field' ) ) {

								if ( getColumn.find( '.forminator-error-message[data-error-field="year"]' ).length ) {

									$( errorMarkup ).insertBefore( getColumn.find( '.forminator-error-message[data-error-field="year"]' ) );

								} else {

									if ( 0 === getDesc.length ) {
										getColumn.append( errorMarkup );
									} else {
										$( errorMarkup ).insertBefore( getDesc );
									}
								}

								if ( 0 === holderField.find( '.forminator-error-message' ).length ) {

									holderField.append(
										'<span class="forminator-error-message" aria-hidden="true"></span>'
									);
								}
							}

							if ( 'month' === holder.data( 'field' ) ) {

								if ( getColumn.find( '.forminator-error-message[data-error-field="day"]' ).length ) {

									$( errorMarkup ).insertBefore(
										getColumn.find( '.forminator-error-message[data-error-field="day"]' )
									);

								} else {

									if ( 0 === getDesc.length ) {
										getColumn.append( errorMarkup );
									} else {
										$( errorMarkup ).insertBefore( getDesc );
									}
								}

								if ( 0 === holderField.find( '.forminator-error-message' ).length ) {

									holderField.append(
										'<span class="forminator-error-message" aria-hidden="true"></span>'
									);
								}
							}

							if ( 'year' === holder.data( 'field' ) ) {

								if ( 0 === getDesc.length ) {
									getColumn.append( errorMarkup );
								} else {
									$( errorMarkup ).insertBefore( getDesc );
								}

								if ( 0 === holderField.find( '.forminator-error-message' ).length ) {

									holderField.append(
										'<span class="forminator-error-message" aria-hidden="true"></span>'
									);
								}
							}
						}

						holderError = getColumn.find( '.forminator-error-message[data-error-field="' + holder.data( 'field' ) + '"]' );

						// Insert error message
						holderError.html( errorMessage );
						holderField.find( '.forminator-error-message' ).html( errorMessage );

					} else if ( holderTime.length > 0 ) {

						getColumn = holderTime.parent();
						getError  = getColumn.find( '.forminator-error-message[data-error-field="' + holder.data( 'field' ) + '"]' );
						getDesc   = getColumn.find( '.forminator-description' );

						errorMarkup = '<span class="forminator-error-message" data-error-field="' + holder.data( 'field' ) + '" aria-hidden="true"></span>';

						if ( 0 === getError.length ) {

							if ( 'hours' === holder.data( 'field' ) ) {

								if ( getColumn.find( '.forminator-error-message[data-error-field="minutes"]' ).length ) {

									$( errorMarkup ).insertBefore(
										getColumn.find( '.forminator-error-message[data-error-field="minutes"]' )
									);
								} else {

									if ( 0 === getDesc.length ) {
										getColumn.append( errorMarkup );
									} else {
										$( errorMarkup ).insertBefore( getDesc );
									}
								}

								if ( 0 === holderField.find( '.forminator-error-message' ).length ) {

									holderField.append(
										'<span class="forminator-error-message" aria-hidden="true"></span>'
									);
								}
							}

							if ( 'minutes' === holder.data( 'field' ) ) {

								if ( 0 === getDesc.length ) {
									getColumn.append( errorMarkup );
								} else {
									$( errorMarkup ).insertBefore( getDesc );
								}

								if ( 0 === holderField.find( '.forminator-error-message' ).length ) {

									holderField.append(
										'<span class="forminator-error-message" aria-hidden="true"></span>'
									);
								}
							}
						}

						holderError = getColumn.find( '.forminator-error-message[data-error-field="' + holder.data( 'field' ) + '"]' );

						// Insert error message
						holderError.html( errorMessage );
						holderField.find( '.forminator-error-message' ).html( errorMessage );

					} else {

						var getError = holderField.find( '.forminator-error-message' );
						var getDesc  = holderField.find( '.forminator-description' );

						if ( 0 === getError.length ) {

							if ( 0 === getDesc.length ) {
								holderField.append( errorMarkup );
							} else {
								$( errorMarkup ).insertBefore( getDesc );
							}
						}

						holderError = holderField.find( '.forminator-error-message' );

						// Insert error message
						holderError.html( errorMessage );

					}

					// Field invalid status for screen readers
					holder.attr( 'aria-invalid', 'true' );

					// Field error status
					holderField.addClass( 'forminator-has_error' );
					holder.trigger('validation:highlight');

				},

				unhighlight: function (element, errorClass, validClass) {

					var holder      = $( element );
					var holderField = holder.closest( '.forminator-field' );
					var holderTime  = holder.closest( '.forminator-timepicker' );
					var holderDate  = holder.closest( '.forminator-date-input' );
					var holderError = '';

					if ( holderDate.length > 0 ) {
						holderError = holderDate.parent().find( '.forminator-error-message[data-error-field="' + holder.data( 'field' ) + '"]' );
					} else if ( holderTime.length > 0 ) {
						holderError = holderTime.parent().find( '.forminator-error-message[data-error-field="' + holder.data( 'field' ) + '"]' );
					} else {
						holderError = holderField.find( '.forminator-error-message' );
					}

					// Remove invalid attribute for screen readers
					holder.removeAttr( 'aria-invalid' );

					// Remove error message
					holderError.remove();

					// Remove error class
					holderField.removeClass( 'forminator-has_error' );
					holder.trigger('validation:unhighlight');

				},

				rules: self.settings.rules,

				messages: self.settings.messages

			});

		}
	});

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function (options) {
		return this.each(function () {
			if (!$.data(this, pluginName)) {
				$.data(this, pluginName, new ForminatorFrontValidate(this, options));
			}
		});
	};
	$.validator.addMethod("validurl", function (value, element) {
		var url = $.validator.methods.url.bind(this);
		return url(value, element) || url('http://' + value, element);
	});
	$.validator.addMethod("forminatorPhoneNational", function (value, element) {
		// Uses intlTelInput to check if the number is valid.
		return this.optional(element) || $(element).intlTelInput('isValidNumber');
	});
	$.validator.addMethod("forminatorPhoneInternational", function (value, element) {
		// Uses intlTelInput to check if the number is valid.
		return this.optional(element) || $(element).intlTelInput('isValidNumber');
	});
	$.validator.addMethod("dateformat", function (value, element, param) {
		// dateITA method from jQuery Validator additional. Date method is deprecated and doesn't work for all formats
		var check = false,
			re    = 'yy-mm-dd' === param ||
					'yy/mm/dd' === param ||
					'yy.mm.dd' === param
				? /^\d{4}-\d{1,2}-\d{1,2}$/ : /^\d{1,2}-\d{1,2}-\d{4}$/,
			adata, gg, mm, aaaa, xdata;
		value = value.replace(/[ /.]/g, '-');
		if (re.test(value)) {
			if ('dd/mm/yy' === param || 'dd-mm-yy' === param || 'dd.mm.yy' === param) {
				adata = value.split("-");
				gg    = parseInt(adata[0], 10);
				mm    = parseInt(adata[1], 10);
				aaaa  = parseInt(adata[2], 10);
			} else if ('mm/dd/yy' === param || 'mm.dd.yy' === param || 'mm-dd-yy' === param) {
				adata = value.split("-");
				mm    = parseInt(adata[0], 10);
				gg    = parseInt(adata[1], 10);
				aaaa  = parseInt(adata[2], 10);
			} else {
				adata = value.split("-");
				aaaa  = parseInt(adata[0], 10);
				mm    = parseInt(adata[1], 10);
				gg    = parseInt(adata[2], 10);
			}
			xdata = new Date(Date.UTC(aaaa, mm - 1, gg, 12, 0, 0, 0));
			if ((xdata.getUTCFullYear() === aaaa) && (xdata.getUTCMonth() === mm - 1) && (xdata.getUTCDate() === gg)) {
				check = true;
			} else {
				check = false;
			}
		} else {
			check = false;
		}
		return this.optional(element) || check;
	});
	$.validator.addMethod("maxwords", function (value, element, param) {
		return this.optional(element) || jQuery.trim(value).split(/\s+/).length <= param;
	});
	$.validator.addMethod("trim", function (value, element, param) {
		return true === this.optional(element) || 0 !== value.trim().length;
	});
	$.validator.addMethod("emailWP", function (value, element, param) {
		if (this.optional(element)) {
			return true;
		}

		// Test for the minimum length the email can be
		if (value.trim().length < 6) {
			return false;
		}

		// Test for an @ character after the first position
		if (value.indexOf('@', 1) < 0) {
			return false;
		}

		// Split out the local and domain parts
		var parts = value.split('@', 2);

		// LOCAL PART
		// Test for invalid characters
		if (!parts[0].match(/^[a-zA-Z0-9!#$%&'*+\/=?^_`{|}~\.-]+$/)) {
			return false;
		}

		// DOMAIN PART
		// Test for sequences of periods
		if (parts[1].match(/\.{2,}/)) {
			return false;
		}

		var domain = parts[1];
		// Split the domain into subs
		var subs   = domain.split('.');
		if (subs.length < 2) {
			return false;
		}

		var subsLen = subs.length;
		for (var i = 0; i < subsLen; i++) {
			// Test for invalid characters
			if (!subs[i].match(/^[a-z0-9-]+$/i)) {
				return false;
			}
		}

		return true;
	});
	$.validator.addMethod("forminatorPasswordStrength", function (value, element, param) {
		var passwordStrength = value.trim();

		//at least 8 characters
		if ( ! passwordStrength || passwordStrength.length < 8) {
			return false;
		}

		var symbolSize = 0, natLog, score;
		//at least one number
		if ( passwordStrength.match(/[0-9]/) ) {
			symbolSize += 10;
		}
		//at least one lowercase letter
		if ( passwordStrength.match(/[a-z]/) ) {
			symbolSize += 20;
		}
		//at least one uppercase letter
		if ( passwordStrength.match(/[A-Z]/) ) {
			symbolSize += 20;
		}
		if ( passwordStrength.match(/[^a-zA-Z0-9]/) ) {
			symbolSize += 30;
		}
		//at least one special character
		if ( passwordStrength.match(/[=!\-@._*#&$]/) ) {
			symbolSize += 30;
		}

		natLog = Math.log( Math.pow(symbolSize, passwordStrength.length) );
		score = natLog / Math.LN2;

		return score < 56 ? false : true;
	});

	// $.validator.methods.required = function(value, element, param) {
	// 	console.log("required", element);
	//
	// 	return someCondition && value != null;
	// }

	// override core jquertvalidation number, to use HTML5 spec
	$.validator.methods.number = function (value, element, param) {
		return this.optional(element) || /^[-+]?[0-9]+[.]?[0-9]*([eE][-+]?[0-9]+)?$/.test(value);
	};

})(jQuery, window, document);
