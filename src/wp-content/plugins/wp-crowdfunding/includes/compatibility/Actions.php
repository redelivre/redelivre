<?php
defined( 'ABSPATH' ) || exit;

//TEMPLATE HOOKS ACTIONS
add_action( 'wpneo_before_crowdfunding_single_campaign_summary',    array( $template_hook_obj, 'campaign_single_feature_image' ) );
add_action( 'wpneo_crowdfunding_after_feature_img',                 array( $template_hook_obj, 'campaign_single_description' ) );
add_action( 'wpneo_crowdfunding_single_campaign_summary',           array( $template_hook_obj, 'single_campaign_summary' ) );
add_action( 'wpneo_crowdfunding_default_single_campaign_tabs',      array( $template_hook_obj, 'single_campaign_tabs' ), 10 );
add_action( 'wpneo_after_crowdfunding_single_campaign_summary',     array( $template_hook_obj, 'campaign_single_tab' ) );

add_action( 'wpneo_campaign_story_right_sidebar',                   array( $template_hook_obj, 'story_right_sidebar' ) );
add_action( 'wpneo_campaign_loop_item_before_content',              array( $template_hook_obj, 'loop_item_thumbnail' ) );

add_action( 'wpneo_campaign_loop_item_content',                     array( $template_hook_obj, 'campaign_loop_item_content' ) );
add_action( 'wpneo_dashboard_campaign_loop_item_content',           array( $template_hook_obj, 'dashboard_campaign_loop_item_content' ) );
add_action( 'wpneo_dashboard_campaign_loop_item_before_content',    array( $template_hook_obj, 'loop_item_thumbnail' ) );

//TEMPLATING
add_action( 'wpneo_cf_select_theme',  array( $templating_obj, 'selected_theme_callback' ) );