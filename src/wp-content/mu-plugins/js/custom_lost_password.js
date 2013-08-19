
jQuery(document).ready(function(){
	
	jQuery("#lost-password-send").click(function(){
		var lost_password = new Custom_Lost_Password();
		lost_password.send();
	});
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