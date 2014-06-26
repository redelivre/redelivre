jQuery(function(){
    jQuery('div.section').each(function(){
        if (jQuery(this).find('h3 input').is(':checked')) {
            jQuery(this).find('.section-content').show();
        }
        else {
            jQuery(this).find('.section-content').hide();
        }
    });

    jQuery('h3 input').click(function(){
        if (jQuery(this).is(':checked')) {
            jQuery('#'+jQuery(this).data('section')+' .section-content').slideDown();
        }
        else{
            jQuery('#'+jQuery(this).data('section')+' .section-content').slideUp();
        }
    });

    // jQuery( "#sortable" ).sortable();
});