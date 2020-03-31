<?php
namespace WPCF\shortcode;

defined( 'ABSPATH' ) || exit;


class Campaign_Submit_Form {
    public function __construct() {
        add_shortcode( 'wpcf_form', array( $this, 'campaign_form_callback' ) );        
    }
    
    // Shortcode for Forntend Submission Form
    public function campaign_form_callback( $atts ){
        
        global $post, $wpdb;

        $html = '';
        $title = $description = $short_description = $category = $tag = $image_url = $image_id = $video = $start_date = $end_date = $minimum_price = $maximum_price = $recommended_price = $pledge_amount = $funding_goal = $campaign_end_method = $type = $contributor_show = $paypal = $country =
        $location = $edit_form = $edit_id = $checked = $checked2 = '';

        $reward = '';
        if( isset($_GET['action']) && isset($_GET['postid']) ){
            if( $_GET['action'] == 'edit' ){
                $post_id = (int) sanitize_text_field($_GET['postid']);

                //Prevent if unauthorised access
                $campaign_users = new \WPCF\woocommerce\Submit_Form();
                $wp_query_users_product_id = $campaign_users->logged_in_user_campaign_ids();
                if ( ! in_array($post_id, $wp_query_users_product_id)){

                    $html.= '<header class="wpneo-page-header">';
                    $html.= '<h1 class="wpneo-page-title">'. __( 'Not Found', 'wp-crowdfunding' ) .'</h1>';
                    $html .= '</header>';
                    $html .= '<h2 class="wpneo-subtitle">'. __( 'This is somewhat embarrassing, isn’t it?', 'wp-crowdfunding' ) .'</h2>';
                    $html .= '<p>'. __( 'It looks like nothing was found at this location. Maybe try a search?', 'wp-crowdfunding' ) .'</p>';
                    $html .= get_search_form( false );

                    return $html;
                }

                $args = array(
                    'p'             => $post_id,
                    'post_type'     => 'product',
                    'post_status'   => array('publish', 'pending', 'draft', 'auto-draft')
                );
                $the_query = new \WP_Query( $args );
                if ( $the_query->have_posts() ) {
                    while ( $the_query->have_posts() ) {
                        $the_query->the_post();
                        if( $post->post_author == get_current_user_id() ){

                            $title              = get_the_title();
                            $short_description  = get_the_excerpt();
                            $description        = get_the_content();
                            $category           = strip_tags(get_the_term_list( get_the_ID(), 'product_cat', '', ','));
                            $tag                = strip_tags( get_the_term_list( get_the_ID(), "product_tag","",", ") );
                            if ( has_post_thumbnail() ) {
                                $image_url          = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );
                                $image_url          = $image_url[0];
                                $image_id           = get_post_thumbnail_id( get_the_ID() );
                            }
                            $video              = get_post_meta( get_the_ID(), 'wpneo_funding_video', true );
                            $start_date         = get_post_meta( get_the_ID(), '_nf_duration_start', true );
                            $end_date           = get_post_meta( get_the_ID(), '_nf_duration_end', true );
                            $minimum_price      = get_post_meta( get_the_ID(), 'wpneo_funding_minimum_price', true );
                            $maximum_price      = get_post_meta( get_the_ID(), 'wpneo_funding_maximum_price', true );
                            $recommended_price  = get_post_meta( get_the_ID(), 'wpneo_funding_recommended_price', true );
                            $pledge_amount      = get_post_meta( get_the_ID(), 'wpcf_predefined_pledge_amount', true );
                            $funding_goal       = get_post_meta( get_the_ID(), '_nf_funding_goal', true );
                            $campaign_end_method= get_post_meta(get_the_ID(), 'wpneo_campaign_end_method', true);
                            $type               = get_post_meta( get_the_ID(), 'wpneo_show_contributor_table', true );
                            $contributor_show   = get_post_meta( get_the_ID(), 'wpneo_mark_contributors_as_anonymous', true );
                            $paypal             = get_post_meta( get_the_ID(), 'wpneo_campaigner_paypal_id', true );
                            $country            = get_post_meta( get_the_ID(), 'wpneo_country', true );
                            $location           = get_post_meta( get_the_ID(), '_nf_location', true );
                            $reward             = get_post_meta( get_the_ID(), 'wpneo_reward', true );
                            $edit_form          = '<input type="hidden" name="edit_form" value="editform"/>';
                            $edit_id            = '<input type="hidden" name="edit_post_id" value="'.$_GET["postid"].'"/>';

                        }
                    }
                }
                wp_reset_postdata();
            }
        }

