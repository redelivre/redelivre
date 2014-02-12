<?php 
/*
Plugin Name: WP-licenses
Plugin URI: http://www.billyblay.com/category/wordpress/wp-licenses/
Description: Este plugin adiciona as licenças Creative Commons, Copyright e domínio público aos posts. Para para personalizar as suas licenças vá até o menu Condigurações → <a href="options-general.php?page=wp_licenses_key"> WP-licenses </a>.
Version: 0.0.7
Author: Billy Blay
Author URI: http://www.billyblay.com/
*/

$licensepluginurl = trailingslashit(get_bloginfo('wpurl')).PLUGINDIR.'/'.dirname(plugin_basename(__FILE__));

function wp_licenses_lang_init() {
  load_plugin_textdomain( 'wp-licenses', false, dirname( plugin_basename( __FILE__ ) ). '/lang/' ); 
}
add_action('init', 'wp_licenses_lang_init');

$wplang = get_locale();

add_option('license-title','1','','yes');
add_option('license-tooltip','1','','yes');
add_option('license-theme','1','','yes');
add_option('license-content','1','','yes');
add_option('license-version','3.0','','yes');
add_option('license-jurisdiction','br','','yes');
add_option('license-language',$wplang,'','yes');

$licenseimageinfo['cr']['name'] = 'copyright';
$licenseimageinfo['cr']['alt'] = __('All the rights reserved (Copyright)','wp-licenses');
$licenseimageinfo['cr']['title'] = __('Only the author has the rights to give or commercialize this work.','wp-licenses');

$licenseimageinfo['pd']['name'] = 'pd';
$licenseimageinfo['pd']['alt'] = __('Public Domain','wp-licenses');
$licenseimageinfo['pd']['title'] = __('The work is available for distribution without any commercial purposes.','wp-licenses');

$licenseimageinfo['cl']['name'] = 'copyleft';
$licenseimageinfo['cl']['alt'] = __('Some rights are reserved','wp-licenses');
$licenseimageinfo['cl']['title'] = __('Everybody has the right to copy and distribute this work, since its right credits are given.','wp-licenses');

$licenseimageinfo['remix']['name'] = 'remix';
$licenseimageinfo['remix']['alt'] = __('to Remix','wp-licenses');
$licenseimageinfo['remix']['title'] = __('to adapt the work','wp-licenses');

$licenseimageinfo['share']['name'] = 'share';
$licenseimageinfo['share']['alt'] = __('to Share','wp-licenses');
$licenseimageinfo['share']['title'] = __('to copy, distribute and transmit the work.','wp-licenses');

$licenseimageinfo['by']['name'] = 'by';
$licenseimageinfo['by']['alt'] = __('Attribution','wp-licenses');
$licenseimageinfo['by']['title'] = __('You let others copy, distribute, display, and perform your copyrighted work - and derivative works based upon it - but only if they give credit the way you request.','wp-licenses');

$licenseimageinfo['nc']['name'] = 'nc';
$licenseimageinfo['nc']['alt'] = __('Noncommercial','wp-licenses');
$licenseimageinfo['nc']['title'] = __('You let others copy, distribute, display, and perform your work - and derivative works based upon it - but for noncommercial purposes only.','wp-licenses');

$licenseimageinfo['sa']['name'] = 'sa';
$licenseimageinfo['sa']['alt'] = __('Share Alike','wp-licenses');
$licenseimageinfo['sa']['title'] = __('You allow others to distribute derivative works only under a license identical to the license that governs your work.','wp-licenses');

$licenseimageinfo['nd']['name'] = 'nd';
$licenseimageinfo['nd']['alt'] =  __('No Derivative Works','wp-licenses');
$licenseimageinfo['nd']['title'] =  __('You let others copy, distribute, display, and perform only verbatim copies of your work, not derivative works based upon it.','wp-licenses');

/* ------------------------------------------------------ */ 

$cl 	= licenses_image_mount("copyleft");
$cr 	= licenses_image_mount("copyright");
$remix 	= licenses_image_mount("remix");
$share 	= licenses_image_mount("share");
$by 	= licenses_image_mount("by");
$pd  	= licenses_image_mount("pd");
$nc 	= licenses_image_mount("nc");
$sa 	= licenses_image_mount("sa");
$nd 	= licenses_image_mount("nd");

/* ------------------------------------------------------ */ 

