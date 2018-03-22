//WRAP INTO ANOTHER FUNCT TO LAUNCH AT EACH AJAX CHANGE
function mcaAjaxChange(){
    jQuery(document).ready(function($){  

        $comment = $(mcaCommentTextarea);
        if($comment.length > 0){
            //FIND mcaAuthors
            var mcaAuthors = new Array;
            var $elems = $('.mca-author');
            $elems.each(function(index){
                mcaAuthors.push({val:$(this).attr('data-name'),meta:$(this).attr('data-realname')});
            });
            sort_and_unique( mcaAuthors );

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
}
mcaAjaxChange();

function sort_and_unique( my_array ) {
    my_array.sort(value);
    function value(a,b) {
      if (a.val < b.val)
          return -1;
       else if (a.val == b.val)
          return 0;
       else
          return 1;
    }

    for ( var i = 1; i < my_array.length; i++ ) {
        if ( my_array[i]['val'] === my_array[ i - 1 ]['val'] ) {
            my_array.splice( i--, 1 );
        }
    }
    return my_array;
};