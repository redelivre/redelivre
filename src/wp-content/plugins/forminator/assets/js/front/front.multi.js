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
	var pluginName = "forminatorFront",
	    defaults   = {
		    form_type: 'custom-form',
		    rules: {},
		    messages: {},
		    conditions: {},
		    inline_validation: false,
		    chart_design: 'bar',
		    chart_options: {},
		    forminator_fields: [],
		    max_nested_formula: 5,
		    general_messages: {
			    calculation_error: 'Failed to calculate field.',
			    payment_require_ssl_error: 'SSL required to submit this form, please check your URL.',
				payment_require_amount_error: 'PayPal amount must be greater than 0.'
		    },
		    payment_require_ssl : false,
	    };

	// The actual plugin constructor
	function ForminatorFront(element, options) {
		this.element                    = element;
		this.$el                        = $(this.element);
		this.forminator_selector        = '#' + $(this.element).attr('id') + '[data-forminator-render="' + $(this.element).data('forminator-render') + '"]';
		this.forminator_loader_selector = 'div[data-forminator-render="' + $(this.element).data('forminator-render') + '"]' + '[data-form="' + $(this.element).attr('id') + '"]';

		// jQuery has an extend method which merges the contents of two or
		// more objects, storing the result in the first object. The first object
		// is generally empty as we don't want to alter the default options for
		// future instances of the plugin
		this.settings = $.extend({}, defaults, options);

		// special treatment for rules, messages, and conditions
		if (typeof this.settings.messages !== 'undefined') {
			this.settings.messages = this.maybeParseStringToJson(this.settings.messages, 'object');
		}
		if (typeof this.settings.rules !== 'undefined') {
			this.settings.rules = this.maybeParseStringToJson(this.settings.rules, 'object');
		}
		if (typeof this.settings.calendar !== 'undefined') {
			this.settings.calendar = this.maybeParseStringToJson(this.settings.calendar, 'array');
		}

		this._defaults = defaults;
		this._name     = pluginName;
		this.init();
	}

	// Avoid Plugin.prototype conflicts
	$.extend(ForminatorFront.prototype, {
		init: function () {
			var self = this;

			$(this.forminator_loader_selector).remove();

			// If form from hustle popup, do not show
			if (this.$el.closest('.wph-modal').length === 0) {
				this.$el.show();
			}

			// Show form when popup trigger with click
			$(document).on("hustle:module:displayed", function (e, data) {
				var $modal = $('.wph-modal-active');
				$modal.find('form').css('display', '');
			});

			// Show form when popup trigger
			setTimeout(function () {
				var $modal = $('.wph-modal-active');
				$modal.find('form').css('display', '');
			}, 10);

			//selective activation based on type of form
			switch (this.settings.form_type) {
				case  'custom-form':
					this.init_custom_form();
					break;
				case  'poll':
					this.init_poll_form();
					break;
				case  'quiz':
					this.init_quiz_form();
					break;

			}

			//init submit
			$(this.element).forminatorFrontSubmit({
				form_type: self.settings.form_type,
				forminator_selector: self.forminator_selector,
				chart_design: self.settings.chart_design,
				chart_options: self.settings.chart_options,
				fadeout: self.settings.fadeout,
				fadeout_time: self.settings.fadeout_time,
				has_loader: self.settings.has_loader,
				loader_label: self.settings.loader_label,
				resetEnabled: self.settings.is_reset_enabled,
			});


			// TODO: confirm usage on form type
			// Handle field activation classes
			this.activate_field();
			// Handle special classes for material design
			// this.material_field();

			// Init small form for all type of form
			this.small_form();

		},
		init_custom_form: function () {

			var self = this;

			//initiate validator

			this.init_intlTelInput_validation();

			if (this.settings.inline_validation) {

				$(this.element).forminatorFrontValidate({
					rules: self.settings.rules,
					messages: self.settings.messages
				});
			}

			// initiate calculator
			$(this.element).forminatorFrontCalculate({
				forminatorFields: self.settings.forminator_fields,
				maxExpand: self.settings.max_nested_formula,
				generalMessages: self.settings.general_messages,
				memoizeTime: self.settings.calcs_memoize_time || 300,
			});

			// initiate merge tags
			$(this.element).forminatorFrontMergeTags({
				forminatorFields: self.settings.forminator_fields,
			});

			//initiate pagination
			this.init_pagination();

			// initiate payment if exist
			var first_payment = $(this.element).find('div[data-is-payment="true"], input[data-is-payment="true"]').first();

			if (first_payment.length) {
				var payment_type = first_payment.data('paymentType');
				if (payment_type === 'stripe') {
					$(this.element).forminatorFrontPayment({
						type: payment_type,
						paymentEl: first_payment,
						paymentRequireSsl: self.settings.payment_require_ssl,
						generalMessages: self.settings.general_messages,
						fadeout_time: self.settings.fadeout_time,
						has_loader: self.settings.has_loader,
						loader_label: self.settings.loader_label,
					});
				}
				if (payment_type === 'paypal') {
					$(this.element).forminatorFrontPayPal({
						type: payment_type,
						paymentEl: this.settings.paypal_config,
						paymentRequireSsl: self.settings.payment_require_ssl,
						generalMessages: self.settings.general_messages,
						has_loader: self.settings.has_loader,
						loader_label: self.settings.loader_label,
					});
				}
			}

			//initiate condition
			$(this.element).forminatorFrontCondition(this.settings.conditions, this.settings.calendar);

			//initiate forminator ui scripts
			this.init_fui();

			//initiate datepicker
			$(this.element).find('.forminator-datepicker').forminatorFrontDatePicker(this.settings.calendar);

			// Handle responsive captcha
			this.responsive_captcha();

			// Handle field counter
			this.field_counter();

			// Handle number input
			this.field_number();

			// Handle time fields
			this.field_time();

			// Handle upload field change
			this.upload_field();

			// Handle function on resize
			$(window).on('resize', function () {

				self.responsive_captcha();

			});

		},
		init_poll_form: function() {

			var self       = this,
				$fieldset  = this.$el.find( 'fieldset' ),
				$selection = this.$el.find( '.forminator-radio input' ),
				$input     = this.$el.find( '.forminator-input' ),
				$field     = $input.closest( '.forminator-field' )
				;

			// Load input states
			FUI.inputStates( $input );

			// Show input when option has been selected
			$selection.on( 'click', function() {

				// Reset
				$field.addClass( 'forminator-hidden' );
				$field.attr( 'aria-hidden', 'true' );
				$input.removeAttr( 'tabindex' );
				$input.attr( 'name', '' );

				var checked = this.checked,
					$id     = $( this ).attr( 'id' ),
					$name   = $( this ).attr( 'name' )
					;

				// Once an option has been chosen, remove error class.
				$fieldset.removeClass( 'forminator-has_error' );

				if ( self.$el.find( '.forminator-input#' + $id + '-extra' ).length ) {

					var $extra = self.$el.find( '.forminator-input#' + $id + '-extra' ),
						$extraField = $extra.closest( '.forminator-field' )
						;

					if ( checked ) {

						$extra.attr( 'name', $name + '-extra' );

						$extraField.removeClass( 'forminator-hidden' );
						$extraField.removeAttr( 'aria-hidden' );

						$extra.attr( 'tabindex', '-1' );
						$extra.focus();

					} else {

						$extraField.addClass( 'forminator-hidden' );
						$extraField.attr( 'aria-hidden', 'true' );

						$extra.removeAttr( 'tabindex' );

					}
				}

				return true;

			});

			// Disable options
			if ( this.$el.hasClass( 'forminator-poll-disabled' ) ) {

				this.$el.find( '.forminator-radio' ).each( function() {

					$( this ).addClass( 'forminator-disabled' );
					$( this ).find( 'input' ).attr( 'disabled', true );

				});
			}
		},

		init_quiz_form: function () {
			var self = this;

			this.$el.find('.forminator-button').each(function () {
				$(this).prop("disabled", true);
			});

			this.$el.find('.forminator-answer input').each(function () {
				$(this).attr('checked', false);
			});

			this.$el.find('.forminator-result--info button').on('click', function () {
				location.reload();
			});

			this.$el.find('.forminator-submit-rightaway').click(function () {
				self.$el.submit();
				$(this).closest('.forminator-question').find('.forminator-submit-rightaway').addClass('forminator-has-been-disabled').attr('disabled', 'disabled');
			});

			this.$el.on('click', '.forminator-social--icon a', function (e) {
				e.preventDefault();
				var social        = $(this).data('social'),
				    url       = $(this).closest('.forminator-social--icons').data('url'),
				    message       = $(this).closest('.forminator-social--icons').data('message'),
				    message       = encodeURIComponent(message),
					social_shares = {
						'facebook': 'https://www.facebook.com/sharer/sharer.php?u=' + window.location.href + '&t=' + message,
						'twitter': 'https://twitter.com/intent/tweet?&url=' + url + '&text=' + message,
						'google': 'https://plus.google.com/share?url=' + window.location.href,
						'linkedin': 'https://www.linkedin.com/shareArticle?mini=true&url=' + window.location.href + '&title=' + message
					};

				if (social_shares[social] !== undefined) {
					var newwindow = window.open(social_shares[social], social, 'height=' + $(window).height() + ',width=' + $(window).width());
					if (window.focus) {
						newwindow.focus();
					}
					return false;
				}
			});

			this.$el.on('change', '.forminator-answer input', function (e) {
				var count          = 0,
				    amount_answers = self.$el.find('.forminator-question').length;

				self.$el.find('.forminator-answer input').each(function () {
					if ($(this).prop('checked')) {
						count++;
					}

					if (count === amount_answers) {
						self.$el.find('.forminator-button').each(function () {
							$(this).prop("disabled", false);
						});
					}
				});

			});

		},

		small_form: function () {

			var form      = $( this.element ),
				formWidth = form.width()
				;

			if ( 783 < Math.max( document.documentElement.clientWidth, window.innerWidth || 0 ) ) {

				if ( form.hasClass( 'forminator-size--small' ) ) {

					if ( 500 < formWidth ) {
						form.removeClass( 'forminator-size--small' );
					}
				} else {
					var hasHustle = form.closest('.hustle-content');

					if ( 500 >= formWidth && ! hasHustle.length ) {
						form.addClass( 'forminator-size--small' );
					}
				}
			}
		},

		init_intlTelInput_validation: function () {

			var form        = $(this.element),
			    is_material = form.is('.forminator-design--material'),
			    fields      = form.find('.forminator-field--phone');

			fields.each(function () {

				// Initialize intlTelInput plugin on each field with "format check" enabled and
				// set to check either "international" or "standard" phones.
				var is_national_phone = $(this).data('national_mode'),
				    country           = $(this).data('country');

				if ('undefined' !== typeof (is_national_phone)) {

					if (is_material) {
						//$(this).unwrap('.forminator-input--wrap');
					}

					var args = {
						nationalMode: ('enabled' === is_national_phone) ? true : false,
						initialCountry: 'us',
						utilsScript: window.ForminatorFront.cform.intlTelInput_utils_script,
					};

					if ('undefined' !== typeof (country)) {
						args.initialCountry = country;
						args.allowDropdown  = false;
					}

					$(this).intlTelInput(args);

					if ( ! is_material ) {
						$(this).closest( '.forminator-field' ).find( 'div.intl-tel-input' ).addClass( 'forminator-phone' );
					} else {
						$(this).closest( '.forminator-field' ).find( 'div.intl-tel-input' ).addClass( 'forminator-input-with-phone' );
					}

					// intlTelInput plugin adds a markup that's not compatible with 'material' theme when 'allowDropdown' is true (default).
					// If we're going to allow users to disable the dropdown, this should be adjusted accordingly.
					if (is_material) {
						//$(this).closest('.intl-tel-input.allow-dropdown').addClass('forminator-phone-intl').removeClass('intl-tel-input');
						//$(this).wrap('<div class="forminator-input--wrap"></div>');
					}
				}
			});

		},

		init_fui: function () {

			var form        = $( this.element ),
				input       = form.find( '.forminator-input' ),
				textarea    = form.find( '.forminator-textarea' ),
				select      = form.find( '.forminator-select' ),
				select2     = form.find( '.forminator-select2' ),
				multiselect = form.find( '.forminator-multiselect' ),
				stripe		= form.find( '.forminator-stripe-element' )
			;

			if ( input.length ) {
				input.each( function() {
					FUI.inputStates( this );
				});
			}

			if ( textarea.length ) {
				textarea.each( function() {
					FUI.textareaStates( this );
				});
			}

			if ( select2.length ) {
				FUI.select2();
			}

			if ( select.length ) {
				select.each( function() {
					FUI.select( this );
				});
			}

			if ( multiselect.length ) {
				FUI.multiSelectStates( multiselect );
			}

			if ( form.hasClass( 'forminator-design--material' ) ) {
				if ( input.length ) {
					input.each( function() {
						FUI.inputMaterial( this );
					});
				}

				if ( textarea.length ) {
					textarea.each( function() {
						FUI.textareaMaterial( this );
					});
				}

				if ( stripe.length ) {
					stripe.each( function() {
						var field = $(this).closest('.forminator-field');
						var label = field.find('.forminator-label');

						if (label.length) {
							field.addClass('forminator-stripe-floating');
							// Add floating class
							label.addClass('forminator-floating--input');
						}
					});
				}
			}
		},

		responsive_captcha: function () {
			$(this.element).find('.forminator-g-recaptcha').each(function () {
				if ($(this).is(':visible')) {
					var width = $(this).parent().width(),
					    scale = 1;
					if (width < 302) {
						scale = width / 302;
					}
					$(this).css('transform', 'scale(' + scale + ')');
					$(this).css('-webkit-transform', 'scale(' + scale + ')');
					$(this).css('transform-origin', '0 0');
					$(this).css('-webkit-transform-origin', '0 0');
				}
			});
		},

		init_pagination: function () {
			var self      = this,
			    num_pages = $(this.element).find(".forminator-pagination").length,
			    hash      = window.location.hash,
			    hashStep  = false,
			    step      = 0;

			if (num_pages > 0) {
				//find from hash
				if (typeof hash !== "undefined" && hash.indexOf('step-') >= 0) {
					hashStep = true;
					step     = hash.substr(6, 8);
				}

				$(this.element).forminatorFrontPagination({
					totalSteps: num_pages,
					hashStep: hashStep,
					step: step,
					inline_validation: self.settings.inline_validation
				});
			}
		},

		activate_field: function () {

			var form     = $( this.element );
			var input    = form.find( '.forminator-input' );
			var textarea = form.find( '.forminator-textarea' );

			function classFilled( el ) {

				var element       = $( el );
				var elementValue  = element.val().trim();
				var elementField  = element.closest( '.forminator-field' );
				var elementAnswer = element.closest( '.forminator-poll--answer' );

				var filledClass = 'forminator-is_filled';

				if ( '' !== elementValue ) {
					elementField.addClass( filledClass );
					elementAnswer.addClass( filledClass );
				} else {
					elementField.removeClass( filledClass );
					elementAnswer.removeClass( filledClass );
				}

				element.change( function( e ) {

					if ( '' !== elementValue ) {
						elementField.addClass( filledClass );
						elementAnswer.addClass( filledClass );
					} else {
						elementField.removeClass( filledClass );
						elementAnswer.removeClass( filledClass );
					}

					e.stopPropagation();

				});
			};

			function classHover( el ) {

				var element       = $( el );
				var elementField  = element.closest( '.forminator-field' );
				var elementAnswer = element.closest( '.forminator-poll--answer' );

				var hoverClass = 'forminator-is_hover';

				element.mouseover( function( e ) {
					elementField.addClass( hoverClass );
					elementAnswer.addClass( hoverClass );
					e.stopPropagation();
				}).mouseout( function( e ) {
					elementField.removeClass( hoverClass );
					elementAnswer.removeClass( hoverClass );
					e.stopPropagation();
				});
			};

			function classActive( el ) {

				var element       = $( el );
				var elementField  = element.closest( '.forminator-field' );
				var elementAnswer = element.closest( '.forminator-poll--answer' );

				var activeClass = 'forminator-is_active';

				element.focus( function( e ) {
					elementField.addClass( activeClass );
					elementAnswer.addClass( activeClass );
					e.stopPropagation();
				}).blur( function( e ) {
					elementField.removeClass( activeClass );
					elementAnswer.removeClass( activeClass );
					e.stopPropagation();
				});
			};

			function classError( el ) {

				var element       = $( el );
				var elementValue  = element.val().trim();
				var elementField  = element.closest( '.forminator-field' );
				var elementTime   = element.attr( 'data-field' );

				var timePicker = element.closest( '.forminator-timepicker' );
				var timeColumn = timePicker.parent();

				var errorField = elementField.find( '.forminator-error-message' );

				var errorClass = 'forminator-has_error';

				element.on( 'load change keyup keydown', function( e ) {

					if ( undefined !== typeof elementTime && false !== elementTime ) {

						if ( 'hours' === element.data( 'field' ) ) {

							var hoursError = timeColumn.find( '.forminator-error-message[data-error-field="hours"]' );

							if ( '' !== elementValue && 0 !== hoursError.length ) {
								hoursError.remove();
							}
						}

						if ( 'minutes' === element.data( 'field' ) ) {

							var minutesError = timeColumn.find( '.forminator-error-message[data-error-field="minutes"]' );

							if ( '' !== elementValue && 0 !== minutesError.length ) {
								minutesError.remove();
							}
						}
					} else {

						if ( '' !== elementValue && errorField.text() ) {
							errorField.remove();
							elementField.removeClass( errorClass );
						}
					}

					e.stopPropagation();

				});
			};

			if ( input.length ) {

				input.each( function() {
					//classFilled( this );
					//classHover( this );
					//classActive( this );
					classError( this );
				});
			}

			if ( textarea.length ) {

				textarea.each( function() {
					//classFilled( this );
					//classHover( this );
					//classActive( this );
					classError( this );
				});
			}

			form.find('.forminator-select + .select2, .forminator-time + .select2').each(function () {

				var $select = $(this);

				// Set field active class on hover
				$select.mouseover(function (e) {
					e.stopPropagation();
					$(this).closest('.forminator-field').addClass('forminator-is_hover');

				}).mouseout(function (e) {
					e.stopPropagation();
					$(this).closest('.forminator-field').removeClass('forminator-is_hover');

				});

				// Set field active class on focus
				$select.on('click', function (e) {
					e.stopPropagation();
					checkSelectActive();
					if ($select.hasClass('select2-container--open')) {
						$(this).closest('.forminator-field').addClass('forminator-is_active');
					} else {
						$(this).closest('.forminator-field').removeClass('forminator-is_active');
					}

				});


			});

			function checkSelectActive() {
				if (form.find('.select2-container').hasClass('select2-container--open')) {
					setTimeout(checkSelectActive, 300);
				} else {
					form.find('.select2-container').closest('.forminator-field').removeClass('forminator-is_active');
				}
			}
		},

		field_counter: function () {
			var form = $(this.element);
			form.find('.forminator-input, .forminator-textarea').each(function () {
				var $input = $(this),
				    count  = 0;

				$input.on('change keyup', function (e) {
					e.stopPropagation();
					var $field = $(this).closest('.forminator-col'),
					    $limit = $field.find('.forminator-description span')
					;

					if ($limit.length) {
						if ($limit.data('limit')) {
							if ($limit.data('type') !== "words") {
								count = $(this).val().trim().length;
							} else {
								count = $(this).val().trim().split(/\s+/).length;
							}
							$limit.html(count + ' / ' + $limit.data('limit'));
						}
					}
				});

			});
		},

		field_number: function () {
			// var form = $(this.element);
			// form.find('input[type=number]').on('change keyup', function () {
			// 	if( ! $(this).val().match(/^\d+$/) ){
			// 		var sanitized = $(this).val().replace(/[^0-9]/g, '');
			// 		$(this).val(sanitized);
			// 	}
			// });
			var form = $(this.element);
			form.find('input[type=number]').each(function () {
				$(this).keypress(function (e) {
					var i;
					var allowed = [44, 45, 46];
					var key     = e.which;

					for (i = 48; i < 58; i++) {
						allowed.push(i);
					}

					if (!(allowed.indexOf(key) >= 0)) {
						e.preventDefault();
					}
				});
			});

			form.find('.forminator-currency').each(function () {
				var decimals = $(this).data('decimals');
				$(this).change(function (e) {
				    this.value = parseFloat(this.value).toFixed(decimals);
				});
			});
		},

		field_time: function () {
			$('.forminator-input-time').on('input', function (e) {
				var $this = $(this),
				    value = $this.val()
				;

				// Allow only 2 digits for time fields
				if (value && value.length >= 2) {
					$this.val(value.substr(0, 2));
				}
			});
		},

		material_field: function () {
			/*
			var form = $(this.element);
			if (form.is('.forminator-design--material')) {
				var $input    = form.find('.forminator-input--wrap'),
				    $textarea = form.find('.forminator-textarea--wrap'),
				    $date     = form.find('.forminator-date'),
				    $product  = form.find('.forminator-product');

				var $navigation = form.find('.forminator-pagination--nav'),
				    $navitem    = $navigation.find('li');

				$('<span class="forminator-nav-border"></span>').insertAfter($navitem);

				$input.prev('.forminator-field--label').addClass('forminator-floating--input');
				$input.closest('.forminator-phone-intl').prev('.forminator-field--label').addClass('forminator-floating--input');
				$textarea.prev('.forminator-field--label').addClass('forminator-floating--textarea');

				if ($date.hasClass('forminator-has_icon')) {
					$date.prev('.forminator-field--label').addClass('forminator-floating--date');
				} else {
					$date.prev('.forminator-field--label').addClass('forminator-floating--input');
				}
			}
			*/
		},

		toggle_file_input: function() {

			var $form = $( this.element );

			$form.find( '.forminator-file-upload' ).each( function() {

				var $field = $( this );
				var $input = $field.find( 'input' );
				var $remove = $field.find( '.forminator-button-delete' );

				// Toggle remove button depend on input value
				if ( '' !== $input.val() ) {
					$remove.show(); // Show remove button
				} else {
					$remove.hide(); // Hide remove button
				}
			});
		},

		upload_field: function () {

			var self = this,
			    form = $(this.element)
			;

			// Toggle file remove button
			this.toggle_file_input();

			// Handle remove file button click
			form.find( '.forminator-button-delete' ).on('click', function (e) {

				e.preventDefault();

				var $self  = $(this),
				    $input = $self.siblings('input'),
				    $label = $self.closest( '.forminator-file-upload' ).find('> span')
					;

				// Cleanup
				$input.val('');
				$label.html( $label.data( 'empty-text' ) );
				$self.hide();

			});

			form.find( '.forminator-button-upload' ).on( 'click', function (e) {

				e.preventDefault();

				var $id        = $(this).attr('data-id'),
				    $target    = form.find('input#' + $id),
				    $nameLabel = $(this).closest( '.forminator-file-upload' ).find( '> span' )
					;

				$target.trigger('click');

				$target.change(function () {

					var vals = $(this).val(),
					    val  = vals.length ? vals.split('\\').pop() : ''
					;

					$nameLabel.text(val);

					self.toggle_file_input();

				});
			});

			form.find( '.forminator-input-file' ).on('change', function (e) {

				e.preventDefault();

				var $file   = $(this)[0].files.length,
				    $remove = $(this).find('.forminator-button-delete');

				if ($file === 0) {
					$remove.hide();
				} else {
					$remove.show();
				}

			});
		},

		renderCaptcha: function (captcha_field) {
			var self = this;
			//render captcha only if not rendered
			if (typeof $(captcha_field).data('forminator-recapchta-widget') === 'undefined') {
				var size = $(captcha_field).data('size'),
				    data = {
					    sitekey: $(captcha_field).data('sitekey'),
					    theme: $(captcha_field).data('theme'),
					    size: size
				    };

				if (size === 'invisible') {
					data.badge    = 'inline';
					data.callback = function (token) {
						$(self.element).trigger('submit.frontSubmit');
					};
				}

				if (data.sitekey !== "") {
					// noinspection Annotator
					var widget = window.grecaptcha.render(captcha_field, data);
					// mark as rendered
					$(captcha_field).data('forminator-recapchta-widget', widget);
					this.responsive_captcha();
				}
			}
		},

		hide: function () {
			this.$el.hide();
		},
		/**
		 * Return JSON object if possible
		 *
		 * We tried our best here
		 * if there is an error/exception, it will return empty object/array
		 *
		 * @param string
		 * @param type ('array'/'object')
		 */
		maybeParseStringToJson: function (string, type) {
			var object = {};
			// already object
			if (typeof string === 'object') {
				return string;
			}

			if (type === 'object') {
				string = '{' + string.trim() + '}';
			} else if (type === 'array') {
				string = '[' + string.trim() + ']';
			} else {
				return {};
			}

			try {
				// remove trailing comma, duh
				/**
				 * find `,`, after which there is no any new attribute, object or array.
				 * New attribute could start either with quotes (" or ') or with any word-character (\w).
				 * New object could start only with character {.
				 * New array could start only with character [.
				 * New attribute, object or array could be placed after a bunch of space-like symbols (\s).
				 *
				 * Feel free to hack this regex if you got better idea
				 * @type {RegExp}
				 */
				var trailingCommaRegex = /\,(?!\s*?[\{\[\"\'\w])/g;
				string                 = string.replace(trailingCommaRegex, '');

				object = JSON.parse(string);
			} catch (e) {
				console.error(e.message);
				if (type === 'object') {
					object = {};
				} else if (type === 'array') {
					object = [];
				}
			}

			return object;

		},

	});

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function (options) {
		return this.each(function () {
			if (!$.data(this, pluginName)) {
				$.data(this, pluginName, new ForminatorFront(this, options));
			}
		});
	};

	// hook from wp_editor tinymce
	$(document).on('tinymce-editor-init', function (event, editor) {
		// trigger editor change to save value to textarea,
		// default wp tinymce textarea update only triggered when submit
		var count  = 0;
		editor.on('change', function () {
			// only forminator
			if (editor.id.indexOf('forminator-wp-editor-') === 0) {
				editor.save();
			}
			var editor_id = editor.id,
				$field = $('#' + editor_id ).closest('.forminator-col'),
				$limit = $field.find('.forminator-description span')
			;
			if ($limit.length) {
				if ($limit.data('limit')) {
					if ($limit.data('type') !== "words") {
						count = editor.getContent({ format: 'text' }).length;
					} else {
						count = editor.getContent({ format: 'text' }).split(/\s+/).length;
					}
					$limit.html(count + ' / ' + $limit.data('limit'));
				}
			}


		});
	});

})(jQuery, window, document);

// noinspection JSUnusedGlobalSymbols
var forminator_render_captcha = function () {
	// TODO: avoid conflict with another plugins that provide recaptcha
	//  notify forminator front that grecaptcha loaded. anc can be used
	jQuery('.forminator-g-recaptcha').each(function () {
		// find closest form
		var form = jQuery(this).closest('form');
		if (form.length > 0) {
			var forminatorFront = form.data('forminatorFront');
			if (typeof forminatorFront !== 'undefined') {
				forminatorFront.renderCaptcha(jQuery(this)[0]);
			}
		}
	});
};
