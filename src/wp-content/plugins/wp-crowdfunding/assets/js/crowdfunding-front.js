// Crowdfunding Scripts

jQuery(document).ready(function($){
    
    var count = 0;
    var numItems = $('.wpneo-block').length;
    if(numItems!=0){ count = numItems; }
    $.fn.createNewForm = function( count ){
        return this.each(function(){
            $(this).find('input,textarea,select').each(function(){
                var $that = $(this);
                $that.attr('name', this.name.replace(/\d/, count ));
                $that.val('');
            });
        });
    };
    $('.add-new').on('click', function(e){
        var $form       = $('.wpneo-block').last(),
            $cloned     = $form.clone();
        $cloned.createNewForm(count);
        $('#wpneo-clone .add-new').before( $($cloned) );
        count = count+1;
    });
    $(document).on('click','.remove-button',function(events){
        if($('.wpneo-block').length > 1){
            $(this).parent('.wpneo-block').remove();
        }
    });
    $('#wpneo_form_start_date, #wpneo_form_end_date').datepicker({
        dateFormat : 'dd-mm-yy'
    });


    // Pie Chart
    $('.crowdfound-pie-chart').easyPieChart({
        barColor: '#1adc68',
        trackColor: '#f5f5f5',
        scaleColor: false,
        lineWidth: 5,
    });

    $('.datepickers_1').datepicker({
        dateFormat: 'yy-mm-dd'
    });
    $('.datepickers_2').datepicker({
        dateFormat: 'yy-mm-dd'
    });

    // Insert Campaign Post Data
    $('#wpneofrontenddata').submit(submit_frontend);
    function submit_frontend(){
        tinyMCE.triggerSave();
        var front_end_data = $(this).serialize();
        $.ajax({
            type:"POST",
            url: wpcf_ajax_object.ajax_url,
            data: front_end_data,
            success:function(data){
                var parseData = JSON.parse(data);

                if ( ! parseData.success){
                    //Reset reCaptcha if failed
                    if( (typeof grecaptcha !== 'undefined') && ($('.g-recaptcha').length !== 0) ) {
                        grecaptcha.reset();
                    }
                }
                if (wpcf_modal(data)){  }
            },
            error: function(jqXHR, textStatus, errorThrown){
                wpcf_modal({'success':0, 'message':'Error sending data'})
            }
        });
        return false;
    }

    $( document ).on('click', '.wpcf-print-button', function (e) {
        window.print();
    });

    // Common Modal Function
    function wpcf_modal( data, print = false ){
        var data = JSON.parse(data);
        var html = '<div class="wpneo-modal-wrapper"> ' +
            ' <div class="wpneo-modal-content"> ' +
            '<div class="wpneo-modal-wrapper-head">' +
            '<h4 id="wpcf_modal_title">Message</h4><a href="javascript:;" class="wpneo-modal-close">&times;</a>';
            if( print ){
                html += '</div><span class="wpcf-print-button button">print</span>';
            }
            html += '<div class="wpneo-modal-content-inner"><div id="wpcf_modal_message"></div></div></div></div>';
        if ($('.wpneo-modal-wrapper').length == 0){
            $('body').append(html);
            if (data.redirect){
                if ( $('#wpneo_crowdfunding_redirect_url').length == 0 ){
                    $('body').append('<input type="hidden" id="wpneo_crowdfunding_redirect_url" value="'+data.redirect+'" />');
                }
            }
        }
        if (data.success == 1){
            if(data.message){
                $('.wpneo-modal-wrapper #wpcf_modal_message').html( data.message );
            }
            if(data.title){
                $('.wpneo-modal-wrapper #wpcf_modal_title').html( data.title );
            }
            $('.wpneo-modal-wrapper').css({'display': 'block'});
            if ( $('#wpneofrontenddata').length > 0 ){
                $("#wpneofrontenddata")[0].reset();
            }
            return true;
        }else {
            $('.wpneo-modal-wrapper #wpcf_modal_message').html(data.message);
            $('.wpneo-modal-wrapper').css({'display': 'block'});
            return false;
        }
    }
    window.wpcf_modal = wpcf_modal; //make global function

    // Image Upload Function
    function wpcf_upload_image( button_class ) {
        var _custom_media = true,
            _orig_send_attachment = wp.media.editor.send.attachment;
        $('body').on('click',button_class, function(e) {
            var button_id ='#'+$(this).attr('id');
            var button = $(button_id);
            _custom_media = true;
            wp.media.editor.send.attachment = function(props, attachment){
                if ( _custom_media  ) {
                    var attachment_url = attachment.url;
                    $('.wpneo-form-image-url').val(attachment_url);
                    $('.wpneo-form-image-id').val(attachment.id);
                } else {
                    return _orig_send_attachment.apply( button_id, [props, attachment] );
                }
            };
            wp.media.editor.open(button);
            return false;
        });
    }
    wpcf_upload_image('.wpneo-image-upload');
    $(document).on('click','.add_media', function(){
        _custom_media = false;
    });

    // Login Toggle Add (Frontend Submit Form)
    $('.wpneoShowLogin').on('click', function (e) {
        e.preventDefault();
        $('.wpneo_login_form_div').slideToggle();
    });

    // Repeatable Rewards
    function countRemovesBtn(btn) {
        var rewards_count = $(btn).length;
        if (rewards_count > 1){
            $(btn).show();
        }else {
            $(btn).hide();
            if (btn == '.removeCampaignRewards') {
                $('.reward_group').show();
            }
            if (btn == '.removecampaignupdate') {
                $('#campaign_update_field').show();
            }
        }
        $(btn).first().hide();
    }

    //Add Rewards
    $('#addreward').on('click', function (e) {
        e.preventDefault();
        var rewards_fields = $('.reward_group').html();
        $('#rewards_addon_fields').append(rewards_fields);
        $('#rewards_addon_fields .campaign_rewards_field_copy:last-child').find('input,textarea,select').each(function(){
            if ( ($(this).attr('name') != 'remove_rewards')&&($(this).attr('type') != 'button') ){
                $(this).val('');
            }
        });
        countRemovesBtn('.removeCampaignRewards');
    });

    // Remove Campaign Reward
    $('body').on('click', '.removeCampaignRewards', function (e) {
        e.preventDefault();
        $(this).closest('.campaign_rewards_field_copy').html('');
        countRemovesBtn('.removeCampaignRewards');
    });
    countRemovesBtn('.removeCampaignRewards');

    //Add More Campaign Update Field
    $('#addcampaignupdate').on('click', function (e) {
        e.preventDefault();
        var update_fields = $('#campaign_update_field').html();
        $('#campaign_update_addon_field').append(update_fields);
        countRemovesBtn('.removecampaignupdate');
    });
    
    // Remove Campaign Update
    $('body').on('click', '.removecampaignupdate', function (e) {
        e.preventDefault();
        $(this).closest('.campaign_update_field_copy').html('').hide();
        countRemovesBtn('.removecampaignupdate');
    });
    countRemovesBtn('.removecampaignupdate');

    // Dashboard Edit Form
    $('#wpneo_active_edit_form').on('click', function(e){
        e.preventDefault();
        $('#wpneo_update_display_wrapper').hide();
        $('#wpneo_update_form_wrapper').fadeIn('slow');
    });

    // Edit Enable
    $('#wpneo-edit').on('click', function (e) {
        e.preventDefault();
        $('#wpneo-edit').hide();
        $('.wpneo-content input,.wpneo-content textarea,.wpneo-content select').not('.wpneo-content input[name="username"]').removeAttr("disabled").css( "border", "1px solid #dfe1e5" );
        $('.wpneo-save-btn').delay(100).fadeIn('slow');
        $('.wpneo-cancel-btn').delay(100).fadeIn('slow');
        $('button.wpneo-image-upload').show();
    });

    // Dashboard Data Save
    function wpcf_dashboard_data_save(){
        var return_data;
        var postdata = $('#wpneo-dashboard-form').serializeArray();
        $.ajax({
                async: false,
                url : wpcf_ajax_object.ajax_url,
                type: "POST",
                data : postdata,
                success:function(data, textStatus, jqXHR) {
                    wpcf_modal(data);
                    return_data = data;
                },
                error: function(jqXHR, textStatus, errorThrown){
                    wpcf_modal({'success':0, 'message':'Error sending data'})
                }
            });
        $('.wpneo-content input,.wpneo-content textarea,.wpneo-content select').attr("disabled","disabled").css( "border", "none" );
        $('.wpneo-cancel-btn').hide();
        $('#wpneo-edit').delay(100).fadeIn('slow');
        return return_data;
    }

    // Dashboard Cancel Button
    $('.wpneo-cancel-btn').on('click', function(e){
        e.preventDefault();
        $('.wpneo-content input,.wpneo-content textarea,.wpneo-content select').attr("disabled","disabled").css( "border", "none" );
        $('.wpneo-cancel-btn').hide();
        $('#wpneo-dashboard-save').hide();
        $('#wpneo-profile-save').hide();
        $('#wpneo-contact-save').hide();
        $('button.wpneo-image-upload').hide();
        $('#wpneo-edit').delay(100).fadeIn('slow');
    });

    // Dashboard Froentend ( Dashboard )
    $('#wpneo-dashboard-save').on('click', function (e) {
        e.preventDefault(); //STOP default action
        var postdata = $('#wpneo-dashboard-form').serializeArray();
        wpcf_dashboard_data_save();
    });

    // Dashboard Froentend ( Profile )
    $('#wpneo-profile-save').on('click', function (e) {
        e.preventDefault(); //STOP default action
        wpcf_dashboard_data_save();
    });

    // Dashboard Froentend ( Contact )
    $('#wpneo-contact-save').on('click', function (e) {
        e.preventDefault(); //STOP default action
        wpcf_dashboard_data_save();
    });

    // Dashboard Froentend ( Password )
    $('#wpneo-password-save').on('click', function (e) {
        e.preventDefault(); //STOP default action
        wpcf_dashboard_data_save();
    });

    // Dashboard Froentend ( Update )
    $('#wpneo-update-save').on('click', function (e) {
        e.preventDefault(); //STOP default action
        var return_respone = wpcf_dashboard_data_save();
        wpcf_modal(return_respone);
    });

    // Tab Menu Action (Product Single)
    $('.wpneo-tabs-menu a').on("click", (function (e) {
        e.preventDefault();
        $('.wpneo-tabs-menu li').removeClass('wpneo-current');
        $(this).parent().addClass('wpneo-current');
        var currentTab = $(this).attr('href');
        $('.wpneo-tab-content').hide();
        $(currentTab).fadeIn();
        return false;
    }));
    $($('.wpneo-current a').attr('href')).fadeIn();

    // Modal Bio in Product details
    $('.wpneo-fund-modal-btn').on('click', function (e) {
        e.preventDefault();
        var author = $(this).data('author');
        $.ajax({
            type:"POST",
            url: wpcf_ajax_object.ajax_url,
            data: { 'action': 'wpcf_bio_action', 'author': author },
            success:function(data){
                wpcf_modal( data );
            },
            error: function(jqXHR, textStatus, errorThrown){ wpcf_modal({'success':0, 'message':'Error'}) }
        });
    });

    // Modal Close Option
    $(document).on('click', '.wpneo-modal-close', function(){
        $('.wpneo-modal-wrapper').css({'display': 'none'});
        if ( $('#wpneo_crowdfunding_redirect_url').length > 0 ) {
            location.href = $('#wpneo_crowdfunding_redirect_url').val();
        }
    });

    // Donate Field Add Max & Min Amount
    $('input[name="wpneo_donate_amount_field"]').on('blur change paste', function(){
        var input_price = $(this).val();
        var min_price = $(this).data('min-price');
        var max_price = $(this).data('max-price');
        if (input_price < min_price){
            if(min_price){
                $(this).val( min_price );
                $('.wpneo-tooltip-min').css({'visibility': 'visible'});
            }
        }else if (max_price < input_price){
            if(max_price){
                $(this).val( max_price );
                $('.wpneo-tooltip-max').css({'visibility': 'visible'});
            }
        }else{
            $('.wpneo-tooltip-min,.wpneo-tooltip-max').css({'visibility': 'hidden'});
        }
    });

    // Add Love Campaign
    $(document).on('click', '#love_this_campaign', function () {
        var campaign_id = $(this).data('campaign-id');
        $.ajax({
            type:"POST",
            url: wpcf_ajax_object.ajax_url,
            data: {'action': 'love_campaign_action', 'campaign_id': campaign_id},
            success:function(data){
                data = JSON.parse(data);
                if (data.success == 1){
                    $('#campaign_loved_html').html(data.return_html);
                }
            },
            error: function(jqXHR, textStatus, errorThrown){
                wpcf_modal({'success':0, 'message':'Error'})
            }
        });
    });

    // Remove Love Campaign
    $(document).on('click', '#remove_from_love_campaign', function () {
        var campaign_id = $(this).data('campaign-id');
        $.ajax({
            type:"POST",
            url: wpcf_ajax_object.ajax_url,
            data: {'action': 'remove_love_campaign_action', 'campaign_id': campaign_id},
            success:function(data){
                data = JSON.parse(data);
                $('#campaign_loved_html').html(data.return_html);
            },
            error: function(jqXHR, textStatus, errorThrown){
                wpcf_modal({'success':0, 'message':'Error'})
            }
        });
    });

    $(document).on('click', '#user-registration-btn', function (e) {
        e.preventDefault();
        var registration_form_data = $(this).closest('form').serialize();
        $.ajax({
            type:"POST",
            url: wpcf_ajax_object.ajax_url,
            data: registration_form_data,
            success:function(data){
                wpcf_modal(data);
                data = JSON.parse(data);
                if (data.success){
                    location.href = data.redirect;
                }else {
                    if(typeof grecaptcha !== 'undefined'){
                        grecaptcha.reset();
                    }
                }
            },
            error: function(jqXHR, textStatus, errorThrown){
                wpcf_modal({'success':0, 'message':'Error'});
            }
        });
    });
    
    var image = $('input[name=wpneo-form-image-url]').val();
    if( image!='' ){
        $('#wpneo-image-show').html('<img width="150" src="'+image+'" />');
    }
    $(document).on('click','.media-button-insert',function(e){
        var image = $('input[name=wpneo-form-image-url]').val();
        if( $('.profile-form-img').length > 0 ){
            $('.profile-form-img').attr( 'src',image );
        }else{
            if(image!=''){
                $('#wpneo-image-show').html('<img width="150" src="'+image+'" />');
            }
        }
    });

    // Hide Billing and Shipping Information
    if( $('body.woocommerce-checkout').length >= 1 ){
        if( $('#billing_email').length < 1 ){
            $('#customer_details').css({'display': 'none'});
        }
    }

    // Form Reward Image Upload
    $('body').on('click','.wpneo-image-upload-btn',function(e) {
        e.preventDefault();
        var that = $(this);

        var image = wp.media({
            title: 'Upload Image',
            multiple: false
        }).open()
            .on('select', function(e){
                var uploaded_image = image.state().get('selection').first();
                var uploaded_url = uploaded_image.toJSON().url;
                uploaded_image = uploaded_image.toJSON().id;
                $(that).parent().find( '.wpneo_rewards_image_field' ).val( uploaded_image );
                $(that).parent().find( '.wpneo_rewards_image_field_url' ).val( uploaded_url );
            });
    });
    $('body').on('click','.wpneo-image-remove',function(e) {
        var that = $(this);
        $(that).parent().find( 'wpneo_rewards_image_field_url' ).val( '' );
        $(that).parent().find( '.wpneo_rewards_image_field' ).val( '' );
    });

    // Reward On Click
    $('body').on('click','.price-value-change',function(e) {
        e.preventDefault();
        var reward = $(this).data('reward-amount');
        $("html, body").animate({ scrollTop: 0 }, 600,
            function() {
                setTimeout(function(){
                    $(".wpneo_donate_amount_field").addClass("wpneosplash");
                }, 100 );
                setTimeout(function(){
                    $(".wpneo_donate_amount_field").val( reward );
                    $( ".wpneo_donate_amount_field" ).removeClass( "wpneosplash" );
                }, 1000 );
            });
    });
    $(document).on('click','table.reward_table_dashboard tr',function(e) {
        $(this).find('.reward_description').slideToggle();
    });

    // Order View (Dashboard Page)
    $(document).on('click', '.wpcf-order-view', function (e) {
        e.preventDefault();
        var orderid = $(this).data('orderid');
        $.ajax({
            type:"POST",
            url: wpcf_ajax_object.ajax_url,
            data: { 'action': 'wpcf_order_action', 'orderid': orderid },
            success:function(data){
                wpcf_modal( data, true );
            },
            error: function(jqXHR, textStatus, errorThrown){
                wpcf_modal({'success':0, 'message':'Error'})
            }
        });
    });

    // Embed Popup (Single Page)
    $(document).on('click', '.wpneo-icon-embed', function (e) {
        e.preventDefault();
        var postid = $(this).data('postid');
        $.ajax({
            type:"POST",
            url: wpcf_ajax_object.ajax_url,
            data: { 'action': 'wpcf_embed_action', 'postid': postid },
            success:function(data){
                wpcf_modal(data);
            },
            error: function(jqXHR, textStatus, errorThrown){ wpcf_modal({'success':0, 'message':'Error'}) }
        });
    });

    /**
     * Place the predefined price in the donation input value
     *
     * @since 10.22
     */
    $(document).on('click', 'ul.wpcf_predefined_pledge_amount li a', function(){
        var price = $(this).attr('data-predefined-price');
        $('.wpneo_donate_amount_field').val(price);
    });

    $('select[name="wpneo-form-type"]').on('change', function(){
        if( $(this).val() == 'never_end' ){
            $('#wpneo_form_start_date').parents('.wpneo-single').hide();
            $('#wpneo_form_end_date').parents('.wpneo-single').hide();
        }else{
            $('#wpneo_form_start_date').parents('.wpneo-single').show();
            $('#wpneo_form_end_date').parents('.wpneo-single').show();
        }
    });
    
});
