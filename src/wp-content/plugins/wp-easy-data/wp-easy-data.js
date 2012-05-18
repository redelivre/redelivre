
jQuery(document).ready(function() {

    jQuery("input.wp-easy-data-calendar").datepicker({clickInput:true,firstDay:1});
    
    jQuery("input.wp-easy-data-color").each(function() {
    	var th = this;
    	if (jQuery(th).val()) {
    		var initColor = jQuery(this).val();
    		jQuery(th).css('backgroundColor', initColor);
    	} else {
    		initColor: '#FFFFFF';
    	}
    	jQuery(this).ColorPicker({
        	color: initColor,
        	onShow: function (colpkr) {
    			jQuery(colpkr).fadeIn(500);
        		return false;
        	},
        	onHide: function (colpkr) {
        		jQuery(colpkr).fadeOut(500);
        		return false;
        	},
        	onChange: function (hsb, hex, rgb) {
        		jQuery(th).css('backgroundColor', '#' + hex);
        		jQuery(th).val('#' + hex);
        	}
        });
    });
    
    jQuery('.pagination_select').each(function() {
    	jQuery(this).change(function() {
        	location.href=jQuery(this).val();
        });
    });
    
    jQuery('.button_search').click(function() {
        var pattern = jQuery(this).prev().prev().val();
        location.href=pattern.replace('_s_', jQuery(this).prev().val());        
    });
    
    // tratar o enter
    jQuery('.button_search').prev().keyup(function(event) {
        if (event.keyCode == 13) {
            jQuery(this).next().trigger('click');
            return false;
        }
    });
    
    var richEditorButtons = new Array();
    richEditorButtons = wp_easy_data.richEditorButtons.split(',');
    var richEditors = new nicEditor({
    	iconsPath: wp_easy_data.baseUrl + 'nicEdit/nicEditorIcons.gif',
    	maxHeight: 200,
    	buttonList: richEditorButtons
    });
    jQuery('.wp-easy-data-richEditor').each(function() {
    	richEditors.panelInstance(this.id);
    });

});
