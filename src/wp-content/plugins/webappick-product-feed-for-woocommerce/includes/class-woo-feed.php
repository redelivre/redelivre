<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://webappick.com
 * @since      1.0.0
 *
 * @package    Woo_Feed
 * @subpackage Woo_Feed/includes
 */
/** @define "WOO_FEED_FREE_PATH''./../" */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woo_Feed
 * @subpackage Woo_Feed/includes
 * @author     Wahid <wahid0003@gmail.com.com>
 */
class Woo_Feed {
	
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Woo_Feed_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;
	
	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $woo_feed The string used to uniquely identify this plugin.
	 */
	protected $woo_feed;
	
	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;
	
	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->woo_feed = 'woo-feed';
		$this->version  = WOO_FEED_FREE_VERSION;
	}
	
	
	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woo_Feed_Loader. Orchestrates the hooks of the plugin.
	 * - Woo_Feed_i18n. Defines internationalization functionality.
	 * - Woo_Feed_Admin. Defines all hooks for the admin area.
	 * - Woo_Feed_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		
		/**
		 * Load Error Logger File Handler
		 */
		require_once WOO_FEED_FREE_PATH . 'includes/classes/class-woo-feed-log-handler-file.php';
		/**
		 * Support for older version of WooCommerce
		 */
		require_once WOO_FEED_FREE_PATH . 'includes/wc-legacy-support.php';
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once WOO_FEED_FREE_PATH . 'includes/class-woo-feed-loader.php';
		
		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once WOO_FEED_FREE_PATH . 'includes/class-woo-feed-i18n.php';
		
		/**
		 * The class responsible for getting all product information
		 * of the plugin.
		 */
		require_once WOO_FEED_FREE_PATH . 'includes/classes/class-woo-feed-products.php';
		require_once WOO_FEED_FREE_PATH . 'includes/classes/class-woo-feed-products-v3.php';
		require_once WOO_FEED_FREE_PATH . 'includes/classes/class-woo-feed-merchant.php';
		
		/**
		 * The class responsible for processing feed
		 */
		require_once WOO_FEED_FREE_PATH . 'includes/classes/class-woo-feed-engine.php';
		
		/**
		 * The class contain all merchants attribute dropdown
		 */
		require_once WOO_FEED_FREE_PATH . 'includes/classes/class-woo-feed-dropdown.php';
		
		/**
		 * The class contain merchant attributes
		 */
		require_once WOO_FEED_FREE_PATH . 'includes/classes/class-woo-feed-default-attributes.php';
		
		/**
		 * The class responsible for generating feed
		 */
		require_once WOO_FEED_FREE_PATH . 'includes/feeds/class-woo-feed-generate.php';
		
		/**
		 * The class is a FTP library
		 */
		require_once WOO_FEED_FREE_PATH . 'includes/classes/class-woo-feed-ftp.php';
		
		/**
		 * The class responsible for save feed
		 */
		require_once WOO_FEED_FREE_PATH . 'includes/classes/class-woo-feed-savefile.php';
		require_once WOO_FEED_FREE_PATH . 'includes/classes/class-woo-feed-admin-message.php';
		/**
		 * Merchant classes
		 */
		require_once WOO_FEED_FREE_PATH . 'includes/feeds/class-woo-feed-google.php';
		require_once WOO_FEED_FREE_PATH . 'includes/feeds/class-woo-feed-facebook.php';
		require_once WOO_FEED_FREE_PATH . 'includes/feeds/class-woo-feed-pinterest.php';
		require_once WOO_FEED_FREE_PATH . 'includes/feeds/class-woo-feed-custom.php';
		/**
		 * Docs Page Class
		 */
		require_once WOO_FEED_FREE_PATH . 'includes/class-woo-feed-docs.php';
		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once WOO_FEED_FREE_PATH . 'admin/class-woo-feed-admin.php';
		
		/**
		 * The class responsible for making list table
		 */
		require_once WOO_FEED_FREE_PATH . 'includes/classes/class-woo-feed-list-table.php';
		
		
		/**
		 * The class responsible for making feed list
		 */
		require_once WOO_FEED_FREE_PATH . 'admin/class-woo-feed-manage-list.php';
		
		require_once WOO_FEED_FREE_PATH . 'includes/classes/class-woo-feed-sftp.php';
		
		$this->loader = new Woo_Feed_Loader();
	}
	
	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woo_Feed_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Woo_Feed_i18n();
		$plugin_i18n->set_domain( $this->get_woo_feed() );
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}
	
	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		
		$plugin_admin = new Woo_Feed_Admin( $this->get_woo_feed(), $this->get_version() );
		
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'load_admin_pages' );
		$this->loader->add_action( 'admin_page_access_denied', $plugin_admin, 'handle_old_menu_slugs' );
		$this->loader->add_filter( 'plugin_action_links_' . WOO_FEED_PLUGIN_BASE_NAME, $plugin_admin, 'woo_feed_plugin_action_links' );
	}
	
	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		if ( wooFeed_check_WC() && wooFeed_is_WC_supported() ) {
			$this->load_dependencies();
			$this->set_locale();
			$this->define_admin_hooks();
			$this->loader->run();
		}
	}
	
	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_woo_feed() {
		return $this->woo_feed;
	}
	
	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Woo_Feed_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}
	
	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}
}