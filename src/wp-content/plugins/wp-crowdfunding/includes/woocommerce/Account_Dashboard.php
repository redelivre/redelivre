<?php
namespace WPCF\woocommerce;

defined( 'ABSPATH' ) || exit;

class Account_Dashboard {

    public function __construct(){            
        add_action( 'init',                                                 array( $this, 'endpoints') );
        add_filter( 'query_vars',                                           array( $this, 'query_vars'), 0 );
        add_filter( 'woocommerce_account_menu_items',                       array( $this, 'menu_items') );
        add_action( 'woocommerce_account_crowdfunding-dashboard_endpoint',  array( $this, 'dashboard_callback' ) );
        add_action( 'woocommerce_account_profile_endpoint',                 array( $this, 'profile_callback') );
        add_action( 'woocommerce_account_my-campaigns_endpoint',            array( $this, 'campaigns_callback') );
        add_action( 'woocommerce_account_backed-campaigns_endpoint',        array( $this, 'backed_campaigns_callback') );
        add_action( 'woocommerce_account_pledges-received_endpoint',        array( $this, 'pledges_received_callback') );
        add_action( 'woocommerce_account_bookmarks_endpoint',               array( $this, 'bookmarks_callback') );
    }


    // Rewrite Rules For Woocommerce My Account Page
    public function endpoints() {
        add_rewrite_endpoint( 'crowdfunding-dashboard', EP_ROOT | EP_PAGES );
        add_rewrite_endpoint( 'profile', EP_ROOT | EP_PAGES );
        add_rewrite_endpoint( 'my-campaigns', EP_ROOT | EP_PAGES );
        add_rewrite_endpoint( 'backed-campaigns', EP_ROOT | EP_PAGES );
        add_rewrite_endpoint( 'pledges-received', EP_ROOT | EP_PAGES );
        add_rewrite_endpoint( 'bookmarks', EP_ROOT | EP_PAGES );
    }

    // Query Variable
    public function query_vars( $vars ) {
        $vars[] = 'crowdfunding-dashboard';
        $vars[] = 'profile';
        $vars[] = 'my-campaigns';
        $vars[] = 'backed-campaigns';
        $vars[] = 'pledges-received';
        $vars[] = 'bookmarks';
        return $vars;
    }

    // Woocommerce Menu Items
    public function menu_items( $items ) {
        $new_items = array(
            'crowdfunding-dashboard'=> __( 'Crowdfunding Dashboard', 'wp-crowdfunding' ),
            'profile'               => __( 'Profile', 'wp-crowdfunding' ),
            'my-campaigns'          => __( 'My Campaigns', 'wp-crowdfunding' ),
            'backed-campaigns'      => __( 'Backed Campaigns', 'wp-crowdfunding' ),
            'pledges-received'      => __( 'Pledges Received', 'wp-crowdfunding' ),
            'bookmarks'             => __( 'Bookmarks', 'wp-crowdfunding' ),
        );
        $items = array_merge( $new_items,$items );
        return $items;
    }


    // Crowdfunding Dashboard
    public function dashboard_callback() {
        $html = '';
        require_once WPCF_DIR_PATH.'includes/woocommerce/dashboard/dashboard.php';
        echo $html;
    }

    // Profile
    public function profile_callback() {
        $html = '';
        require_once WPCF_DIR_PATH.'includes/woocommerce/dashboard/profile.php';
        echo $html;
    }

    // My Profile
    public function campaigns_callback() {
        $html = '';
        require_once WPCF_DIR_PATH.'includes/woocommerce/dashboard/campaign.php';
        echo $html;
    }

    // Backed Campaigns
    public function backed_campaigns_callback() {
        $html = '';
        require_once WPCF_DIR_PATH.'includes/woocommerce/dashboard/investment.php';
        echo $html;
    }

    // Pledges Received
    public function pledges_received_callback() {
        $html = '';
        require_once WPCF_DIR_PATH.'includes/woocommerce/dashboard/order.php';
        echo $html;
    }

    // Bookmarks
    public function bookmarks_callback() {
        $html = '';
        require_once WPCF_DIR_PATH.'includes/woocommerce/dashboard/bookmark.php';
        echo $html;
    }
}