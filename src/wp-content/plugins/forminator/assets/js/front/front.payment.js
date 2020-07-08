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
	var pluginName = "forminatorFrontPayment",
	    defaults   = {
		    type: 'stripe',
		    paymentEl: null,
		    paymentRequireSsl: false,
		    generalMessages: {},
	    };

	// The actual plugin constructor
	function ForminatorFrontPayment(element, options) {
		this.element = element;
		this.$el     = $(this.element);

		// jQuery has an extend method which merges the contents of two or
		// more objects, storing the result in the first object. The first object
		// is generally empty as we don't want to alter the default options for
		// future instances of the plugin
		this.settings              = $.extend({}, defaults, options);
		this._defaults             = defaults;
		this._name                 = pluginName;
		this._stripeData           = null;
		this._stripe			   = null;
		this._cardElement          = null;
		this._stripeToken		   = null;
		this._beforeSubmitCallback = null;
		this._form                 = null;
		this._paymentIntent        = null;
		this.init();
	}

	// Avoid Plugin.prototype conflicts
	$.extend(ForminatorFrontPayment.prototype, {
		init: function () {
			if (!this.settings.paymentEl) {
				return;
			}

			var self         = this;
			this._stripeData = this.settings.paymentEl.data();

			this.mountCardField();

			$(this.element).on('payment.before.submit.forminator', function (e, formData, callback) {
				self._form = self.getForm(e);
				self._beforeSubmitCallback = callback;
				self.validateStripe(e, formData);
			});

			// Listen for fields change to update ZIP mapping
			this.$el.find(
				'input.forminator-input, .forminator-select, .forminator-checkbox, .forminator-radio, .forminator-select2'
			).each(function () {
				$(this).on('change', function (e) {
					self.mapZip(e);
				});
			});
		},

		validateStripe: function(e, formData) {
			var self = this;

			this._stripe.createToken(this._cardElement).then(function (result) {
				if (result.error) {
					self.showCardError(result.error.message, true);
				} else {
					self.hideCardError();
					self.updateAmount(e, formData);
				}
			});
		},

		isValid: function(focus) {
			var self = this;

			this._stripe.createToken(this._cardElement).then(function (result) {
				if (result.error) {
					self.showCardError(result.error.message, focus);
				} else {
					self.hideCardError();
				}
			});
		},

		getForm: function(e) {
			var $form = $( e.target );

			if(!$form.hasClass('forminator-custom-form')) {
				$form = $form.closets('form.forminator-custom-form');
			}

			return $form;
		},

		updateAmount: function(e, formData) {
			e.preventDefault();
			var self = this;
			var updateFormData = formData;

			updateFormData.set( 'action', 'forminator_update_payment_amount' );
			updateFormData.set( 'paymentid', this.getStripeData('paymentid') );

			$.ajax({
				type: 'POST',
				url: window.ForminatorFront.ajaxUrl,
				data: updateFormData,
				cache: false,
				contentType: false,
				processData: false,
				beforeSend: function () {
					if( typeof self.settings.has_loader !== "undefined" && self.settings.has_loader ) {
						// Disable form fields
						self._form.addClass('forminator-fields-disabled');

						var $target_message = self._form.find('.forminator-response-message');

						$target_message.html('<p>' + self.settings.loader_label + '</p>');

						$target_message.removeAttr("aria-hidden")
							.prop("tabindex", "-1")
							.removeClass('forminator-success forminator-error')
							.addClass('forminator-loading forminator-show');
					}

					self._form.find('button').attr('disabled', true);
				},
				success: function (data) {
					if (data.success === true) {
						// Store payment id
						if (typeof data.data !== undefined && typeof data.data.paymentid !== undefined) {
							self.$el.find('#forminator-stripe-paymentid').val(data.data.paymentid);
							self._stripeData['paymentid'] = data.data.paymentid;
						}

						self.handleCardPayment(data, e, formData);
					} else {
						self.show_error(data.data.message);

						if(data.data.errors.length) {
							self.show_messages(data.data.errors);
						}

						var $captcha_field = self._form.find('.forminator-g-recaptcha');

						if ($captcha_field.length) {
							$captcha_field = $($captcha_field.get(0));

							var recaptcha_widget = $captcha_field.data('forminator-recapchta-widget'),
								recaptcha_size = $captcha_field.data('size');

							if (recaptcha_size === 'invisible') {
								window.grecaptcha.reset(recaptcha_widget);
							}
						}
					}
				},
				error: function (err) {
					var $message = err.status === 400 ? window.ForminatorFront.cform.upload_error : window.ForminatorFront.cform.error;

					self.show_error($message);
				}
			})
		},

		show_error: function(message) {
			var $target_message = this._form.find('.forminator-response-message');

			this._form.find('button').removeAttr('disabled');

			$target_message.removeAttr("aria-hidden")
				.prop("tabindex", "-1")
				.removeClass('forminator-loading')
				.addClass('forminator-error forminator-show');

			$target_message.html('<p>' + message + '</p>');

			this.focus_to_element($target_message);

			this.enable_form();
		},

		enable_form: function() {
			if( typeof this.settings.has_loader !== "undefined" && this.settings.has_loader ) {
				var $target_message = this._form.find('.forminator-response-message');

				// Enable form fields
				this._form.removeClass('forminator-fields-disabled');

				$target_message.removeClass('forminator-loading');
			}
		},

		mapZip: function (e) {
			var verifyZip = this.getStripeData('veifyZip');
			var zipField = this.getStripeData('zipField');
			var changedField = $(e.currentTarget).attr('name');

			// Verify ZIP is enabled, mapped field is not empty and changed field is the mapped field, proceed
			if (verifyZip && zipField !== "" && changedField === zipField) {
				if (e.originalEvent !== undefined) {
					// Get field
					var value = this.get_field_value(zipField);

					// Update card element
					this._cardElement.update({
						value: {
							postalCode: value
						}
					});
				}
			}
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

		show_messages: function (errors) {
			var self = this,
				forminatorFrontCondition = self.$el.data('forminatorFrontCondition');
			if (typeof forminatorFrontCondition !== 'undefined') {
				// clear all validation message before show new one
				this.$el.find('.forminator-error-message').remove();
				var i = 0;
				errors.forEach(function (value) {
					var element_id = Object.keys(value),
						message = Object.values(value),
						element = forminatorFrontCondition.get_form_field(element_id);
					if (element.length) {
						if (i === 0) {
							// focus on first error
							self.$el.trigger('forminator.front.pagination.focus.input',[element]);
							self.focus_to_element(element);
						}

						if ($(element).hasClass('forminator-input-time')) {
							var $time_field_holder = $(element).closest('.forminator-field:not(.forminator-field--inner)'),
								$time_error_holder = $time_field_holder.children('.forminator-error-message');

							if ($time_error_holder.length === 0) {
								$time_field_holder.append('<span class="forminator-error-message" aria-hidden="true"></span>');
								$time_error_holder = $time_field_holder.children('.forminator-error-message');
							}
							$time_error_holder.html(message);
						}

						var $field_holder = $(element).closest('.forminator-field--inner');

						if ($field_holder.length === 0) {
							$field_holder = $(element).closest('.forminator-field');
							if ($field_holder.length === 0) {
								// handling postdata field
								$field_holder = $(element).find('.forminator-field');
								if ($field_holder.length > 1) {
									$field_holder = $field_holder.first();
								}
							}
						}

						var $error_holder = $field_holder.find('.forminator-error-message');

						if ($error_holder.length === 0) {
							$field_holder.append('<span class="forminator-error-message" aria-hidden="true"></span>');
							$error_holder = $field_holder.find('.forminator-error-message');
						}
						$(element).attr('aria-invalid', 'true');
						$error_holder.html(message);
						$field_holder.addClass('forminator-has_error');
						i++;
					}
				});
			}

			return this;
		},

		getBillingData: function (formData) {
			var billing = this.getStripeData('billing');

			// If billing is disabled, return
			if (!billing) {
				return {}
			};

			// Get billing fields
			var billingName = this.getStripeData('billingName');
			var billingEmail = this.getStripeData('billingEmail');
			var billingAddress = this.getStripeData('billingAddress');

			// Create billing object
			var billingObject = {
				address: {}
			}

			if( billingName ) {
				var nameField = this.get_field_value(billingName);

				// Check if Name field is multiple
				if (!nameField) {
					var fName = this.get_field_value(billingName + '-first-name') || '';
					var lName = this.get_field_value(billingName + '-last-name') || '';

					nameField = fName + ' ' + lName;
				}

				// Check if Name field is empty in the end, if not assign to the object
				if (nameField) {
					billingObject.name = nameField;
				}
			}

			// Map email field
			if(billingEmail) {
				var billingEmailValue = this.get_field_value(billingEmail) || '';
				if (billingEmailValue) {
					billingObject.email = billingEmailValue;
				}
			}

			// Map address line 1 field
			var addressLine1 = this.get_field_value(billingAddress + '-street_address') || '';
			if (addressLine1) {
				billingObject.address.line1 = addressLine1;
			}

			// Map address line 2 field
			var addressLine2 = this.get_field_value(billingAddress + '-address_line') || '';
			if (addressLine2) {
				billingObject.address.line2 = addressLine2;
			}

			// Map address city field
			var addressCity = this.get_field_value(billingAddress + '-city') || '';
			if (addressCity) {
				billingObject.address.city = addressCity;
			}

			// Map address state field
			var addressState = this.get_field_value(billingAddress + '-state') || '';
			if (addressState) {
				billingObject.address.state = addressState;
			}

			// Map address country field
			var countryField = this.get_form_field(billingAddress + '-country');
			var addressCountry = countryField.find(':selected').data('country-code');

			if (addressCountry) {
				billingObject.address.country = addressCountry;
			}

			// Map address country field
			var addressZip = this.get_field_value(billingAddress + '-zip') || '';
				if (addressZip) {
				billingObject.address.postal_code = addressZip;
			}

			return {
				payment_method_data: {
					billing_details: billingObject
				}
			}
		},

		handleCardPayment: function (data, e, formData) {
			var self = this;
			var secret = data.data.paymentsecret || false;

			// Check if we already tried to pay
			if(this._paymentIntent !== null) {
				if(typeof this._paymentIntent.paymentIntent !== "undefined") {
					// Check if payment was successful
					if(this._paymentIntent.paymentIntent.status === "succeeded") {
						// Success, submit the form
						if (self._beforeSubmitCallback) {
							self._beforeSubmitCallback.call();
						}

						return;
					}
				}
			}

			var receipt = this.getStripeData('receipt');
			var receiptEmail = this.getStripeData('receiptEmail');
			var receiptObject = {};

			if( receipt && receiptEmail ) {
				receiptObject = {
					receipt_email: this.get_field_value(receiptEmail) || ''
				};
			}

			// Handle card payment
			this._stripe.handleCardPayment(
				secret, this._cardElement, Object.assign(
					receiptObject,
					this.getBillingData()
				)
			).then(function(result) {
				if (result.error) {
					self.show_error(result.error.message);
				} else {
					// Capture Payment Intent object
					self._paymentIntent = result;

					// Success, submit the form
					if (self._beforeSubmitCallback) {
						self._beforeSubmitCallback.call();
					}
				}
			});
		},

		mountCardField: function () {
			var key = this.getStripeData('key');
			var cardIcon = this.getStripeData('cardIcon');
			var verifyZip = this.getStripeData('veifyZip');
			var zipField = this.getStripeData('zipField');
			var fieldId = this.getStripeData('fieldId');

			// Init Stripe
			this._stripe = Stripe( key, {
				locale: this.getStripeData('language')
			} );

			// Create empty ZIP object
			var zipObject = {}

			if (!verifyZip) {
				// If verify ZIP is disabled, disable ZIP
				zipObject.hidePostalCode = true;
			} else {
				// Set empty post code, later will be updated when field is changed
				zipObject.value = {
					postalCode: '',
				};
			}

			var stripeObject = {};
			var fontFamily = this.getStripeData('fontFamily');
			var customFonts = this.getStripeData('customFonts');
			if (fontFamily && customFonts) {
				stripeObject.fonts = [
					{
						cssSrc: 'https://fonts.googleapis.com/css?family=' + fontFamily,
					}
				];
			}

			var elements = this._stripe.elements(stripeObject);

			this._cardElement = elements.create('card', Object.assign(
				{
					classes: {
						base: this.getStripeData('baseClass'),
						complete: this.getStripeData('completeClass'),
						empty: this.getStripeData('emptyClass'),
						focus: this.getStripeData('focusedClass'),
						invalid: this.getStripeData('invalidClass'),
						webkitAutofill: this.getStripeData('autofilledClass'),
					},
					style: {
						base: {
							iconColor: this.getStripeData( 'iconColor' ),
							color: this.getStripeData( 'fontColor' ),
							lineHeight: this.getStripeData( 'lineHeight' ),
							fontWeight: this.getStripeData( 'fontWeight' ),
							fontFamily: this.getStripeData( 'fontFamily' ),
							fontSmoothing: 'antialiased',
							fontSize: this.getStripeData( 'fontSize' ),
							'::placeholder': {
								color: this.getStripeData( 'placeholder' ),
							},
							':hover': {
								iconColor: this.getStripeData( 'iconColorHover' ),
							}
						},
						invalid: {
							iconColor: this.getStripeData( 'iconColorError' ),
							color: this.getStripeData( 'fontColorError' ),
						},
					},
					iconStyle: 'solid',
					hideIcon: !cardIcon,
				},
				zipObject
			));
			this._cardElement.mount('#card-element-' + fieldId);
			this.validateCard();
		},

		validateCard: function () {
			var self = this;
			this._cardElement.on( 'change', function( event ) {
				if ( self.$el.find( '.forminator-stripe-element' ).hasClass( 'StripeElement--empty' ) ) {
					self.$el.find( '.forminator-stripe-element' ).closest( '.forminator-field' ).removeClass( 'forminator-is_filled' );
				} else {
					self.$el.find( '.forminator-stripe-element' ).closest( '.forminator-field' ).addClass( 'forminator-is_filled' );
				}

				if ( self.$el.find( '.forminator-stripe-element' ).hasClass( 'StripeElement--invalid' ) ) {
					self.$el.find( '.forminator-stripe-element' ).closest( '.forminator-field' ).addClass( 'forminator-has_error' );
				}
			});

			this._cardElement.on('focus', function(event) {
				self.$el.find('.forminator-stripe-element').closest('.forminator-field').addClass('forminator-is_active');
			});

			this._cardElement.on('blur', function(event) {
				self.$el.find('.forminator-stripe-element').closest('.forminator-field').removeClass('forminator-is_active');

				self.isValid(false);
			});
		},

		hideCardError: function () {
			var $field_holder = this.$el.find('.forminator-card-message');
			var $error_holder = $field_holder.find('.forminator-error-message');

			if ($error_holder.length === 0) {
				$field_holder.append('<span class="forminator-error-message" aria-hidden="true"></span>');
				$error_holder = $field_holder.find('.forminator-error-message');
			}

			$field_holder.closest('.forminator-field').removeClass('forminator-has_error');
			$error_holder.html('');
		},

		showCardError: function (message, focus) {
			var $field_holder = this.$el.find('.forminator-card-message');
			var $error_holder = $field_holder.find('.forminator-error-message');

			if ($error_holder.length === 0) {
				$field_holder.append('<span class="forminator-error-message" aria-hidden="true"></span>');
				$error_holder = $field_holder.find('.forminator-error-message');
			}

			$field_holder.closest('.forminator-field').addClass('forminator-has_error');
			$field_holder.closest('.forminator-field').addClass( 'forminator-is_filled' );
			$error_holder.html(message);

			if(focus) {
				this.focus_to_element($field_holder.closest('.forminator-field'));
			}
		},

		getStripeData: function (key) {
			if (typeof this._stripeData[key] !== 'undefined') {
				return this._stripeData[key];
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

		get_field_value: function (element_id) {
			var $element = this.get_form_field(element_id);
			var value    = '';
			var checked  = null;

			if (this.field_is_radio($element)) {
				checked = $element.filter(":checked");
				if (checked.length) {
					value = checked.val();
				}
			} else if (this.field_is_checkbox($element)) {
				$element.each(function () {
					if ($(this).is(':checked')) {
						value = $(this).val();
					}
				});

			} else if (this.field_is_select($element)) {
				value = $element.val();
			} else {
				value = $element.val()
			}

			return value;
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
	});

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function (options) {
		return this.each(function () {
			if (!$.data(this, pluginName)) {
				$.data(this, pluginName, new ForminatorFrontPayment(this, options));
			}
		});
	};

})(jQuery, window, document);
