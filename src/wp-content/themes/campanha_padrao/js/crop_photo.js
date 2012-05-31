(function($){
    $(document).ready(function() {
        $('#save-position').click(function() {
            var left = $('#photo-wrapper img').css('left');
            var top = $('#photo-wrapper img').css('top');
            var width = $('#photo-wrapper img').css('width');
            $.post(
                ajaxurl,
                {action: 'savePhotoPosition', filename: 'foto1', left: left, top: top, width: width},
                function(result) {
                    $("#save-response").show().delay(1000).fadeOut(2000);
                }
            );
        });
        
        $("#photo-wrapper").css({
            width:200,
            height:300,
            overflow: 'hidden'
        }).mouseover(function() {
            $('#zoom-plus, #zoom-minus').show();
        }).mouseout(function() {
            $('#zoom-plus, #zoom-minus').hide();
        });
        
        $("#photo-wrapper img").css({
            cursor: 'move',
            zIndex:1
        }).draggable();
        
        var zoom_interval;
        $(document).mouseup(function() {
            clearInterval(zoom_interval);
        });
        
        $("#zoom-plus").mousedown(function() {
            zoom_interval = setInterval(function() {
                var w = parseInt($('#photo-wrapper img').css('width')) + 2
                $('#photo-wrapper img').css('width', w);
            }, 20);
        }).disableSelection();
        
        $("#zoom-minus").mousedown(function() {
            zoom_interval = setInterval(function() {
                if ($('#photo-wrapper img').width() <= $('#photo-wrapper').width() || $('#photo-wrapper img').height() <= $('#photo-wrapper').height()) {
                    return;
                }
                
                var w = parseInt($('#photo-wrapper img').css('width')) - 2
                $('#photo-wrapper img').css('width', w);
            }, 20);
        }).disableSelection();
    });
})(jQuery);
