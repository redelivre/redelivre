jQuery(function($) {
	$('.flexslider').flexslider({
		animation: "slide",
		smoothHeight: true,
		easing: 'swing',
		direction: 'horizontal',
		slideshow: false,
		touch: true
	});

	////////////////////////////////////////
	// Fix correção place holder para IE //
	////////////////////////////////////////
	

	if (!Modernizr.input.placeholder) {
		$('input[placeholder], textarea[placeholder]').each(function(){
			var placeValue = $(this).attr('placeholder');
			$(this).val(placeValue);
		});

		$('input[placeholder], textarea[placeholder]').focus(function(){
			$(this).val('');
		});

		$('input[placeholder], textarea[placeholder]').blur(function(){
			var inputvalue = $(this).val();
			var placeValue = $(this).attr('placeholder');

			if (inputvalue == '') {
				$(this).val(placeValue);
			}
		});
	}
});