function licenses_image_mount($sel) {
	global $licensepluginurl;
	global $licenseimageinfo;
	$licenseimagetheme = get_option('license-theme');
	$licensetooltip = get_option('license-tooltip');
	
	foreach ($licenseimageinfo as $value) {
		if ($sel == $value['name']) {		
			$licenseimagetag = '<img src="'.$licensepluginurl.'/img/licenses/t'.$licenseimagetheme.'/'.$value['name'].'.gif" alt="'.$value['alt'].'" ';
			if ($licensetooltip == 1) {
				$licenseimagetag .= 'title="'.$value['alt'].' -- '.$value['title'].'" class="cc-tooltip" />';
			} else {
				$licenseimagetag .=  'title="'.$value['alt'].'" />';
			}
		}
	}
	return $licenseimagetag;
}
/* ------------------------------------------------------ */ 
function wp_licences_add_custom_box() {
	if( function_exists( 'add_meta_box' )) {
		add_meta_box( 'wp_licences_sectionid', __( 'Which kind of license will be used for this post?', 'wp-licenses' ), 'wp_licences_inner_custom_box', 'post', 'normal','high' );
	} else {
		add_action('dbx_post_advanced', 'wp_licences_old_custom_box' );
	}
}
/* Prints the edit form for pre-WordPress 2.5 post/page */

function wp_licences_old_custom_box() {

	echo '<div class="dbx-b-ox-wrapper">';
	echo '<fieldset id="myplugin_fieldsetid" class="dbx-box">';
	echo '<div class="dbx-h-andle-wrapper"><h3 class="dbx-handle">' .  __( 'Which kind of license will be used for this post?', 'wp-licenses' ) . "</h3></div>";   
	echo '<div class="dbx-c-ontent-wrapper"><div class="dbx-content">';
	// output editing form
	wp_licences_inner_custom_box();
	// end wrapper
	echo "</div></div></fieldset></div>\n";
}

function wp_licences_inner_custom_box() {
	global $post;
	$direitos 	  = stripslashes(get_post_meta($post->ID, 'direitos', true));
	$usocomercial = stripslashes(get_post_meta($post->ID, 'usocomercial', true));
	$obraderivada = stripslashes(get_post_meta($post->ID, 'obraderivada', true));
	    
    echo '<input value="wplicense_edit" type="hidden" name="wplicense_edit" />';
	
    echo '<p id="intro" class="box shadow">'. __("You just created a work that you're proud of. Now it's time to become creative about how to make it available.",'wp-licenses').'</p>';
	
	echo '<div id="direitos">';
	
	echo '<p>';
	echo '<input type="radio" class="radio" name="wplicense_direitos" id="direitos_0" value="pd" ';
	if ($direitos == "pd") { echo ' checked = "checked" ';}
	echo ' />';
    echo '<label for="direitos_0">'. __('Public Domain','wp-licenses').'</label><br />';
    echo '<small>('. __('The work is available for distribution without any commercial purposes.','wp-licenses').')</small> </p>';

    echo '<p>';
    echo '<input type="radio" class="radio" name="wplicense_direitos" id="direitos_1" value="copyright" ';
	if ($direitos == "copyright") { echo 'checked = "checked" ';}
	echo '/>';
    echo '<label for="direitos_1">'. __('All the rights reserved (Copyright)','wp-licenses').'</label><br />';
    echo '<small>('. __('Only you have the right to give or commercialize this work.','wp-licenses').')</small></p>';
        
	echo '<p>';
    echo '<input type="radio" class="radio" name="wplicense_direitos" id="direitos_2" value="copyleft" ';
   	if ($direitos == "copyleft") { echo 'checked = "checked" ';} 
	echo ' />';
    echo '<label for="direitos_2" id="lbl-alguns-direitos">'. __('Some rights are reserved (Creative Commons)','wp-licenses').'</label><br />';
    echo '<small>('. __('Everybody has the right to copy and distribute this work, since its right credits are given.','wp-licenses').')</small></p></div>';
             
    echo '<div id="alguns-direitos" class="box ';
	if ($direitos != "copyleft") { echo 'wplicense_none';} 
	echo '">';
    echo '<p id="info">'. __('The Creative Commons licences help you to share your work keeping its rights. Other people may copy or distribute your work, since they give its proper credits and only under the conditions required by you.','wp-licenses').'</p>';
     
	echo '<h4>'. __('Allow commercial uses of your work?','wp-licenses').'</h4>';
    echo '<p>';
    echo '<input name="wplicense_usocomercial" type="radio" class="radio" id="radio" value="1"';
	if ($direitos == "copyleft" && $usocomercial == "1") { echo 'checked = "checked" ';} 
	echo ' />';
    echo '<label for="radio">'. __('Yes','wp-licenses').'</label></p>';
	
    echo '<p>';
    echo '<input type="radio" class="radio" name="wplicense_usocomercial" id="radio2" value="2" ';
	if ($direitos == "copyleft"  &&  $usocomercial == "2") { echo 'checked = "checked" ';} 
	echo ' />';
	echo '<label for="radio2">'. __('No','wp-licenses').'</label></p>';
	
    echo '<h4>'. __('Allow modifications of your work?','wp-licenses').'</h4>';
    echo '<p>';
    echo '<input type="radio" class="radio" name="wplicense_obraderivada" id="radio3" value="1" ';
	if ($direitos == "copyleft"  &&  $obraderivada == "1") { echo 'checked = "checked"';} 
	echo ' />';
    echo '<label for="radio3">'. __('Yes','wp-licenses').'</label> </p>';
                 
    echo '<p>';
    echo '<input type="radio" class="radio" name="wplicense_obraderivada" id="radio4" value="2" ';
	if ($direitos == "copyleft"  &&  $obraderivada == "2") { echo 'checked = "checked"';} 
	echo ' />';
    echo '<label for="radio4">'. __('Yes, as long as others share alike','wp-licenses').'</label> </p>';
                 
    echo '<p>';
    echo '<input type="radio" class="radio" name="wplicense_obraderivada" id="radio5" value="3" ';
	if ($direitos == "copyleft"  &&  $obraderivada == "3") { echo 'checked = "checked"';} 
	echo ' />';
    echo '<label for="radio5">'. __('No','wp-licenses').'</label></p></div>';

	the_licenses();
}

