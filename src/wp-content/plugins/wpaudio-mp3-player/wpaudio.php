<?php

/*
Plugin Name: WPaudio
Plugin URI: http://wpaudio.com
Description: Play mp3s and podcasts in your posts by converting links and tags into a simple, customizable audio player.
Version: 3.1
Author: Todd Iceton
Author URI: http://todd.is

Copyright 2010 Todd Iceton (email: todd@wpaudio.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

## WPaudio version
$wpa_version = '3.1';

## Pre-2.6 compatibility (from WP codex)
if ( ! defined( 'WP_CONTENT_URL' ) )
	define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
if ( ! defined( 'WPAUDIO_URL' ) )
	define( 'WPAUDIO_URL', WP_PLUGIN_URL . '/wpaudio-mp3-player' );
	
## Get WPaudio's options from DB (or create them if not found)
$wpa_options = wpaOptions();

## WP handlers - add WPaudio processing at necessary events
# If it's not an admin page, get everything for the player
if ( !is_admin() ) {
	# Calling scripts
	add_action('init', 'wpaLibraries');
	# Add header action to include CSS and JS vars
	add_action('wp_head', 'wpaHead');
	# Add shortcode for WPaudio player
	add_shortcode('wpaudio', 'wpaShortcode');
	# Add filter for shortcode in excerpt and widgets
	add_filter('the_excerpt', 'do_shortcode');
	add_filter('widget_text', 'do_shortcode');
	# Add filter for non-shortcode substitutes (including excerpts and widgets)
	if ($wpa_options['wpa_tag_audio']) {
		add_filter('the_content', 'wpaFilter');
		add_filter('the_excerpt', 'wpaFilter');
		add_filter('widget_text', 'wpaFilter');
	}
}
# Add admin
add_action('admin_menu', 'wpa_menu');
# Add track
if ($wpa_options['wpa_track_permalink']) add_action('publish_post', 'wpaPostNew');

function wpaOptions(){
	## WPA options and defaults
	global $wpa_version;
	$wpa_options = Array(
		'wpa_version' => $wpa_version,
		'wpa_pref_link_mp3' => 0,
		'wpa_tag_audio' => 0,
		'wpa_track_permalink' => 1,
		'wpa_style_text_font' => 'Sans-serif',
		'wpa_style_text_size' => '18px',
		'wpa_style_text_weight' => 'normal',
		'wpa_style_text_letter_spacing' => 'normal',
		'wpa_style_text_color' => 'inherit',
		'wpa_style_link_color' => '#24f',
		'wpa_style_link_hover_color' => '#02f',
		'wpa_style_bar_base_bg' => '#eee',
		'wpa_style_bar_load_bg' => '#ccc',
		'wpa_style_bar_position_bg' => '#46f',
		'wpa_style_sub_color' => '#aaa'
	);
	if ( $wpa_options_db = get_option( 'wpaudio_options' ) ) {
		foreach ( $wpa_options as $key => $value ) {
			if ( isset($wpa_options_db[$key]) ) {
				$wpa_options[$key] = $wpa_options_db[$key];
			}
		}
	}
	else {
		# Get legacy options and remove if they exist
		if ( get_option('wpa_tag_audio') ) {
			foreach ($wpa_options as $key => $value) {
				$wpa_option_old_db = get_option($key);
				if ( $wpa_option_old_db !== false && $wpa_option_old_db !== '' ) {
					$wpa_options[$key] = $wpa_option_old_db;
				}
				delete_option($key);
			}
		}
		# Create wpaudio_options
		add_option('wpaudio_options', $wpa_options, '', 'no');
		//update_option('wpaudio_options', $wpa_options);
	}
	return $wpa_options;
}

## Built-in libraries
function wpaLibraries(){
	global $wpa_version;
	//wp_deregister_script( 'jquery' );
	//wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js', '1.4.3' );
	if ( version_compare( get_bloginfo( 'version' ), '2.8', '>=' ) ) {
		if ( WP_DEBUG === false ) {
			wp_register_script( 'wpaudio', WPAUDIO_URL . '/wpaudio.min.js', Array('jquery'), $wpa_version, true );
		}
		else {
			wp_register_script( 'wpaudio', WPAUDIO_URL . '/wpaudio.js', Array('jquery'), $wpa_version, true);
		}
		wp_enqueue_script( 'wpaudio' );
	}
	else {
		wp_enqueue_script('jquery');
		add_action('wp_footer', 'wpaFooterForOldVersions');
	}
}

## WPaudio style, jQuery, SWFObject
function wpaHead(){
	global $wpa_options;
	# Put all styles into the _wpaudio settings object
	$style = '';
	foreach ( $wpa_options as $key => $value ) {
		$exploded = explode('_', $key, 3);
		if ( $exploded[1] == 'style' ) {
			$style .= $exploded[2] . ":'$value',";
		}
	}
	$style = trim( $style, ',' );
	$style = '{' . $style . '}';
	# Common JS
	$wpa_pref_link_mp3 = ($wpa_options['wpa_pref_link_mp3']) ? 'true' : 'false';
	$head = "<script type='text/javascript'>/* <![CDATA[ */ var _wpaudio = {url: '" . WPAUDIO_URL . "', enc: {}, convert_mp3_links: $wpa_pref_link_mp3, style: $style}; /* ]]> */</script>";
	echo $head;
}

