<?php
defined( 'ABSPATH' ) || exit;

$post_id = (int) $_GET['postid'];
$saved_campaign_update = get_post_meta($post_id, 'wpneo_campaign_updates', true);
$saved_campaign_update_a = (array) json_decode($saved_campaign_update, true);

if(isset($_GET["postid"])){
    $post_author = get_post_field( 'post_author', $_GET["postid"] );
    if( $post_author == get_current_user_id() ){
        $var = get_post_meta( $_GET["postid"],"wpneo_campaign_updates",true );
    }
}

$data = get_user_meta(get_current_user_id());

$html .= '<div id="wpneo_update_form_wrapper" style="display: none;">';

    $html .= '<div class="wpneo-content">';
    $html .= '<form id="wpneo-dashboard-form" action="" method="" class="wpneo-form">';

    $display = 'block';
    if (count($saved_campaign_update_a) > 0) $display = 'none';
    
    $html .= '<div class="panel woocommerce_options_panel" id="campaign_status">';
    $html .= '<div class="wpneo-shadow wpneo-padding25 wpneo-clearfix">';
        
        $html .= '<div style="display: '.$display.'" id="campaign_update_field">';
            $html .= '<div class="campaign_update_field_copy">';
                $html .= '<p class="form-field wpneo_prject_update_date_field[]_field wpneo-single wpneo-first-half">';
                    $html .= '<label for="wpneo_prject_update_date_field[]">'.__("Date", "wp-crowdfunding").':</label>';
                    $html .= '<input type="text" placeholder="'.date('d-m-Y').'" value="" id="wpneo_prject_update_date_field[]" name="wpneo_prject_update_date_field[]" style="" class="datepicker">';
                $html .= '</p>';
                $html .= '<p class="form-field wpneo_prject_update_title_field[]_field wpneo-single wpneo-second-half">';
                    $html .= '<label for="wpneo_prject_update_title_field[]">'.__("Update Title", "wp-crowdfunding").':</label>';
                    $html .= '<input type="text" placeholder="'.__("Update Title", "wp-crowdfunding").'" value="" id="wpneo_prject_update_title_field[]" name="wpneo_prject_update_title_field[]" style="" class="short">';
                $html .= '</p>';
                $html .= '<p class="form-field wpneo_prject_update_details_field[]_field wpneo-single">';
                    $html .= '<label for="wpneo_prject_update_details_field[]">'.__("Update Details", "wp-crowdfunding").':</label>';
                    $html .= '<textarea cols="20" rows="2" placeholder="'.__("Update Details", "wp-crowdfunding").'" id="wpneo_prject_update_details_field[]" name="wpneo_prject_update_details_field[]" style="" class="short"></textarea>';
                $html .= '</p>';
                $html .= '<input type="button" value="'.__('Remove', 'wp-crowdfunding').'" class="button tagadd removecampaignupdate" name="remove_udpate" style="display: none;">';
            $html .= '</div>';
        $html .= '</div>';

        $html .= '<div id="campaign_update_addon_field">';
            if ( count($saved_campaign_update_a) > 0 ){
                foreach( $saved_campaign_update_a as $key => $value ){
                    $html .= '<div class="campaign_update_field_copy">';
                        $html .= '<p class="form-field wpneo_prject_update_date_field[]_field wpneo-single wpneo-first-half">';
                            $html .= '<label for="wpneo_prject_update_date_field[]">'.__("Date", "wp-crowdfunding").':</label>';
                            $html .= '<input type="text" placeholder="'.date('d-m-Y').'" value="'.esc_attr($value['date']).'" id="wpneo_prject_update_date_field[]" name="wpneo_prject_update_date_field[]" style="" class="datepicker">';
                        $html .= '</p>';
                        $html .= '<p class="form-field wpneo_prject_update_title_field[]_field wpneo-single wpneo-second-half">';
                            $html .= '<label for="wpneo_prject_update_title_field[]">'.__("Update Title", "wp-crowdfunding").':</label>';
                            $html .= '<input type="text" placeholder="'.__("Update Title", "wp-crowdfunding").'" value="'.esc_attr($value['title']).'" id="wpneo_prject_update_title_field[]" name="wpneo_prject_update_title_field[]" style="" class="short">';
                        $html .= '</p>';
                        $html .= '<p class="form-field wpneo_prject_update_details_field[]_field wpneo-single">';
                            $html .= '<label for="wpneo_prject_update_details_field[]">'.__("Update Details", "wp-crowdfunding").':</label>';
                            $html .= '<textarea cols="20" rows="2" placeholder="'.__("Update Details", "wp-crowdfunding").'" id="wpneo_prject_update_details_field[]" name="wpneo_prject_update_details_field[]" style="" class="short" >'.esc_textarea($value['details']).'</textarea>';
                        $html .= '</p>';
                        $html .= '<input type="button" value="'.__('Remove', 'wp-crowdfunding').'" class="button tagadd removecampaignupdate" name="remove_udpate" style="display: none;">';
                    $html .= '</div>';
                }
            }
        $html .= '</div>';

        $html .= '<input type="button" value="+ '.__('Add Update', 'wp-crowdfunding').'" id="addcampaignupdate" class="button tagadd" name="save_update">';
        $html .= '<div style="clear: both;"></div>';
    $html .= '</div>';

    $html .= '<input type="hidden"  value="wpneo_update_status_save" name="action" />';
    $html .= '<input type="hidden"  value="'. intval(esc_attr($post_id)) .'" name="postid" />';
    $html .= '</div>';//wpneo-padding25
    //Save Button
    $html .= '<div class="wpneo-buttons-group float-right">';
    $html .= '<button id="wpneo-update-save" class="wpneo-save-btn" type="submit">'.__( "Save" , "wp-crowdfunding" ).'</button>';
    $html .= '</div>';
    $html .= '<div class="clear-float"></div>';

    $html .= wp_nonce_field( 'wpcf_form_action', 'wpcf_form_action_field', true, false );

    $html .= '</form>';
    $html .= '</div>';
$html .='</div>'; //update_form_wrapper


$html .= '<div id="wpneo_update_display_wrapper">';
    if (count($saved_campaign_update_a) > 0){
        $html .= '<div class="wpneo-form">';
            $html .='<div class="wpneo-shadow wpneo-padding25 wpneo-clearfix">';
                $html .= '<table class="stripe-table">';
                    $html .= '<thead>';
                        $html .= '<tr>';
                            $html .= '<th>'.__('Date', 'wp-crowdfunding').'</th>';
                            $html .= '<th>'.__('Title', 'wp-crowdfunding').'</th>';
                        $html .= '</tr>';
                    $html .= '</thead>';
                    $html .= '<tbody>';
                        foreach($saved_campaign_update_a as $key => $value){
                            $html .= '<tr>';
                                $html .= '<td>'.$value['date'].'</td>';
                                $html .= '<td>'.$value['title'].'</td>';
                            $html .= '</tr>';
                        }
                    $html .= '</tbody>';
                $html .= '</table>';
            $html .= '</div>';
            $html .= '<input type="button" value="'.__('Add Update', 'wp-crowdfunding').'" id="wpneo_active_edit_form" class="button tagadd" name="save_update">';
        $html .= '</div>';

    } else {
        $html .= '<div class="wpneo-form">';
            $html .= '<input type="button" value="'.__('Add Update', 'wp-crowdfunding').'" id="wpneo_active_edit_form" class="button tagadd" name="save_update">';
        $html .= '</div>';
    }
$html .='</div>'; //wpneo_update_display_wrapper
