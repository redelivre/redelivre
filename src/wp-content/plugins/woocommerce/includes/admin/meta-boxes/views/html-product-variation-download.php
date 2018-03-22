<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<tr>
	<td class="file_name">
		<input type="text" class="input_text" placeholder="<?php esc_attr_e( 'File name', 'woocommerce' ); ?>" name="_wc_variation_file_names[<?php echo $variation_id; ?>][]" value="<?php echo esc_attr( $file['name'] ); ?>" />
		<input type="hidden" name="_wc_variation_file_hashes[<?php echo $variation_id; ?>][]" value="<?php echo esc_attr( $key ); ?>" />
	</td>
	<td class="file_url"><input type="text" class="input_text" placeholder="<?php esc_attr_e( "http://", 'woocommerce' ); ?>" name="_wc_variation_file_urls[<?php echo $variation_id; ?>][]" value="<?php echo esc_attr( $file['file'] ); ?>" /></td>
	<td class="file_url_choose" width="1%"><a href="#" class="button upload_file_button" data-choose="<?php esc_attr_e( 'Choose file', 'woocommerce' ); ?>" data-update="<?php esc_attr_e( 'Insert file URL', 'woocommerce' ); ?>"><?php echo str_replace( ' ', '&nbsp;', __( 'Choose file', 'woocommerce' ) ); ?></a></td>
	<td width="1%"><a href="#" class="delete"><?php _e( 'Delete', 'woocommerce' ); ?></a></td>
</tr>
