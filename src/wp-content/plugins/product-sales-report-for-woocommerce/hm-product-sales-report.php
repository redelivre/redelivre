<?php
/**
 * Plugin Name: Product Sales Report for WooCommerce
 * Description: Generates a report on individual WooCommerce products sold during a specified time period.
 * Version: 1.4.10
 * Author: Potent Plugins
 * Author URI: http://potentplugins.com/?utm_source=product-sales-report-for-woocommerce&utm_medium=link&utm_campaign=wp-plugin-author-uri
 * License: GNU General Public License version 3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
 * WC tested up to: 4.0.0
 */
 
/*
    Product Sales Report for WooCommerce
    Copyright (C) 2020  Aspen Grove Studios

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

/* CREDITS:
 * This plugin contains code copied from and/or based on the following third-party products,
 * in addition to any others indicated in code comments or license files:
 *
 * WordPress, by Automattic, GPLv2+
 * WooCommerce, by Automattic, GPLv3+
 *
 * See licensing and copyright information in the ./license directory.
 * This file was modified by Jonathan Hall. Last modified 2020-06-04.
*/

// Add the Product Sales Report to the WordPress admin
add_action('admin_menu', 'hm_psrf_admin_menu');
function hm_psrf_admin_menu() {
	add_submenu_page('woocommerce', 'Product Sales Report', 'Product Sales Report', 'view_woocommerce_reports', 'hm_sbpf', 'hm_sbpf_page');
}

function hm_psrf_default_report_settings() {
	return array(
		'report_time' => '30d',
		'report_start' => date('Y-m-d', current_time('timestamp') - (86400 * 31)),
		'report_end' => date('Y-m-d', current_time('timestamp') - 86400),
		'order_statuses' => array('wc-processing', 'wc-on-hold', 'wc-completed'),
		'products' => 'all',
		'product_cats' => array(),
		'product_ids' => '',
		'variations' => 0,
		'orderby' => 'quantity',
		'orderdir' => 'desc',
		'fields' => array('product_id', 'product_sku', 'product_name', 'quantity_sold', 'gross_sales'),
		'limit_on' => 0,
		'limit' => 10,
		'include_header' => 1,
		'exclude_free' => 0
	);
}

// This function generates the Product Sales Report page HTML
function hm_sbpf_page() {

	$savedReportSettings = get_option('hm_psr_report_settings');
	if (isset($_POST['op']) && $_POST['op'] == 'preset-del' && !empty($_POST['r']) && isset($savedReportSettings[$_POST['r']])) {
		unset($savedReportSettings[$_POST['r']]);
		update_option('hm_psr_report_settings', $savedReportSettings);
		$_POST['r'] = 0;
		echo('<script type="text/javascript">location.href = location.href;</script>');
	}
	
	$reportSettings = (empty($savedReportSettings) ?
						hm_psrf_default_report_settings() :
						array_merge(hm_psrf_default_report_settings(),
								$savedReportSettings[
									isset($_POST['r']) && isset($savedReportSettings[$_POST['r']]) ? $_POST['r'] : 0
								]
						));
	
	// For backwards compatibility with pre-1.4 versions
	if (!empty($reportSettings['cat'])) {
		$reportSettings['products'] = 'cats';
		$reportSettings['product_cats'] = array($reportSettings['cat']);
	}
	
	$fieldOptions = array(
		'product_id' => 'Product ID',
		'variation_id' => 'Variation ID',
		'product_sku' => 'Product SKU',
		'product_name' => 'Product Name',
		'product_categories' => 'Product Categories',
		'variation_attributes' => 'Variation Attributes',
		'quantity_sold' => 'Quantity Sold',
		'gross_sales' => 'Gross Sales',
		'gross_after_discount' => 'Gross Sales (After Discounts)'
	);
		
	include(dirname(__FILE__).'/admin.php');
}

