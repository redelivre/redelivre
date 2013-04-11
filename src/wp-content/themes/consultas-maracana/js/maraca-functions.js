jQuery(document).ready(function(){
	js_maraca = new JS_Maraca();
	jQuery(".register-form .bt-enviar").live('click', function(){
                jQuery('#status-ajax').addClass('working');
		js_maraca.ajax_register_form();
	});
});

function JS_Maraca(){
	
	this.ajax_register_form = function(){
		
		data = {
			action: 'register_form_save',
			formdata: jQuery(".register-form").serialize()
		}
		
		jQuery.post(wpajax.url, data, function(response){
                        jQuery('#status-ajax').removeClass('working');
			jQuery(".register-form-container").html(response);
		});
	};
	
	
}