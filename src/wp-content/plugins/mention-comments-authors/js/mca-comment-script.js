jQuery(document).ready(function($){

    $comment = $(mcaCommentTextarea);
    if($comment.length > 0){
        //ADD AUTOSUGGEST
        var customItemTemplate = "<div><span />&nbsp;<small /></div>";

        function elementFactory(element, e) {
            var template = $(customItemTemplate).find('span')
                                                .text('@' + e.val).end()
                                                .find('small')
                                                .text("(" + (e.meta || e.val) + ")").end();
            element.append(template);
        };

        $comment.sew({values: mcaAuthors, elementFactory: elementFactory});

        //SCROLL TO LAST COMMS
        $('.mca-button').on('click',function(){
            $('.mca-fired').removeClass('mca-fired');
            $('.mca-prevent-elem').removeClass('mca-prevent-elem');
            $('.mca-comment-text-wrapper').removeClass('mca-comment-text-wrapper');

            var target = $(this).attr('data-target');
            var $elems = $('.mca-author');

            var $ishim = null;
            var elemPassed = false;

            $(this).parents('.mca-author').addClass('mca-fired');

            $elems.each(function(index){
                if ( $(this).hasClass('mca-fired') ) {
                    elemPassed = true;
                }
                if( ( elemPassed && $ishim ) || index == $elems.length-1){
                    $ishim.addClass('mca-prevent-elem').parent().addClass('mca-comment-text-wrapper');
                    $('body,html').animate({scrollTop:$ishim.offset().top-200}, 200);
                    return false;
                }
                if($(this).attr('data-name') == target)
                    $ishim = $(this);
            });
        });
    }
});