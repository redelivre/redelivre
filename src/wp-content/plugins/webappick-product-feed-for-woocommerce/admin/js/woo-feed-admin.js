// noinspection JSUnresolvedVariable
/**!
 * WooFeed Scripts
 * @version 3.3.6
 * @package WooFeed
 * @copyright 2020 WebAppick
 *
 */
(function ($, window, document, wpAjax, opts) {
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
    $.fn.disabled = function( status ) {
        $(this).each( function(){
            // noinspection ES6ConvertVarToLetConst
            var self = $(this), prop = 'disabled';
            if( typeof( self.prop( prop ) ) !== 'undefined' ) {
                self.prop( prop, status === void 0 || status === true  );
            } else {
                ! 0 === status ? self.addClass( prop ) : self.removeClass( prop );
            }
        } );
        return self; // method chaining
    };
    /**
     * Check if a HTMLElement or jQuery is disabled
     */
    $.fn.isDisabled = function() {
        // noinspection ES6ConvertVarToLetConst
        var self = $(this), prop = 'disabled';
        return ( typeof( self.prop( prop ) ) !== 'undefined' ) ? self.prop( prop ) : self.hasClass( prop );
    };

    /**
     * Clear Tooltip for clip board js
     * @param {Object} event
     */
    function clearTooltip( event ) {
        $(event.currentTarget).removeClass( function (index, className) {
            return (className.match (/\btooltipped-\S+/g) || []).join(' ');
        } ).removeClass('tooltipped').removeAttr('aria-label');
    }

    function showTooltip( elem, msg ) {
        $( elem ).addClass('tooltipped tooltipped-s').attr( 'aria-label', msg );
    }

    function fallbackMessage(action) {
        // noinspection ES6ConvertVarToLetConst
        var actionMsg, actionKey = (action === 'cut' ? 'X' : 'C');
        if (/iPhone|iPad/i.test(navigator.userAgent)) {
            actionMsg = 'No support :(';
        } else if (/Mac/i.test(navigator.userAgent)) {
            actionMsg = 'Press ⌘-' + actionKey + ' to ' + action;
        } else {
            actionMsg = 'Press Ctrl-' + actionKey + ' to ' + action;
        }
        return actionMsg;
    }

    /* global ajaxurl, wpAjax, postboxes, pagenow, alert, deleteUserSetting, typenow, adminpage, thousandsSeparator, decimalPoint, isRtl */
    /**
     * Alias of jQuery.extend()
     * @param {Object} _default
     * @param {Object} _args
     */
    const extend = ( _default, _args ) => $.extend( true, {}, _default, _args );
    // noinspection ES6ConvertVarToLetConst
    var $copyBtn, clipboard, googleCategories,
        helper = {
            in_array: function( needle, haystack ) {
                try {
                    return haystack.indexOf( needle ) !== -1;
                } catch( e ) {
                    return false;
                }
            },
            selectize_render_item: function( data, escape ) {
                return '<div class="item wapk-selectize-item">' + escape(data.text) + '</div>'; // phpcs:ignore WordPressVIPMinimum.JS.StringConcat.Found
            },
            ajax_fail: function ( e ) {
                console.warn( e );
                alert( ( e.hasOwnProperty( 'statusText' ) && e.hasOwnProperty( 'status' ) ) ? opts.ajax.error + '\n' + e.statusText + ' (' + e.status + ')' : e );
            },
            sortable: function( el, config ) {
                console.log( ( el || $('.sorted_table') ) );
                ( el || $('.sorted_table') ).wf_sortable( extend( {
                    containerSelector: 'table',
                    itemPath: '> tbody',
                    itemSelector: 'tr',
                    handle: 'i.wf_sortedtable',
                    placeholder: '<tr class="placeholder"><td colspan="9"></td></tr>',
                }, config ));
            },
            selectize: function( el, config ) {
                return ( el || $('select.selectize') ).not('.selectized').each(function(){
                    let self = $(this);
                    self.selectize( extend( {
                        create: self.data('create') || false,
                        plugins: self.data('plugins') ? self.data('plugins').split(',').map( s => s.trim() ): [],//['remove_button'],
                        render: {item: helper.selectize_render_item,}
                    }, config ) );
                } );
            }
        }, // helper functions
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
            init: function() {
                let self = this;
                self.form = $('.generateFeed');
                if( ! self.form.length ) return;
                // noinspection ES6ConvertVarToLetConst
                var outOfStockVisibilityRow = $('.out-of-stock-visibility');
                // Initialize Table Sorting
                // noinspection JSUnresolvedFunction
                helper.sortable();
                helper.selectize();
                $(document)
                    .on('change', '[name="is_outOfStock"], [name="product_visibility"]', function () {
                        if ($('[name="is_outOfStock"]:checked').val() === 'n' && $('[name="product_visibility"]:checked').val() === '1') {
                            outOfStockVisibilityRow.show();
                        } else {
                            outOfStockVisibilityRow.hide();
                        }
                    })
                    .on('change', '.attr_type', function () {
                        // Attribute type selection
                        // noinspection ES6ConvertVarToLetConst
                        var type = $(this).val(), row = $(this).closest('tr');
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
                        // noinspection ES6ConvertVarToLetConst
                        var row = $(this).closest('tr'),
                            attribute = row.find('.wf_mattributes'),
                            type = row.find('.attr_type'),
                            valueColumn = row.find('td:eq(4)'),
                            provider = $('#provider').val();
                        if (attribute.val() === 'current_category' && type.val() === 'pattern' && helper.in_array(provider, ['google', 'facebook', 'pinterest'])) {
                            if (valueColumn.find('select.selectize').length === 0) {
                                // noinspection ES6ConvertVarToLetConst
                                var selectizeOpts = {
                                    options: googleCategories,
                                    config: {render: {item: helper.selectize_render_item,}},
                                };
                                valueColumn.find('input.wf_default').remove();
                                valueColumn.append('<span class="wf_default wf_attributes"><select name="default[]" class="selectize"></select></span>');
                                // valueColumn.find('.wf_attributes select').selectize({render: {item: helper.selectize_render_item,}});
                                // noinspection JSUnresolvedVariable
                                valueColumn.append('<span style="font-size:x-small;"><a style="color: red" href="http://webappick.helpscoutdocs.com/article/19-how-to-map-store-category-with-merchant-category" target="_blank">' + opts.learn_more + '</a></span>');
                                if( ! googleCategories ) {
                                    valueColumn.append('<span class="spinner is-active" style="margin: 0;"></span>');
                                }
                                // noinspection ES6ConvertVarToLetConst
                                var select = valueColumn.find('.wf_attributes select');
                                helper.selectize( select, {
                                    preload: true,
                                    placeholder: opts.form.select_category,
                                    load: function( query, cb ) {
                                        if( ! googleCategories ) {
                                            wpAjax.send('get_google_categories', {
                                                type: 'GET',
                                                data: {
                                                    _ajax_nonce: opts.nonce,
                                                    action: "get_google_categories",
                                                    provider: provider
                                                }
                                            }).then(function (r) {
                                                googleCategories = r;
                                                cb( googleCategories );
                                                valueColumn.find('.spinner').remove();
                                            }).fail(helper.ajax_fail);
                                        } else {
                                            cb( googleCategories );
                                        }
                                    }
                                } );
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
                    .trigger('change');
                $('.outputType').fancySelect();
                $(document).trigger(new jQuery.Event('feedEditor.init', {target: this.form}));
            },
            /**
             * Render Merchant info ajax response and handle allowed feed type for selected merchant
             * @param {jQuery|HTMLElement} merchantInfo jQuery dom object
             * @param {jQuery|HTMLElement} feedType     jQuery dom object
             * @param {Object} r            ajax response object
             */
            renderMerchantInfo: function( merchantInfo, feedType, r ) {
                // noinspection ES6ConvertVarToLetConst
                for( var k in r ) {
                    if( r.hasOwnProperty( k ) ) {
                        merchantInfo.find( '.merchant-info-section.' + k + ' .data' ).html( r[k] ); // phpcs:ignore WordPressVIPMinimum.JS.HTMLExecutingFunctions.html
                        if( 'feed_file_type' === k ) {
                            // noinspection ES6ConvertVarToLetConst,JSUnresolvedVariable
                            var types = r[k].split(",").map(function(t){return t.trim().toLowerCase()}).filter(function(t){
                                // noinspection JSUnresolvedVariable
                                return t !== '' && t !== opts.na.toLowerCase()
                            });
                            if( types.length ) {
                                feedType.find('option').removeAttr('selected').each( function(){
                                    // noinspection ES6ConvertVarToLetConst
                                    var opt = $(this);
                                    opt.val() && ! helper.in_array(opt.val(),types) ? opt.disabled( ! 0) : opt.disabled( ! 1);
                                } );
                                if( types.length === 1 ) feedType.find('option[value="' + types[0] + '"]').attr( 'selected', 'selected' );
                            } else feedType.find('option').disabled( ! 1 );
                        }
                    }
                }
                merchantInfo.find( '.spinner' ).removeClass( 'is-active' );
                feedType.disabled( ! 1 );
                feedType.trigger('change');
                feedType.parent().find('.spinner').removeClass( 'is-active' );
            },
            /**
             * Render Feed Template Tabs and settings while creating new feed.
             * @param {jQuery|HTMLElement} feedForm     feed from query dom object
             * @param {object} r            merchant template ajax response object
             */
            renderMerchantTemplate: function( feedForm, r ) {
                // noinspection ES6ConvertVarToLetConst
                for ( var k in r ) {
                    if ( r.hasOwnProperty( k ) ) {
                        if ( 'tabs' === k ) {
                            feedForm.html( r[k]); // phpcs:ignore WordPressVIPMinimum.JS.HTMLExecutingFunctions.html
                        } else {
                            // noinspection ES6ConvertVarToLetConst
                            var contentSettings = $('[name="'+k+'"]');
                            if ( contentSettings.length ) {
                                contentSettings.each( function() {
                                    // noinspection ES6ConvertVarToLetConst
                                    var elem = $(this);
                                    if ( elem.is( 'select' ) ) {
                                        elem.find( '[value="'+r[k]+'"]').prop( 'selected', true );
                                    } else if ( ( elem.is('[type=checkbox]') || elem.is('[type=radio]') ) && elem.val() === r[k] ) {
                                        elem.prop( 'checked', true )
                                    } else {
                                        elem.val( r[k] ); // type=text
                                    }
                                } ).trigger('change');
                            }
                        }
                    }
                }
                feedEditor.init();
            }
        }, // Feed Editor Table
        merchantInfoCache = [],
        merchantTemplateCache = [];
    // expose to the global scope
    window.wf = { helper, feedEditor };
    $(window).load(function () {
        $copyBtn = $('.toClipboard');

        // Template loading ui conflict
        if( $(location). attr("href").match( /webappick.*feed/g ) != null ) {
            $('#wpbody-content').addClass('woofeed-body-content');
        }

        $('[data-toggle_slide]').on('click', function(e) {
            e.preventDefault();
            $($(this).data('toggle_slide')).slideToggle('fast');
        });
        postboxes.add_postbox_toggles(pagenow);
        if( ! ClipboardJS.isSupported() || /iPhone|iPad/i.test(navigator.userAgent) ) {
            $copyBtn.find('img').hide(0);
        } else {
            $copyBtn.each( function(){
                $(this).on( 'mouseleave', clearTooltip ).on( 'blur', clearTooltip );
            } );
            clipboard = new ClipboardJS('.toClipboard');
            clipboard.on( 'error', function( event ) { showTooltip( event.trigger, fallbackMessage( event.action ) ) } )
                .on( 'success', function( event ) { showTooltip( event.trigger, 'Copied!' ) } );
        }
        // initialize editor
        feedEditor.init();
        helper.selectize(); // render all other selectize

        // Generate Feed Add Table Row
        $(document).on('click', '#wf_newRow', function () {
            $("#table-1 tbody tr:first").clone().find('input').val('').end().find("select:not('.wfnoempty')").val('').end().insertAfter("#table-1 tbody tr:last");
            $('.outputType').each(function (index) {
                //do stuff to each individually.
                $(this).attr('name', "output_type[" + index + "][]"); //sets the val to the index of the element, which, you know, is useless
            });
        });

        // XML Feed Wrapper
        $('#feedType,#provider').on('change', function () {
            // noinspection ES6ConvertVarToLetConst,
            var type = $('#feedType').val(), provider = $('#provider').val(), itemWrapper = $('.itemWrapper'), wf_csv_txt = $('.wf_csvtxt');
            // noinspection JSUnresolvedVariable
            if ( type === 'xml' ) {
                itemWrapper.show();
                wf_csv_txt.hide();
            } else if ( type === 'csv' || type === 'txt' ) {
                itemWrapper.hide();
                wf_csv_txt.show();
            } else if ( type === '' ) {
                itemWrapper.hide();
                wf_csv_txt.hide();
            }
            if( type !== '' && helper.in_array( provider, ['google', 'facebook', 'pinterest'] ) ) {
                itemWrapper.hide();
            }
        }).trigger( 'change' );
        // Tooltip only Text
        {
            $('.wfmasterTooltip').hover(function () {
                // Hover over code
                // noinspection ES6ConvertVarToLetConst
                var title = $(this).attr('wftitle');
                $(this).data('tipText', title).removeAttr('wftitle');
                $('<p class="wftooltip"></p>').text(title).appendTo('body').fadeIn('slow');
            }, function () {
                // Hover out code
                $(this).attr('wftitle', $(this).data('tipText'));
                $('.wftooltip').remove();
            }).mousemove(function (e) {
                $('.wftooltip').css({top: e.pageY + 10, left: e.pageX + 20})
            });
        }

        // Attribute type selection for dynamic attribute
        $(document).on('change', '.dType', function () {
            // noinspection ES6ConvertVarToLetConst
            var type = $(this).val(), row = $(this).closest('tr');
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
        });

        // Generate Feed Table Row Delete
        $(document).on('click', '.delRow', function ( e ) {
            e.preventDefault();
            $(this).closest('tr').remove();
        });

        // Generate Feed Form Submit
        $(".generateFeed").validate();
        $(document).on('submit', '#generateFeed', function () {
            //event.preventDefault();
            // Feed Generating form validation
            $(this).validate();
            if ($(this).valid()) {
                $(".makeFeedResponse").show().html( '<b style="color: darkblue;"><i class="dashicons dashicons-sos wpf_spin"></i> ' + opts.form.generate + '</b>' ); // phpcs:ignore WordPressVIPMinimum.JS.HTMLExecutingFunctions.html, WordPressVIPMinimum.JS.StringConcat.Found
            }
        });

        // Generate Update Feed Form Submit
        $(".updatefeed").validate();
        $('[name="save_feed_config"]').on( 'click', function( e ) {
            e.preventDefault();
            $('#updatefeed').trigger( 'submit', { save: true } );
        } );
        $(document).on('submit', '#updatefeed', function ( e, data ) {
            // Feed Generating form validation
            $(this).validate();
            if ( $(this).valid() ) {
                $(".makeFeedResponse").show().html( '<b style="color: darkblue;"><i class="dashicons dashicons-sos wpf_spin"></i> ' + ( data && data.save ? opts.form.save : opts.form.generate ) + '</b>' ); // phpcs:ignore WordPressVIPMinimum.JS.HTMLExecutingFunctions.html, WordPressVIPMinimum.JS.StringConcat.Found
            }
        });

        // Get Merchant View
        $("#provider").on('change', function ( event ) {
            event.preventDefault();
            if( ! $(this).closest('.generateFeed').hasClass('add-new') ) return; // only for new feed.
            // noinspection ES6ConvertVarToLetConst
            var merchant = $(this).val(),
                feedType = $("#feedType"),
                feedForm = $("#providerPage"),
                merchantInfo = $('#feed_merchant_info');
            // set loading..
            // noinspection JSUnresolvedVariable
            feedForm.html('<h3><span style="float:none;margin: -3px 0 0;" class="spinner is-active"></span> ' + opts.form.loading_tmpl + '</h3>'); // phpcs:ignore WordPressVIPMinimum.JS.HTMLExecutingFunctions.html, WordPressVIPMinimum.JS.StringConcat.Found
            merchantInfo.find( '.spinner' ).addClass( 'is-active' );
            feedType.disabled( ! 0 ); // disable dropdown
            feedType.parent().find('.spinner').addClass( 'is-active' );
            merchantInfo.find( '.merchant-info-section .data' ).html(''); // remove previous data
            // Get Merchant info for selected Provider/Merchant
            if( merchantInfoCache.hasOwnProperty( merchant ) ) {
                feedEditor.renderMerchantInfo( merchantInfo, feedType, merchantInfoCache[merchant] );
            } else {
                wpAjax.send( 'woo_feed_get_merchant_info', {
                    type: 'GET',
                    data: { nonce: opts.nonce, provider: merchant }
                } ).then( function( r ) {
                    merchantInfoCache[merchant] = r;
                    feedEditor.renderMerchantInfo( merchantInfo, feedType, r );
                } ).fail( helper.ajax_fail );
            }

            // Get FeedForm For Selected Provider/Merchant
            if( merchantTemplateCache.hasOwnProperty( merchant ) ) {
                feedEditor.renderMerchantTemplate( feedForm, merchantTemplateCache[merchant] );
            } else {
                wpAjax.post( 'get_feed_merchant', {
                    _ajax_nonce: opts.nonce, merchant: merchant
                }, )
                    .then( function( r ) {
                        merchantTemplateCache[merchant] = r;
                        feedEditor.renderMerchantTemplate( feedForm, r );
                    } ).fail( helper.ajax_fail );
            }
        });

        // Feed Active and Inactive status change via ajax
        $('.woo_feed_status_input').on('change',function(){
            // noinspection ES6ConvertVarToLetConst
            var  $feedName = $(this).val(), counter = ( $(this)[0].checked ) ? 1 : 0;
            wpAjax.post( 'update_feed_status', { _ajax_nonce: opts.nonce, feedName: $feedName, status: counter }, );
        });

        // Adding for Copy-to-Clipboard functionality in the settings page
        $("#woo_feed_settings_error_copy_clipboard_button").on('click', function() {
            $('#woo_feed_settings_error_report').select();
            document.execCommand('copy');
            if (window.getSelection) {window.getSelection().removeAllRanges();}
            else if (document.selection) {document.selection.empty();}
        });

        //Checking whether php ssh2 extension is added or not
        $(document).on('change', '.ftporsftp', function () {
            // noinspection ES6ConvertVarToLetConst
            var server = $(this).val(), status = $('.ssh2_status');
            if (server === 'sftp') {
                // noinspection JSUnresolvedVariable
                status.show().css('color', 'dodgerblue').text(opts.form.sftp_checking);
                // noinspection JSUnresolvedVariable
                wpAjax.post('get_ssh2_status', {_ajax_nonce: opts.nonce, server: server})
                    .then(function (response) {
                        if ( response === 'exists' ) {
                            // noinspection JSUnresolvedVariable
                            status.css('color', '#2CC185').text(opts.form.sftp_available);
                            setTimeout( function () {
                                status.hide();
                            }, 1500 );
                        } else {
                            // noinspection JSUnresolvedVariable
                            status.show().css('color', 'red').text(opts.form.sftp_warning);
                        }
                    })
                    .fail( function( e ) {
                        status.hide();
                        helper.ajax_fail( e );
                    } );
            } else {
                status.hide();
            }
        });
    });
}( jQuery, window, document, wp.ajax, wpf_ajax_obj ));