        //Protect this page from Guest user
        if (!is_user_logged_in()){
            $html .= '<div class="woocommerce">';
            $html .= '<div class="woocommerce-info">'.__("Please log in first.","wp-crowdfunding").' <a class="wpneoShowLogin" href="#">'.__("Click here to login.","wp-crowdfunding").'</a></div>';
            $html .= wpcf_function()->login_form();
            $html .= '</div>';
            return $html;
        }

        if (is_user_logged_in()){
            if (!current_user_can('campaign_form_submit')) {
                $html .= '<div class="woocommerce-info">'.__("You do not have permission to create a new campaign. Please contact the website administrator.","wp-crowdfunding").'</div>';
                return $html;
            }
        }


        $html .= '<form type="post" action="" id="wpneofrontenddata">';


        //Title
        $html .= '<div class="wpneo-single">';
        $html .= '<div class="wpneo-name">'.__( "Title" , "wp-crowdfunding" ).'</div>';
        $html .= '<div class="wpneo-fields">';
        $html .= '<input type="text" name="wpneo-form-title" value="'.$title.'">';
        $html .= '<small>'.__("Put the campaign title here","wp-crowdfunding").'</small>';
        $html .= '</div>';
        $html .= '</div>';


        //Product Description
        $html .= '<div class="wpneo-single">';
        $html .= '<div class="wpneo-name">'.__( "Description" , "wp-crowdfunding" ).'</div>';
        $html .= '<div class="wpneo-fields">';
        ob_start();
        wp_editor( $description, 'wpneo-form-description' );
        $html .= ob_get_clean();
        $html .= '<small>'.__("Put the campaign description here","wp-crowdfunding").'</small>';
        $html .= '</div>';
        $html .= '</div>';


        //Product Short Description
        $html .= '<div class="wpneo-single">';
        $html .= '<div class="wpneo-name">'.__( "Short Description" , "wp-crowdfunding" ).'</div>';
        $html .= '<div class="wpneo-fields">';
        ob_start();
        wp_editor( $short_description, 'wpneo-form-short-description', array('editor_height'=>200) );
        $html .= ob_get_clean();
        $html .= '<small>'.__("Put Here Product Short Description","wp-crowdfunding").'</small>';
        $html .= '</div>';
        $html .= '</div>';


        //Category
        $html .= '<div class="wpneo-single">';
        $html .= '<div class="wpneo-name">'.__( "Category" , "wp-crowdfunding" ).'</div>';
        $html .= '<div class="wpneo-fields">';
        $html .= '<select name="wpneo-form-category">';


