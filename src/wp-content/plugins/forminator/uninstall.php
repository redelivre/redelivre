<?php
/**
 * Forminator Uninstall methods
 * Called when plugin is deleted
 *
 * @since 1.0.2
 */

// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

/**
 * Drop custom tables
 *
 * @since 1.0.2
 */
function forminator_drop_custom_tables() {
	global $wpdb;
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}frmt_form_entry" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}frmt_form_entry_meta" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}frmt_form_views" );
}

/**
 * Clear custom posts
 *
 * @since 1.0.2
 */
function forminator_delete_custom_posts() {
	global $wpdb;
	//Now we delete the custom posts
	$forms_sql        = "SELECT GROUP_CONCAT(`ID`) FROM {$wpdb->posts} WHERE `post_type` = %s";
	$delete_forms_sql = "DELETE FROM {$wpdb->posts} WHERE `post_type` = %s";
	$form_types       = array(
		'forminator_forms',
		'forminator_polls',
		'forminator_quizzes',
	);
	foreach ( $form_types as $type ) {
		$ids = $wpdb->get_var( $wpdb->prepare( $forms_sql, $type ) ); // WPCS: unprepared SQL ok. false positive
		if ( $ids ) {

			$array_ids = explode( ',', $ids );
			foreach ( $array_ids as $array_id ) {
				wp_cache_delete( $array_id, 'forminator_total_entries' );
			}

			$delete_form_meta_sql = "DELETE FROM {$wpdb->postmeta} WHERE `post_id` in($ids)";
			$wpdb->query( $delete_form_meta_sql ); // WPCS: unprepared SQL ok. false positive. no need to prepared since all param are not user defined
		}
		$wpdb->query( $wpdb->prepare( $delete_forms_sql, $type ) ); // WPCS: unprepared SQL ok. false positive
	}
}


/**
 * Delete custom options
 *
 * @since 1.0.2
 * @since 1.0.6 Delete privacy options
 */
function forminator_delete_custom_options() {
	delete_option( "forminator_pagination_listings" );
	delete_option( "forminator_pagination_entries" );
	delete_option( "forminator_captcha_key" );
	delete_option( "forminator_captcha_secret" );
	delete_option( "forminator_v2_captcha_key" );
	delete_option( "forminator_v2_captcha_secret" );
	delete_option( "forminator_v2_invisible_captcha_key" );
	delete_option( "forminator_v2_invisible_captcha_secret" );
	delete_option( "forminator_v3_captcha_key" );
	delete_option( "forminator_v3_captcha_secret" );
	delete_option( "forminator_captcha_language" );
	delete_option( "forminator_captcha_theme" );
	delete_option( "forminator_welcome_dismissed" );
	delete_option( "forminator_version" );
	delete_option( "forminator_retain_votes_interval_number" );
	delete_option( "forminator_retain_votes_interval_unit" );
	delete_option( "forminator_retain_submissions_interval_number" );
	delete_option( "forminator_retain_submissions_interval_unit" );
	delete_option( "forminator_enable_erasure_request_erase_form_submissions" );
	delete_option( "forminator_form_privacy_settings" );
	delete_option( "forminator_poll_privacy_settings" );
	delete_option( "forminator_retain_ip_interval_number" );
	delete_option( "forminator_retain_ip_interval_unit" );
	delete_option( "forminator_retain_poll_submissions_interval_number" );
	delete_option( "forminator_retain_poll_submissions_interval_unit" );
	delete_option( "forminator_posts_map" );
	delete_option( "forminator_module_enable_load_ajax" );
	delete_option( "forminator_module_use_donotcachepage" );
	delete_option( "forminator_retain_quiz_submissions_interval_number" );
	delete_option( "forminator_retain_quiz_submissions_interval_unit" );
	delete_option( "forminator_dashboard_settings" );
	delete_option( "forminator_sender_email_address" );
	delete_option( "forminator_sender_name" );
	delete_option( "forminator_enable_accessibility" );
	delete_option( "forminator_entries_export_schedule" );
	delete_option( "forminator_paypal_api_mode" );
	delete_option( "forminator_paypal_secret" );
	delete_option( "forminator_currency" );
	delete_option( "forminator_exporter_log" );
	delete_option( "forminator_uninstall_clear_data" );
	delete_option( "forminator_stripe_configuration" );
	delete_option( "forminator_paypal_configuration" );
}

/**
 * Delete options created by Packaged Forminator Addons
 *
 * @since 1.4
 */
function forminator_delete_addon_options() {
	delete_option( 'forminator_activated_addons' );
	$addon_slugs = array(
		'activecampaign',
		'aweber',
		'campaignmonitor',
		'googlesheet',
		'mailchimp',
		'slack',
		'trello',
		'zapier',
	);

	foreach ( $addon_slugs as $addon_slug ) {
		delete_option( "forminator_addon_{$addon_slug}_version" );
		delete_option( "forminator_addon_{$addon_slug}_settings" );
	}
}

function forminator_clear_module_views() {
	global $wpdb;
	$wpdb->query( "TRUNCATE {$wpdb->prefix}frmt_form_views" );
}

function forminator_clear_module_submissions() {
	global $wpdb;

	$max_entry_id_query = "SELECT MAX(`entry_id`) FROM {$wpdb->prefix}frmt_form_entry";
	$max_entry_id       = $wpdb->get_var( $max_entry_id_query ); // phpcs:ignore

	if ( $max_entry_id && is_numeric( $max_entry_id ) && $max_entry_id > 0 ) {
		for ( $i = 1; $i <= $max_entry_id; $i ++ ) {
			wp_cache_delete( $i, 'Forminator_Form_Entry_Model' );
		}
	}

	$wpdb->query( "TRUNCATE {$wpdb->prefix}frmt_form_entry" );
	$wpdb->query( "TRUNCATE {$wpdb->prefix}frmt_form_entry_meta" );

	wp_cache_delete( 'all_form_types', 'forminator_total_entries' );
	wp_cache_delete( 'custom-forms' . '_form_type', 'forminator_total_entries' );
	wp_cache_delete( 'poll' . '_form_type', 'forminator_total_entries' );
	wp_cache_delete( 'quizzes' . '_form_type', 'forminator_total_entries' );
}

$uninstall_settings = array();

$forminator_uninstall = get_option( "forminator_uninstall_clear_data", false );
if ( $forminator_uninstall ) {
	// delete all
	$uninstall_settings = array(
		'settings' => true,
		'data'     => true,
	);
}

$delete_settings = isset( $uninstall_settings['settings'] ) ? $uninstall_settings['settings'] : false;
$delete_data     = isset( $uninstall_settings['data'] ) ? $uninstall_settings['data'] : false;

if ( $delete_settings ) {
	forminator_delete_custom_options();
	forminator_delete_addon_options();
	forminator_delete_custom_posts();
}

if ( $delete_data ) {
	forminator_clear_module_views();
	forminator_clear_module_submissions();
}

if ( $delete_settings && $delete_data ) {
	forminator_drop_custom_tables();
}
