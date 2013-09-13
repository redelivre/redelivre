$(function(){
    $('div.section').each(function(){
        if ($(this).find('h3 input').is(':checked')) {
            $(this).find('.section-content').show();
        }
        else {
            $(this).find('.section-content').hide();
        }
    });

    $('h3 input').click(function(){
        if ($(this).is(':checked')) {
            $('#'+$(this).data('section')+' .section-content').slideDown();
        }
        else{
            $('#'+$(this).data('section')+' .section-content').slideUp();
        }
    });

    // $( "#sortable" ).sortable();
});