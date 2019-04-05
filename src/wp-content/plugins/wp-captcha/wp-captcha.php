<?php
/**
 * @package WP Captcha
 * @version 1.0.0
*/

/*
Plugin Name: WP Captcha
Plugin URI: https://github.com/devnathverma/wp-captcha/
Description: WP Captcha prove that the visitor is a human being and not a spam robot. WP Captcha asks the visitors to answer a math questions.
Author: Devnath verma
Author Email: devnathverma@gmail.com
Version: 1.0.0
Text Domain: wp-captcha
Domain Path: /languages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/*  © Copyright 2015 Devnath verma (devnathverma@gmail.com)

    This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if( !defined('ABSPATH') ) {
	
	die( 'You are not allowed to call this page directly.' );
}

if( !class_exists('WP_Captcha') ) {
	
	class WP_Captcha {
		
		/**
		* Construct the plugin object
		* @version 1.0.0
		* @package WP Captcha
		*/			 
		public function __construct() {
			
			register_activation_hook( __FILE__, array( $this, 'wpc_network_propagate' ) );
			add_action( 'init', array($this, '_wpc_init') );
			$this->_wpc_define_constants();
		    $this->_wpc_load_files();
		}
		
		/**
	    * Register activation for single and multisites
	    * @version 1.0.0
		* @package WP Captcha
	    */
		public function wpc_network_propagate($network_wide) {
			
			if ( is_multisite() && $network_wide ) { 
				
				global $wpdb;
				$currentblog = $wpdb->blogid;
				$activated = array();
		 
				$sql = "SELECT blog_id FROM {$wpdb->blogs}";
				$blog_ids = $wpdb->get_col($wpdb->prepare($sql,null));
				foreach ($blog_ids as $blog_id) {
					switch_to_blog($blog_id);
					$this->wpc_activate();
					$activated[] = $blog_id;
				}
	 
				switch_to_blog($currentblog);
				update_site_option('wpc_activated', $activated);
			} 
			else 
			{
				$this->wpc_activate();
			}
		}
		
		/**
	    * Create table used in plugin
	    * @version 1.0.0
		* @package WP Captcha
	    */
		public function wpc_activate() {
			
		}
		
		/**
	    * Define paths
	    * @version 1.0.0
		* @package WP Captcha
	    */
		public function _wpc_define_constants() {
			
			global $wpdb;

	    	if ( !defined( 'WPC_VERSION' ) )
				define('WPC_VERSION', '1.0.0');

			if ( !defined( 'WPC_FOLDER' ) )
				define('WPC_FOLDER', basename(dirname(__FILE__)));
			
			if ( !defined( 'WPC_DIR' ) )
				define('WPC_DIR', plugin_dir_path(__FILE__));
			
			if ( !defined( 'WPC_INC' ) )
				define('WPC_INC', WPC_DIR.'include'.'/');
				
			if ( !defined( 'WPC_CLASS' ) )
				define('WPC_CLASS', WPC_INC.'classes'.'/');
				
			if ( !defined( 'WPC_CLASS_WIDGET' ) )
				define('WPC_CLASS_WIDGET', WPC_INC.'class-widgets');
				
			if ( !defined( 'WPC_SHORTCODE' ) )
				define('WPC_SHORTCODE', WPC_INC.'shortcodes');		
				
			if ( !defined( 'WPC_FORMS' ) )
				define('WPC_FORMS', WPC_INC.'forms');
				
			if ( !defined( 'WPC_FUNCTION' ) )
				define('WPC_FUNCTION', WPC_INC.'function'.'/');
			
			if ( !defined( 'WPC_URL' ) )
				define('WPC_URL', plugin_dir_url(WPC_FOLDER).WPC_FOLDER.'/');
			
			if ( !defined( 'WPC_CSS' ) )
				define('WPC_CSS', WPC_URL.'assets/css'.'/');
			
			if ( !defined( 'WPC_JS' ) )
				define('WPC_JS', WPC_URL.'assets/js'.'/');
			
			if ( !defined( 'WPC_IMAGES' ) )
				define('WPC_IMAGES', WPC_URL.'assets/images'.'/');
			
			if ( !defined( 'WPC_FONTS' ) )
				define('WPC_FONTS', WPC_URL.'assets/fonts'.'/');
			
			if ( !defined( 'WPC_ICONS' ) )	
				define('WPC_ICONS', WPC_URL.'assets/icons'.'/');
		}
		
		/**
	    * Required files includes in plugin
	    * @version 1.0.0
		* @package WP Captcha
	    */
		public function _wpc_load_files() { 
			
	  	}
		
		/**
	    * Call wordpress actions
	    * @version 1.0.0
		* @package WP Captcha
	    */
		public function _wpc_init() { 
			
			add_action( 'admin_menu', array(&$this, 'wpc_admin_menu') );
			add_action( 'admin_enqueue_scripts', array(&$this, 'wpc_load_scripts_backend') );
			
			$options = get_option( '_wpc_captcha_settings' );
			$options = unserialize($options);
			
			$wpc_enable_login_form = ( $options['wpc_enable_login_form'] != "" ) ? sanitize_text_field( $options['wpc_enable_login_form'] ) : 'true';
			
			$wpc_enable_register_form = ( $options['wpc_enable_register_form'] != "" ) ? sanitize_text_field( $options['wpc_enable_register_form'] ) : 'true';
			
			$wpc_enable_forgot_form = ( $options['wpc_enable_forgot_form'] != "" ) ? sanitize_text_field( $options['wpc_enable_forgot_form'] ) : 'true';
			
			$wpc_enable_comment_form = ( $options['wpc_enable_comment_form'] != "" ) ? sanitize_text_field( $options['wpc_enable_comment_form'] ) : 'true';
			
			/**
			* Add captcha in Login Form
			* @version 1.0.0
			* @package WP Captcha
			*/
			if ( $wpc_enable_login_form == 'true' ) {
				
				add_action( 'login_form', array(&$this, 'wpc_captcha_login_form') );
				add_filter( 'authenticate', array(&$this, 'wpc_captcha_login_check'), 21, 1 );	
			}
			
			/**
			* Add captcha in Register Form
			* @version 1.0.0
			* @package WP Captcha
			*/
			if ( $wpc_enable_register_form == 'true' ) {
				
				add_action( 'register_form', array(&$this, 'wpc_captcha_register_form') );
				add_action( 'register_post', array(&$this, 'wpc_captcha_register_post'), 10, 3 );				
			}
			
			/**
			* Add captcha in Forgot Password Form
			* @version 1.0.0
			* @package WP Captcha
			*/
			if ( $wpc_enable_forgot_form == 'true' ) {
				
				add_action( 'lostpassword_form', array(&$this, 'wpc_captcha_register_form') );
				add_action( 'lostpassword_post', array(&$this, 'wpc_captcha_forgot_password'), 10, 3 );
			}
			
			/**
			* Add captcha in Comments Form
			* @version 1.0.0
			* @package WP Captcha
			*/
			if ( $wpc_enable_comment_form == 'true' ) {
				
				add_action( 'comment_form_after_fields', array(&$this, 'wpc_captcha_comment_form'), 1 );
				add_action( 'comment_form_logged_in_after', array(&$this, 'wpc_captcha_comment_form'), 1 );
				
				add_filter( 'preprocess_comment', array(&$this, 'wpc_captcha_comment_post') );
			}
		}
		
		/**
		* This function used to create menus on admin section.
		* @version 1.0.0
		* @package WP Captcha
		*/	
        public function wpc_admin_menu() {
			
			// Create Admin Menus
			add_menu_page(
				__('WP Captcha', "wp-captcha"), 
				__('WP Captcha', "wp-captcha"), 
				'manage_options', 
				'wp-captcha', 
				array(&$this, 'wpc_captcha')
			);
		}
		
		
		/**
	    * Create tabs menu used in plugin
	    * @version 1.0.0
		* @package WP Captcha
	    */
		public function wpc_captcha() {
        
			$menu_tabs = array(
                'settings' => __( 'WP Captcha', 'wp-captcha' )
            );
        ?>
            <h2>
                <span class="glyphicon glyphicon-asterisk"></span>
            </h2>

            <?php
			
            echo '<ul id="wpc-main-nav" class="nav-tab-wrapper">';
            
            if( !empty($_GET['tab']) ) {

                $current_tab = $_GET['tab']; 
            
            } else {

                $current_tab = 'settings'; 
            }

            foreach($menu_tabs as $tab_key => $tab_title ) {
              
                $active_tab = '';
              
                if( $current_tab == $tab_key ) 
                {                           
                    $active_tab = 'nav-tab-active';
                }
              
                echo '<li>';
                echo '<a class="nav-tab ' . $active_tab . '" href="'.admin_url('admin.php?page=wp-captcha&tab='.$tab_key).'">'. $tab_title .'</a>';
                echo '</li>';
            }

            echo '</ul>';
            
            if( !empty($current_tab) ) {

                switch( $current_tab ) {
                  
                    case 'settings' : $this->wpc_tab_settings(); break;

                    default : $this->wpc_tab_settings();            
                }

            } else {

                $this->wpc_tab_settings();
            }
        }
		
		/**
	    * Includes slider settings form used in plugin
	    * @version 1.0.0
		* @package WP Captcha
	    */
		public function wpc_tab_settings() {
			
			include( WPC_FORMS . '/wpc-settings.php');
		}
		
		/**
		* This function used to add captcha into the login form.
		* @version 1.0.0
		* @package WP Captcha
		*/	
        public function wpc_captcha_login_form() {
			
			$this->wpc_display_captcha();
			
			return true;
		}
		
		/**
		* This function used to add captcha into the register form.
		* @version 1.0.0
		* @package WP Captcha
		*/
		public function wpc_captcha_register_form() {
			
			$this->wpc_display_captcha();
			
			return true;
		}
		
		/**
		* This function used to add captcha into the comment form.
		* @version 1.0.0
		* @package WP Captcha
		*/
		public function wpc_captcha_comment_form() {
			
			$options = get_option( '_wpc_captcha_settings' );
			$options = unserialize($options);
			
			$wpc_enable_hide_comment_form = ( $options['wpc_enable_hide_comment_form'] != "" ) ? sanitize_text_field( $options['wpc_enable_hide_comment_form'] ) : 'true';
			
			/**
			* Skip captcha if user is logged in and the settings allow
			* @version 1.0.0
			* @package WP Captcha
			*/
			if ( is_user_logged_in() && $wpc_enable_hide_comment_form == 'true' ) {
				
				return true;
			}
	
			$this->wpc_display_captcha();
	
			remove_action( 'comment_form', array(&$this, 'wpc_captcha_comment_form') );
	
			return true;
		}
		
		/**
		* This function used to captcha logic work.
		* @version 1.0.0
		* @package WP Captcha
		*/	
		public function wpc_display_captcha() {
			
			$options = get_option( '_wpc_captcha_settings' );
			$options = unserialize($options);
			
			$wpc_captcha_title = ( $options['wpc_captcha_title'] != "" ) ? sanitize_text_field( $options['wpc_captcha_title'] ) : '';
		
			// generating random numbers
			$wpc_random_number1 = rand( 1, 9 );
			$wpc_random_number2 = rand( 1, 9 );
			?>
            
            <p>
				<?php if ( !empty($wpc_captcha_title) ) { ?>	
                
                <label for="<?php echo $wpc_captcha_title; ?>"> <?php echo $wpc_captcha_title; ?> </label><br />
                
                <?php } ?>
		
            	<?php echo $wpc_random_number1 . ' + ' . $wpc_random_number2 . ' = '; ?>
                
                <input name="wpc_random_total" type="text" maxlength="2" size="2" style="margin-bottom:0; display:inline; font-size: 12px; width: 40px;" />
    
                <input name="wpc_random_number1" type="hidden" value="<?php echo $wpc_random_number1; ?>" />
                <input name="wpc_random_number2" type="hidden" value="<?php echo $wpc_random_number2; ?>" />
            <p>
            <br />
            <?php
		}
		
		/**
		* This function checks the captcha posted with a login when login errors are absent
		* @version 1.0.0
		* @package WP Captcha
		*/	
		public function wpc_captcha_login_check($user) {
			
			$wpc_random_total = sanitize_text_field($this->wpc_string($_REQUEST["wpc_random_total"]));
			$wpc_first_randnumber = sanitize_text_field($this->wpc_string($_REQUEST["wpc_random_number1"]));
			$wpc_second_randnumber = sanitize_text_field($this->wpc_string($_REQUEST["wpc_random_number2"]));
			
			/* Add error if captcha is empty */			
			if ( ( !isset( $wpc_random_total ) || "" == $wpc_random_total ) && isset($_REQUEST["loggedout"]) ) {
				
				$error = new WP_Error();
				
				$error->add( 'wpc_captcha_error', '<strong>' . __( 'ERROR', 'wp-captcha' ) . '</strong>: Please enter a Captcha value.');
				
				wp_clear_auth_cookie();
				
				return $error;
			}
			
			if ( isset( $wpc_random_total ) && isset( $wpc_first_randnumber ) && isset( $wpc_second_randnumber ) ) {
				
				$wpc_checktotal = $wpc_first_randnumber + $wpc_second_randnumber;
				
				if ( $wpc_random_total == $wpc_checktotal ) {
				
					/* Captcha was matched */
					return $user;
												
				} else {
					
					wp_clear_auth_cookie();
					/* Add error if captcha is incorrect */
					
					$error = new WP_Error();
					
					if ( empty($wpc_random_total) )
						$error->add( 'wpc_captcha_error', '<strong>' . __( 'ERROR', 'captcha' ) . '</strong>: Please enter a Captcha value.');
					else
						$error->add( 'wpc_captcha_error', '<strong>' . __( 'ERROR', 'captcha' ) . '</strong>: Please enter a valid Captcha value.');
					
					return $error;
				}
			} else {
				
				if ( isset( $_REQUEST["log"] ) && isset( $_REQUEST["pwd"] ) ) {
					
					/* captcha was not found in _REQUEST */
					$error = new WP_Error();
					$error->add( 'wpc_captcha_error', '<strong>' . __( 'ERROR', 'captcha' ) . '</strong>: Please enter a Captcha value.');
					
					return $error;
					
				} else {
					
					/* it is not a submit */
					return $user;
				}
			} 
		}
		
		/**
		* This function checks the captcha posted with registration
		* @version 1.0.0
		* @package WP Captcha
		*/
		public function wpc_captcha_register_post($login, $email, $errors) {
			
			$wpc_random_total = sanitize_text_field($this->wpc_string($_REQUEST["wpc_random_total"]));
			$wpc_first_randnumber = sanitize_text_field($this->wpc_string($_REQUEST["wpc_random_number1"]));
			$wpc_second_randnumber = sanitize_text_field($this->wpc_string($_REQUEST["wpc_random_number2"]));
			
			/**
			* Captcha is empty
			* @version 1.0.0
			* @package WP Captcha
			*/
			if ( !isset( $wpc_random_total ) || empty($wpc_random_total) ) {
								
					$errors->add( 'wpc_captcha_blank', '<strong>' . __( 'ERROR', 'wp-captcha' ) . '</strong>: Please enter a Captcha value.');
					
					return $errors;
			}
			
			if ( isset( $wpc_random_total ) && isset( $wpc_first_randnumber ) && isset( $wpc_second_randnumber ) ) {
				
				$wpc_checktotal = $wpc_first_randnumber + $wpc_second_randnumber;
				
				if ( $wpc_random_total == $wpc_checktotal ) {
				
					/* Captcha was matched */
					return;
					
				} else {
					
					wp_clear_auth_cookie();
					
					$errors->add( 'wpc_captcha_error', '<strong>' . __( 'ERROR', 'wp-captcha' ) . '</strong>: Please enter a valid Captcha value.');
					
					return $errors;
				}
				
			} else {
				
				$errors->add( 'wpc_captcha_wrong', '<strong>'. __( 'ERROR', 'wp-captcha') . '</strong>: Please enter a valid Captcha value.');
				
				return $errors;
			}
		}

		
		/**
		* This function checks the captcha posted with forgot password form
		* @version 1.0.0
		* @package WP Captcha
		*/
		public function wpc_captcha_forgot_password() {
			
			$wpc_random_total = sanitize_text_field($this->wpc_string($_REQUEST["wpc_random_total"]));
			$wpc_first_randnumber = sanitize_text_field($this->wpc_string($_REQUEST["wpc_random_number1"]));
			$wpc_second_randnumber = sanitize_text_field($this->wpc_string($_REQUEST["wpc_random_number2"]));
			
			/* If captcha doesn't entered */
			if ( ! isset( $wpc_random_total ) || empty($wpc_random_total) ) {
				
					wp_die( 'Please enter a Captcha value' );
			}
			
			/* Check entered captcha */
			if ( isset( $wpc_random_total ) && isset( $wpc_first_randnumber ) && isset( $wpc_second_randnumber ) ) {
					
				$wpc_checktotal = $wpc_first_randnumber + $wpc_second_randnumber;
				
				if ( $wpc_random_total == $wpc_checktotal ) {
				
					/* Captcha was matched */
					return;
												
				} else {
					
					wp_clear_auth_cookie();
					
					wp_die( 'Please enter a valid Captcha value' );
				}
				
			} else {
							
				wp_die( 'Please enter a Captcha value' );
			}
		}
		
		/**
		* This function used to checks captcha posted with the comments.
		* @version 1.0.0
		* @package WP Captcha
		*/
		public function wpc_captcha_comment_post($comment) {

			$options = get_option( '_wpc_captcha_settings' );
			$options = unserialize($options);
			
			$wpc_random_total = sanitize_text_field($this->wpc_string($_REQUEST["wpc_random_total"]));
			$wpc_first_randnumber = sanitize_text_field($this->wpc_string($_REQUEST["wpc_random_number1"]));
			$wpc_second_randnumber = sanitize_text_field($this->wpc_string($_REQUEST["wpc_random_number2"]));
			
			$wpc_enable_hide_comment_form = ( $options['wpc_enable_hide_comment_form'] != "" ) ? sanitize_text_field( $options['wpc_enable_hide_comment_form'] ) : 'true';
			
			/**
			* Skip captcha if user is logged in and the settings allow
			* @version 1.0.0
			* @package WP Captcha
			*/
			if ( is_user_logged_in() && $wpc_enable_hide_comment_form == 'true' ) {
				
				return $comment;
			}
			
			/**
			* Skip captcha for comment replies from the admin menu
			* @version 1.0.0
			* @package WP Captcha
			*/
			if ( isset( $_REQUEST["action"] ) && 'replyto-comment' == $_REQUEST["action"] &&
			( check_ajax_referer( 'replyto-comment', '_ajax_nonce', false ) || check_ajax_referer( 'replyto-comment', '_ajax_nonce-replyto-comment', false ) ) ) {
				
				return $comment;
			}
			
			/**
			* Skip captcha for trackback or pingback
			* @version 1.0.0
			* @package WP Captcha
			*/
			if ( !empty($comment['comment_type']) && $comment['comment_type'] != 'comment' ) {
				
				return $comment;
			}
			
			/**
			* Captcha is empty
			* @version 1.0.0
			* @package WP Captcha
			*/
			if ( ! isset( $wpc_random_total ) || empty($wpc_random_total) ) {
				
					wp_die( 'Please enter a Captcha value' );
			}
			
			/**
			* Check entered captcha
			* @version 1.0.0
			* @package WP Captcha
			*/
			if ( isset( $wpc_random_total ) && isset( $wpc_first_randnumber ) && isset( $wpc_second_randnumber ) ) {
				
				$wpc_checktotal = $wpc_first_randnumber + $wpc_second_randnumber;
				
				if ( $wpc_random_total == $wpc_checktotal ) {
				
					/**
					* Captcha was matched
					* @version 1.0.0
					* @package WP Captcha
					*/
					return $comment;
												
				} else {
					
					wp_clear_auth_cookie();
					
					wp_die( 'Please enter a valid Captcha value' );
				}
				
			} else {
							
				wp_die( 'Please enter a Captcha value' );
			}
		}
					
		/**
		* This function used to load text domain for multilanguages.
		* @version 1.0.0
		* @package WP Captcha
		*/	
		public function wpc_load_languages() {
			
		 	// Load Text Domain
			load_plugin_textdomain( 'wp-captcha', false, dirname( plugin_basename( __FILE__ ) ).'/languages/' );
		}
		
		/**
	    * Load JS and CSS in backend
	    * @version 1.0.0
		* @package WP Captcha
	    */
		public function wpc_load_scripts_backend() {

            if( is_admin() ) { 
            
                wp_enqueue_style( 'wpc-backend-css', WPC_CSS.'wpc-backend-css.css' );
            }
        }
		
		/**
	    * Return string
	    * @version 1.0.0
		* @package WP Captcha
	    */
		public function wpc_string($text) {
			
			return htmlspecialchars(stripslashes($text));
		}
		
    } // END class WP_Captcha
	
	/**
	* Initialize WP_Captcha class
	*/
	$wpc_captcha = new WP_Captcha();
	
} // END if( !class_exists('WP_Captcha') )