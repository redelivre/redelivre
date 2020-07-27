<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       piwebsolution.com
 * @since      1.0.0
 *
 * @package    Pisol_Ewcl
 * @subpackage Pisol_Ewcl/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Pisol_Ewcl
 * @subpackage Pisol_Ewcl/admin
 * @author     Rajesh Singh <rajeshsingh520@gmail.com>
 */
class Pisol_Ewcl_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		new Pi_Ewcl_Menu($this->plugin_name, $this->version);

		add_action('admin_init', array($this,'plugin_redirect'));

		add_action('plugins_loaded', array($this,'clearCsvFolder'));
	}

	function plugin_redirect(){
		if (get_option('pi_ewcl_do_activation_redirect', false)) {
			delete_option('pi_ewcl_do_activation_redirect');
			if(!isset($_GET['activate-multi']))
			{
				wp_redirect('admin.php?page=pisol-ewcl-notification');
			}
		}
	}

	function clearCsvFolder(){
		$upload_dir   = wp_upload_dir();
        $directory =  $upload_dir['basedir'].'/ewcl_customers/';
		$files = glob($directory.'*.csv'); //get all file names
		foreach($files as $file){
			if(is_file($file))
			unlink($file); //delete file
		}
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Pisol_Ewcl_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Pisol_Ewcl_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Pisol_Ewcl_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Pisol_Ewcl_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/pisol-ewcl-admin.js', array( 'jquery' ), $this->version, false );

	}

}
