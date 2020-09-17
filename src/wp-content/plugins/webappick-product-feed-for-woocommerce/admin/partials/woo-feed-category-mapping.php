<?php
/**
 * Add New Category Mapping View
 *
 * @link       https://webappick.com/
 * @since      1.0.0
 *
 * @package    Woo_Feed
 * @subpackage Woo_Feed/admin/partial
 * @author     Ohidul Islam <wahid@webappick.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

$wooFeedDropDown = new Woo_Feed_Dropdown();
$value           = array();
if ( isset( $_GET['action'], $_GET['cmapping'] ) ) { // phpcs:ignore
	$option = get_option( sanitize_text_field( $_GET['cmapping'] ) ); // phpcs:ignore
	$value  = maybe_unserialize( $option );
}
?>
<div class="wrap">
	<h2><?php esc_html_e( 'Category Mapping', 'woo-feed' ); ?></h2>
	<?php WPFFWMessage()->displayMessages(); ?>
	<form action="" name="feed" id="category-mapping-form" method="post" autocomplete="off">
		<?php wp_nonce_field( 'category-mapping' ); ?>
		<table class=" widefat fixed" id="cmTable">
			<tbody>
			<tr>
				<td width="30%">
					<label for="providers"><b><?php esc_html_e( 'Merchant', 'woo-feed' ); ?> <span class="requiredIn">*</span></b></label>
				</td>
				<td>
					<select name="mappingprovider" id="providers" class="generalInput" required>
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $wooFeedDropDown->merchantsDropdown( isset( $value['mappingprovider'] ) ? $value['mappingprovider'] : '' );
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td><b><?php esc_html_e( 'Mapping Name', 'woo-feed' ); ?><span class="requiredIn">*</span></b></td>
				<td>
					<input required value="<?php echo isset( $value['mappingname'] ) ? esc_attr( $value['mappingname'] ) : ''; ?>" name="mappingname" wftitle="<?php esc_attr_e( 'Mapping Name should be unique and don\'t use space. Otherwise it will override the existing Category Mapping. Example: myMappingName or my_mapping_name', 'woo-feed' ); ?>" type="text" class="generalInput wfmasterTooltip">
				</td>
			</tr>
			</tbody>
		</table>
		<br/>
		<table class="table tree widefat fixed ">
			<thead>
			<tr>
				<th><?php esc_html_e( 'Local Category', 'woo-feed' ); ?></th>
				<th><?php esc_html_e( 'Merchant Category', 'woo-feed' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php woo_feed_render_categories( 0, '', $value ); ?>
			</tbody>
			<tfoot>
			<tr>
				<td colspan="2">
					<button name="<?php echo isset( $_GET['action'] ) ? esc_attr( sanitize_text_field( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>" type="submit" class="button button-large button-primary"><?php esc_html_e( 'Save Mapping', 'woo-feed' ); ?></button>
				</td>
			</tr>
			</tfoot>
		</table>
	</form>
</div>