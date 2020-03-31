<?php
namespace WPCF;

defined( 'ABSPATH' ) || exit;

if (! class_exists('Initial_Setup')) {

    class Initial_Setup {

        /**
         * Do some task during plugin activation
         */
        public function initial_plugin_activation() {
            if (get_option('wpneo_crowdfunding_is_used')) { // Check is plugin used before or not
                return false;
            }
            self::update_option();
            self::insert_page();
        }

        /**
         * Insert settings option data
         */
        public function update_option() {
            $init_setup_data = array(
                'wpneo_crowdfunding_is_used' => WPCF_VERSION,
                'wpneo_cf_selected_theme' => 'basic',
                'vendor_type' => 'woocommerce',
                'wpneo_default_campaign_status' => 'draft',
                'wpneo_campaign_edit_status' => 'pending',
                'wpneo_enable_color_styling' => 'true',
                'wpneo_show_min_price' => 'true',
                'wpneo_show_max_price' => 'true',
                'wpneo_show_recommended_price' => 'true',
                'wpneo_show_target_goal' => 'true',
                'wpneo_show_target_date' => 'true',
                'wpneo_show_target_goal_and_date' => 'true',
                'wpneo_show_campaign_never_end' => 'true',
                'wpneo_enable_paypal_per_campaign_email' => 'true',
                'wpneo_single_page_template' => 'in_wp_crowdfunding',
                'wpneo_single_page_reward_design' => '1',
                'hide_cf_campaign_from_shop_page' => 'false',
                'wpneo_crowdfunding_add_to_cart_redirect' => 'checkout_page',
                'wpneo_single_page_id' => 'true',
                'wpneo_enable_recaptcha' => 'false',
                'wpneo_enable_recaptcha_in_user_registration' => 'false',
                'wpneo_enable_recaptcha_campaign_submit_page' => 'false',
                'wpneo_requirement_agree_title' => 'I agree with the terms and conditions.',
            );

            foreach ($init_setup_data as $key => $value ) {
                update_option( $key , $value );
            }
    
            //Upload Permission
            update_option( 'wpneo_user_role_selector', array('administrator', 'editor', 'author', 'shop_manager') );
            $role_list = get_option( 'wpneo_user_role_selector' );
            if( is_array( $role_list ) ){
                if( !empty( $role_list ) ){
                    foreach( $role_list as $val ){
                        $role = get_role( $val );
                        if ($role){
	                        $role->add_cap( 'campaign_form_submit' );
	                        $role->add_cap( 'upload_files' );
                        }
                    }
                }
            }
        }

        /**
         * Insert menu page
         */
        public function insert_page() {
            // Create page object
            $dashboard = array(
                'post_title'    => 'CF Dashboard',
                'post_content'  => '[wpcf_dashboard]',
                'post_type'     => 'page',
                'post_status'   => 'publish',
            );
            $form = array(
                'post_title'    => 'CF campaign form',
                'post_content'  => '[wpcf_form]',
                'post_type'     => 'page',
                'post_status'   => 'publish',
            );
            $listing = array(
                'post_title'    => 'CF Listing Page',
                'post_content'  => '[wpcf_listing]',
                'post_type'     => 'page',
                'post_status'   => 'publish',
            );
            $registration = array(
                'post_title'    => 'CF User Registration',
                'post_content'  => '[wpcf_registration]',
                'post_type'     => 'page',
                'post_status'   => 'publish',
            );
        
            /**
             * Insert the page into the database
             * @Dashbord, @Form, @Listing and @Registration Pages Object
             */
            $dashboard_page = wp_insert_post( $dashboard );
            if ( !is_wp_error( $dashboard_page ) ) {
                wpcf_function()->update_text( 'wpneo_crowdfunding_dashboard_page_id', $dashboard_page );
            }
            $form_page = wp_insert_post( $form );
            if( !is_wp_error( $form_page ) ){
                wpcf_function()->update_text( 'wpneo_form_page_id', $form_page );
            }
            $listing_page = wp_insert_post( $listing );
            if( !is_wp_error( $listing_page ) ){
                wpcf_function()->update_text( 'wpneo_listing_page_id', $listing_page );
            }
            $registration_page = wp_insert_post( $registration );
            if( !is_wp_error( $registration_page ) ){
                wpcf_function()->update_text( 'wpneo_registration_page_id', $registration_page );
            }
        }


        /**
         * Reset method, the ajax will call that method for Reset Settings
         */
        public function settings_reset() {
            self::update_option();
        }

        /**
         * Deactivation Hook For Crowdfunding
         */
        public function initial_plugin_deactivation(){

        }


        /**
         * Show notice if there is no woocommerce
         */
        public static function no_vendor_notice(){
            printf(
                '<div class="notice notice-error is-dismissible"><p>%1$s <a target="_blank" href="%2$s">%3$s</a> %4$s</p></div>', 
                __('Please install & activate','wp-crowdfunding'), 
                'https://wordpress.org/plugins/woocommerce/', 
                __('WooCommerce','wp-crowdfunding'), 
                __('in order to use WP Crowdfunding plugin.','wp-crowdfunding') 
            );
        }
        

        public static function wc_low_version(){
            printf(
                '<div class="notice notice-error is-dismissible"><p>%1$s <a target="_blank" href="%2$s">%3$s</a> %4$s</p></div>', 
                __('Your','wp-crowdfunding'), 
                'https://wordpress.org/plugins/woocommerce/', 
                __('WooCommerce','wp-crowdfunding'), 
                __('version is below then 3.0, please update.','wp-crowdfunding') 
            );
        }

    }
}