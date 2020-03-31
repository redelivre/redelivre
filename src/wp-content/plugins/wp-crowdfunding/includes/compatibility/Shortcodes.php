<?php
defined( 'ABSPATH' ) || exit;

//SHORTCODES
add_shortcode( 'wp_crowdfunding_campaign_box',          array( $wpcf_campaign_box, 'campaign_box_callback' ) );
add_shortcode( 'wpneo_crowdfunding_dashboard',          array( $wpcf_dashboard, 'dashboard_callback' ) );
add_shortcode( 'wpneo_crowdfunding_listing',            array( $wpcf_project_listing, 'listing_callback' ) );
add_shortcode( 'wpneo_crowdfunding_form',               array( $wpcf_campaign_submit_from, 'campaign_form_callback' ) );
add_shortcode( 'wpneo_search_shortcode',                array( $wpcf_search_box, 'search_callback' ) );
add_shortcode( 'wpneo_registration',                    array( $wpcf_registraion, 'registration_callback' ) );
add_shortcode( 'wp_crowdfunding_single_campaign',       array( $wpcf_single_campaign, 'single_campaign_callback' ) );
add_shortcode( 'wp_crowdfunding_donate',                array( $wpcf_donate, 'donate_callback' ) );
add_shortcode( 'wp_crowdfunding_popular_campaigns',     array( $wpcf_popular_campaign, 'popular_campaigns_callback' ) );
