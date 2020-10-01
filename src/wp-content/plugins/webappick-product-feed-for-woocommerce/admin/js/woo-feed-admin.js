// noinspection JSUnresolvedletiable
/**!
 * WooFeed Scripts
 * @version 3.3.6
 * @package WooFeed
 * @copyright 2020 WebAppick
 *
 */
/* global ajaxurl, wpAjax, postboxes, pagenow, alert, deleteUserSetting, typenow, adminpage, thousandsSeparator, decimalPoint, isRtl */
// noinspection JSUnresolvedVariable
(function($, window, document, wpAjax, opts) {
	"use strict";
	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 */

	/**
	 * disable element utility
	 *
	 * @since 3.1.9
	 *
	 * @param {*} status
	 * @returns {jQuery|HTMLElement}
	 */
	$.fn.disabled = function(status) {
		$(this).each(function() {
			let self = $(this),
				prop = 'disabled';

			if (typeof self.prop(prop) !== 'undefined') {
				self.prop(prop, status === void 0 || status === true);
			} else {
				!0 === status ? self.addClass(prop) : self.removeClass(prop);
			}
		});
		return self; // method chaining
	};
	
	/**
	 * Check if a HTMLElement or jQuery is disabled
	 */
	$.fn.isDisabled = function() {
		let self = $(this),
			prop = 'disabled';
		return typeof self.prop(prop) !== 'undefined' ? self.prop(prop) : self.hasClass(prop);
	};
	
	/**
	 * Clear Tooltip for clip board js
	 * @param {Object} event
	 */
	const clearTooltip = (event) => {
		$(event.currentTarget).removeClass( (index, className) => (className.match(/\btooltipped-\S+/g) || []).join(' ') ).removeClass('tooltipped').removeAttr('aria-label');
	};

	const showTooltip = (elem, msg) => {
		$(elem).addClass('tooltipped tooltipped-s').attr('aria-label', msg);
	};

	const fallbackMessage = (action)  =>{
		let actionMsg,
			actionKey = action === 'cut' ? 'X' : 'C';

		if (/iPhone|iPad/i.test(navigator.userAgent)) {
			actionMsg = 'No support :(';
		} else if (/Mac/i.test(navigator.userAgent)) {
			actionMsg = 'Press ⌘-' + actionKey + ' to ' + action;
		} else {
			actionMsg = 'Press Ctrl-' + actionKey + ' to ' + action;
		}

		return actionMsg;
	};
	
	/**
	 * Alias of jQuery.extend()
	 * @param {Object} _default
	 * @param {Object} _args
	 */
	const extend = (_default, _args) => $.extend(true, {}, _default, _args);
	
	let $copyBtn,
		clipboard,
		googleCategories,
		helper = {
			in_array: (needle, haystack) => {
				try {
					return haystack.indexOf(needle) !== -1;
				} catch (e) {
					return false;
				}
			},
			selectize_render_item: (data, escape) => `<div class="item wapk-selectize-item">${ escape(data.text)}</div>`, // phpcs:ignore WordPressVIPMinimum.JS.StringConcat.Found,
			ajax_fail: e => {
				console.warn(e);
				alert(e.hasOwnProperty('statusText') && e.hasOwnProperty('status') ? opts.ajax.error + '\n' + e.statusText + ' (' + e.status + ')' : e);
			},
			/**
			 * Initialize Sortable
			 * @param {jQuery|HTMLElement} el
			 * @param {object} config
			 * @param {int|boolean} column
			 * @param {function} onDrop
			 * @return {jQuery|HTMLElement}
			 */
			sortable: (el, config, column, onDrop) => {
				return (el || $('.sorted_table')).each(function() {
					let self = $(this),
						column_count = self.find('tbody > tr:eq(0) > td').length || column || 9;
					self.wf_sortable(extend({
						containerSelector: 'table',
						itemPath: '> tbody',
						itemSelector: 'tr',
						handle: 'i.wf_sortedtable',
						placeholder: `<tr class="placeholder"><td colspan="${column_count}"></td></tr>`,
						onDrop: ($item, container, _super, event) => {
							$item.removeClass(container.group.options.draggedClass).removeAttr('style');
							$("body").removeClass(container.group.options.bodyClass);
							if ( onDrop && 'function' === typeof( onDrop ) ) {
								onDrop( $item, container, _super, event );
							}
						},
					}, config));
				});
			},
			selectize: (el, config) => {
				return (el || $('select.selectize')).not('.selectized').each(function() {
					let self = $(this);
					self.selectize(extend({
						create: self.data('create') || false,
						plugins: self.data('plugins') ? self.data('plugins').split(',').map(function(s) {
							return s.trim();
						}) : [],
						//['remove_button'],
						render: {
							item: helper.selectize_render_item
						}
					}, config));
				});
			},
			fancySelect: (el, config) => {
				return (el || $('select.fancySelect')).not('.FancySelectInit').each(function() {
					let self = $(this);
					self.fancySelect(extend({
						maxItemShow: 3
					}, config));
				});
			},
			reindex_config_table: () => {
				$('#table-1').find('tbody tr').each( ( x, el ) => {
					$(el).find('[name]').each( ( x1, el ) => {
						$(el).attr('name', $(el).attr('name').replace(/(\[\d\])/g, `[${x}]`));
					} );
				} );
			},
			common: () => {
				helper.sortable( $('.sorted_table'), {}, 9, helper.reindex_config_table );
				helper.selectize();
				helper.fancySelect($('.outputType'));
			}
		},
		// helper functions
		feedEditor = {
			/**
			 * The Editor Form Elem.
			 * @type {jQuery|HTMLElement}
			 */
			form: null,

			/**
			 * Initialize The Feed Editor {Tabs...}
			 * @returns {void}
			 */
			init: function () {
				let self = this;
				self.form = $('.generateFeed');
				if (!self.form.length) return;
				helper.common();
				// noinspection JSUnresolvedVariable
				$(document).trigger(new jQuery.Event('feedEditor.init', {
					target: this.form
				}));
			},

			/**
			 * Render Merchant info ajax response and handle allowed feed type for selected merchant
			 * @param {jQuery|HTMLElement} merchantInfo jQuery dom object
			 * @param {jQuery|HTMLElement} feedType     jQuery dom object
			 * @param {Object} r            ajax response object
			 */
			renderMerchantInfo: function (merchantInfo, feedType, r) {
				for (let k in r) {
					if (r.hasOwnProperty(k)) {
						merchantInfo.find('.merchant-info-section.' + k + ' .data').html(r[k]); // phpcs:ignore WordPressVIPMinimum.JS.HTMLExecutingFunctions.html

						if ('feed_file_type' === k) {
							(function() {
								let types = r[k].split(",").map(function(t) {
									return t.trim().toLowerCase();
								}).filter(function(t) {
									// noinspection JSUnresolvedVariable
									return t !== '' && t !== opts.na.toLowerCase();
								});

								if (types.length) {
									feedType.find('option').removeAttr('selected').each(function() {
										let opt = $(this);
										opt.val() && !helper.in_array(opt.val(), types) ? opt.disabled(!0) : opt.disabled(!1);
									});
									if (types.length === 1) feedType.find('option[value="' + types[0] + '"]').attr('selected', 'selected');
								} else feedType.find('option').disabled(!1);
							})();
						}
					}
				}

				merchantInfo.find('.spinner').removeClass('is-active');
				feedType.disabled(!1);
				feedType.trigger('change');
				feedType.parent().find('.spinner').removeClass('is-active');
			},

			/**
			 * Render Feed Template Tabs and settings while creating new feed.
			 * @param {jQuery|HTMLElement} feedForm     feed from query dom object
			 * @param {object} r            merchant template ajax response object
			 */
			renderMerchantTemplate: function (feedForm, r) {
				let _loop = function _loop(k) {
					if (r.hasOwnProperty(k)) {
						if ('tabs' === k) {
							// noinspection JSUnresolvedFunction
							feedForm.html(r[k]); // phpcs:ignore WordPressVIPMinimum.JS.HTMLExecutingFunctions.html
						} else {
							let contentSettings = $('[name="' + k + '"]');

							if (contentSettings.length) {
								contentSettings.each(function() {
									let elem = $(this);

									if (elem.is('select')) {
										elem.find('[value="' + r[k] + '"]').prop('selected', true);
									} else if ((elem.is('[type=checkbox]') || elem.is('[type=radio]')) && elem.val() === r[k]) {
										elem.prop('checked', true);
									} else {
										elem.val(r[k]); // type=text
									}
								}).trigger('change');
							}
						}
					}
				};

				for (let k in r) {
					_loop(k);
				}

				feedEditor.init();
			}
		},
		// Feed Editor Table
		merchantInfoCache = [],
		merchantTemplateCache = [],
		tooltip = () => {
			// Tooltip only Text
			$('.wfmasterTooltip')
				.hover(function () {
					// Hover over code
					let self = $(this), title = self.attr('wftitle');
					self.data('tipText', title).removeAttr('wftitle');
					$('<p class="wftooltip"></p>').text(title).appendTo('body').fadeIn('slow');
				}, function () {
					// Hover out code
					let self = $(this);
					self.attr('wftitle', self.data('tipText'));
					$('.wftooltip').remove();
				})
				.mousemove(function (e) {
					$('.wftooltip').css({
						top: e.pageY + 10,
						left: e.pageX + 20
					});
				});
		},
		clip = () => {
			$copyBtn = $('.toClipboard');
			if (!ClipboardJS.isSupported() || /iPhone|iPad/i.test(navigator.userAgent)) {
				$copyBtn.find('img').hide(0);
			} else {
				$copyBtn.each(function() {
					$(this).on('mouseleave', clearTooltip).on('blur', clearTooltip);
				});
				clipboard = new ClipboardJS('.toClipboard');
				clipboard.on('error', function(event) {
					showTooltip(event.trigger, fallbackMessage(event.action));
				}).on('success', function(event) {
					showTooltip(event.trigger, 'Copied!');
				});
			}
		};
	
	/**
	 * Feed Generator Module
	 */
	class feedGenerator {
		
		/**
		 * Constructor
		 * @constructor
		 */
		constructor() {
			this._feed = opts.generator.feed; // wf_config+xxxx
			this._limit = opts.generator.limit;
			this._progress = 0;
			this._timer = null;
			this._color = false;
			// batch info
			this._total_batch = 0;
			this._current_batch = 0;
			this._product_ids = [];
			this._progress_per_batch = 0;
			this._refresh = true;
			// noinspection JSUnresolvedVariable
			this._regenerate = opts.generator.regenerate;
			window.isRegenerating = false;
			this._all_btn = $('.wpf_regenerate');
			this._current_btn = $(`#${this._feed.replace( 'wf_config', 'wf_feed_' )}`);
			this._current_btn_label = '';
		}
		
		/**
		 * Init Hooks (Event)
		 * @return {feedGenerator}
		 */
		init() {
			let self = this;
			if ( '' !== this._feed && this._regenerate && false === window.isRegenerating ) {
				this.generate();
			}
			$(document).on('click', '.wpf_regenerate', function(event) {
				event.preventDefault();
				self._current_btn = $( this );
				if( self._current_btn.hasClass('disabled') || window.isRegenerating === true ) return;
				self._feed = self._current_btn.attr('id').replace( 'wf_feed_', 'wf_config' );
				if( '' !== self._feed ) {
					self.generate();
				}
			});
			return this;
		}
		
		_block_button() {
			if ( this._all_btn.length ) {
				this._all_btn.addClass('disabled');
			}
			if ( this._current_btn.length ) {
				this._current_btn.find('span').addClass('wpf_spin reverse_spin');
				this._current_btn_label = this._current_btn.attr('title');
				// noinspection JSUnresolvedVariable
				this._current_btn.attr( 'aria-label', opts.regenerate ).attr( 'title', opts.regenerate );
			}
		}
		
		_unblock_button() {
			if ( this._all_btn.length ) {
				this._all_btn.removeClass('disabled');
			}
			if ( this._current_btn.length ) {
				this._current_btn.find('span').removeClass('wpf_spin');
				this._current_btn.find('span').removeClass('reverse_spin');
				this._current_btn.attr( 'aria-label', this._current_btn_label ).attr( 'title', this._current_btn_label );
			}
		}
		
		/**
		 * Generate Feed
		 * @return void
		 */
		generate() {
			let self = this;
			window.isRegenerating = true;
			this._block_button();
			this._resetProgressBar();
			this._progressBarActive();
			this._log( 'Counting Total Products' );
			this._updateProgressStatus( 'Fetching products.' );
			this._get_product_ids().then( response => {
				this._progress = 10;
				self._log( {response} );
				if(response.success) {
					self._log( `Total ${response.total} Products found.` );
					self._product_ids = response.product;
					self._total_batch = this._product_ids.length;
					self._current_batch = 0;
					self._progress_per_batch = ( 90 - this._progress ) / this._total_batch;
					self._process_batch();
					self._updateProgressStatus( 'Processing Products...' );
				} else {
					self._updateProgressStatus( response.data.message );
				}
			}).fail( error => {
				self._log( error );
				self._updateProgressStatus( error.message );
				self._color = 'red';
				setTimeout( function(){
					self._stopProgressBar();
					self._unblock_button();
				}, 1500 );
			} );
		}
		
		/**
		 * Get Product Ids
		 * @returns {$.promise}
		 * @private
		 */
		_get_product_ids() {
			this._progress = 5;
			return wpAjax.post( 'get_product_information', {
				_ajax_nonce: opts.nonce,
				feed: this._feed,
				limit: this._limit,
			} );
		}
		
		/**
		 * Run the Batch
		 * @private
		 */
		_process_batch() {
			let self = this;
			let status = `Processing Batch ${this._current_batch+1} of ${this._total_batch}`;
			this._updateProgressStatus( status );
			this._log( status );
			wpAjax.post( 'make_batch_feed', {
				_ajax_nonce: opts.nonce,
				feed: this._feed,
				products: this._product_ids[this._current_batch],
				loop: this._current_batch,
			} ).then( response => {
				self._current_batch++;
				self._log( `Batch ${self._current_batch} Completed` );
				self._log( response );
				if ( self._current_batch < self._total_batch ) {
					self._process_batch();
					self._progress += self._progress_per_batch;
				}
				if ( self._current_batch === self._total_batch ) {
					self._save_feed_file();
				}
			} ).fail( error => {
				self._log( error );
				self._updateProgressStatus( error.message );
				self._color = 'red';
				setTimeout( function(){
					self._stopProgressBar();
					self._unblock_button();
				}, 1500 );
			} );
		}
		
		/**
		 * Save Feed Data from temp to feed file
		 * @private
		 */
		_save_feed_file() {
			let self = this;
			this._log( 'Saving feed file' );
			this._updateProgressStatus( 'Saving feed file' );
			wpAjax.post( 'save_feed_file', {
				_ajax_nonce: opts.nonce,
				feed: this._feed,
			} ).then( response => {
				self._log( response );
				self._progress = 100;
				if ( self._refresh ) {
					window.location.href = `${opts.pages.list.feed}&link=${response.url}&cat=${response.cat}`;
				}
				setTimeout( function(){
					self._stopProgressBar();
					setTimeout( function(){
						self._resetProgressBar( true );
						self._unblock_button();
					}, 3000 );
				}, 2500 );
			} ).fail( error => {
				self._log( error );
				self._updateProgressStatus( error.message );
				self._color = 'red';
				setTimeout( function(){
					self._stopProgressBar();
					self._unblock_button();
				}, 1500 );
			} );
		}
		
		/**
		 * Console log wrapper with debug settings.
		 * @param data
		 * @returns {feedGenerator}
		 * @private
		 */
		_log( data ) {
			// noinspection JSUnresolvedVariable
			if ( opts.wpf_debug ) {
				console.log( data );
			}
			return this;
		}
		
		/**
		 * Run the progressbar refresh interval
		 * @param {int} refreshInterval
		 * @returns {feedGenerator}
		 * @private
		 */
		_progressBarActive( refreshInterval = 0 ) {
			let self = this;
			this._toggleProgressBar( true );
			this._timer = setInterval( function(){
				self._updateProgressBar();
			}, refreshInterval || 1000 );
			return this;
		}
		
		/**
		 * Stop Progressbar
		 * @returns {feedGenerator}
		 * @private
		 */
		_stopProgressBar() {
			clearInterval( this._timer );
			return this;
		}
		
		/**
		 * Reset Progressbar
		 * @returns {feedGenerator}
		 * @private
		 */
		_resetProgressBar( update ) {
			this._toggleProgressBar( false );
			this._updateProgressStatus( '' );
			clearInterval( this._timer );
			this._color = false;
			this._timer = null;
			this._progress = 0;
			if ( update ) {
				this._updateProgressBar();
			}
			return this;
		}
		
		/**
		 * Show hide the progress bar el
		 * @param status
		 * @returns {feedGenerator}
		 * @private
		 */
		_toggleProgressBar( status ) {
			let table = $('#feed_progress_table');
			if ( status ) {
				table.show();
			} else {
				table.hide();
			}
			return this;
		}
		
		/**
		 * Update Progress bar text status
		 * @param {string} status
		 * @returns {feedGenerator}
		 * @private
		 */
		_updateProgressStatus( status ) {
			$( '.feed-progress-status' ).text( status );
			return this;
		}
		
		/**
		 * Update Progress Data
		 * hooked with setInterval
		 * @private
		 */
		_updateProgressBar() {
			let percentage = $( '.feed-progress-percentage' ),
				bar = $( '.feed-progress-bar-fill' ),
				_progress = `${Math.round( this._progress )}%`;
			bar.css( {
				width: _progress,
				background: this._color || "#3DC264",
			} );
			percentage.text( _progress );
		}
	}
	// expose to the global scope
	window.wf = {
		helper: helper,
		feedEditor: feedEditor,
		generator: feedGenerator,
	};
	$(window).load(function() {
		// Template loading ui conflict
		if ($(location).attr("href").match(/webappick.*feed/g) !== null) {
			$('#wpbody-content').addClass('woofeed-body-content');
		}
		// ClipBoardJS
		clip();
		// postbox toggle
		postboxes.add_postbox_toggles(pagenow);
		// initialize generator
		let generator = new feedGenerator();
		generator.init();
		// noinspection JSUnresolvedVariable
		if( '' !== opts.generator.feed && opts.generator.regenerate ) {
		
		}
		// initialize editor
		feedEditor.init();
		helper.common(); // Generate Feed Add Table Row
		tooltip();
		// validate feed editor
		$(".generateFeed").validate();
		// document events
		$(document)
			.on('click', '[data-toggle_slide]', function(e) {
				e.preventDefault();
				$($(this).data('toggle_slide')).slideToggle('fast');
			})
			// XML Feed Wrapper
			.on('click', '#wf_newRow', function () {
				let tbody = $('#table-1 tbody'),
					template = $('#feed_config_template').text().trim().replace(/__idx__/g, tbody.find('tr').length);
				tbody.append(template);
				helper.fancySelect($('.outputType'));
			})
			// feed delete alert.
			.on('click', '.single-feed-delete', function (event) {
				event.preventDefault();
				// noinspection JSUnresolvedVariable
				if (confirm(opts.form.del_confirm)) {
					window.location.href = $(this).attr('val');
				}
			})
			// clear cache data.
			.on('click', '.wf_clean_cache_wrapper', function(event) {
				event.preventDefault();
				var nonce = $('.woo-feed-clean-cache-nonce').val();
				var loader = $('.woo-feed-cache-loader');

				//show loader
				loader.show();

				// passed cache nonce
				wpAjax.post('clear_cache_data', {
					_ajax_clean_nonce: nonce
				}).then(function (response) {
					if( response.success ) {
						loader.hide(); //hide loader
					}
				}).fail(function (e) {
					console.log('something wrong');
				});

			})
			// feed value dropdown change.
			.on('change', '.wf_attr.wf_attributes', function(event) {
				event.preventDefault();

				$('.fancy-picker-picked').trigger("click"); // trigger fancy select box clicked

				// price attributes
				var price_attributes = ['price', 'current_price', 'sale_price', 'price_with_tax', 'current_price_with_tax', 'sale_price_with_tax'];
				// current value
				var current_attribute_value = $(this).val();
				var outputSelect = $(this).parents('tr').find('.outputType');
				var fancyOption = $(this).parents('tr').find('.fancy-picker-content .fancy-picker-option');
				var fancyDataPicker = $(this).parents('tr').find('.fancy-picker-data span');
				var selectIf, selectKey;

				// when select any custom taxonomy
				if( "" !== current_attribute_value && -1 !== current_attribute_value.indexOf('wf_taxo') ) {
					selectIf = 'for_custom_taxo';
					selectKey = "parent_if_empty";
				}

				// when select any price attribute
				if( price_attributes.includes(current_attribute_value)  ) {
					selectIf = 'for_price';
					selectKey = "Price";
				}

				// remove selected class from old selected option
				fancyOption.removeClass('selected');

				// when value dropdown is selected as price or any custom taxonomy
				if( selectIf === 'for_custom_taxo' || selectIf === 'for_price' ) {

					// update "Option Type" when select key matches
					fancyOption.each(function(item) {
						if( selectKey === $(this).text() ) {
							$(this).addClass('selected');
							fancyDataPicker.text(selectKey);
							outputSelect.find("option").text(selectKey);
							outputSelect.find("option").val( $(this).data('value') );
						}
					});

				}

			})
			// bulk delete alert.
			.on('click', '#doaction, #doaction2', function () {
				// noinspection JSUnresolvedVariable
				return confirm(opts.form.del_confirm_multi);
			})
			// Generate Feed Table Row Delete
			.on('change', '.dType', function () {
				let self = $(this),
					type = self.val(),
					row = self.closest('tr');
				
				if (type === 'pattern') {
					row.find('.value_attribute').hide();
					row.find('.value_pattern').show();
				} else if (type === 'attribute') {
					row.find('.value_attribute').show();
					row.find('.value_pattern').hide();
				} else if (type === 'remove') {
					row.find('.value_attribute').hide();
					row.find('.value_pattern').hide();
				}
			})
			// Generate Feed Form Submit
			.on('click', '.delRow', function (e) {
				e.preventDefault();
				$(this).closest('tr').remove();
				helper.reindex_config_table();
			})
			.on('submit', '#generateFeed', function () {
				// Feed Generating form validation
				$(this).validate();
				
				if ($(this).valid()) {
					$(".makeFeedResponse")
						.show()
						.html(`<b style="color: darkblue;"><i class="dashicons dashicons-sos wpf_spin"></i> ${opts.form.generate}</b>`); // phpcs:ignore WordPressVIPMinimum.JS.HTMLExecutingFunctions.html, WordPressVIPMinimum.JS.StringConcat.Found
				}
			})
			// Generate Update Feed Form Submit
			.on('submit', '#updatefeed', function (e, data) {
				// Feed Generating form validation
				$(this).validate();
				
				if ($(this).valid()) {
					$(".makeFeedResponse")
						.show()
						.html(`<b style="color: darkblue;"><i class="dashicons dashicons-sos wpf_spin"></i> ${data && data.save ? opts.form.save : opts.form.generate}</b>`); // phpcs:ignore WordPressVIPMinimum.JS.HTMLExecutingFunctions.html, WordPressVIPMinimum.JS.StringConcat.Found
				}
			})
			.on('change', '.ftporsftp', function () {
				let server = $(this).val(),
					status = $('.ssh2_status');
				
				if (server === 'sftp') {
					// noinspection JSUnresolvedVariable
					status.show().css('color', 'dodgerblue').text(opts.form.sftp_checking);
					wpAjax.post('get_ssh2_status', {
						_ajax_nonce: opts.nonce,
						server: server
					}).then(function (response) {
						if (response === 'exists') {
							// noinspection JSUnresolvedVariable
							status.css('color', '#2CC185').text(opts.form.sftp_available);
							setTimeout(function () {
								status.hide();
							}, 1500);
						} else {
							// noinspection JSUnresolvedVariable
							status.show().css('color', 'red').text(opts.form.sftp_warning);
						}
					}).fail(function (e) {
						status.hide();
						helper.ajax_fail(e);
					});
				} else {
					status.hide();
				}
			})
			.on('click', '[name="save_feed_config"]', function (e) {
				e.preventDefault();
				$('#updatefeed').trigger('submit', {
					save: true
				});
			})
			.on('change', '#provider', function (event) {
				event.preventDefault();
				
				if (!$(this).closest('.generateFeed').hasClass('add-new')) return; // only for new feed.
				
				let merchant = $(this).val(),
					feedType = $("#feedType"),
					feedForm = $("#providerPage"),
					merchantInfo = $('#feed_merchant_info'); // set loading..
				
				// noinspection JSUnresolvedVariable
				feedForm.html('<h3><span style="float:none;margin: -3px 0 0;" class="spinner is-active"></span> ' + opts.form.loading_tmpl + '</h3>'); // phpcs:ignore WordPressVIPMinimum.JS.HTMLExecutingFunctions.html, WordPressVIPMinimum.JS.StringConcat.Found
				
				merchantInfo.find('.spinner').addClass('is-active');
				feedType.disabled(!0); // disable dropdown
				
				feedType.parent().find('.spinner').addClass('is-active');
				merchantInfo.find('.merchant-info-section .data').html(''); // remove previous data
				// Get Merchant info for selected Provider/Merchant
				
				if (merchantInfoCache.hasOwnProperty(merchant)) {
					feedEditor.renderMerchantInfo(merchantInfo, feedType, merchantInfoCache[merchant]);
				} else {
					wpAjax.send('woo_feed_get_merchant_info', {
						type: 'GET',
						data: {
							nonce: opts.nonce,
							provider: merchant
						}
					}).then(function (r) {
						merchantInfoCache[merchant] = r;
						feedEditor.renderMerchantInfo(merchantInfo, feedType, r);
					}).fail(helper.ajax_fail);
				} // Get FeedForm For Selected Provider/Merchant
				
				
				if (merchantTemplateCache.hasOwnProperty(merchant)) {
					feedEditor.renderMerchantTemplate(feedForm, merchantTemplateCache[merchant]);
				} else {
					wpAjax.post('get_feed_merchant', {
						_ajax_nonce: opts.nonce,
						merchant: merchant
					}).then(function (r) {
						merchantTemplateCache[merchant] = r;
						feedEditor.renderMerchantTemplate(feedForm, r);
					}).fail(helper.ajax_fail);
				}
			})
			// Feed Active and Inactive status change via ajax
			.on('change', '.woo_feed_status_input', function () {
				let self = $(this);
				wpAjax.post('update_feed_status', {
					_ajax_nonce: opts.nonce,
					feedName: self.val(),
					status: self[0].checked ? 1 : 0
				});
			});
		// event with trigger
		$(document)
			.on('change', '[name="is_outOfStock"], [name="product_visibility"]', function () {
				let outOfStockVisibilityRow = $('.out-of-stock-visibility');
				if ($('[name="is_outOfStock"]:checked').val() === 'n' && $('[name="product_visibility"]:checked').val() === '1') {
					outOfStockVisibilityRow.show();
				} else {
					outOfStockVisibilityRow.hide();
				}
			})
			.on('change', '.attr_type', function () {
				// Attribute type selection
				let self = $(this),
					type = self.val(),
					row = self.closest('tr');
				
				if (type === 'pattern') {
					row.find('.wf_attr').hide();
					row.find('.wf_attr').val('');
					row.find('.wf_default').show();
				} else {
					row.find('.wf_attr').show();
					row.find('.wf_default').hide();
					row.find('.wf_default').val('');
				}
			})
			.on('change', '.wf_mattributes, .attr_type', function () {
				let row = $(this).closest('tr'),
					attribute = row.find('.wf_mattributes'),
					type = row.find('.attr_type'),
					valueColumn = row.find('td:eq(4)'),
					provider = $('#provider').val();
				
				// noinspection JSUnresolvedVariable
				if (opts.form.google_category.hasOwnProperty(attribute.val()) && type.val() === 'pattern' && helper.in_array(provider, opts.form.google_category[attribute.val()])) {
					if (valueColumn.find('select.selectize').length === 0) {
						valueColumn.find('input.wf_default').remove();
						valueColumn.append('<span class="wf_default wf_attributes"><select name="default[]" class="selectize"></select></span>');
						// noinspection JSUnresolvedVariable
						valueColumn.append(`<span style="font-size:x-small;"><a style="color: red" href="http://webappick.helpscoutdocs.com/article/19-how-to-map-store-category-with-merchant-category" target="_blank">${opts.learn_more}</a></span>`);
						
						if (!googleCategories) {
							valueColumn.append('<span class="spinner is-active" style="margin: 0;"></span>');
						}
						
						let select = valueColumn.find('.wf_attributes select');
						// noinspection JSUnresolvedVariable
						helper.selectize(select, {
							preload: true,
							placeholder: opts.form.select_category,
							load: function load(query, cb) {
								if (!googleCategories) {
									wpAjax.send('get_google_categories', {
										type: 'GET',
										data: {
											_ajax_nonce: opts.nonce,
											action: "get_google_categories",
											provider: provider
										}
									}).then(function (r) {
										googleCategories = r;
										cb(googleCategories);
										valueColumn.find('.spinner').remove();
									}).fail(helper.ajax_fail);
								} else {
									cb(googleCategories);
								}
							}
						});
					}
				} else {
					if (attribute.val() !== 'current_category' && valueColumn.find('input.wf_default').length === 0) {
						valueColumn.find('span').remove();
						valueColumn.append('<input autocomplete="off" class="wf_default wf_attributes"  type="text" name="default[]" value="">');
						
						if (type.val() !== 'pattern') {
							valueColumn.find('input.wf_default').hide();
						}
					}
				}
			})
			.on('change', '#feedType,#provider', function () {
				let type = $('#feedType').val(),
					provider = $('#provider').val(),
					itemWrapper = $('.itemWrapper'),
					wf_csv_txt = $('.wf_csvtxt');
				
				// noinspection JSUnresolvedVariable
				if (type !== '' && helper.in_array(provider, opts.form.item_wrapper_hidden)) {
					itemWrapper.hide();
				} else if (type === 'xml') {
					itemWrapper.show();
					wf_csv_txt.hide();
				} else if (type === 'csv' || type === 'txt') {
					itemWrapper.hide();
					wf_csv_txt.show();
				} else {
					itemWrapper.hide();
					wf_csv_txt.hide();
				}
			})
			.trigger('change');
	});
})(jQuery, window, document, wp.ajax, wpf_ajax_obj);
