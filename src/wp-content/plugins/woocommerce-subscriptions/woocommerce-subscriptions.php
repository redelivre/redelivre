<?php
/**
 * Plugin Name: WooCommerce Subscriptions
 * Plugin URI: https://www.woocommerce.com/products/woocommerce-subscriptions/
 * Description: Sell products and services with recurring payments in your WooCommerce Store.
 * Author: WooCommerce
 * Author URI: https://woocommerce.com/
 * Version: 3.0.5
 *
 * WC requires at least: 3.0.9
 * WC tested up to: 4.2
 * Woo: 27147:6115e6d7e297b623a169fdcf5728b224
 *
 * Copyright 2019 WooCommerce
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package		WooCommerce Subscriptions
 * @author		WooCommerce.
 * @since		1.0
 */

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) || ! function_exists( 'is_woocommerce_active' ) ) {
	require_once( dirname( __FILE__ ) . '/woo-includes/woo-functions.php' );
}

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), '6115e6d7e297b623a169fdcf5728b224', '27147' );

/**
 * Check if WooCommerce is active and at the required minimum version, and if it isn't, disable Subscriptions.
 *
 * @since 1.0
 */
if ( ! is_woocommerce_active() || version_compare( get_option( 'woocommerce_db_version' ), WC_Subscriptions::$wc_minimum_supported_version, '<' ) ) {
	add_action( 'admin_notices', 'WC_Subscriptions::woocommerce_inactive_notice' );
	return;
}

define( 'WCS_INIT_TIMESTAMP', gmdate( 'U' ) );

// Manually load functions files.
require_once( dirname( __FILE__ ) . '/wcs-functions.php' );
require_once( dirname( __FILE__ ) . '/includes/gateways/paypal/includes/wcs-paypal-functions.php' );

// Load and set up the Autoloader
require_once( dirname( __FILE__ ) . '/includes/class-wcs-autoloader.php' );
$wcs_autoloader = new WCS_Autoloader( dirname( __FILE__ ) );
$wcs_autoloader->register();

// Load libraries manually.
require_once( dirname( __FILE__ ) . '/includes/libraries/action-scheduler/action-scheduler.php' );

// Initialize our classes.
WC_Subscriptions_Coupon::init();
WC_Subscriptions_Product::init();
WC_Subscriptions_Admin::init();
WC_Subscriptions_Manager::init();
WC_Subscriptions_Cart::init();
WC_Subscriptions_Cart_Validator::init();
WC_Subscriptions_Order::init();
WC_Subscriptions_Renewal_Order::init();
WC_Subscriptions_Checkout::init();
WC_Subscriptions_Email::init();
WC_Subscriptions_Addresses::init();
WC_Subscriptions_Change_Payment_Gateway::init();
WC_Subscriptions_Payment_Gateways::init();
WCS_PayPal_Standard_Change_Payment_Method::init();
WC_Subscriptions_Switcher::init();
WC_Subscriptions_Tracker::init();
WCS_Upgrade_Logger::init();
new WCS_Cart_Renewal();
new WCS_Cart_Resubscribe();
new WCS_Cart_Initial_Payment();
WCS_Download_Handler::init();
WCS_Retry_Manager::init();
new WCS_Cart_Switch();
WCS_Limiter::init();
WCS_Admin_System_Status::init();
WCS_Upgrade_Notice_Manager::init();
WCS_Staging::init();
WCS_Permalink_Manager::init();
WCS_Custom_Order_Item_Manager::init();
WCS_Early_Renewal_Modal_Handler::init();
WCS_Dependent_Hook_Manager::init();

// Some classes run init on a particular hook.
add_action( 'init', array( 'WC_Subscriptions_Synchroniser', 'init' ) );
add_action( 'after_setup_theme', array( 'WC_Subscriptions_Upgrader', 'init' ), 11 );
add_action( 'init', array( 'WC_PayPal_Standard_Subscriptions', 'init' ), 11 );
add_action( 'init', array( 'WCS_WC_Admin_Manager', 'init' ), 11 );

/**
 * The main subscriptions class.
 *
 * @since 1.0
 */
class WC_Subscriptions {

	public static $name = 'subscription';

	public static $activation_transient = 'woocommerce_subscriptions_activated';

	public static $plugin_file = __FILE__;

	public static $version = '3.0.5';

	public static $wc_minimum_supported_version = '3.0';

	private static $total_subscription_count = null;

	private static $scheduler;

	/** @var WCS_Cache_Manager */
	public static $cache;

	/** @var WCS_Autoloader */
	protected static $autoloader;

