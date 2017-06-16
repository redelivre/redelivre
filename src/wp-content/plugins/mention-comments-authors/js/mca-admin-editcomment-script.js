jQuery( document ).ready( function( $ ) {

    $comment = $('#content');
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
        $comment.sew( { values: oldAuthors, elementFactory: elementFactory } );
    }
} );