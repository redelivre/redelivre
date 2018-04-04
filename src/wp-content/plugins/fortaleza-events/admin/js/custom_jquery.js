jQuery(document).ready(function() {
        jQuery('button#get_event').click(function(event){
            event.preventDefault();
            var event_image=jQuery(this).attr('rel1');
            var event_title=jQuery(this).attr('rel2');
            var event_language=jQuery(this).attr('rel3');
            var age_rating=jQuery(this).attr('rel4');
            var event_author=jQuery(this).attr('rel5');
            var event_content=jQuery(this).attr('rel6');
            
            var start_date=jQuery(this).attr('date');
            //alert(start_date);
            var start_time=jQuery(this).attr('start_time');
           // alert(start_time);
            var end_time=jQuery(this).attr('end_time');
            //alert(end_time);
            var price=jQuery(this).attr('price');
           // alert(price);
            var address=jQuery(this).attr('address');
           // alert(address);
           var spaceID=jQuery(this).attr('spaceID');
            
            var ajax_url= jQuery("#ajax_url").val();
            var redirect_link= jQuery("#redirect_link").val();
            //alert(ajax_url);
                 if (confirm('Are you sure you want to add this agenda in your website?')) {
                   jQuery(".ajax_loader_img").show();
                    jQuery.ajax({
                        type:'POST',
                        url: ajax_url,
                        data: {
                            action: 'add_event',
                            _event_image: event_image,
                            _event_title: event_title,
                            _event_language:event_language,
                            _age_rating:age_rating,
                            _event_author:event_author,
                            _event_content:event_content,
                            
                            _start_date:start_date, 
                            _start_time:start_time,
                            _end_time:end_time,
                            _price:price,
                            _address:address,
                            _spaceID:spaceID

                     },
                        success:function(html){
                           // alert(html);die();
                            //window.location.href = redirect_link;
                            setTimeout(function () { 
                               alert('Event Sucessfully Added..!');
                               jQuery(".ajax_loader_img").hide(); 
                           }, 2000);
                           
                           exit;
                         }
                    });
                }
             });
});

// delete event
jQuery(document).ready(function() {
    jQuery('button#delete_event').click(function(event){
         event.preventDefault();
         var del_post_id=jQuery(this).attr('rel1');
         var ajax_url= jQuery("#ajax_url").val();
         var redirect_link= jQuery("#redirect_link").val();
            if (confirm('Are you sure you want to delete this agenda in your website?')) {
                jQuery(".ajax_loader_img").show();
                jQuery.ajax({
                    type:'POST',
                    url: ajax_url,
                    data: {
                        action: 'delete_event',
                        _del_id_: del_post_id,
                    },
                    success:function(html){
                    //window.location.href = redirect_link;
                        setTimeout(function () { 
                             alert("Event Sucessfully Deleted..!")
                            jQuery(".ajax_loader_img").hide(); 
                        }, 2000);
                     }
                });
            }
        });
});



