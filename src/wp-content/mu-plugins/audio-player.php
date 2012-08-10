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
    
	$playerURL = WPMU_PLUGIN_URL . '/includes/audio-player/';
    
    $player ='<object type="application/x-shockwave-flash"
        	data="' . $playerURL . '/player.swf" id="audioplayer1"
        	class="audioplayer" height="24" width="220" style="visibility: visible">
        	<param name="movie" value="' . $playerURL . '/player.swf">
        	<param name="FlashVars"
        		value="playerID=1&amp;soundFile=' . $fileURL . '">
        	<param name="quality" value="high">
        	<param name="menu" value="false">
        	<param name="wmode" value="transparent">
        </object>';
    
    if ($dl)
    $player .= '<br/><a href="' . WPMU_PLUGIN_URL . '/includes/audio-player/download_audio.php?file_url=' . $fileURL . '">Download</a>';
    
    return $player;
    
}



?>
