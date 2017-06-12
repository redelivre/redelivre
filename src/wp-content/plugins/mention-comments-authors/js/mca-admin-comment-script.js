jQuery( document ).ready( function( $ ) {

    function mcaAdminClick( comment_id, comment_post_id ) {
        $.ajax( {
            url  : ajaxurl,
            type : 'POST',
            data : {
                action : 'mca_admin_get_previous_commentators',
                comment_id : comment_id,
                comment_post_id : comment_post_id,
            }
        } ).done( function( data ) {
            if ( data.success ) {
                $comment = $('#replycontent');
                if ( $comment.length > 0 ) {
                    //ADD AUTOSUGGEST
                    var customItemTemplate = "<div><span />&nbsp;<small /></div>";

                    function elementFactory( element, e ) {
                        var template = $( customItemTemplate ).find('span')
                                                            .text('@' + e.val).end()
                                                            .find('small')
                                                            .text("(" + (e.meta || e.val) + ")").end();
                        element.append(template);
                    };
                    $comment.sew( { values: data.data, elementFactory: elementFactory } );
                }
            }
        } );
    }

    commentReply.NormalCommentReplyOpen = commentReply.open;
    commentReply.open = function( comment_id, post_id, action ) {
        commentReply.NormalCommentReplyOpen( comment_id, post_id, action );
        mcaAdminClick( comment_id, post_id );
    }

    commentReply.NormalCommentReplyAddcomment = commentReply.addcomment;
    commentReply.addcomment = function( post_id ) {
        commentReply.NormalCommentReplyAddcomment( post_id );
        mcaAdminClick( false, post_id );
    }

    // commentReply.addcomment = function( post_id ) {
    //     $('#add-new-comment').fadeOut(200, function(){
    //         commentReply.open(0, post_id, 'add');
    //         $('table.comments-box').css('display', '');
    //         $('#no-comments').remove();
    //     });
    //     mcaAdminClick( false, post_id );
    // }
} );