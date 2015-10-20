(function($) {
    $(document).ready(function ($) {
    	
    	/* Social widget handlers */
    	
    	$("body").on("click", "a.mks_add_social", function(e){
				e.preventDefault();

				var widget_holder = $(this).closest('.widget-inside');
				var cloner = widget_holder.find('.mks_social_clone');
				
				widget_holder.find('.mks_social_container').append('<li>'+cloner.html()+'</li>');
				
			});
			
			$("body").on("click", "a.mks_remove_social", function(e){
				e.preventDefault();
				$(this).closest('li').remove();
			});
		});
		
})(jQuery);