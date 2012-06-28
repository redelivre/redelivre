(function($){
    $(document).ready(function() {
        // update image preview whenever a value is changed
        $('#graphic_material_form :input[type!="hidden"]').each(function() {
            $(this).change(function() {
                $('#save-reminder').show();
                updatePreview();
            });
        });
        
        $('#graphic_material_form :input.mColorPicker').each(function() {
            $(this).bind('colorpicked', function () {
                $('#save-reminder').show();
                updatePreview();
            });
        });
        
        $('.shapeItem').click(function() {
            $('#listShapes label').removeClass('active');
            $(this).find('label').addClass('active');
            
        });
        
        updatePreview();
        
    });
})(jQuery);

function updatePreview() {
    jQuery("body").css("cursor", "wait");
    jQuery.ajax({
        url: ajaxurl,
        type: 'get',
        data: jQuery('#graphic_material_form').serialize(),
        success: function(data) {
            if (data) {
                jQuery('#graphic_material_preview').html('<h3>Pré-visualização</h3>' + data.image);
                jQuery('#candidateSize').val(data.candidateSize);
            }
            jQuery("body").css("cursor", "auto");
        },
        dataType: 'json',
    });
}
