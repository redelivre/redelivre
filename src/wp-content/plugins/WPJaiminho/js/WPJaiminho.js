// prepare the form when the DOM is ready 
jQuery(document).ready(function(){ 
	var options = { 
		target: '#jaiminho-output',   // target element(s) to be updated with server response 
 
        // other available options: 
        beforeSubmit:  function () { jQuery('#jaiminho-message').text("aguarde, enviando..."); },
        type: 'post',
        timeout:   5000 
    }; 
    		 
    // bind to the form's submit event 
    jQuery('#jaiminho-form').submit(function() { 
        // inside event callbacks 'this' is the DOM element so we first 
        // wrap it in a jQuery object and then invoke ajaxSubmit 
    	jQuery('#jaiminho-message').text("aguarde, enviando...");
    	jQuery(this).ajaxSubmit(options); 
 
        jQuery('#jaiminho-message').empty();
 
        // !!! Important !!! 
        // always return false to prevent standard browser submit and page navigation 
        return false; 
    }); 
});