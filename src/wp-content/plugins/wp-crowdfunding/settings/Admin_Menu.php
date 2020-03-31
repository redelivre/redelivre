<?php
namespace WPCF\settings;

defined( 'ABSPATH' ) || exit;

class Admin_Menu {

    public function __construct() {
        add_action('wp_head',      array($this, 'style_custom_css' ));
        add_action('admin_menu',   array($this, 'register_menu_page' ));
        add_action('admin_init',   array($this, 'save_menu_settings' ));
    }


    /**
     * Crowdfunding Custom Styling Option
     */
    public function style_custom_css(){

        if( 'true' == get_option('wpneo_enable_color_styling') ){
            $color_scheme       = get_option( 'wpneo_color_scheme' );
            $button_bg          = get_option( 'wpneo_button_bg_color' );
            $button_bg_hover    = get_option( 'wpneo_button_bg_hover_color' );
            $button_text_color  = get_option( 'wpneo_button_text_color' );
            $text_hover_color   = get_option( 'wpneo_button_text_hover_color' );
            $custom_css         = get_option( 'wpneo_custom_css' );
    
            $style = '';
    
            if( $button_bg ){
                $style .= '.wpneo_donate_button, 
                            #wpneo-tab-reviews .submit,
                            .wpneo-edit-btn,
                            .wpneo-image-upload.float-right,
                            .wpneo-image-upload-btn,
                            .wpneo-save-btn,
                            #wpneo_active_edit_form,
                            .removeCampaignRewards,
                            #addreward,
                            .btn-style1,
                            #addcampaignupdate,
                            .wpneo-profile-button,
                            .dashboard-btn-link,
                            .wpneo_login_form_div #wp-submit,
                            .wpneo-submit-campaign,
                            input[type="button"].wpneo-image-upload,
                            input[type="button"]#search-submit,
                            #addreward,input[type="submit"].wpneo-submit-campaign,
                            .dashboard-btn-link,.label-primary,
                            .btn-style1,#wpneo-tab-reviews .submit,.dashboard-head-date input[type="submit"],
                            .wp-crowd-btn-primary, .wpneo_withdraw_button,.wpneo-dashboard-head-left ul li.active,
                            .wpneo-pagination ul li a:hover, .wpneo-pagination ul li span.current{ background-color:'.$button_bg.'; color:'.$button_text_color.'; }';
    
                $style .= '.wpneo_donate_button:hover, 
                            #wpneo-tab-reviews .submit:hover,
                            .wpneo-edit-btn:hover,
                            .wpneo-image-upload.float-right:hover,
                            .wpneo-image-upload-btn:hover,
                            .wpneo-save-btn:hover,
                            .removeCampaignRewards:hover,
                            #addreward:hover,
                            .removecampaignupdate:hover,
                            .btn-style1:hover,
                            #addcampaignupdate:hover,
                            #wpneo_active_edit_form:hover,
                            .removecampaignupdate:hover,
                            .wpneo-profile-button:hover,
                            .dashboard-btn-link:hover,
                            .wpneo_login_form_div #wp-submit:hover,
                            .wpneo-submit-campaign:hover,
                            .wpneo_donate_button:hover,.dashboard-head-date input[type="submit"]:hover,
                            .wp-crowd-btn-primary:hover,
                            .wpneo_withdraw_button:hover{ background-color:'.$button_bg_hover.'; color:'.$text_hover_color.'; }';
            }
    
            if( $color_scheme ){
                $style .=  '#neo-progressbar > div,
                            ul.wpneo-crowdfunding-update li:hover span.round-circle,
                            .wpneo-links li a:hover, .wpneo-links li.active a,#neo-progressbar > div {
                                background-color: '.$color_scheme.';
                            }
                            .wpneo-dashboard-summary ul li.active {
                                background: '.$color_scheme.';
                            }
                            .wpneo-tabs-menu li.wpneo-current {
                                border-bottom: 3px solid '.$color_scheme.';
                            }
                            .wpneo-pagination ul li a:hover,
                            .wpneo-pagination ul li span.current {
                                border: 2px solid '.$color_scheme.';
                            }
                            .wpneo-dashboard-summary ul li.active:after {
                                border-color: '.$color_scheme.' rgba(0, 128, 0, 0) rgba(255, 255, 0, 0) rgba(0, 0, 0, 0);
                            }
                            .wpneo-fields input[type="email"]:focus,
                            .wpneo-fields input[type="text"]:focus,
                            .wpneo-fields select:focus,
                            .wpneo-fields textarea {
                                border-color: '.$color_scheme.';
                            }
                            .wpneo-link-style1,
                            ul.wpneo-crowdfunding-update li .wpneo-crowdfunding-update-title,
                            .wpneo-fields-action span a:hover,.wpneo-name > p,
                            .wpneo-listings-dashboard .wpneo-listing-content h4 a,
                            .wpneo-listings-dashboard .wpneo-listing-content .wpneo-author a,
                            .wpcf-order-view,#wpneo_crowdfunding_modal_message td a,
                            .dashboard-price-number,.wpcrowd-listing-content .wpcrowd-admin-title h3 a,
                            .campaign-listing-page .stripe-table a,.stripe-table  a.label-default:hover,
                            a.wpneo-fund-modal-btn.wpneo-link-style1,.wpneo-tabs-menu li.wpneo-current a,
                            .wpneo-links div a:hover, .wpneo-links div.active a{
                                color: '.$color_scheme.';
                            }
                            .wpneo-links div a:hover .wpcrowd-arrow-down, .wpneo-links div.active a .wpcrowd-arrow-down {
                                border: solid '.$color_scheme.';
                                border-width: 0 2px 2px 0;
                            }
                            .wpneo-listings-dashboard .wpneo-listing-content h4 a:hover,
                            .wpneo-listings-dashboard .wpneo-listing-content .wpneo-author a:hover,
                            #wpneo_crowdfunding_modal_message td a:hover{
                                color: rgba('.$color_scheme.','.$color_scheme.','.$color_scheme.',0.95);
                            }';
    
                list($r, $g, $b) = sscanf( $color_scheme, "#%02x%02x%02x" );
                $style .=  '.tab-rewards-wrapper .overlay { background: rgba('.$r.','.$g.','.$b.',.95); }';
            }
    
