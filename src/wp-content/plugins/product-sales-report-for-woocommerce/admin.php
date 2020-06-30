<?php
// Print header
echo('
	<div class="wrap">
		<h2>Product Sales Report</h2>
');

// Check for WooCommerce
if (!class_exists('WooCommerce')) {
	echo('<div class="error"><p>This plugin requires that WooCommerce is installed and activated.</p></div></div>');
	return;
} else if (!function_exists('wc_get_order_types')) {
	echo('<div class="error"><p>The Product Sales Report plugin requires WooCommerce 2.2 or higher. Please update your WooCommerce install.</p></div></div>');
	return;
}

// Print form

echo('<div id="poststuff">
		<div id="post-body" class="columns-2">
			<div id="post-body-content" style="position: relative;">
				<form action="#hm_sbp_table" method="post">
					<input type="hidden" name="hm_sbp_do_export" value="1" />
	');
wp_nonce_field('hm_sbpf_do_export');
echo('
			<table class="form-table">
				<tr valign="top">
					<th scope="row">
						<label for="hm_sbp_field_report_time">Report Period:</label>
					</th>
					<td>
						<select name="report_time" id="hm_sbp_field_report_time">
							<option value="0d"'.($reportSettings['report_time'] == '0d' ? ' selected="selected"' : '').'>Today</option>
							<option value="1d"'.($reportSettings['report_time'] == '1d' ? ' selected="selected"' : '').'>Yesterday</option>
							<option value="7d"'.($reportSettings['report_time'] == '7d' ? ' selected="selected"' : '').'>Previous 7 days (excluding today)</option>
							<option value="30d"'.($reportSettings['report_time'] == '30d' ? ' selected="selected"' : '').'>Previous 30 days (excluding today)</option>
							<option value="0cm"'.($reportSettings['report_time'] == '0cm' ? ' selected="selected"' : '').'>Current calendar month</option>
							<option value="1cm"'.($reportSettings['report_time'] == '1cm' ? ' selected="selected"' : '').'>Previous calendar month</option>
							<option value="+7d"'.($reportSettings['report_time'] == '+7d' ? ' selected="selected"' : '').'>Next 7 days (future dated orders)</option>
							<option value="+30d"'.($reportSettings['report_time'] == '+30d' ? ' selected="selected"' : '').'>Next 30 days (future dated orders)</option>
							<option value="+1cm"'.($reportSettings['report_time'] == '+1cm' ? ' selected="selected"' : '').'>Next calendar month (future dated orders)</option>
							<option value="all"'.($reportSettings['report_time'] == 'all' ? ' selected="selected"' : '').'>All time</option>
							<option value="custom"'.($reportSettings['report_time'] == 'custom' ? ' selected="selected"' : '').'>Custom date range</option>
						</select>
					</td>
				</tr>
				<tr valign="top" class="hm_sbp_custom_time">
					<th scope="row">
						<label for="hm_sbp_field_report_start">Start Date:</label>
					</th>
					<td>
						<input type="date" name="report_start" id="hm_sbp_field_report_start" value="'.$reportSettings['report_start'].'" />
					</td>
				</tr>
				<tr valign="top" class="hm_sbp_custom_time">
					<th scope="row">
						<label for="hm_sbp_field_report_end">End Date:</label>
					</th>
					<td>
						<input type="date" name="report_end" id="hm_sbp_field_report_end" value="'.$reportSettings['report_end'].'" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label>Include Orders With Status:</label>
					</th>
					<td>');
foreach (wc_get_order_statuses() as $status => $statusName) {
	echo('<label><input type="checkbox" name="order_statuses[]"'.(in_array($status, $reportSettings['order_statuses']) ? ' checked="checked"' : '').' value="'.$status.'" /> '.$statusName.'</label><br />');
}
			echo('</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label>Include Products:</label>
					</th>
					<td>
						<label><input type="radio" name="products" value="all"'.($reportSettings['products'] == 'all' ? ' checked="checked"' : '').' /> All products</label><br />
						<label><input type="radio" name="products" value="cats"'.($reportSettings['products'] == 'cats' ? ' checked="checked"' : '').' /> Products in categories:</label><br />
						<div style="padding-left: 20px; width: 300px; max-height: 200px; overflow-y: auto;">
					');
foreach (get_terms('product_cat', array('hierarchical' => false)) as $term) {
	echo('<label><input type="checkbox" name="product_cats[]"'.(in_array($term->term_id, $reportSettings['product_cats']) ? ' checked="checked"' : '').' value="'.$term->term_id.'" /> '.htmlspecialchars($term->name).'</label><br />');
}
			echo('
						</div>
						<label><input type="radio" name="products" value="ids"'.($reportSettings['products'] == 'ids' ? ' checked="checked"' : '').' /> Product ID(s):</label> 
						<input type="text" name="product_ids" style="width: 400px;" placeholder="Use commas to separate multiple product IDs" value="'.htmlspecialchars($reportSettings['product_ids']).'" /><br />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label>Product Variations:</label>
					</th>
					<td>
						<label>
							<input type="radio" name="variations" value="0"'.(empty($reportSettings['variations']) ? ' checked="checked"' : '').' class="variations-fld" />
							Group product variations together
						</label><br />
						<label>
							<input type="radio" name="variations" value="1" disabled="disabled" class="variations-fld" />
							Report on each variation separately<sup style="color: #f00;">PRO</sup>
						</label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="hm_sbp_field_orderby">Sort By:</label>
					</th>
					<td>
						<select name="orderby" id="hm_sbp_field_orderby">
							<option value="product_id"'.($reportSettings['orderby'] == 'product_id' ? ' selected="selected"' : '').'>Product ID</option>
							<option value="quantity"'.($reportSettings['orderby'] == 'quantity' ? ' selected="selected"' : '').'>Quantity Sold</option>
							<option value="gross"'.($reportSettings['orderby'] == 'gross' ? ' selected="selected"' : '').'>Gross Sales</option>
							<option value="gross_after_discount"'.($reportSettings['orderby'] == 'gross_after_discount' ? ' selected="selected"' : '').'>Gross Sales (After Discounts)</option>
						</select>
						<select name="orderdir">
							<option value="asc"'.($reportSettings['orderdir'] == 'asc' ? ' selected="selected"' : '').'>ascending</option>
							<option value="desc"'.($reportSettings['orderdir'] == 'desc' ? ' selected="selected"' : '').'>descending</option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label>Report Fields:</label>
					</th>
					<td id="hm_psr_report_field_selection">');
$fieldOptions2 = $fieldOptions;
foreach ($reportSettings['fields'] as $fieldId) {
	if (!isset($fieldOptions2[$fieldId]))
		continue;
	echo('<label><input type="checkbox" name="fields[]" checked="checked" value="'.$fieldId.'"'.(in_array($fieldId, array('variation_id', 'variation_attributes')) ? ' class="variation-field"' : '').' /> '.$fieldOptions2[$fieldId].'</label>');
	unset($fieldOptions2[$fieldId]);
}
foreach ($fieldOptions2 as $fieldId => $fieldDisplay) {
	echo('<label><input type="checkbox" name="fields[]" value="'.$fieldId.'"'.(in_array($fieldId, array('variation_id', 'variation_attributes')) ? ' class="variation-field"' : '').' /> '.$fieldDisplay.'</label>');
}
unset($fieldOptions2);
			echo('</td>
				</tr>
				<tr valign="top">
					<th scope="row" colspan="2" class="th-full">
						<label>
							<input type="checkbox" name="exclude_free"'.(empty($reportSettings['exclude_free']) ? '' : ' checked="checked"').' />
							Exclude free products
						</label>
						<p class="description">If checked, order line items with a total amount of zero (after discounts) will be excluded from the report calculations.</p>
					</th>
				</tr>
				<tr valign="top">
					<th scope="row" colspan="2" class="th-full">
						<label>
							<input type="checkbox" name="limit_on"'.(empty($reportSettings['limit_on']) ? '' : ' checked="checked"').' />
							Show only the first
							<input type="number" name="limit" value="'.$reportSettings['limit'].'" min="0" step="1" class="small-text" />
							products
						</label>
					</th>
				</tr>
				<tr valign="top">
					<th scope="row" colspan="2" class="th-full">
						<label>
							<input type="checkbox" name="include_header"'.(empty($reportSettings['include_header']) ? '' : ' checked="checked"').' />
							Include header row
						</label>
					</th>
				</tr>
			</table>
			<p class="description">Note: Line item refunds created during the reporting period (regardless of the original order date) will be deducted from sales quantities and amounts if the status of the line item refund matches one of the selected order statuses (e.g. Complete), independent of the status of the original order. If you would like to disable this behavior, please check out our <a href="http://potentplugins.com/downloads/product-sales-report-pro-wordpress-plugin/?utm_source=product-sales-report&amp;utm_medium=link&amp;utm_campaign=wp-plugin-upgrade-link" target="_blank">Pro plugin</a>, which also applies status filtering differently for line item refunds. Note that line item refunds are not the same as setting an entire order to the Refunded status, which does not automatically create a line item refund.</p>');
			
			echo('<p class="submit">
				<button type="submit" class="button-primary" onclick="jQuery(this).closest(\'form\').attr(\'target\', \'\'); return true;">View Report</button>
				<button type="submit" class="button-primary" name="hm_sbp_download" value="1" onclick="jQuery(this).closest(\'form\').attr(\'target\', \'_blank\'); return true;">Download Report as CSV</button>
			</p>
		</form>
		
		</div> <!-- /post-body-content -->
		
		<div id="postbox-container-1" class="postbox-container">
			<div id="side-sortables" class="meta-box-sortables">
			
				<div class="postbox">
					<h2><a href="http://potentplugins.com/downloads/product-sales-report-pro-wordpress-plugin/?utm_source=product-sales-report&amp;utm_medium=link&amp;utm_campaign=wp-plugin-upgrade-link" target="_blank">Upgrade to Pro</a></h2>
					<div class="inside">
						<p><strong>Upgrade to <a href="http://potentplugins.com/downloads/product-sales-report-pro-wordpress-plugin/?utm_source=product-sales-report&amp;utm_medium=link&amp;utm_campaign=wp-plugin-upgrade-link" target="_blank">Product Sales Report Pro</a> for the following additional features:</strong></p>
						<ul style="list-style-type: disc; padding-left: 1.5em;">
<li>Report on product variations individually.</li>
<li>Optionally include products with no sales.</li>
<li>Report on shipping methods used.</li>
<li>Limit the report to orders with a matching custom meta field (e.g. delivery date).</li>
<li>Change the names and order of fields in the report.</li>
<li>Include <strong style="color: #f00;">custom fields</strong> defined by WooCommerce or another plugin on a product or product variation.</li>
<li>Save multiple report presets to save time when generating different reports.</li>
<li>Export in Excel (XLSX or XLS) format.</li>
<li>Send the report as an email attachment.</li>
						</ul>
						<p>
							<strong>Receive a 10% discount with the coupon code <span style="color: #f00;">WCREPORT10</span>!</strong>
							<a href="http://potentplugins.com/downloads/product-sales-report-pro-wordpress-plugin/?utm_source=product-sales-report&amp;utm_medium=link&amp;utm_campaign=wp-plugin-upgrade-link" target="_blank">Buy Now &gt;</a><br />
							(Not valid with any other discount.)
						</p>
					</div>
				</div>
				
				<div class="postbox">
					<h2><a href="https://potentplugins.com/downloads/scheduled-email-reports-woocommerce-plugin/?utm_source=product-sales-report&amp;utm_medium=link&amp;utm_campaign=wp-plugin-upgrade-link" target="_blank">Schedule Email Reports</a></h2>
					<div class="inside">
						<strong>Automatically send reports as email attachments on a recurring schedule.</strong><br />
						<a href="https://potentplugins.com/downloads/scheduled-email-reports-woocommerce-plugin/?utm_source=product-sales-report&amp;utm_medium=link&amp;utm_campaign=wp-plugin-upgrade-link" target="_blank">Get the add-on plugin &gt;</a>
					</div>
				</div>
				<div class="postbox">
					<h2><a href="https://potentplugins.com/downloads/frontend-reports-woocommerce-plugin/?utm_source=product-sales-report&amp;utm_medium=link&amp;utm_campaign=wp-plugin-upgrade-link" target="_blank">Embed Report in Frontend Pages</a></h2>
					<div class="inside">
						<strong>Display the report or a download link in posts and pages using a shortcode.</strong><br />
						<a href="https://potentplugins.com/downloads/frontend-reports-woocommerce-plugin/?utm_source=product-sales-report&amp;utm_medium=link&amp;utm_campaign=wp-plugin-upgrade-link" target="_blank">Get the add-on plugin &gt;</a>
					</div>
				</div>
				
			</div> <!-- /side-sortables-->
		</div><!-- /postbox-container-1 -->
		
		</div> <!-- /post-body -->
		<br class="clear" />
		</div> <!-- /poststuff -->
		
		');
		
		
		if (!empty($_POST['hm_sbp_do_export'])) {
			echo('<table id="hm_sbp_table">');
			if (!empty($_POST['include_header'])) {
				echo('<thead><tr>');
				foreach (hm_sbpf_export_header(null, true) as $rowItem)
					echo('<th>'.htmlspecialchars($rowItem).'</th>');
				echo('</tr></thead>');
			}
			echo('<tbody>');
			foreach (hm_sbpf_export_body(null, true) as $row) {
				echo('<tr>');
				foreach ($row as $rowItem) {
					echo('<td>'.htmlspecialchars($rowItem).'</td>');
				}
				echo('</tr>');
			}
			echo('</tbody></table>');
			
		}
		
		$potent_slug = 'product-sales-report-for-woocommerce';
		include(__DIR__.'/plugin-credit.php');
		
		echo('
			<h4>More <strong style="color: #f00;">free</strong> plugins for WooCommerce:</h4>
			<a href="https://wordpress.org/plugins/export-order-items-for-woocommerce/" target="_blank" style="margin-right: 10px;"><img src="'.plugins_url('images/xoiwc-icon.png', __FILE__).'" alt="Export Order Items" /></a>
			<a href="https://wordpress.org/plugins/stock-export-and-import-for-woocommerce/" target="_blank" style="margin-right: 10px;"><img src="'.plugins_url('images/sxiwc-icon.png', __FILE__).'" alt="Stock Export and Import" /></a>
			<a href="https://wordpress.org/plugins/sales-trends-for-woocommerce/" target="_blank" style="margin-right: 10px;"><img src="'.plugins_url('images/wcst-icon.png', __FILE__).'" alt="Sales Trends" /></a>
			<a href="https://wordpress.org/plugins/price-match-for-woocommerce/" target="_blank" style="margin-right: 10px;"><img src="'.plugins_url('images/wcpm-icon.png', __FILE__).'" alt="Price Match" /></a>
			<a href="https://wordpress.org/plugins/donations-for-woocommerce/" target="_blank" style="margin-right: 10px;"><img src="'.plugins_url('images/wcdon-icon.png', __FILE__).'" alt="Donations" /></a>
		');

		
echo('
	</div>
	
	<script type="text/javascript" src="'.plugins_url('js/hm-product-sales-report.js', __FILE__).'"></script>
');
?>