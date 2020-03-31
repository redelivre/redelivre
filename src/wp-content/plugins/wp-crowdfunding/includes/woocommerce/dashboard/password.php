<?php
defined( 'ABSPATH' ) || exit;

$html .= '<div class="wpneo-content">';
    $html .= '<form id="wpneo-dashboard-form" action="" method="post" class="wpneo-form">';

        $html .= '<div class="wpneo-shadow wpneo-padding25 wpneo-clearfix">';
            // Current Password
            $html .= '<div class="wpneo-single">';
                $html .= '<div class="wpneo-name float-left">';
                    $html .= '<p>'.__( "Current Password" , "wp-crowdfunding" ).'</p>';
                $html .= '</div>';
                $html .= '<div class="wpneo-fields float-right">';
                    $html .= '<input type="hidden" name="action" value="wpneo_password_form">';
                    $html .= '<input type="password" name="password" value="" autocomplete="off">';
                $html .= '</div>';
            $html .= '</div>';

            // New Password
            $html .= '<div class="wpneo-single">';
                $html .= '<div class="wpneo-name float-left">';
                    $html .= '<p>'.__( "New Password" , "wp-crowdfunding" ).'</p>';
                $html .= '</div>';
                $html .= '<div class="wpneo-fields float-right">';
                    $html .= '<input type="password" name="new-password" value="" autocomplete="off">';
                $html .= '</div>';
            $html .= '</div>';

            // Retype Password
            $html .= '<div class="wpneo-single">';
                $html .= '<div class="wpneo-name float-left">';
                $html .= '<p>'.__( "Retype Password" , "wp-crowdfunding" ).'</p>';
                $html .= '</div>';
                $html .= '<div class="wpneo-fields float-right">';
                $html .= '<input type="password" name="retype-password" value="" autocomplete="off">';
                $html .= '</div>';
            $html .= '</div>';
        $html .= '</div>';//wpneo-shadow


        $html .= wp_nonce_field( 'wpneo_crowdfunding_dashboard_form_action', 'wpneo_crowdfunding_dashboard_nonce_field', true, false );

        //Save Button
        $html .= '<div class="wpneo-buttons-group float-right">';
            $html .= '<button id="wpneo-dashboard-btn-cancel" class="wpneo-cancel-btn wpneo-hidden" type="submit">'.__( "Cancel" , "wp-crowdfunding" ).'</button>';
            $html .= '<button id="wpneo-password-save" class="wpneo-save-btn" type="submit">'.__( "Save" , "wp-crowdfunding" ).'</button>';
        $html .= '</div>';
        $html .= '<div class="clear-float"></div>';

    $html .= '</form>';
$html .= '</div>';