            if( $custom_css ){ $style .= $custom_css; }
    
            $output = '<style type="text/css"> '.$style.' </style>';
            echo $output;
        }
    }
    
    

    /**
     * Crowdfunding Menu Option Page
     */
    public function register_menu_page(){
        add_menu_page( 
            'Crowdfunding',
            'Crowdfunding',
            'manage_options',
            'wpcf-crowdfunding',
            '',
            'dashicons-admin-multisite', 
            null 
        );

        $addon_pro =  __('Add-ons', 'wp-crowdfunding');
        if( !defined('WPCF_PRO_FILE') ){
            $addon_pro = __('Add-ons <span class="dashicons dashicons-star-filled" style="color:#ef450b"/>', 'wp-crowdfunding');
        }
        add_submenu_page(
            'wpcf-crowdfunding',
            __('Add-ons', 'wp-crowdfunding'),
            $addon_pro,
            'manage_options',
            'wpcf-crowdfunding',
            array( $this, 'wpcf_manage_addons' )
        );
        add_submenu_page(
            'wpcf-crowdfunding',
            __( 'Settings', 'wp-crowdfunding' ),
            __( 'Settings', 'wp-crowdfunding' ),
            'manage_options',
            'wpcf-settings',
            array( $this, 'wpcf_menu_page' )
        );
    }

    // Addon Listing
    public function wpcf_manage_addons() {
        include WPCF_DIR_PATH.'settings/view/Addons.php';
    }


    /**
     * Display a custom menu page
     */
    public function wpcf_menu_page(){
        // Settings Tab With slug and Display name
        $tabs = apply_filters('wpcf_settings_panel_tabs', array(
                'general' => array(
                    'tab_name' => __('General Settings','wp-crowdfunding'),
                    'load_form_file' => WPCF_DIR_PATH.'settings/tabs/Tab_General.php'
                ),
                'style' => array(
                    'tab_name' => __('Style','wp-crowdfunding'),
                    'load_form_file' => WPCF_DIR_PATH.'settings/tabs/Tab_Style.php'
                ),
            )
        );

        if( class_exists( 'WooCommerce' ) ){
            $woo_tab = array(
                'tab_name' => __('WooCommerce Settings','wp-crowdfunding'),
                'load_form_file' => WPCF_DIR_PATH.'settings/tabs/Tab_Woocommerce.php'
            );
            $tabs = array_slice($tabs, 0, 1, true) + array('woocommerce' => $woo_tab) + array_slice($tabs, 1, count($tabs), true);
        }

        $current_page = 'general';
        if( ! empty($_GET['tab']) ){
            $current_page = sanitize_text_field($_GET['tab']);
        }

        // $screen = get_current_screen();
        // print_r( $screen );   
        if (wpcf_function()->post('wpneo_settings_page_nonce_field')){
            echo '<div class="notice notice-success is-dismissible">';
                echo '<p>'.__( "Settings have been Saved.", "wp-crowdfunding" ).'</p>';
            echo '</div>';
        }

        // Print the Tab Title
        echo '<h2 class="nav-tab-wrapper">';
        foreach( $tabs as $tab => $name ){
            $class = ( $tab == $current_page ) ? ' nav-tab-active' : '';
            echo "<a class='nav-tab$class' href='?page=wpcf-settings&tab=$tab'>{$name['tab_name']}</a>";
        }
        echo '</h2>';
        ?>

        <form id="wpcf-crowdfunding" role="form" method="post" action="">
            <?php
            //Load tab file
            $default_file = WPCF_DIR_PATH.'settings/tabs/Tab_General.php';

            if( array_key_exists(trim(esc_attr($current_page)), $tabs) ){
                if( file_exists($default_file) ){
                    include_once $tabs[$current_page]['load_form_file'];
                }else{
                    include_once $default_file;
                }
            }else{
                include_once $default_file;
            }
            wp_nonce_field( 'wpneo_settings_page_action', 'wpneo_settings_page_nonce_field' );
            submit_button( null, 'primary', 'wpneo_admin_settings_submit_btn' );
            ?>
            <a href="javascript:;" class="button wpneo-crowdfunding-reset-btn"> <i class="dashicons dashicons-image-rotate"></i> <?php _e('Reset Settings', 'wp-crowdfunding'); ?></a>
        </form>
        <?php
    }


    /**
     * Add menu settings action
     */
    public function save_menu_settings() {
        
        if (wpcf_function()->post('wpneo_settings_page_nonce_field') && wp_verify_nonce( sanitize_text_field(wpcf_function()->post('wpneo_settings_page_nonce_field')), 'wpneo_settings_page_action' ) ){

            $current_tab = sanitize_text_field(wpcf_function()->post('wpneo_crowdfunding_admin_tab'));
            if( ! empty($current_tab) ){

                /**
                 * General Settings
                 */
                if ( $current_tab == 'tab_general' ){

                    $vendor_type = sanitize_text_field(wpcf_function()->post('vendor_type'));
                    wpcf_function()->update_text('vendor_type', $vendor_type);

                    $campaign_status = sanitize_text_field(wpcf_function()->post('wpneo_default_campaign_status'));
                    wpcf_function()->update_text('wpneo_default_campaign_status', $campaign_status);
                    
                    $edit_status = sanitize_text_field(wpcf_function()->post('wpneo_campaign_edit_status'));
                    wpcf_function()->update_text('wpneo_campaign_edit_status', $edit_status);

                    $min_price = sanitize_text_field(wpcf_function()->post('wpneo_show_min_price'));
                    wpcf_function()->update_checkbox('wpneo_show_min_price', $min_price);

                    $max_price = sanitize_text_field(wpcf_function()->post('wpneo_show_max_price'));
                    wpcf_function()->update_checkbox('wpneo_show_max_price', $max_price);

                    $recommended_price = sanitize_text_field(wpcf_function()->post('wpneo_show_recommended_price'));
                    wpcf_function()->update_checkbox('wpneo_show_recommended_price', $recommended_price);

                    $target_goal = sanitize_text_field(wpcf_function()->post('wpneo_show_target_goal'));
                    wpcf_function()->update_checkbox('wpneo_show_target_goal', $target_goal);

                    $target_date = sanitize_text_field(wpcf_function()->post('wpneo_show_target_date'));
                    wpcf_function()->update_checkbox('wpneo_show_target_date', $target_date);

                    $target_goal_and_date = sanitize_text_field(wpcf_function()->post('wpneo_show_target_goal_and_date'));
                    wpcf_function()->update_checkbox('wpneo_show_target_goal_and_date', $target_goal_and_date);

                    $campaign_never_end = sanitize_text_field(wpcf_function()->post('wpneo_show_campaign_never_end'));
                    wpcf_function()->update_checkbox('wpneo_show_campaign_never_end', $campaign_never_end);

                    $paypal_per_campaign_email = sanitize_text_field(wpcf_function()->post('wpneo_enable_paypal_per_campaign_email'));
                    wpcf_function()->update_checkbox('wpneo_enable_paypal_per_campaign_email', $paypal_per_campaign_email);

                    $role_selector = wpcf_function()->post('wpneo_user_role_selector');
                    update_option( 'wpneo_user_role_selector', $role_selector );


                    $role_list = maybe_unserialize(get_option( 'wpneo_user_role_selector' ));
                    $roles  = get_editable_roles();
                    foreach( $roles as $key=>$role ){
                        if( isset( $role['capabilities']['campaign_form_submit'] ) ){
                            $role = get_role( $key );
                            $role->remove_cap( 'campaign_form_submit' );
                        }
                    }

                    if( is_array( $role_list ) ){
                        if( !empty( $role_list ) ){
                            foreach( $role_list as $val ){
                                $role = get_role( $val );
                                $role->add_cap( 'campaign_form_submit' );
                                $role->add_cap( 'upload_files' );
                            }
                        }
                    }

                    $form_page_id = intval(wpcf_function()->post('wpneo_form_page_id'));
                    if (!empty($form_page_id)) {
                        global $wpdb;
                        $page_id = $form_page_id;
                        update_option( 'wpneo_form_page_id', $page_id );

                        //Update That Page with new crowdFunding [wpneo_crowdfunding_form]
                        $previous_content = str_replace( array( '[wpcf_form]', '[wpneo_crowdfunding_form]' ), array( '', '' ), get_post_field('post_content', $page_id));
                        $new_content = $previous_content . '[wpcf_form]';
                        //Update Post
                        $wpdb->update($wpdb->posts, array('post_content' => $new_content), array('ID'=> $page_id));
                    }

                    $dashboard_page_id = intval(wpcf_function()->post('wpneo_crowdfunding_dashboard_page_id'));
                    if (!empty($dashboard_page_id)) {
                        $page_id = $dashboard_page_id;
                        update_option('wpneo_crowdfunding_dashboard_page_id', $page_id);

                        //Update That Page with new crowdFunding [wpcf_dashboard]
                        $previous_content = str_replace( array( '[wpcf_dashboard]', '[wpneo_crowdfunding_dashboard]' ), array( '', '' ), get_post_field('post_content', $page_id));
                        $new_content = $previous_content . '[wpcf_dashboard]';
                        //Update Post
                        $wpdb->update($wpdb->posts, array('post_content' => $new_content), array('ID'=> $page_id));
                    }

                    $wpcf_user_reg_success_redirect_uri = sanitize_text_field(wpcf_function()->post('wpcf_user_reg_success_redirect_uri'));
                    update_option('wpcf_user_reg_success_redirect_uri', $wpcf_user_reg_success_redirect_uri);
                }


                // Listing Page Settings
                if ( $current_tab == 'tab_listing_page' ){
                    $columns  = intval(wpcf_function()->post('number_of_collumn_in_row'));
                    wpcf_function()->update_text('number_of_collumn_in_row', $columns );

                    $description_limits = intval(wpcf_function()->post('number_of_words_show_in_listing_description'));
                    wpcf_function()->update_text('number_of_words_show_in_listing_description', $description_limits );

                    $show_rating = sanitize_text_field(wpcf_function()->post('wpneo_show_rating'));
                    wpcf_function()->update_checkbox('wpneo_show_rating', $show_rating);
                }


                // Single Page Settings
                if ( $current_tab == 'tab_single_page' ){
                    $reward_design = intval(wpcf_function()->post('wpneo_single_page_reward_design'));
                    wpcf_function()->update_text('wpneo_single_page_reward_design', $reward_design);

                    $fixed_price = sanitize_text_field(wpcf_function()->post('wpneo_reward_fixed_price'));
                    wpcf_function()->update_checkbox('wpneo_reward_fixed_price', $fixed_price);
                }


                // WooCommerce Settings
                if ( $current_tab == 'tab_woocommerce' ){
                    $hide_shop_page = sanitize_text_field(wpcf_function()->post('hide_cf_campaign_from_shop_page'));
                    wpcf_function()->update_checkbox('hide_cf_campaign_from_shop_page', $hide_shop_page );

                    $single = sanitize_text_field(wpcf_function()->post('wpneo_single_page_id'));
                    wpcf_function()->update_checkbox('wpneo_single_page_id', $single );

                    $from_checkout = sanitize_text_field(wpcf_function()->post('hide_cf_address_from_checkout'));
                    wpcf_function()->update_checkbox('hide_cf_address_from_checkout', $from_checkout );

                    $listing = intval(sanitize_text_field(wpcf_function()->post('wpneo_listing_page_id')));
                    wpcf_function()->update_text('wpneo_listing_page_id', $listing );

                    $form_page = intval(sanitize_text_field(wpcf_function()->post('wpneo_form_page_id')));
                    wpcf_function()->update_text('wpneo_form_page_id', $form_page );

                    $registration = intval(sanitize_text_field(wpcf_function()->post('wpneo_registration_page_id')));
                    wpcf_function()->update_text('wpneo_registration_page_id', $registration );

                    $categories = sanitize_text_field(wpcf_function()->post('seperate_crowdfunding_categories'));
                    wpcf_function()->update_checkbox('seperate_crowdfunding_categories', $categories );

                    $selected_theme = sanitize_text_field(wpcf_function()->post('wpneo_cf_selected_theme'));
                    wpcf_function()->update_text('wpneo_cf_selected_theme', $selected_theme );

                    $requirement_title = sanitize_text_field(wpcf_function()->post('wpneo_requirement_title'));
                    wpcf_function()->update_text('wpneo_requirement_title', $requirement_title);

                    $requirement = sanitize_text_field(wpcf_function()->post('wpneo_requirement_text'));
                    wpcf_function()->update_text('wpneo_requirement_text', $requirement );

                    $agree_title = sanitize_text_field(wpcf_function()->post('wpneo_requirement_agree_title'));
                    wpcf_function()->update_text('wpneo_requirement_agree_title', $agree_title);

                    $cart_redirect = sanitize_text_field(wpcf_function()->post('wpneo_crowdfunding_add_to_cart_redirect'));
                    wpcf_function()->update_text('wpneo_crowdfunding_add_to_cart_redirect', $cart_redirect);

                    $collumns  = intval(wpcf_function()->post('number_of_collumn_in_row'));
                    wpcf_function()->update_text('number_of_collumn_in_row', $collumns );

                    $number_of_words_show_in_listing_description = intval(wpcf_function()->post('number_of_words_show_in_listing_description'));
                    wpcf_function()->update_text('number_of_words_show_in_listing_description', $number_of_words_show_in_listing_description);

                    $show_rating = sanitize_text_field(wpcf_function()->post('wpneo_show_rating'));
                    wpcf_function()->update_checkbox('wpneo_show_rating', $show_rating);

                    //Load single campaign to WooCommerce or not
                    $page_template = sanitize_text_field(wpcf_function()->post('wpneo_single_page_template'));
                    wpcf_function()->update_checkbox('wpneo_single_page_template', $page_template);

                    $reward_design = intval(wpcf_function()->post('wpneo_single_page_reward_design'));
                    wpcf_function()->update_text('wpneo_single_page_reward_design', $reward_design);

                    $fixed_price = sanitize_text_field(wpcf_function()->post('wpneo_reward_fixed_price'));
                    wpcf_function()->update_checkbox('wpneo_reward_fixed_price', $fixed_price);

                    $enable_tax = sanitize_text_field(wpcf_function()->post('wpcf_enable_tax'));
                    wpcf_function()->update_checkbox('wpcf_enable_tax', $enable_tax);
                }

                // Style Settings
                if ( $current_tab == 'tab_style' ){

                    $styling = sanitize_text_field(wpcf_function()->post('wpneo_enable_color_styling'));
                    wpcf_function()->update_checkbox( 'wpneo_enable_color_styling', $styling);

                    $scheme = sanitize_text_field(wpcf_function()->post('wpneo_color_scheme'));
                    wpcf_function()->update_text('wpneo_color_scheme', $scheme);

                    $button_bg_color = sanitize_text_field(wpcf_function()->post('wpneo_button_bg_color'));
                    wpcf_function()->update_text('wpneo_button_bg_color', $button_bg_color);

                    $button_bg_hover_color = sanitize_text_field(wpcf_function()->post('wpneo_button_bg_hover_color'));
                    wpcf_function()->update_text('wpneo_button_bg_hover_color', $button_bg_hover_color);

                    $button_text_color = sanitize_text_field(wpcf_function()->post('wpneo_button_text_color'));
                    wpcf_function()->update_text('wpneo_button_text_color', $button_text_color);

                    $button_text_hover_color = sanitize_text_field(wpcf_function()->post('wpneo_button_text_hover_color'));
                    wpcf_function()->update_text('wpneo_button_text_hover_color', $button_text_hover_color);

                    $custom_css = wpcf_function()->post( 'wpneo_custom_css' );
                    wpcf_function()->update_text( 'wpneo_custom_css', $custom_css );
                }
            }
        }
    }

}