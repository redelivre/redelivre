"use strict";
/**!
 * WooFeed Fancy Select
 * @version 1.0.0
 * @copyright 2020 WebAppick
 * @author Kudratullah <mhamudul.hk@gmail.com>
 */

(function($, window, document) {
	// @TODO if multiple select has required attribute and only one item is selected then return false (user should not able to deselect the last one).
	// @TODO make the place holder with input field like selectize so we can set the required attribute if the select has required attribute, which can be useful for showing required warning.
	"use strict";
	
	/**
	 * FancySelect Constructor
	 * @param {jQuery|HTMLElement} $select
	 * @param {Object} [options]
	 * @constructor
	 */
	let FancySelect = function FancySelect($select, options) {
		let self = this,
			defaults = {
				options: [],
				optgroups: [],
				items: [],
				placeholder: '',
				delimiter: ',',
				splitOn: null,
				persist: !0,
				diacritics: !0,
				create: !1,
				createOnBlur: !1,
				createFilter: null,
				highlight: !0,
				openOnFocus: !0,
				maxOptions: 1e3,
				maxItems: null,
				maxItemShow: 3,
				hideSelected: null,
				addPrecedence: !1,
				selectOnTab: !1,
				preload: !1,
				allowEmptyOption: !1,
				closeAfterSelect: !1,
				scrollDuration: 60,
				loadThrottle: 300,
				loadingClass: "loading",
				dataAttr: "data-data",
				optgroupField: "optgroup",
				valueField: "value",
				labelField: "text",
				disabledField: "disabled",
				optgroupLabelField: "label",
				optgroupValueField: "value",
				lockOptgroupOrder: !1,
				sortField: "$order",
				searchField: ["text"],
				searchConjunction: "and",
				mode: null,
				wrapperClass: "selectize-control",
				inputClass: "selectize-input",
				dropdownClass: "selectize-dropdown",
				dropdownContentClass: "selectize-dropdown-content",
				dropdownParent: null,
				copyClassesToDropdown: !0,
				render: {}
			},
			settings = $.extend(true, {}, defaults, options),
			select = $select[0];
		select.fancySelect = self;
		self.order = 0;
		let computedStyle = window.getComputedStyle && window.getComputedStyle(select, null);
		let dir = computedStyle ? computedStyle.getPropertyValue('direction') : select.currentStyle && select.currentStyle.direction;
		dir = dir || $select.parents('[dir]:first').attr('dir') || '';
		self.computedStyle = computedStyle; // for now, android support in general is too spotty to support validity

		const SUPPORTS_VALIDITY_API = !/android/i.test(window.navigator.userAgent) && !! document.createElement('input').validity; // helper (private) methods

		const debounce = function (fn, delay) {
			let timeout;
			return function() {
				let self = this;
				let args = arguments;
				window.clearTimeout(timeout);
				timeout = window.setTimeout(function() {
					fn.apply(self, args);
				}, delay);
			};
		};
		
		let optionsMap = {},
			optHelper = {
				readData: function ($el) {
					let data = self.dataAttr && $el.attr(self.dataAttr);

					if (typeof data === 'string' && data.length) {
						return JSON.parse(data);
					}

					return null;
				},
				addOption: function ($option, group) {
					$option = $($option);
					let value = self.hash_key($option.val());
					if (!value && !settings.allowEmptyOption) return; // if the option already exists, it's probably been
					// duplicated in another optgroup. in this case, push
					// the current group to the "optgroup" property on the
					// existing option so that it's rendered in both places.

					if (optionsMap.hasOwnProperty(value)) {
						if (group) {
							let arr = optionsMap[value][field_optgroup];

							if (!arr) {
								optionsMap[value][field_optgroup] = group;
							} else if (!$.isArray(arr)) {
								optionsMap[value][field_optgroup] = [arr, group];
							} else {
								arr.push(group);
							}
						}

						return;
					}

					let option = optHelper.readData($option) || {};
					option[self.settings.labelField] = option[self.settings.labelField] || $option.text();
					option[self.settings.valueField] = option[self.settings.valueField] || value;
					option[self.settings.disabledField] = option[self.settings.disabledField] || $option.prop('disabled');
					option[self.settings.optgroupField] = option[self.settings.optgroupField] || group;
					option.$order = ++self.order;
					optionsMap[value] = option;
					self.settings.options.push(option);

					if ($option.is(':selected')) {
						self.settings.items.push(option);
					}
				},
				addGroup: function ($optgroup) {
					let i, n, id, optgroup, $options;
					$optgroup = $($optgroup);
					id = $optgroup.attr('label');

					if (id) {
						optgroup = optHelper.readData($optgroup) || {};
						optgroup[self.settings.optgroupLabelField] = id;
						optgroup[self.settings.optgroupValueField] = id;
						optgroup[self.settings.disabledField] = $optgroup.prop('disabled');
						optgroup.$order = ++self.order;
						self.settings.optgroups.push(optgroup);
					}

					$options = $('option', $optgroup);

					for (i = 0, n = $options.length; i < n; i++) {
						optHelper.addOption($options[i], id);
					}
				}
			}; // setup default state
		
		$.extend(self, {
			settings: settings,
			$select: $select,
			tabIndex: $select.attr('tabindex') || '',
			tagType: 1,
			rtl: /rtl/i.test(dir),
			multiple: $select.attr('multiple'),
			eventNS: '.FancySelect' + ++FancySelect.count,
			highlightedValue: null,
			isBlurring: false,
			isOpen: false,
			isDisabled: false,
			isRequired: $select.is('[required]'),
			isInvalid: false,
			isLocked: false,
			isFocused: false,
			isInputHidden: false,
			isSetup: false,
			isRendered: false,
			isShiftDown: false,
			isCmdDown: false,
			isCtrlDown: false,
			ignoreFocus: false,
			ignoreBlur: false,
			ignoreHover: false,
			hasOptions: false,
			currentResults: null,
			lastValue: '',
			caretPos: 0,
			loading: 0,
			loadedSearches: {},
			$activeOption: null,
			$activeItems: [],
			optgroups: {},
			options: {},
			userOptions: {},
			items: {},
			renderCache: {},
			onSearchChange: settings.loadThrottle === null ? self.onSearchChange : debounce(self.onSearchChange, settings.loadThrottle)
		});

		if ('' === self.settings.placeholder) {
			self.settings.placeholder = $select.attr('placeholder') || $select.attr('data-placeholder');

			if (!self.settings.placeholder && !self.settings.allowEmptyOption) {
				self.settings.placeholder = $select.children('option[value=""]').text();
			}
		}

		self.settings.maxItems = self.multiple ? null : 1;

		for (let i = 0, n = self.$select.children().length; i < n; i++) {
			let tagName = self.$select.children()[i].tagName.toLowerCase();

			if (tagName === 'optgroup') {
				optHelper.addGroup(self.$select.children()[i]);
			} else if (tagName === 'option') {
				optHelper.addOption(self.$select.children()[i]);
			}
		}

		self.$wrapper = $('<div class="fancy-picker">').addClass($select.attr('class'));

		if (self.computedStyle.hasOwnProperty('width')) {
			self.$wrapper.css({
				width: self.computedStyle.width
			});
		}
		
		self.$outputWrapper = $('<div class="fancy-picker-picked">').appendTo(self.$wrapper);
		self.originalPlaceholder = $('<span class="fancy-picker-placeholder">').appendTo(self.$outputWrapper);
		self.dataPlaceholder = $('<span class="fancy-picker-data">').appendTo(self.$outputWrapper);
		self.dataCountPlaceholder = $('<span class="fancy-picker-count">').appendTo(self.$outputWrapper);
		self.$dropdown = $('<div class="fancy-picker-ui">').appendTo(self.$wrapper);
		self.$dropdownContent = $('<div class="fancy-picker-content">').appendTo(self.$dropdown);
		self.setup();
	};

	FancySelect.count = 0;
	// public methods.
	$.extend(FancySelect.prototype, {
		/**
		 * Creates all elements and sets up event bindings.
		 * @return {void}
		 */
		setup: function () {
			let self = this;
			self.revertSettings = {
				$children: self.$select.children().detach(),
				tabindex: self.$select.attr('tabindex')
			};
			self.$select.attr('tabindex', -1).hide().after(self.$wrapper);
			self.$select.data('FancySelect', self);
			self.$select.addClass('FancySelectInit');
			
			self.settings.items.sort(  ( a, b ) => a[self.settings.sortField] - b[self.settings.sortField] );
			
			
			if (self.preload) {
				self.render();
			}
			
			self.updatePlaceholder();
			self.updateOriginalInput();
			
			self.$wrapper.on('click' + self.eventNS, '.fancy-picker-picked', function(e) {
				self.$select.trigger('show');
				
				if (!self.isRendered) {
					self.render();
				}
				
				self.$wrapper.toggleClass('active');
				self.$select.trigger('shown');
			});
			$(document).on('click' + self.eventNS, function(e) {
				if (!$(e.target).closest(self.$wrapper).length) {
					self.$select.trigger('hide');
					self.$wrapper.removeClass('active');
					self.$select.trigger('hidden');
				}
			});
			self.$wrapper.on('click' + self.eventNS, '.fancy-picker-option:not(.disabled)', function(e) {
				e.preventDefault();
				let current = $(this),
					selected = false,
					value = self.hash_key(current.data('value'));
				
				if (self.multiple) {
					if (!current.hasClass('selected')) {
						selected = true;
						current.addClass('selected');
					} else current.removeClass('selected');
					
					if (selected) {
						self.settings.items.push( self.getSelectedOptionData( value ) );
					} else {
						self.settings.items = self.settings.items.filter( x => x[self.settings.valueField] !== value );
					}
					
					self.settings.items.sort(  ( a, b ) => a[self.settings.sortField] - b[self.settings.sortField] );
				} else {
					self.$dropdownContent.find('.fancy-picker-option').not(current).removeClass('selected');
					current.addClass('selected');
					selected = true;
					self.settings.items = [ self.getSelectedOptionData( value ) ];
					self.$wrapper.removeClass('active');
				}
				
				self.updatePlaceholder();
				self.updateOriginalInput();
			});
			$(window).on('resize' + self.eventNS, function() {
				let computedStyle = window.getComputedStyle && window.getComputedStyle(self.$select[0], null);
				
				if (computedStyle.hasOwnProperty('width')) {
					self.$wrapper.css({
						width: computedStyle.width
					});
				}
			});
			self.$select.trigger('initialize');
		},
		/**
		 * Render The FancySelect UI
		 * @return {void}
		 */
		render: function () {
			let self = this,
				dropdownItems = [];
			const optgroup = self.settings.optgroups;
			const options = self.settings.options;
			const optClass = ( value ) => self.isSelected(value) ? 'fancy-picker-option selected' : 'fancy-picker-option';
			if ( optgroup.length ) {
				for ( let i = 0; i < optgroup.length; i++ ) {
					dropdownItems.push( `<div class="fancy-picker-option-group">` );
					dropdownItems.push( `<div class="fancy-picker-option-group-label">${optgroup[i][self.settings.labelField]}</div>` );
					const group_options = options.filter( item => item[self.settings.optgroupField] === optgroup[i][self.settings.valueField]);
					for (let ii = 0; ii < group_options.length; ii++ ) {
						let option = group_options[i];
						dropdownItems.push( `<div class="${optClass( option[self.settings.valueField] )}" data-value="${option[self.settings.valueField]}">${option[self.settings.labelField]}</div>` );
					}
					dropdownItems.push( `</div>` );
				}
			} else {
				for (let i = 0; i < options.length; i++) {
					let option = options[i];
					dropdownItems.push( `<div class="${optClass( option[self.settings.valueField] )}" data-value="${option[self.settings.valueField]}">${option[self.settings.labelField]}</div>` );
				}
			}
			
			self.$dropdownContent.html(dropdownItems.join(''));
			self.$select.trigger('rendered');
			self.isRendered = true;
		},
		/**
		 * Set Placeholder & Update Selected Data Placeholder
		 * @return {FancySelect}
		 */
		updatePlaceholder: function () {
			let self = this,
				placeholderData = [];
			const items = self.settings.items;
			const maxItemShow = items.length > self.settings.maxItemShow ? self.settings.maxItemShow - 1 : self.settings.maxItemShow;
			self.originalPlaceholder.text(self.settings.placeholder);
			
			if (items.length) {
				self.originalPlaceholder.hide();
			} else {
				self.originalPlaceholder.show();
			}
			
			items.slice(0, maxItemShow).forEach( (item)  => {
				placeholderData.push( `<span>${item[self.settings.labelField]}</span>` );
			});
			self.dataPlaceholder.html( placeholderData.join( `<span class="fancy-picker-separator">${self.settings.delimiter}</span>` ) );
			
			if (items.length > self.settings.maxItemShow) {
				let title = [];
				items.forEach( (item) => {
					title.push(item[self.settings.labelField]);
				});
				self.dataCountPlaceholder.attr('title', title.join(self.settings.delimiter.trim() + ' '));
				self.dataCountPlaceholder.html('+' + (items.length - maxItemShow) + ' More &hellip;');
			} else {
				self.dataCountPlaceholder.removeAttr('title');
				self.dataCountPlaceholder.html('');
			}
			
			self.$select.trigger('placeholderChanged');
			return self;
		},
		/**
		 * Update The Original Select Tag
		 * @param {boolean} ?silent
		 * @return {FancySelect}
		 */
		updateOriginalInput: function (silent) {
			let self = this,
				options = [],
				changed = false;
			silent = true === silent ? true : false;
			const items = self.settings.items;
			for (let i = 0; i < items.length; i++) {
				options.push('<option value="' + items[i][self.settings.valueField] + '" selected="selected">' + items[i][self.settings.labelField] || '' + '</option>');
				changed = true;
			}
			
			self.$select.html(options.join(''));
			
			if (!silent && changed) {
				self.$select.trigger('change');
			}
			return self;
		},
		/**
		 * Get Selected Option Data (value, label, etc.)
		 * @param {string} hash_key
		 * @return {object|boolean}
		 */
		getSelectedOptionData: function (hash_key) {
			let self = this,
				selected = self.settings.options.filter( x => x[self.settings.valueField] === hash_key );
			return selected.length ? selected[0] : false;
		},
		/**
		 * Check if input is selected (in the items list )
		 * @param {string} hash_key
		 * @return {boolean}
		 */
		isSelected: function (hash_key) {
			let self = this;
			return self.settings.items.filter( x => x[self.settings.valueField] === hash_key ).length > 0;
		},
		/**
		 * Convert input to it's best string representation.
		 * @param {string|*} input
		 * @return {string}
		 */
		hash_key: (input) => input + '',
		/**
		 * Completely destroys the control and
		 * unbinds all event listeners so that it can
		 * be garbage collected.
		 */
		destroy: function () {
			let self = this;
			self.$select.trigger('destroy');
			self.trigger('destroy');
			self.off();
			self.$select
				.html('')
				.append(self.revertSettings.$children)
				.removeAttr('tabindex')
				.removeClass('FancySelectInit')
				.attr({tabindex: self.revertSettings.tabindex})
				.show();
			self.$select.removeData('FancySelect');
			self.$wrapper.remove();
			
			if (--FancySelect.count === 0 && FancySelect.$testInput) {
				FancySelect.$testInput.remove();
				FancySelect.$testInput = undefined;
			} // this doesn't fire on create feed. as fields are loaded via ajax.
			// add custom event with the form object (with namespace)
			// trigger event on form.init()
			// remove previous listener on from.init(). first. so multiple listener not executed.
			// see selectize https://github.com/selectize/selectize.js/blob/master/src/selectize.js#L2097
			// add event listener here...
			
			
			$(window).off(self.eventNS);
			$(document).off(self.eventNS);
			$(document.body).off(self.eventNS);
			delete self.$select[0].fancySelect;
		},
		
	});
	
	/**
	 * MicroEvent - to make any js object an event emitter
	 *
	 * - pure javascript - server compatible, browser compatible
	 * - dont rely on the browser doms
	 * - super simple - you get it immediatly, no mistery, no magic involved
	 *
	 * @author Jerome Etienne (https://github.com/jeromeetienne)
	 * @link https://github.com/jeromeetienne/microevent.js
	 */
	$.extend(FancySelect.prototype, {
		bind	: function(event, fct){
			this._events = this._events || {};
			this._events[event] = this._events[event]	|| [];
			this._events[event].push(fct);
		},
		unbind	: function(event, fct){
			this._events = this._events || {};
			if( event in this._events === false  )	return;
			this._events[event].splice(this._events[event].indexOf(fct), 1);
		},
		trigger	: function(event /* , args... */){
			this._events = this._events || {};
			if( event in this._events === false  )	return;
			for(let i = 0; i < this._events[event].length; i++){
				this._events[event][i].apply(this, Array.prototype.slice.call(arguments, 1));
			}
		}
	});
	/**
	 * jQuery Wrapper
	 * @param {Object} [user_options]
	 * @returns {jquery|HTMLElement}
	 */
	$.fn.fancySelect = function(user_options) {
		return this.each(function() {
			if (this.fancySelect) return;
			if ('select' !== this.tagName.toLowerCase()) return;
			new FancySelect($(this), user_options);
		});
	};
})(jQuery, window, document);
