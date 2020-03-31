<?php
namespace WPCF\woocommerce;

defined( 'ABSPATH' ) || exit;

class Base {

    /**
     * @var null
     *
     * Instance of this class
     */
    protected static $_instance = null;

    /**
     * @return null|Base
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Base constructor.
     *
     * @hook
     */
    public function __construct() {
        add_action('admin_enqueue_scripts',            array($this, 'admin_script')); //Add Additional backend js and css
        add_action('wp_enqueue_scripts',               array($this, 'frontend_script')); //Add frontend js and css
        add_action('init',                             array($this, 'media_pluggable'));
        add_action('admin_init',                       array($this, 'network_disable_notice' ));
        add_action('admin_head',                       array($this, 'add_mce_button'));
        add_action('wp_ajax_wpcf_settings_reset',      array($this, 'settings_reset'));
        add_action('wp_ajax_wpcf_addon_enable_disable',array($this, 'addon_enable_disable'));
        add_filter('admin_footer_text',                 array($this, 'admin_footer_text'), 2); // Footer Text, Asking Rating
        add_action('wp_ajax_wpcf_rated',                array($this, 'admin_footer_text_rated'));
        add_filter('plugin_action_links_'.WPCF_BASENAME,array($this, 'settings_link' ), 10, 5);
    }

    
    public function media_pluggable(){
        if (is_user_logged_in()){
            if(is_admin()){
                if (current_user_can('campaign_form_submit')){
                    add_action( 'pre_get_posts', array($this, 'set_user_own_media') );
                }
            }
        }
    }

    // Attachment Filter
    public function set_user_own_media($query){
        if ($query) {
            if (! empty($query->query['post_type'])) {
                if(!current_user_can('administrator')){
                    if ($query->query['post_type'] == 'attachment') {
                        $user = wp_get_current_user();
                        $query->set('author', $user->ID);
                    }
                }
            }
        }
    }

    public function settings_link($links){
		$actionsLinks = array(
		    'settings' => '<a href="'.admin_url('admin.php?page=wpcf-settings').'">Settings</a>',
		    'wpcf_docs' => '<a href="https://www.themeum.com/docs/wp-crowdfunding-introduction/" target="_blank">'.__('Docs', 'wp-crowdfunding').'</a>',
            'wpcf_support' => '<a href="https://www.themeum.com/support-forums/" target="_blank">'.__('Support', 'wp-crowdfunding').'</a>',
        );
		if( !defined('WPCF_PRO_VERSION') ){
			$actionsLinks['wpcf_update_pro'] = '<a href="https://www.themeum.com/product/wp-crowdfunding-plugin/?utm_source=crowdfunding_plugin" target="_blank">'.__('Update Pro', 'wp-crowdfunding').'</a>';
		}
        return array_merge($actionsLinks, $links);
    }

    // Set notice for disable in network
    public function network_disable_notice(){
        if (is_plugin_active_for_network(WPCF_BASENAME)){
            add_action('admin_notices', array($this, 'network_notice_callback'));
        }
    }

    // Disable Notice
    public static function network_notice_callback(){
        $html = '';
        $html .= '<div class="notice notice-error is-dismissible">';
            $html .= '<p>'.__('WP Crowdfunding will not work properly if you activate it from network, please deactivate from network and activate again from individual site admin.', 'wp-crowdfunding').'</p>';
        $html .= '</div>';
        echo $html;
    }


