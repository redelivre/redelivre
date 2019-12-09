<?php

//replace links to mp3 to player
add_filter('the_content', 'replace_mp3_links');

function replace_mp3_links($content) {
    
    if (!is_feed()) {
        $pattern = "/<a ([^=]+=['\"][^\"']+['\"] )*href=['\"](([^?\"']+\.mp3))['\"]( [^=]+=['\"][^\"']+['\"])*>([^<]+)<\/a>/i";
        $content = preg_replace_callback( $pattern, '_replace_mp3_links_do_replace', $content );
    }
    
    return $content;
    
}

function _replace_mp3_links_do_replace($matches) {

    $data = preg_split("/[\|]/", $matches[3]);
			
    //$files = array();
    
    foreach ( explode( ",", trim($data[0]) ) as $afile ) {
        $afile = trim($afile);
        //array_push( $files, $afile );
        
        return get_audio_player($afile);

    }

}


function print_audio_player($fileURL, $dl = true){

    echo get_audio_player($fileURL, $dl = true);

}

function get_audio_player($fileURL, $dl = true){
    $player ='<audio controls>
				<source src="' . $fileURL . '">
			 </audio>';
    if ($dl) {
    	//$player .= '<br/><a href="'. $fileURL . '">Download</a>';
    }
    
    return $player;
    
}



?>
