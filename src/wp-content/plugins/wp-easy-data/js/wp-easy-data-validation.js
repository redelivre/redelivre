
function hide_from_errors(form) {
	jQuery(form).find('#error div').each(function(){
		jQuery(this).hide();		
	}); 
}

jQuery(document).ready(function (){
	
	
	jQuery('#wp-easy-data_form').submit(function() {
	  var errorObjects = new Array();
	  
	  hide_from_errors(this);
	  
	  jQuery(this).find('.required').each(function(){
		if(jQuery(this).val().length == 0) {
			errorObjects.push({type:'null',obj:jQuery(this)});
			
		}
	  });
	  
	  jQuery(this).find('.email').each(function(){
		var ereg_mail = new RegExp("^[a-z0-9_\-]+(\.[_a-z0-9\-]+)*@([_a-z0-9\-]+\.)+([a-z]{2}|aero|arpa|biz|com|coop|edu|gov|info|int|jobs|mil|museum|name|nato|net|org|pro|travel)$");
		if(!ereg_mail.test(jQuery(this).val())) {
			errorObjects.push({type:'format',obj:jQuery(this)});
			
		}
	  });
	  
	  
	  jQuery(this).find('.restrictedText').each(function(){
		var ereg_restrictedText = new RegExp("^[a-z0-9_]+$","i");
		if(!ereg_restrictedText.test(jQuery(this).val())) {
			errorObjects.push({type:'format',obj:jQuery(this)});
		}
	  });
	  
	  
	  
	  
	 
	  if(errorObjects.length > 0) {
		  showCrudFormErros(jQuery(this),errorObjects);
		  return false;
		  		  
	  }
	 
		return true;
	  
	});	
});


function showCrudFormErros (target_form,eo) {
	var reported_inputs = new Array();
	
	var errorDiv = target_form.find('#error');
	errorDiv.removeClass('error');
	errorDiv.find("ul").html("");
	
	
	
	for( var i = 0 ; i< eo.length; i++ ) {
		var input =  eo[i].obj
		try{
			if( jQuery.inArray( input.attr('id') , reported_inputs) == -1) {
				reported_inputs.push(input.attr('id'));
				
				var label = jQuery("label[for='"+ input.attr('id')  +"']").html()
				
				errorDiv.find("#"+eo[i].type).show();
				errorDiv.find("#"+eo[i].type+" ul" ).append('<li>'+ label + "</li>");
				
				if(!errorDiv.hasClass('error')) {
					errorDiv.addClass('error');
				}
				
			}
		}catch(e){
			
			//alert(e + "\n ERRRO " + eo[i].type);
			
		}
	}
	
	jQuery.scrollTo(errorDiv,400);
	
}


