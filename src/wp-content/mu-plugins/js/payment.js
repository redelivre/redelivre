
jQuery(document).ready(function() {
    jQuery("#payment-checkout-form").validate({
        rules: {					
            card_number: {
                minlength: 16,
                maxlength: 16,
                number: true     				
            },
            cctype: {
                required:true				
            },
            nome_portador: {
                required:true
            },
            card_code: {
                minlength: 3,
                maxlength: 3,
                required:true
            }
        },
        messages: {
            card_number: {       				
                minlength: payment.msg_card_min,
                maxlength: payment.msg_card_max,
                required: payment.msg_card_required
            },
            cctype: {
                required: payment.cctype_required,
                number: payment.cctype_number
            },
            nome_portador: {
                required: payment.nome_required
            },
            card_code: {
                minlength: payment.car_code_length,
                maxlength: payment.car_code_length,
                required: payment.car_code_required
            }			
        },
        errorPlacement: function(error, element) {
            if (element.attr("name") == "cctype")
               error.insertAfter("#spanmastercard");
            else
                error.insertAfter(element);
        }
        
    });

    jQuery("#payment-checkout-form").submit(function() {
        if (jQuery(this).valid()) {
            
            //jQuery(this).hide();
            jQuery('#payment-checkout-loading').show();
            
            request = jQuery.ajax({
                type: 'POST',
                url: payment.ajaxurl,
                //dataType: 'html',
                data: jQuery("#payment-checkout-form").serialize(),
                
                complete: function() {
                    if (request.responseText == 'success') {
                        jQuery('#payment-checkout-loading').hide();
                        jQuery('#payment-checkout-success').show();
                    } else {
                        jQuery('#payment-checkout-loading').hide();
                        jQuery('#payment-checkout-error').show();
                    }
                }
            });
        } 
        
        return false;
    });
});
