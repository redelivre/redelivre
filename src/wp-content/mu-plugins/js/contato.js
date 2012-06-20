(function($) {
    
    $(document).ready(function() {

        // Formulario de Contato
        
        jQuery('#formcontato').validate({

			errorElement: 'p',
			errorPlacement: function(error, element) {
			    error.appendTo( element.parents('#formcontato').find('#error-for-' + element.attr('name')) );
		    },
            submitHandler: function(form) {
                jQuery('#contato-loader').show();
                jQuery('#formcontato .feedback').hide();
                jQuery.post(
                    vars.ajaxurl, jQuery("#formcontato").serialize(),
                    function(response) {
                        jQuery('#contato-loader').hide();
                        if (response.success) {
                            jQuery('#contato-success').fadeIn().delay(8000).fadeOut();
                            jQuery('#formcontato input[type=text], #formcontato textarea').each(function() {
                                jQuery(this).val('');
                            });
                            
                        } else {
                            jQuery('#contato-error').show();
                        }

                    }
                );
                return false;
            },
            rules: {
                nome: 'required',
                email: {
                    email: true,
                    required: true
                },
                //telefone: 'required',
                mensagem: 'required',
                
                
            },
            messages: {
                nome: {
                    required: 'Digite seu nome'
                },
                email: {
                    email: 'Digite um e-mail válido',
                    required: 'Digite um e-mail válido'
                },
                /*
                telefone: {
                    required: 'o telefone é obrigatório'
                },
                */ 
                mensagem: {
                    required: 'Escreva uma mensagem'
                }
            }
        });
        
        
        

    });
    
})(jQuery);

