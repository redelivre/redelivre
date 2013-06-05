$(function(){
	$('.moip-check').on('click', function(){
		var el = $('.content-moip');

		if($('.moip-check input[type=checkbox]').is(':checked')){
			$('.content-moip').fadeIn('fast');
		}
		else{
			$('.content-moip').fadeOut('fast');
		}
	});

	$('.contribuicao').on('click', function(){
		if($(this).children('input[type=checkbox]').is(':checked')){
			$(this).next().fadeIn('fast');
		}
		else{
			$(this).next().fadeOut('fast');
		}
	});

	$('.price').priceFormat({
	    prefix: 'R$ ',
	    centsSeparator: ',',
	    thousandsSeparator: '.'
	});

	$('.colorSelector1').ColorPicker({
		color: $('.colorSelector1 input').val(),
		onShow: function (colpkr) {
			if(!$(colpkr).is(':visible')){
				$(colpkr).fadeIn(500);
			}

			return false;
		},
		onHide: function (colpkr) {
			if($(colpkr).is(':visible')){
				$(colpkr).fadeOut(500);
			}

			return false;
		},
		onChange: function (hsb, hex, rgb) {
			$('.colorSelector1 div').css('backgroundColor', '#' + hex);
			$('.colorSelector1 input').val('#' + hex);
		}
	});

	$('.colorSelector2').ColorPicker({
		color: $('.colorSelector2 input').val(),
		onShow: function (colpkr) {
			if(!$(colpkr).is(':visible')){
				$(colpkr).fadeIn(500);
			}

			return false;
		},
		onHide: function (colpkr) {
			if($(colpkr).is(':visible')){
				$(colpkr).fadeOut(500);
			}

			return false;
		},
		onChange: function (hsb, hex, rgb) {
			$('.colorSelector2 div').css('backgroundColor', '#' + hex);
			$('.colorSelector2 input').val('#' + hex);
		}
	});

	$('.colorSelector3').ColorPicker({
		color: $('.colorSelector3 input').val(),
		onShow: function (colpkr) {
			if(!$(colpkr).is(':visible')){
				$(colpkr).fadeIn(500);
			}

			return false;
		},
		onHide: function (colpkr) {
			if($(colpkr).is(':visible')){
				$(colpkr).fadeOut(500);
			}

			return false;
		},
		onChange: function (hsb, hex, rgb) {
			$('.colorSelector3 div').css('backgroundColor', '#' + hex);
			$('.colorSelector3 input').val('#' + hex);
		}
	});
});;