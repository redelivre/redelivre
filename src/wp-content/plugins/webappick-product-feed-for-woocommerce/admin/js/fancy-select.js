/**!
 * WooFeed Fancy Select
 * @version 1.0.0
 * @copyright 2020 WebAppick
 * @author Kudratullah <mhamudul.hk@gmail.com>
 */
(function ($, window, document ) {
    "use strict";
    /**
     * FancySelect Constructor
     * @param {jQuery|HTMLElement} $select
     * @param {Object} [options]
     * @constructor
     */
    let FancySelect = function ( $select, options ) {
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
            settings = $.extend( true, {}, defaults, options ),
            select = $select[0];
        select.fancySelect = self;
        self.order = 0;
        let computedStyle = window.getComputedStyle && window.getComputedStyle( select, null );
        let dir = computedStyle ? computedStyle.getPropertyValue('direction') : input.currentStyle && input.currentStyle.direction;
        dir = dir || $select.parents('[dir]:first').attr('dir') || '';
        self.computedStyle = computedStyle;
        // for now, android support in general is too spotty to support validity
        const SUPPORTS_VALIDITY_API = !/android/i.test(window.navigator.userAgent) && !!document.createElement('input').validity;
        // helper (private) methods
        const debounce = function(fn, delay) {
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
        const hash_key = ( str ) => str + '';
        let optionsMap = {};
        const optHelper = {
            readData: function($el) {
                const data = self.dataAttr && $el.attr(self.dataAttr);
                if (typeof data === 'string' && data.length) {
                    return JSON.parse(data);
                }
                return null;
            },
            addOption: function($option, group) {
                $option = $($option);

                const value = hash_key($option.val());
                if (!value && !settings.allowEmptyOption) return;

                // if the option already exists, it's probably been
                // duplicated in another optgroup. in this case, push
                // the current group to the "optgroup" property on the
                // existing option so that it's rendered in both places.
                if (optionsMap.hasOwnProperty(value)) {
                    if (group) {
                        const arr = optionsMap[value][field_optgroup];
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

                var option             = optHelper.readData($option) || {};
                option[self.settings.labelField]    = option[self.settings.labelField] || $option.text();
                option[self.settings.valueField]    = option[self.settings.valueField] || value;
                option[self.settings.disabledField] = option[self.settings.disabledField] || $option.prop('disabled');
                option[self.settings.optgroupField] = option[self.settings.optgroupField] || group;
                option.$order = ++self.order;

                optionsMap[value] = option;
                self.settings.options.push(option);

                if ($option.is(':selected')) {
                    self.settings.items.push(value);
                }
            },
            addGroup: function($optgroup) {
                var i, n, id, optgroup, $options;

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
            },
        };
        // setup default state
        $.extend(self, {
            settings         : settings,
            $select          : $select,
            tabIndex         : $select.attr('tabindex') || '',
            tagType          : 1,
            rtl              : /rtl/i.test(dir),
            multiple         : $select.attr('multiple'),
            eventNS          : '.FancySelect' + (++FancySelect.count),
            highlightedValue : null,
            isBlurring       : false,
            isOpen           : false,
            isDisabled       : false,
            isRequired       : $select.is('[required]'),
            isInvalid        : false,
            isLocked         : false,
            isFocused        : false,
            isInputHidden    : false,
            isSetup          : false,
            isRendered       : false,
            isShiftDown      : false,
            isCmdDown        : false,
            isCtrlDown       : false,
            ignoreFocus      : false,
            ignoreBlur       : false,
            ignoreHover      : false,
            hasOptions       : false,
            currentResults   : null,
            lastValue        : '',
            caretPos         : 0,
            loading          : 0,
            loadedSearches   : {},

            $activeOption    : null,
            $activeItems     : [],

            optgroups        : {},
            options          : {},
            userOptions      : {},
            items            : {},
            renderCache      : {},
            onSearchChange   : settings.loadThrottle === null ? self.onSearchChange : debounce(self.onSearchChange, settings.loadThrottle)
        });

        if ( '' === self.settings.placeholder ) {
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

        self.$wrapper = $('<div class="fancy-picker">').addClass( $select.attr('class') );
        if ( self.computedStyle.hasOwnProperty('width') ) {
            self.$wrapper.css( { width: self.computedStyle.width } );
        }
        self.$outputWrapper = $('<div class="fancy-picker-picked">').appendTo( self.$wrapper );
        self.originalPlaceholder = $('<span class="fancy-picker-placeholder">').appendTo( self.$outputWrapper );
        self.dataPlaceholder = $('<span class="fancy-picker-data">').appendTo( self.$outputWrapper );
        self.dataCountPlaceholder = $('<span class="fancy-picker-count">').appendTo( self.$outputWrapper );
        self.$dropdown = $('<div class="fancy-picker-ui">').appendTo( self.$wrapper );
        self.$dropdownContent = $('<div class="fancy-picker-content">').appendTo( self.$dropdown );

        self.setup();
    };
    FancySelect.count = 0;

    // public methods
    $.extend(FancySelect.prototype, {
        /**
         * Creates all elements and sets up event bindings.
         */
        setup: function() {
            let self = this;
            self.revertSettings = {
                $children : self.$select.children().detach(),
                tabindex  : self.$select.attr('tabindex')
            };
            self.$select.attr('tabindex', -1).hide().after(self.$wrapper);
            self.$select.data('FancySelect', self);
            self.$select.addClass('FancySelectInit');
            self.settings.items.sort();
            if ( self.preload ) {
                self.render();
            }
            self.updatePlaceholder();
            self.updateOriginalInput();
            self.$wrapper.on( 'click'+ self.eventNS, '.fancy-picker-picked', function( e ) {
                self.$select.trigger( 'show' );
                if ( ! self.isRendered ) {
                    self.render();
                }
                self.$wrapper.toggleClass('active');
                self.$select.trigger( 'shown' );
            } );
            $(document).on( 'click' + self.eventNS, function( e ) {
                if(!$(e.target).closest(self.$wrapper).length) {
                    self.$select.trigger( 'hide' );
                    self.$wrapper.removeClass('active');
                    self.$select.trigger( 'hidden' );
                }
            });
            self.$wrapper.on( 'click' + self.eventNS, '.fancy-picker-option:not(.disabled)', function( e ) {
                e.preventDefault();
                let current = $(this), selected = false, value = current.data('value');
                if ( self.multiple ) {
                    if ( !current.hasClass( 'selected' ) ) {
                        selected = true;
                        current.addClass( 'selected' )
                    } else current.removeClass( 'selected' );

                    if ( selected ) {
                        self.settings.items.push( value );
                    } else {
                        self.settings.items = self.settings.items.filter( x => x != value );
                    }
                    self.settings.items.sort();
                } else {
                    self.$dropdownContent.find('.fancy-picker-option').not(current).removeClass('selected');
                    current.addClass('selected');
                    selected = true;
                    self.settings.items = [ value ];
                    self.$wrapper.removeClass('active');
                }
                self.updatePlaceholder();
                self.updateOriginalInput();
            });
            self.$select.trigger('initialize' );
        },
        updateOriginalInput: function( silent ) {
            let self = this, options = [], changed = false;
            silent = ( true === silent ) ? true : false;
            for ( let i = 0, n = self.settings.items.length; i < n; i++) {
                let selected = self.getSelectedOptionData( self.settings.items[i] );
                if ( selected ) {
                    options.push('<option value="' + self.settings.items[i] + '" selected="selected">' + selected[self.settings.labelField] || '' + '</option>');
                    changed = true;
                }
            }
            self.$select.html( options.join('') );
            if ( ! silent && changed ) {
                self.$select.trigger( 'change' );
            }
        },
        render: function() {
            let self = this;
            let dropdownItems = [];
            for ( let i = 0, n = self.settings.options.length; i < n; i++) {
                let option = self.settings.options[i],
                    classes = self.isSelected( option[self.settings.valueField] ) ? 'fancy-picker-option selected' : 'fancy-picker-option';
                dropdownItems.push( `<div class="${classes}" data-value="${option[self.settings.valueField]}">${option[self.settings.labelField]}</div>` );
            }
            self.$dropdownContent.html(dropdownItems.join(''));
            self.$select.trigger( 'rendered' );
            self.isRendered = true;
        },
        updatePlaceholder: function() {
            let self = this;

            self.originalPlaceholder.text( self.settings.placeholder );
            if ( self.settings.items.length ) {
                self.originalPlaceholder.hide();
            } else {
                self.originalPlaceholder.show();
            }
            let placeholderData = [];
            let maxItemShow = self.settings.items.length > self.settings.maxItemShow ? self.settings.maxItemShow - 1 : self.settings.maxItemShow;
            self.settings.items.slice( 0, maxItemShow ).forEach( item => {
                let data = self.getSelectedOptionData( item );
                if( data ) placeholderData.push( `<span>${data[self.settings.labelField]}</span>` );
            });
            self.dataPlaceholder.html(placeholderData.join( `<span class="fancy-picker-separator">${self.settings.delimiter}</span> ` ));
            if( self.settings.items.length > self.settings.maxItemShow ) {
                let title = [];
                self.settings.items.forEach( item => {
                    let data = self.getSelectedOptionData( item );
                    if( data ) title.push( data[self.settings.labelField] );
                });
                self.dataCountPlaceholder.attr('title', title.join( self.settings.delimiter.trim() + ' ' ) );
                self.dataCountPlaceholder.html( '+' + ( self.settings.items.length - maxItemShow ) + ' More &hellip;' )
            } else {
                self.dataCountPlaceholder.removeAttr( 'title' );
                self.dataCountPlaceholder.html('');
            }
            self.$select.trigger( 'placeholderChanged' )
        },
        getSelectedOptionData: function( has_key ) {
            let self = this;
            let selected = self.settings.options.filter( (x)=> x[self.settings.valueField] == has_key );
            return selected.length ? selected[0]: false;
        },
        isSelected: function( has_key ) {
            let self = this;
            return self.settings.items.filter( x => x == has_key ).length > 0;
        },
        /**
         * Completely destroys the control and
         * unbinds all event listeners so that it can
         * be garbage collected.
         */
        destroy: function() {
            let self = this;
            self.$select.trigger('destroy');
            self.trigger('destroy');
            self.off();

            self.$select
                .html('')
                .append(self.revertSettings.$children)
                .removeAttr('tabindex')
                .removeClass('selectized')
                .attr({tabindex: self.revertSettings.tabindex})
                .show();

            self.$control_input.removeData('grow');
            self.$select.removeData('selectize');

            if (--FancySelect.count == 0 && FancySelect.$testInput) {
                FancySelect.$testInput.remove();
                FancySelect.$testInput = undefined;
            }

            // this doesn't fire on create feed. as fields are loaded via ajax.
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

        /**
         * MicroEvent - to make any js object an event emitter
         *
         * - pure javascript - server compatible, browser compatible
         * - dont rely on the browser doms
         * - super simple - you get it immediatly, no mistery, no magic involved
         *
         * @author Jerome Etienne (https://github.com/jeromeetienne)
         */
        on: function(event, fct){
            this._events = this._events || {};
            this._events[event] = this._events[event] || [];
            this._events[event].push(fct);
        },
        off: function(event, fct){
            const n = arguments.length;
            if (n === 0) return delete this._events;
            if (n === 1) return delete this._events[event];

            this._events = this._events || {};
            if (event in this._events === false) return;
            this._events[event].splice(this._events[event].indexOf(fct), 1);
        },
        trigger: function(event /* , args... */){
            this._events = this._events || {};
            if (event in this._events === false) return;
            for (let i = 0; i < this._events[event].length; i++){
                this._events[event][i].apply(this, Array.prototype.slice.call(arguments, 1));
            }
        }
    });
    /**
     * jQuery Wrapper
     * @param {Object} [user_options]
     * @returns {jquery|HTMLElement}
     */
    $.fn.fancySelect = function( user_options ) {
        return this.each(function() {
            if (this.fancySelect) return;
            if ( 'select' !== this.tagName.toLowerCase() ) return;
            new FancySelect( $(this), user_options )
        });
    };
})(jQuery, window, document);