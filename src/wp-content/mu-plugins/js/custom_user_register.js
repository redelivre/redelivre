var jQuery_validator = null;
jQuery(document).ready(function(){
	jQuery("#custom-register-resposta").hide();
	custom_register = new Custom_User_Register();
	jQuery("#custom-register-send").click(function(){
		custom_register.ajax_register();
	});	
	jQuery_validator = jQuery("#registerform").validate();
});

/**
* Classe javascript para o formulário de registo de usuário
*
*/
function Custom_User_Register(){
	
	/**
	* Valida formulário de contato
	*
	*/
	this.valida_form = function(){
		var erros = new Array();
		if(jQuery("#custom-register-username").val() == ""){
			erros.push("<li>É preciso escolher um <strong>nome de usuário</strong></li>");
		}
		
		if(jQuery("#custom-register-realname").val() == ""){
			erros.push("<li>É preciso informar seu <strong>nome completo</strong></li>");	
		}
		
		if(jQuery("#custom-register-password").val() == ""){
			erros.push("<li>É preciso definir uma <strong>senha</strong></li>");
		}
		
		if(jQuery("#custom-register-email").val() == ""){
			erros.push("<li>É preciso informar seu <strong>email</strong></li>");
		}
		
		if(jQuery("#custom-register-password").val() != jQuery("#custom-register-password-review").val()){
			erros.push("<li>As senhas digitadas não conferem</li>");
		}
		var erros_txt = '';
		if(erros.length > 0){
			jQuery("#custom-register-resposta").html("");
			for( i in erros){				
				erros_txt += erros[i];
			}
			
			jQuery("#custom-register-resposta").append('<div class="resposta-erro">' + erros_txt + '</div>');
			jQuery("#custom-register-resposta").slideDown();
			
			return false;
		} else {
			if(jQuery('#registerform').valid())
				return true;
			else
			{
				jQuery_validator.focusInvalid();
				return false;
			}
		}
	}
	
	/**
	* Processa o cadastro de usuário via ajax e envia a resposta para a página.
	*
	*/
	this.ajax_register = function(){
		if(this.valida_form())
		{
			var all_fields = jQuery("input[name*='custom-register-']").map(function()
			{
				return { name:jQuery(this).attr("name").replace("custom-register-",""), value:jQuery(this).val()};
		    }).get();
			
			var data = {
					action: 'custom_register_send',
					username: jQuery("#custom-register-username").val(),
					realname: jQuery("#custom-register-realname").val(),
					password: jQuery("#custom-register-password").val(),
					email: jQuery("#custom-register-email").val(),
					captcha_code: jQuery("#captcha_code").val(),
					all_fields: all_fields
			};
						
			jQuery("#custom-register-resposta").html("Enviando. Por favor, aguarde.");
			jQuery("#custom-register-resposta").slideDown();
			
			jQuery.post(custom_register_ajax.ajaxurl, data, function(response){
				jQuery("#custom-register-resposta").html("<div class='resposta-sucesso'>" + response + '</div>');
				si_captcha_refresh('si_image_reg','reg','/wp-content/plugins/si-captcha-for-wordpress/captcha','/wp-content/plugins/si-captcha-for-wordpress/captcha/securimage_show.php?si_form_id=reg&prefix='); return false;
			});
		}
	}
	
}