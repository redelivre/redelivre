/* global _, jQuery */

jQuery( function ( $ ) {

	var fullContainer = $( panelsStyles.fullContainer );
	if ( fullContainer.length === 0 ) {
		fullContainer = $( 'body' );
	}

	// Stretch all the full width rows
	var stretchFullWidthRows = function () {
		var $panelsRow = $( '.siteorigin-panels-stretch.panel-row-style' );
		$panelsRow.each( function () {
			var $$ = $( this );
			
			var stretchType = $$.data( 'stretch-type' );
			var defaultSidePadding = stretchType === 'full-stretched-padded' ? '' : 0;
			
			// Reset all the styles associated with row stretching
			$$.css( {
				'margin-left': 0,
				'margin-right': 0,
				'padding-left': defaultSidePadding,
				'padding-right': defaultSidePadding
			} );

			var leftSpace = $$.offset().left - fullContainer.offset().left,
				rightSpace = fullContainer.outerWidth() - leftSpace - $$.parent().outerWidth();

			$$.css( {
				'margin-left': - leftSpace,
				'margin-right': - rightSpace,
				'padding-left': stretchType === 'full' ? leftSpace : defaultSidePadding,
				'padding-right': stretchType === 'full' ? rightSpace : defaultSidePadding
			} );

			var cells = $$.find( '> .panel-grid-cell' );

			if ( stretchType === 'full-stretched' && cells.length === 1 ) {
				cells.css( {
					'padding-left': 0,
					'padding-right': 0
				} );
			}

			$$.css( {
				'border-left': 0,
				'border-right': 0
			} );
		} );

		if ( $panelsRow.length ) {
			$( window ).trigger( 'panelsStretchRows' );
		}
	}
	$( window ).on( 'resize load', stretchFullWidthRows );
	stretchFullWidthRows();

	// This should have been done in the footer, but run it here just incase.
	$( 'body' ).removeClass( 'siteorigin-panels-before-js' );

} );
