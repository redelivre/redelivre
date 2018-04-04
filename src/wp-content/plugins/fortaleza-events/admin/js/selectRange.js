jQuery(document).ready(function() { 
	jQuery('#daterange').daterangepicker(
		{
			ranges: {
	           ' Today': [new Date(), new Date()],
	           ' Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
	           ' This Week (Mon - Sun)': [moment().startOf('week').add('days', 1), moment().endOf('week').add('days', 1)],
	           ' Last Week (Mon - Sun)': [moment().subtract('days', 6).startOf('week').add('days', 1), moment().subtract('days', 6).endOf('week').add('days', 1)],
	           ' Next Week (Mon - Sun)': [moment().endOf('week').add('days', 2), moment().endOf('week').add('days', 8)],
	           //' Last 7 Days': [moment().subtract('days', 6), new Date()],
	          // ' Last 14 Days': [moment().subtract('days', 13), new Date()],
	          // ' Last 30 Days': [moment().subtract('days', 29), new Date()],
	           ' This Month': [moment().startOf('month'), moment().endOf('month')],
			   ' Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')],
			   ' Next Month': [moment().startOf('month').add('month', 1), moment().endOf('month').add('month', 1)]
			},
			opens: 'left',
			format: 'YYYY-MM-DD',
			startDate: new Date(),
			endDate: new Date()
					}, 
					function(start, end) {
						if(start.format('MMMM')=='Invalid date'){ 
							set_start_end_date_event();
						}	
						else
						{
							jQuery('#daterange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
							jQuery('#from_date_picker').val(start.format('YYYY-MM-DD'));
							jQuery('#to_date_picker').val(end.format('YYYY-MM-DD'));
						}	
		}
	);
}); 

//Select for select event
jQuery('.ranges ul li').live('click',function() { 
	var cr_val=jQuery(this).attr('cus_sel');
	jQuery('.ranges ul li').removeClass('active');
	jQuery(this).addClass('active');
	jQuery('#ct_sel').val(cr_val);	
});

jQuery(window).bind("load", function() { 
//get default Selected
	var ct_sel=jQuery('#ct_sel').val();
	var df_sel=jQuery('#df_sel').val();
	if(ct_sel){ 
		jQuery('.ranges ul li').removeClass('active');
		jQuery(".ranges ul li").each(function () {
			var ct_rec=jQuery(this).attr('cus_sel');
			if(ct_rec==ct_sel){
				jQuery(this).addClass('active');				
			}
		});	
	}
	
	if(df_sel){ 
		jQuery('.ranges ul li').removeClass('default_active');
		jQuery(".ranges ul li").each(function () {
			var ct_rec=jQuery(this).attr('cus_sel');
			if(ct_rec==df_sel){
				jQuery(this).addClass('default_active');				
			}
		});	
	}	
});


// date picker for future date

    jQuery(document).ready(function() { 
	jQuery('#daterange_two').daterangepicker(
		{
                ranges: {
                   ' Today': [new Date(), new Date()], 
                   'Next Week (Mon - Sun)': [moment().endOf('week').add('days', 2), moment().endOf('week').add('days', 8)],
	           'This Month': [moment().startOf('month'), moment().endOf('month')],
                },
                    opens: 'left',
                    format: 'YYYY-MM-DD',
                    startDate: new Date(),
                    endDate: new Date()
                }, 
                    function(start, end) {
                        if(start.format('MMMM')=='Invalid date'){ 
                                set_start_end_date_event();
                        }else{
                            jQuery('#daterange_two span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                            jQuery('#from_date_picker').val(start.format('YYYY-MM-DD'));
                            jQuery('#to_date_picker').val(end.format('YYYY-MM-DD'));
                        }	
                    }
            );
    }); 