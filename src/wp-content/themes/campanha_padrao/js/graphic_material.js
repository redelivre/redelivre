(function($){
    $(document).ready(function() {
        // update image preview whenever a value is changed
        $('#graphic_material_form :input[type=radio]').each(function() {
            $(this).change(function() {
                updatePreview();
            });
        });
        
        $('#color_0').bind('colorpicked', function () {
            updatePreview();
        });
    });
    
    function updatePreview() {
        $.ajax({
            url: ajaxurl,
            type: 'get',
            data: $('#graphic_material_form').serialize(),
            success: function(data) {
                $('#image_preview').html(data);
            } 
        });
    }
})(jQuery);