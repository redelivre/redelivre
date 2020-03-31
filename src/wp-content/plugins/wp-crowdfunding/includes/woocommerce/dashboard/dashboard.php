<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$current_user = wp_get_current_user();
?>
<?php

ob_start();
include_once WPCF_DIR_PATH.'includes/woocommerce/dashboard/wpcrowd-reports-chart.php';
$html .= ob_get_clean();
?>

<?php

$html .= '<div class="wpneo-row">';
    $html .= '<div class="wpneo-col6">';
    $html .= '<div class="wpneo-shadow wpneo-padding25 wpneo-clearfix">'; 
        $html .= '<h4>'.__( "My Campaigns" , "wp-crowdfunding" ).'</h4>';
        include_once WPCF_DIR_PATH.'includes/woocommerce/dashboard/dashboard-campaign.php';
    $html .= '</div>';//wpneo-shadow 
    $html .= '</div>';//wpneo-col6 
    $html .= '<div class="wpneo-col6">';

    ob_start();
    do_action('wpcf_dashboard_place_3');
    $html .= ob_get_clean();

    $html .= '<div class="wpneo-content wpneo-shadow wpneo-padding25 wpneo-clearfix">'; 
        $html .= '<form id="wpneo-dashboard-form" action="" method="" class="wpneo-form">';
                // User Name
                $html .= '<h4>'.__('My Information', 'wp-crowdfunding').'</h4>';
                $html .= '<div class="wpneo-single">';
                    $html .= '<div class="wpneo-name float-left">';
                        $html .= '<p>'.__( "Username:" , "wp-crowdfunding" ).'</p>';
                    $html .= '</div>';
                    $html .= '<div class="wpneo-fields float-right">';
                        $html .= '<input type="hidden" name="action" value="wpneo_dashboard_form">';
                        $html .= '<input type="text" name="username" value="'.$current_user->user_login.'" disabled>';
                    $html .= '</div>';
                $html .= '</div>';
            
                // Email Address
                $html .= '<div class="wpneo-single">';
                    $html .= '<div class="wpneo-name float-left">';
                        $html .= '<p>'.__( "Email:" , "wp-crowdfunding" ).'</p>';
                    $html .= '</div>';
                    $html .= '<div class="wpneo-fields float-right">';
                        $html .= '<input type="email" name="email" value="'.$current_user->user_email.'" disabled>';
                    $html .= '</div>';
                $html .= '</div>';

                // First Name
                $html .= '<div class="wpneo-single">';
                    $html .= '<div class="wpneo-name float-left">';
                        $html .= '<p>'.__( "First Name:" , "wp-crowdfunding" ).'</p>';
                    $html .= '</div>';
                    $html .= '<div class="wpneo-fields float-right">';
                        $html .= '<input type="text" name="firstname" value="'.$current_user->user_firstname.'" disabled>';
                    $html .= '</div>';
                $html .= '</div>';

                // Last Name
                $html .= '<div class="wpneo-single">';
                    $html .= '<div class="wpneo-name float-left">';
                        $html .= '<p>'.__( "Last Name:" , "wp-crowdfunding" ).'</p>';
                    $html .= '</div>';
                    $html .= '<div class="wpneo-fields float-right">';
                        $html .= '<input type="text" name="lastname" value="'.$current_user->user_lastname.'" disabled>';
                    $html .= '</div>';
                $html .= '</div>';

                // Website
                $html .= '<div class="wpneo-single">';
                    $html .= '<div class="wpneo-name float-left">';
                        $html .= '<p>'.__( "Website:" , "wp-crowdfunding" ).'</p>';
                    $html .= '</div>';
                    $html .= '<div class="wpneo-fields float-right">';
                        $html .= '<input type="text" name="website" value="'.$current_user->user_url.'" disabled>';
                    $html .= '</div>';
                $html .= '</div>';

                // Bio Info
                $html .= '<div class="wpneo-single">';
                    $html .= '<div class="wpneo-name float-left">';
                        $html .= '<p>'.__( "Bio:" , "wp-crowdfunding" ).'</p>';
                    $html .= '</div>';
                    $html .= '<div class="wpneo-fields float-right">';
                        $html .= '<textarea name="description" rows="3" disabled>'.$current_user->description.'</textarea>';
                    $html .= '</div>';
                $html .= '</div>';

            $html .= '<h4>'.__('Payment Info', 'wp-crowdfunding').'</h4>';
            ob_start();
            do_action('wpcf_dashboard_after_dashboard_form');
            $html .= ob_get_clean();

            $html .= wp_nonce_field( 'wpneo_crowdfunding_dashboard_form_action', 'wpneo_crowdfunding_dashboard_nonce_field', true, false );
            //Save Button
            $html .= '<div class="wpneo-buttons-group float-right">';
                $html .= '<button id="wpneo-edit" class="wpneo-edit-btn">'.__( "Edit" , "wp-crowdfunding" ).'</button>';
                $html .= '<button id="wpneo-dashboard-btn-cancel" class="wpneo-cancel-btn wpneo-hidden" type="submit">'.__( "Cancel" , "wp-crowdfunding" ).'</button>';
                $html .= '<button id="wpneo-dashboard-save" class="wpneo-save-btn wpneo-hidden" type="submit">'.__( "Save" , "wp-crowdfunding" ).'</button>';
            $html .= '</div>';
            $html .= '<div class="clear-float"></div>';
        $html .= '</form>';//#wpneo-dashboard-form
    $html .= '</div>';//wpneo-content
    $html .= '</div>';//wpneo-col6 
$html .= '</div>';//wpneo-row
