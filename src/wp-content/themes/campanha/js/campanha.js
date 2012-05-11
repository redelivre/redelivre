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
    });
})(jQuery);