/* ------------------------------------------------------ */ 

class wp_licences_plugin {
	function post_meta_tags($id) {
		$wplicense_edit = $_POST['wplicense_edit'];
		if (isset($wplicense_edit) && !empty($wplicense_edit)) {
				
			$direitos = $_POST['wplicense_direitos'];
			$usocomercial = $_POST['wplicense_usocomercial'];
			$obraderivada = $_POST['wplicense_obraderivada'];
				
			delete_post_meta($id, 'direitos');
			delete_post_meta($id, 'usocomercial');
			delete_post_meta($id, 'obraderivada');
							
			if (isset($direitos) 	 && !empty($direitos)) 	   { add_post_meta($id, 'direitos', $direitos);}
			if (isset($usocomercial) && !empty($usocomercial)) { add_post_meta($id, 'usocomercial', $usocomercial);}
			if (isset($obraderivada) && !empty($obraderivada)) { add_post_meta($id, 'obraderivada', $obraderivada);}
		}
	}
} 

/* ------------------------------------------------------ */ 

function the_licenses() {
	global $post;		
	$direitos = stripslashes(get_post_meta($post->ID, "direitos", true));
	$usocomercial = stripslashes(get_post_meta($post->ID, 'usocomercial', true));
	$obraderivada = stripslashes(get_post_meta($post->ID, 'obraderivada', true));
	global $cr, $pd, $cl, $by, $nc, $remix, $share, $sa, $nd;
	$licenseversion = get_option('license-version');
	$licenselanguage = get_option('license-language');
	$licensejurisdiction = get_option('license-jurisdiction');

	/* Creative commons */
	if ($direitos == "copyleft") {
		if ($usocomercial == "1") {
			if ($obraderivada == "1") { 
				$CC['print']['images'] = $share.$remix.$by;
				$CC['print']['text'] = __('Attribution','wp-licenses');
				$CC['print']['url'] = 'by';
			}
			if ($obraderivada == "2") { 	
				$CC['print']['images'] = $share.$remix.$by.$nc;
				$CC['print']['text'] = __('Attribution-Share Alike','wp-licenses');
				$CC['print']['url'] = 'by-sa';
			} 
			if ($obraderivada == "3") { 	
				$CC['print']['images'] = $share.$by.$nd;
				$CC['print']['text'] = __('Attribution-No Derivative Works','wp-licenses');
				$CC['print']['url'] = 'by-nd';
			}
		} 
	
		if ($usocomercial == "2") {
			if ($obraderivada == "1") { 		
				$CC['print']['images'] = $share.$remix.$by.$nc;
				$CC['print']['text'] = __('Attribution-Noncommercial','wp-licenses');
				$CC['print']['url'] = 'by-nc';
			}
			if ($obraderivada == "2") { 	
				$CC['print']['images'] = $share.$remix.$by.$nc.$sa;
				$CC['print']['text'] = __('Attribution-Noncommercial-Share Alike','wp-licenses');
				$CC['print']['url'] = 'by-nc-sa';
			} 
			if ($obraderivada == "3") { 
				$CC['print']['images'] = $share.$by.$nc.$nd;
				$CC['print']['text'] = __('Attribution-Noncommercial-No Derivative Works','wp-licenses');
				$CC['print']['url'] = 'by-nc-nd';
			}			
		} 
		/* Gera a licença padrão se nenhuma opção for escolhida durante a edição do post */
		if ($usocomercial == "" || $obraderivada == "") { 
				$CC['print']['images'] = $share.$remix.$by.$nc.$sa;
				$CC['print']['text'] = __('Attribution-Noncommercial-Share Alike','wp-licenses');
				$CC['print']['url'] = 'by-nc-sa';
		} 
		
	} 
	
	/* Outras licenças*/

	if ($direitos == "pd") { 
		$CC['print']['images'] = $pd;
		$obraderivada = ""; 
		$usocomercial = "";
	}
	if ($direitos == "copyright") { 
		$CC['print']['images'] = $cr;
		$obraderivada = ""; 
		$usocomercial = "";
	}
	if ($direitos != "") { 	
		
		if (is_admin()){ 
			echo '<h4>'.__('The post license will be:','wp-licenses').'</h4>'; 
		} else if ( get_option('license-title') == 1){ 
			echo '<strong>'.__('License:','wp-licenses').'</strong><br />'; 
		} 
		
		echo '<div id="wplicense_box">';
		if ($licenselanguage) $licenselanguage = '/deed.'.$licenselanguage;
		if ($licensejurisdiction) $licensejurisdiction = '/'.$licensejurisdiction;
		 
		foreach ($CC as $value) { 
			echo $value['images'];
			if (isset($value['url'])  != "") {
				echo '<div id="wplicense_link">';
				if (is_admin()) {
				echo '<a href="http://creativecommons.org/licenses/'.$value['url'].'/'.$licenseversion.$licensejurisdiction.$licenselanguage.'" rel="external">'.$value['text'].' '.$licenseversion.'</a>';
				} else {
				echo '<span xmlns:dct="http://purl.org/dc/terms/" href="http://purl.org/dc/dcmitype/Text" property="dct:title" rel="dct:type">'.get_the_title().'</span> ';
				echo __('by','wp-licenses').' <a xmlns:cc="http://creativecommons.org/ns#" href="'.get_author_posts_url(get_the_author_meta('ID')).'" property="cc:attributionName" rel="cc:attributionURL">'.get_the_author().'</a> '.__('is licensed under a','wp-licenses').' <a rel="license" href="http://creativecommons.org/licenses/'.$value['url'].'/'.$licenseversion.$licensejurisdiction.$licenselanguage.'">'.$value['text'].' '.$licenseversion.'</a>';
				}
				echo '</div>';
			}
		}
		echo '</div>';
	}
}

