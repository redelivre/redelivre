(function($){
    $(document).ready(function() {
        // update image preview whenever a value is changed
        $('#graphic_material_form :input:not(:hidden)').each(function() {
            $(this).change(function() {
                updatePreview();
            });
        });
        
        $('#graphic_material_form :input.mColorPicker').each(function() {
            $(this).bind('colorpicked', function () {
                updatePreview();
            });
        })
        
        // check if browser support SVG
        if (!document.implementation.hasFeature("http://www.w3.org/TR/SVG11/feature#Shape", "1.0")) {
            $('#graphic_material_content').hide();
            $('#svg_not_supported').show();
        }
        
        $('#graphic_material_form :input[name=export]').click(function() {
            $('#graphic_material_form :input[name=action]').val('campanha_export_flyer')
            $.ajax({
                url: ajaxurl,
                type: 'get',
                data: $('#graphic_material_form').serialize(),
                success: function(data) {
                    window.location.replace(data);
                } 
            });
        });
    });
    
    function updatePreview() {
        $("body").css("cursor", "wait");
        $.ajax({
            url: ajaxurl,
            type: 'get',
            data: $('#graphic_material_form').serialize(),
            success: function(data) {
                $('#graphic_material_preview').html('<h2>Pré-visualização</h2>' + data);
                $("body").css("cursor", "auto");
            } 
        });
    }
})(jQuery);