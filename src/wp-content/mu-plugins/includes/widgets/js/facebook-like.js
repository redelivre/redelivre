function facebook_like_form_auto_height(input_select, input_height)
{
	if(jQuery('#'+input_select).val() == 'false')
	{
		jQuery('#'+input_height).val('0');
	}
}

function facebook_like_form_auto_height_init(input_select, input_height)
{
	jQuery("#"+input_select).change(function () {
		facebook_like_form_auto_height(input_select, input_height);
	});
}

jQuery(document).ready(function() {
	jQuery('.FacebookLikeBox').each(function(){
		var iffb = jQuery(this).contents("iframe");
		iffb.attr('src', iffb.attr('src').replace('wXXXXw', 'width='+jQuery(this).width()));
	});
});