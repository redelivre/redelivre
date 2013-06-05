jQuery(document).ready(function($){
	$('div.price-livre input').priceFormat({
	    prefix: 'R$ ',
	    centsSeparator: ',',
	    thousandsSeparator: '.'
	});

	$('.link-moip1').click(function(){
		$('.form-moip1').submit();
		return false;
	});

	$('.link-moip2').click(function(){
		$('.form-moip2').submit();
		return false;
	});

	$('.link-moip3').click(function(){
		$('.form-moip3').submit();
		return false;
	});

	$('.link-moip4').click(function(){
		$('.form-moip4').submit();
		return false;
	});

	$('.valor-livre-input').keyup(function(){
		var value = $(this).val();
		$('.valor-livre-output').val(value.replace('R$', '').replace(' ', '').replace('.', '').replace(',', ''));
	});
});