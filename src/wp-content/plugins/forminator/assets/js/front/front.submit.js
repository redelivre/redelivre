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
	var pluginName = "forminatorFrontSubmit",
		defaults = {
			form_type: 'custom-form',
			forminatorFront: false,
			forminator_selector: '',
			chart_design: 'bar',
			chart_options: {}
		};

	// The actual plugin constructor
	function ForminatorFrontSubmit(element, options) {
		this.element = element;
		this.$el = $(this.element);
		this.forminatorFront = null;


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
	$.extend(ForminatorFrontSubmit.prototype, {
		init: function () {
			this.forminatorFront = this.$el.data('forminatorFront');
			switch (this.settings.form_type) {
				case 'custom-form':
					if (!this.settings.forminator_selector || !$(this.settings.forminator_selector).length) {
						this.settings.forminator_selector = '.forminator-custom-form';
					}
					this.handle_submit_custom_form();
					break;
				case 'quiz':
					if (!this.settings.forminator_selector || !$(this.settings.forminator_selector).length) {
						this.settings.forminator_selector = '.forminator-quiz';
					}
					this.handle_submit_quiz();
					break;
				case 'poll':
					if (!this.settings.forminator_selector || !$(this.settings.forminator_selector).length) {
						this.settings.forminator_selector = '.forminator-poll';
					}
					this.handle_submit_poll();
					break;

			}
		},

		decodeHtmlEntity: function(str) {
			return str.replace(/&#(\d+);/g, function(match, dec) {
				return String.fromCharCode(dec);
			});
		},

		handle_submit_custom_form: function () {
			var self = this,
				form = $(this.element);

			var success_available = self.$el.find('.forminator-response-message').find('.forminator-label--success').not(':hidden');
			if (success_available.length) {
				self.focus_to_element(self.$el.find('.forminator-response-message'), true);
			}
			$('.def-ajaxloader').hide();
			var isSent = false;
			$('body').on('click', '#lostPhone', function (e) {
				e.preventDefault();
				var that = $(this);
				if (isSent === false) {
					isSent = true;
					$.ajax({
						type: 'GET',
						url: that.attr('href'),
						beforeSend: function () {
							that.attr('disabled', 'disabled');
							$('.def-ajaxloader').show();
						},
						success: function (data) {
							that.removeAttr('disabled');
							$('.def-ajaxloader').hide();
							$('.notification').text(data.data.message);
							isSent = false;
						}
					})
				}
			});

			$('body').on('click', '.auth-back', function (e) {
				e.preventDefault();
				var moduleId  = self.$el.attr( 'id' ),
					authId    = moduleId + '-authentication',
					authInput = $( '#' + authId + '-input' )
				;
				authInput.attr( 'disabled','disabled' );
				FUI.closeAuthentication();
			});

			$('body').on('submit.frontSubmit', this.settings.forminator_selector, function (e) {
				var $this = $(this),
				    thisForm = this,
				    submitEvent = e,
					formData = new FormData(this),
					$target_message = $this.find('.forminator-response-message'),
					$captcha_field = $this.find('.forminator-g-recaptcha');

				if (self.$el.data('forminatorFrontPayment')) {
					if ($captcha_field.length) {
						//validate only first
						$captcha_field = $($captcha_field.get(0));

						// get the recatpcha widget
						var recaptcha_widget = $captcha_field.data('forminator-recapchta-widget'),
							recaptcha_size = $captcha_field.data('size'),
							$captcha_response = window.grecaptcha.getResponse(recaptcha_widget);

						if (recaptcha_size === 'invisible') {
							if ($captcha_response.length === 0) {
								window.grecaptcha.execute(recaptcha_widget);
								return false;
							}
						}
						// reset after getResponse
						if (self.$el.hasClass('forminator_ajax')) {
							window.grecaptcha.reset(recaptcha_widget);
						}

						$target_message.html('');
						if ($captcha_field.hasClass("error")) {
							$captcha_field.removeClass("error");
						}

						if ($captcha_response.length === 0) {
							if (!$captcha_field.hasClass("error")) {
								$captcha_field.addClass("error");
							}
							$target_message.html('<label class="forminator-label--error"><span>' + window.ForminatorFront.cform.captcha_error + '</span></label>');
							self.focus_to_element($target_message);

							return false;
						}
					}
				}

				var submitCallback = function() {
					formData = new FormData(this); // reinit values

					if (!self.$el.data('forminatorFrontPayment')) {
						if ($captcha_field.length) {
							//validate only first
							$captcha_field = $($captcha_field.get(0));

							// get the recatpcha widget
							var recaptcha_widget = $captcha_field.data('forminator-recapchta-widget'),
								recaptcha_size = $captcha_field.data('size'),
								$captcha_response = window.grecaptcha.getResponse(recaptcha_widget);

							if (recaptcha_size === 'invisible') {
								if ($captcha_response.length === 0) {
									window.grecaptcha.execute(recaptcha_widget);
									return false;
								}
							}
							// reset after getResponse
							if (self.$el.hasClass('forminator_ajax')) {
								window.grecaptcha.reset(recaptcha_widget);
							}

							$target_message.html('');
							if ($captcha_field.hasClass("error")) {
								$captcha_field.removeClass("error");
							}

							if ($captcha_response.length === 0) {
								if (!$captcha_field.hasClass("error")) {
									$captcha_field.addClass("error");
								}
								$target_message.html('<label class="forminator-label--error"><span>' + window.ForminatorFront.cform.captcha_error + '</span></label>');
								self.focus_to_element($target_message);

								return false;
							}
						}
					}

					if (self.$el.hasClass('forminator_ajax')) {
						$target_message.html('');
						self.$el.find('.forminator-button-submit').addClass('forminator-button-onload');

						// Safari FIX, if empty file input, ajax broken
						// Check if input empty
						self.$el.find("input[type=file]").each(function () {
							// IE does not support FormData.delete()
							if ($(this).val() === "") {
								if (typeof(window.FormData.prototype.delete) === 'function') {
									formData.delete($(this).attr('name'));
								}
							}
						});

						var form_type = '';
						if( typeof self.settings.has_loader !== "undefined" && self.settings.has_loader ) {
							// Disable form fields
							form_type = self.$el.find('input[name="form_type"]').val();
							if( 'login' !== form_type ) {
								form.addClass('forminator-fields-disabled');
							}
							$target_message.html('<p>' + self.settings.loader_label + '</p>');

							$target_message.removeAttr("aria-hidden")
								.prop("tabindex", "-1")
								.removeClass('forminator-success forminator-error')
								.addClass('forminator-loading forminator-show');
						}

						e.preventDefault();
						$.ajax({
							type: 'POST',
							url: window.ForminatorFront.ajaxUrl,
							data: formData,
							cache: false,
							contentType: false,
							processData: false,
							beforeSend: function () {
								$this.find('button').attr('disabled', true);
								$this.trigger('before:forminator:form:submit', formData);
							},
							success: function( data ) {

								// Hide validation errors
								$this.find( '.forminator-error-message' ).remove();
								$this.find( '.forminator-field' ).removeClass( 'forminator-has_error' );

								$this.find( 'button' ).removeAttr( 'disabled' );
								$target_message.html( '' ).removeClass( 'forminator-accessible forminator-error forminator-success' );

								if ( typeof data.data.authentication !== 'undefined' &&
									( 'show' === data.data.authentication || 'invalid' === data.data.authentication ) ) {
									var moduleId  = self.$el.attr( 'id' ),
										authId    = moduleId + '-authentication',
										authField = $( '#' + authId ),
										authInput = $( '#' + authId + '-input' )
									;
									authField.find('.forminator-authentication-notice').removeClass('error');
									authField.find('.lost-device-url').attr('href', data.data.lost_url);

									if( 'show' === data.data.authentication ) {
										authInput.removeAttr( 'disabled' );
										FUI.openAuthentication( authId, moduleId, authId + '-input' );
									}
									if ( 'invalid' === data.data.authentication ) {
										authField.find('.forminator-authentication-notice').addClass('error');
										authField.find('.forminator-authentication-notice').html('<p>' + data.data.message + '</p>');
									}

									return false;

								}
								var $label_class = data.success ? 'forminator-success' : 'forminator-error';

								if (typeof data.message !== "undefined") {
									$target_message.removeAttr("aria-hidden")
										.prop("tabindex", "-1")
										.addClass($label_class + ' forminator-show');
									self.focus_to_element($target_message, $label_class === 'forminator-success');
									$target_message.html('<p>' + data.message + '</p>');

									if(!data.data.success && data.data.errors.length) {
										var errors_html = '<ul class="forminator-screen-reader-only">';
										$.each(data.data.errors, function(index,value) {
											for(var propName in value) {
											    if(value.hasOwnProperty(propName)) {
											       errors_html += '<li>' + value[propName] + '</li>';
											    }
											}
										})
										errors_html += '</ul>';
										$target_message.append(errors_html);
									}
								} else {
									if (typeof data.data !== "undefined") {
										var isShowSuccessMessage = true;
										//Remove background of the success message if form behaviour is redirect and the success message is empty
										if ( typeof data.data.url !== 'undefined' && '' === $.trim(data.data.message) ) {
											isShowSuccessMessage = false;
										}
										if ( isShowSuccessMessage ) {
											$target_message.removeAttr("aria-hidden")
												.prop("tabindex", "-1")
												.addClass($label_class + ' forminator-show');
											self.focus_to_element($target_message, $label_class === 'forminator-success');
											$target_message.html('<p>' + data.data.message + '</p>');
										}

										if(!data.data.success && data.data.errors.length) {
											var errors_html = '<ul class="forminator-screen-reader-only">';
											$.each(data.data.errors, function(index,value) {
												//errors_html += '<li>' + value
												for(var propName in value) {
												    if(value.hasOwnProperty(propName)) {
												        errors_html += '<li>' + value[propName] + '</li>';
												    }
												}
											})
											errors_html += '</ul>';
											$target_message.append(errors_html);
										}
									}
								}

								if (!data.data.success && data.data.errors.length) {
									$this.trigger('forminator:form:submit:failed', formData);
									self.show_messages(data.data.errors);
								}

								if (data.success === true) {
									// Reset form
									if ($this[0]) {
										var resetEnabled = self.settings.resetEnabled;
										if(resetEnabled) {
											$this[0].reset();
										}

										if (typeof data.data.select_field !== "undefined") {
											$.each(data.data.select_field, function (index, value) {
												if (value.length > 0) {
													$.each(value, function (i, v) {
														if (v['value']) {
															if (v['type'] === 'multiselect') {
																$this.find("#" + index + " input[value=" + v['value'] + "]").closest('li').remove().trigger("change");
															} else {
																$this.find("#" + index + " option[value=" + v['value'] + "]").remove().trigger("change");
															}
														}
													});
												}
											});
										}
										// Reset upload field
										$this.find(".forminator-button-delete").hide();
										$this.find('.forminator-file-upload input').val("");
										$this.find('.forminator-file-upload > span').html(window.ForminatorFront.cform.no_file_chosen);

										// Reset selects
										$this.find('.forminator-select').each(function () {
											var defaultValue = $(this).data('default-value');
											$(this).val(defaultValue).trigger("change");
										});

										$this.trigger('forminator:form:submit:success', formData);

										// restart condition after form reset to ensure values of input already reset-ed too
										$this.trigger('forminator.front.condition.restart');
									}

									if (typeof data.data.url !== "undefined") {

										//check if newtab option is selected
										if(typeof data.data.newtab !== "undefined" && data.data.newtab !== "sametab"){
											if ( 'newtab_hide' === data.data.newtab ) {
												//hide if newtab redirect with hide form option selected
												self.$el.hide();
											}
											//new tab redirection
											window.open( self.decodeHtmlEntity( data.data.url ), '_blank' );
										} else {
											//same tab redirection
											window.location.href = self.decodeHtmlEntity( data.data.url );
										}

									}

									if (typeof data.data.behav !== "undefined" && data.data.behav === 'behaviour-hide') {
										self.$el.find('.forminator-row').hide();
										self.$el.find('.forminator-pagination-steps').hide();
										self.$el.find('.forminator-pagination-footer').hide();
										self.$el.find('.forminator-pagination-steps, .forminator-pagination-progress').hide();
									}
								}
							},
							error: function (err) {
								$this.find('button').removeAttr('disabled');
								$target_message.html('');
								var $message = err.status === 400 ? window.ForminatorFront.cform.upload_error : window.ForminatorFront.cform.error;
								$target_message.html('<label class="forminator-label--notice"><span>' + $message + '</span></label>');
								self.focus_to_element($target_message);

								$this.trigger('forminator:form:submit:failed', formData);
							},
							complete: function(xhr,status) {
								self.$el.find('.forminator-button-submit').removeClass('forminator-button-onload');

								$this.trigger('forminator:form:submit:complete', formData);
							}
						}).always(function () {
							if( typeof self.settings.has_loader !== "undefined" && self.settings.has_loader ) {
								// Enable form fields
								form.removeClass('forminator-fields-disabled forminator-partial-disabled');

								$target_message.removeClass('forminator-loading');
							}

							$this.trigger('after:forminator:form:submit', formData);
						});
					} else {
						if( typeof self.settings.has_loader !== "undefined" && self.settings.has_loader ) {
							// Disable form fields
							form.addClass('forminator-fields-disabled');

							$target_message.html('<p>' + self.settings.loader_label + '</p>');

							$target_message.removeAttr("aria-hidden")
								.prop("tabindex", "-1")
								.removeClass('forminator-success forminator-error')
								.addClass('forminator-loading forminator-show');
						}

						submitEvent.currentTarget.submit();
					}
				};

				// payment setup
				var paymentIsHidden = self.$el.find('div[data-is-payment="true"]')
					.closest('.forminator-row').hasClass('forminator-hidden');
				if (self.$el.data('forminatorFrontPayment') && !paymentIsHidden) {
					self.$el.trigger('payment.before.submit.forminator', [formData, function () {
						submitCallback.apply(thisForm);
					}]);
				} else {
					submitCallback.apply(thisForm);
				}

				return false;
			});
		},

		handle_submit_quiz: function( data ) {

			var self = this;

			$( 'body' ).on( 'submit.frontSubmit', this.settings.forminator_selector, function( e ) {

				var form      = $(this),
					ajaxData  = [],
					answer    = form.find( '.forminator-answer' ),
					button    = self.$el.find('.forminator-button'),
					loadLabel = button.data( 'loading' )
					;

				e.preventDefault();
				e.stopPropagation();

				// Enable all inputs
				self.$el.find( '.forminator-has-been-disabled' ).removeAttr( 'disabled' );

				// Serialize fields, that should be placed here!
				ajaxData = form.serialize();

				// Disable inputs again
				self.$el.find( '.forminator-has-been-disabled' ).attr( 'disabled', 'disabled' );

				// Add loading label.
				if ( loadLabel !== '' ) {
					button.text( loadLabel );
				}

				answer.each( function() {

					var answer = $( this ),
						input  = answer.find( 'input' ),
						status = answer.find( '.forminator-answer--status' ),
						loader = '<i class="forminator-icon-loader forminator-loading"></i>'
						;

					if ( input.is( ':checked' ) ) {

						if ( 0 === status.html().length ) {
							status.html( loader );
						}
					}
				});

				$.ajax({
					type: 'POST',
					url: window.ForminatorFront.ajaxUrl,
					data: ajaxData,
					beforeSend: function() {
						self.$el.find( 'button' ).attr( 'disabled', 'disabled' );
						form.trigger( 'before:forminator:quiz:submit', ajaxData );
					},
					success: function( data ) {

						if ( data.success ) {

							if ( data.data.type === 'nowrong' ) {

								window.history.pushState( 'forminator', 'Forminator', data.data.result_url );
								self.$el.find( '.forminator-quiz--result' ).html( data.data.result );
								self.$el.find( '.forminator-answer input' ).attr( 'disabled', 'disabled' );

							} else if ( data.data.type === 'knowledge' ) {

								window.history.pushState( 'forminator', 'Forminator', data.data.result_url );

								if ( self.$el.find( '.forminator-quiz--result' ).size() > 0 ) {
									self.$el.find( '.forminator-quiz--result' ).html( data.data.finalText );
								}

								Object.keys( data.data.result ).forEach( function( key ) {

									var responseClass,
										responseIcon,
										parent  = self.$el.find( '#' + key ),
										result  = parent.find( '.forminator-question--result' ),
										submit  = parent.find( '.forminator-submit-rightaway' ),
										answer  = parent.find( '[id|="' + data.data.result[key].answer + '"]' ).closest( '.forminator-answer' ),
										answers = parent.find( '.forminator-answer input' )
										;

									// Check if selected answer is right or wrong.
									if ( data.data.result[key].isCorrect ) {
										responseClass = 'forminator-is_correct';
										responseIcon  = '<i class="forminator-icon-check"></i>';
									} else {
										responseClass = 'forminator-is_incorrect';
										responseIcon  = '<i class="forminator-icon-cancel"></i>';
									}

									// Show question result.
									result.text( data.data.result[key].message );
									result.addClass( 'forminator-show' );
									submit.attr( 'disabled', true );
									submit.attr( 'aria-disabled', true );

									// Prevent user from changing answer.
									answers.attr( 'disabled', true );
									answers.attr( 'aria-disabled', true );

									// Check if selected answer is right or wrong.
									answer.addClass( responseClass );
									if ( 0 === answer.find( '.forminator-answer--status' ).html().length ) {
										answer.find( '.forminator-answer--status' ).html( responseIcon );
									} else {

										if ( 0 !== answer.find( '.forminator-answer--status .forminator-icon-loader' ).length ) {
											answer.find( '.forminator-answer--status' ).html( responseIcon );
										}
									}

								});
							}

							form.trigger( 'forminator:quiz:submit:success', ajaxData ) ;

						} else {
							self.$el.find( 'button' ).removeAttr( 'disabled' );
							form.trigger( 'forminator:quiz:submit:failed', ajaxData );
						}
					}
				}).always(function () {
					form.trigger('after:forminator:quiz:submit', ajaxData);
				});
				return false;
			});

			$('body').on('click', '.forminator-result--retake', function (e) {
				var pageId = self.$el.find('input[name="page_id"]').val();
				var ajaxData = {
					action: 'forminator_reload_quiz',
					pageId: pageId,
					nonce: self.$el.find('input[name="forminator_nonce"]').val()
				};

				e.preventDefault();

				$.post( window.ForminatorFront.ajaxUrl, ajaxData, function( response ) {
					if ( response.success == true && response.html ) {
						window.location.replace(response.html);
					}
				} );
			});
		},

		handle_submit_poll: function () {
			var self = this,
				poll_form = self.$el.html();

			// Hide (success) response message
			var success_available = self.$el.find( '.forminator-response-message' ).not( ':hidden' );

			if ( success_available.length ) {

				self.focus_to_element(
					self.$el.find( '.forminator-response-message' ),
					true
				);
			}

			$( 'body' ).on( 'submit.frontSubmit', this.settings.forminator_selector, function (e) {
				var $this    = $( this ),
					ajaxData = $this.serialize()
					;

				var $response = self.$el.find( '.forminator-response-message' ),
					$options  = self.$el.find( 'fieldset' ),
					$submit   = self.$el.find( '.forminator-button' )
					;

				function response_clean() {
					// Remove content
					$response.html( '' );

					// Remove all classes
					$response.removeClass( 'forminator-show' );
					$response.removeClass( 'forminator-error' );
					$response.removeClass( 'forminator-success' );

					// Hide for screen readers
					$response.removeAttr( 'tabindex' );
					$response.attr( 'aria-hidden', true );

					// Remove options error class
					$options.removeClass( 'forminator-has_error' );

				}

				function response_message( message, custom_class ) {

					// Print message
					$response.html( '<p>' + message + '</p>' );

					// Add necessary classes
					$response.addClass( 'forminator-' + custom_class );
					$response.addClass( 'forminator-show' );

					// Show for screen readers
					$response.removeAttr( 'aria-hidden' );
					$response.attr( 'tabindex', '-1' );

					// Focus message
					$response.focus();

					// Add options error class
					if ( 'error' === custom_class ) {

						if ( ! $options.find( 'input[type="radio"]' ).is( ':checked' ) ) {
							$options.addClass( 'forminator-has_error' );
						}
					}
				}

				if ( self.$el.hasClass( 'forminator_ajax' ) ) {
					response_clean();

					$.ajax({
						type: 'POST',
						url:  window.ForminatorFront.ajaxUrl,
						data: ajaxData,

						beforeSend: function() {

							// Animate "submit" button
							$submit.addClass( 'forminator-onload' );

							// Trigger "submit" action
							$this.trigger( 'before:forminator:poll:submit', ajaxData );

						},

						success: function( data ) {

							var $label_class = data.success ? 'success' : 'error';

							// Stop "submit" animation
							$submit.removeClass( 'forminator-onload' );

							if ( false === data.success ) {

								// Print message
								response_message( data.data.message, $label_class );

								// Failed response
								$this.trigger( 'forminator:poll:submit:failed', ajaxData );

							} else {

								if ( 'undefined' !== typeof data.data ) {

									$label_class = data.data.success ? 'success' : 'error';

									// Print message
									response_message( data.data.message, $label_class );

									// Auto close message
									setTimeout( function() {
										response_clean();
									}, 2500 );

								}
							}

							if ( true === data.success ) {

								if ( typeof data.data.url !== 'undefined' ) {
									window.location.href = data.data.url;
								} else {

									// url not exist, it will render chart on the fly if chart_data exist on response
									// check length is > 1, because [0] is header
									if ( typeof data.data.chart_data !== 'undefined' && data.data.chart_data.length > 1 ) {

										if ( 'link_on' === data.data.results_behav ) {

											if ( $this.find( '.forminator-note' ).length ) {
												$this.find( '.forminator-note' ).remove();
												$this.find( '.forminator-poll-footer' ).append( data.data.results_link );
											}
										}

										if ( 'show_after' === data.data.results_behav ) {

											self.render_poll_chart(
												data.data.chart_data,
												data.data.back_button,
												self,
												poll_form,
												[
													data.data.votes_text,
													data.data.votes_count,
													[
														data.data.grids_color,
														data.data.labels_color,
														data.data.onchart_label
													],
													[
														data.data.tooltips_bg,
														data.data.tooltips_color
													]
												]
											);

											console.log( data.data.grids_color );
										}
									}
								}

								// Success response
								$this.trigger( 'forminator:poll:submit:success', ajaxData );

							}
						},

						error: function() {

							response_clean();

							// Stop "submit" animation
							$submit.removeClass( '.forminator-onload' );

							// Failed response
							$this.trigger( 'forminator:poll:submit:failed', ajaxData );

						}
					}).always( function() {

						$this.trigger( 'after:forminator:poll:submit', ajaxData );

					});

					return false;

				}

				return true;

			});
		},

		render_poll_chart: function( chart_data, back_button, forminatorSubmit, poll_form, chart_extras ) {
			var pollId      = forminatorSubmit.$el.attr( 'id' ) + '-' + forminatorSubmit.$el.data('forminatorRender'),
				chartId     = 'forminator-chart-poll-' + pollId,
				pollBody    = forminatorSubmit.$el.find( '.forminator-poll-body' ),
				pollFooter  = forminatorSubmit.$el.find( '.forminator-poll-footer' )
				;

			function chart_clean() {

				var canvas = forminatorSubmit.$el.find( '.forminator-chart-wrapper' ),
					wrapper = forminatorSubmit.$el.find( '.forminator-chart' )
					;

				canvas.remove();
				wrapper.remove();

			}

			function chart_create() {
				var canvas = $( '<canvas id="' + chartId + '" class="forminator-chart" role="img" aria-hidden="true"></canvas>' );

				pollBody.append( canvas );
			}

			function chart_show() {
				var canvas = forminatorSubmit.$el.find( '.forminator-chart' ),
					wrapper = forminatorSubmit.$el.find( '.forminator-chart-wrapper' )
					;

				if ( wrapper.length ) {

					// Show canvas
					canvas.addClass( 'forminator-show' );

					// Show wrapper
					wrapper.addClass( 'forminator-show' );
					wrapper.removeAttr( 'aria-hidden' );
					wrapper.attr( 'tabindex', '-1' );

					// Focus message
					wrapper.focus();
				} else {
					// Fallback text
					canvas.html( '<p>Fallback text...</p>' );

					// Show canvas
					canvas.addClass( 'forminator-show' );
					canvas.removeAttr( 'aria-hidden' );
					canvas.attr( 'tabindex', '-1' );

					// Focus message
					canvas.focus();
				}
			}

			function hide_answers() {
				var answers = pollBody.find( '.forminator-field' );

				answers.hide();
				answers.attr( 'aria-hidden', 'true' );
			}

			function replace_footer() {

				var button = $( back_button );

				pollFooter.empty();
				pollFooter.append( button );

			}

			function back_to_poll() {

				var button = forminatorSubmit.$el.find( '.forminator-button' );

				button.click( function( e ) {

					if ( forminatorSubmit.$el.hasClass( 'forminator_ajax' ) ) {
						forminatorSubmit.$el.html( poll_form );
					} else {
						location.reload();
					}

					e.preventDefault();

				});
			}

			// Remove previously chart if exists
			chart_clean();

			// Create chart markup
			chart_create();

			// Load chart
			FUI.pollChart(
				'#' + chartId,
				chart_data,
				forminatorSubmit.settings.chart_design,
				chart_extras
			);

			// Hide poll answers
			hide_answers();

			// Show poll chart
			chart_show();

			// Replace footer
			replace_footer();
			back_to_poll();

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

		show_messages: function( errors ) {
			var self = this,
				forminatorFrontCondition = self.$el.data('forminatorFrontCondition');
			if (typeof forminatorFrontCondition !== 'undefined') {

				// clear all validation message before show new one
				this.$el.find('.forminator-error-message').remove();
				var i = 0;

				errors.forEach( function( value ) {

					var elementId  = Object.keys( value ),
						getElement = forminatorFrontCondition.get_form_field( elementId )
						;

					var holder      = $( getElement ),
						holderField = holder.closest( '.forminator-field' ),
						holderDate  = holder.closest( '.forminator-date-input' ),
						holderTime  = holder.closest( '.forminator-timepicker' ),
						holderError = '',
						getColumn   = false,
						getError    = false,
						getDesc     = false
						;

					var errorMessage = Object.values( value ),
						errorMarkup  = '<span class="forminator-error-message" aria-hidden="true"></span>'
						;

					if ( getElement.length ) {

						// Focus on first error
						if ( i === 0 ) {
							self.$el.trigger( 'forminator.front.pagination.focus.input', [getElement]);
							self.focus_to_element( getElement );
						}

						// CHECK: Timepicker field.
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

							var getError = holderField.find( '.forminator-error-message' ),
								getDesc  = holderField.find( '.forminator-description' )
								;

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

						i++;

					}
				});
			}

			return this;
		}

	});

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function (options) {
		return this.each(function () {
			if (!$.data(this, pluginName)) {
				$.data(this, pluginName, new ForminatorFrontSubmit(this, options));
			}
		});
	};

})(jQuery, window, document);