$licensecontent = get_option('license-content');
if ($licensecontent) { 
	add_filter( 'the_content', 'the_licenses' );
}

add_action('admin_menu', 'wp_licenses_menu');
	function wp_licenses_menu() {
	add_options_page('WP-licenses', 'WP-licenses', 'manage_options', 'wp_licenses_key', 'wp_licenses_options');
}

function wp_licenses_options() { ?>
<div class="wrap">
  	
	<?php screen_icon( 'options-general' ); ?>  
  	<h2>WP-licenses</h2>

  	<form method="post" action="options.php" class="wplicenses-option">
   
    <h3><?php _e('Options','wp-licenses') ?></h3>
    
    <fieldset>
        <legend><?php _e('Choose the theme','wp-licenses') ?></legend>
        <div id="type-1" class="list">
          <input type="radio" name="license-theme" value="1" <?php checked(1, get_option('license-theme')); ?> id="license-theme-1" />
          <label for="license-theme-1"><?php _e('Default','wp-licenses') ?></label>
        </div>
        <div id="type-2" class="list">
          <input type="radio" name="license-theme" value="2" <?php checked(2, get_option('license-theme')); ?> id="license-theme-2" />
          <label for="license-theme-2"><?php _e('Miniature ','wp-licenses') ?></label>
        </div>
    </fieldset>
    
    <fieldset>
    	<legend><?php _e('Title and Scripts','wp-licenses') ?></legend>
		
        <label>
      	<input type="checkbox" name="license-title" value="1" <?php checked(1, get_option('license-title')); ?> />
      	<?php _e('Show the title','wp-licenses') ?>
		</label><br />
        
        <label>
        <input type="checkbox" name="license-tooltip" value="1" <?php checked('1', get_option('license-tooltip')); ?> />
        <?php _e('Enable tooltip','wp-licenses') ?>
        </label>
        
    </fieldset>

    <fieldset>
    	<label><?php _e('Choose the version of your license','wp-licenses'); ?><br />
    	<select name="license-version">
        	<option value="2.0" <?php selected('2.0', get_option('license-version')); ?>>2.0</option>
            <option value="2.5" <?php selected('2.5', get_option('license-version')); ?>>2.5</option>
            <option value="3.0" <?php selected('3.0', get_option('license-version')); ?>>3.0</option>
        </select>
    	</label>
        <p><?php _e('You should use it for new works, and you may want to relicense existing works under it. No works are automatically put under the new license, however.','wp-licenses') ?></p>
        
        <label><?php _e('Jurisdiction of your license','wp-licenses') ?><br />
    	<select name="license-jurisdiction">
        	<option value="" <?php selected('', get_option('license-jurisdiction')); ?>><?php _e('International','wp-licenses') ?></option>
        	<option value="br" <?php selected('br', get_option('license-jurisdiction')); ?>><?php _e('Brazil','wp-licenses') ?></option>
        </select>
    	</label>
        <p><?php _e("Use the option 'International' if you desire a license using language and terminology from international treaties. If the licenses have been ported to your jurisdiction and you feel that your jurisdiction's ported licenses account for some aspect of local legislation that the international licenses do not, then you may want to consider which license is better suited for your needs",'wp-licenses') ?></p>
        
         <label><?php _e('Language','wp-licenses') ?><br />
    	<select name="license-language">
        	<option value="hy" <?php selected('hy', get_option('license-language')); ?>>Armenian</option>
            <option value="be" <?php selected('be', get_option('license-language')); ?>>Belarusian</option>
            <option value="es" <?php selected('es', get_option('license-language')); ?>>Castellano</option>
            <option value="es_ES" <?php selected('es_ES', get_option('license-language')); ?>>Castellano (España)</option>
            <option value="ca" <?php selected('ca', get_option('license-language')); ?>>Català</option>
            <option value="da" <?php selected('da', get_option('license-language')); ?>>Dansk</option>
            <option value="de" <?php selected('de', get_option('license-language')); ?>>Deutsch</option>
            <option value="et" <?php selected('et', get_option('license-language')); ?>>Eesti</option>
            <option value="en_US" <?php selected('en_US', get_option('license-language')); ?>>English</option>
            <option value="eo" <?php selected('eo', get_option('license-language')); ?>>Esperanto</option>
            <option value="fr" <?php selected('fr', get_option('license-language')); ?>>français</option>
            <option value="hr" <?php selected('hr', get_option('license-language')); ?>>hrvatski</option>
            <option value="it" <?php selected('it', get_option('license-language')); ?>>Italiano</option>
            <option value="lv" <?php selected('lv', get_option('license-language')); ?>>Latviski</option>
            <option value="hu" <?php selected('hu', get_option('license-language')); ?>>Magyar</option>
            <option value="nl" <?php selected('nl', get_option('license-language')); ?>>Nederlands</option>
            <option value="vi" <?php selected('vi', get_option('license-language')); ?>>Người Việt/Tiếng Việt</option>
            <option value="no" <?php selected('no', get_option('license-language')); ?>>Norsk</option>
            <option value="pl" <?php selected('pl', get_option('license-language')); ?>>polski</option>
            <option value="pt" <?php selected('pt', get_option('license-language')); ?>>Português</option>
            <option value="pt_BR" <?php selected('pt_BR', get_option('license-language')); ?>>Português (BR)</option>
            <option value="ro" <?php selected('ro', get_option('license-language')); ?>>română</option>
            <option value="sr_LATN" <?php selected('sr_LATN', get_option('license-language')); ?>>srpski (latinica)</option>
            <option value="fi" <?php selected('fi', get_option('license-language')); ?>>Suomeksi</option>
            <option value="fi" <?php selected('fi', get_option('license-language')); ?>>svenska</option>
            <option value="tr" <?php selected('tr', get_option('license-language')); ?>>Türkçe</option>
            <option value="cs" <?php selected('cs', get_option('license-language')); ?>>čeština</option>
            <option value="el" <?php selected('el', get_option('license-language')); ?>>Ελληνικά</option>
            <option value="mk" <?php selected('mk', get_option('license-language')); ?>>македонски</option>
            <option value="ru" <?php selected('ru', get_option('license-language')); ?>>Русский</option>
            <option value="sr" <?php selected('sr', get_option('license-language')); ?>>српски </option>
            <option value="ar" <?php selected('ar', get_option('license-language')); ?>>العربية</option>
            <option value="th" <?php selected('th', get_option('license-language')); ?>>ไทย</option>
            <option value="ka" <?php selected('ka', get_option('license-language')); ?>>ქართული</option>
            <option value="zh_HK" <?php selected('zh_HK', get_option('license-language')); ?>>中文 (香港)</option> 
            <option value="ja" <?php selected('ja', get_option('license-language')); ?>>日本語</option> 
            <option value="zh_TW" <?php selected('zh_TW', get_option('license-language')); ?>>華語 (台灣)</option> 
            <option value="ko" <?php selected('ko', get_option('license-language')); ?>>한국어</option>
        </select>
    	</label>
    </fieldset>
    <fieldset id="howtouse">
            <legend><?php _e('How to use','wp-licenses') ?></legend>
            
            <label>
            <input type="checkbox" name="license-content" value="1" <?php checked('1', get_option('license-content')); ?> />
            <?php _e('Show the license automatically at the end of the post content','wp-licenses') ?>
            </label>
            
            <p><?php _e('Or put the code above where you want to show the licenses','wp-licenses') ?></p>
            <code>&lt;?php if ( function_exists( 'the_licenses' ) ) { the_licenses(); } ?&gt;</code>
            
    </fieldset>
    
    <?php wp_nonce_field('update-options'); ?>
	<input type="hidden" name="action" value="update" />
    <input type="hidden" name="page_options" value="license-theme, license-tooltip, license-title, license-content, license-version, license-language, license-jurisdiction" />	
    <input type="submit" class="button-primary" value="<?php _e('Save Changes', 'wp-licenses') ?>" />

  </form>
 
</div>
<?php 
}

