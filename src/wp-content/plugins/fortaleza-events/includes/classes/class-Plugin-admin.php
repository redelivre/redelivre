<?php
/** @wordpress-plugin
 * Author:            CwebConsultants
 * Author URI:        http://www.cwebconsultants.com/
 */
/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 */

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 * @author     Your Name <email@example.com>
 */
class cWeb_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name       The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_action('admin_menu', array(&$this, 'register_my_custom_menu_page'));
                //add_action( 'init', array(&$this, '_cweb_custom_post'));               

	}

	/**
	 * Register the stylesheets for the Dashboard.
	 *
	 * @since    1.0.0
	 */
		public function enqueue_styles() {
			/**
			 * This function is provided for demonstration purposes only.
			 *
			 * An instance of this class should be passed to the run() function
			 * defined in Plugin_Name_Loader as all of the hooks are defined
			 * in that particular class.
			 *
			 * The Plugin_Name_Loader will then create the relationship
			 * between the defined hooks and the functions defined in this
			 * class.
			 */

                    wp_enqueue_style( $this->plugin_name, plugin_dir_url(dirname(dirname(__FILE__))) . 'admin/css/admin.css', array(), $this->version, 'all' );
                    
		    wp_enqueue_style( 'datepicker', plugin_dir_url(dirname(dirname(__FILE__))) . 'admin/css/daterangepicker.css', array(), $this->version, 'all' );
		}

		/**
		* Register the JavaScript for the dashboard.
		*
		* @since    1.0.0
		*/
		
	   public function enqueue_scripts() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
                wp_enqueue_script( 'admin_custom_jquery', plugin_dir_url(dirname(dirname(__FILE__))) . 'admin/js/custom_jquery.js', array( 'jquery' ), $this->version, false );
                wp_enqueue_script( 'jquery-moment', plugin_dir_url(dirname(dirname(__FILE__))) . 'admin/js/moment.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'jquery-daterangepicker', plugin_dir_url(dirname(dirname(__FILE__))) . 'admin/js/daterangepicker.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'jquery-selectRange', plugin_dir_url(dirname(dirname(__FILE__))) . 'admin/js/selectRange.js', array( 'jquery' ), $this->version, false );

		}

		/** Menu Function **/
        function register_my_custom_menu_page() {
            global $submenu;
			global $PluginTextDomain;
			global $cwebPluginName;
            add_menu_page(__($cwebPluginName,$PluginTextDomain), __('fortaleza-events',$PluginTextDomain), 'read', 'settings', array(&$this, 'manage_settings'));
            //add_submenu_page('cweb_review', __('Settings',$PluginTextDomain), __('Settings',$PluginTextDomain),'8', 'settings', array(&$this, 'manage_settings'));
            //add_submenu_page('cweb_review', __('Actor User',$PluginTextDomain), __('Actor User',$PluginTextDomain),'8', 'actor_user', array(&$this, 'manage_actor_data'));
           // add_submenu_page('cweb_review', __('Creator User',$PluginTextDomain), __('Creator User',$PluginTextDomain),'8', 'creator_user', array(&$this, 'manage_creator_data'));
            //add_submenu_page('cweb_review', __('Brands User',$PluginTextDomain), __('Brands User',$PluginTextDomain),'8', 'brands_user', array(&$this, 'manage_brands_data'));
        }

       
        /** Catalogue Function **/
        function manage_settings() {
	    global $PluginTextDomain;
            if (!current_user_can('read')) {
                wp_die(__('You do not have sufficient permissions to access this page.',$PluginTextDomain));
            } else {
                include(CWEB_FS_PATH1 . 'admin/admin-pages/settings.php');
            }
        }
    }