function wpaFooterForOldVersions() {
	echo '<script type="text/javascript" src="' . WPAUDIO_URL . '/wpaudio.min.js"></script>';
}

# Used only for wpaudio shortcode tags
function wpaShortcode($atts){
	# Convert shortcodes to WPaudio player depending on settings
	extract(shortcode_atts(Array(
		'url' => false,
		'text' => false,
		'dl' => true,
		'autoplay' => false
	), $atts));
	# If no url, return with nothing
	if (!$url)
		return;
	# Get player HTML and JS
	return wpaLink($url, $text, $dl, $autoplay);
}

# Make WPA link
function wpaLink($url, $text = false, $dl = true, $autoplay = false) {
	$id = uniqid('wpaudio-');
	$class = 'wpaudio';
	$html = '';
	# Handle dl URLs and no dl players
	if ($dl == '0') {
		$js_url = wpaUnicode($url);
		$href = '#';
		$class .= ' wpaudio-nodl';
	}
	elseif (is_string($dl)) {
		$js_url = wpaUnicode($url);
		$href = $dl;
	}
	else {
		$href = $url;
	}
	if (isset($js_url)) {
		$class .= ' wpaudio-enc';
		$html .= "<script type='text/javascript'>_wpaudio.enc['$id'] = '$js_url';</script>";
	}
	# Handle blank text
	if (!$text) {
		$text = basename($url);
		$class .= ' wpaudio-readid3';
	}
	# Autoplay
	if ($autoplay == '1') {
		$class .= ' wpaudio-autoplay';
	}
	$html .= "<a id='$id' class='$class' href='$href'>$text</a>";
	return $html;
}

# Used for audio tags
function wpaFilter($content){
	## Convert audio tags and links to WPaudio player depending on settings
	$tag_regex = '/\[audio:(.*?)\]/';
	$tag_match = preg_match_all($tag_regex, $content, $tag_matches);
	# Replace audio tags with player links
	if ($tag_match){
		foreach ($tag_matches[1] as $key => $value){
			# This is one tag, first get parameters and URLs
			$params = explode('|', $value);
			$clips = Array('urls' => Array(), 'titles' => Array(), 'artists' => Array());
			$clips['urls'] = explode(',', $params[0]);
			# Process extra parameters if they exist
			for ($i=1; $i<count($params); $i++) {
				# Get the parameter name and value
				$param = explode('=', $params[$i]);
				if ($param[0] == 'titles' || $param[0] == 'artists')
					$clips[$param[0]] = explode(',', $param[1]);
			}
			# Get player(s)
			$player = '';
			foreach ($clips['urls'] as $ukey => $uvalue) {
				$text = '';
				$text .= (isset($clips['artists'][$ukey])) ? $clips['artists'][$ukey] : '';
				$text .= (isset($clips['artists'][$ukey]) && isset($clips['titles'][$ukey])) ? ' - ' : '';
				$text .= (isset($clips['titles'][$ukey])) ? $clips['titles'][$ukey] : '';
				if (!$text) $text = false;
				$player .= wpaLink($uvalue, $text);
			}
			$content = str_replace($tag_matches[0][$key], $player, $content);
		}
	}
	return $content;
}

