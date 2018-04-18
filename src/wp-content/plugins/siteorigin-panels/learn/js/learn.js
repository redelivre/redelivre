jQuery( function( $ ){

	var getVideoEmbed = function( videoId ) {
		return '<iframe src="https://player.vimeo.com/video/' +
			encodeURI( videoId ) +
			'?autoplay=1&title=0&byline=0&portrait=0" width="640" height="360" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
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
				.find( '.video-title' ).html( lesson.title ).end()
				.find( '.main-poster' ).hide().attr( 'src', lesson.poster ).show( ).end()
				.find( '.learn-description' ).html( lesson.description ).end()
				.find( '.form-description' ).html( lesson.form_description ).end()
				.find( 'input[name="lesson_id"]' ).val( lessonId ).end()
				.css({
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

	$( '#siteorigin-learn' ).find( '.main-poster, .play-button, .video-play-info, .video-play-info-text' ).click( function(){
		$( '#siteorigin-learn' )
			.find( '.poster-wrapper' ).hide().end()
			.find( '.video-iframe' ).show().html( getVideoEmbed( $( '#siteorigin-learn .poster-wrapper' ).data( 'video' ) ) );
	} );

    $(document).keyup(function(e) {
		// when escape is pressed
		if ( e.keyCode === 27 && $( '#siteorigin-learn-overlay' ).is( ':visible' ) ) {
			e.preventDefault();
			$( '#siteorigin-learn .learn-close' ).click();
		}
    });
} );
