<?php
/*
Plugin Name: iteia-wp-video
Plugin URI: http://www.iteia.org.br/plugins
Version: 1.1
Author: Billy Blay
Description: Exibe vídeos do iteia através de shortcodes

 *      
 *      
 *      Copyright 2010 Billy Blay billy.blay@gmail.com
 *      
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *      
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *      
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */

function iteia_func($atts) {
	extract(shortcode_atts(array(
		'id' => 'video',
		'w' =>'590',
		'h' =>'385',
	), $atts));
	
$embed =  '<div id="player" style="width:'.$w.'px;height:'.$h.'px;"></div>
<script>

flowplayer("player", "http://releases.flowplayer.org/swf/flowplayer-3.2.9.swf", {

    clip : {			
        autoPlay: false,
        autoBuffering: true,
        baseUrl: \'http://www.iteia.org.br/conteudo/videos/convertidos/\',
		scaling: \'fit\'
    },

    playlist: [
        \'video_'.$id.'.flv\'
    ],

    plugins: {
        controls: {
            playlist: false,	
            tooltips: {
                buttons: true,
                fullscreen: \'Tela cheia\',
                fullscreenExit: \'Sair\',
                previous: \'Anterior\',
                next: \'Próximo\',
                play: \'Tocar\',
                pause:\'Parar\',
                mute: \'Desligar o som\',
                unmute: \'Ligar o som\'
            }
        }
    }
});
</script>';
	
	return $embed;
}
add_shortcode('iteia', 'iteia_func');

function FlowplayerAction() {  
	if (!is_admin()){ 		
		wp_enqueue_script('flow', 'http://releases.flowplayer.org/js/flowplayer-3.2.8.min.js');
	}
}

add_action('wp_print_scripts', 'FlowplayerAction');


?>