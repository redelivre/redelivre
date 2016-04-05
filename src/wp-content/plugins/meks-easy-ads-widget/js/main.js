(function($) {

    $(document).ready(function ($) {
    	
    	/* Add next add */
    	
		$("body").on("click", "a.mks_add_ad", function(e){
			e.preventDefault();
			var widget_holder = $(this).closest('.widget-inside');
			var cloner = widget_holder.find('.mks_ads_clone');
			
			widget_holder.find('.mks_ads_container').append('<li style="margin-bottom: 15px;">'+cloner.html()+'</li>');
			
		});
		

		/* Show/hide custom size */

		$("body").on("click", "input.mks-ad-size", function(e){
			if($(this).val() == 'custom'){
				$(this).parent().next().show();
			} else {
				$(this).parent().next().hide();
			}				
		});


		/* Show/hide rotation speed */

		$("body").on("click", "input.mks-ad-rotate", function(e){
			if($(this).is(":checked")){
				$(this).parent().next().show();
			} else {
				$(this).parent().next().hide();
			}				
		});

	});

})(jQuery);