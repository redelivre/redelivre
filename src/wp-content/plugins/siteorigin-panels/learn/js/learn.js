jQuery( function( $ ){

	var getWistiaEmbed = function( videoId ) {
		return '<iframe src="//fast.wistia.net/embed/iframe/' +
			encodeURI( videoId ) +
			'?autoplay=1" allowtransparency="true" frameborder="0" scrolling="no" class="wistia_embed" name="wistia_embed" allowfullscreen mozallowfullscreen webkitallowfullscreen oallowfullscreen msallowfullscreen width="640" height="360"></iframe>';
	};

	$('body').on( 'click', 'a[href^="#siteorigin-learn-"]', function( e ) {
		var $$ = $(this);
		var lessonId = $$.attr( 'href' ).replace( '#siteorigin-learn-', '' );
		if( lessonId && soLearn.lessons.hasOwnProperty( lessonId ) ) {
			e.preventDefault();
			var lesson = soLearn.lessons[ lessonId ];

			$( '#siteorigin-learn' ).show();
			var dialog = $( '#siteorigin-learn-dialog' );

			// Add all the data
			dialog
				.find( '.video-iframe' ).hide().end()
				.find( '.poster-wrapper' ).data( 'video', lesson.video ).end()
				.find( '.main-poster' ).hide().attr( 'src', lesson.poster ).fadeIn( ).end()
				.find( '.learn-description' ).html( lesson.description ).end()
				.find( '.form-description' ).html( lesson.form_description ).end()
				.find( 'input[name="lesson_id"]' ).val( lessonId ).end();

			dialog.css({
				'margin-top': - dialog.outerHeight() / 2,
				'margin-left': - dialog.outerWidth() / 2,
			});
		}
	} );

	$('body').on( 'mouseover', 'a[href^="#siteorigin-learn-"]', function( e ) {
		// Only do this if the dialog is hidden
		if( $( '#siteorigin-learn' ).is( ':visible' ) ) return;

		var $$ = $(this);
		var lessonId = $$.attr( 'href' ).replace( '#siteorigin-learn-', '' );
		if( lessonId && soLearn.lessons.hasOwnProperty( lessonId ) ) {
			// This preloads the image.
			$( '#siteorigin-learn-dialog .main-poster' ).attr( 'src', soLearn.lessons[ lessonId ].poster );
		}
	} );



	// General actions of the dialog
	$( '#siteorigin-learn-overlay' ).add( '#siteorigin-learn .learn-close' ).click( function(){
		$( '#siteorigin-learn' )
			.hide()
			.find( '.video-iframe' ).empty().hide().end()
			.find( '.poster-wrapper' ).show();
	} );

	$( '#siteorigin-learn form' ).submit( function( e ){
		var $$ = $( this );

		// Check the content
		var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		var email = $$.find( 'input[name="email"]' );

		if( email.val() === '' || ! re.test( email.val() ) ) {
			e.preventDefault();
			alert( $$.data( 'email-error' ) );
			return;
		}

		$( '#siteorigin-learn .learn-close' ).click();
	} );

	$( '#siteorigin-learn' ).find( '.main-poster, .play-button' ).click( function(){
		$( '#siteorigin-learn' )
			.find( '.poster-wrapper' ).hide().end()
			.find( '.video-iframe' ).show().html( getWistiaEmbed( $( '#siteorigin-learn .poster-wrapper' ).data( 'video' ) ) );
	} );
} );
