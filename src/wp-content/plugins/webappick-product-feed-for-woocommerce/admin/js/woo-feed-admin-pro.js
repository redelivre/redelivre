// noinspection JSUnresolvedVariable,ES6ConvertVarToLetConst,SpellCheckingInspection
/**!
 * WooFeed Pro Scripts
 * @version 1.0.0
 * @package WooFeed
 * @copyright 2020 WebAppick
 *
 */
(function ($, window, document, wpAjax, opts) {
    "use strict";
    /* global ajaxurl, wpAjax, postboxes, pagenow, alert, deleteUserSetting, typenow, adminpage, thousandsSeparator, decimalPoint, isRtl */
    $(window).load(function () {
        // noinspection ES6ConvertVarToLetConst,SpellCheckingInspection
        var sliders = $('.wapk-slider');
        if( sliders.length ) {
            sliders.slick({
                autoplay: true,
                dots: true,
                centerMode: true,
                arrows: false,
                slidesToShow: 1,
                slidesToScroll: 1,
                lazyLoad: 'progressive'
            });
        }
    } );
}( jQuery, window, document, wp.ajax, wpf_ajax_obj ));