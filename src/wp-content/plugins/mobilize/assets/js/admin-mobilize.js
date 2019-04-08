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

		var linkID = 0;
		jQuery('#mobilize-links-add').click(function() {
			if (jQuery('#mobilize-links-text').val() &&
					jQuery('#mobilize-links-url').val())
			{

				var ul = jQuery('<li></li>').appendTo(jQuery('#mobilize-links-list'));

				text = jQuery('<input type="hidden">').appendTo(ul);
				text.val(jQuery('#mobilize-links-text').val());
				text.attr('name', 'mobilize[links][' + linkID + '][text]');
				text = jQuery('<input type="hidden">').appendTo(ul);
				text.val(jQuery('#mobilize-links-url').val());
				text.attr('name', 'mobilize[links][' + linkID + '][url]');
				text = jQuery('<input type="hidden">').appendTo(ul);
				text.val(jQuery('#mobilize-links-description').val());
				text.attr('name', 'mobilize[links][' + linkID + '][description]');
				linkID += 1;

				button = jQuery('<input type="button" value="X">').appendTo(ul);
				ul.append(jQuery('<div/>').text(
							jQuery('#mobilize-links-text').val()).html());

				button.click(function() {
					jQuery(this).parent().remove();
				});

				jQuery('#mobilize-links-text').val('');
				jQuery('#mobilize-links-url').val('');
				jQuery('#mobilize-links-description').val('');
			}
		});

		jQuery('#mobilize-links-list input').click(function() {
			jQuery(this).parent().remove();
		});
});
