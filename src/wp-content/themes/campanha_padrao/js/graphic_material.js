(function($){
    $(document).ready(function() {
        // update image preview whenever a value is changed
        $('#graphic_material_form :input:not(:hidden)').each(function() {
            $(this).change(function() {
                updatePreview();
            });
        });
        
        $('#color_0').bind('colorpicked', function () {
            updatePreview();
        });
        
        // check if browser support SVG
        if (!document.implementation.hasFeature("http://www.w3.org/TR/SVG11/feature#Shape", "1.0")) {
            $('#graphic_material_content').hide();
            $('#svg_not_supported').show();
        }
    });
    
    function updatePreview() {
        $("body").css("cursor", "wait");
        $.ajax({
            url: ajaxurl,
            type: 'get',
            data: $('#graphic_material_form').serialize(),
            success: function(data) {
                $('#image_preview').html(data);
                $("body").css("cursor", "auto");
            } 
        });
    }
})(jQuery);