        $cat_args = array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
        );
        //Get is Crowdfunding Categories only
        $is_only_crowdfunding_categories = get_option('seperate_crowdfunding_categories');
        if ('true' === $is_only_crowdfunding_categories){
            $cat_args['meta_query'] = array(
                array(
                    'key' => '_marked_as_crowdfunding',
                    'value' => '1'
                )
            );
        }
        $all_cat = get_terms($cat_args );
        foreach ($all_cat as $value) {
            $selected = ($category == $value->name) ? 'selected':'';
            $html .= '<option '.$selected.' value="'.$value->slug.'">'.$value->name.'</option>';
        }
        $html .= '</select>';
        $html .= '<small>'.__("Select your campaign category","wp-crowdfunding").'</small>';
        $html .= '</div>';
        $html .= '</div>';


        //Tag
        $html .= '<div class="wpneo-single">';
        $html .= '<div class="wpneo-name">'.__( "Tag" , "wp-crowdfunding" ).'</div>';
        $html .= '<div class="wpneo-fields">';
        $html .= '<input type="text" name="wpneo-form-tag" placeholder="'.__( "Tag","wp-crowdfunding" ).'" value="'.$tag.'">';
        $html .= '<small>'.__("Separate tags with commas eg: tag1,tag2","wp-crowdfunding").'</small>';
        $html .= '</div>';
        $html .= '</div>';


        //Image
        $html .= '<div class="wpneo-single">';
        $html .= '<div class="wpneo-name">'.__( "Feature Image" , "wp-crowdfunding" ).'</div>';
        $html .= '<div class="wpneo-fields">';
        $html .= '<input type="text" name="wpneo-form-image-url" class="wpneo-upload wpneo-form-image-url" value="'.$image_url.'">';
        $html .= '<input type="hidden" name="wpneo-form-image-id" class="wpneo-form-image-id" value="'.$image_id.'">';
        $html .= '<input type="button" id="cc-image-upload-file-button" class="wpneo-image-upload float-right" value="'.__("Upload Image","wp-crowdfunding").'" data-url="'. get_site_url().'" />';
        $html .= '<small>'.__("Upload a campaign feature image","wp-crowdfunding").'</small>';
        $html .= '</div>';
        $html .= '</div>';


        //Video
        $html .= '<div class="wpneo-single">';
        $html .= '<div class="wpneo-name">'.__( "Video" , "wp-crowdfunding" ).'</div>';
        $html .= '<div class="wpneo-fields">';
        $html .= '<input type="text" name="wpneo-form-video" value="'.$video.'" placeholder="'.__( "https://","wp-crowdfunding" ).'" >';
        $html .= '<small>'.__("Put the campaign video URL here","wp-crowdfunding").'</small>';
        $html .= '</div>';
        $html .= '</div>';

        //Campaign End Method
        $html .= '<div class="wpneo-single">';
        $html .= '<div class="wpneo-name">'.__("Campaign End Method" , "wp-crowdfunding" ).'</div>';
        $html .= '<div class="wpneo-fields">';
        $html .= '<select name="wpneo-form-type">';
        if (get_option('wpneo_show_target_goal') == 'true') {
            $selected = $campaign_end_method == 'target_goal' ? 'selected="selected"' : '';
            $html .= '<option value="target_goal" '.$selected.'>' . __("Target Goal", "wp-crowdfunding") . '</option>';
        }
        if (get_option('wpneo_show_target_date') == 'true') {
            $selected = $campaign_end_method == 'target_date' ? 'selected="selected"' : '';
            $html .= '<option value="target_date" '.$selected.'>' . __("Target Date", "wp-crowdfunding") . '</option>';
        }
        if (get_option('wpneo_show_target_goal_and_date') == 'true') {
            $selected = $campaign_end_method == 'target_goal_and_date' ? 'selected="selected"' : '';
            $html .= '<option value="target_goal_and_date" '.$selected.'>' . __("Target Goal & Date", "wp-crowdfunding") . '</option>';
        }
        if (get_option('wpneo_show_campaign_never_end') == 'true') {
            $selected = $campaign_end_method == 'never_end' ? 'selected="selected"' : '';
            $html .= '<option value="never_end" '.$selected.'>' . __("Campaign Never Ends", "wp-crowdfunding") . '</option>';
        }
        $html .= '</select>';
        $html .= '<small>'.__("Choose the stage when campaign will end","wp-crowdfunding").'</small>';
        $html .= '</div>';
        $html .= '</div>';


        //Start Date
        $html .= '<div class="wpneo-single wpneo-first-half">';
        $html .= '<div class="wpneo-name">'.__( "Start Date" , "wp-crowdfunding" ).'</div>';
        $html .= '<div class="wpneo-fields">';
        $html .= '<input type="text" name="wpneo-form-start-date" value="'.$start_date.'" id="wpneo_form_start_date">';
        $html .= '<small>'.__("Campaign start date (dd-mm-yy)","wp-crowdfunding").'</small>';
        $html .= '</div>';
        $html .= '</div>';


        //End Date
        $html .= '<div class="wpneo-single wpneo-second-half">';
        $html .= '<div class="wpneo-name">'.__( "End Date" , "wp-crowdfunding" ).'</div>';
        $html .= '<div class="wpneo-fields">';
        $html .= '<input type="text" name="wpneo-form-end-date" value="'.$end_date.'" id="wpneo_form_end_date">';
        $html .= '<small>'.__("Campaign end date (dd-mm-yy)","wp-crowdfunding").'</small>';
        $html .= '</div>';
        $html .= '</div>';


        //Minimum Amount
        if (get_option('wpneo_show_min_price') == 'true') {
            $html .= '<div class="wpneo-single wpneo-first-half">';
            $html .= '<div class="wpneo-name">'.__( "Minimum Amount" , "wp-crowdfunding" ).'</div>';
            $html .= '<div class="wpneo-fields">';
            $html .= '<input type="number" name="wpneo-form-min-price" value="'.$minimum_price.'">';
            $html .= '<small>'.__("Minimum campaign funding amount","wp-crowdfunding").'</small>';
            $html .= '</div>';
            $html .= '</div>';
        }


        //Maximum Amount
        if (get_option('wpneo_show_max_price') == 'true') {
            $html .= '<div class="wpneo-single wpneo-second-half">';
            $html .= '<div class="wpneo-name">'.__( "Maximum Amount" , "wp-crowdfunding" ).'</div>';
            $html .= '<div class="wpneo-fields">';
            $html .= '<input type="number" name="wpneo-form-max-price" value="'.$maximum_price.'" >';
            $html .= '<small>'.__("Maximum campaign funding amount","wp-crowdfunding").'</small>';
            $html .= '</div>';
            $html .= '</div>';
        }


        //Funding Goal
        $html .= '<div class="wpneo-single">';
        $html .= '<div class="wpneo-name">'.__( "Funding Goal" , "wp-crowdfunding" ).'</div>';
        $html .= '<div class="wpneo-fields">';
        $html .= '<input type="number" name="wpneo-form-funding-goal" value="'.$funding_goal.'">';
        $html .= '<small>'.__("Campaign funding goal","wp-crowdfunding").'</small>';
        $html .= '</div>';
        $html .= '</div>';

        //Recommended Amount
        if (get_option('wpneo_show_recommended_price') == 'true') {
            $html .= '<div class="wpneo-single wpneo-first-half">';
            $html .= '<div class="wpneo-name">'.__( "Recommended Amount" , "wp-crowdfunding" ).'</div>';
            $html .= '<div class="wpneo-fields">';
            $html .= '<input type="number" name="wpneo-form-recommended-price" value="'.$recommended_price.'">';
            $html .= '<small>'.__("Recommended campaign funding amount","wp-crowdfunding").'</small>';
            $html .= '</div>';
            $html .= '</div>';
        }

        //Predefined Pledge Amount
        $html .= '<div class="wpneo-single wpneo-second-half">';
        $html .= '<div class="wpneo-name">'.__( "Predefined Pledge Amount" , "wp-crowdfunding" ).'</div>';
        $html .= '<div class="wpneo-fields">';
        $html .= '<input type="text" name="wpcf_predefined_pledge_amount" value="'.$pledge_amount.'">';
        $html .= '<small>'.__("Predefined amount allow you to place the amount in donate box by click, price should separated by comma (,), example: <code>10,20,30,40</code>","wp-crowdfunding").'</small>';
        $html .= '</div>';
        $html .= '</div>';

        $html .= apply_filters('wpcf_after_campaign_form_pledge_amount', isset($post_id) ? $post_id : 0 );

        //Show Contributor Table
        $html .= '<div class="wpneo-single">';
        $html .= '<div class="wpneo-name">'.__( "Contributor Table" , "wp-crowdfunding" ).'</div>';
        $html .= '<div class="wpneo-fields">';
        if( $type == '1' )
            $checked = 'checked="checked"';
        $html .= '<input type="checkbox" '.$checked.' name="wpneo-form-contributor-table" value="1" >'.__("Show contributor table on campaign single page","wp-crowdfunding" );
        $html .= '</div>';
        $html .= '</div>';

        //Mark Contributors as Anonymous
        $html .= '<div class="wpneo-single">';
        $html .= '<div class="wpneo-name">'.__( "Contributor Anonymity" , "wp-crowdfunding" ).'</div>';
        $html .= '<div class="wpneo-fields">';
        if( $contributor_show == '1' )
            $checked2 = 'checked="checked"';
        $html .= '<input type="checkbox" '.$checked2.' name="wpneo-form-contributor-show" value="1" >'.__("Make contributors anonymous on the contributor table","wp-crowdfunding" );
        $html .= '</div>';
        $html .= '</div>';

        //Country
        $html .= '<div class="wpneo-single">';
        $html .= '<div class="wpneo-name">'.__( "Country" , "wp-crowdfunding" ).'</div>';
        $html .= '<div class="wpneo-fields">';
        $countries_obj      = new \WC_Countries();
        $countries          = $countries_obj->__get('countries');
        array_unshift($countries, __('Select a country', 'wp-crowdfunding') );
        $html .= '<select name="wpneo-form-country">';
        foreach ($countries as $key=>$value) {
            if( $country==$key ){
                $html .= '<option selected="selected" value="'.$key.'">'.$value.'</option>';
            }else{
                $html .= '<option value="'.$key.'">'.$value.'</option>';
            }
        }
        $html .= '</select>';
        $html .= '<small>'.__("Select your country","wp-crowdfunding").'</small>';
        $html .= '</div>';
        $html .= '</div>';


        //Location
        $html .= '<div class="wpneo-single">';
        $html .= '<div class="wpneo-name">'.__( "Location" , "wp-crowdfunding" ).'</div>';
        $html .= '<div class="wpneo-fields">';
        $html .= '<input type="text" name="wpneo-form-location" value="'.$location.'" >';
        $html .= '<small>'.__("Put the campaign location here","wp-crowdfunding").'</small>';
        $html .= '</div>';
        $html .= '</div>';

        
        // Clone Field
        $reward = stripslashes($reward);
        $reward_data_array = json_decode($reward, true);
        $meta_count = is_array($reward_data_array) ? count($reward_data_array) : 0;
        $html .= '<div class="wpneo-reward-option">'.__("Reward Option","wp-crowdfunding").'</div>';
        $html .= '<div class="panel" id="reward_options">';

        $month_list = array(
            '1' => __( 'January' , 'wp-crowdfunding' ),
            '2' => __( 'February' , 'wp-crowdfunding' ),
            '3' => __( 'March' , 'wp-crowdfunding' ),
            '4' => __( 'April' , 'wp-crowdfunding' ),
            '5' => __( 'May' , 'wp-crowdfunding' ),
            '6' => __( 'June' , 'wp-crowdfunding' ),
            '7' => __( 'July' , 'wp-crowdfunding' ),
            '8' => __( 'August' , 'wp-crowdfunding' ),
            '9' => __( 'September' , 'wp-crowdfunding' ),
            '10' => __( 'October' , 'wp-crowdfunding' ),
            '11' => __( 'November' , 'wp-crowdfunding' ),
            '12' => __( 'December' , 'wp-crowdfunding' ),
        );
        if ($meta_count > 0) {
            if (is_array($reward_data_array) && !empty($reward_data_array)) {
                foreach ($reward_data_array as $k => $v) {
                    $html .=  "<div class='reward_group'>";
                    $html .=  "<div class='campaign_rewards_field_copy'>";

                    // Pledge Amount
                    $html .= '<div class="wpneo-single">';
                    $html .= '<div class="wpneo-name">'.__( "Pledge Amount" , "wp-crowdfunding" ).'</div>';
                    $html .= '<div class="wpneo-fields">';
                    $html .= '<input type="text" value="'.$v['wpneo_rewards_pladge_amount'].'" id="wpneo_rewards_pladge_amount[]" name="wpneo_rewards_pladge_amount[]" style="" class="wc_input_price">';
                    $html .= '<small>'.__("Put the pledge amount here","wp-crowdfunding").'</small>';
                    $html .= '</div>';
                    $html .= '</div>';

                    // Reward Image
                    $html .= '<div class="wpneo-single">';
                    $html .= '<div class="wpneo-name">'.__( "Reward Image" , "wp-crowdfunding" ).'</div>';
                    $html .= '<div class="wpneo-fields">';
                    $attachment_url = '';
                    if( $v['wpneo_rewards_image_field'] ){
                        $attachment_url = wp_get_attachment_url( $v['wpneo_rewards_image_field'] );
                    }
                    $html .= '<input type="text" name="wpneo_rewards_image_fields" class="wpneo-upload wpneo_rewards_image_field_url" value="'.$attachment_url.'">';
                    $html .= '<input type="hidden" name="wpneo_rewards_image_field[]" class="wpneo_rewards_image_field" value="'.$v['wpneo_rewards_image_field'].'">';
                    $html .= '<input type="button" id="cc-image-upload-file-button" class="wpneo-image-upload-btn float-right" value="'.__("Upload Image","wp-crowdfunding").'"/>';
                    $html .= '<small>'.__("Upload a reward image","wp-crowdfunding").'</small>';
                    $html .= '</div>';
                    $html .= '</div>';

                    // Reward
                    $html .= '<div class="wpneo-single form-field wpneo_rewards_description[]_field">';
                    $html .= '<div class="wpneo-name">'.__( "Reward" , "wp-crowdfunding" ).'</div>';
                    $html .= '<div class="wpneo-fields">';
                    $html .= '<textarea cols="20" rows="2" id="wpneo_rewards_description[]" name="wpneo_rewards_description[]" style="" class="short">'.$v['wpneo_rewards_description'].'</textarea>';
                    $html .= '<small>'.__("Put the reward description here","wp-crowdfunding").'</small>';
                    $html .= '</div>';
                    $html .= '</div>';

                    // Estimated Delivery Month
                    $html .= '<div class="wpneo-single wpneo-first-half">';
                    $html .= '<div class="wpneo-name">'.__( "Estimated Delivery Month" , "wp-crowdfunding" ).'</div>';
                    $html .= '<div class="wpneo-fields">';
                    $html .= '<select style="" class="select short" name="wpneo_rewards_endmonth[]" id="wpneo_rewards_endmonth[]">';
                    $html .= '<option value="">'.__('- Select -', 'wp-crowdfunding').'</option>';
                    foreach($month_list as $key => $val){
                        $month_key = strtolower(substr($val,0,3));
                        $selected = ($v['wpneo_rewards_endmonth'] == $month_key)? 'selected':'';
                        $html .= '<option value="'.$month_key.'" '.$selected.'>'.$val.'</option>';
                    }
                    $html .= '</select>';
                    $html .= '<small>'.__("Estimated Delivery Month of the Reward","wp-crowdfunding").'</small>';
                    $html .= '</div>';
                    $html .= '</div>';

                    // Estimated Delivery Year
                    $html .= '<div class="wpneo-single wpneo-second-half">';
                    $html .= '<div class="wpneo-name">'.__( "Estimated Delivery Year" , "wp-crowdfunding" ).'</div>';
                    $html .= '<div class="wpneo-fields">';
                    $html .= '<select style="" class="select short" name="wpneo_rewards_endyear[]" id="wpneo_rewards_endyear[]">';
                    $html .= '<option value=""> '.__('- Select -', 'wp-crowdfunding').' </option>';
                    for ($i=2019; $i<=2025; $i++){
                        $selected = ($v['wpneo_rewards_endyear'] == $i)? 'selected':'';
                        $html .= '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
                    }
                    $html .= '</select>';
                    $html .= '<small>'.__("Estimated Delivery Year of the Reward","wp-crowdfunding").'</small>';
                    $html .= '</div>';
                    $html .= '</div>';

                    // Quantity
                    $html .= '<div class="wpneo-single">';
                    $html .= '<div class="wpneo-name">'.__( "Quantity" , "wp-crowdfunding" ).'</div>';
                    $html .= '<div class="wpneo-fields">';
                    $html .= '<input type="text" value="'.$v['wpneo_rewards_item_limit'].'" id="wpneo_rewards_item_limit[]" name="wpneo_rewards_item_limit[]" style="" class="wc_input_price">';
                    $html .= '<small>'.__("Quantity of physical products","wp-crowdfunding").'</small>';
                    $html .= '</div>';
                    $html .= '</div>';

                    $html .= '<div class="wpneo-remove-button">';
                    $html .= '<input name="remove_rewards" type="button" class="button tagadd removeCampaignRewards text-right" value="' . __('- Remove', 'wp-crowdfunding') . '" />';
                    $html .= '</div>';

                    $html .=  "</div>";
                    $html .=  "</div>";
                }
            }
        } else {
            $html .= '<div class="reward_group" style="display: block;">';
            $html .= '<div class="campaign_rewards_field_copy">';

            // Pledge Amount
            $html .= '<div class="wpneo-single">';
            $html .= '<div class="wpneo-name">'.__( "Pledge Amount" , "wp-crowdfunding" ).'</div>';
            $html .= '<div class="wpneo-fields">';
            $html .= '<input type="text" value="" id="wpneo_rewards_pladge_amount[]" name="wpneo_rewards_pladge_amount[]" style="" class="wc_input_price">';
            $html .= '<small>'.__("Pledge Amount","wp-crowdfunding").'</small>';
            $html .= '</div>';
            $html .= '</div>';

            // Reward Image
            $html .= '<div class="wpneo-single">';
            $html .= '<div class="wpneo-name">'.__( "Reward Image" , "wp-crowdfunding" ).'</div>';
            $html .= '<div class="wpneo-fields">';
            $html .= '<input type="text" name="wpneo_rewards_image_fields" class="wpneo-upload wpneo_rewards_image_field_url" value="">';
            $html .= '<input type="hidden" name="wpneo_rewards_image_field[]" class="wpneo_rewards_image_field" value="">';
            $html .= '<input type="button" id="cc-image-upload-file-button" class="wpneo-image-upload-btn float-right" value="'.__("Upload Image","wp-crowdfunding").'"/>';
            $html .= '<small>'.__("Upload a reward image","wp-crowdfunding").'</small>';
            $html .= '</div>';
            $html .= '</div>';

            // Reward
            $html .= '<div class="wpneo-single form-field">';
            $html .= '<div class="wpneo-name">'.__( "Reward" , "wp-crowdfunding" ).'</div>';
            $html .= '<div class="wpneo-fields float-right">';
            $html .= '<textarea cols="20" rows="2" id="wpneo_rewards_description[]" name="wpneo_rewards_description[]" style="" class="short"></textarea>';
            $html .= '<small>'.__("Reward Description","wp-crowdfunding").'</small>';
            $html .= '</div>';
            $html .= '</div>';


            // Estimated Delivery Month
            $html .= '<div class="wpneo-single wpneo-first-half">';
            $html .= '<div class="wpneo-name">'.__( "Estimated Delivery Month" , "wp-crowdfunding" ).'</div>';
            $html .= '<div class="wpneo-fields">';
            $html .= '<select style="" class="select short" name="wpneo_rewards_endmonth[]" id="wpneo_rewards_endmonth[]">';
            $html .= '<option selected="selected" value="">'.__('- Select -', 'wp-crowdfunding').'</option>';
            foreach( $month_list as $key => $val){
                $html .= '<option value="'.strtolower(substr($val,0,3)).'">'.$val.'</option>';
            }
            $html .= '</select>';
            $html .= '<small>'.__("Estimated Delivery Month of the Reward","wp-crowdfunding").'</small>';
            $html .= '</div>';
            $html .= '</div>';

            // Estimated Delivery Year
            $html .= '<div class="wpneo-single wpneo-second-half">';
            $html .= '<div class="wpneo-name">'.__( "Estimated Delivery Year" , "wp-crowdfunding" ).'</div>';
            $html .= '<div class="wpneo-fields">';
            $html .= '<select style="" class="select short" name="wpneo_rewards_endyear[]" id="wpneo_rewards_endyear[]">';
            $html .= '<option selected="selected" value="">'.__('- Select -', 'wp-crowdfunding').'</option>';
            $html .= '<option value="2019">2019</option>';
            $html .= '<option value="2020">2020</option>';
            $html .= '<option value="2021">2021</option>';
            $html .= '<option value="2022">2022</option>';
            $html .= '<option value="2023">2023</option>';
            $html .= '<option value="2024">2024</option>';
            $html .= '<option value="2025">2025</option>';
            $html .= '</select>';
            $html .= '<small>'.__("Estimated Delivery Year of the Reward","wp-crowdfunding").'</small>';
            $html .= '</div>';
            $html .= '</div>';

            // Quantity
            $html .= '<div class="wpneo-single">';
            $html .= '<div class="wpneo-name">'.__( "Quantity" , "wp-crowdfunding" ).'</div>';
            $html .= '<div class="wpneo-fields">';
            $html .= '<input type="text" value="" id="wpneo_rewards_item_limit[]" name="wpneo_rewards_item_limit[]" style="" class="wc_input_price">';
            $html .= '<small>'.__("Quantity of physical products","wp-crowdfunding").'</small>';
            $html .= '</div>';
            $html .= '</div>';

            $html .= '<div class="wpneo-remove-button">';
            $html .= '<input type="button" value="'.__("- Remove","wp-crowdfunding").'" class="button tagadd removeCampaignRewards" name="remove_rewards" style="display: none;">';
            $html .= '</div>';

            $html .= '</div>';

            $html .= '</div>';
        }

        if ( wpcf_function()->is_free() ) {
            $html .= '<div style="clear: both;"></div>';
            if(is_admin()){
                $html .= '<p><i> ' . __('pro version is required to add more than 1 reward', 'wp-crowdfunding') . '. <a href="https://www.themeum.com/product/wp-crowdfunding-plugin/?utm_source=crowdfunding_plugin" target="_blank">' . __('click here to get pro version', 'wp-crowdfunding') . '</a></i></p>';
            }
        } else {
            $html .= '<div id="rewards_addon_fields"></div>';
            $html .= '<div class="text-right">';
            $html .= '<input type="button" value="' . __("+ Add", "wp-crowdfunding") . '" id="addreward" class="button tagadd" name="save">';
            $html .= '</div>';
        }

        $html .= '</div>';
        
        // Clone Field
        $html .= $edit_form;
        $html .= $edit_id;

        $html .= apply_filters('wpcf_before_closing_crowdfunding_campaign_form', '' );

        $requirement_title = get_option( 'wpneo_requirement_title', '' );
        $requirement_text = get_option( 'wpneo_requirement_text', '' );
        $requirement_agree_title = get_option( 'wpneo_requirement_agree_title', '' );
        $html .= '<div class="wpneo-title">'.$requirement_title.'</div>';
        $html .= '<div class="wpneo-text">'.$requirement_text.'</div>';
        $html .= '<div class="wpneo-requirement-title"><input id="wpcf-term-agree" type="checkbox" value="agree" name="wpneo_terms_agree" /> <label for="wpcf-term-agree">'.$requirement_agree_title.'</label></div>';

        $var = get_option( 'wpneo_crowdfunding_dashboard_page_id', '' );
        if( $var != '' ){
            $var = get_permalink( $var );
        }else{
            $var = get_home_url();
        }
        $html .= '<div class="wpneo-form-action">';
        $html .= '<input type="hidden" name="action" value="addfrontenddata"/>';
        $html .= '<input type="submit" class="wpneo-submit-campaign" value="'.__("Submit campaign","wp-crowdfunding").'">';
        $html .= '<a href="'.$var.'" class="wpneo-cancel-campaign">'.__("Cancel","wp-crowdfunding").'</a>';
        $html .= '</div>';

        $html .= wp_nonce_field( 'wpcf_form_action', 'wpcf_form_action_field', true, false );

        $html .= '</form>';

        return $html;
    }
}


