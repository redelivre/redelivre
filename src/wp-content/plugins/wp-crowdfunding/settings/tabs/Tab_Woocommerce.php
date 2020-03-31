<?php
defined( 'ABSPATH' ) || exit;
$pages = wpcf_function()->get_pages();
$page_array = array();
if (count($pages)>0) {
    foreach ($pages as $page) {
        $page_array[$page->ID] = $page->post_title;
    }
}
$pages = $page_array;


// #WooCommerce Settings (Tab Settings)
$arr =  array(
    // #Listing Page Seperator
    array(
        'type'      => 'seperator',
        'label'     => __('WooCommerce Settings','wp-crowdfunding'),
        'desc'      => __('All settings related to WooCommerce','wp-crowdfunding'),
        'top_line'  => 'true',
    ),

    // #Hide Crowdfunding Campaign From Shop Page
    array(
        'id'        => 'hide_cf_campaign_from_shop_page',
        'type'      => 'checkbox',
        'value'     => 'true',
        'label'     => __('Hide Crowdfunding Campaign From Shop Page','wp-crowdfunding'),
        'desc'      => __('Enable/Disable','wp-crowdfunding'),
    ),

    // #Product Single Page Fullwith
/*    array(
        'id'        => 'wpneo_single_page_id',
        'type'      => 'checkbox',
        'value'     => 'true',
        'label'     => __('Enable/Disable','wp-crowdfunding'),
        'desc'      => __('Crowdfunding Product Single Page Fullwith.','wp-crowdfunding'),
    ),*/


    // #Listing Page Select
    array(
        'id'        => 'hide_cf_address_from_checkout',
        'type'      => 'checkbox',
        'value'     => 'true',
        'label'     => __('Hide Billing Address From Checkout Page','wp-crowdfunding'),
        'desc'      => __('Enable/Disable','wp-crowdfunding'),
    ),

    // #Listing Page Select
    array(
        'id'        => 'wpneo_listing_page_id',
        'type'      => 'dropdown',
        'option'    => $pages,
        'label'     => __('Select Listing Page','wp-crowdfunding'),
        'desc'      => __('Select Crowdfunding Product Listing Page.','wp-crowdfunding'),
    ),

    // #Campaign Registration Page Select
    array(
        'id'        => 'wpneo_registration_page_id',
        'type'      => 'dropdown',
        'option'    => $pages,
        'label'     => __('Select Registration Page','wp-crowdfunding'),
        'desc'      => __('Select Crowdfunding Registration Page.','wp-crowdfunding'),
    ),

	// #Categories
	array(
		'type'      => 'seperator',
		'label'     => __('Categories Settings','wp-crowdfunding'),
		'desc'      => __('Exclude or include WooCommerce product categories.','wp-crowdfunding'),
		'top_line'  => 'true',
	),

	array(
		'id'        => 'seperate_crowdfunding_categories',
		'type'      => 'checkbox',
		'value'     => 'true',
		'label'     => __('Separate Crowdfunding Categories','wp-crowdfunding'),
		'desc'      => __('Enable/Disable','wp-crowdfunding'),
	),

    // #Listing Page Seperator
    array(
        'type'      => 'seperator',
        'label'     => __('Submit Form Text Settings','wp-crowdfunding'),
        'desc'      => __('All settings related to Submit Form Text.','wp-crowdfunding'),
        'top_line'  => 'true',
    ),

    // #Campaign Submit Form Requirement Title
    array(
        'id'        => 'wpneo_requirement_title',
        'type'      => 'text',
        'label'     => __('Submit Form Requirement Title','wp-crowdfunding'),
        'desc'      => __('Additional title for Submit Form Requirement Title goes here.','wp-crowdfunding'),
        'value'     => ''
    ),

    // #Campaign Submit Form Requirement Text
    array(
        'id'        => 'wpneo_requirement_text',
        'type'      => 'textarea',
        'value'     => '',
        'label'     => __('Submit Form Requirement Text','wp-crowdfunding'),
        'desc'      => __('Additional text for Submit Form Requirement goes here.','wp-crowdfunding'),
    ),

    // #Campaign Submit Form Requirement Agree Title
    array(
        'id'        => 'wpneo_requirement_agree_title',
        'type'      => 'text',
        'value'     => '',
        'label'     => __('Submit Form Agree Title','wp-crowdfunding'),
        'desc'      => __('The checkmark text for agreeing with terms and conditions.','wp-crowdfunding'),
    ),

    array(
        'id'        => 'wpneo_crowdfunding_add_to_cart_redirect',
        'type'      => 'radio',
        'option'    =>  array( 'checkout_page' => 'Checkout Page', 'cart_page' => 'Cart Page', 'none' => 'None' ) ,
        'label'     => __('Button Submit Action of "Back This Campaign" ','wp-crowdfunding'),
        'desc'      => __('This action will determine where to redirect after clicking on “Back This Campaign” button of campaign single page.','wp-crowdfunding'),
    ),

    // #Listing Page Seperator
    array(
        'type'      => 'seperator',
        'label'     => __('Listing Page Settings','wp-crowdfunding'),
        'top_line'  => 'true',
    ),

    // #Number of Columns in a Row
    array(
        'id'        => 'number_of_collumn_in_row',
        'type'      => 'dropdown',
        'option'    => array(
            '2' => __('2','wp-crowdfunding'),
            '3' => __('3','wp-crowdfunding'),
            '4' => __('4','wp-crowdfunding'),
        ),
        'label'     => __('Number of Columns in a Row','wp-crowdfunding'),
        'desc'      => __('Number of Columns in your Listing Page','wp-crowdfunding'),
    ),

    // #Number of Words Shown in Listing Description
    array(
        'id'        => 'number_of_words_show_in_listing_description',
        'type'      => 'number',
        'min'       => '1',
        'max'       => '',
        'value'     => '20',
        'label'     => __('Number of Words Shown in Listing Description','wp-crowdfunding'),
    ),

    // #Single Page Seperator
    array(
        'type'      => 'seperator',
        'label'     => __('Single Page Settings','wp-crowdfunding'),
        'top_line'  => 'true',
    ),

    //Load campaign in single page
    array(
        'id'        => 'wpneo_single_page_template',
        'type'      => 'radio',
        'option'    => array(
            'in_wp_crowdfunding' => __('In WP Crowdfunding own template','wp-crowdfunding'),
            'in_woocommerce' => __('In WooCommerce Default','wp-crowdfunding'),
        ),
        'label'     => __('Template for campaign single page','wp-crowdfunding'),
    ),

    // #Number of Columns in a Row
    array(
        'id'        => 'wpneo_single_page_reward_design',
        'type'      => 'dropdown',
        'option'    => array(
            '1' => __('1','wp-crowdfunding'),
            '2' => __('2','wp-crowdfunding'),
        ),
        'label'     => __('Select Style for Rewards','wp-crowdfunding'),
    ),

    // #Reward fixed price
    array(
        'id'        => 'wpneo_reward_fixed_price',
        'type'      => 'checkbox',
        'value'     => 'true',
        'label'     => __('Set fixed price instead of range on Rewards','wp-crowdfunding'),
        'desc'      => __('Enable/Disable','wp-crowdfunding'),
    ),

	array(
		'type'      => 'seperator',
		'label'     => __('Tax Settings','wp-crowdfunding'),
		'top_line'  => 'true',
	),
	// #Reward fixed price
	array(
		'id'        => 'wpcf_enable_tax',
		'type'      => 'checkbox',
		'value'     => 'true',
		'label'     => __('Enable/Disable','wp-crowdfunding'),
		'desc'      => __('Enable Tax in Crowdfunding Products','wp-crowdfunding'),
	),

	// #Save Function
    array(
        'id'        => 'wpneo_crowdfunding_admin_tab',
        'type'      => 'hidden',
        'value'     => 'tab_woocommerce',
    ),
);
wpcf_function()->generator( apply_filters('wp_crowdfunding_wc_settings', $arr) );