	/**
	 * Set up the class, including it's hooks & filters, when the file is loaded.
	 *
	 * @since 1.0
	 *
	 * @param WCS_Autoloader $autoloader Autoloader instance.
	 */
	public static function init( $autoloader = null ) {
		self::$autoloader = $autoloader ? $autoloader : new WCS_Autoloader( dirname( __FILE__ ) );

		// Register our custom subscription order type after WC_Post_types::register_post_types()
		add_action( 'init', __CLASS__ . '::register_order_types', 6 );

		add_filter( 'woocommerce_data_stores', __CLASS__ . '::add_data_stores', 10, 1 );

		// Register our custom subscription order statuses before WC_Post_types::register_post_status()
		add_action( 'init', __CLASS__ . '::register_post_status', 9 );

		add_action( 'init', __CLASS__ . '::maybe_activate_woocommerce_subscriptions' );

		register_deactivation_hook( __FILE__, __CLASS__ . '::deactivate_woocommerce_subscriptions' );

		// Override the WC default "Add to cart" text to "Sign up now" (in various places/templates)
		add_filter( 'woocommerce_order_button_text', __CLASS__ . '::order_button_text' );
		add_action( 'woocommerce_subscription_add_to_cart', __CLASS__ . '::subscription_add_to_cart', 30 );
		add_action( 'woocommerce_variable-subscription_add_to_cart', __CLASS__ . '::variable_subscription_add_to_cart', 30 );
		add_action( 'wcopc_subscription_add_to_cart', __CLASS__ . '::wcopc_subscription_add_to_cart' ); // One Page Checkout compatibility

		// Enqueue front-end styles, run after Storefront because it sets the styles to be empty
		add_filter( 'woocommerce_enqueue_styles', __CLASS__ . '::enqueue_styles', 100, 1 );

		// Load translation files
		add_action( 'init', __CLASS__ . '::load_plugin_textdomain', 3 );

		// Load frontend scripts
		add_action( 'wp_enqueue_scripts', __CLASS__ . '::enqueue_frontend_scripts', 3 );

		// Load dependent files
		add_action( 'plugins_loaded', __CLASS__ . '::load_dependant_classes' );

		// Attach hooks which depend on WooCommerce constants
		add_action( 'plugins_loaded', array( __CLASS__, 'attach_dependant_hooks' ) );

		// Make sure the related order data store instance is loaded and initialised so that cache management will function
		add_action( 'plugins_loaded', 'WCS_Related_Order_Store::instance' );

		// Make sure the related order data store instance is loaded and initialised so that cache management will function
		add_action( 'plugins_loaded', 'WCS_Customer_Store::instance' );

		// Staging site or site migration notice
		add_action( 'admin_notices', __CLASS__ . '::woocommerce_site_change_notice' );

		// Add the "Settings | Documentation" links on the Plugins administration screen
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), __CLASS__ . '::action_links' );

		add_filter( 'action_scheduler_queue_runner_batch_size', __CLASS__ . '::action_scheduler_multisite_batch_size' );

		add_action( 'in_plugin_update_message-' . plugin_basename( __FILE__ ), __CLASS__ . '::update_notice', 10, 2 );

		// get details of orders of a customer
		add_action( 'wp_ajax_wcs_get_customer_orders', __CLASS__ . '::get_customer_orders' );

		self::$cache = WCS_Cache_Manager::get_instance();

		$scheduler_class = apply_filters( 'woocommerce_subscriptions_scheduler', 'WCS_Action_Scheduler' );

		self::$scheduler = new $scheduler_class();
	}

	 /**
	 * Get customer's order details via ajax.
	 */
	public static function get_customer_orders() {
		check_ajax_referer( 'get-customer-orders', 'security' );

		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			wp_die( -1 );
		}

		$user_id = absint( $_POST['user_id'] );

		$orders = wc_get_orders( array( 'customer' => $user_id, 'post_type' => 'shop_order', 'posts_per_page' => '-1' ) );

		$customer_orders = array();
		foreach ( $orders as $order ) {
			$customer_orders[ wcs_get_objects_property( $order, 'id' ) ] = $order->get_order_number();
		}

		wp_send_json( $customer_orders );
	}

	/**
	 * Register data stores for WooCommerce 3.0+
	 *
	 * @since 2.2.0
	 */
	public static function add_data_stores( $data_stores ) {
		// Our custom data stores.
		$data_stores['subscription']                   = 'WCS_Subscription_Data_Store_CPT';
		$data_stores['product-variable-subscription']  = 'WCS_Product_Variable_Data_Store_CPT';

		// Use WC core data stores for our products.
		$data_stores['product-subscription_variation']      = 'WC_Product_Variation_Data_Store_CPT';
		$data_stores['order-item-line_item_pending_switch'] = 'WC_Order_Item_Product_Data_Store';

		return $data_stores;
	}

	/**
	 * Register core post types
	 *
	 * @since 2.0
	 */
	public static function register_order_types() {

		wc_register_order_type(
			'shop_subscription',
			apply_filters( 'woocommerce_register_post_type_subscription',
				array(
					// register_post_type() params
					'labels'              => array(
						'name'               => __( 'Subscriptions', 'woocommerce-subscriptions' ),
						'singular_name'      => __( 'Subscription', 'woocommerce-subscriptions' ),
						'add_new'            => _x( 'Add Subscription', 'custom post type setting', 'woocommerce-subscriptions' ),
						'add_new_item'       => _x( 'Add New Subscription', 'custom post type setting', 'woocommerce-subscriptions' ),
						'edit'               => _x( 'Edit', 'custom post type setting', 'woocommerce-subscriptions' ),
						'edit_item'          => _x( 'Edit Subscription', 'custom post type setting', 'woocommerce-subscriptions' ),
						'new_item'           => _x( 'New Subscription', 'custom post type setting', 'woocommerce-subscriptions' ),
						'view'               => _x( 'View Subscription', 'custom post type setting', 'woocommerce-subscriptions' ),
						'view_item'          => _x( 'View Subscription', 'custom post type setting', 'woocommerce-subscriptions' ),
						'search_items'       => __( 'Search Subscriptions', 'woocommerce-subscriptions' ),
						'not_found'          => self::get_not_found_text(),
						'not_found_in_trash' => _x( 'No Subscriptions found in trash', 'custom post type setting', 'woocommerce-subscriptions' ),
						'parent'             => _x( 'Parent Subscriptions', 'custom post type setting', 'woocommerce-subscriptions' ),
						'menu_name'          => __( 'Subscriptions', 'woocommerce-subscriptions' ),
					),
					'description'         => __( 'This is where subscriptions are stored.', 'woocommerce-subscriptions' ),
					'public'              => false,
					'show_ui'             => true,
					'capability_type'     => 'shop_order',
					'map_meta_cap'        => true,
					'publicly_queryable'  => false,
					'exclude_from_search' => true,
					'show_in_menu'        => current_user_can( 'manage_woocommerce' ) ? 'woocommerce' : true,
					'hierarchical'        => false,
					'show_in_nav_menus'   => false,
					'rewrite'             => false,
					'query_var'           => false,
					'supports'            => array( 'title', 'comments', 'custom-fields' ),
					'has_archive'         => false,

					// wc_register_order_type() params
					'exclude_from_orders_screen'       => true,
					'add_order_meta_boxes'             => true,
					'exclude_from_order_count'         => true,
					'exclude_from_order_views'         => true,
					'exclude_from_order_webhooks'      => true,
					'exclude_from_order_reports'       => true,
					'exclude_from_order_sales_reports' => true,
					'class_name'                       => self::is_woocommerce_pre( '3.0' ) ? 'WC_Subscription_Legacy' : 'WC_Subscription',
				)
			)
		);
	}

	/**
	 * Method that returns the not found text. If the user has created at least one subscription, the standard message
	 * will appear. If that's empty, the long, explanatory one will appear in the table.
	 *
	 * Filters:
	 * - woocommerce_subscriptions_not_empty: gets passed the boolean option value. 'true' means the subscriptions
	 * list is not empty, the user is familiar with how it works, and standard message appears.
	 * - woocommerce_subscriptions_not_found_label: gets the original message for other plugins to modify, in case
	 * they want to add more links, or modify any of the messages.
	 * @since  2.0
	 *
	 * @return string what appears in the list table of the subscriptions
	 */
	private static function get_not_found_text() {
		$subscriptions_exist = self::$cache->cache_and_get( 'wcs_do_subscriptions_exist', 'wcs_do_subscriptions_exist' );
		if ( true === apply_filters( 'woocommerce_subscriptions_not_empty', $subscriptions_exist ) ) {
			$not_found_text = __( 'No Subscriptions found', 'woocommerce-subscriptions' );
		} else {
			$not_found_text = '<p>' . __( 'Subscriptions will appear here for you to view and manage once purchased by a customer.', 'woocommerce-subscriptions' ) . '</p>';
			// translators: placeholders are opening and closing link tags
			$not_found_text .= '<p>' . sprintf( __( '%sLearn more about managing subscriptions &raquo;%s', 'woocommerce-subscriptions' ), '<a href="http://docs.woocommerce.com/document/subscriptions/store-manager-guide/#section-3" target="_blank">', '</a>' ) . '</p>';
			// translators: placeholders are opening and closing link tags
			$not_found_text .= '<p>' . sprintf( __( '%sAdd a subscription product &raquo;%s', 'woocommerce-subscriptions' ), '<a href="' . esc_url( WC_Subscriptions_Admin::add_subscription_url() ) . '">', '</a>' ) . '</p>';
		}

		return apply_filters( 'woocommerce_subscriptions_not_found_label', $not_found_text );
	}

	/**
	 * Register our custom post statuses, used for order/subscription status
	 */
	public static function register_post_status() {

		$subscription_statuses = wcs_get_subscription_statuses();

		$registered_statuses = apply_filters( 'woocommerce_subscriptions_registered_statuses', array(
			'wc-active'         => _nx_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'post status label including post count', 'woocommerce-subscriptions' ),
			'wc-switched'       => _nx_noop( 'Switched <span class="count">(%s)</span>', 'Switched <span class="count">(%s)</span>', 'post status label including post count', 'woocommerce-subscriptions' ),
			'wc-expired'        => _nx_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'post status label including post count', 'woocommerce-subscriptions' ),
			'wc-pending-cancel' => _nx_noop( 'Pending Cancellation <span class="count">(%s)</span>', 'Pending Cancellation <span class="count">(%s)</span>', 'post status label including post count', 'woocommerce-subscriptions' ),
		) );

		if ( is_array( $subscription_statuses ) && is_array( $registered_statuses ) ) {

			foreach ( $registered_statuses as $status => $label_count ) {

				register_post_status( $status, array(
					'label'                     => $subscription_statuses[ $status ], // use same label/translations as wcs_get_subscription_statuses()
					'public'                    => false,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => $label_count,
				) );
			}
		}
	}

	/**
	 * Enqueues scripts for frontend
	 *
	 * @since 2.3
	 */
	public static function enqueue_frontend_scripts() {
		$dependencies = array( 'jquery' );

		if ( is_cart() || is_checkout() ) {
			wp_enqueue_script( 'wcs-cart', plugin_dir_url( WC_Subscriptions::$plugin_file ) . 'assets/js/frontend/wcs-cart.js', $dependencies, WC_Subscriptions::$version, true );
		} elseif ( is_product() ) {
			wp_enqueue_script( 'wcs-single-product', plugin_dir_url( WC_Subscriptions::$plugin_file ) . 'assets/js/frontend/single-product.js', $dependencies, WC_Subscriptions::$version, true );
		} elseif ( wcs_is_view_subscription_page() ) {
			global $wp;
			$subscription   = wcs_get_subscription( $wp->query_vars['view-subscription'] );

			if ( $subscription && current_user_can( 'view_order', $subscription->get_id() ) ) {
				$dependencies[] = 'jquery-blockui';
				$script_params  = array(
					'ajax_url'               => esc_url( WC()->ajax_url() ),
					'subscription_id'        => $subscription->get_id(),
					'add_payment_method_msg' => __( 'To enable automatic renewals for this subscription, you will first need to add a payment method.', 'woocommerce-subscriptions' ) . "\n\n" . __( 'Would you like to add a payment method now?', 'woocommerce-subscriptions' ),
					'auto_renew_nonce'       => WCS_My_Account_Auto_Renew_Toggle::can_user_toggle_auto_renewal( $subscription ) ? wp_create_nonce( "toggle-auto-renew-{$subscription->get_id()}" ) : false,
					'add_payment_method_url' => esc_url( $subscription->get_change_payment_method_url() ),
					'has_payment_gateway'    => $subscription->has_payment_gateway() && wc_get_payment_gateway_by_order( $subscription )->supports( 'subscriptions' ),
				);
				wp_enqueue_script( 'wcs-view-subscription', plugin_dir_url( WC_Subscriptions::$plugin_file ) . 'assets/js/frontend/view-subscription.js', $dependencies, WC_Subscriptions::$version, true );
				wp_localize_script( 'wcs-view-subscription', 'WCSViewSubscription', apply_filters( 'woocommerce_subscriptions_frontend_view_subscription_script_parameters', $script_params ) );
			}
		}
	}

	/**
	 * Enqueues stylesheet for the My Subscriptions table on the My Account page.
	 *
	 * @since 1.5
	 */
	public static function enqueue_styles( $styles ) {

		if ( is_checkout() || is_cart() ) {
			$styles['wcs-checkout'] = array(
				'src'     => str_replace( array( 'http:', 'https:' ), '', plugin_dir_url( __FILE__ ) ) . 'assets/css/checkout.css',
				'deps'    => 'wc-checkout',
				'version' => WC_VERSION,
				'media'   => 'all',
			);
		} elseif ( is_account_page() ) {
			$styles['wcs-view-subscription'] = array(
				'src'     => str_replace( array( 'http:', 'https:' ), '', plugin_dir_url( __FILE__ ) ) . 'assets/css/view-subscription.css',
				'deps'    => 'woocommerce-smallscreen',
				'version' => self::$version,
				'media'   => 'all',
			);
		}

		return $styles;
	}

	/**
	 * Loads the my-subscriptions.php template on the My Account page.
	 *
	 * @since 1.0
	 * @param int $current_page
	 */
	public static function get_my_subscriptions_template( $current_page = 1 ) {

		$all_subscriptions  = wcs_get_users_subscriptions();

		$current_page    = empty( $current_page ) ? 1 : absint( $current_page );
		$posts_per_page = get_option( 'posts_per_page' );

		$max_num_pages = ceil( count( $all_subscriptions ) / $posts_per_page );

		$subscriptions = array_slice( $all_subscriptions, ( $current_page - 1 ) * $posts_per_page, $posts_per_page );

		wc_get_template( 'myaccount/my-subscriptions.php', array( 'subscriptions' => $subscriptions, 'current_page' => $current_page, 'max_num_pages' => $max_num_pages, 'paginate' => true ), '', plugin_dir_path( __FILE__ ) . 'templates/' );
	}

	/**
	 * Output a redirect URL when an item is added to the cart when a subscription was already in the cart.
	 *
	 * @since 1.0
	 */
	public static function redirect_ajax_add_to_cart( $fragments ) {

		$fragments['error'] = true;
		$fragments['product_url'] = wc_get_cart_url();

		# Force error on add_to_cart() to redirect
		add_filter( 'woocommerce_add_to_cart_validation', '__return_false', 10 );
		add_filter( 'woocommerce_cart_redirect_after_error', __CLASS__ . '::redirect_to_cart', 10, 2 );
		do_action( 'wc_ajax_add_to_cart' );

		return $fragments;
	}

	/**
	* Return a url for cart redirect.
	*
	* @since 2.3.0
	*/
	public static function redirect_to_cart( $permalink, $product_id ) {

		return wc_get_cart_url();
	}

	/**
	 * When a subscription is added to the cart, remove other products/subscriptions to
	 * work with PayPal Standard, which only accept one subscription per checkout.
	 *
	 * If multiple purchase flag is set, allow them to be added at the same time.
	 *
	 * @deprecated 2.6.0
	 * @since 1.0
	 */
	public static function maybe_empty_cart( $valid, $product_id, $quantity, $variation_id = '', $variations = array() ) {
		wcs_deprecated_function( __METHOD__, '2.6.0', 'WC_Subscriptions_Cart_Validator::maybe_empty_cart()' );

		$is_subscription                 = WC_Subscriptions_Product::is_subscription( $product_id );
		$cart_contains_subscription      = WC_Subscriptions_Cart::cart_contains_subscription();
		$multiple_subscriptions_possible = WC_Subscriptions_Payment_Gateways::one_gateway_supports( 'multiple_subscriptions' );
		$manual_renewals_enabled         = ( 'yes' == get_option( WC_Subscriptions_Admin::$option_prefix . '_accept_manual_renewals', 'no' ) );
		$canonical_product_id            = ! empty( $variation_id ) ? $variation_id : $product_id;

		if ( $is_subscription && 'yes' != get_option( WC_Subscriptions_Admin::$option_prefix . '_multiple_purchase', 'no' ) ) {

			// Generate a cart item key from variation and cart item data - which may be added by other plugins
			$cart_item_data = (array) apply_filters( 'woocommerce_add_cart_item_data', array(), $product_id, $variation_id, $quantity );
			$cart_item_id   = WC()->cart->generate_cart_id( $product_id, $variation_id, $variations, $cart_item_data );
			$product        = wc_get_product( $product_id );

			// If the product is sold individually or if the cart doesn't already contain this product, empty the cart.
			if ( ( $product && $product->is_sold_individually() ) || ! WC()->cart->find_product_in_cart( $cart_item_id ) ) {
				$coupons = WC()->cart->get_applied_coupons();
				WC()->cart->empty_cart();
				WC()->cart->set_applied_coupons( $coupons );
			}
		} elseif ( $is_subscription && wcs_cart_contains_renewal() && ! $multiple_subscriptions_possible && ! $manual_renewals_enabled ) {

			WC_Subscriptions_Cart::remove_subscriptions_from_cart();

			wc_add_notice( __( 'A subscription renewal has been removed from your cart. Multiple subscriptions can not be purchased at the same time.', 'woocommerce-subscriptions' ), 'notice' );

		} elseif ( $is_subscription && $cart_contains_subscription && ! $multiple_subscriptions_possible && ! $manual_renewals_enabled && ! WC_Subscriptions_Cart::cart_contains_product( $canonical_product_id ) ) {

			WC_Subscriptions_Cart::remove_subscriptions_from_cart();

			wc_add_notice( __( 'A subscription has been removed from your cart. Due to payment gateway restrictions, different subscription products can not be purchased at the same time.', 'woocommerce-subscriptions' ), 'notice' );

		} elseif ( $cart_contains_subscription && 'yes' != get_option( WC_Subscriptions_Admin::$option_prefix . '_multiple_purchase', 'no' ) ) {

			WC_Subscriptions_Cart::remove_subscriptions_from_cart();

			wc_add_notice( __( 'A subscription has been removed from your cart. Products and subscriptions can not be purchased at the same time.', 'woocommerce-subscriptions' ), 'notice' );

			// Redirect to cart page to remove subscription & notify shopper
			if ( self::is_woocommerce_pre( '3.0.8' ) ) {
				add_filter( 'add_to_cart_fragments', __CLASS__ . '::redirect_ajax_add_to_cart' );
			} else {
				add_filter( 'woocommerce_add_to_cart_fragments', __CLASS__ . '::redirect_ajax_add_to_cart' );
			}
		}

		return WC_Subscriptions_Cart_Validator::maybe_empty_cart( $valid, $product_id, $quantity, $variation_id, $variations );
	}

	/**
	 * Removes all subscription products from the shopping cart.
	 *
	 * @deprecated 2.6.0
	 * @since 1.0
	 */
	public static function remove_subscriptions_from_cart() {
		wcs_deprecated_function( __METHOD__, '2.6.0', 'WC_Subscriptions_Cart::remove_subscriptions_from_cart()' );

		WC_Subscriptions_Cart::remove_subscriptions_from_cart();
	}

	/**
	 * For a smoother sign up process, tell WooCommerce to redirect the shopper immediately to
	 * the checkout page after she clicks the "Sign up now" button
	 *
	 * Only enabled if multiple checkout is not enabled.
	 *
	 * @param string $url The cart redirect $url WooCommerce determined.
	 * @since 1.0
	 */
	public static function add_to_cart_redirect( $url ) {

		// If product is of the subscription type
		if ( isset( $_REQUEST['add-to-cart'] ) && is_numeric( $_REQUEST['add-to-cart'] ) && WC_Subscriptions_Product::is_subscription( (int) $_REQUEST['add-to-cart'] ) ) {

			// Redirect to checkout if mixed checkout is disabled
			if ( 'yes' != get_option( WC_Subscriptions_Admin::$option_prefix . '_multiple_purchase', 'no' ) ) {

				$quantity   = isset( $_REQUEST['quantity'] ) ? $_REQUEST['quantity'] : 1;
				$product_id = $_REQUEST['add-to-cart'];

				$add_to_cart_notice = wc_add_to_cart_message( array( $product_id => $quantity ), true, true );

				if ( wc_has_notice( $add_to_cart_notice ) ) {
					$notices                  = wc_get_notices();
					$add_to_cart_notice_index = array_search( $add_to_cart_notice, $notices['success'] );

					unset( $notices['success'][ $add_to_cart_notice_index ] );
					wc_set_notices( $notices );
				}

				$url = wc_get_checkout_url();
			}
		}

		return $url;
	}

	/**
	 * Override the WooCommerce "Place order" text with "Sign up now"
	 *
	 * @since 1.0
	 */
	public static function order_button_text( $button_text ) {
		global $product;

		if ( WC_Subscriptions_Cart::cart_contains_subscription() ) {
			$button_text = get_option( WC_Subscriptions_Admin::$option_prefix . '_order_button_text', __( 'Sign up now', 'woocommerce-subscriptions' ) );
		}

		return $button_text;
	}

	/**
	 * Load the subscription add_to_cart template.
	 *
	 * Use the same cart template for subscription as that which is used for simple products. Reduce code duplication
	 * and is made possible by the friendly actions & filters found through WC.
	 *
	 * Not using a custom template both prevents code duplication and helps future proof this extension from core changes.
	 *
	 * @since 1.0
	 */
	public static function subscription_add_to_cart() {
		wc_get_template( 'single-product/add-to-cart/subscription.php', array(), '', plugin_dir_path( __FILE__ ) . 'templates/' );
	}

	/**
	 * Load the variable subscription add_to_cart template
	 *
	 * Use a very similar cart template as that of a variable product with added functionality.
	 *
	 * @since 2.0.9
	 */
	public static function variable_subscription_add_to_cart() {
		global $product;

		// Enqueue variation scripts
		wp_enqueue_script( 'wc-add-to-cart-variation' );

		// Get Available variations?
		$get_variations = sizeof( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );

		// Load the template
		wc_get_template( 'single-product/add-to-cart/variable-subscription.php', array(
			'available_variations' => $get_variations ? $product->get_available_variations() : false,
			'attributes'           => $product->get_variation_attributes(),
			'selected_attributes'  => $product->get_default_attributes(),
		), '', plugin_dir_path( __FILE__ ) . 'templates/' );
	}

	/**
	 * Compatibility with WooCommerce On One Page Checkout.
	 *
	 * Use OPC's simple add to cart template for simple subscription products (to ensure data attributes required by OPC are added).
	 *
	 * Variable subscription products will be handled automatically because they identify as "variable" in response to is_type() method calls,
	 * which OPC uses.
	 *
	 * @since 1.5.16
	 */
	public static function wcopc_subscription_add_to_cart() {
		global $product;
		wc_get_template( 'checkout/add-to-cart/simple.php', array( 'product' => $product ), '', PP_One_Page_Checkout::$template_path );
	}

	/**
	 * Takes a number and returns the number with its relevant suffix appended, eg. for 2, the function returns 2nd
	 *
	 * @since 1.0
	 */
	public static function append_numeral_suffix( $number ) {

		// Handle teens: if the tens digit of a number is 1, then write "th" after the number. For example: 11th, 13th, 19th, 112th, 9311th. http://en.wikipedia.org/wiki/English_numerals
		if ( strlen( $number ) > 1 && 1 == substr( $number, -2, 1 ) ) {
			// translators: placeholder is a number, this is for the teens
			$number_string = sprintf( __( '%sth', 'woocommerce-subscriptions' ), $number );
		} else { // Append relevant suffix
			switch ( substr( $number, -1 ) ) {
				case 1:
					// translators: placeholder is a number, numbers ending in 1
					$number_string = sprintf( __( '%sst', 'woocommerce-subscriptions' ), $number );
					break;
				case 2:
					// translators: placeholder is a number, numbers ending in 2
					$number_string = sprintf( __( '%snd', 'woocommerce-subscriptions' ), $number );
					break;
				case 3:
					// translators: placeholder is a number, numbers ending in 3
					$number_string = sprintf( __( '%srd', 'woocommerce-subscriptions' ), $number );
					break;
				default:
					// translators: placeholder is a number, numbers ending in 4-9, 0
					$number_string = sprintf( __( '%sth', 'woocommerce-subscriptions' ), $number );
					break;
			}
		}

		return apply_filters( 'woocommerce_numeral_suffix', $number_string, $number );
	}


	/*
	 * Plugin House Keeping
	 */

	/**
	 * Called when WooCommerce is inactive or running and out-of-date version to display an inactive notice.
	 *
	 * @since 1.2
	 */
	public static function woocommerce_inactive_notice() {
		if ( current_user_can( 'activate_plugins' ) ) {
			$admin_notice_content = '';

			if ( ! is_woocommerce_active() ) {
				$install_url = wp_nonce_url( add_query_arg( array( 'action' => 'install-plugin', 'plugin' => 'woocommerce' ), admin_url( 'update.php' ) ), 'install-plugin_woocommerce' );

				// translators: 1$-2$: opening and closing <strong> tags, 3$-4$: link tags, takes to woocommerce plugin on wp.org, 5$-6$: opening and closing link tags, leads to plugins.php in admin
				$admin_notice_content = sprintf( esc_html__( '%1$sWooCommerce Subscriptions is inactive.%2$s The %3$sWooCommerce plugin%4$s must be active for WooCommerce Subscriptions to work. Please %5$sinstall & activate WooCommerce &raquo;%6$s',  'woocommerce-subscriptions' ), '<strong>', '</strong>', '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>', '<a href="' .  esc_url( $install_url ) . '">', '</a>' );
			} elseif ( version_compare( get_option( 'woocommerce_db_version' ), self::$wc_minimum_supported_version, '<' ) ) {
				// translators: 1$-2$: opening and closing <strong> tags, 3$: minimum supported WooCommerce version, 4$-5$: opening and closing link tags, leads to plugin admin
				$admin_notice_content = sprintf( esc_html__( '%1$sWooCommerce Subscriptions is inactive.%2$s This version of Subscriptions requires WooCommerce %3$s or newer. Please %4$supdate WooCommerce to version %3$s or newer &raquo;%5$s', 'woocommerce-subscriptions' ), '<strong>', '</strong>', self::$wc_minimum_supported_version,'<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">', '</a>' );
			}

			if ( $admin_notice_content ) {
				require_once( dirname( __FILE__ ) . '/includes/admin/class-wcs-admin-notice.php' );
				$notice = new WCS_Admin_Notice( 'error' );
				$notice->set_simple_content( $admin_notice_content );
				$notice->display();
			}
		}
	}

	/**
	 * Checks on each admin page load if Subscriptions plugin is activated.
	 *
	 * Apparently the official WP API is "lame" and it's far better to use an upgrade routine fired on admin_init: http://core.trac.wordpress.org/ticket/14170
	 *
	 * @since 1.1
	 */
	public static function maybe_activate_woocommerce_subscriptions() {
		$is_active = get_option( WC_Subscriptions_Admin::$option_prefix . '_is_active', false );

		if ( false == $is_active ) {

			// Add the "Subscriptions" product type
			if ( ! get_term_by( 'slug', self::$name, 'product_type' ) ) {
				wp_insert_term( self::$name, 'product_type' );
			}

			// Maybe add the "Variable Subscriptions" product type
			if ( ! get_term_by( 'slug', 'variable-subscription', 'product_type' ) ) {
				wp_insert_term( __( 'Variable Subscription', 'woocommerce-subscriptions' ), 'product_type' );
			}

			// If no Subscription settings exist, its the first activation, so add defaults
			if ( get_option( WC_Subscriptions_Admin::$option_prefix . '_cancelled_role', false ) == false ) {
				WC_Subscriptions_Admin::add_default_settings();
			}

			// if this is the first time activating WooCommerce Subscription we want to enable PayPal debugging by default.
			if ( '0' == get_option( WC_Subscriptions_Admin::$option_prefix . '_previous_version', '0' ) && false == get_option( WC_Subscriptions_admin::$option_prefix . '_paypal_debugging_default_set', false ) ) {
				$paypal_settings          = get_option( 'woocommerce_paypal_settings' );
				$paypal_settings['debug'] = 'yes';
				update_option( 'woocommerce_paypal_settings', $paypal_settings );
				update_option( WC_Subscriptions_admin::$option_prefix . '_paypal_debugging_default_set', 'true' );
			}

			update_option( WC_Subscriptions_Admin::$option_prefix . '_is_active', true );

			set_transient( self::$activation_transient, true, 60 * 60 );

			flush_rewrite_rules();

			do_action( 'woocommerce_subscriptions_activated' );
		}

	}

	/**
	 * Called when the plugin is deactivated. Deletes the subscription product type and fires an action.
	 *
	 * @since 1.0
	 */
	public static function deactivate_woocommerce_subscriptions() {

		delete_option( WC_Subscriptions_Admin::$option_prefix . '_is_active' );

		flush_rewrite_rules();

		do_action( 'woocommerce_subscriptions_deactivated' );
	}

	/**
	 * Called on plugins_loaded to load any translation files.
	 *
	 * @since 1.1
	 */
	public static function load_plugin_textdomain() {

		$plugin_rel_path = apply_filters( 'woocommerce_subscriptions_translation_file_rel_path', dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		// Then check for a language file in /wp-content/plugins/woocommerce-subscriptions/languages/ (this will be overriden by any file already loaded)
		load_plugin_textdomain( 'woocommerce-subscriptions', false, $plugin_rel_path );
	}

	/**
	 * Loads classes that depend on WooCommerce base classes.
	 *
	 * @since 1.2.4
	 */
	public static function load_dependant_classes() {
		new WCS_Admin_Post_Types();
		new WCS_Admin_Meta_Boxes();
		new WCS_Admin_Reports();
		new WCS_Report_Cache_Manager();
		WCS_Webhooks::init();
		new WCS_Auth();
		WCS_API::init();
		WCS_Template_Loader::init();
		new WCS_Query();
		WCS_Remove_Item::init();
		WCS_User_Change_Status_Handler::init();
		WCS_My_Account_Payment_Methods::init();
		WCS_My_Account_Auto_Renew_Toggle::init();

		if ( self::is_woocommerce_pre( '3.0' ) ) {
			WCS_Product_Legacy::init();

			// Load WC_DateTime when it doesn't exist yet so we can use it for datetime handling consistently with WC 3.0+
			if ( ! class_exists( 'WC_DateTime' ) ) {
				require_once( dirname( __FILE__ ) . '/includes/libraries/class-wc-datetime.php' );
			}
		} else {
			new WCS_Deprecated_Filter_Hooks();
		}

		// Provide a hook to enable running deprecation handling for stores that might want to check for deprecated code
		if ( apply_filters( 'woocommerce_subscriptions_load_deprecation_handlers', false ) ) {
			new WCS_Action_Deprecator();
			new WCS_Filter_Deprecator();
			new WCS_Dynamic_Action_Deprecator();
			new WCS_Dynamic_Filter_Deprecator();
		}

		if ( class_exists( 'WCS_Early_Renewal' ) ) {
			$notice = new WCS_Admin_Notice( 'error' );

			$notice->set_simple_content( sprintf( __( '%1$sWarning!%2$s We can see the %1$sWooCommerce Subscriptions Early Renewal%2$s plugin is active. Version %3$s of %1$sWooCommerce Subscriptions%2$s comes with that plugin\'s functionality packaged into the core plugin. Please deactivate WooCommerce Subscriptions Early Renewal to avoid any conflicts.', 'woocommerce-subscriptions' ), '<b>', '</b>', self::$version ) );
			$notice->set_actions( array(
				array(
					'name' => __( 'Installed Plugins', 'woocommerce-subscriptions' ),
					'url'  => admin_url( 'plugins.php' ),
				),
			) );

			$notice->display();
		} else {
			WCS_Early_Renewal_Manager::init();
			require_once( dirname( __FILE__ ) . '/includes/early-renewal/wcs-early-renewal-functions.php' );
			if ( WCS_Early_Renewal_Manager::is_early_renewal_enabled() ) {
				new WCS_Cart_Early_Renewal();
			}
		}

		$failed_scheduled_action_manager = new WCS_Failed_Scheduled_Action_Manager( new WC_Logger() );
		$failed_scheduled_action_manager->init();

		if ( class_exists( 'WC_Abstract_Privacy' ) ) {
			new WCS_Privacy();
		}
	}

	/**
	 * Some hooks need to check for the version of WooCommerce, which we can only do after WooCommerce is loaded.
	 *
	 * @since 1.5.17
	 */
	public static function attach_dependant_hooks() {

		// Redirect the user immediately to the checkout page after clicking "Sign Up Now" buttons to encourage immediate checkout
		add_filter( 'woocommerce_add_to_cart_redirect', __CLASS__ . '::add_to_cart_redirect' );

		if ( self::is_woocommerce_pre( '2.6' ) ) {
			// Display Subscriptions on a User's account page
			add_action( 'woocommerce_before_my_account', __CLASS__ . '::get_my_subscriptions_template' );
		}

		// Ensure the autoloader knows which API to use.
		self::$autoloader->use_legacy_api( WC_Subscriptions::is_woocommerce_pre( '3.0' ) );
	}

	/**
	 * Displays a notice when Subscriptions is being run on a different site, like a staging or testing site.
	 *
	 * @since 1.3.8
	 */
	public static function woocommerce_site_change_notice() {

		if ( self::is_duplicate_site() && current_user_can( 'manage_options' ) ) {

			if ( ! empty( $_REQUEST['_wcsnonce'] ) && wp_verify_nonce( $_REQUEST['_wcsnonce'], 'wcs_duplicate_site' ) && isset( $_GET['wc_subscription_duplicate_site'] ) ) {

				if ( 'update' === $_GET['wc_subscription_duplicate_site'] ) {

					WC_Subscriptions::set_duplicate_site_url_lock();

				} elseif ( 'ignore' === $_GET['wc_subscription_duplicate_site'] ) {

					update_option( 'wcs_ignore_duplicate_siteurl_notice', self::get_current_sites_duplicate_lock() );

				}

				wp_safe_redirect( remove_query_arg( array( 'wc_subscription_duplicate_site', '_wcsnonce' ) ) );

			} elseif ( self::get_current_sites_duplicate_lock() !== get_option( 'wcs_ignore_duplicate_siteurl_notice' ) ) {
				$notice = new WCS_Admin_Notice( 'error' );
				$notice->set_simple_content(
					sprintf(
						// translators: 1$-2$: opening and closing <strong> tags. 3$-4$: opening and closing link tags for learn more. Leads to duplicate site article on docs. 5$-6$: Opening and closing link to production URL. 7$: Production URL .
						esc_html__( 'It looks like this site has moved or is a duplicate site. %1$sWooCommerce Subscriptions%2$s has disabled automatic payments and subscription related emails on this site to prevent duplicate payments from a staging or test environment. %1$sWooCommerce Subscriptions%2$s considers %5$s%7$s%6$s to be the site\'s URL. %3$sLearn more &raquo;%4$s.', 'woocommerce-subscriptions' ),
						'<strong>', '</strong>',
						'<a href="https://docs.woocommerce.com/document/subscriptions-handles-staging-sites/" target="_blank">', '</a>',
						'<a href="' . esc_url( self::get_site_url_from_source( 'subscriptions_install' ) ) . '" target="_blank">', '</a>',
						esc_url( self::get_site_url_from_source( 'subscriptions_install' ) )
					)
				);
				$notice->set_actions( array(
					array(
						'name'  => __( 'Quit nagging me (but don\'t enable automatic payments)', 'woocommerce-subscriptions' ),
						'url'   => wp_nonce_url( add_query_arg( 'wc_subscription_duplicate_site', 'ignore' ), 'wcs_duplicate_site', '_wcsnonce' ),
						'class' => 'button button-primary',
					),
					array(
						'name'  => __( 'Enable automatic payments', 'woocommerce-subscriptions' ),
						'url'   => wp_nonce_url( add_query_arg( 'wc_subscription_duplicate_site', 'update' ), 'wcs_duplicate_site', '_wcsnonce' ),
						'class' => 'button',
					),
				) );

				$notice->display();
			}
		}
	}

	/**
	 * A general purpose function for grabbing an array of subscriptions in form of 'subscription_key' => 'subscription_details'.
	 *
	 * The $args param is based on the parameter of the same name used by the core WordPress @see get_posts() function.
	 * It can be used to choose which subscriptions should be returned by the function, how many subscriptions should be returned
	 * and in what order those subscriptions should be returned.
	 *
	 * @param array $args A set of name value pairs to determine the return value.
	 *		'subscriptions_per_page' The number of subscriptions to return. Set to -1 for unlimited. Default 10.
	 *		'offset' An optional number of subscription to displace or pass over. Default 0.
	 *		'orderby' The field which the subscriptions should be ordered by. Can be 'start_date', 'expiry_date', 'end_date', 'status', 'name' or 'order_id'. Defaults to 'start_date'.
	 *		'order' The order of the values returned. Can be 'ASC' or 'DESC'. Defaults to 'DESC'
	 *		'customer_id' The user ID of a customer on the site.
	 *		'product_id' The post ID of a WC_Product_Subscription, WC_Product_Variable_Subscription or WC_Product_Subscription_Variation object
	 *		'subscription_status' Any valid subscription status. Can be 'any', 'active', 'cancelled', 'suspended', 'expired', 'pending' or 'trash'. Defaults to 'any'.
	 * @return array Subscription details in 'subscription_key' => 'subscription_details' form.
	 * @since 1.4
	 */
	public static function get_subscriptions( $args = array() ) {

		if ( isset( $args['orderby'] ) ) {
			// Although most of these weren't public orderby values, they were used internally so may have been used by developers
			switch ( $args['orderby'] ) {
				case '_subscription_status' :
					_deprecated_argument( __METHOD__, '2.0', 'The "_subscription_status" orderby value is deprecated. Use "status" instead.' );
					$args['orderby'] = 'status';
					break;
				case '_subscription_start_date' :
					_deprecated_argument( __METHOD__, '2.0', 'The "_subscription_start_date" orderby value is deprecated. Use "start_date" instead.' );
					$args['orderby'] = 'start_date';
					break;
				case 'expiry_date' :
				case '_subscription_expiry_date' :
				case '_subscription_end_date' :
					_deprecated_argument( __METHOD__, '2.0', 'The expiry date orderby value is deprecated. Use "end_date" instead.' );
					$args['orderby'] = 'end_date';
					break;
				case 'trial_expiry_date' :
				case '_subscription_trial_expiry_date' :
					_deprecated_argument( __METHOD__, '2.0', 'The trial expiry date orderby value is deprecated. Use "trial_end_date" instead.' );
					$args['orderby'] = 'trial_end_date';
					break;
				case 'name' :
					_deprecated_argument( __METHOD__, '2.0', 'The "name" orderby value is deprecated - subscriptions no longer have just one name as they may contain multiple items.' );
					break;
			}
		}

		_deprecated_function( __METHOD__, '2.0', 'wcs_get_subscriptions( $args )' );

		$subscriptions = wcs_get_subscriptions( $args );

		$subscriptions_in_deprecated_structure = array();

		// Get the subscriptions in the backward compatible structure
		foreach ( $subscriptions as $subscription ) {
			$subscriptions_in_deprecated_structure[ wcs_get_old_subscription_key( $subscription ) ] = wcs_get_subscription_in_deprecated_structure( $subscription );
		}

		return apply_filters( 'woocommerce_get_subscriptions', $subscriptions_in_deprecated_structure, $args );
	}

	/**
	 * Returns the longest possible time period
	 *
	 * @since 1.3
	 */
	public static function get_longest_period( $current_period, $new_period ) {

		if ( empty( $current_period ) || 'year' == $new_period ) {
			$longest_period = $new_period;
		} elseif ( 'month' === $new_period && in_array( $current_period, array( 'week', 'day' ) ) ) {
			$longest_period = $new_period;
		} elseif ( 'week' === $new_period && 'day' === $current_period ) {
			$longest_period = $new_period;
		} else {
			$longest_period = $current_period;
		}

		return $longest_period;
	}

	/**
	 * Returns the shortest possible time period
	 *
	 * @since 1.3.7
	 */
	public static function get_shortest_period( $current_period, $new_period ) {

		if ( empty( $current_period ) || 'day' == $new_period ) {
			$shortest_period = $new_period;
		} elseif ( 'week' === $new_period && in_array( $current_period, array( 'month', 'year' ) ) ) {
			$shortest_period = $new_period;
		} elseif ( 'month' === $new_period && 'year' === $current_period ) {
			$shortest_period = $new_period;
		} else {
			$shortest_period = $current_period;
		}

		return $shortest_period;
	}


	/**
	 * Returns WordPress/Subscriptions record of the site URL for this site
	 *
	 * @param string $source Takes values 'current_wp_site' or 'subscriptions_install'
	 * @since 2.3.6
	 */
	public static function get_site_url_from_source( $source = 'current_wp_site' ) {
		// Let the default source be WP
		if ( 'subscriptions_install' === $source ) {
			$site_url = self::get_site_url();
		} elseif ( ! is_multisite() && defined( 'WP_SITEURL' ) ) {
			$site_url = WP_SITEURL;
		} else {
			$site_url = get_site_url();
		}

		return $site_url;
	}

	/**
	 * Returns Subscriptions record of the site URL for this site
	 *
	 * @since 1.3.8
	 */
	public static function get_site_url( $blog_id = null, $path = '', $scheme = null ) {
		if ( empty( $blog_id ) || ! is_multisite() ) {
			$url = get_option( 'wc_subscriptions_siteurl' );
		} else {
			switch_to_blog( $blog_id );
			$url = get_option( 'wc_subscriptions_siteurl' );
			restore_current_blog();
		}

		// Remove the prefix used to prevent the site URL being updated on WP Engine
		$url = str_replace( '_[wc_subscriptions_siteurl]_', '', $url );

		$url = set_url_scheme( $url, $scheme );

		if ( ! empty( $path ) && is_string( $path ) && strpos( $path, '..' ) === false ) {
			$url .= '/' . ltrim( $path, '/' );
		}

		return apply_filters( 'wc_subscriptions_site_url', $url, $path, $scheme, $blog_id );
	}

	/**
	 * Checks if the WordPress site URL is the same as the URL for the site subscriptions normally
	 * runs on. Useful for checking if automatic payments should be processed.
	 *
	 * @since 1.3.8
	 */
	public static function is_duplicate_site() {

		$wp_site_url_parts  = wp_parse_url( self::get_site_url_from_source( 'current_wp_site' ) );
		$wcs_site_url_parts = wp_parse_url( self::get_site_url_from_source( 'subscriptions_install' ) );

		if ( ! isset( $wp_site_url_parts['path'] ) && ! isset( $wcs_site_url_parts['path'] ) ) {
			$paths_match = true;
		} elseif ( isset( $wp_site_url_parts['path'] ) && isset( $wcs_site_url_parts['path'] ) && $wp_site_url_parts['path'] == $wcs_site_url_parts['path'] ) {
			$paths_match = true;
		} else {
			$paths_match = false;
		}

		if ( isset( $wp_site_url_parts['host'] ) && isset( $wcs_site_url_parts['host'] ) && $wp_site_url_parts['host'] == $wcs_site_url_parts['host'] ) {
			$hosts_match = true;
		} else {
			$hosts_match = false;
		}

		// Check the host and path, do not check the protocol/scheme to avoid issues with WP Engine and other occasions where the WP_SITEURL constant may be set, but being overridden (e.g. by FORCE_SSL_ADMIN)
		if ( $paths_match && $hosts_match ) {
			$is_duplicate = false;
		} else {
			$is_duplicate = true;
		}

		return apply_filters( 'woocommerce_subscriptions_is_duplicate_site', $is_duplicate );
	}


	/**
	 * Include Docs & Settings links on the Plugins administration screen
	 *
	 * @param mixed $links
	 * @since 1.4
	 */
	public static function action_links( $links ) {

		$plugin_links = array(
			'<a href="' . WC_Subscriptions_Admin::settings_tab_url() . '">' . __( 'Settings', 'woocommerce-subscriptions' ) . '</a>',
			'<a href="http://docs.woocommerce.com/document/subscriptions/">' . _x( 'Docs', 'short for documents', 'woocommerce-subscriptions' ) . '</a>',
			'<a href="https://woocommerce.com/my-account/marketplace-ticket-form/">' . __( 'Support', 'woocommerce-subscriptions' ) . '</a>',
		);

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Creates a URL to prevent duplicate payments from staging sites.
	 *
	 * The URL can not simply be the site URL, e.g. http://example.com, because WP Engine replaces all
	 * instances of the site URL in the database when creating a staging site. As a result, we obfuscate
	 * the URL by inserting '_[wc_subscriptions_siteurl]_' into the middle of it.
	 *
	 * We don't use a hash because keeping the URL in the value allows for viewing and editing the URL
	 * directly in the database.
	 *
	 * @since 1.4.2
	 * @return string The duplicate lock URL.
	 */
	public static function get_current_sites_duplicate_lock() {
		$site_url = self::get_site_url_from_source( 'current_wp_site' );
		$scheme   = parse_url( $site_url, PHP_URL_SCHEME ) . '://';
		$site_url = str_replace( $scheme, '', $site_url );

		return $scheme . substr_replace( $site_url, '_[wc_subscriptions_siteurl]_', strlen( $site_url ) / 2, 0 );
	}

	/**
	 * Sets a flag in the database to record the site's url. This then checked to determine if we are on a duplicate
	 * site or the original/main site, uses @see self::get_current_sites_duplicate_lock();
	 *
	 * @since 1.4.2
	 */
	public static function set_duplicate_site_url_lock() {
		update_option( 'wc_subscriptions_siteurl', self::get_current_sites_duplicate_lock() );
	}

	/**
	 * Check if the installed version of WooCommerce is older than a specified version.
	 *
	 * @since 1.5.29
	 */
	public static function is_woocommerce_pre( $version ) {

		if ( ! defined( 'WC_VERSION' ) || version_compare( WC_VERSION, $version, '<' ) ) {
			$woocommerce_is_pre_version = true;
		} else {
			$woocommerce_is_pre_version = false;
		}

		return $woocommerce_is_pre_version;
	}

	/**
	 * Renewals use a lot more memory on WordPress multisite (10-15mb instead of 0.1-1mb) so
	 * we need to reduce the number of renewals run in each request.
	 *
	 * @since version 1.5
	 */
	public static function action_scheduler_multisite_batch_size( $batch_size ) {

		if ( is_multisite() ) {
			$batch_size = 10;
		}

		return $batch_size;
	}

	/**
	 * Include the upgrade notice that will fire when 2.0 is released.
	 *
	 * @param array $plugin_data information about the plugin
	 * @param array $r response from the server about the new version
	 */
	public static function update_notice( $plugin_data, $r ) {

		// Bail if the update notice is not relevant (new version is not yet 2.0 or we're already on 2.0)
		if ( version_compare( '2.0.0', $plugin_data['new_version'], '>' ) || version_compare( '2.0.0', $plugin_data['Version'], '<=' ) ) {
			return;
		}

		$update_notice = '<div class="wc_plugin_upgrade_notice">';
		// translators: placeholders are opening and closing tags. Leads to docs on version 2
		$update_notice .= sprintf( __( 'Warning! Version 2.0 is a major update to the WooCommerce Subscriptions extension. Before updating, please create a backup, update all WooCommerce extensions and test all plugins, custom code and payment gateways with version 2.0 on a staging site. %sLearn more about the changes in version 2.0 &raquo;%s', 'woocommerce-subscriptions' ), '<a href="http://docs.woocommerce.com/document/subscriptions/version-2/">', '</a>' );
		$update_notice .= '</div> ';

		echo wp_kses_post( $update_notice );
	}

	/**
	 * Send notice to store admins if they have previously updated Subscriptions to 2.0 and back to v1.5.n.
	 *
	 * @since 2.0
	 */
	public static function show_downgrade_notice() {
		if ( version_compare( get_option( WC_Subscriptions_Admin::$option_prefix . '_active_version', '0' ), self::$version, '>' ) ) {

			echo '<div class="update-nag">';
			echo sprintf( esc_html__( 'Warning! You are running version %s of WooCommerce Subscriptions plugin code but your database has been upgraded to Subscriptions version 2.0. This will cause major problems on your store.', 'woocommerce-subscriptions' ), esc_html( self::$version ) ) . '<br />';
			echo sprintf( esc_html__( 'Please upgrade the WooCommerce Subscriptions plugin to version 2.0 or newer immediately. If you need assistance, after upgrading to Subscriptions v2.0, please %sopen a support ticket%s.', 'woocommerce-subscriptions' ), '<a href="https://woocommerce.com/my-account/marketplace-ticket-form/">', '</a>' );
			echo '</div> ';

		}
	}

	/* Deprecated Functions */

	/**
	 * Gets a WC_Product using the new core WC @see wc_get_product() function if available, otherwise
	 * instantiating an instance of the WC_Product class.
	 *
	 * @since 1.2.4
	 * @deprecated 2.4.0
	 */
	public static function get_product( $product_id ) {
		_deprecated_function( __METHOD__, '2.4.0', 'wc_get_product()' );
		return wc_get_product( $product_id );
	}

	/**
	 * Add WooCommerce error or success notice regardless of the version of WooCommerce running.
	 *
	 * @param  string $message The text to display in the notice.
	 * @param  string $notice_type The singular name of the notice type - either error, success or notice. [optional]
	 * @since version 1.4.5
	 * @deprecated 2.2.16
	 */
	public static function add_notice( $message, $notice_type = 'success' ) {
		wcs_deprecated_function( __METHOD__, '2.2.16', 'wc_add_notice( $message, $notice_type )' );
		wc_add_notice( $message, $notice_type );
	}

	/**
	 * Print WooCommerce messages regardless of the version of WooCommerce running.
	 *
	 * @since version 1.4.5
	 * @deprecated 2.2.16
	 */
	public static function print_notices() {
		wcs_deprecated_function( __METHOD__, '2.2.16', 'wc_print_notices()' );
		wc_print_notices();
	}

	/**
	 * Workaround the last day of month quirk in PHP's strtotime function.
	 *
	 * @since 1.2.5
	 * @deprecated 2.0
	 */
	public static function add_months( $from_timestamp, $months_to_add ) {
		_deprecated_function( __METHOD__, '2.0', 'wcs_add_months()' );
		return wcs_add_months( $from_timestamp, $months_to_add );
	}

	/**
	 * A flag to indicate whether the current site has roughly more than 3000 subscriptions. Used to disable
	 * features on the Manage Subscriptions list table that do not scale well (yet).
	 *
	 * Deprecated since querying the new subscription post type is a lot more efficient and no longer puts strain on the database
	 *
	 * @since 1.4.4
	 * @deprecated 2.0
	 */
	public static function is_large_site() {
		_deprecated_function( __METHOD__, '2.0' );
		return apply_filters( 'woocommerce_subscriptions_is_large_site', false );
	}

	/**
	 * Returns the total number of Subscriptions on the site.
	 *
	 * @since 1.4
	 * @deprecated 2.0
	 */
	public static function get_total_subscription_count() {
		_deprecated_function( __METHOD__, '2.0' );

		if ( null === self::$total_subscription_count ) {
			self::$total_subscription_count = self::get_subscription_count();
		}

		return apply_filters( 'woocommerce_get_total_subscription_count', self::$total_subscription_count );
	}

	/**
	 * Returns an associative array with the structure 'status' => 'count' for all subscriptions on the site
	 * and includes an "all" status, representing all subscriptions.
	 *
	 * @since 1.4
	 * @deprecated 2.0
	 */
	public static function get_subscription_status_counts() {
		_deprecated_function( __METHOD__, '2.0' );

		$results = wp_count_posts( 'shop_subscription' );
		$count   = array();

		foreach ( $results as $status => $count ) {

			if ( in_array( $status, array_keys( wcs_get_subscription_statuses() ) ) || in_array( $status, array( 'trash', 'draft' ) ) ) {
				$counts[ $status ] = $count;
			}
		}

		// Order with 'all' at the beginning, then alphabetically
		ksort( $counts );
		$counts = array( 'all' => array_sum( $counts ) ) + $counts;

		return apply_filters( 'woocommerce_subscription_status_counts', $counts );
	}

	/**
	 * Takes an array of filter params and returns the number of subscriptions which match those params.
	 *
	 * @since 1.4
	 * @deprecated 2.0
	 */
	public static function get_subscription_count( $args = array() ) {
		_deprecated_function( __METHOD__, '2.0' );

		$args['subscriptions_per_page'] = -1;
		$subscription_count = 0;

		if ( ( ! isset( $args['subscription_status'] ) || in_array( $args['subscription_status'], array( 'all', 'any' ) ) ) && ( isset( $args['include_trashed'] ) && true === $args['include_trashed'] ) ) {

			$args['subscription_status'] = 'trash';
			$subscription_count += count( wcs_get_subscriptions( $args ) );
			$args['subscription_status'] = 'any';
		}

		$subscription_count += count( wcs_get_subscriptions( $args ) );

		return apply_filters( 'woocommerce_get_subscription_count', $subscription_count, $args );
	}

	/**
	 * which was called @see woocommerce_format_total() prior to WooCommerce 2.1.
	 *
	 * Deprecated since we no longer need to support the workaround required for WC versions < 2.1
	 *
	 * @since version 1.4.6
	 * @deprecated 2.0
	 */
	public static function format_total( $number ) {
		_deprecated_function( __METHOD__, '2.0', 'wc_format_decimal()' );
		return wc_format_decimal( $number );
	}

	/**
	 * Displays a notice to upgrade if using less than the ideal version of WooCommerce
	 *
	 * @since 1.3
	 */
	public static function woocommerce_dependancy_notice() {
		_deprecated_function( __METHOD__, '2.1', __CLASS__ . '::woocommerce_inactive_notice()' );
	}
}

WC_Subscriptions::init( $wcs_autoloader );
