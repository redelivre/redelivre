<?php
/**
 * Content Settings Table
 *
 * @package WooFeed
 * @subpackage Editor
 * @version 1.0.0
 * @since WooFeed 3.2.6
 * @author KD <mhamudul.hk@gmail.com>
 * @copyright 2019 WebAppick <support@webappick.com>
 */
if ( ! defined( 'ABSPATH' ) ) {
	die(); // silence
}
/**
 * @global array $feedRules
 * @global Woo_Feed_Dropdown $wooFeedDropDown
 * @global Woo_Feed_Merchant $merchant
 */
global $feedRules, $wooFeedDropDown, $merchant;
?>
<table class="widefat fixed">
	<thead>
		<tr>
            <th colspan="2" class="woo-feed-table-heading">
                <span class="woo-feed-table-heading-title"><?php _e( 'Content Settings', 'woo-feed' ); ?></span>
                <?php woo_feed_clear_cache_button(); ?>
            </th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th><label for="provider"><?php _e( 'Template', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
			<td>
				<select wftitle="<?php esc_attr_e( 'Select a template', 'woo-feed' ); ?>" name="provider" id="provider" class="generalInput wfmasterTooltip" required>
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $wooFeedDropDown->merchantsDropdown( $feedRules['provider'] );
					?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="filename"><?php _e( 'File Name', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
			<td>
				<input name="filename" value="<?php echo isset( $feedRules['filename'] ) ? esc_attr( $feedRules['filename'] ) : ''; ?>" type="text" id="filename" class="generalInput wfmasterTooltip" wftitle="<?php esc_attr_e( 'Filename should be unique. Otherwise it will override the existing filename.', 'woo-feed' ); ?>" required>
			</td>
		</tr>
		<tr>
			<th><label for="feedType"><?php _e( 'Feed Type', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
			<td>
				<select name="feedType" id="feedType" class="generalInput" required>
					<option value=""></option>
					<?php
					foreach ( woo_feed_get_file_types() as $file_type => $label ) {
						printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $file_type ), esc_html( $label ), selected( $feedRules['feedType'], $file_type, false ) );
					}
					?>
				</select>
				<span class="spinner" style="float: none; margin: 0;"></span>
			</td>
		</tr>
		<tr class="itemWrapper" style="display: none;">
			<th><label for="itemsWrapper"><?php _e( 'Items Wrapper', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
			<td>
				<input name="itemsWrapper" id="itemsWrapper" type="text" value="<?php echo esc_attr( $feedRules['itemsWrapper'] ); ?>" class="generalInput" required="required">
			</td>
		</tr>
		<tr class="itemWrapper" style="display: none;">
			<th><label for="itemWrapper"><?php _e( 'Single Item Wrapper', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
			<td>
				<input name="itemWrapper" id="itemWrapper" type="text" value="<?php echo esc_attr( $feedRules['itemWrapper'] ); ?>" class="generalInput" required="required">
			</td>
		</tr>
		<?php
		/*
		<tr class="itemWrapper" style="display: none;">
			<th><label for="extraHeader"><?php _e( 'Extra Header', 'woo-feed' ); ?> </label></th>
			<td>
				<textarea name="extraHeader" id="extraHeader"  style="width: 100%" placeholder="<?php esc_html_e( 'Insert Extra Header value. Press enter at the end of each line.', 'woo-feed' ); ?>" rows="3"><?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo isset( $feedRules['extraHeader'] ) ? $feedRules['extraHeader'] : '';
				?></textarea>
			</td>
		</tr>
		 */
		?>
		<tr class="wf_csvtxt" style="display: none;">
			<th><label for="delimiter"><?php _e( 'Delimiter', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
			<td>
				<select name="delimiter" id="delimiter" class="generalInput">
					<?php
					foreach ( woo_feed_get_csv_delimiters() as $k => $v ) {
						printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $k ), esc_html( $v ), selected( $feedRules['delimiter'], $k, false ) );
					}
					?>
				</select>
			</td>
		</tr>
		<tr class="wf_csvtxt" style="display: none;">
			<th><label for="enclosure"><?php _e( 'Enclosure', 'woo-feed' ); ?> <span class="requiredIn">*</span></label></th>
			<td>
				<select name="enclosure" id="enclosure" class="generalInput">
					<?php
					foreach ( woo_feed_get_csv_enclosure() as $k => $v ) {
						/** @noinspection HtmlUnknownAttribute */
						printf( '<option value="%1$s" %3$s>%2$s</option>', esc_attr( $k ), esc_html( $v ), selected( $feedRules['enclosure'], $k, false ) );
					}
					?>
				</select>
			</td>
		</tr>
	</tbody>
</table>
<?php
// End of file woo-feed-content-settings.php
