(function($){
    
    $(document).ready(function(){
    
        $('.slideshow').each(function() {
        
            var selector = '#' + $(this).attr('id');
            
            $(selector + ' img:gt(0)').hide();
            if($(selector + ' img:gt(0)').length > 0)
                setInterval(function(){
                    $(selector + ' :first-child').fadeOut()
                    .next('img').fadeIn()
                    .end().appendTo(selector);
                }, 
                3000);
        
        });
        
    });
    
})(jQuery);