// Hook into WordPress init; this function performs report generation when
// the admin form is submitted
add_action('init', 'hm_sbpf_on_init', 9999);
function hm_sbpf_on_init() {
	global $pagenow;
	
	// Check if we are in admin and on the report page
	if (!is_admin())
		return;
	if ($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'hm_sbpf' && !empty($_POST['hm_sbp_do_export'])) {
		
		// Verify the nonce
		check_admin_referer('hm_sbpf_do_export');
		
		$newSettings = array_intersect_key($_POST, hm_psrf_default_report_settings());
		foreach ($newSettings as $key => $value)
			if (!is_array($value))
				$newSettings[$key] = htmlspecialchars($value);
		
		// Update the saved report settings
		$savedReportSettings = get_option('hm_psr_report_settings');
		$savedReportSettings[0] = array_merge(hm_psrf_default_report_settings(), $newSettings);
		

		update_option('hm_psr_report_settings', $savedReportSettings);
		
		// Check if no fields are selected or if not downloading
		if (empty($_POST['fields']) || empty($_POST['hm_sbp_download']))
			return;
		
		
		// Assemble the filename for the report download
		$filename =  'Product Sales - ';
		if (!empty($_POST['cat']) && is_numeric($_POST['cat'])) {
			$cat = get_term($_POST['cat'], 'product_cat');
			if (!empty($cat->name))
				$filename .= addslashes(html_entity_decode($cat->name)).' - ';
		}
		$filename .= date('Y-m-d', current_time('timestamp')).'.csv';
		
		// Send headers
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		
		// Output the report header row (if applicable) and body
		$stdout = fopen('php://output', 'w');
		if (!empty($_POST['include_header']))
			hm_sbpf_export_header($stdout);
		hm_sbpf_export_body($stdout);
		
		exit;
	}
}

// This function outputs the report header row
function hm_sbpf_export_header($dest, $return=false) {
	$header = array();
	
	foreach ($_POST['fields'] as $field) {
		switch ($field) {
			case 'product_id':
				$header[] = 'Product ID';
				break;
			case 'variation_id':
				$header[] = 'Variation ID';
				break;
			case 'product_sku':
				$header[] = 'Product SKU';
				break;
			case 'product_name':
				$header[] = 'Product Name';
				break;
			case 'variation_attributes':
				$header[] = 'Variation Attributes';
				break;
			case 'quantity_sold':
				$header[] = 'Quantity Sold';
				break;
			case 'gross_sales':
				$header[] = 'Gross Sales';
				break;
			case 'gross_after_discount':
				$header[] = 'Gross Sales (After Discounts)';
				break;
			case 'product_categories':
				$header[] = 'Product Categories';
				break;
		}
	}
	
	if ($return)
		return $header;
	fputcsv($dest, $header);
}

