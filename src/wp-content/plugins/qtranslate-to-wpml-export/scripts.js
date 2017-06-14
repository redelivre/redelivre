    jQuery(document).ready(function(){
        jQuery('#qt_import_start').click(qt_import_terms_batch);
        
        var qt_terms_batch = 0;
        var qt_terms_keepgoing = 0;
        function qt_import_terms_batch(){
            
            if(jQuery('#confirm_import:checked, #confirm_dbbk:checked').length < 2){
                return;
            }
            
            qt_terms_batch++;
            jQuery('#qt_import_working').fadeIn();
            jQuery('#qt_import_start').attr('disabled', 'disabled');
            jQuery.ajax({
                type: "POST",
                url: ajaxurl,
                dataType: 'json',
                data: 'action=qt_terms_ajx&qt_terms_batch='+qt_terms_batch+'&qt_terms_keepgoing='+qt_terms_keepgoing+'&qt_nonce='+jQuery('#qt_terms').val(),
                success: function(res){                            
                    jQuery('#qt_import_status:hidden').show();
                    jQuery('#qt_import_status').html(res.messages.join('<br />') + jQuery('#qt_import_status').html());
                    if(res.keepgoing){
                        qt_terms_keepgoing = 1;
                        qt_import_terms_batch();
                    }else{
                        jQuery('#qt_import_working').fadeOut();
                        qt_keepgoing = 0;
                        qt_import_process_batch();
                    }
                }
                                    
            })                        
        }


        var qt_import_batch = 0;
        var qt_keepgoing = 0;
        function qt_import_process_batch(){

            qt_import_batch++;
            jQuery('#qt_import_working').fadeIn();
            jQuery('#qt_import_start').attr('disabled', 'disabled');
            jQuery.ajax({
                type: "POST",
                url: ajaxurl,
                dataType: 'json',
                data: 'action=qt_import_ajx&qt_import_batch='+qt_import_batch+'&qt_keepgoing='+qt_keepgoing+'&qt_nonce='+jQuery('#qt_import').val(),
                success: function(res){                            
                    jQuery('#qt_import_status:hidden').show();
                    jQuery('#qt_import_status').html(res.messages.join('<br />') + jQuery('#qt_import_status').html());
                    if(res.keepgoing){
                        qt_keepgoing = 1;
                        qt_import_process_batch();        
                    }else{
                        jQuery('#qt_import_working').fadeOut();
                        /*jQuery('#qt_import_start').removeAttr('disabled');*/
                        qt_keepgoing = 0;
                        // Fixing links starts
                        qt_fix_links();
                    }
                }
                                    
            })                        
        }
        
        jQuery('#qt_clean_form select[name=language_keep]').change(function(){
                                
            if(jQuery(this).val().length > 0){
                
                var lang_code = jQuery(this).val();
                var lang_sel = jQuery(this).find('option[value='+lang_code+']').html();
                var langs_removed = [];
                jQuery(this).find('option').each(function(){
                    if(jQuery(this).val() != '' && jQuery(this).html() != lang_sel){
                        langs_removed.push(jQuery(this).html());   
                    }                            
                });
                
                jQuery('#qt_language_removed').html(langs_removed.join(', '));
                jQuery('#qt_language_kept').html(lang_sel);
                jQuery('#qt_clean_confirm').fadeIn();
                
            }else{
                jQuery('#qt_clean_confirm').hide();
            }
        });
        
        jQuery('#confirm_delete, #confirm_keep').click(function(){                    
            if(jQuery('#confirm_delete:checked, #confirm_keep:checked').length == 2){
                jQuery('#qt_clean_form :submit').removeAttr('disabled');
            }else{
                jQuery('#qt_clean_form :submit').attr('disabled', 'disabled');
            }
        })

        
        jQuery('#confirm_import, #confirm_dbbk').click(function(){                    
            if(jQuery('#confirm_import:checked, #confirm_dbbk:checked').length == 2){
                jQuery('#qt_import_start').removeAttr('disabled');
            }else{
                jQuery('#qt_import_start').attr('disabled', 'disabled');
            }
        })
        
        jQuery('#qt_clean_form').submit(qt_clean_process_batch);
            
        var qt_clean_batch = 0;
        var qt_keepgoing = 0;
        function qt_clean_process_batch(){
            qt_clean_batch++;
            jQuery('#qt_clean_working').fadeIn();
            jQuery('#qt_clean_start').attr('disabled', 'disabled');
            jQuery.ajax({
                type: "POST",
                url: ajaxurl,
                dataType: 'json',
                data: 'action=qt_clean_ajx&lang='+jQuery('#qt_clean_form select[name=language_keep]').val()+'&qt_clean_batch='+qt_clean_batch+'&qt_keepgoing='+qt_keepgoing,
                success: function(res){                                                        
                    jQuery('#qt_clean_status').html(res.messages.join('<br />') + jQuery('#qt_clean_status').html());
                    jQuery('#qt_clean_status:hidden').show();
                    if(res.keepgoing){
                        qt_keepgoing = 1;
                        qt_clean_process_batch();        
                    }else{
                        jQuery('#qt_clean_working').fadeOut();
                        qt_keepgoing = 0;
                    }
                }
                                    
            })                        
            
            return false;
        }
        
        
        var qt_lfix_batch = 0;
        function qt_fix_links(){
            qt_lfix_batch++;
            jQuery('#qt_import_working').fadeIn();
            jQuery('#qt_import_start').attr('disabled', 'disabled');
            jQuery.ajax({
                type: "POST",
                url: ajaxurl,
                dataType: 'json',
                data: 'action=qt_fix_links_ajx&qt_lfix_batch='+qt_lfix_batch+'&qt_keepgoing='+qt_keepgoing+'&qt_nonce='+jQuery('#qt_links').val(),
                success: function(res){                            
                    jQuery('#qt_import_status').html(res.messages.join('<br />') + jQuery('#qt_import_status').html());
                    if(res.keepgoing){
                        qt_keepgoing = 1;
                        qt_fix_links();        
                    }else{
                        jQuery('#qt_import_working').fadeOut();
                        /*jQuery('#qt_import_start').removeAttr('disabled');*/
                        qt_keepgoing = 0;                                
                        if(res.redirects){
                            jQuery('#qt_import_redirects textarea').val(res.redirects);
                            jQuery('#qt_import_redirects').fadeIn();
                        }
                        
                    }
                }
                                    
            })                        
        }
        
        
        jQuery('#qt_verify_htaccess').click(function(){
            
            jQuery('#qt_verify_htaccess_yes, #qt_verify_htaccess_no').hide();
            jQuery.ajax({
                type: "POST",
                url: ajaxurl,
                dataType: 'json',
                data: 'action=qt_verify_htaccess',
                success: function(res){                            
                    if(res.found == 1){
                        jQuery('#qt_verify_htaccess_yes').fadeIn();
                    }else{
                        jQuery('#qt_verify_htaccess_no').fadeIn();
                    }    
                }
                                    
            })                        
            
            
        })
        
        
        
    })