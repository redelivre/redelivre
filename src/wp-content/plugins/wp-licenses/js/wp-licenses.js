/*
+---------------------------------------------------------------+
|																|
|	WordPress Plugin: WP-licenses 2.0							|
|	File Name: wp-licenses.js									|
|	Enqueue ID: wpLicenseScript									|
|	File Written By:											|
|	- Billy Blay												|
|	- http://billyblay.com										|
|																|
+---------------------------------------------------------------+
*/
var $j = jQuery.noConflict();

$j(document).ready(function($){  
	$('#direitos input:radio').click(function() {
		if ( $j('#direitos_0, #direitos_1 ').is(':checked') ) {
				$j("#alguns-direitos").slideUp("slow");
		};
		if ( $j('#direitos_2').is(':checked') ) {
				$j("#alguns-direitos").slideDown("slow");
		};
	});
});

$j(function($) {
    $j('a[rel*=external]').click( function() {
        window.open(this.href);
        return false;
    });
});