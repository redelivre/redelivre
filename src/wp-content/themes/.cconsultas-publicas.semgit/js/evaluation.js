jQuery(document).ready(function(){
    // voto do usuario em uma das opcoes da avaliacao de um objeto
    jQuery('#object_evaluation input').live('click', function() {
        var radioButton = jQuery(this);
        jQuery('body').css('cursor', 'progress');
        jQuery.ajax({
            url: consulta.ajaxurl,
            type: 'post',
            data: {action: 'object_evaluation', userVote: jQuery(this).attr('id'), postId: jQuery(this).siblings('#post_id').val() },
            dataType: 'json',
            success: function(data) {
                jQuery('body').css('cursor', 'auto');
                radioButton.closest('li').find('.count_object_votes').html(data.count);
                radioButton.closest('.evaluation_container').html(data.html);
            }
        });
    });
    
    // controla a exibicao da caixa de avaliacao na listagem de objetos
    jQuery('.show_evaluation').click(function() {
        jQuery(this).parent().siblings('.evaluation_container').toggle('slow');
    });
});
