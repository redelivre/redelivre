<?php
defined( 'ABSPATH' ) || exit;

add_action('wpcf_campaign_listing_before_loop', 'campaign_listing_by_author_before_loop');
function campaign_listing_by_author_before_loop(){
	if (! empty($_GET['author'])) {
		echo '<h3>'.__('Campaigns by: ', 'wp-crowdfunding').' '.wpcf_function()->author_name_by_login(sanitize_text_field(trim($_GET['author']))).'</h3>';
	}
}

function wpcf_campaign_order_number_data( $min_data, $max_data, $post_id ){
	global $woocommerce, $wpdb;
	$query  =   "SELECT 
                    COUNT(p.ID)
                FROM 
                    {$wpdb->prefix}posts as p,
                    {$wpdb->prefix}woocommerce_order_items as i,
                    {$wpdb->prefix}woocommerce_order_itemmeta as im
                WHERE 
                    p.post_type='shop_order' 
                    AND p.post_status='wc-completed' 
                    AND i.order_id=p.ID 
                    AND i.order_item_id = im.order_item_id
                    AND im.meta_key='_product_id' 
                    AND im.order_item_id IN (
                                            SELECT 
                                                DISTINCT order_item_id 
                                            FROM 
                                                {$wpdb->prefix}woocommerce_order_itemmeta 
                                            WHERE 
                                                meta_key = '_line_total' 
                                                AND meta_value 
                                                    BETWEEN 
                                                        {$min_data} 
                                                        AND {$max_data}
                                            )
                    AND im.meta_value={$post_id}";
	$orders = $wpdb->get_var( $query );
	return $orders;
}

// Bio Data View
add_action( 'wp_ajax_nopriv_wpcf_bio_action', 'wpcf_bio_campaign_action' );
add_action( 'wp_ajax_wpcf_bio_action', 'wpcf_bio_campaign_action' );
function wpcf_bio_campaign_action(){
	$html = '';
	$author         = sanitize_text_field($_POST['author']);
	if( $author ){

		$user_info      = get_user_meta($author);
		$creator        = get_user_by('id', $author);
		$html .= '<div  class="wpneo-profile">';
		if( $creator->ID ){
			$img_src = '';
			$image_id = get_user_meta( $creator->ID , 'profile_image_id', true );
			if( $image_id != '' ){
				$img_src = wp_get_attachment_image_src( $image_id, 'full' );
				$img_src = $img_src[0];
			}
			if (!empty($img_src)){
				$html .= '<img width="105" height="105" class="profile-avatar" srcset="'.$img_src.'" alt="">';
			}
		}
		$html .= '</div>';
		$html .= '<div class="wpneo-profile">';
		$html .= '<div class="wpneo-profile-name"><a href="'.wpcf_function()->campaign_url($creator->ID).'">'.wpcf_function()->get_author_name().'</a></div>';
		$location = wpcf_function()->campaign_location();
		if ($location){
			$html .= '<div class="wpneo-profile-location">';
			$html .= '<i class="wpneo-icon wpneo-icon-location"></i> <span>'.$location.'</span>';
			$html .= '</div>';
		}
		$html .= '<div class="wpneo-profile-campaigns">'.wpcf_function()->author_campaigns($author)->post_count.__( " Campaigns" , "wp-crowdfunding" ).' | '.wpcf_function()->loved_count().__( " Loved campaigns" , "wp-crowdfunding" ).'</div>';
		$html .= '</div>';

		if ( ! empty($user_info['profile_about'][0])){
			$html .= '<div class="wpneo-profile-about">';
			$html .= '<h3>'.__("Profile Information","wp-crowdfunding").'</h3>';
			$html .= '<p>'.$user_info['profile_about'][0].'</p>';
			$html .= '</div>';
		}

		if ( ! empty($user_info['profile_portfolio'][0])){
			$html .= '<div class="wpneo-profile-about">';
			$html .= '<h3>'.__("Portfolio","wp-crowdfunding").'</h3>';
			$html .= '<p>'.$user_info['profile_portfolio'][0].'</p>';
			$html .= '</div>';
		}

		$html .= '<div class="wpneo-profile-about">';
		$html .= '<h3>'.__("Contact Info","wp-crowdfunding").'</h3>';
		if ( ! empty($user_info['profile_email1'][0])){
			$html .= '<p>'.__("Email: ","wp-crowdfunding").$user_info['profile_email1'][0].'</p>';
		}
		if ( ! empty($user_info['profile_mobile1'][0])){
			$html .= '<p>'.__("Phone: ","wp-crowdfunding").$user_info['profile_mobile1'][0].'</p>';
		}
		if ( ! empty($user_info['profile_fax'][0])){
			$html .= '<p>'.__("Fax: ","wp-crowdfunding").$user_info['profile_fax'][0].'</p>';
		}
		if ( ! empty($user_info['profile_website'][0])){
			$html .= '<p>'.__("Website: ","wp-crowdfunding").' <a href="'.wpcf_function()->url($user_info['profile_website'][0]).'"> '.wpcf_function()->url($user_info['profile_website'][0]).' </a></p>';
		}
		if ( ! empty($user_info['profile_email1'][0])){
			$html .= '<a class="wpneo-profile-button" href="mailto:'.$user_info['profile_email1'][0].'" target="_top">'.__("Contact Me","wp-crowdfunding").'</a>';
		}
		$html .= '</div>';

		$html .= '<div class="wpneo-profile-about">';
		$html .= '<h3>'.__("Social Link","wp-crowdfunding").'</h3>';
		if ( ! empty($user_info['profile_facebook'][0])){
			$html .= '<a class="wpcf-social-link" href="'.$user_info["profile_facebook"][0].'"><i class="wpneo-icon wpneo-icon-facebook"></i></a>';
		}
		if ( ! empty($user_info['profile_twitter'][0])){
			$html .= '<a class="wpcf-social-link" href="'.$user_info["profile_twitter"][0].'"><i class="wpneo-icon wpneo-icon-twitter"></i></a>';
		}
		if ( ! empty($user_info['profile_vk'][0])){
			$html .= '<a class="wpcf-social-link" href="'.$user_info["profile_vk"][0].'"><i class="wpneo-icon wpneo-icon-gplus"></i></a>';
		}
		if ( ! empty($user_info['profile_linkedin'][0])){
			$html .= '<a class="wpcf-social-link" href="'.$user_info["profile_linkedin"][0].'"><i class="wpneo-icon wpneo-icon-linkedin"></i></a>';
		}
		if ( ! empty($user_info['profile_pinterest'][0])){
			$html .= '<a class="wpcf-social-link" href="'.$user_info["profile_pinterest"][0].'"><i class="wpneo-icon wpneo-icon-pinterest"></i></a>';
		}
		$html .= '</div>';

		$title = __("About the campaign creator","wp-crowdfunding");

		die(json_encode(array('success'=> 1, 'message' => $html, 'title' => $title )));
	}
}