// This function generates and outputs the report body rows
function hm_sbpf_export_body($dest, $return=false) {
	global $woocommerce, $wpdb;
	
	$product_ids = array();
	if ($_POST['products'] == 'cats') {
		$cats = array();
		foreach ($_POST['product_cats'] as $cat)
			if (is_numeric($cat))
				$cats[] = $cat;
		$product_ids = get_objects_in_term($cats, 'product_cat');
	} else if ($_POST['products'] == 'ids') {
		foreach (explode(',', $_POST['product_ids']) as $productId) {
			$productId = trim($productId);
			if (is_numeric($productId))
				$product_ids[] = $productId;
		}
	}
	
	// Calculate report start and end dates (timestamps)
	switch ($_POST['report_time']) {
		case '0d':
			$end_date = strtotime('midnight', current_time('timestamp'));
			$start_date = $end_date;
			break;
		case '1d':
			$end_date = strtotime('midnight', current_time('timestamp')) - 86400;
			$start_date = $end_date;
			break;
		case '7d':
			$end_date = strtotime('midnight', current_time('timestamp')) - 86400;
			$start_date = $end_date - (86400 * 6);
			break;
		case '1cm':
			$start_date = strtotime(date('Y-m', current_time('timestamp')).'-01 midnight -1month');
			$end_date = strtotime('+1month', $start_date) - 86400;
			break;
		case '0cm':
			$start_date = strtotime(date('Y-m', current_time('timestamp')).'-01 midnight');
			$end_date = strtotime('+1month', $start_date) - 86400;
			break;
		case '+1cm':
			$start_date = strtotime(date('Y-m', current_time('timestamp')).'-01 midnight +1month');
			$end_date = strtotime('+1month', $start_date) - 86400;
			break;
		case '+7d':
			$start_date = strtotime('midnight', current_time('timestamp')) + 86400;
			$end_date = $start_date + (86400 * 6);
			break;
		case '+30d':
			$start_date = strtotime('midnight', current_time('timestamp')) + 86400;
			$end_date = $start_date + (86400 * 29);
			break;
		case 'custom':
			$end_date = strtotime('midnight', strtotime($_POST['report_end']));
			$start_date = strtotime('midnight', strtotime($_POST['report_start']));
			break;
		default: // 30 days is the default
			$end_date = strtotime('midnight', current_time('timestamp')) - 86400;
			$start_date = $end_date - (86400 * 29);
	}
	
	// Assemble order by string
	$orderby = (in_array($_POST['orderby'], array('product_id', 'gross', 'gross_after_discount')) ? $_POST['orderby'] : 'quantity');
	$orderby .= ' '.($_POST['orderdir'] == 'asc' ? 'ASC' : 'DESC');
	
	// Create a new WC_Admin_Report object
	include_once($woocommerce->plugin_path().'/includes/admin/reports/class-wc-admin-report.php');
	$wc_report = new WC_Admin_Report();
	$wc_report->start_date = $start_date;
	$wc_report->end_date = $end_date;
	
	//echo(date('Y-m-d', $end_date));
	
	$where_meta = array();
	if ($_POST['products'] != 'all') {
		$where_meta[] = array(
			'type' => 'order_item_meta',
			'meta_key' => '_product_id',
			'operator' => 'in',
			'meta_value' => $product_ids
		);
	}
	if (!empty($_POST['exclude_free'])) {
		$where_meta[] = array(
			'meta_key' => '_line_total',
			'meta_value' => 0,
			'operator' => '!=',
			'type' => 'order_item_meta'
		);
	}
	
	// Get report data
	
	// Avoid max join size error
	$wpdb->query('SET SQL_BIG_SELECTS=1');
	
	// Prevent plugins from overriding the order status filter
	add_filter('woocommerce_reports_order_statuses', 'hm_psrf_report_order_statuses', 9999);
	
	// Based on woocommerce/includes/admin/reports/class-wc-report-sales-by-product.php
	$sold_products = $wc_report->get_order_report_data(array(
		'data' => array(
			'_product_id' => array(
				'type' => 'order_item_meta',
				'order_item_type' => 'line_item',
				'function' => '',
				'name' => 'product_id'
			),
			'_qty' => array(
				'type' => 'order_item_meta',
				'order_item_type' => 'line_item',
				'function' => 'SUM',
				'name' => 'quantity'
			),
			'_line_subtotal' => array(
				'type' => 'order_item_meta',
				'order_item_type' => 'line_item',
				'function' => 'SUM',
				'name' => 'gross'
			),
			'_line_total' => array(
				'type' => 'order_item_meta',
				'order_item_type' => 'line_item',
				'function' => 'SUM',
				'name' => 'gross_after_discount'
			)
		),
		'query_type' => 'get_results',
		'group_by' => 'product_id',
		'where_meta' => $where_meta,
		'order_by' => $orderby,
		'limit' => (!empty($_POST['limit_on']) && is_numeric($_POST['limit']) ? $_POST['limit'] : ''),
		'filter_range' => ($_POST['report_time'] != 'all'),
		'order_types' => wc_get_order_types(),
		'order_status' => hm_psrf_report_order_statuses()
	));
	
	// Remove report order statuses filter
	remove_filter('woocommerce_reports_order_statuses', 'hm_psrf_report_order_statuses', 9999);
	
	if ($return)
		$rows = array();

	// Output report rows
	foreach ($sold_products as $product) {
		$row = array();
		
		foreach ($_POST['fields'] as $field) {
			switch ($field) {
				case 'product_id':
					$row[] = $product->product_id;
					break;
				case 'variation_id':
					$row[] = (empty($product->variation_id) ? '' : $product->variation_id);
					break;
				case 'product_sku':
					$row[] = get_post_meta($product->product_id, '_sku', true);
					break;
				case 'product_name':
					$row[] = html_entity_decode(get_the_title($product->product_id));
					break;
				case 'quantity_sold':
					$row[] = $product->quantity;
					break;
				case 'gross_sales':
					$row[] = $product->gross;
					break;
				case 'gross_after_discount':
					$row[] = $product->gross_after_discount;
					break;
				case 'product_categories':
					$terms = get_the_terms($product->product_id, 'product_cat');
					if (empty($terms)) {
						$row[] = '';
					} else {
						$categories = array();
						foreach ($terms as $term)
							$categories[] = $term->name;
						$row[] = implode(', ', $categories);
					}
					break;
			}
		}
			
		if ($return)
			$rows[] = $row;
		else
			fputcsv($dest, $row);
	}
	if ($return)
		return $rows;
}

