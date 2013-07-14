<?php
/*

Plugin Name:  Simple Dropbox Upload Form

Plugin URI:   http://cdsincdesign.com/simple-dropbox-upload-form/

Description:  Use the shortcode [simple-wp-dropbox] in any page to insert a Dropbox file upload form.

Version:      1.8.8

Author:       Creative Design Solutions

Author URI:   http://cdsincdesign.com/

*/

/*

Copyright (C) 2012 Steven Whitney(at)cdsincdesign.com

This program is free software: you can redistribute it and/or modify

it under the terms of the GNU General Public License as published by

the Free Software Foundation, either version 3 of the License, or

(at your option) any later version.

This program is distributed in the hope that it will be useful,

but WITHOUT ANY WARRANTY; without even the implied warranty of

MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the

GNU General Public License for more details.

You should have received a copy of the GNU General Public License

along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

define('USE_BUNDLED_PEAR', true);
//We need to set the PEAR_Includes folder in the path
if (USE_BUNDLED_PEAR)
	set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc/Dropbox/pear_includes' . PATH_SEPARATOR . get_include_path());
else
	set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc/Dropbox/pear_includes');

include ('inc/wpsdb_auth.php');
include ('inc/wpsdbClass.php');

function wpsdb_build_stylesheet_url() {
    echo '<link rel="stylesheet" href="' . plugins_url() . '/simple-dropbox-upload-form/css/wpsdb-style.css?build=' . date( "Ymd", strtotime( '-24 days' ) ) . '" type="text/css" media="screen" />';
}

function wpsdb_build_stylesheet_content() {
	//wp_deregister_script( 'jquery' );
	if( isset( $_GET['build'] ) && addslashes( $_GET['build'] ) == date( "Ymd", strtotime( '-24 days' ) ) ) {
		header("Content-type: text/css");
		header('Cache-Control: no-cache');
		header('Pragma: no-cache');
		header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
		echo "/* WP Simple Dropbox */\n";
		echo ".wp-dropbox {display: inline;}\n";
		echo "#wpsdb-success {color:".esc_attr( get_option( 'wpsdb_thank_color' ) ).";}\n";
		echo "#wpsdb-error{color:".esc_attr( get_option( 'wpsdb_thank_color' ) ).";}\n";
		echo "#TB_overlay{z-index:999999999 !important;}";
		echo "#TB_window{z-index:999999999 !important;}";
		define( 'DONOTCACHEPAGE', 1 ); // don't let wp-super-cache cache this page.
		die();
	}
}

if(!is_admin()){
	add_action( 'init', 'wpsdb_build_stylesheet_content' );
	add_action( 'wp_head', 'wpsdb_build_stylesheet_url' );
}

