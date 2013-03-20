jQuery(document).ready(function(){
    jQuery('#interact-comentar, .comment-reply-link').click(function() {
        jQuery('#sugestao_alteracao').attr('checked', false);
        jQuery('#comment_type').hide();
    });
    
    jQuery('#interact-sugerir').click(function() {
        
        jQuery('#sugestao_alteracao').attr('checked', true);
        jQuery('#comment_type').show();
        jQuery.scrollTo('#respond', {duration: 500});
        return false;
        
    });
    
	jQuery('#select-tema').change(function() {
        
        jQuery('#select-acoes').html('Carregando Ações...');
        
        var tema_id = jQuery(this).val();
        
        jQuery.ajax({
            url: consulta.ajaxurl, 
            type: 'post',
            data: {action: 'get_acoes_do_tema', tema: tema_id},
            success: function(data) {
                jQuery('#select-acoes').html(data);
                jQuery('#select-acoes ul.acoes li').click(function() {
                
                    var check = jQuery(this).children('input');
                    if (check.is(':checked')) {
                        check.attr('checked', false);
                        jQuery(this).removeClass('selected');
                    } else {
                        check.attr('checked', true);
                        jQuery(this).addClass('selected');
                    }
                
                });
            } 
        });
        
    }).change();
    
    // TEMPLATE WIDGETS - AJAX
    jQuery('.template-widget-form').live('submit',function(){
        var div_id = jQuery(this).data("div_id");
        jQuery.post(consulta.ajaxurl, jQuery(this).serialize(), function (response) {
            if (response) {
                jQuery('.hl-lightbox-close').click();
                jQuery('#'+div_id).html(response);
            }
        })
        return false;
    });
});
