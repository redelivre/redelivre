
jQuery(document).ready(function() {
	jQuery('#wp-easy-data-table').sortable(
			{
				items			: 'tr.sortable',
				placeholder		: 'dragAjuda',
				activeclass 	: 'dragAtivo',
				hoverclass 		: 'dragHover',
				handle			: 'td.drag',
				opacity			: 0.7,
				update 		    : function() {
					var i = sortableOffset;
					jQuery('tr.sortable:not(".ui-sortable-helper")').each(function() {
                    	  jQuery(this).find('input.item_order').val(i);
                    	  i ++;
                    });        
                },
				onStart         : function()
				{
					jQuery.iAutoscroller.start(this, document.getElementsByTagName('body'));
				},
				onStop          : function()
				{
					jQuery.iAutoscroller.stop();
				}
			});
	
	var sortableOffset = jQuery('#sortable_init_offset').val();
	
	jQuery('tr.sortable').hover(function() {
		jQuery(this).find('.item_order').show();
	}, function() {
		jQuery(this).find('.item_order').hide();
	});
	
});
