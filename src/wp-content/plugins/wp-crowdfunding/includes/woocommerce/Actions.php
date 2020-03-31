<?php
namespace WPCF\woocommerce;

defined( 'ABSPATH' ) || exit;

class Actions {

    /**
     * @var null
     *
     * Instance of this class
     */
    protected static $_instance = null;

    /**
     * @return null
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Actions constructor.
     *
     * @hook
     */

    public function __construct() {
        add_action( 'wp_ajax_wpneo_dashboard_form',      array($this, 'dashboard_form_save'));
        add_action( 'wp_ajax_wpneo_profile_form',        array($this, 'profile_form_save'));
        add_action( 'wp_ajax_wpneo_contact_form',        array($this, 'contact_form_save'));
        add_action( 'wp_ajax_wpneo_password_form',       array($this, 'password_form_save'));
        add_action( 'wp_ajax_wpneo_update_status_save',  array($this, 'update_status_save'));
    }



    // General Form Action for Dashboard
    public function dashboard_form_save() {
        if ( ! isset( $_POST['wpneo_crowdfunding_dashboard_nonce_field'] ) || ! wp_verify_nonce( $_POST['wpneo_crowdfunding_dashboard_nonce_field'], 'wpneo_crowdfunding_dashboard_form_action' )) {
            die(json_encode(array('success'=> 0, 'message' => __('Sorry, your nonce did not verify.', 'wp-crowdfunding'))));
        }

        $id             = get_current_user_id();
        $email          = ( $_POST['email'] ) ? sanitize_email($_POST['email']) : "";
        $firstname      = ( $_POST['firstname'] ) ? sanitize_text_field($_POST['firstname']) : "";
        $lastname       = ( $_POST['lastname'] ) ? sanitize_text_field($_POST['lastname']) : "";
        $website        = ( $_POST['website'] ) ? esc_url_raw($_POST['website']) : "";
        $description    = ( $_POST['description'] ) ? sanitize_text_field($_POST['description'] ): "";

        $userdata = array(
            'ID'                => $id,
            'user_email'        => $email,
            'first_name'        => $firstname,
            'last_name'         => $lastname,
            'user_url'          => $website,
            'description'       => $description,
        );
        do_action('wpcf_after_save_dashboard');

        $update = wp_update_user( $userdata );
        $redirect = get_permalink(get_option('wpneo_crowdfunding_dashboard_page_id')).'?page_type=dashboard';
        if ($update){
            die(json_encode(array('success'=> 1, 'message' => __('Successfully updated', 'wp-crowdfunding'), 'redirect' => $redirect)));
        }else{
            die(json_encode(array('success'=> 0, 'message' => __('Error updating, please try again', 'wp-crowdfunding'), 'redirect' => $redirect)));
        }
    }

