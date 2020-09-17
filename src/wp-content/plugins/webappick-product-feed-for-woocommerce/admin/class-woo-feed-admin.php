<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://webappick.com/
 * @since      1.0.0
 *
 * @package    Woo_Feed
 * @subpackage Woo_Feed/admin
 * @author     Ohidul Islam <wahid@webappick.com>
 */
class Woo_Feed_Admin {
	
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $woo_feed The ID of this plugin.
	 */
	private $woo_feed;
	
	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;
	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $woo_feed The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 *
	 */
	public function __construct( $woo_feed, $version ) {
		
		$this->woo_feed = $woo_feed;
		$this->version  = $version;
		
	}
	
	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @param string $hook
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook ) {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in woo_feed_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The woo_feed_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$mainDeps = array();
		$ext = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.css' : '.min.css';
		if ( false !== strpos( $hook, 'webappick' ) && false !== strpos( $hook, 'feed' ) ) {
			wp_enqueue_style('thickbox');
			wp_register_style( 'selectize', plugin_dir_url( __FILE__ ) . 'css/selectize' . $ext, array(), $this->version );
			wp_enqueue_style( 'fancy-select', plugin_dir_url( __FILE__ ) . 'css/fancy-select' . $ext, array(), $this->version );
			wp_register_style( 'slick', plugin_dir_url( __FILE__ ) . 'css/slick' . $ext, array(), $this->version );
			wp_register_style( 'slick-theme', plugin_dir_url( __FILE__ ) . 'css/slick-theme' . $ext, array(), $this->version );
			$mainDeps = array( 'selectize', 'fancy-select', 'list-tables', 'edit' );
			if ( 'woo-feed_page_webappick-feed-pro-vs-free' == $hook ) {
				$mainDeps = array_merge( $mainDeps, array( 'slick', 'slick-theme' ) );
			}
		}
		wp_register_style( $this->woo_feed, plugin_dir_url( __FILE__ ) . 'css/woo-feed-admin' . $ext, $mainDeps, $this->version, 'all' );
		wp_register_style( $this->woo_feed . '-pro', plugin_dir_url( __FILE__ ) . 'css/woo-feed-admin-pro' . $ext, [ $this->woo_feed ], $this->version, 'all' );
		wp_enqueue_style( $this->woo_feed );
		wp_enqueue_style( $this->woo_feed . '-pro' );
	}
	
	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @param string $hook
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woo_Feed_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The woo_feed_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$ext = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.js' : '.min.js';
		if ( false !== strpos( $hook, 'webappick' ) && false !== strpos( $hook, 'feed' ) ) {
			wp_enqueue_script('thickbox');
			if ( is_network_admin() ) {
				add_action( 'admin_head', '_thickbox_path_admin_subfolder' );
			}
			wp_register_script( 'jquery-selectize', plugin_dir_url( __FILE__ ) . 'js/selectize.min.js', array( 'jquery' ), $this->version, false );
			wp_register_script( 'fancy-select', plugin_dir_url( __FILE__ ) . 'js/fancy-select' . $ext, array( 'jquery' ), $this->version, false );
			wp_register_script( 'jquery-validate', plugin_dir_url( __FILE__ ) . 'js/jquery.validate.min.js', array( 'jquery' ), $this->version, false );
			wp_register_script( 'jquery-validate-additional-methods', plugin_dir_url( __FILE__ ) . 'js/additional-methods.min.js', array( 'jquery', 'jquery-validate' ), $this->version, false );
			wp_register_script( 'jquery-sortable', plugin_dir_url( __FILE__ ) . 'js/jquery-sortable' . $ext, array( 'jquery' ), $this->version, false );
			
			if ( ! wp_script_is( 'clipboard', 'registered' ) ) {
				wp_register_script( 'clipboard', plugin_dir_url( __FILE__ ) . 'js/clipboard.min.js', [], '2.0.4', false );
			}
			
			$feedScriptDependency = [
				'jquery',
				'clipboard',
				'jquery-selectize',
				'jquery-sortable',
				'jquery-validate',
				'jquery-validate-additional-methods',
				'wp-util',
				'utils',
				'wp-lists',
				'postbox',
				'tags-box',
				// 'underscore', 'word-count', 'jquery-ui-autocomplete',
				'jquery-touch-punch',
				'fancy-select',
			];
			
			wp_register_script( $this->woo_feed, plugin_dir_url( __FILE__ ) . 'js/woo-feed-admin' . $ext, $feedScriptDependency, $this->version, false );
			
			$js_opts = array(
				'wpf_ajax_url' => admin_url( 'admin-ajax.php' ),
				'wpf_debug'    => woo_feed_is_debugging_enabled(),
				'pages'        => [
					'list' => [
						'feed' => esc_url( admin_url( 'admin.php?page=webappick-manage-feeds' ) ),
					],
				],
				'nonce'        => wp_create_nonce( 'wpf_feed_nonce' ),
				'is_feed_edit' => isset( $_GET['page'], $_GET['action'] ) && 'webappick-manage-feeds' == $_GET['page'] && 'edit-feed' == $_GET['action'], // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				'is_feed_add'  => isset( $_GET['page'] ) && 'webappick-new-feed' == $_GET['page'], // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				'na'           => esc_html__( 'N/A', 'woo-feed' ),
				'regenerate'   => esc_html__( 'Generating...', 'woo-feed' ),
				'learn_more'   => esc_html__( 'Learn More..', 'woo-feed' ),
				'form'         => array(
					'select_category'     => esc_attr__( 'Select A Category', 'woo-feed' ),
					'loading_tmpl'        => esc_html__( 'Loading Template...', 'woo-feed' ),
					'generate'            => esc_html__( 'Delivering Configuration...', 'woo-feed' ),
					'save'                => esc_html__( 'Saving Configuration...', 'woo-feed' ),
					'sftp_checking'       => esc_html__( 'Wait! Checking Extensions ...', 'woo-feed' ),
					'sftp_warning'        => esc_html__( 'Warning! Enable PHP ssh2 extension to use SFTP. Contact your server administrator.', 'woo-feed' ),
					'sftp_available'      => esc_html__( 'SFTP Available!', 'woo-feed' ),
					'one_item_required'   => esc_html__( 'Please add one or more items to continue.', 'woo-feed' ),
					'google_category'     => woo_feed_merchant_require_google_category(),
					'del_confirm'         => esc_html__( 'Are you sure you want to delete this item?', 'woo-feed' ),
					'del_confirm_multi'   => esc_html__( 'Are you sure you want to delete selected items?', 'woo-feed' ),
					'item_wrapper_hidden' => woo_feed_get_item_wrapper_hidden_merchant(),
				),
				'generator'    => [
					'limit'      => woo_feed_get_options( 'per_batch' ),
					'feed'       => '',
					'regenerate' => false,
				],
				'ajax'         => [
					'url'   => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'wpf_feed_nonce' ),
					'error' => esc_html__( 'There was an error processing ajax request.', 'woo-feed' ),
				],
			);
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( ( isset( $_GET['feed_created'] ) || isset( $_GET['feed_updated'] ) || isset( $_GET['feed_imported'] ) ) && isset( $_GET['feed_regenerate'] ) && 1 == $_GET['feed_regenerate'] ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$fileName = isset( $_GET['feed_name'] ) && ! empty( $_GET['feed_name'] ) ? sanitize_text_field( $_GET['feed_name'] ) : ''; // trigger feed regenerate...
				if ( ! empty( $fileName ) ) {
					// filename must be wf_config+XXX format for js to work.
					$js_opts['generator']['feed'] = 'wf_config' . woo_feed_extract_feed_option_name( $fileName );
					$js_opts['generator']['regenerate'] = true;
				}
			}
			wp_localize_script( $this->woo_feed, 'wpf_ajax_obj', $js_opts );
			wp_enqueue_script( $this->woo_feed );
			
			if ( 'woo-feed_page_webappick-feed-pro-vs-free' === $hook ) {
				wp_register_script( 'jquery-slick', plugin_dir_url( __FILE__ ) . 'js/slick' . $ext, array( 'jquery' ), $this->version, false );
				wp_register_script( $this->woo_feed . '-pro', plugin_dir_url( __FILE__ ) . 'js/woo-feed-admin-pro' . $ext, [ $this->woo_feed, 'jquery-slick' ], $this->version, false );
				wp_enqueue_script( $this->woo_feed . '-pro' );
			}
		}
	}
	
	/**
	 * Add Go to Pro and Documentation link
	 * @param array $links
	 * @return array
	 */
	public function woo_feed_plugin_action_links( $links ) {
		
		$links[] = '<a style="color: #389e38; font-weight: bold;" href="https://webappick.com/plugin/woocommerce-product-feed-pro/?utm_source=freePlugin&utm_medium=go_premium&utm_campaign=free_to_pro&utm_term=wooFeed" target="_blank">' . __( 'Get Pro', 'woo-feed' ) . '</a>';
		/** @noinspection HtmlUnknownTarget */
		$links[] = sprintf( '<a style="color:#ce7304; font-weight: bold;" href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=webappick-feed-docs' ) ), __( 'Docs', 'woo-feed' ) );
		/** @noinspection HtmlUnknownTarget */
		$links[] = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=webappick-feed-settings' ) ), __( 'Settings', 'woo-feed' ) );
		return $links;
	}
	
	/**
	 * Register the Plugin's Admin Pages for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function load_admin_pages() {
		/**
		 * This function is provided for making admin pages into admin area.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WOO_FEED_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WOO_FEED_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if ( function_exists( 'add_options_page' ) ) {
			add_menu_page( __( 'Woo Feed', 'woo-feed' ), __( 'Woo Feed', 'woo-feed' ), 'manage_woocommerce', 'webappick-manage-feeds', 'woo_feed_manage_feed', 'dashicons-rss' );
			add_submenu_page( 'webappick-manage-feeds', __( 'Manage Feeds', 'woo-feed' ), __( 'Manage Feeds', 'woo-feed' ), 'manage_woocommerce', 'webappick-manage-feeds', 'woo_feed_manage_feed' );
			add_submenu_page( 'webappick-manage-feeds', __( 'Make Feed', 'woo-feed' ), __( 'Make Feed', 'woo-feed' ), 'manage_woocommerce', 'webappick-new-feed', 'woo_feed_generate_new_feed' );
            add_submenu_page( 'webappick-manage-feeds', __( 'Category Mapping', 'woo-feed' ), __( 'Category Mapping', 'woo-feed' ), 'manage_woocommerce', 'webappick-feed-category-mapping', 'woo_feed_category_mapping' );
			add_submenu_page( 'webappick-manage-feeds', __( 'Settings', 'woo-feed' ), __( 'Settings', 'woo-feed' ), 'manage_woocommerce', 'webappick-feed-settings', 'woo_feed_config_feed' );
			add_submenu_page( 'webappick-manage-feeds', __( 'Documentation', 'woo-feed' ), '<span class="woo-feed-docs">' . __( 'Docs', 'woo-feed' ) . '</span>', 'manage_woocommerce', 'webappick-feed-docs', array( WooFeedDocs::getInstance(), 'woo_feed_docs' ) );
		}
	}
	
	/**
	 * Redirect user to with new menu slug (if user browser any bookmarked url)
	 * @return void
	 * @since 3.1.7
	 */
	public function handle_old_menu_slugs() {
		global $pagenow;
		// redirect user to new old slug => new slug
		$redirect_to = array(
			'webappick-product-feed-for-woocommerce/admin/class-woo-feed-admin.php' => 'webappick-new-feed',
			'woo_feed_manage_feed' => 'webappick-manage-feeds',
			'woo_feed_config_feed' => 'webappick-feed-settings',
			'woo_feed_pro_vs_free' => 'webappick-feed-pro-vs-free',
		);
		if ( 'admin.php' === $pagenow && isset( $plugin_page ) && ! empty( $plugin_page ) ) {
			foreach ( $redirect_to as $from => $to ) {
				if ( $plugin_page !== $from ) {
					continue;
				}
				wp_safe_redirect( admin_url( 'admin.php?page=' . $to ), 301 );
				die();
			}
		}
	}
}
