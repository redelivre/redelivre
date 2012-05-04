(function($){
    $(document).ready(function(){
        $('#form-cadastre-se input').blur(function() {
            if ($(this).val() != '')
                $(this).addClass('hasvalue');
            else
                $(this).removeClass('hasvalue');
        });
    });
})(jQuery);






































