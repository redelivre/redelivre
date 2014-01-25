/**
 * Theme Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */

( function( $ ) {
	wp.customize( 'et_origin[sidebar_bg_color]', function( value ) {
		value.bind( function( to ) {
			$( '#info-bg' ).css( 'background', to );
			$( '#top-menu a:hover .link_text, .current-menu-item > a, #top-menu .current-menu-item > a:hover, #top-menu .current-menu-item > a:hover .link_bg, .et_active_dropdown > li a, #top-menu .et_clicked, #mobile-nav' ).css( 'color', to );
		} );
	} );

	wp.customize( 'et_origin[sidebar_borders_color]', function( value ) {
		value.bind( function( to ) {
			$( '.widget, #top-menu a, #mobile-nav, #info-area, #info-bg, #top-menu' ).css( 'border-color', to );
		} );
	} );

	wp.customize( 'et_origin[sidebar_active_link_bg]', function( value ) {
		value.bind( function( to ) {
			$( '.current-menu-item > a, .et_active_dropdown > li a, #top-menu .et_clicked, #mobile-nav, #top-menu .link_bg, #top-menu .current-menu-item > a:hover, #top-menu .current-menu-item > a:hover .link_bg' ).css( 'background', to );
		} );
	} );

	wp.customize( 'et_origin[sidebar_dropdown_link_bg]', function( value ) {
		value.bind( function( to ) {
			$( '#top-menu ul ul .link_bg' ).css( 'background', to );
		} );
	} );

	wp.customize( 'et_origin[color_schemes]', function( value ) {
		value.bind( function( to ) {
			var $body = $( 'body' ),
				body_classes = $body.attr( 'class' ),
				et_customizer_color_scheme_prefix = 'et_color_scheme_',
				body_class;

			body_class = body_classes.replace( /et_color_scheme_[^\s]+/, '' );
			$body.attr( 'class', $.trim( body_class ) );

			if ( 'none' !== to  )
				$body.addClass( et_customizer_color_scheme_prefix + to );
		} );
	} );
} )( jQuery );