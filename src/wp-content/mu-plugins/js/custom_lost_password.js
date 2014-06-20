
jQuery(document).ready(function(){
	
	jQuery("#lost-password-send").click(function(){
		var lost_password = new Custom_Lost_Password();
		lost_password.send();
	});
	jQuery("#lost-password-send-reset").click(function()
	{
		if(jQuery("#pass1").val() == jQuery("#pass2").val() && jQuery("#pass1").length > 6)
		{
			var lost_password = new Custom_Lost_Password_Reset();
			lost_password.send();
		}
	});
	si_captcha_refresh('si_image_reg','reg','/wp-content/plugins/si-captcha-for-wordpress/captcha','/wp-content/plugins/si-captcha-for-wordpress/captcha/securimage_show.php?si_form_id=reg&prefix=');
});

function Custom_Lost_Password(){

	this.send = function(){
		var data = {
			'action': 'custom_lost_password_send',
			'user-email': jQuery("#user-email").val(),
			'captcha_code': jQuery("#captcha_code").val(),
			'si_code_reg' : jQuery("#si_code_reg").val()
		}		
		
		jQuery.post(custom_lost_password_ajax.url, data, function(response){
			jQuery(".resposta-ajax").html(response);
		});
	}
	
}
function Custom_Lost_Password_Reset(){

	this.send = function(){
		var data = {
			'action': 'resetpass',
			'pass1': jQuery("#pass1").val(),
			'pass2': jQuery("#pass2").val(),
			'login': jQuery("#user_login").val(),
			'key': jQuery("#key").val()
		}		
		
		jQuery.post(custom_lost_password_ajax.url, data, function(response)
		{
			//jQuery("#lost-password-send-reset").hide();
			jQuery(".resposta-ajax").html(response);
		});
	}
	
}