(function($){
	$(document).ready(function(){
		var $comment_form 	= $('#commentform'),
			$mobile_nav 	= $('#mobile-nav'),
			$info_area 		= $('#info-area'),
			$top_menu 		= $('#top-menu');

		$top_menu.find('a').each( function(){
			var $this_link 	= $(this),
				link_text	= $this_link.text(),
				dropdown_sign = '';

			if ( $this_link.siblings('ul').length ) dropdown_sign = '<span>+</span>';

			$this_link.html( '<span class="link_bg"></span>' + '<span class="link_text">' + link_text + dropdown_sign + '</span>' );
		} ).click( function(){
			var $this_link = $(this);

			if ( $this_link.attr('href') == '#' && $this_link.hasClass('et_clicked') ) {
				$this_link.removeClass('et_clicked');

				$this_link.siblings('ul').slideUp( 500 ).removeClass('et_active_dropdown');

				return false;
			}

			if ( $this_link.siblings('ul').length ){
				if ( $this_link.hasClass('et_clicked') ) {
					return;
				} else {
					$this_link.addClass('et_clicked');

					$this_link.siblings('ul').slideDown( 500 ).addClass('et_active_dropdown');

					return false;
				}
			}
		} );

		$mobile_nav.click( function(){
			var $this_item = $(this);

			if ( ! $this_item.hasClass('et_mobile_open') ){
				$top_menu.slideDown( 500 );
				$this_item.addClass('et_mobile_open');
			} else {
				$top_menu.slideUp( 500 );
				$this_item.removeClass('et_mobile_open');
			}
		} );

		$comment_form.find('input:text, textarea').each(function(index,domEle){
			var $et_current_input = jQuery(domEle),
				$et_comment_label = $et_current_input.siblings('label'),
				et_comment_label_value = $et_current_input.siblings('label').text();
			if ( $et_comment_label.length ) {
				$et_comment_label.hide();
				if ( $et_current_input.siblings('span.required') ) {
					et_comment_label_value += $et_current_input.siblings('span.required').text();
					$et_current_input.siblings('span.required').hide();
				}
				$et_current_input.val(et_comment_label_value);
			}
		}).bind('focus',function(){
			var et_label_text = jQuery(this).siblings('label').text();
			if ( jQuery(this).siblings('span.required').length ) et_label_text += jQuery(this).siblings('span.required').text();
			if (jQuery(this).val() === et_label_text) jQuery(this).val("");
		}).bind('blur',function(){
			var et_label_text = jQuery(this).siblings('label').text();
			if ( jQuery(this).siblings('span.required').length ) et_label_text += jQuery(this).siblings('span.required').text();
			if (jQuery(this).val() === "") jQuery(this).val( et_label_text );
		});

		// remove placeholder text before form submission
		$comment_form.submit(function(){
			$comment_form.find('input:text, textarea').each(function(index,domEle){
				var $et_current_input = jQuery(domEle),
					$et_comment_label = $et_current_input.siblings('label'),
					et_comment_label_value = $et_current_input.siblings('label').text();

				if ( $et_comment_label.length && $et_comment_label.is(':hidden') ) {
					if ( $et_comment_label.text() == $et_current_input.val() )
						$et_current_input.val( '' );
				}
			});
		});

		et_apply_actions();

		if ( $('ul.et_disable_top_tier').length ) $("ul.et_disable_top_tier > li > ul").prev("a").attr("href","#");

		if ( $('.pagination .alignleft a').length ){
			$('#wrapper').infinitescroll({
				navSelector		: ".pagination",
				nextSelector	: ".pagination .alignleft a",
				itemSelector	: "#wrapper .post",
				loading: {
					msgText		: et_origin_strings.load_posts,
					finishedMsg	: et_origin_strings.no_posts
				}
			}, function(){ et_apply_actions(); } );
		}

		function et_apply_actions(){
			if ( /Android|Windows Phone OS|iPhone|iPad|iPod/i.test(navigator.userAgent) ) {
				$('.entry-image').click( function( event ){
					var go_to_link 	= false
						$this_image = $(this);

					if ( $this_image.hasClass( 'et_hover' ) ) go_to_link = true;

					$('.entry-image').removeClass('et_hover');
					$this_image.toggleClass('et_hover');

					if ( ! go_to_link ) event.preventDefault();
				} );
			}

			$('.entry-image .readmore').hover(
				function(){
					$(this).find('span').stop(true,true).animate( { 'top' : '-24px', 'opacity' : '0' }, 150, function(){
						$(this).css( { 'top' : '86px' } ).animate( { 'top' : '9px', 'opacity' : '1' }, 150 );
					} );
				}, function(){
					$(this).find('span').css( { 'top' : '9px', 'opacity' : '1' } ).stop(true,true).animate( { 'top' : '46px', 'opacity' : '0' }, 150, function(){
						$(this).css( { 'top' : '-24px', 'opacity' : '1' } ).animate( { 'top' : '9px' }, 150 );
					} );
				}
			);

			$('#ie8 .image-info').css( 'opacity', '0' );

			if ( $('#ie8 .image-info').length ){
				$('.entry-image').hover( function(){
					$(this).find('.image-info').css( 'opacity', '1' );
				}, function(){
					$(this).find('.image-info').css( 'opacity', '0' );
				} );
			}
		};
	});
})(jQuery)