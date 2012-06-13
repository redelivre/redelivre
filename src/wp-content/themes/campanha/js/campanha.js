(function($){
    $(document).ready(function(){
        $('#form-cadastre-se input').blur(function() {
            if ($(this).val() != '') {
                $(this).addClass('hasvalue');
            } else {
                $(this).removeClass('hasvalue');
            }
        });
        
        $('#form-cadastre-se input').each(function(index) {
            id = $(this).attr('id');
            if ((id == 'userinput' || id == 'emailinput' || id == 'password') && $(this).val() != '') {
                $(this).addClass('hasvalue');
            }
        });

        // update cities field when creating a new campaign        
        $('#state').change(function() {
            if ($('#state').val() != '')
                $('#city').html('<option value="">Carregando...</option>');
                
            $.ajax({
                url: ajaxurl,
                type: 'get',
                data: {action: 'campanha_get_cities_options', uf: $('#state').val()},
                success: function(data) {
                    $('#city').html(data);
                } 
            });
        });
        
        
        // carrocel da home
        if($('#home-main-section').length){
            var locked = false;
            // BOTAO PREVIOUS
            $('#home-main-section #prev').click(function(){
                if(locked) return;
                locked = true;
                
                var $current = $('#janela article.current'), $prev;
                
                // se a frase que está aberta é a primeira frase, a frase anterior será a ultima frase
                if($current.is($('#janela article:first').get(0))){
                    $prev = $('#janela article:last');
                    $current.slideUp();
                }else{
                    $prev = $current.prev();
                }
                
                $prev.addClass('current').slideDown(function(){ locked = false; });
                $current.removeClass('current').slideUp();
                
                $("#features h3").removeClass('active');
                $("#features h3."+$prev.attr('id')).addClass('active');
            });
            
            // BOTAO NEXT
            $('#home-main-section #next').click(function(){
                if(locked) return;
                locked = true;
                
                var $current = $('#janela article.current'), $next;
                
                // se a frase que está aberta é a ultima frase, a frase anterior será a primeira frase
                if($current.is($('#janela article:last').get(0))){
                    $next = $('#janela article:first');
                }else{
                    $next = $current.next();
                }
                
                $next.addClass('current').slideDown(function(){ locked = false; });
                $current.removeClass('current').slideUp();
                
                $("#features h3").removeClass('active');
                $("#features h3."+$next.attr('id')).addClass('active');
            });
            
            $('#features h3').click(function(){
                if($(this).hasClass('active'))
                    return;
                
                var $current = $('#janela article.current'), $clicked = $("#"+$(this).data('frase'));
                
                $("#features h3").removeClass('active');
                $(this).addClass('active');
                
                $clicked.addClass('current').slideDown();
                $current.removeClass('current').slideUp();
            });
        }
    });
})(jQuery);
