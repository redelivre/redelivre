<?php
/**
 * Common Add Feed Template
 */
if ( ! defined( 'ABSPATH' ) ) {
	die();
}
global $provider, $feedRules;
?>
<table class="table tree widefat fixed sorted_table mtable" style="width: 100%;" id="table-1">
	<thead>
	<tr>
		<th></th>
		<th><?php echo esc_html( ucfirst( $provider ) ) . ' '; ?><?php _e( 'Attributes', 'woo-feed' ); ?></th>
		<th><?php _e( 'Prefix', 'woo-feed' ); ?></th>
		<th><?php _e( 'Type', 'woo-feed' ); ?></th>
		<th><?php _e( 'Value', 'woo-feed' ); ?></th>
		<th><?php _e( 'Suffix', 'woo-feed' ); ?></th>
		<th><?php _e( 'Output Type', 'woo-feed' ); ?></th>
		<th><?php _e( 'Command', 'woo-feed' ); ?></th>
		<th></th>
	</tr>
	</thead>
	<tbody>
	<?php
	
	foreach ( $feedRules['mattributes'] as $key => $value ) {
		$prefix          = $feedRules['prefix'][ $key ];
		$attrSelected    = ( 'attribute' == $feedRules['type'][ $key ] ) ? 'selected' : '';
		$patternSelected = ( 'pattern' == $feedRules['type'][ $key ] ) ? 'selected' : '';
		$attribute       = $feedRules['attributes'][ $key ];
		$pattern         = $feedRules['default'][ $key ];
		$suffix          = $feedRules['suffix'][ $key ];
		$outputType      = $feedRules['output_type'][ $key ];
		$limit           = $feedRules['limit'][ $key ];
		?>
		<tr>
			<td><i class="wf_sortedtable dashicons dashicons-menu"></i></td>
			<td>
				<input type="text" name="mattributes[]" autocomplete="off" required value="<?php echo esc_attr( $value ); ?>" class="wf_validate_attr wf_mattributes">
			</td>
			<td>
				<input type="text" name="prefix[]" autocomplete="off" value="<?php echo esc_attr( $prefix ); ?>" class="wf_ps">
			</td>
			<td>
				<select name="type[]" class="attr_type wfnoempty">
					<option <?php echo esc_attr( $attrSelected ); ?> value="attribute"><?php _e( 'Attribute', 'woo-feed' ); ?></option>
					<option <?php echo esc_attr( $patternSelected ); ?> value="pattern"><?php _e( 'Pattern', 'woo-feed' ); ?></option>
				</select>
			</td>
			<td>
				<select <?php echo ( 'selected' != $attrSelected ) ? "style='display:none;'" : ''; ?> name="attributes[]" required="required" class="wf_validate_attr wf_attr wf_attributes">
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $wooFeedProduct->attributeDropdown( $attribute );
					?>
				</select>
				<input value="<?php echo esc_attr( $pattern ); ?>" type="text" name="default[]" autocomplete="off" class="wf_default wf_attributes" <?php echo ( 'selected' != $patternSelected ) ? "style='display:none;'" : ''; ?>">
			</td>
			<td>
				<input type="text" name="suffix[]" autocomplete="off" value="<?php echo esc_attr( $suffix ); ?>" class="wf_ps">
			</td>
			<td>
				<select name="output_type[][]"  class="outputType wfnoempty" data-placeholder="<?php esc_attr_e( 'Select Output Type', 'woo-feed' ); ?>" multiple>
					<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $wooFeedDropDown->outputTypes( $outputType );
					?>
				</select>
			</td>
			<td>
				<input type="text" value="<?php echo esc_attr( $limit ); ?>" name="limit[]" class="wf_ps">
			</td>
			<td>
				<i class="delRow dashicons dashicons-trash"></i>
			</td>
		</tr>
		<?php
	}
	?>
	</tbody>
	<tfoot>
	<tr>
		<td>
			<button type="button" class="button-small button-primary" id="wf_newRow"><?php _e( 'Add New Row', 'woo-feed' ); ?></button>
		</td>
		<td colspan="8"></td>
	</tr>
	</tfoot>
</table>
