(function($){
    $(document).ready(function() {
        $('#save-position').click(function() {
            var left = $('#photo-wrapper img').css('left');
            var top = $('#photo-wrapper img').css('top');
            var type = $('input[name=type]').val();
            $("body").css("cursor", "wait");
            $.post(
                ajaxurl,
                {action: 'savePhotoPosition', left: left, top: top, type: type},
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
