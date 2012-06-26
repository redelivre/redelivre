(function($){
    $(document).ready(function() {
        $('#save-position').click(function() {
            var left = $('#photo-wrapper img').css('left');
            var top = $('#photo-wrapper img').css('top');
            var filename = $('input[name=graphic_material_filename]').val();
            var minWidth = $('input[name=minWidth]').val();
            var minHeight = $('input[name=minHeight]').val();
            $("body").css("cursor", "wait");
            $.post(
                ajaxurl,
                {action: 'savePhotoPosition', filename: filename, minWidth: minWidth, minHeight: minHeight, left: left, top: top},
                function(result) {
                    $("#save-response").show().delay(1000).fadeOut(2000);
                    updatePreview();
                    $("body").css("cursor", "auto");
                }
            );
        });
        
        $("#photo-wrapper img").css({
            cursor: 'move',
            zIndex:1
        }).draggable();
    });
})(jQuery);