    // Hooks your functions into the correct filters
    function add_mce_button() {
        // check user permissions
        if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
            return;
        }
        // check if WYSIWYG is enabled
        if ( 'true' == get_user_option( 'rich_editing' ) ) {
            add_filter( 'mce_external_plugins', array($this, 'add_tinymce_js') );
            add_filter( 'mce_buttons', array($this, 'register_mce_button') );
        }
    }

    public function admin_script(){
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style( 'wpcf-crowdfunding-css', WPCF_DIR_URL .'assets/css/crowdfunding.css', false, WPCF_VERSION );
        wp_enqueue_script( 'wpcf-jquery-scripts', WPCF_DIR_URL .'assets/js/crowdfunding.js', array('jquery','wp-color-picker'), WPCF_VERSION, true );
    }

    /**
     * Registering necessary js and css
     * @frontend
     */
    public function frontend_script(){
        wp_enqueue_style( 'neo-crowdfunding-css-front', WPCF_DIR_URL .'assets/css/crowdfunding-front.css', false, WPCF_VERSION );
        wp_enqueue_style( 'jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css' );
        
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery' ) );
        wp_enqueue_script( 'jquery.easypiechart', WPCF_DIR_URL .'assets/js/jquery.easypiechart.min.js', array('jquery'), WPCF_VERSION, true);
        wp_enqueue_script( 'wp-neo-jquery-scripts-front', WPCF_DIR_URL .'assets/js/crowdfunding-front.js', array('jquery'), WPCF_VERSION, true);
        wp_localize_script( 'wp-neo-jquery-scripts-front', 'wpcf_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
        wp_enqueue_media();
    }







    // Declare script for new button
    function add_tinymce_js( $plugin_array ) {
        $plugin_array['crowdfunding_button'] = WPCF_DIR_URL .'assets/js/mce-button.js';
        return $plugin_array;
    }
    // Register new button in the editor
    function register_mce_button( $buttons ) {
        array_push( $buttons, 'crowdfunding_button' );
        return $buttons;
    }

    public function admin_footer_text($footer_text){
        if ( ! function_exists('wc_get_screen_ids')){
            return $footer_text;
        }

        $current_screen = get_current_screen();
        $crowdfunding_screen_ids = wpcf_function()->get_screen_id();

        if ( ! in_array($current_screen->id, $crowdfunding_screen_ids)){
            return $footer_text;
        }

        if ( ! get_option( 'wpcf_admin_footer_text_rated' ) ) {
            $footer_text = sprintf(__('If you like <strong>WP Crowdfunding</strong> please leave us a 5-stars %s rating. A huge thanks in advance!', 'wp-crowdfunding'), '<a href="https://wordpress.org/support/plugin/wp-crowdfunding/reviews?rate=5#new-post" target="_blank" class="wpcf-rating-link" data-rated="' . esc_attr__('Thanks :)', 'woocommerce') . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>');
            wc_enqueue_js("
                jQuery( 'a.wpcf-rating-link' ).click( function() {
                    jQuery.post( '" . admin_url('admin-ajax.php') . "', { action: 'wpcf_rated' } );
                    jQuery( this ).parent().text( jQuery( this ).data( 'rated' ) );
                });
            ");
        }else{
            $footer_text = sprintf( __( 'Thank you for raise funds with <strong>WP Crowdfunding</strong> by %s.', 'wp-crowdfunding' ), '<a href="https://www.themeum.com/?utm_source=wp_crowdfunding_plugin_admin" target="_blank">Themeum</a>');
        }

        return $footer_text;
    }

    /**
     * Added rated
     */
    function admin_footer_text_rated(){
        update_option('wpcf_admin_footer_text_rated', 'true');
    }



    /**
     * Reset method
     */

    public function settings_reset(){
        $initial_setup = new \WPCF\Initial_Setup();
        $initial_setup->wpcf_settings_reset();
    }

    /**
     * Method for enable / disable addons
     */
    public function addon_enable_disable(){
        $addonsConfig = maybe_unserialize(get_option('wpcf_addons_config'));
        $isEnable = (bool) sanitize_text_field( wpcf_function()->avalue_dot('isEnable', $_POST) );
        $addonFieldName = sanitize_text_field( wpcf_function()->avalue_dot('addonFieldName', $_POST) );
        $addonsConfig[$addonFieldName]['is_enable'] = ($isEnable) ? 1 : 0;
        update_option('wpcf_addons_config', $addonsConfig);
        wp_send_json_success();
    }
}