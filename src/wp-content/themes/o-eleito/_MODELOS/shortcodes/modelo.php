<?php

function exemplo_shortcodes() {
	
	function colunas_shortcode ($atts, $content) {
		
		if (is_singular()) {
			$c = explode('[--]', $content);
			$c[0] = do_shortcode($c[0]);
			$c[1] = do_shortcode($c[1]);
			return "
			  <div style='clear: both;'></div>
			  <div style='width: 49.5%; float: left; padding-right: 0.5%;'>
				  {$c[0]}
			  </div>
			  <div style='width: 49.5%; float: left; padding-left: 0.5%;'>
				  {$c[1]}
			  </div>
			  <div style='clear: both;'></div>";
		} else return str_replace('[--]', '', do_shortcode($content));
	}
	
	add_shortcode('colunas', 'colunas_shortcode');
    
    
    function embed_shortcode ($atts, $content) {
        

        $embed = '<object width="' . $atts['largura'] . '" height="385"><param name="movie" value="http://www.youtube.com/v/RXSw6ph3C6w&hl=pt_BR&fs=1&"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/RXSw6ph3C6w&hl=pt_BR&fs=1&" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="' . $atts['largura'] . '" height="385"></embed></object>';
        
        return $embed;
        
    }
	
	add_shortcode('embed', 'embed_shortcode');

}

add_action('init', 'exemplo_shortcodes');


?>