    // Profile Form Action for Dashboard
    public function profile_form_save(){
        if ( ! isset( $_POST['wpneo_crowdfunding_dashboard_nonce_field'] ) || ! wp_verify_nonce( $_POST['wpneo_crowdfunding_dashboard_nonce_field'], 'wpneo_crowdfunding_dashboard_form_action' )) {
            die(json_encode(array('success'=> 0, 'message' => __('Sorry, your nonce did not verify.', 'wp-crowdfunding'))));
        }

        $user_id             = get_current_user_id();

        $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';

        $profile_name         = ( $_POST['profile_name'] ) ? sanitize_text_field($_POST['profile_name']) : "";
        $profile_website      = ( $_POST['profile_website'] ) ? esc_url_raw($_POST['profile_website']) : "";
        $profile_about        = ( $_POST['profile_about'] ) ? implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST['profile_about']))) : "";
        $profile_portfolio    = ( $_POST['profile_portfolio'] ) ? sanitize_text_field($_POST['profile_portfolio']) : "";
        $profile_bio          = ( $_POST['profile_bio'] ) ? implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST['profile_bio']))) : "";
        $profile_mobile1      = ( $_POST['profile_mobile1'] ) ? sanitize_text_field($_POST['profile_mobile1']) : "";
        $profile_email1       = ( $_POST['profile_email1'] ) ? sanitize_email($_POST['profile_email1']) : "";
        $profile_fax          = ( $_POST['profile_fax'] ) ? sanitize_text_field($_POST['profile_fax']) : "";
        $profile_address      = ( $_POST['profile_address'] ) ? sanitize_text_field($_POST['profile_address']) : "";
        $profile_facebook     = ( $_POST['profile_facebook'] ) ? sanitize_text_field($_POST['profile_facebook']) : "";
        $profile_twitter      = ( $_POST['profile_twitter'] ) ? sanitize_text_field($_POST['profile_twitter']) : "";
        $profile_vk           = ( $_POST['profile_vk'] ) ? sanitize_text_field($_POST['profile_vk']) : "";
        $profile_linkedin     = ( $_POST['profile_linkedin'] ) ? sanitize_text_field($_POST['profile_linkedin']) : "";
        $profile_pinterest    = ( $_POST['profile_pinterest'] ) ? sanitize_text_field($_POST['profile_pinterest']) : "";
        $profile_image_id     = ( $_POST['profile_image_id'] ) ? sanitize_text_field($_POST['profile_image_id']) : "";

        //add_user_meta
        update_user_meta( $user_id,'profile_name',      $profile_name );
        update_user_meta( $user_id,'profile_website',   $profile_website );
        update_user_meta( $user_id,'profile_about',     $profile_about );
        update_user_meta( $user_id,'profile_portfolio', $profile_portfolio );
        update_user_meta( $user_id,'profile_bio',       $profile_bio );
        update_user_meta( $user_id,'profile_mobile1',   $profile_mobile1 );
        update_user_meta( $user_id,'profile_email1',    $profile_email1 );
        update_user_meta( $user_id,'profile_fax',       $profile_fax );
        update_user_meta( $user_id,'profile_address',   $profile_address );
        update_user_meta( $user_id,'profile_facebook',  $profile_facebook );
        update_user_meta( $user_id,'profile_twitter',   $profile_twitter );
        update_user_meta( $user_id,'profile_vk',        $profile_vk );
        update_user_meta( $user_id,'profile_linkedin',  $profile_linkedin );
        update_user_meta( $user_id,'profile_pinterest', $profile_pinterest );
        update_user_meta( $user_id,'profile_image_id',  intval($profile_image_id) );

        //Update User Info
        wp_update_user( array( 'ID' => $user_id, 'first_name' => $first_name, 'last_name' => $last_name ) );

        do_action('wpcf_after_save_profile');
        $redirect = get_permalink(get_option('wpneo_crowdfunding_dashboard_page_id')).'?page_type=profile';
        die(json_encode(array('success'=> 1, 'message' => __('Successfully updated','wp-crowdfunding'),'redirect' => $redirect)));
    }

    // Profile Form Action for Dashboard
    public function contact_form_save(){
        if ( ! isset( $_POST['wpneo_crowdfunding_dashboard_nonce_field'] ) || ! wp_verify_nonce( $_POST['wpneo_crowdfunding_dashboard_nonce_field'], 'wpneo_crowdfunding_dashboard_form_action' )) {
            die(json_encode(array('success'=> 0, 'message' => __('Sorry, your nonce did not verify.', 'wp-crowdfunding'))));
        }

        $user_id                 = get_current_user_id();
        // Shipping Address
        $shipping_first_name     = ( $_POST['shipping_first_name'] ) ? sanitize_text_field($_POST['shipping_first_name']) : "";
        $shipping_last_name      = ( $_POST['shipping_last_name'] ) ? sanitize_text_field($_POST['shipping_last_name']) : "";
        $shipping_company        = ( $_POST['shipping_company'] ) ? sanitize_text_field($_POST['shipping_company']) : "";
        $shipping_address_1      = ( $_POST['shipping_address_1'] ) ? sanitize_text_field($_POST['shipping_address_1']) : "";
        $shipping_address_2      = ( $_POST['shipping_address_2'] ) ? sanitize_text_field($_POST['shipping_address_2']) : "";
        $shipping_city           = ( $_POST['shipping_city'] ) ? sanitize_text_field($_POST['shipping_city']) : "";
        $shipping_postcode       = ( $_POST['shipping_postcode'] ) ? intval(sanitize_text_field($_POST['shipping_postcode'])) : "";
        $shipping_country        = ( $_POST['shipping_country'] ) ? sanitize_text_field($_POST['shipping_country']) : "";
        $shipping_state          = ( $_POST['shipping_state'] ) ? sanitize_text_field($_POST['shipping_state']) : "";
        // Billing Address
        $billing_first_name     =  ( $_POST['billing_first_name'] ) ? sanitize_text_field($_POST['billing_first_name']) : "";
        $billing_last_name      =  ( $_POST['billing_last_name'] ) ? sanitize_text_field($_POST['billing_last_name']) : "";
        $billing_company        =  ( $_POST['billing_company'] ) ? sanitize_text_field($_POST['billing_company']) : "";
        $billing_address_1      =  ( $_POST['billing_address_1'] ) ? sanitize_text_field($_POST['billing_address_1']) : "";
        $billing_address_2      =  ( $_POST['billing_address_2'] ) ? sanitize_text_field($_POST['billing_address_2']) : "";
        $billing_city           =  ( $_POST['billing_city'] ) ? sanitize_text_field($_POST['billing_city']) : "";
        $billing_postcode       =  ( $_POST['billing_postcode'] ) ? intval(sanitize_text_field($_POST['billing_postcode'])) : "";
        $billing_country        =  ( $_POST['billing_country'] ) ? sanitize_text_field($_POST['billing_country']) : "";
        $billing_state          =  ( $_POST['billing_state'] ) ? sanitize_text_field($_POST['billing_state']) : "";
        $billing_phone          =  ( $_POST['billing_phone'] ) ? sanitize_text_field($_POST['billing_phone']) : "";
        $billing_email          =  ( $_POST['billing_email'] ) ? sanitize_email($_POST['billing_email']) : "";


        update_user_meta($user_id,'shipping_first_name', $shipping_first_name);
        update_user_meta($user_id,'shipping_last_name', $shipping_last_name);
        update_user_meta($user_id,'shipping_company', $shipping_company);
        update_user_meta($user_id,'shipping_address_1', $shipping_address_1);
        update_user_meta($user_id,'shipping_address_2', $shipping_address_2);
        update_user_meta($user_id,'shipping_city', $shipping_city);
        update_user_meta($user_id,'shipping_postcode', $shipping_postcode);
        update_user_meta($user_id,'shipping_country', $shipping_country);
        update_user_meta($user_id,'shipping_state', $shipping_state);

        //add_user_meta ( Billing )
        update_user_meta($user_id,'billing_first_name', $billing_first_name);
        update_user_meta($user_id,'billing_last_name', $billing_last_name);
        update_user_meta($user_id,'billing_company', $billing_company);
        update_user_meta($user_id,'billing_address_1', $billing_address_1);
        update_user_meta($user_id,'billing_address_2', $billing_address_2);
        update_user_meta($user_id,'billing_city', $billing_city);
        update_user_meta($user_id,'billing_postcode', $billing_postcode);
        update_user_meta($user_id,'billing_country', $billing_country);
        update_user_meta($user_id,'billing_state', $billing_state);
        update_user_meta($user_id,'billing_phone', $billing_phone);

        update_user_meta($user_id,'billing_email', $billing_email);

        $redirect = get_permalink(get_option('wpneo_crowdfunding_dashboard_page_id')).'?page_type=contact';

        die(json_encode(array('success'=> 1, 'message' => __('Successfully updated'), 'redirect' => $redirect)));
    }

    // Password Form Action for Dashboard
    public function password_form_save() {
        if ( ! isset( $_POST['wpneo_crowdfunding_dashboard_nonce_field'] ) || ! wp_verify_nonce( $_POST['wpneo_crowdfunding_dashboard_nonce_field'], 'wpneo_crowdfunding_dashboard_form_action' )) {
            die(json_encode(array('success'=> 0, 'message' => __('Sorry, your nonce did not verify.', 'wp-crowdfunding'))));
        }

        $id                 = get_current_user_id();
        $password           = sanitize_text_field($_POST['password']);
        $new_password       = sanitize_text_field($_POST['new-password']);
        $retype_password    = sanitize_text_field($_POST['retype-password']);
        $redirect           = get_permalink(get_option('wpneo_crowdfunding_dashboard_page_id')).'?page_type=password';

        if( isset($_POST['password']) && isset($_POST['new-password']) && isset($_POST['retype-password']) ){
            if( ( $new_password == $retype_password ) && ( $retype_password != "" ) ){
                $user = get_user_by( 'id', $id );
                if ( $user && wp_check_password( $password, $user->data->user_pass, $user->ID) ){
                    wp_set_password( $new_password, $id );
                    die(json_encode(array('success'=> 1, 'message' => __('Password successfully updated'), 'redirect' => $redirect)));
                }
            }
            die(json_encode(array('success'=> 0, 'message' => __('Error updating, please try again'), 'redirect' => $redirect)));
        }
    }

    public function update_status_save(){
        if ( ! isset( $_POST['wpcf_form_action_field'] ) || ! wp_verify_nonce( $_POST['wpcf_form_action_field'], 'wpcf_form_action' ) ) {
            die(json_encode(array('success'=> 0, 'message' => __('Sorry, your status did not verify.', 'wp-crowdfunding'))));
            exit;
        }
        if ( ! empty($_POST['wpneo_prject_update_title_field'])){
            $data           = array();
            $post_id        = $_POST['postid'];
            $title_field    = $_POST['wpneo_prject_update_title_field'];
            $date_field     = $_POST['wpneo_prject_update_date_field'];
            $details_field  = $_POST['wpneo_prject_update_details_field'];
            $field_count    = count($title_field);
            for ($i=0; $i<$field_count; $i++){
                if (! empty($title_field[$i])) {
                    $data[] = array(
                        'date'      => sanitize_text_field( $date_field[$i] ),
                        'title'     => sanitize_text_field( $title_field[$i] ),
                        'details'   => esc_html( $details_field[$i] )
                    );
                }
            }
            $data_json = json_encode($data,JSON_UNESCAPED_UNICODE);
            $post_update = wpcf_function()->update_meta( $post_id, 'wpneo_campaign_updates', $data_json );
            if ($post_update) {
                WC()->mailer(); // load email classes
                do_action('wpcf_campaign_update_email', $post_id);
                
            }
            $redirect = get_permalink(get_option('wpneo_crowdfunding_dashboard_page_id')).'?page_type=update&postid='.$post_id;
            die(json_encode(array('success'=> 1, 'message' => __('Successfully updated'), 'redirect' => $redirect)));
        }
    }

}
