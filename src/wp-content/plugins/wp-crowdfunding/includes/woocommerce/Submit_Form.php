<?php
namespace WPCF\woocommerce;

defined( 'ABSPATH' ) || exit;

class Submit_Form {

    public function __construct() {
        add_action( 'wp_ajax_addfrontenddata', array($this, 'frontend_data_save')); // Save data for frontend campaign submit form
    }

    /**
     * @param int $user_id
     * @return array
     *
     * Get logged user all campaign id;
     */
    public function logged_in_user_campaign_ids($user_id = 0) {
        global $wpdb;
        if ($user_id == 0)
            $user_id = get_current_user_id();

        //Removed AND post_status = 'publish'
        $wp_query_users_product_id = $wpdb->get_col("select ID from {$wpdb->posts} WHERE post_author = {$user_id} AND post_type = 'product' ");
        return $wp_query_users_product_id;
    }

    /**
     * @frontend_data_save()
     *
     * Save
     */

    function frontend_data_save(){
        if ( ! isset( $_POST['wpcf_form_action_field'] ) || ! wp_verify_nonce( $_POST['wpcf_form_action_field'], 'wpcf_form_action' ) ) {
            die(json_encode(array('success'=> 0, 'message' => __('Sorry, your data did not verify.', 'wp-crowdfunding'))));
            exit;
        }

        global $wpdb;
        $title = $description = $category = $tag = $image_id = $video = $start_date = '';
        $end_date = $min_price = $max_price = $recommended_price = $type = '';
        $contributor_table = $contributor_show = $country = $location = $video = '';

        if ( empty($_POST['wpneo-form-title'])){
            die(json_encode(array('success'=> 0, 'message' => __('Title required', 'wp-crowdfunding'))));
        }
        if ( empty($_POST['wpneo-form-description'])){
            die(json_encode(array('success'=> 0, 'message' => __('Description required', 'wp-crowdfunding'))));
        }
        if ( empty($_POST['wpneo-form-short-description'])){
            die(json_encode(array('success'=> 0, 'message' => __('Short Description required', 'wp-crowdfunding'))));
        }
        if ( empty($_POST['wpneo-form-funding-goal'])){
            die(json_encode(array('success'=> 0, 'message' => __('Funding goal required', 'wp-crowdfunding'))));
        }
        if ( empty($_POST['wpneo_terms_agree'])){
            die(json_encode(array('success'=> 0, 'message' => __('Please check terms condition', 'wp-crowdfunding'))));
        }

        if( $_POST['wpneo-form-title'] ){                       $title = sanitize_text_field($_POST['wpneo-form-title']); }
        if( $_POST['wpneo-form-description'] ){                 $description = $_POST['wpneo-form-description']; }
        if( $_POST['wpneo-form-short-description'] ){           $short_description = $_POST['wpneo-form-short-description']; }
        if( $_POST['wpneo-form-category'] ){                    $category = sanitize_text_field($_POST['wpneo-form-category']); }
        if( $_POST['wpneo-form-tag'] ){                         $tag = sanitize_text_field($_POST['wpneo-form-tag']); }
        if( $_POST['wpneo-form-image-id'] ){                    $image_id = sanitize_text_field($_POST['wpneo-form-image-id']); }
        if( $_POST['wpneo-form-video'] ){                       $video = sanitize_text_field($_POST['wpneo-form-video']); }
        if( $_POST['wpneo-form-start-date'] ){                  $start_date = sanitize_text_field($_POST['wpneo-form-start-date']); }
        if( $_POST['wpneo-form-end-date'] ){                    $end_date = sanitize_text_field($_POST['wpneo-form-end-date']); }
        if( $_POST['wpneo-form-min-price'] ){                   $min_price = sanitize_text_field($_POST['wpneo-form-min-price']); }
        if( $_POST['wpneo-form-max-price'] ){                   $max_price = sanitize_text_field($_POST['wpneo-form-max-price']); }
        if( $_POST['wpneo-form-recommended-price'] ){           $recommended_price = sanitize_text_field($_POST['wpneo-form-recommended-price']); }
        if( isset($_POST['wpcf_predefined_pledge_amount']) ){   $wpcf_predefined_pledge_amount = sanitize_text_field($_POST['wpcf_predefined_pledge_amount']); }
        if( isset($_POST['wpcf_campaign_sizes']) ){             $wpcf_campaign_sizes = sanitize_text_field($_POST['wpcf_campaign_sizes']); }
        if( isset($_POST['wpcf_campaign_colors']) ){            $wpcf_campaign_colors = sanitize_text_field($_POST['wpcf_campaign_colors']); }
        if( $_POST['wpneo-form-funding-goal'] ){                $funding_goal = sanitize_text_field($_POST['wpneo-form-funding-goal']); }
        if( $_POST['wpneo-form-type'] ){                        $type = sanitize_text_field($_POST['wpneo-form-type']); }
        if( $_POST['wpneo-form-contributor-table'] ){           $contributor_table = sanitize_text_field($_POST['wpneo-form-contributor-table']); }
        if( $_POST['wpneo-form-contributor-show'] ){            $contributor_show 	= sanitize_text_field($_POST['wpneo-form-contributor-show']); }
        if( $_POST['wpneo-form-paypal'] ){                      $paypal = sanitize_text_field($_POST['wpneo-form-paypal']); }
        if( $_POST['wpneo-form-country'] ){                     $country = sanitize_text_field($_POST['wpneo-form-country']); }
        if( $_POST['wpneo-form-location'] ){                    $location = sanitize_text_field($_POST['wpneo-form-location']); }

        $user_id = get_current_user_id();
        $my_post = array(
            'post_type'		=>'product',
            'post_title'    => $title,
            'post_content'  => $description,
            'post_excerpt'  => $short_description,
            'post_author'   => $user_id,
        );

        do_action('wpcf_before_campaign_submit_action');

        if(isset($_POST['edit_form'])){
            //Prevent if unauthorised access
            $wp_query_users_product_id = $this->logged_in_user_campaign_ids();
            $my_post['ID'] = $_POST['edit_post_id'];

            $campaign_status = get_option('wpneo_campaign_edit_status', 'pending');
            $my_post['post_status'] = $campaign_status;

            if ( ! in_array($my_post['ID'], $wp_query_users_product_id)) {
                header('Content-Type: application/json');
                echo json_encode(array('success' => 0, 'msg' => 'Unauthorized action'));
                exit;
            }
            $post_id = wp_update_post( $my_post );
        }else{
            $my_post['post_status'] = get_option( 'wpneo_default_campaign_status' );
            $post_id = wp_insert_post( $my_post );
            if ($post_id) {
                WC()->mailer(); // load email classes
                do_action('wpcf_after_campaign_email',$post_id);
            }
        }

        if ($post_id) {
            if( $category != '' ){
                $cat = explode(' ',$category );
                wp_set_object_terms( $post_id , $cat, 'product_cat',true );
            }
            if( $tag != '' ){
                $tag = explode( ',',$tag );
                wp_set_object_terms( $post_id , $tag, 'product_tag',true );
            }
            wp_set_object_terms( $post_id , 'crowdfunding', 'product_type',true );

            wpcf_function()->update_meta($post_id, '_thumbnail_id', esc_attr($image_id));
            wpcf_function()->update_meta($post_id, 'wpneo_funding_video', esc_url($video));
            wpcf_function()->update_meta($post_id, '_nf_duration_start', esc_attr($start_date));
            wpcf_function()->update_meta($post_id, '_nf_duration_end', esc_attr($end_date));
            wpcf_function()->update_meta($post_id, 'wpneo_funding_minimum_price', esc_attr($min_price));
            wpcf_function()->update_meta($post_id, 'wpneo_funding_maximum_price', esc_attr($max_price));
            wpcf_function()->update_meta($post_id, 'wpneo_funding_recommended_price', esc_attr($recommended_price));
            wpcf_function()->update_meta($post_id, 'wpcf_predefined_pledge_amount', esc_attr($wpcf_predefined_pledge_amount));

            if( isset($wpcf_campaign_sizes )) {
                wpcf_function()->update_meta($post_id, 'wpcf_campaign_sizes', esc_attr($wpcf_campaign_sizes));
            }
            if( isset($wpcf_campaign_colors)) {
                wpcf_function()->update_meta($post_id, 'wpcf_campaign_colors', esc_attr($wpcf_campaign_colors));
            }

            wpcf_function()->update_meta($post_id, '_nf_funding_goal', esc_attr($funding_goal));
            wpcf_function()->update_meta($post_id, 'wpneo_campaign_end_method', esc_attr($type));
            wpcf_function()->update_meta($post_id, 'wpneo_show_contributor_table', esc_attr($contributor_table));
            wpcf_function()->update_meta($post_id, 'wpneo_mark_contributors_as_anonymous', esc_attr($contributor_show));
            wpcf_function()->update_meta($post_id, 'wpneo_campaigner_paypal_id', esc_attr($paypal));
            wpcf_function()->update_meta($post_id, 'wpneo_country', esc_attr($country));
            wpcf_function()->update_meta($post_id, '_nf_location', esc_html($location));

            //Saved repeatable rewards
            if (!empty($_POST['wpneo_rewards_pladge_amount'])) {
                $data             = array();
                $pladge_amount    = $_POST['wpneo_rewards_pladge_amount'];
                $description      = $_POST['wpneo_rewards_description'];
                $endmonth         = $_POST['wpneo_rewards_endmonth'];
                $endyear          = $_POST['wpneo_rewards_endyear'];
                $item_limit       = $_POST['wpneo_rewards_item_limit'];
                $image_field      = $_POST['wpneo_rewards_image_field'];
                $field_number     = count($pladge_amount);
                for ($i = 0; $i < $field_number; $i++) {
                    if (!empty($pladge_amount[$i])) {
                        $data[] = array(
                            'wpneo_rewards_pladge_amount'   => intval($pladge_amount[$i]),
                            'wpneo_rewards_description'     => esc_html($description[$i]),
                            'wpneo_rewards_endmonth'        => esc_html($endmonth[$i]),
                            'wpneo_rewards_endyear'         => esc_html($endyear[$i]),
                            'wpneo_rewards_item_limit'      => esc_html($item_limit[$i]),
                            'wpneo_rewards_image_field'     => esc_html($image_field[$i]),
                        );
                    }
                }
                $data_json = json_encode($data,JSON_UNESCAPED_UNICODE);
                wpcf_function()->update_meta($post_id, 'wpneo_reward', $data_json);
            }
        }
        $redirect = get_permalink(get_option('wpneo_crowdfunding_dashboard_page_id')).'?page_type=campaign';
        die(json_encode(array('success'=> 1, 'message' => __('Campaign successfully submitted', 'wp-crowdfunding'), 'redirect' => $redirect)));
    }

}