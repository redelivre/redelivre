(function($){
    $(document).ready(function() {
        // customize strings from my sites admin menu        
        a = $('#wp-admin-bar-my-sites a').first();
        if (a.text() == 'Meus sites') {
            a.text('Meus projetos');
            a.removeAttr('href');
        }      
        
        li = $('#wp-admin-bar-blog-1');
        a = li.find('a').first();
        if (a.text() == 'Redelivre') {
            a.text('Administrar projetos');
            a.css('background-image', 'url()');
            li.find('.ab-sub-wrapper').remove();
        }
    });
})(jQuery);