function wp_licenses_scripts() {  
	global $post, $licensepluginurl;	
	wp_enqueue_script('jquery'); 	
				
		wp_register_script ('tooltipScript', $licensepluginurl.'/js/tooltip.js', array('jquery'),'1.3');
		wp_enqueue_script  ('tooltipScript');
		
		wp_register_style ('tooltipStyle', $licensepluginurl.'/css/tooltip.css', false,'1', 'screen');
		wp_enqueue_style  ('tooltipStyle');

	if (is_admin()){ 
		wp_register_script('wpLicenseScript',  $licensepluginurl.'/js/wp-licenses.js', array('jquery'),'2.0');
		wp_enqueue_script ('wpLicenseScript');
		
		wp_register_style ('wpLicenseStyle', $licensepluginurl.'/css/wp-licenses.css', false, '2.0', 'screen');
		wp_enqueue_style  ('wpLicenseStyle');
	}
}

$_wplicense_plugin = new wp_licences_plugin();

add_action('do_meta_boxes', 'wp_licences_add_custom_box');
add_action('edit_post', array($_wplicense_plugin, 'post_meta_tags'));
add_action('publish_post', array($_wplicense_plugin, 'post_meta_tags'));
add_action('save_post', array($_wplicense_plugin, 'post_meta_tags'));
add_action('edit_page_form', array($_wplicense_plugin, 'post_meta_tags'));
add_action('wp_head', 'wp_licenses_scripts', 0);
add_action('admin_enqueue_scripts', 'wp_licenses_scripts');
?>