add_action('admin_enqueue_scripts', 'hm_psrf_admin_enqueue_scripts');
function hm_psrf_admin_enqueue_scripts() {
	wp_enqueue_style('hm_psrf_admin_style', plugins_url('css/hm-product-sales-report.css', __FILE__));
	wp_enqueue_style('pikaday', plugins_url('css/pikaday.css', __FILE__));
	wp_enqueue_script('moment', plugins_url('js/moment.min.js', __FILE__));
	wp_enqueue_script('pikaday', plugins_url('js/pikaday.js', __FILE__));
}

// Schedulable email report hook
add_filter('pp_wc_get_schedulable_email_reports', 'hm_psrf_add_schedulable_email_reports');
function hm_psrf_add_schedulable_email_reports($reports) {
	$reports['hm_psr'] = array(
		'name' => 'Product Sales Report',
		'callback' => 'hm_psrf_run_scheduled_report',
		'reports' => array(
			'last' => 'Last used settings'
		)
	);
	return $reports;
}

function hm_psrf_run_scheduled_report($reportId, $start, $end, $args=array(), $output=false) {
	$savedReportSettings = get_option('hm_psr_report_settings');
	if (!isset($savedReportSettings[0]))
		return false;
	$prevPost = $_POST;
	$_POST = $savedReportSettings[0];
	$_POST['report_time'] = 'custom';
	$_POST['report_start'] = date('Y-m-d', $start);
	$_POST['report_end'] = date('Y-m-d', $end);
	$_POST = array_merge($_POST, array_intersect_key($args, $_POST));
	
	if ($output) {
		echo('<table><thead><tr>');
		foreach (hm_sbpf_export_header(null, true) as $heading) {
			echo("<th>$heading</th>");
		}
		echo('</tr></thead><tbody>');
		foreach (hm_sbpf_export_body(null, true) as $row) {
			echo('<tr>');
			foreach ($row as $cell)
				echo('<td>'.htmlspecialchars($cell).'</td>');
			echo('</tr>');
		}
		echo('</tbody></table>');
		$_POST = $prevPost;
		return;
	}
	
	$filename = get_temp_dir().'/Product Sales Report.csv';
	$out = fopen($filename, 'w');
	if (!empty($_POST['include_header']))
		hm_sbpf_export_header($out);
	hm_sbpf_export_body($out);
	fclose($out);
	
	$_POST = $prevPost;
	
	return $filename;
}

function hm_psrf_report_order_statuses() {
	$wcOrderStatuses = wc_get_order_statuses();
	$orderStatuses = array();
	if (!empty($_POST['order_statuses'])) {
		foreach ($_POST['order_statuses'] as $orderStatus) {
			if (isset($wcOrderStatuses[$orderStatus]))
				$orderStatuses[] = substr($orderStatus, 3);
		}
	}
	return $orderStatuses;
}

/* Review/donate notice */

register_activation_hook(__FILE__, 'hm_psrf_first_activate');
function hm_psrf_first_activate() {
	$pre = 'hm_psr';
	$firstActivate = get_option($pre.'_first_activate');
	if (empty($firstActivate)) {
		update_option($pre.'_first_activate', time());
	}
}
if (is_admin() && get_option('hm_psr_rd_notice_hidden') != 1 && time() - get_option('hm_psr_first_activate') >= (14*86400)) {
	add_action('admin_notices', 'hm_psrf_rd_notice');
	add_action('wp_ajax_hm_psrf_rd_notice_hide', 'hm_psrf_rd_notice_hide');
}
function hm_psrf_rd_notice() {
	$pre = 'hm_psr';
	$slug = 'product-sales-report-for-woocommerce';
	echo('
		<div id="'.$pre.'_rd_notice" class="updated notice is-dismissible"><p>Do you use the <strong>Product Sales Report</strong> plugin?
		Please support our free plugin by <a href="https://wordpress.org/support/view/plugin-reviews/'.$slug.'" target="_blank">writing a review</a> and/or <a href="https://potentplugins.com/donate/?utm_source='.$slug.'&amp;utm_medium=link&amp;utm_campaign=wp-plugin-notice-donate-link" target="_blank">making a donation</a>!
		Thanks!</p></div>
		<script>jQuery(document).ready(function($){$(\'#'.$pre.'_rd_notice\').on(\'click\', \'.notice-dismiss\', function(){jQuery.post(ajaxurl, {action:\'hm_psrf_rd_notice_hide\'})});});</script>
	');
}
function hm_psrf_rd_notice_hide() {
	$pre = 'hm_psr';
	update_option($pre.'_rd_notice_hidden', 1);
}
?>