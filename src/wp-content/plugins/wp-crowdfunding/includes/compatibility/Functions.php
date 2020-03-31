<?php
defined( 'ABSPATH' ) || exit;

function wpneo_crowdfunding_get_author_name(){
    return wpcf_function()->get_author_name();
}

function author_name_by_login($author_login){
    return wpcf_function()->author_name_by_login($author_login);
}

function get_wpcf_author_campaigns_url($author_id = 0, $author_nicename = '') {
    wpcf_function()->campaign_url( $author_id, $author_nicename );
}

function wpneo_crowdfunding_get_campaigns_location(){
    return wpcf_function()->campaign_location();
}

function wpneo_crowdfunding_get_total_fund_raised_by_campaign($campaign_id = 0){
    return wpcf_function()->fund_raised($campaign_id);
}

function wpneo_crowdfunding_get_total_goal_by_campaign($campaign_id){
    return wpcf_function()->total_goal($campaign_id);
}

function wpneo_crowdfunding_price($price, $args = array()){
    return wpcf_function()->price( $price, $args = array() );
}

function wpneo_loved_campaign_count($user_id = 0){
    return wpcf_function()->loved_count($user_id);
}
function is_campaign_loved_html($user_id = 0){
    return wpcf_function()->campaign_loved($user_id);
}

function wpneo_crowdfunding_wc_login_form(){
    return wpcf_function()->login_form();
}

function wpneo_crowdfunding_author_all_campaigns($author_id = 0){
    return wpcf_function()->author_campaigns( $author_id );
}

function wpneo_crowdfunding_add_http($url){
    return wpcf_function()->url($url);
}

function wpneo_crowdfunding_embeded_video($url){
    return wpcf_function()->get_embeded_video( $url );
}

function wpneo_crowdfunding_campaign_listing_by_author_url($user_login){
    return wpcf_function()->get_author_url( $user_login );
}

function wpneo_crowdfunding_load_template($template = '404'){
    return wpcf_function()->template($template);
}

function wpneo_crowdfunding_pagination($page_numb, $max_page) {
    return wpcf_function()->get_pagination($page_numb, $max_page);
}

function wpneo_wc_version_check($version = '3.0') {
    return wpcf_function()->wc_version($version = '3.0');
}

function wpneo_crowdfunding_campaign_single_love_this() {
    return wpcf_function()->campaign_single_love_this();
}

function WPNEOCF() {
    return wpcf_function();
}