// prepare the form when the DOM is ready 
jQuery(document).ready(function(){ 
	var options = { 
		target: '#jaiminho-output',   // target element(s) to be updated with server response 
 
        // other available options: 
        beforeSubmit:  function () { jQuery('#jaiminho-message').text("aguarde, enviando...") },  // pre-submit callback 
        success:       function () { jQuery('#jaiminho-message').html(""); }  // post-submit callback 
        //url:       url         // override for form's 'action' attribute 
        //type:      type        // 'get' or 'post', override for form's 'method' attribute 
        //dataType:  null        // 'xml', 'script', or 'json' (expected server response type) 
        //clearForm: true        // clear all form fields after successful submit 
        //resetForm: true        // reset the form after successful submit 
 
        // $.ajax options can be used here too, for example: 
        //timeout:   3000 
    }; 
    		 
    // bind to the form's submit event 
    jQuery('#jaiminho-form').submit(function() { 
        // inside event callbacks 'this' is the DOM element so we first 
        // wrap it in a jQuery object and then invoke ajaxSubmit 
    	jQuery(this).ajaxSubmit(options); 
 
        // !!! Important !!! 
        // always return false to prevent standard browser submit and page navigation 
        return false; 
    }); 
});