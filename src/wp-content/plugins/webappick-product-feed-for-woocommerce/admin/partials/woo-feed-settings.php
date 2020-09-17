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
$settings = woo_feed_get_options( 'all' );
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
						<input class="regular-text" type="number" min="1" name="batch_limit" id="batch_limit" value="<?php echo esc_attr( $settings['per_batch'] ); ?>">
						<p class="description"><?php _e( 'Don\'t change the value if you are not sure about this. Plugin may fail to make feed.', 'woo-feed' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="product_query_type"><?php _e( 'Product Query Type', 'woo-feed' ); ?></label></th>
					<td>
						<select name="product_query_type" id="product_query_type">
							<?php foreach ( woo_feed_get_query_type_options() as $k => $v ) { ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $settings['product_query_type'], $k ); ?> ><?php echo esc_html( $v ); ?></option>
							<?php } ?>
						</select>
						<p class="description"><?php _e( 'Don\'t change the value if you are not sure about this. Plugin may fail to make feed.', 'woo-feed' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="enable_error_debugging"><?php _e( 'Debug Mode', 'woo-feed' ); ?></label>
					</th>
					<td>
						<select name="enable_error_debugging" id="enable_error_debugging">
							<option value="off"<?php selected( $settings['enable_error_debugging'], 'off' ); ?>><?php esc_html_e( 'Off', 'woo-feed' ); ?></option>
							<option value="on"<?php selected( $settings['enable_error_debugging'], 'on' ); ?>><?php esc_html_e( 'On', 'woo-feed' ); ?></option>
						</select>
						<label for="clear_all_logs">
							<input type="checkbox" name="clear_all_logs" id="clear_all_logs" value="on"><?php _e( 'Clear All Log Data', 'woo-feed' ); ?>
						</label>
						<p class="description" style="font-size: smaller;color: #ea3d3d;font-weight: bold;"><?php _e( 'Enabling Logging will decrease performance of feed generation.', 'woo-feed' ); ?></p>
					</td>
				</tr>
				<tr>
					<td><label for="opt_in"><?php _e( 'Send Debug Info', 'woo-feed' ); ?></label></td>
					<td>
						<label for="opt_in">
							<input type="checkbox" id="opt_in" name="opt_in" value="on" <?php checked( WooFeedWebAppickAPI::getInstance()->is_tracking_allowed(), true ); ?>> <?php _e( 'Allow WooFeed To Collect Debug Info.', 'woo-feed' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'To opt out, leave this box unchecked. Your Feed Data remains un-tracked, and no data will be collected. No sensitive data is tracked.', 'woo-feed' ); ?><br><a href="#" data-toggle_slide=".tracker_collection_list"><?php esc_html_e( 'See What We Collect.', 'woo-feed' ); ?></a></p>
						<ul class="tracker_collection_list" style="display: none;">
							<li>
                            <?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo implode( '</li><li>', WooFeedWebAppickAPI::getInstance()->get_data_collection_description() );
							?>
                            </li>
						</ul>
					</td>
				</tr>
				<tr>
					<td><label for="cache_ttl"><?php esc_html_e( 'WooFeed Cache TTL', 'woo-feed' ); ?></label></td>
					<td>
						<select name="cache_ttl" id="cache_ttl">
							<?php foreach ( woo_feed_get_cache_ttl_options() as $k => $v ) { ?>
								<option value="<?php echo esc_attr( $k ); ?>"<?php selected( $settings['cache_ttl'], $k ); ?>><?php echo esc_html( $v ); ?></option>
							<?php } ?>
						</select>
						<label for="purge_feed_cache">
							<input type="checkbox" name="purge_feed_cache" id="purge_feed_cache" value="1"> <?php esc_html_e( 'Purge Cache Now', 'woo-feed' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'When to expire the Cache.', 'woo-feed' ); ?></p>
					</td>
				</tr>
				<?php do_action( 'woo_feed_after_settings_page_fields' ); ?>
				<tr>
					<td colspan="2">
						<p class="submit" style="text-align: left; padding: 8px 10px; width:50%; float:right;">
							<input type="submit" class="button button-primary" name="wa_woo_feed_config" value="<?php esc_attr_e( 'Save Changes', 'woo-feed' ); ?>">
						</p>
					</td>
				</tr>
				</tbody>
			</table>
		</form>
	</div>
</div>
