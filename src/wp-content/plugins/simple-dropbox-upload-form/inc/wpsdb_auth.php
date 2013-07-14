<?php

$wpsdbAuth = new wpsdbAuth();

class wpsdbAuth {

	function wpsdbAuth() {

		//add_action('admin_menu', array(&$this, 'add_admin'));

		add_action("admin_print_scripts", array(&$this, 'js_libs'));

		add_action("admin_print_styles", array(&$this, 'style_libs'));

		add_action('wp_ajax_choice', array(&$this, 'choice'));

	}

	/*function add_admin() {

		add_theme_page('Black Or White?', 'Black Or White?', 'administrator', 'black-or-white', array(&$this, 'admin_view'));

	}*/

	function js_libs() {
		wp_enqueue_script('jquery');
		wp_enqueue_script('thickbox');
	}

	function style_libs() {
		//wp_enqueue_style( 'wpsdb-settings-page', plugins_url( '/css/wpsdb-style-admin.css', __FILE__ ) );
		wp_enqueue_style('thickbox');
	}

	/*function admin_view() {

		?>

		<div class="wrap">

			<h2>Black or White?</h2>

			<p>

				<a class="thickbox button" href="<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php?action=choice&width=650&height=600" title="Auth">Auth</a>

			</p>

			<p>

				Your choice: <span class="your-choice"></span>

			</p>

		</div>

		<?php

	}*/

	function choice() {

		/* Please supply your own consumer key and consumer secret */

		$consumerKey = get_option( 'wpsdb_key' );

		$consumerSecret = get_option( 'wpsdb_secret' );

		include 'Dropbox/autoload.php';

		$oauth = new Dropbox_OAuth_Wordpress($consumerKey, $consumerSecret);

		/****************TODO************* /

		if(get_option('wpsdb_php_pear')=="php"){

			include 'Dropbox/autoload.php';

			$oauth = new Dropbox_OAuth_PHP($consumerKey, $consumerSecret);

			if (!class_exists('OAuth')){

				// We're going to tell you

				echo "PHP OAuth was not found on this server";

				echo '<br /><button class="button" style="line-height:15px;margin-left:5px;" onClick="parent.tb_remove();">Close</button> ';

				exit();

			}

		}

		// If the PHP OAuth extension is not available, you can try

		// PEAR's HTTP_OAUTH instead.

		if(get_option('wpsdb_php_pear')=="pear"){

			include 'Dropbox/autoload.php';

			$oauth = new Dropbox_OAuth_PEAR($consumerKey, $consumerSecret);

			if (!class_exists('HTTP_OAuth_Consumer')) {

			  // We're going to tell you

			  print "HTTP OAuth is not installed on this server.";

			  print '<br /><button class="button" style="line-height:15px;margin-left:5px;" onClick="parent.tb_remove();">Close</button> ';

			  exit();

			}

		}

		/***********************************/

		$state = get_option( 'wpsdb_auth_step' );

		if($state == "1" || $state == ""){

			$state = 1;

		}

		switch($state) {

			default:

				echo "Please confirm the requred fields are populated.";

				echo '<br /><button class="button" style="line-height:15px;margin-left:5px;" onClick="parent.tb_remove();">Close</button> ';

				die();

		    case 1 :

			   echo "<strong>Step 1:</strong> Pre-authorization acquired!<br />";

			   $tokens = $oauth->getRequestToken();

			   update_option( 'wpsdb_auth_step', 2 );

			   update_option( 'wpsdb_auth_token', $tokens['token']);

			   update_option( 'wpsdb_auth_token_secret', $tokens['token_secret']);

			   //print_r($tokens);

			   // Note that if you want the user to automatically redirect back, you can

			   // add the 'callback' argument to getAuthorizeUrl.

			   echo "<strong>Step 2:</strong> After pressing continue you will be redirected to DropBox.<br />

			   After accepting the authorization you will be redirected back to the<br />WordPress Admin. Return

			   to the plugin settings page and press the Confirm button to finish the process.";

			   //echo '<a href="'.$oauth->getAuthorizeUrl().'" class="button" target="_blank">GO!</a>';

			   echo '<br /><br /><a href="'.$oauth->getAuthorizeUrl(get_option('siteurl').'/wp-admin/').'" class="button-primary">Continue</a>';

			   echo '<button class="button" style="line-height:15px;margin-left:5px;" onClick="parent.displaymessage();">Cancel</button> ';

			   die();

		    case 2 :

			   echo "<strong>Step 3:</strong> Confirming authorization... ";

			   $wpsdb_token = get_option( 'wpsdb_auth_token' );

			   $wpsdb_token_secret = get_option( 'wpsdb_auth_token_secret' );

			   $oauth->setToken($wpsdb_token,$wpsdb_token_secret);

			   try{
				   $tokens = $oauth->getAccessToken();
				   
			   }catch(Dropbox_Exception_RequestToken $r){
				   
				   echo "<br /><strong>It seems your authorization might be expired or invalid.";
				   echo "<br />This might be resolved by using the reset settings feature in the";
				   echo "<br />settings panel or alternately disabling the plugin and re-enabling it</strong>";
				   
				   print '<br /><br /><button class="button-primary" style="line-height:15px;margin-left:5px;" onClick="parent.tb_remove();">Close</button> ';
				   
				   break;
			   }
			   			   
			   //print_r($tokens);

			   update_option( 'wpsdb_auth_step', 3 );

			   update_option( 'wpsdb_auth_token', $tokens['token']);

			   update_option( 'wpsdb_auth_token_secret', $tokens['token_secret']);

			   echo "DONE |<br />";

		    case 3 :

			   //echo "The user is authenticated\n";

			   $wpsdb_token = get_option( 'wpsdb_auth_token' );

			   $wpsdb_token_secret = get_option( 'wpsdb_auth_token_secret' );

			   $oauth->setToken($wpsdb_token,$wpsdb_token_secret);
			   
			   echo "<strong>Account info:</strong> ";
			   
			   $wpsdropbox = new Dropbox_API($oauth);
			   $wpsdropbox_ainfo = $wpsdropbox->getAccountInfo();
			   echo $wpsdropbox_ainfo['email'];
			   if(get_option('wpsdb_menu_pref')!='main'):
			   	echo '<br /><br /><a href="'.get_option('siteurl').'/wp-admin/options-general.php?page=simple-dropbox-upload-form/wp-dropbox.php" class="button-primary">Finish!</a><br /><br />';
			   else:
			   	echo '<br /><br /><a href="'.get_option('siteurl').'/wp-admin/admin.php?page=simple-dropbox-upload-form/wp-dropbox.php" class="button-primary">Finish!</a><br /><br />';
			   endif;

			   break;

		}

		/*$wpsdropbox = new Dropbox_API($oauth);

		//echo "<strong>Account info:</strong> ";

		$wpsdropbox_ainfo = $wpsdropbox->getAccountInfo();

		echo $wpsdropbox_ainfo['email'];*/

		//echo '<br /><br /><a href="'.get_option('siteurl').'/wp-admin/options-general.php?page=simple-dropbox-upload-form/wp-dropbox.php" class="button">Finish!</a><br /><br />';

		//print_r($dropbox->getAccountInfo());

		exit();

	}

}

?>
