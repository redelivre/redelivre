
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
			'user-email': jQuery("#user-email").val()
		}		
		
		jQuery.post(custom_lost_password_ajax.url, data, function(response){
			jQuery(".resposta-ajax").html(response);
		});
	}
	
}