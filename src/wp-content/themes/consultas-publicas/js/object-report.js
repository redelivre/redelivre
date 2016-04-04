jQuery(document).ready(function() {
    jQuery('.toggle_evaluation').click(function() {
        jQuery(this).siblings('#evaluation_bars, #evaluation_scale').toggle('slow');
    });
});