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
	var pluginName = "forminatorFrontPagination",
		defaults = {
			totalSteps: 0,
			step: 0,
			hashStep: 0,
			inline_validation: false
		};

	// The actual plugin constructor
	function ForminatorFrontPagination(element, options) {
		this.element = $(element);
		this.$el = this.element;
		this.totalSteps = 0;
		this.step = 0;
		this.hashStep = false;
		this.next_button = window.ForminatorFront.cform.pagination_next;
		this.prev_button = window.ForminatorFront.cform.pagination_prev;
		this.next_button_txt = '';
		this.prev_button_txt = '';
		this.custom_label = [];
		this.form_id = 0;
		this.element = '';

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
	$.extend(ForminatorFrontPagination.prototype, {
		init: function () {

			if (this.$el.find('input[name=form_id]').length > 0) {
				this.form_id = this.$el.find('input[name=form_id]').val();
			}

			this.totalSteps = this.settings.totalSteps;
			this.step = this.settings.step;
			this.element = this.$el.find('[data-step=' + this.step + ']').data('name');
			if (this.form_id && typeof window.Forminator_Cform_Paginations === 'object' && typeof window.Forminator_Cform_Paginations[this.form_id] === 'object') {
				this.custom_label = window.Forminator_Cform_Paginations[this.form_id];
			}
			if (this.settings.hashStep && this.step > 0) {
				this.go_to(this.step, true);
			} else {
				this.go_to(0, false);
			}

			this.render_navigation();
			this.render_bar_navigation();
			this.render_footer_navigation( this.form_id );
			this.init_events();
			this.update_buttons();
			this.update_navigation();

		},
		init_events: function () {
			var self = this;

			this.$el.find('.forminator-button-back').click(function (e) {
				e.preventDefault();
				self.handle_click('prev');
			});
			this.$el.find('.forminator-button-next').click(function (e) {
				e.preventDefault();
				self.handle_click('next');
			});

			this.$el.on('reset', function (e) {
				self.on_form_reset(e);
			});

			this.$el.on('forminator.front.pagination.focus.input', function (e, input) {
				self.on_focus_input(e, input);
			});

		},

		/**
		 * On reset event of Form
		 *
		 * @since 1.0.3
		 *
		 * @param e
		 */
		on_form_reset: function (e) {
			// Trigger pagination to first page
			this.go_to(0, true);
			this.update_buttons();
		},

		/**
		 * On Input focused
		 *
		 * @param e
		 * @param input
		 */
		on_focus_input: function (e, input) {
			//Go to page where element exist
			var step = this.get_page_of_input(input);
			this.go_to(step, true);
			this.update_buttons();
		},

		render_footer_navigation: function( form_id ) {
			var footer_html = '',
				paypal_field = '';
			if ( this.custom_label[ this.element ] && this.custom_label[ 'pagination-labels' ] === 'custom' ){
				this.prev_button_txt = this.custom_label[ this.element ][ 'prev-text' ] !== '' ? this.custom_label[ this.element ][ 'prev-text' ] : this.prev_button;
				this.next_button_txt = this.custom_label[ this.element ][ 'next-text' ] !== '' ? this.custom_label[ this.element ][ 'next-text' ] : this.next_button;
			} else {
				this.prev_button_txt = this.prev_button;
				this.next_button_txt = this.next_button;
			}

			if ( this.$el.hasClass('forminator-design--material') ) {
				footer_html = '<div class="forminator-pagination-footer">' +
					'<button class="forminator-button forminator-button-back"><span class="forminator-button--mask" aria-label="hidden"></span><span class="forminator-button--text">' + this.prev_button_txt + '</span></button>' +
					'<button class="forminator-button forminator-button-next"><span class="forminator-button--mask" aria-label="hidden"></span><span class="forminator-button--text">' + this.next_button_txt + '</span></button>';
				if( this.custom_label[ 'has-paypal' ] === true ) {
					paypal_field = ( this.custom_label['paypal-id'] ) ? this.custom_label['paypal-id'] : '';
					footer_html += '<div class="forminator-payment forminator-button-paypal forminator-hidden ' + paypal_field + '-payment" id="paypal-button-container-' + form_id + '">';
				}
				footer_html += '</div>';
				this.$el.append( footer_html );

			} else {
				footer_html = '<div class="forminator-pagination-footer">' +
					'<button class="forminator-button forminator-button-back">' + this.prev_button_txt + '</button>' +
					'<button class="forminator-button forminator-button-next">' + this.next_button_txt + '</button>';
				if( this.custom_label['has-paypal'] === true ) {
					paypal_field = ( this.custom_label['paypal-id'] ) ? this.custom_label['paypal-id'] : '';
					footer_html += '<div class="forminator-payment forminator-button-paypal forminator-hidden ' + paypal_field + '-payment" id="paypal-button-container-' + form_id + '">';
				}
				footer_html += '</div>';
				this.$el.append( footer_html );

			}

		},

		render_bar_navigation: function () {

			var $navigation = this.$el.find( '.forminator-pagination-progress' );

			var $progressLabel = '<div class="forminator-progress-label">0%</div>',
				$progressBar   = '<div class="forminator-progress-bar"><span style="width: 0%"></span></div>'
			;

			if ( ! $navigation.length ) return;

			$navigation.html( $progressLabel + $progressBar );

			this.calculate_bar_percentage();

		},

		calculate_bar_percentage: function () {

			var total     = this.totalSteps,
				current   = this.step + 1,
				$progress = this.$el
			;

			if ( ! $progress.length ) return;

			var percentage = Math.round( (current / total) * 100 );

			$progress.find( '.forminator-progress-label' ).html( percentage + '%' );
			$progress.find( '.forminator-progress-bar span' ).css( 'width', percentage + '%' );

		},

		render_navigation: function () {
			var $navigation = this.$el.find('.forminator-pagination-steps');

			var finalSteps = this.$el.find('.forminator-pagination-start');

			if ( ! $navigation.length ) return;

			var steps = this.$el.find( '.forminator-pagination' ).not( '.forminator-pagination-start' );

			$navigation.append( '<div class="forminator-break"></div>' );

			steps.each( function() {

				var $step      = $( this ),
					$stepLabel = $step.data( 'label' ),
					$stepNumb  = $step.data('step') - 1
				;

				var $stepMarkup = '<div class="forminator-step forminator-step-' + $stepNumb + '">' +
					'<span class="forminator-step-label">' + $stepLabel + '</span>' +
					'<span class="forminator-step-dot"></span>' +
					'</div>';

				var $stepBreak = '<div class="forminator-break"></div>';

				$navigation.append( $stepMarkup + $stepBreak );

			});

			finalSteps.each(function () {
				var $step = $(this),
					label = $step.data('label'),
					step = steps.length
				;

				var $stepMarkup = '<div class="forminator-step forminator-step-' + step + '">' +
					'<span class="forminator-step-label">' + label + '</span>' +
					'<span class="forminator-step-dot"></span>' +
					'</div>';

				var $stepBreak = '<div class="forminator-break"></div>';

				$navigation.append( $stepMarkup + $stepBreak );
			});
		},

		handle_click: function (type) {
			var self = this;
			if (type === "prev" && this.step !== 0) {
				this.go_to(this.step - 1, true);
				this.update_buttons();
			} else if (type === "next") {
				//do validation before next if inline validation enabled
				if (this.settings.inline_validation) {
					if (!this.is_step_inputs_valid()) {
						return;
					}
				}

				if(typeof this.$el.data().forminatorFrontPayment !== "undefined") {
					var payment = this.$el.data().forminatorFrontPayment,
						page = this.$el.find('[data-step=' + this.step + ']'),
						hasStripe = page.find(".forminator-stripe-element")
					;

					// Check if Stripe exists on current step
					if (hasStripe.length > 0) {
						payment._stripe.createToken(payment._cardElement).then(function (result) {
							if (result.error) {
								payment.showCardError(result.error.message, true);
							} else {
								payment.hideCardError();
								self.go_to(self.step + 1, true);
								self.update_buttons();
							}
						});
					} else {
						this.go_to(this.step + 1, true);
						this.update_buttons();
					}
				} else {
					this.go_to(this.step + 1, true);
					this.update_buttons();
				}
			}
		},

		/**
		 * Check current inputs on step is in valid state
		 */
		is_step_inputs_valid: function () {
			var valid = true,
				errors = 0,
				validator = this.$el.data('validator'),
				page = this.$el.find('[data-step=' + this.step + ']');

			//inline validation disabled
			if (typeof validator === 'undefined') {
				return true;
			}

			//get fields on current page
			page.find("input, select, textarea, [contenteditable]")
				.not(":submit, :reset, :image, :disabled")
				.not(':hidden:not(.forminator-wp-editor-required, .forminator-input-file-required)')
				.not('[gramm="true"]')
				.each(function (key, element) {
					valid = validator.element(element);
					if (!valid) {
						if (errors === 0) {
							// focus on first error
							element.focus();
						}
						errors++;
					}
				});

			return errors === 0;
		},

		/**
		 * Get page on the input
		 *
		 * @since 1.0.3
		 *
		 * @param input
		 * @returns {number|*}
		 */
		get_page_of_input: function(input) {
			var step_page = this.step;
			var page = $(input).closest('.forminator-pagination');
			if (page.length > 0) {
				var step = $(page).data('step');
				if (typeof step !== 'undefined') {
					step_page = +step;
				}
			}

			return step_page;
		},

		update_buttons: function () {
			if (this.step === 0) {
				this.$el.find('.forminator-button-back').closest( '.forminator-pagination-footer' ).css({
					'justify-content': 'flex-end'
				});
				this.$el.find('.forminator-button-back').addClass( 'forminator-hidden' );
			} else {
				this.$el.find('.forminator-button-back').closest( '.forminator-pagination-footer' ).css({
					'justify-content': ''
				});
				this.$el.find('.forminator-button-back').removeClass('forminator-hidden');
			}

			if (this.step === this.totalSteps) {
				//keep pagination content on last step before submit
				this.step--;
				this.$el.submit();
			}

			if ( this.step === ( this.totalSteps - 1 ) ) {

				var submit_button_text = this.$el.find('.forminator-pagination-submit').html(),
					last_button_txt = ( this.custom_label[ 'pagination-labels' ] === 'custom'
						&& this.custom_label['last-previous'] !== '' ) ? this.custom_label['last-previous'] : this.prev_button;

				if ( this.$el.hasClass('forminator-design--material') ) {

					this.$el.find('.forminator-button-back .forminator-button--text').html( last_button_txt );
					this.$el.find('.forminator-button-next')
						.removeClass('forminator-button-next')
						.attr('id', 'forminator-submit')
						.addClass('forminator-button-submit')
						.find('.forminator-button--text')
						.html('')
						.html(submit_button_text);
					if( this.custom_label['has-paypal'] === true ) {
						this.$el.find('.forminator-button-submit').addClass('forminator-hidden');
						this.$el.find('.forminator-payment')
							.attr('id', 'forminator-paypal-submit')
							.removeClass('forminator-hidden');
					}
				} else {
					this.$el.find('.forminator-button-back').html( last_button_txt );
					this.$el.find( '.forminator-button-next' )
						.removeClass( 'forminator-button-next' )
						.attr( 'id', 'forminator-submit' )
						.addClass( 'forminator-button-submit' )
						.html( submit_button_text );
					if( this.custom_label['has-paypal'] === true ) {
						this.$el.find('.forminator-button-submit').addClass('forminator-hidden');
						this.$el.find('.forminator-payment')
							.attr('id', 'forminator-paypal-submit')
							.removeClass('forminator-hidden');
					}
				}
			} else {
				this.element = this.$el.find('[data-step=' + this.step + ']').data('name');
				if ( this.custom_label[this.element] && this.custom_label['pagination-labels'] === 'custom'){
					this.prev_button_txt = this.custom_label[this.element]['prev-text'] !== '' ? this.custom_label[this.element]['prev-text'] : this.prev_button;
					this.next_button_txt = this.custom_label[this.element]['next-text'] !== '' ? this.custom_label[this.element]['next-text'] : this.next_button;
				}else{
					this.prev_button_txt = this.prev_button;
					this.next_button_txt = this.next_button;
				}
				if ( this.$el.hasClass('forminator-design--material') ) {
					this.$el.find( '#forminator-submit' )
						.removeAttr( 'id' )
						.removeClass( 'forminator-button-submit' )
						.addClass( 'forminator-button-next' );
					if( this.custom_label['has-paypal'] === true ) {
						this.$el.find( '#forminator-paypal-submit' ).removeAttr( 'id' ).addClass('forminator-hidden');
						this.$el.find('.forminator-button-next').removeClass('forminator-button-submit forminator-hidden');
					}

					this.$el.find( '.forminator-button-back .forminator-button--text' ).html( this.prev_button_txt );
					this.$el.find( '.forminator-button-next .forminator-button--text' ).html( this.next_button_txt );

				} else {
					this.$el.find( '#forminator-submit' )
						.removeAttr( 'id' )
						.removeClass( 'forminator-button-submit' )
						.addClass( 'forminator-button-next' );
					if( this.custom_label['has-paypal'] === true ) {
						this.$el.find( '#forminator-paypal-submit' ).removeAttr( 'id' ).addClass('forminator-hidden');
						this.$el.find('.forminator-button-next').removeClass( 'forminator-button-submit forminator-hidden' );
					}
					this.$el.find( '.forminator-button-back' ).html( this.prev_button_txt );
					this.$el.find( '.forminator-button-next' ).html( this.next_button_txt );

				}
			}
		},

		go_to: function (step, scrollToTop) {
			this.step = step;

			if (step === this.totalSteps) return false;

			// Hide all parts
			this.$el.find('.forminator-pagination').css({
				'height': '0',
				'opacity': '0',
				'visibility': 'hidden'
			}).attr( 'aria-hidden', 'true' );

			// Show desired page
			this.$el.find('[data-step=' + step + ']').css({
				'height': 'auto',
				'opacity': '1',
				'visibility': 'visible'
			}).removeAttr( 'aria-hidden' );

			//exec responsive captcha
			var forminatorFront = this.$el.data('forminatorFront');
			if (typeof forminatorFront !== 'undefined') {
				forminatorFront.responsive_captcha();
			}

			this.update_navigation();

			if (scrollToTop) {
				this.scroll_to_top_form();
			}
		},

		update_navigation: function () {

			// Update navigation
			this.$el.find( '.forminator-current' ).removeClass('forminator-current' );
			this.$el.find( '.forminator-step-' + this.step ).addClass( 'forminator-current' );

			this.calculate_bar_percentage();
		},

		/**
		 * Reset vertical screen position between sections
		 * https://app.asana.com/0/385581670491499/784073712068017/f
		 * Support Hustle Modal
		 */
		scroll_to_top_form: function () {
			var $element        = this.$el;
			// find first input row
			var first_input_row = this.$el.find('.forminator-row').not(':hidden').first();
			if (first_input_row.length) {
				$element = first_input_row;
			}

			if ($element.length) {
				var parent_selector = 'html,body';

				// check inside sui modal
				if (this.$el.closest('.sui-dialog').length > 0) {
					parent_selector = '.sui-dialog';
				}

				// check inside hustle modal (prioritize)
				if (this.$el.closest('.wph-modal').length > 0) {
					parent_selector = '.wph-modal';
				}

				$(parent_selector).animate({scrollTop: ($element.offset().top - ($(window).height() - $element.outerHeight(true)) / 2)}, 500, function () {
					if (!$element.attr("tabindex")) {
						$element.attr("tabindex", -1);
					}
					$element.focus();
				});
			}

		}
	});

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function (options) {
		return this.each(function () {
			if (!$.data(this, pluginName)) {
				$.data(this, pluginName, new ForminatorFrontPagination(this, options));
			}
		});
	};

})(jQuery, window, document);