function show_dropbox($atts, $content = NULL){
		
	add_thickbox();
	
	$wpsdb_short_attr = shortcode_atts(
			array(
				'notification' => get_bloginfo('admin_email'),
				'fields' => 'none'
			), $atts );
			
	//print_r($wpsdb_short_attr);
			
	$wpsdb_up_method = get_option('wpsdb_php_pear');

	$wpsdb_path = get_option( 'wpsdb_path' );

	$wpsdb_tmp_path = get_option( 'wpsdb_temp_path' );

	$wpsdb_allow_ext = trim( get_option( 'wpsdb_allow_ext' ) );
	
	$wpsdb_thank_message = stripslashes(get_option( 'wpsdb_thank_message' ));
	
	$wpsdb_show_progress = get_option( 'wpsdb_show_progress' );
	
	$wpsdb_show_multi = get_option( 'wpsdb_show_multi' );
	
	$wpsdb_show_form = get_option( 'wpsdb_show_form' );
	
	$wpsdb_delete_file = get_option( 'wpsdb_delete_file' );

	$wpsdb_key = get_option( 'wpsdb_key' );

	$wpsdb_secret = get_option( 'wpsdb_secret' );

	$wpsdb_token = get_option( 'wpsdb_auth_token' );

	$wpsdb_token_secret = get_option( 'wpsdb_auth_token_secret' );
	
	echo '<div class="wp-dropbox">';

	$wpsshowform = "showit";

	try {

		if ($wpsdb_allow_ext == '')

			throw new Exception(__('Need to configure allowed file extensions!','simpleDbUpload'));

		if ((get_option('wpsdb_auth_step') < 3) or ($wpsdb_key == '') or ($wpsdb_secret == '') or ($wpsdb_token == '') or ($wpsdb_token_secret == '') )

			throw new Exception(__('Need to authorize plugin!','simpleDbUpload'));

	} catch(Exception $e) {

    	echo '<span id="wpsdb-error">'.__('Error:','simpleDbUpload'). ' ' . htmlspecialchars($e->getMessage()) . '</span>';

		$wpsshowform = "hideit";

	}
	
	if (isset($_POST['upupnaway']) and $_POST['upupnaway']) {
		try{
			$wpsdbMultiFiles = $_POST['multimages'];
			$wpsdbMultiFiles = split(",",$wpsdbMultiFiles);
			$wpsdbMultiFiles = array_filter($wpsdbMultiFiles);
	
			//echo count($wpsdbMultiFiles);
			if (!file_exists($wpsdb_tmp_path.'/')){
				if (!mkdir($wpsdb_tmp_path.'/'))
					throw new Exception(__('Internal Server Error!','simpleDbUpload'));
			}
			
			try {
				
				require_once (dirname( __FILE__ ) . '/inc/Dropbox/autoload.php');
				
				if($wpsdb_up_method == 'curl'){
					$oauth = new Dropbox_OAuth_Curl($wpsdb_key, $wpsdb_secret);
				}
				if (class_exists('HTTP_OAuth_Consumer') && $wpsdb_up_method == 'php'){
					$oauth = new Dropbox_OAuth_PHP($wpsdb_key, $wpsdb_secret);
				}elseif(class_exists('OAuth') && $wpsdb_up_method == 'pear'){
					$oauth = new Dropbox_OAuth_PEAR($wpsdb_key, $wpsdb_secret);	
				}
				
				$oauth->setToken($wpsdb_token,$wpsdb_token_secret);
				
				$dropbox = new Dropbox_API($oauth);
				
			} catch(Exception $e) {
				
				echo '<span id="wpsdb-error">'.__('Error:','simpleDbUpload'). ' ' . htmlspecialchars($e->getMessage()) . '</span>';
				
				$wpsshowform = "hideit";
			
			}
				
			foreach ($wpsdbMultiFiles as $wpsdbMF){
				//echo $wpsdbMF.'<br />';
				
				$wpsdbNewFileName = explode(".",$wpsdbMF);
				$wpsdbTmpFile = $wpsdb_tmp_path.'/'.preg_replace('/\s+/', '_', $wpsdbNewFileName[0]) . "_" . rand(1000,9999) . "_" . date("Y-m-d").".".preg_replace("/\s+/", '_', end($wpsdbNewFileName));//$wpsdbNewFileName[1]);
				//echo $wpsdbTmpFile.'<br />';
				//echo $wpsdb_tmp_path.'/tmp/'.$wpsdbMF.'<br/>';
				
				if (!rename($wpsdb_tmp_path.'/'.$wpsdbMF, $wpsdbTmpFile)){
					throw new Exception(__('Problem with uploaded file!','simpleDbUpload'));
					exit();
				}
					
				// Upload
	
				$wpschunks = explode("/",$wpsdbTmpFile);
	
				for($i = 0; $i < count($wpschunks); $i++){
					$c = $i;
				}
				
				if ( !$dropbox->putFile(trim($wpsdb_path,'/').'/'.$wpschunks[$c], $wpsdbTmpFile,"dropbox") ) {
					throw new Exception(__('ERROR! Upload Failed.','simpleDbUpload'));
				}
				if($wpsdb_delete_file == "True"){
					if (isset($wpsdbTmpFile) && file_exists($wpsdbTmpFile)){
						unlink($wpsdbTmpFile);
					}
				}
	
			}
			
			//$adminEmail = get_bloginfo('admin_email');
			$adminEmail = $wpsdb_short_attr['notification'];
			$headers = 'From: Simple Dropbox <simpledropbox@'.$_SERVER['HTTP_HOST'].'>' . "\r\n";
			wp_mail($adminEmail, 'New File Received', 'You have received a new file from the dropbox form on your website.', $headers);//, $attachments);
			
			echo (get_option('wpsdb_redirect_page')!='')?'<script>window.location = "'.get_page_link(get_option('wpsdb_redirect_page')).'"</script>':'<span id="wpsdb-success">'.$wpsdb_thank_message.'</span>';
			//echo '<span id="wpsdb-success">'.$wpsdb_thank_message.'</span>';
	
			if($wpsdb_show_form == "True"){
				$wpsshowform = "showit";
			}else{
				$wpsshowform = "hideit";
			}
			
		}catch(Exception $e) {
		
		echo '<span id="wpsdb-error">'.__('Error: ','simpleDbUpload') . ' ' . html_entity_decode($e->getMessage()) . '</span>';
		
		$wpsshowform = "showit";
		
		}
	}

	if (isset($_POST['gogogadget']) and $_POST['gogogadget']) {

		try {
			
			$wpsallowedExtensions = split("[ ]+", $wpsdb_allow_ext);
			
			foreach ($_FILES as $file) { 
				
				if ($file['tmp_name'] > '') { 
					//if($wpsdb_up_method == 'curl'){
						$file['name'] = str_replace(' ', '_', $file['name']);
					/*}else{
						$file['name'] = str_replace(' ', '%20', $file['name']);
					}*/
					
					if (!in_array(end(explode(".", strtolower($file['name']))), $wpsallowedExtensions)) { 
					
					$wpsext = implode(", ", $wpsallowedExtensions);
					
					throw new Exception(__('Allowed file extensions: ','simpleDbUpload').''.$wpsext);			
					} 	
				} 
			} 

			// Rename uploaded file to reflect original name
			
			if ($_FILES['file']['error'] !== UPLOAD_ERR_OK)
				throw new Exception(__('File was not uploaded from your computer.','simpleDbUpload'));
			
			if (!file_exists($wpsdb_tmp_path)){
				if (!mkdir($wpsdb_tmp_path))
					throw new Exception(__('Internal Server Error!','simpleDbUpload'));
			}
			
			if ($_FILES['file']['name'] === "")
				throw new Exception(__('File name not supplied by the browser.','simpleDbUpload'));
						
			$wpsnew_file_name = explode(".",$file['name']);
			
			$wpstmpFile = $wpsdb_tmp_path.'/'.str_replace("/\0", '_', $wpsnew_file_name[0]) . "_" . rand(1000,9999) . "_" . date("Y-m-d") . "." . str_replace("/\0", '_', end($wpsnew_file_name));//$wpsnew_file_name[1]);

			if (!move_uploaded_file($_FILES['file']['tmp_name'], $wpstmpFile))
				throw new Exception(__('Problem with uploaded file!','simpleDbUpload'));

			// Upload

			$wpschunks = explode("/",$wpstmpFile);

			for($i = 0; $i < count($wpschunks); $i++){
				$c = $i;
			}
			
			try {
			
				require_once (dirname( __FILE__ ) . '/inc/Dropbox/autoload.php');
				//include 'inc/Dropbox/autoload.php';
				
				if($wpsdb_up_method == 'curl'){
					$oauth = new Dropbox_OAuth_Curl($wpsdb_key, $wpsdb_secret);
				}
				if (class_exists('HTTP_OAuth_Consumer') && $wpsdb_up_method == 'php'){
					$oauth = new Dropbox_OAuth_PHP($wpsdb_key, $wpsdb_secret);
				}elseif(class_exists('OAuth') && $wpsdb_up_method == 'pear'){
					$oauth = new Dropbox_OAuth_PEAR($wpsdb_key, $wpsdb_secret);	
				}
	
				$oauth->setToken($wpsdb_token,$wpsdb_token_secret);
	
				$dropbox = new Dropbox_API($oauth);
	
			} catch(Exception $e) {
	
				echo '<span id="wpsdb-error">'.__('Error:','simpleDbUpload'). ' ' . htmlspecialchars($e->getMessage()) . '</span>';
	
				$wpsshowform = "hideit";
	
			} 
			
			if ( !$dropbox->putFile(trim($wpsdb_path,'/').'/'.$wpschunks[$c], $wpstmpFile,"dropbox") ) {
				throw new Exception(__('ERROR! Upload Failed.','simpleDbUpload'));
			}

			echo '<span id="wpsdb-success">'.$wpsdb_thank_message.'</span>';

			if($wpsdb_show_form == "True"){
				$wpsshowform = "showit";
			}else{
				$wpsshowform = "hideit";
			}
			
			if($wpsdb_delete_file == "True"){
				$wpsdelete_file = "deleteit";
			}else{
				$wpsdelete_file = "keepit";
			}
			
			//$adminEmail = get_bloginfo('admin_email');
			$adminEmail = $wpsdb_short_attr['notification'];
			//$adminEmail = "hiphopsmurf@gmail.com";
			$headers = 'From: Simple Dropbox <simpledropbox@'.$_SERVER['HTTP_HOST'].'>' . "\r\n";
			wp_mail($adminEmail, 'New File Received', 'You have received a new file from the dropbox form on your website.', $headers);//, $attachments);

		} catch(Exception $e) {

				echo '<span id="wpsdb-error">'.__('Error: ','simpleDbUpload') . ' ' . html_entity_decode($e->getMessage()) . '</span>';

			$wpsshowform = "showit";

			$wpsdelete_file = "deleteit";

		}
		

	// Clean up

	if($wpsdelete_file == "deleteit") {
			if (isset($wpstmpFile) && file_exists($wpstmpFile))
				unlink($wpstmpFile);
		}
	}

	if($wpsshowform == "showit") {
		if($wpsdb_show_multi=="True"){
		?>
          <p><a class="thickbox" href="<?php echo plugins_url('',__FILE__).'/multi.php?'; ?>&amp;height=500&amp;width=1000&amp;TB_iframe=true"><?php echo get_option('wpsdb_multi_link_text');?></a></p>
		<form name="multi_image" method="POST" enctype="multipart/form-data">
          	<input type="hidden" name="upupnaway" value="1"/>
               <input type="hidden" name="multimages" id="multimages" value=""/>
          </form>
          <?php
		}
		if($wpsdb_show_progress=="True"){
		?>
          <style>
		.progress{
			position:relative;
			width:200px;
			border:1px solid #ddd;
			padding:1px;border-radius:3px;
			margin-top:10px;
		}
		.bar{
			background-color:#b4f5b4;
			width:0%;
			height:20px;
			border-radius:3px;
		}
		.percent{
			position:absolute;
			display:inline-block;
			top:3px;
			left:48%;
		}
		#cover {
		    background: url("<?php echo plugins_url('/images/gears_animated.gif',__FILE__); ?>") no-repeat scroll center center;
		    position: fixed;
		    height: 100%;
		    width: 100%;
		    overflow:hidden;
		    z-index:999999999;
		}
          </style>
          <?php } ?>
          <form name="single_image" method="POST" enctype="multipart/form-data">
               <input type="hidden" name="gogogadget" value="1"/> 
				<?php
				$wpsdb_fields_exploded = explode(",",$wpsdb_short_attr['fields']);
				
				if($wpsdb_fields_exploded[0]!='none'):
					foreach($wpsdb_fields_exploded as $wpsdb_field):
						$wpsdb_field = trim($wpsdb_field);
						echo '<p><span id="wpsdb_'.$wpsdb_field.'" style="margin-right:3px;">'.$wpsdb_field.':</span>';
						echo '<input name="wpsdb_'.$wpsdb_field.'" id="wpsdb_'.$wpsdb_field.'" type="text" size="34"/>';
						echo '</p>';
					endforeach;
				endif;
				?>
               <input class="input_form" size="34" type="file" name="file" />
               <input id="submit_button" type="submit" value="<?php _e('Submit','simpleDbUpload'); ?>" />
          </form>
          <?php
		if($wpsdb_show_progress=="True"){?>
          <div class="progress">
             <div class="bar"></div>
             <div class="percent">0%</div>
         </div>
         
         <?php /*<div id="status"></div>*/?>
	    <script src="<?php echo plugins_url('/js/jquery.js',__FILE__); ?>"></script>
		<script src="<?php echo plugins_url('/js/jquery.form.js',__FILE__); ?>"></script>
          <script src="<?php echo plugins_url('/js/overlay.js',__FILE__); ?>"></script>
          <script>
		
		jQuery(document).ready(function(){
			var h = jQuery.noConflict(),bar=h('.bar'),percent=h('.percent');
			
			var cover = h('<div />').prependTo('body');
			cover.attr({'id':'cover','style':'display:none'});
			
			h('form').ajaxForm({
				beforeSend:function(){
					<?php /*status.empty();*/?>
					var percentVal='0%';
					bar.width(percentVal)
					percent.html(percentVal);
				},
				uploadProgress:function(event,position,total,percentComplete){
					h(this).openOverlay({sColor:'#ffffff',iOpacity:60});
					h('#cover').fadeIn(500);
					var percentVal=percentComplete+'%';
					bar.width(percentVal)
					percent.html(percentVal);
				},
				success:function(){
					var percentVal='100%';
					bar.width(percentVal)
					percent.html(percentVal);
				},
				complete:function(xhr){
					<?php /*status.html(xhr.responseText);*/?>
					h('#cover').fadeOut(500);
					h(this).openOverlay.closeOverlay();
					setTimeout(function(){
						window.location = <?php echo (get_option('wpsdb_redirect_page'))?'"'.get_page_link(get_option('wpsdb_redirect_page')).'"':'window.location.href.toString()'; ?>;
					},1000);
				}
			});
		})
          </script>
          <?php
		}
	}

	echo "</div>";

}

	if(!function_exists('formatBytes')){
	function formatBytes($bytes, $precision = 2) { 
		$units = array('B', 'KB', 'MB', 'GB', 'TB'); 
		
		$bytes = max($bytes, 0); 
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
		$pow = min($pow, count($units) - 1); 
		
		// Uncomment one of the following alternatives
		$bytes /= pow(1024, $pow);
		// $bytes /= (1 << (10 * $pow)); 
		
		return round($bytes, $precision) . ' ' . $units[$pow]; 
		}
	}
	
	function wpsdb_admin_redirect(){
		print'
		<script type="text/javascript">
		<!--
		window.location = "'.admin_url().'"
		//-->
		</script>';
	}

	function wpsdb_settings_page() {
		//global $wpsdb_oauth;
		if((get_option('wpsdb_menu_pref')=='main' && !stristr($_SERVER['REQUEST_URI'],'admin.php?')) || (get_option('wpsdb_menu_pref')=='settings_menu' && !stristr($_SERVER['REQUEST_URI'],'options-general.php?')) || get_option('wpsdb_activation_redirect') == 'true' ):
			update_option('wpsdb_activation_redirect','false');
			wpsdb_admin_redirect();
		endif;

		if(isset($_POST['wpsdb_cancel_button'])){

		update_option('wpsdb_auth_step',1);

		update_option( 'wpsdb_auth_token', "");

		update_option( 'wpsdb_auth_token_secret', "");			

		}

	?>
<script type="text/javascript">

          function displaymessage()

          {

          var retVal = confirm("Are you sure?");

		   if( retVal == true ){

			 tb_remove();

			 document.body.innerHTML += '<form id="dynForm" action="<?php
			 if(get_option('wpsdb_menu_pref')!='main'):
			 	echo get_option('siteurl')."/wp-admin/options-general.php?page=simple-dropbox-upload-form/wp-dropbox.php";
			 else:
				echo get_option('siteurl')."/wp-admin/admin.php?page=simple-dropbox-upload-form/wp-dropbox.php";
			 endif;
			 ?>" method="post"><input type="hidden" name="wpsdb_cancel_button" value="true"></form>';

			document.getElementById("dynForm").submit();          

			  return true;

		   }else{

			  return false;

		   }

          }

</script>
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=301652803267448";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
</script>

<?php
if(isset($_POST[ 'wpsdb_submit_hidden' ])):
	if( $_POST[ 'wpsdb_submit_hidden' ] == 'Y' ) {
		
		if(wp_verify_nonce($_POST['wpsdb_set_nonce'], plugin_basename(__FILE__))){
		
			// Save the posted value in the database
					
			update_option( 'wpsdb_path', trim($_POST[ 'wpsdb_path' ],'/').'/' );
			
			update_option( 'wpsdb_temp_path', $_POST[ 'wpsdb_temp_path' ] );
			
			update_option( 'wpsdb_allow_ext', $_POST[ 'wpsdb_allow_ext' ] );
			
			update_option( 'wpsdb_thank_message', $_POST[ 'wpsdb_thank_message' ] );
			
			update_option( 'wpsdb_redirect_page', $_POST[ 'wpsdb_redirect_page' ] );
			
			update_option( 'wpsdb_show_progress', $_POST[ 'wpsdb_show_progress' ] );
			
			update_option( 'wpsdb_show_multi', $_POST[ 'wpsdb_show_multi' ] );
			
			update_option( 'wpsdb_multi_link_text', ( $_POST['wpsdb_multi_link_text'] == '' ) ? 'Multiple Files? Click Here!' : $_POST['wpsdb_multi_link_text'] );
			
			update_option('wpsdb_show_multi_size', $_POST['wpsdb_show_multi_size'] );
			
			update_option( 'wpsdb_show_form', $_POST[ 'wpsdb_show_form' ] );
			
			update_option( 'wpsdb_delete_file', $_POST[ 'wpsdb_delete_file' ] );
			
			update_option( 'wpsdb_thank_color', $_POST[ 'wpsdb_thank_color' ] );
			
			update_option( 'wpsdb_php_pear', $_POST[ 'wpsdb_php_pear' ] );
			
			if(!empty($_POST['wpsdb_allow_ext']) && get_option('wpsdb_auth_step') == 0){
				update_option('wpsdb_auth_step',1);
			}

			if(isset($_POST['wpsdb_menu_pref'])):
				if($_POST['wpsdb_menu_pref']=='settings_menu' and get_option('wpsdb_menu_pref')!='settings_menu'){
					update_option('wpsdb_menu_pref','settings_menu');
					wpsdb_admin_redirect();
				}
			endif;
			if(!isset($_POST['wpsdb_menu_pref'])):
				if(/*$_POST['wpsdb_menu_pref']!='settings_menu'*/ empty($_POST['wpsdb_menu_pref']) and get_option('wpsdb_menu_pref')!='main'){
					update_option('wpsdb_menu_pref','main');
					wpsdb_admin_redirect();
				}
			endif;
			if (isset($_POST['wpsdb_reset_confirm']) and isset($_POST['wpsdbreset']) and trim($_POST['wpsdb_reset_confirm']) == trim($_POST['wpsdbreset'])){
					
				wp_dropbox_deactivate('reset');
				
				register_wp_dropbox_settings();
				
				$wpsreset = true;
				?>
				<div class="updated">
					<p><strong>All options have been reset!</strong></p>
				</div>
				<?php
				wpsdb_admin_redirect();
			}
		}else{
			wpsdb_admin_redirect();	
		}
	}
endif;
?>
<?php
// Put an options updated message on the screen
if(isset($_POST[ 'wpsdb_submit_hidden' ])):
	//if ($_POST[ "wpsdb_submit_hidden" ] == 'Y' && !$wpsdb_error && !$wpsreset) {
	if ($_POST[ "wpsdb_submit_hidden" ] == 'Y' && empty($wpsdb_error) && empty($wpsreset)) {
	?>
		 <div class="updated fade">
			  <p>
			  <strong><?php _e('Options saved.', 'simpleDbUpload' ); ?></strong>
			  </p>
		 </div>
	<?php
	}
endif;
?>
<div class="wrap">
     <div id="wpsdb-header">
          <div id="wpsdb-title">
               <h2><?php _e('Simple Dropbox Upload','simpleDbUpload');?><p id="wpsdb-sub-title"><?php _e('This plugin will create a folder in your Dropbox account and allow public uploads.','simpleDbUpload');?></p></h2>
          </div>
          <!--div id="wpsdb-sub-title">
               <p><?php _e('This plugin will create a folder in your Dropbox account and allow public uploads.','simpleDbUpload');?></p>
          </div-->
     </div>
<?php /*
// Put an options updated message on the screen
if(isset($_POST[ 'wpsdb_submit_hidden' ])):
	//if ($_POST[ "wpsdb_submit_hidden" ] == 'Y' && !$wpsdb_error && !$wpsreset) {
	if ($_POST[ "wpsdb_submit_hidden" ] == 'Y' && empty($wpsdb_error) && empty($wpsreset)) {
	?>
		 <div class="updated fade">
			  <p>
			  <strong><?php _e('Options saved.', 'simpleDbUpload' ); ?></strong>
			  </p>
		 </div>
	<?php
	}
endif;*/
?>
  <div id="poststuff" class="metabox-holder has-right-sidebar" style="margin-top:85px">
     <!-- BEGIN SIDEBAR -->
     <div id="side-info-column" class="inner-sidebar">
     <?php 
	if(get_option('wpsdb_auth_step')==3):
	//if(false):
	$consumerKey = get_option( 'wpsdb_key' );

	$consumerSecret = get_option( 'wpsdb_secret' );

	include 'inc/Dropbox/autoload.php';
	
	$oauth = new Dropbox_OAuth_Wordpress($consumerKey, $consumerSecret);

	$wpsdb_token = get_option( 'wpsdb_auth_token' );

	$wpsdb_token_secret = get_option( 'wpsdb_auth_token_secret' );
	
	$oauth->setToken($wpsdb_token,$wpsdb_token_secret);
				
	$wpsdropbox = new Dropbox_API($oauth);
	$wpsdropbox_ainfo = $wpsdropbox->getAccountInfo();
	//print_r($wpsdropbox_ainfo);
	?>
     <div class="meta-box-sortables">
          <div id="about" class="postbox">
               <h3 class="hndle" id="about-sidebar">Account Information</h3>
               <div class="inside">
                    <p>
                    <li><strong>User:</strong> <?php echo $wpsdropbox_ainfo['email'];?></li>
                    <li><?php 
				$wpsdbfreespase = $wpsdropbox_ainfo['quota_info']['quota'] - ($wpsdropbox_ainfo['quota_info']['normal']-$wpsdropbox_ainfo['quota_info']['shared']);
				$wpsdbusedspase = $wpsdropbox_ainfo['quota_info']['normal'] + $wpsdropbox_ainfo['quota_info']['shared'];
				echo '<strong>';
				_e('Space: ','simpleDbUpload');
				echo '</strong>';
				echo '<strong>'.formatBytes((float)$wpsdbusedspase).'</strong> ';
				_e('used','simpleDbUpload');
				echo ' | ';
				echo '<strong>'.formatBytes((float)$wpsdbfreespase).'</strong> ';
				_e('free','simpleDbUpload');
				?></li>
                    <li><a href="https://www.dropbox.com/home/<?php echo get_option( 'wpsdb_path' ); ?>" target="_blank">Dropbox</a></li>
                    </p>
               </div>
          </div>
     </div>
     <?php endif;?>
     <div class="meta-box-sortables">
          <div id="about" class="postbox">
               <h3 class="hndle" id="about-sidebar">Get In Touch!</h3>
               <div class="inside">
                    <p>
                    <a href="http://www.facebook.com/creativedesignsolutionsinc" target="_blank"><img src="<?php echo plugins_url( '/images/facebook.png', __FILE__ ); ?> "></a>&nbsp;
                    <a href="http://www.twitter.com/intent/user?screen_name=cdsincdesign" target="_blank"><img src="<?php echo plugins_url( '/images/twitter.png', __FILE__ ); ?>"></a>&nbsp;
                    <a href="http://www.pinterest.com/hiphopsmurf" target="_blank"><img src="<?php echo plugins_url( '/images/pinterest.png', __FILE__ );?>"></a>&nbsp;
                    <!--a href="http://www.cdsincdesign.com/blog" target="_blank"><img src="<?php echo plugins_url( '/images/wordpress.png', __FILE__ );?>"></a>&nbsp;
                    <a href="http://www.cdsincdesign.com/feed" target="_blank"><img src="<?php echo plugins_url( '/images/rss.png', __FILE__ );?>"></a>&nbsp;
                    <a href="http://www.cdsincdesign.com/contact" target="_blank"><img src="<?php echo plugins_url( '/images/email.png', __FILE__ );?>"></a-->
                    </p>
               </div>
          </div>
     </div>
     
     <div class="meta-box-sortables">
          <div id="about" class="postbox">
               <h3 class="hndle" id="about-sidebar">Keep This Plugin Free!</h3>
               <div class="inside">
                    <p>
                    
                    <form method="POST" enctype="multipart/form-data" action="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=N56JT9B8VVAJN&currency_code=USD&item_name=Plugin Donation - Thank You!" target="_blank">
                    <table border="0" cellpadding="5">
                    <tr>
                    <td width="20%" align="center"><input type="radio" name="amount" value="5" checked><br>$5</td>
                    <td width="20%" align="center"><input type="radio" name="amount" value="10"><br>$10</td>
                    <td width="20%" align="center"><input type="radio" name="amount" value="25"><br>$25</td>
                    <td width="20%" align="center"><input type="radio" name="amount" value="50"><br>$50</td>
                    <td width="20%" align="center"><input type="radio" name="amount" value=""><br>Other<br></td>
                    </tr></table>
                    <input type="image" name="side-donate" value="Donate With PayPal!" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" />
                    </form>
                    
                    </p>
					<?php /*<hr />
					<div style="font-size:16px;margin:0 auto;width:300px" class="blockchain-btn"
					 data-address="13nmr9BCppELeLHwT3EnK4kwsHFYm8kJM2"
					 data-shared="false"
					 data-callback="<?php echo admin_url();?>">
						<div class="blockchain stage-begin">
							<img src="https://blockchain.info//Resources/buttons/donate_64.png"/>
						</div>
						<div class="blockchain stage-loading" style="text-align:center">
							<img src="https://blockchain.info//Resources/loading-large.gif"/>
						</div>
						<div class="blockchain stage-ready">
							 <p align="center">Please Donate To Bitcoin Address: <b>[[address]]</b></p>
							 <p align="center" class="qr-code"></p>
						</div>
						<div class="blockchain stage-paid">
							 Donation of <b>[[value]] BTC</b> Received. Thank You.
						</div>
						<div class="blockchain stage-error">
							<font color="red">[[error]]</font>
						</div>
					</div>*/ ?>
               </div>
          </div>
     </div>
     
     <div class="meta-box-sortables">
          <div id="about" class="postbox">
               <h3 class="hndle" id="about-sidebar">Tell The World!</h3>
               <div class="inside">
                    <p><b>Like This Plugin? Please Share!</b></p>
                    <p><table border="0" cellpadding="0" cellspacing="0">
                    <tr>
                    <td style="padding-top:4px;">
                    <div class="fb-like" data-href="http://cdsincdesign.com/about/plugins/simple-dropbox-upload-form/" data-send="false" data-layout="button_count" data-show-faces="false"></div>
                    </td>
                    <td style="padding-top:4px;">
                    <a href="https://twitter.com/share" class="twitter-share-button" data-url="http://www.cdsincdesign.com/about/plugins/simple-dropbox-upload-form" data-text="Simple Dropbox Uploader for WordPress:" data-via="hipHOPsMuRf">Tweet</a>
                    <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
                    </td>
                    <td id="gplus" style="padding-top:4px;">
                    <!-- Place this tag where you want the +1 button to render. -->
                    <div class="g-plusone" data-size="medium" data-href="http://cdsincdesign.com/about/plugins/simple-dropbox-upload-form/"></div>
                    
                    <!-- Place this tag after the last +1 button tag. -->
                    <script type="text/javascript">
                      (function() {
                        var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
                        po.src = 'https://apis.google.com/js/plusone.js';
                        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
                      })();
                    </script>
                    </td>
                    <td id="pinterest" style="padding-top:4px;">
                    <a href="http://pinterest.com/pin/create/button/?url=http://www.cdsincdesign.com/about/plugins/simple-dropbox-upload-form&media=http://www.cdsincdesign.com/socialimg/simple-dropbox.png&description=WordPress%20plugin%20to%20allow%20users%20to%20upload%20files%20to%20specific%20folders%20on%20your%20Dropbox%20account%20using%20a%20simple%20shortcode%21" class="pin-it-button" count-layout="none"><img border="0" src="//assets.pinterest.com/images/PinExt.png" title="Pin It" /></a>
                    <script type="text/javascript" src="//assets.pinterest.com/js/pinit.js"></script>
                    </td>
                    </tr>
                    </table></p>
               </div>
          </div>
     </div>
     <?php /*
	function renderWpsdbMetabox() {
		if (!function_exists('plugins_api')) {
			require_once(ABSPATH . '/wp-admin/includes/plugin-install.php');
		}
		
		$api = plugins_api('plugin_information', array('slug' => 'wp-bug-tracker'));
		$status = install_plugin_install_status($api);
		
		if ($status['status'] == 'install') {
			$render = $status['url'];
		} else {
			$render = '';
		}
		return $render;
	}
	$render = renderWpsdbMetabox();
	if($render != ''):
	?>
     <div class="meta-box-sortables">
          <div id="about" class="postbox">
               <h3 class="hndle" id="about-sidebar">Bug Tracker</h3>
               <div class="inside">
                    <div style="margin: 5px; float: left; width: 30px; padding: 10px 0 0 5px;">
                    	<img title="logo" rel="logo" src="<?php echo plugins_url('/images/ahm.png',__FILE__);?>" />
                    </div>
               
                    <div style="margin: 5px; float: left; width: 180px; text-align: justify;">
                    <a href="http://wordpress.org/extend/plugins/wp-bug-tracker/" target="_blank">Wordpress Bug Tracker</a><br /> will help us improve Simple Dropbox and you to maintain your system.
                    </div>
                    <div style="text-align: center">
                    <a href="<?php echo $render;?>" class="button button-primary button-large">Install Plugin</a>
                    </div>
                    <br class="clear" />
               </div>
          </div>
     </div>
     <?php endif;*/ ?>
     </div><!-- END SIDE-INFO-COLUMN -->
     <!-- END SIDEBAR -->
     
     <!-- BEGIN CONTENT AREA -->
     <div id="post-body" class="has-sidebar">
     <div id="post-body-content" class="has-sidebar-content">
     <div id="normal-sortables" class="meta-box-sortables">
     <div id="about" class="postbox">
     <div class="inside">
     <br class="clear" />
     <!--center-->
  <?php if(get_option('wpsdb_auth_step')!=3):?>
  <p style="text-align:center"><a href="https://www.dropbox.com/referrals/NTIyNjI4MTM5OQ" target="_blank"><?php _e('Need a Dropbox account? Please use this link so we both get some extra space.','simpleDbUpload');?></a></p><?php endif; ?>
  <?php $wpssure = rand(10000,99999);?>
  <form name="wpsdb_form" method="POST" action="">
  <input type="hidden" name="wpsdb_set_nonce" id="wpsdb_set_nonce" value="<?php echo wp_create_nonce (plugin_basename(__FILE__));?>">
    <table class="form-table">
    	 <tr>
      	<th scope="row"><p><?php _e('Keep Simple Dropbox in settings menu.','simpleDbUpload');?></p></th>
          <td>
               <input type="checkbox" name="wpsdb_menu_pref" value="settings_menu" <?php if(get_option('wpsdb_menu_pref')=='settings_menu')echo 'checked';?> />
          </td>
      </tr>
      <tr>
        <th scope="row"><p><?php _e('Path in dropbox folder.','simpleDbUpload');?></p></th>
        <td><input type="text" size="60" name="wpsdb_path" value="<?php echo get_option( 'wpsdb_path' ); ?>" />
          <br />
          <label class="wpsdb-label" for="inputid"><?php _e('All files/folders will be located in the base of your dropbox.','simpleDbUpload');?></label></td>
      </tr>
      <tr>
        <th scope="row"><p><?php _e('Temporary path on server. Files get saved here if Dropbox server is down.','simpleDbUpload');?></p></th>
        <td><input type="text" size="60" name="wpsdb_temp_path" value="<?php echo get_option( 'wpsdb_temp_path' ); ?>" />
          <br />
          <label class="wpsdb-label" for="inputid"><strong><?php _e('Default Location:','simpleDbUpload');?></strong> <?php echo ABSPATH.'wp-content/uploads/wpdb'; ?></label></td>
      </tr>
      <tr>
        <th scope="row"><p><?php _e('Allowed file extensions, separated by spaces.','simpleDbUpload');?> <strong>(<?php _e('Required','simpleDbUpload');?>)</strong></p></th>
        <td><input type="text" size="60" name="wpsdb_allow_ext" value="<?php echo get_option( 'wpsdb_allow_ext' ); ?>" />
        <br />
          <label class="wpsdb-label" for="inputid"><strong><?php _e('Example:','simpleDbUpload');?></strong> doc docx gif jpg jpeg pdf png psd tif tiff</label></td>
      </tr>
      <tr id="wpsdb-thank-message">
        <th scope="row"><p><?php _e('Message displayed after uploading a file.','simpleDbUpload');?> <a href="javascript:void(0)" class="help"><img border="0" src="<?php echo plugins_url( '/images/help.png', __FILE__ );?>"><span><strong>Single Upload:</strong> This will not be shown if progress indicators are enabled.<br /><strong>Multiple Upload:</strong> This will not be shown if a redirect page is set.</span></a></p></th>
        <td><input type="text" size="60" name="wpsdb_thank_message" value="<?php echo stripslashes(get_option( 'wpsdb_thank_message' )); ?>" /></td>
      </tr>
     <tr id="wpsdb-thank-message-color">
     <th scope="row"><p><?php _e('Text color of thank you message.','simpleDbUpload');?></p></th>
          <td>
               <input type="text" name="wpsdb_thank_color" id="wpsdb-thank-color" value="<?php echo esc_attr( get_option( 'wpsdb_thank_color' ) ); ?>" />
               
               <a href="#" class="pickcolor hide-if-no-js" id="wpsdb-thank-color-example"></a>
               
               <input type="button" class="pickcolor button hide-if-no-js" value="<?php esc_attr_e( 'Select a Color', 'twentyeleven' ); ?>" />
               
               <div id="colorPickerDiv" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
               <br />
               <span><?php printf( __( 'Default color: %s', 'twentyeleven' ), '<span id="default-color">#000000</span>' ); ?></span>
     
          </td>
     </tr>
     <tr>
        <th scope="row"><p><?php _e('Show upload progress indicators?(Experimental)','simpleDbUpload');?> <a href="javascript:void(0)" class="help"><img border="0" src="<?php echo plugins_url( '/images/help.png', __FILE__ );?>"><span>This option will show a progress bar below the form to indicate how much of the file has been uploaded. It will also show a loading icon to help deter users from leaving the page before the file is uploaded.</span></a></p></th>
          <td><select name="wpsdb_show_progress" id="wpsdb_show_progress">
               <option value="True" <?php if(get_option('wpsdb_show_progress')== "True"){?>selected="selected"<?php }?>><?php _e('True','simpleDbUpload');?></option>
               <option value="False" <?php if(get_option('wpsdb_show_progress')== "False"){?>selected="selected"<?php }?>><?php _e('False','simpleDbUpload');?></option>
               </select>               
          </td>
     </tr>
     <tr id="redirect">
          <th scope="row"><p><?php _e('Page directed to after uploading a file.','simpleDbUpload');?> <a href="javascript:void(0)" class="help"><img border="0" src="<?php echo plugins_url( '/images/help.png', __FILE__ );?>"><span>Leave blank to have user directed back to upload form.</span></a></p></th>
          <td>
          <?php 
          $wpsdb_page_option = get_option('wpsdb_redirect_page');
          
          echo '<select name="wpsdb_redirect_page" style="width:150px"><option value="">';
          
          $wpsdb_getpages = get_pages();
          
          foreach ($wpsdb_getpages as $wpsdb_getpage) {
               
               if ($wpsdb_page_option == $wpsdb_getpage->ID) {		
                    $selected_redirect_page = 'selected="selected"';
               } else {
                    $selected_redirect_page = '';
               }
               
               $option_redirect = '<option value="' . $wpsdb_getpage->ID . '" ' . $selected_redirect_page . '>';
               
               $option_redirect .= $wpsdb_getpage->post_title;
               
               $option_redirect .= '</option>';
               
               echo $option_redirect;
          
          }
          ?>
          </td>
     </tr>
     <tr>
        <th scope="row"><p><?php _e('Show multiple upload link?<br />(Experimental)','simpleDbUpload');?> <a href="javascript:void(0)" class="help"><img border="0" src="<?php echo plugins_url( '/images/help.png', __FILE__ );?>"><span>Allows users to drag n drop multiple files.</span></a></p></th>
          <td><select name="wpsdb_show_multi" id="wpsdb_show_multi">
               <option value="True" <?php if(get_option('wpsdb_show_multi')== "True"){?>selected="selected"<?php }?>><?php _e('True','simpleDbUpload');?></option>
               <option value="False" <?php if(get_option('wpsdb_show_multi')== "False"){?>selected="selected"<?php }?>><?php _e('False','simpleDbUpload');?></option>
               </select>               
          </td>
      </tr>
      <tr id="multi_link_text">
        <th scope="row"><p><?php _e('Multiple upload link text.','simpleDbUpload');?></p></th>
        <td><input type="text" size="60" name="wpsdb_multi_link_text" value="<?php echo stripslashes(get_option( 'wpsdb_multi_link_text' )); ?>" /></td>
      </tr>
      <tr id="multi_size">
      <th scope="row"><p><?php _e('Per file size limit. (Multiple Upload Only)','simpleDbUpload');?></p></th>
          <td><select name="wpsdb_show_multi_size" id="wpsdb_show_multi_size">
               <option value="1" <?php if(get_option('wpsdb_show_multi_size')== "1"){?>selected="selected"<?php }?>><?php _e('1MB','simpleDbUpload');?></option>
               <option value="5" <?php if(get_option('wpsdb_show_multi_size')== "5"){?>selected="selected"<?php }?>><?php _e('5MB','simpleDbUpload');?></option>
               <option value="10" <?php if(get_option('wpsdb_show_multi_size')== "10"){?>selected="selected"<?php }?>><?php _e('10MB','simpleDbUpload');?></option>
               <option value="15" <?php if(get_option('wpsdb_show_multi_size')== "15"){?>selected="selected"<?php }?>><?php _e('15MB','simpleDbUpload');?></option>
               <option value="20" <?php if(get_option('wpsdb_show_multi_size')== "20"){?>selected="selected"<?php }?>><?php _e('20MB','simpleDbUpload');?></option>
               </select>
          </td>
      </tr>
      <tr id="showafter">
        <th scope="row"><p><?php _e('Show upload form again after upload?','simpleDbUpload');?> <a href="javascript:void(0)" class="help"><img border="0" src="<?php echo plugins_url( '/images/help.png', __FILE__ );?>"><span><strong>Single Upload:</strong> Doesn't apply to single uploads if progress indicators are enabled.<br /><strong>This feature will be removed in the future.</strong></span></a></p></th>
          <td><select name="wpsdb_show_form">
               <option value="True" <?php if(get_option('wpsdb_show_form')== "True"){?>selected="selected"<?php }?>><?php _e('True','simpleDbUpload');?></option>
               <option value="False" <?php if(get_option('wpsdb_show_form')== "False"){?>selected="selected"<?php }?>><?php _e('False','simpleDbUpload');?></option>
               </select>
          </td>
      </tr>
      <tr>
        <th scope="row"><p><?php _e('Delete local file after upload to dropbox?','simpleDbUpload');?></p></th>
          <td><select name="wpsdb_delete_file">
               <option value="True" <?php if(get_option('wpsdb_delete_file')== "True"){?>selected="selected"<?php }?>><?php _e('True','simpleDbUpload');?></option>
               <option value="False" <?php if(get_option('wpsdb_delete_file')== "False"){?>selected="selected"<?php }?>><?php _e('False','simpleDbUpload');?></option>
               </select>
          </td>
      </tr>
      <tr>
          <th scope="row"><p><?php _e('Use CURL, PHP oAuth or PEAR oAuth?','simpleDbUpload');?></p></th>
          <td><select name="wpsdb_php_pear">
            <option value="curl" <?php if(get_option('wpsdb_php_pear')== "curl"){?>selected="selected"<?php }?>>Curl</option>
            <option value="php" <?php if(get_option('wpsdb_php_pear')== "php"){?>selected="selected"<?php }?>>PHP oAuth</option>
            <option value="pear" <?php if(get_option('wpsdb_php_pear')== "pear"){?>selected="selected"<?php }?>>PEAR oAuth</option>
          </select>
          </td>
     </tr>
      <tr>
        <th scope="row"><p><?php _e('RESET SETTINGS.','simpleDbUpload');?></p></th>
        <td><input type="text" size="60" name="wpsdbreset" autocomplete="off" value="" />
          <br />
          <label class="wpsdb-label" for="inputid"><?php _e('PLEASE TYPE THE FOLLOWING NUMBERS: ','simpleDbUpload'); echo $wpssure;?></label></td>
      </tr>
      <tr>
        <th scope="row" style="width:300px;" class="last_item">
        	<input type="hidden" name="wpsdb_submit_hidden" value="Y" />
          <input type="hidden" name="wpsdb_reset_confirm" value="<?php echo $wpssure;?>" />
          <input type="submit" class="button-primary" style="line-height:15px;" value="<?php _e('Save options','simpleDbUpload'); ?>" /><?php
               $buttonShow = get_option('wpsdb_auth_step');
			if($buttonShow == "0" || $buttonShow == "1"){
		?>
		<a class="thickbox button-secondary" <?php if(get_option('wpsdb_auth_step')==0)echo 'disabled="disabled"'?> href="<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php?action=choice&width=450&height=350" title="Authorize"><?php _e('Authorize','simpleDbUpload');?></a>
		<?php
          }
		if($buttonShow == "2"){
		?>
		<a class="thickbox button-secondary" href="<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php?action=choice&width=450&height=350" title="Confirm"><?php _e('Confirm','simpleDbUpload');?></a>
		<?php
          }
		?>
		<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=K6XUBZSU8RWR2" target="_blank"><img style="margin-bottom:-7px;height:23px;" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" alt="" /></a>
		</th>
        <td class="last_item"></td>
      </tr>
    </table>
  </form>
  <script>
  jQuery(document).ready( function($) {
	var h = jQuery.noConflict();
		
	h("#wpsdb_show_progress").change(function(){
		progress_switch();
	});
	
	progress_switch = function(){
		if(h("#wpsdb_show_progress").val() == "True"){
			//h("#wpsdb-thank-message,#wpsdb-thank-message-color,#showafter").hide();
			h("#redirect").show();
		}else{
			//h("#wpsdb-thank-message,#wpsdb-thank-message-color,#showafter").show();
			h("#redirect").hide();
		}
	}
	progress_switch();
	
	h("#wpsdb_show_multi").change(function(){
		multiple_switch();
	});
	
	multiple_switch = function(){
		if(h("#wpsdb_show_multi").val() == "True"){
			h("#multi_size,#multi_link_text").show();
		}else{
			h("#multi_size,#multi_link_text").hide();
		}
	}
	multiple_switch();
	
  })
  </script>
  <!--/center-->
  <br class="clear" />
  </div><!-- END INSIDE -->
  </div><!-- END ABOUT -->
  </div><!-- END NORMAL-SORTABLES -->
  </div><!-- END POST-BODY-CONTENT-->
  </div><!-- END POST-BODY -->
  </div><!-- END POSTSTUFF -->
</div><!-- END WRAP -->
<?php

$check_settings = new wpsdbFunction;

//$check_settings->removeSettingsGroup();
/*$whattokeep = array('wpsdb_version_number','wpsdb_menu_pref','wpsdb_delete_file',NULL);
$check_settings->updateSettingsGroup($whattokeep);*/
}

	// Version Check
	function wpdb_get_version() {
		if ( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
		$plugin_file = basename( ( __FILE__ ) );
		return $plugin_folder[$plugin_file]['Version'];
	}

	// shortcode

	function shortcode_wp_dropbox( $atts, $content = NULL ) {
		
		/*extract( shortcode_atts( array(
		'link' => ''
		), $atts ) );*/

		// Hackis way to show my shortcode at the right place

		ob_start();

		show_dropbox($atts, $content);

		$output_string=ob_get_contents();

		ob_end_clean();

		return $output_string;

	}
	
	function wpsdb_create_menu() {
		if(get_option('wpsdb_menu_pref')=='main'):
			
			//create new top-level menu
			if(stristr(get_bloginfo( 'version' ),'3.6') !== false):
				$wpsdbAdminMenu = add_menu_page('Simple Dropbox', '<small>Simple Dropbox</small>', 'edit_pages', __FILE__, 'wpsdb_settings_page',plugins_url('/images/simple-dropbox-icon.png', __FILE__),66);
			else:
				$wpsdbAdminMenu = add_menu_page('Simple Dropbox', 'Simple Dropbox', 'edit_pages', __FILE__, 'wpsdb_settings_page',plugins_url('/images/simple-dropbox-icon.png', __FILE__),66);
			endif;
		else:
			//create options page
			$wpsdbAdminMenu = add_options_page('Simple Dropbox', '<img src="' . plugins_url('/images/simple-dropbox-icon.png', __FILE__) .'" />&nbsp;Simple Dropbox', 'edit_pages', __FILE__, 'wpsdb_settings_page');
		endif;
		//call register settings function
		add_action( 'admin_init', 'register_wp_dropbox_settings' );
		add_action('admin_print_styles-' . $wpsdbAdminMenu, 'wpsdb_add_style');
		add_action('admin_print_scripts-' . $wpsdbAdminMenu, 'wpsdb_add_script');
		//add_action('admin_print_scripts-' . $wpsdbAdminMenu, 'wpsdb_other_scripts');
	}
	
	function wpsdb_add_style() {
	    wp_enqueue_style( 'wpsdb-settings-page', plugins_url( '/css/wpsdb-style-admin.css', __FILE__ ) );
	    wp_enqueue_style( 'farbtastic' );
	}
	
	function wpsdb_add_script(){		
		wp_enqueue_script( 'wpsdb-settings-page', plugins_url( '/js/color-options.js', __FILE__ ), array( 'farbtastic' ) );
		
		//wp_enqueue_script( 'farbtastic' );
		//wp_enqueue_script( 'jquery' );
	}
	
	function wpsdb_other_scripts(){
		wp_register_script( 'wpsdb-bci-js', '//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js' );
		wp_enqueue_script( 'wpsdb-bci-js' );
		//wp_register_script('wpsdb-bci', 'https://blockchain.info//Resources/wallet/pay-now-button.js', array( 'wpsdb-bci-js' ),null,false);
		wp_register_script('wpsdb-bci', plugins_url( '/js/pay-now-button.js',__FILE__), array( 'wpsdb-bci-js' ),null,false);
		wp_enqueue_script( 'wpsdb-bci' );
	}
	
	function wp_dropbox_deactivate($whichSet = "all",$wpsdb_ver = NULL){
		if(!$whichSet)$whichSet = "all";
		$cleanUp = new wpsdbFunction;

		remove_shortcode( 'simple-wp-dropbox' );

		if($whichSet == "old"){
			$oldStuff = array(
				'db_username',
				'db_path',
				'db_temp_path',
				'db_allow_ext',
				'db_thank_message',
				'db_show_form',
				'db_delete_file',
				'db_php_pear',
				'db_key',
				'db_secret',
				'db_auth_token',
				'db_auth_token_secret',
				'db_auth_step',
				'db_menu_pref'
			);
			
			$cleanUp->removeOldSettingsGroup($oldStuff);
			
			if(get_option('wpdb_version_number') || get_option('wpdb_version_number')==''){
				delete_option( 'wpdb_version_number' );
			}
			
			if(!get_option('wpsdb_php_pear') || get_option('wpsdb_php_pear')=='wordpress'){
				update_option('wpsdb_php_pear',"curl");
			}
		}

		if($whichSet == "all"){
			$cleanUp->removeSettingsGroup();
		}
		
		if($whichSet == "reset"){
			$whattokeep = array('wpsdb_version_number','wpsdb_key','wpsdb_secret','wpsdb_activation_redirect','wpsdb_php_pear','wpsdb_menu_pref');
			$cleanUp->updateSettingsGroup($whattokeep);
		}
		
	}

	function register_wp_dropbox_settings() {
		
		$registerThem = new wpsdbFunction;
		
		$whatToRegister = array('wpsdb_path','wpsdb_temp_path','wpsdb_allow_ext','wpsdb_thank_message','wpsdb_redirect_page','wpsdb_show_progress','wpsdb_show_multi','wpsdb_multi_link_text','wpsdb_show_multi_size','wpsdb_show_form','wpsdb_delete_file','wpsdb_php_pear','wpsdb_key','wpsdb_secret','wpsdb_auth_token','wpsdb_auth_token_secret','wpsdb_auth_step','wpsdb_menu_pref','wpsdb_thank_color','wpsdb_activation_redirect','wpsdb_version_number');
		
		$registerThem->addSettingsGroup($whatToRegister);
		
		update_option('wpsdb_key', "".base64_decode("bWg4YmZzMGw1dThkcG5t")."");

		update_option( 'wpsdb_secret', "".base64_decode("Y25saTlrMW9leWl3bzZr")."" );
		
		//update_option( 'wpsdb_allow_ext' , 'doc docx gif jpg jpeg pdf png psd tif tiff');
		
		if(!get_option('wpsdb_show_progress')){
			update_option('wpsdb_show_progress',"False");
		}
		
		if(!get_option('wpsdb_show_multi')){
			update_option('wpsdb_show_multi',"False");
		}
		
		if(!get_option('wpsdb_multi_link_text') || get_option('wpsdb_multi_link_text')==''){
			update_option('wpsdb_multi_link_text','Multiple Files? Click Here!');
		}
		
		if(!get_option('wpsdb_show_multi_size')){
			update_option('wpsdb_show_multi_size','1');
		}
		
		if(!get_option('wpsdb_show_form')){
			update_option('wpsdb_show_form',"False");
		}
		
		if(!get_option('wpsdb_delete_file')){
			update_option('wpsdb_delete_file',"True");
		}
		
		if(!get_option('wpsdb_path')){
			update_option('wpsdb_path','SimpleDropBox');
		}

		if(!get_option('wpsdb_temp_path') || get_option('wpsdb_temp_path')==''){
			$upload_dir = wp_upload_dir();
			update_option( 'wpsdb_temp_path', $upload_dir['basedir'].'/wpdb' );
		}
		
		if(!get_option('wpsdb_menu_pref') || get_option('wpsdb_menu_pref')==''){
			update_option('wpsdb_menu_pref','main');
		}
		
		if(!get_option('wpsdb_php_pear') || get_option('wpsdb_php_pear')=='' || get_option('wpsdb_php_pear')=='wordpress'){
			update_option('wpsdb_php_pear',"curl");
		}
		
		if(!get_option('wpsdb_thank_color')){
			update_option('wpsdb_thank_color','#000000');
		}

		if(substr(get_option('wpsdb_version_number'),0,-2) != ('1.7'||'1.8')){
			wp_dropbox_deactivate("old");
			update_option('wpsdb_version_number',wpdb_get_version());
		}
		
		update_option('wpsdb_version_number',wpdb_get_version());
		add_option('wpsdb_activation_redirect', 'true');
	}	
	
	function wpsdb_plugin_redirect() {
	    if (get_option('wpsdb_activation_redirect') == 'true') {
		   update_option('wpsdb_activation_redirect', 'false');
		   wp_redirect(admin_url('admin.php?page=simple-dropbox-upload-form/wp-dropbox.php'));
	    }
	}

	function WP_DB_PluginInit(){
		load_plugin_textdomain('simpleDbUpload', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}
	
	function wpsdb_plugin_action_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		$settings_link = '<a href="#">TEST</a>';
		
		if ( $file == $base )
			array_unshift( $links, $settings_link );

		return $links;
	}
	
	//add_filter( 'plugin_action_links', 'wpsdb_plugin_action_links' , 10, 2 );
	
	function wpsdb_plugin_row_meta( $input, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file != $base )
			return $input;

		$links = array(
			'<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=K6XUBZSU8RWR2" target="_blank"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" alt="PayPal - The safer, easier way to pay online!" /></a>',
			//'<a href="http://cdsincdesign.com/downloads/simple-dropbox-premium-wordpress-plugin/">Purchase Simple Dropbox Premium</a>',
		);

		$input = array_merge( $input, $links );

		return $input;
	}
	
	add_filter( 'plugin_row_meta', 'wpsdb_plugin_row_meta' , 10, 2 );

	// Start this plugin once all other plugins are fully loaded

	//add_action( 'plugins_loaded', 'WPDropbox');

	add_shortcode( 'simple-wp-dropbox', 'shortcode_wp_dropbox' );

	add_action('admin_menu', 'wpsdb_create_menu');
	
	add_action('admin_init', 'wpsdb_plugin_redirect');

	add_action( 'plugins_loaded', 'WP_DB_PluginInit' );
	
	register_deactivation_hook( __FILE__, 'wp_dropbox_deactivate' );
	
	register_activation_hook( __FILE__, 'register_wp_dropbox_settings');

?>