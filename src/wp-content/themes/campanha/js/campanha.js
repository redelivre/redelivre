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
    });
})(jQuery);






































