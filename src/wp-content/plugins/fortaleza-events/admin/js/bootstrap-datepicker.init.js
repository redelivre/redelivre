$.fn.bdatepicker = $.fn.datepicker.noConflict();

$(function()
{
	today_date=new Date(); 
   
	$(".datepicker-class").bdatepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
	});
      
	$(".start_today").bdatepicker({
		format: 'dd-mm-yyyy',
		startDate: today_date,
		autoclose: true
	});	

	$(".start_today_new_format").bdatepicker({
		format: 'DD, d M, yyyy',	
		startDate: today_date,
		autoclose: true,
		todayBtn: true
	});	
	$(".new_format").bdatepicker({
		format: 'DD, d M, yyyy',	
		autoclose: true
	});	

	$(".monday_format").bdatepicker({
		format: 'DD, d M, yyyy',
		weekStart: 1,
		autoclose: true
	});	

	$(".paymentDate").bdatepicker({
		format: 'DD, dd M, yyyy',
		weekStart: 1,
		autoclose: true
	});		
	$(".only_date_format").bdatepicker({
		format: 'd M, yyyy',	
		startDate: today_date,
		autoclose: true
	});		
		
	$(".with_pre_date").bdatepicker({
		format: 'd M, yyyy',	
		autoclose: true
	});	
	
	/* DatePicker */
	// default
	$("#datepicker1").bdatepicker({
		format: 'yyyy-mm-dd',
		startDate: "2013-02-14"
	});

	// component
	$('#datepicker2').bdatepicker({
		format: "dd MM yyyy",
		startDate: "2013-02-14"
	});

	// today button
	$('#datepicker3').bdatepicker({
		format: "dd MM yyyy",
		startDate: "2013-02-14",
		todayBtn: true
	});

	// advanced
	$('#datetimepicker4').bdatepicker({
		format: "dd MM yyyy - hh:ii",
        autoclose: true,
        todayBtn: true,
        startDate: "2013-02-14 10:00",
		
        minuteStep: 10
	});

	
	// meridian
	$('#datetimepicker5').bdatepicker({
		format: "dd MM yyyy - HH:ii P",
	    showMeridian: true,
	    autoclose: true,
	    startDate: "2013-02-14 10:00",
	    todayBtn: true
	});

	// other
	if ($('#datepicker').length) $("#datepicker").bdatepicker({ showOtherMonths:true });
	if ($('#datepicker-inline').length) $('#datepicker-inline').bdatepicker({ inline: true, showOtherMonths:true });

});