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
    
    // Ação para capturar o click dos links de concordo e não concordo das propostas rápidas.    
    jQuery('.proposta-concordo, .proposta-naoconcordo').click(function(event) {        
        var id_span_target = jQuery(event.target).attr('href') + '-' +jQuery(event.target).attr('class');
        
        jQuery.ajax({
            beforeSubmit: function() { jQuery(id_span_target).text(''); jQuery(id_span_target).addClass('working'); }, 
            data: { proposta_inline: 'true', tipo_proposta_inline: jQuery(event.target).attr('class'), item_proposta: jQuery(event.target).attr('href'), post_id: jQuery(event.target).attr('data-post') }
        }).done(function(resposta) {
            jQuery(id_span_target).text(resposta);
            jQuery(id_span_target).removeClass('working');
        });
    });    
});
