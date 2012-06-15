(function($){
    $(document).ready(function() {
        // customize strings from my sites admin menu        
        a = $('#wp-admin-bar-my-sites a').first();
        if (a.text() == 'Meus sites') {
            a.text('Minhas campanhas');
        }      
        
        li = $('#wp-admin-bar-blog-1');
        a = li.find('a').first();
        if (a.text() == 'Campanha Completa') {
            a.text('Administrar campanhas');
            a.css('background-image', 'url()');
            li.find('.ab-sub-wrapper').remove();
        }
    });
})(jQuery);