# Convert string to unicode (to conceal mp3 URLs)
include 'php-utf8/utf8.inc';
function wpaUnicode($str){
	$uni = utf8ToUnicode(utf8_encode($str));
	$output = '';
	foreach ($uni as $value){
		$output .= '\u' . str_pad(dechex($value), 4, '0', STR_PAD_LEFT);
	}
	return $output;
}

## WP admin menu
function wpa_menu() {
	add_options_page('WPaudio Options', 'WPaudio', 'switch_themes', __FILE__, 'wpa_menu_page');
}
function wpa_menu_page() {
	global $wpa_options;
	if ($_POST) {
		# Checkboxes need values
		$wpa_checkboxes = Array(
			'wpa_pref_link_mp3',
			'wpa_tag_audio',
			'wpa_track_permalink'
		);
		foreach ($wpa_checkboxes as $value) {
			$_POST[$value] = (isset($_POST[$value]) && $_POST[$value]) ? 1 : 0;
		}
		# Now process and save all options
		foreach ($wpa_options as $key => $value) {
			if (isset($_POST[$key]) && !is_null($_POST[$key]) && $_POST[$key] !== '')
				$wpa_options[$key] = $_POST[$key];
		}
		update_option('wpaudio_options', $wpa_options);
	}
	wpaOptions();
	?>
<!-- wpa menu begin -->
<div class="wrap">
<h2>WPaudio Options</h2>
<form method="POST" action="">
<?php wp_nonce_field('update-options'); ?>

<div id="poststuff" class="metabox-holder">
	<div class="meta-box-sortables">
		<div class="postbox">
			<h3 class="hndle"><span>Links</span></h3>
			<div class="inside">
				<ul>
					<li>WPaudio will always convert links with the <span style="font-family: Courier, Serif">wpaudio</span> class.  You optionally handle ALL mp3 links too.</li>
					<li><label for="wpa_pref_link_mp3"><input name="wpa_pref_link_mp3" id="wpa_pref_link_mp3" type="checkbox" <?php if ($wpa_options['wpa_pref_link_mp3']) echo ' checked="yes"'; ?>>
						Convert all mp3 links - <span style="font-family: Courier, Serif">&lt;a href="http://domain.com/song.mp3"&gt;Link&lt;/a&gt;</span></label></li>
				</ul>
			</div>
		</div>
		<div class="postbox">
			<h3 class="hndle"><span>Tags</span></h3>
			<div class="inside">
				<ul>
					<li>WPaudio will always convert <span style="font-family: Courier, Serif">[wpaudio]</span> tags, but it can also handle tags from other audio players.</li>
					<li><label for="wpa_tag_audio"><input name="wpa_tag_audio" id="wpa_tag_audio" type="checkbox" <?php if ($wpa_options['wpa_tag_audio']) echo ' checked="yes"'; ?>>
						Handle Audio Player tags - <span style="font-family: Courier, Serif">[audio:http://domain.com/song.mp3]</span></label></li>
				</ul>
			</div>
		</div>
		<div class="postbox">
			<h3 class="hndle"><span>Style</span></h3>
			<div class="inside">
				<ul>
					<li><a href="#" onclick="jQuery('.wpa_style_advanced').css('display', 'block');">It's not necessary to adjust these settings, but click here for advanced options.</a></li>
				</ul>
				<ul class="wpa_style_advanced" style="display: none;">
					<li>Optionally customize WPaudio's font</li>
					<li><label for="wpa_style_text_font"><input type="text" name="wpa_style_text_font" id="wpa_style_text_font" value="<?php echo $wpa_options['wpa_style_text_font']; ?>"> Font face</label></li>
					<li><label for="wpa_style_text_size"><input type="text" name="wpa_style_text_size" id="wpa_style_text_size" value="<?php echo $wpa_options['wpa_style_text_size']; ?>"> Font size</label></li>
					<li><label for="wpa_style_text_weight"><select name="wpa_style_text_weight" id="wpa_style_text_weight">
						<option value="inherit" <?php if ($wpa_options['wpa_style_text_weight'] == 'inherit') echo ' selected'; ?>>Inherit</option>
						<option value="normal" <?php if ($wpa_options['wpa_style_text_weight'] == 'normal') echo ' selected'; ?>>Normal</option>
						<option value="bold" <?php if ($wpa_options['wpa_style_text_weight'] == 'bold') echo ' selected'; ?>>Bold</option>
						</select> Font weight</label></li>
					<li><label for="wpa_style_text_letter_spacing"><input type="text" name="wpa_style_text_letter_spacing" id="wpa_style_text_letter_spacing" value="<?php echo $wpa_options['wpa_style_text_letter_spacing']; ?>"> Letter spacing</label></li>
				</ul>
				<ul class="wpa_style_advanced" style="display: none;">
					<li>Optionally customize colors (Most commonly 3 or 6 character <a href="http://en.wikipedia.org/wiki/Web_colors#Color_table" target="_blank">hex codes</a>.  For example: <span style="font-family: Courier, Serif">#2244ff</span>)</li>
					<li><label for="wpa_style_text_color"><input type="text" name="wpa_style_text_color" id="wpa_style_text_color" value="<?php echo $wpa_options['wpa_style_text_color']; ?>" size="7"> Text color</label></li>
					<li><label for="wpa_style_link_color"><input type="text" name="wpa_style_link_color" id="wpa_style_link_color" value="<?php echo $wpa_options['wpa_style_link_color']; ?>" size="7"> Link color</label></li>
					<li><label for="wpa_style_link_hover_color"><input type="text" name="wpa_style_link_hover_color" id="wpa_style_link_hover_color" value="<?php echo $wpa_options['wpa_style_link_hover_color']; ?>" size="7"> Link hover color</label></li>
					<li><label for="wpa_style_bar_base_bg"><input type="text" name="wpa_style_bar_base_bg" id="wpa_style_bar_base_bg" value="<?php echo $wpa_options['wpa_style_bar_base_bg']; ?>" size="7"> Bar base background</label></li>
					<li><label for="wpa_style_bar_load_bg"><input type="text" name="wpa_style_bar_load_bg" id="wpa_style_bar_load_bg" value="<?php echo $wpa_options['wpa_style_bar_load_bg']; ?>" size="7"> Bar load background</label></li>
					<li><label for="wpa_style_bar_position_bg"><input type="text" name="wpa_style_bar_position_bg" id="wpa_style_bar_position_bg" value="<?php echo $wpa_options['wpa_style_bar_position_bg']; ?>" size="7"> Bar position background</label></li>
				</ul>
			</div>
		</div>
		<div class="postbox">
			<h3 class="hndle"><span>Notification</span></h3>
			<div class="inside">
				<ul>
					<li>I love seeing who's using my plugin!  Please select this option to enable a notification when a post containing the player is published so I can come check out your site.  Your blog may even be featured on WPaudio.com.  Thanks!</li>
					<li><label for="wpa_track_permalink"><input name="wpa_track_permalink" id="wpa_track_permalink" type="checkbox" <?php if ($wpa_options['wpa_track_permalink']) echo ' checked="yes"'; ?>>
						Allow WPaudio notification</label></li>
				</ul>
			</div>
		</div>
	</div>
</div>

<p class="submit">
	<input class="button-primary" type="submit" value="Save Changes">
</p>

</form>
</div>
<!-- wpa menu end -->
<?php
}

## WP new post - add ping if contains wpaudio
function wpaPostNew($id) {
	$post = get_post($id);
	if (strpos(strtolower($post->post_content), 'wpaudio') !== false) {
		$permalink = rawurlencode(get_permalink($id));
		if (function_exists('curl_init') && function_exists('curl_setopt') && function_exists('curl_exec') && function_exists('curl_close')) {
			$ch = curl_init("http://wpaudio.com/t/?url_post=$permalink");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_exec($ch);
			curl_close($ch);
		}
	}
}

?>
