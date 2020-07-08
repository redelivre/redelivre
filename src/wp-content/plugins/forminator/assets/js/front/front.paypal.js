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
	var pluginName = "forminatorFrontPayPal",
		defaults   = {
			type: 'paypal',
			paymentEl: null,
			paymentRequireSsl: false,
			generalMessages: {},
		};

	// The actual plugin constructor
	function ForminatorFrontPayPal(element, options) {
		this.element = element;
		this.$el     = $(this.element);

		// jQuery has an extend method which merges the contents of two or
		// more objects, storing the result in the first object. The first object
		// is generally empty as we don't want to alter the default options for
		// future instances of the plugin
		this.settings              = $.extend({}, defaults, options);
		this._defaults             = defaults;
		this._name                 = pluginName;
		this.paypalData            = null;
		this.init();
	}

	// Avoid Plugin.prototype conflicts
	$.extend(ForminatorFrontPayPal.prototype, {
		init: function () {
			if (!this.settings.paymentEl) {
				return;
			}

			var self         = this;
			this.paypalData = self.settings.paymentEl;

			this.render_paypal_button();
		},

		is_data_valid: function() {
			var paypalData = this.configurePayPal(),
				requireSsl = this.settings.paymentRequireSsl
			;

			if ( paypalData.amount <= 0 ) {
				return false;
			}

			if ( requireSsl && 'https:' !== location.protocol ) {
				return false;
			}

			return true;
		},

		is_form_valid: function() {
			var validate = this.$el.validate(); // Get validate instance
			var isValid = validate.checkForm(); // Valid?
			validate.submitted = {}; // Reset immediate form field checking mode

			return isValid;
		},

		render_paypal_button: function () {
			var $form = this.$el,
				self = this,
				paypalData = this.configurePayPal(),
				$target_message = $form.find('.forminator-response-message'),
				paypalActions,
				error_msg = ForminatorFront.cform.gateway.error,
				requireSsl = this.settings.paymentRequireSsl,
				generalMessage = this.settings.generalMessages,
				style_data = {
					shape: paypalData.shape,
					color: paypalData.color,
					label: paypalData.label,
					layout: paypalData.layout,
					height: parseInt( paypalData.height ),
				};

			if( paypalData.layout !== 'vertical' ) {
				style_data.tagline =  paypalData.tagline;
			}

			paypal.Buttons({
				onInit: function(data, actions) {
					actions.disable();

					if ( paypalData.amount_type === 'variable' && paypalData.variable !== '' ) {
						paypalData.amount = self.get_field_calculation( paypalData.variable );
					}

					// Listen for form field changes
					$form.find('input, select, textarea').change( function() {
						if ( self.is_data_valid() && self.is_form_valid() ) {
							actions.enable();
						}
					});

					// Check if the form is valid on init
					if ( self.is_data_valid() && self.is_form_valid() ) {
						actions.enable();
					}
				},

				env: paypalData.mode,
				style: style_data,
				onClick: function () {
					if( ! $form.valid() && paypalData.amount <= 0 ) {
						$target_message.removeClass('forminator-accessible').addClass('forminator-error').html('').removeAttr( 'aria-hidden' );
						$target_message.html('<label class="forminator-label--error"><span>' + generalMessage.payment_require_amount_error + '</span></label>');
						self.focus_to_element($target_message);
					} else if ( requireSsl && 'https:' !== location.protocol ) {
						$target_message.removeClass('forminator-accessible').addClass('forminator-error').html('').removeAttr( 'aria-hidden' );
						$target_message.html('<label class="forminator-label--error"><span>' + generalMessage.payment_require_ssl_error + '</span></label>');
						self.focus_to_element($target_message);
					}

					if ( paypalData.amount_type === 'variable' && paypalData.variable !== '' ) {
						paypalData.amount = self.get_field_calculation( paypalData.variable );
					}
				},
				createOrder: function(data, actions) {
					$form.addClass('forminator-partial-disabled');

					return actions.order.create({
						purchase_units: [{
							amount: {
								value: paypalData.amount
							}
						}]
					});
				},
				onApprove: function(data, actions) {
					if( typeof self.settings.has_loader !== "undefined" && self.settings.has_loader ) {
						// Disable form fields
						$form.addClass('forminator-fields-disabled');

						$target_message.html('<p>' + self.settings.loader_label + '</p>');

						$target_message.removeAttr("aria-hidden")
							.prop("tabindex", "-1")
							.removeClass('forminator-success forminator-error')
							.addClass('forminator-loading forminator-show');

						self.focus_to_element($target_message);
					}

					return actions.order.capture().then(function(details) {
						var transaction_id = details['purchase_units'][0]['payments']['captures'][0]['id'];
						if( typeof transaction_id === 'undefined' ) {
							return false;
						}
						var get_nonce = $form.find('input[name="forminator_nonce"]').val(),
							payment_data = {
								details: details,
								payment_id: transaction_id,
								forminator_nonce: get_nonce,
								action: 'forminator_submit_form_custom-forms'
							};

						jQuery.ajax({
							type: 'POST',
							url: ForminatorFront.ajaxUrl,
							data: jQuery.param(payment_data),
							success: function (response) {
								if ( response && response.data.success === true ) {
									$form.find('.forminator-paypal-input').val( transaction_id );
									$form.trigger('submit');
								} else {
									error_msg = response.data.error ? response.data.error : error_msg;
									$target_message.removeClass('forminator-accessible').addClass('forminator-error').html('').removeAttr( 'aria-hidden' );
									$target_message.html('<label class="forminator-label--error"><span>' + error_msg + '</span></label>');
									self.focus_to_element($target_message);
								}
							},
							error: function (response) {
								error_msg = response.data ? response.error_message : error_msg;
								$target_message.removeClass('forminator-accessible').addClass('forminator-error').html('').removeAttr( 'aria-hidden' );
								$target_message.html('<label class="forminator-label--error"><span>' + error_msg + '</span></label>');
								self.focus_to_element($target_message);
							}
						});
					});
				},
				onCancel: function (data, actions) {
					if( typeof self.settings.has_loader !== "undefined" && self.settings.has_loader ) {
						// Enable form fields
						$form.removeClass('forminator-fields-disabled forminator-partial-disabled');

						$target_message.removeClass('forminator-loading');
					}

					return actions.redirect();
				},
				onError: function () {
					if( typeof self.settings.has_loader !== "undefined" && self.settings.has_loader ) {
						// Enable form fields
						$form.removeClass('forminator-fields-disabled forminator-partial-disabled');

						$target_message.removeClass('forminator-loading');
					}

					$target_message.removeClass('forminator-accessible').addClass('forminator-error').html('').removeAttr( 'aria-hidden' );
					$target_message.html('<label class="forminator-label--error"><span>' + error_msg + '</span></label>');
					self.focus_to_element($target_message);
				},
			}).render('#paypal-button-container-' + paypalData.form_id);
		},

		configurePayPal: function () {
			var self   = this,
				paypalConfig = {
				form_id: this.getPayPalData('form_id'),
				sandbox_id: this.getPayPalData('sandbox_id'),
				currency: this.getPayPalData('currency'),
				live_id: this.getPayPalData('live_id'),
				amount: 0
			};

			paypalConfig.color = this.getPayPalData('color') ? this.getPayPalData('color') : 'gold';
			paypalConfig.shape = this.getPayPalData('shape') ? this.getPayPalData('shape') : 'rect';
			paypalConfig.label = this.getPayPalData('label') ? this.getPayPalData('label') : 'checkout';
			paypalConfig.layout = this.getPayPalData('layout') ? this.getPayPalData('layout') : 'vertical';
			paypalConfig.tagline = this.getPayPalData('tagline') ? this.getPayPalData('tagline') : 'true';
			paypalConfig.redirect_url = this.getPayPalData('redirect_url') ? this.getPayPalData('redirect_url') : '';
			paypalConfig.mode = this.getPayPalData('mode');
			paypalConfig.locale = this.getPayPalData('locale') ? this.getPayPalData('locale') : 'en_US';
			paypalConfig.debug_mode = this.getPayPalData('debug_mode') ? this.getPayPalData('debug_mode') : 'disable';
			paypalConfig.amount_type = this.getPayPalData('amount_type') ? this.getPayPalData('amount_type') : 'fixed';
			paypalConfig.variable = this.getPayPalData('variable') ? this.getPayPalData('variable') : '';
			paypalConfig.height = this.getPayPalData('height') ? this.getPayPalData('height') : 55;

			var	amountType = this.getPayPalData('amount_type');
			if (amountType === 'fixed') {
				paypalConfig.amount = this.getPayPalData('amount');
			} else if( amountType === 'variable' && paypalConfig.variable !== '' ) {
				paypalConfig.amount =  this.get_field_calculation( paypalConfig.variable );
			}


			return paypalConfig;
		},

		getPayPalData: function (key) {
			if (typeof this.paypalData[key] !== 'undefined') {
				return this.paypalData[key];
			}

			return null;
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

		get_field_calculation: function (element_id) {
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

		focus_to_element: function ($element, fadeout) {
			fadeout = fadeout || false;

			if( fadeout ) {
				fadeout = this.settings.fadeout;
			}

			var fadeout_time = this.settings.fadeout_time;

			// force show in case its hidden of fadeOut
			$element.show();
			$('html,body').animate({scrollTop: ($element.offset().top - ($(window).height() - $element.outerHeight(true)) / 2)}, 500, function () {
				if (!$element.attr("tabindex")) {
					$element.attr("tabindex", -1);
				}
				$element.focus();
				if (fadeout) {
					$element.show().delay( fadeout_time ).fadeOut('slow');
				}

			});
		},


	});

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function (options) {
		return this.each(function () {
			if (!$.data(this, pluginName)) {
				$.data(this, pluginName, new ForminatorFrontPayPal(this, options));
			}
		});
	};

})(jQuery, window, document);
