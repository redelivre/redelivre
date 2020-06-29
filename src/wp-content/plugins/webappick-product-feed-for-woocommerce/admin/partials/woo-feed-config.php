<?php
/**
 * Settings Page
 *
 * @link       https://webappick.com/
 * @since      1.0.0
 * @version    1.0.1
 *
 * @package    Woo_Feed
 * @subpackage Woo_Feed/admin/partial
 * @author     Ohidul Islam <wahid@webappick.com>
 */
$batch_limit = (int) get_option( 'woo_feed_per_batch', 200 );
$queryType   = get_option( 'woo_feed_product_query_type' );
if ( ! $queryType || ! in_array( $queryType, array( 'wc', 'wp', 'both' ) ) ) {
	$queryType = 'wc';
}
if ( ! $batch_limit || $batch_limit <= 0 ) {
	$batch_limit = 200;
}
?>
<div class="wrap wapk-admin">
	<div class="wapk-section">
		<h1 class="wp-heading-inline"><?php _e( 'Settings', 'woo-feed' ); ?></h1>
		<hr class="wp-header-end">
		<?php WPFFWMessage()->displayMessages(); ?>
		<form action="" method="post" autocomplete="off">
			<?php wp_nonce_field( 'woo-feed-config' ); ?>
			<table class="widefat fixed" role="presentation">
				<thead>
				<tr>
					<th colspan="2"><b><?php _e( 'Common Settings', 'woo-feed' ); ?></b></th>
				</tr>
				</thead>
				<tbody>
				<?php do_action( 'woo_feed_before_settings_page_fields' ); ?>
				<tr>
					<th scope="row"><label for="batch_limit"><?php _e( 'Product per batch', 'woo-feed' ); ?></label></th>
					<td>
						<input class="regular-text" type="number" min="1" name="batch_limit" id="batch_limit" value="<?php echo esc_attr( $batch_limit ); ?>">
						<p class="description"><?php _e( 'Don\'t change the value if you are not sure about this. Plugin may fail to make feed.', 'woo-feed' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="woo_feed_product_query_type"><?php _e( 'Product Query Type', 'woo-feed' ); ?></label></th>
					<td>
						<select name="woo_feed_product_query_type" id="woo_feed_product_query_type">
							<option value="wc" <?php selected( $queryType, 'wc' ); ?> ><?php esc_html_e( 'WC_Product_Query', 'woo-feed' ); ?></option>
							<option value="wp" <?php selected( $queryType, 'wp' ); ?> ><?php esc_html_e( 'WP_Query', 'woo-feed' ); ?></option>
							<option value="both" <?php selected( $queryType, 'both' ); ?>><?php esc_html_e( 'Both', 'woo-feed' ); ?></option>
						</select>
						<p class="description"><?php _e( 'Don\'t change the value if you are not sure about this. Plugin may fail to make feed.', 'woo-feed' ); ?></p>
					</td>
				</tr>
				<?php
				/*
				<tr>
					<th scope="row"><label for="enable_error_debugging"><?php _e( 'Debug Mode', 'woo-feed' ); ?></label>
					</th>
					<td>
						<label for="enable_error_debugging">
							<input type="checkbox" name="enable_error_debugging" id="enable_error_debugging" value="on" <?php checked( woo_feed_is_debugging_enabled(), true ); ?> >
							<?php _e( 'Enable Logging', 'woo-feed' ); ?>
						</label>
						<p class="description" style="font-size: smaller;color: #ea3d3d;font-weight: bold;"><?php _e( 'Enabling Logging will decrease performance of feed generation.', 'woo-feed' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="clear_all_logs"><?php _e( 'Clear Logs', 'woo-feed' ); ?></label></th>
					<td>
						<label for="clear_all_logs">
							<input type="checkbox" name="clear_all_logs" id="clear_all_logs" value="on">
							<?php _e( 'Clear All Log Data', 'woo-feed' ); ?>
						</label>
						<p class="description" style="font-size: smaller;color: #ea3d3d;font-weight: bold;"><?php _e( 'This will clear all log files generated by this plugin.', 'woo-feed' ); ?></p>
					</td>
				</tr>
				 */
				?>
				<tr>
					<td><label for="opt_in"><?php _e( 'Send Debug Info', 'woo-feed' ); ?></label></td>
					<td>
						<label for="opt_in">
							<input type="checkbox" id="opt_in" name="opt_in" value="on" <?php checked( WooFeedWebAppickAPI::getInstance()->is_tracking_allowed(), true ); ?>> <?php _e( 'Allow WooFeed To Collect Debug Info.', 'woo-feed' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'To opt out, leave this box unchecked. Your Feed Data remains un-tracked, and no data will be collected. No sensitive data is tracked.', 'woo-feed' ); ?><br><a href="#" data-toggle_slide=".tracker_collection_list"><?php esc_html_e( 'See What We Collect.', 'woo-feed' ); ?></a></p>
						<ul class="tracker_collection_list" style="display: none;">
							<li><?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo implode( '</li><li>', WooFeedWebAppickAPI::getInstance()->get_data_collection_description() );
							?></li>
						</ul>
					</td>
				</tr>
				<?php do_action( 'woo_feed_after_settings_page_fields' ); ?>
				<tr>
					<td colspan="2">
						<p class="submit" style="text-align: center">
							<input type="submit" class="button button-primary" name="wa_woo_feed_config" value="<?php esc_attr_e( 'Save Changes', 'woo-feed' ); ?>">
						</p>
					</td>
				</tr>
				</tbody>
			</table>
		</form>
	</div>
</div>