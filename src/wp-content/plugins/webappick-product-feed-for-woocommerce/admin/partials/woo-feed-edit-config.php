<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}
?><table class="table tree widefat fixed sorted_table mtable" style="width: 100%" id="table-1">
	<thead>
	<tr>
		<th></th>
		<th><?php echo esc_html( ucfirst( $provider ) ); ?> <?php _e( 'Attributes', 'woo-feed' ); ?></th>
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
	if ( isset( $feedRules['mattributes'] ) && count( $feedRules['mattributes'] ) > 0 ) {
		$mAttributes   = $feedRules['mattributes'];
		$wooAttributes = $feedRules['attributes'];
		$type          = $feedRules['type'];
		$default       = $feedRules['default'];
		$prefix        = $feedRules['prefix'];
		$suffix        = $feedRules['suffix'];
		$outputType    = $feedRules['output_type'];
		$limit         = $feedRules['limit'];
		$counter       = 0;
		foreach ( $mAttributes as $merchant => $mAttribute ) {
			?>
			<tr>
				<td><i class="wf_sortedtable dashicons dashicons-menu"></i></td>
				<td>
					<?php if ( method_exists( $wooFeedDropDown, $feedRules['provider'] . 'AttributesDropdown' ) ) { ?>
						<select name="mattributes[]" class="wf_mattributes">
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo $wooFeedDropDown->{$feedRules['provider'] . 'AttributesDropdown'}( esc_attr( $mAttribute ) );
							?>
						</select>
					<?php } else { ?>
						<input type="text" name="mattributes[]" value="<?php echo esc_attr( $mAttribute ); ?>" required class="wf_mattributes">
					<?php } ?>
				</td>
				<td>
					<input type="text" name="prefix[]" value="<?php echo esc_attr( stripslashes( $prefix[ $merchant ] ) ); ?>" autocomplete="off" class="wf_ps"/>
				</td>
				<td>
					<select name="type[]"  class="attr_type wfnoempty">
						<option <?php echo ( 'attribute' == $type[ $merchant ] ) ? 'selected="selected" ' : ''; ?>value="attribute"><?php _e( 'Attribute', 'woo-feed' ); ?></option>
						<option <?php echo ( 'pattern' == $type[ $merchant ] ) ? 'selected="selected" ' : ''; ?> value="pattern"><?php _e( 'Pattern', 'woo-feed' ); ?></option>
					</select>
				</td>
				<td>
					<select <?php echo ( 'attribute' == $type[ $merchant ] ) ? '' : 'style=" display: none;" '; ?>name="attributes[]" class="wf_attr wf_attributes">
						<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo $wooFeedProduct->attributeDropdown( esc_attr( $wooAttributes[ $merchant ] ) );
							?>
					</select>
					<?php if ( in_array( $feedRules['provider'], array( 'google', 'facebook', 'pinterest' ) ) && 'current_category' == $mAttribute ) { ?>
						<span <?php echo ( 'pattern' == $type[ $merchant ] ) ? '' : 'style=" display: none;" '; ?>class="wf_default wf_attributes">
							<select name="default[]" class="selectize" data-placeholder="<?php esc_attr_e( 'Select A Category', 'woo-feed' ); ?>">
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $wooFeedDropDown->googleTaxonomy( esc_attr( $default[ $merchant ] ) );
								?>
							</select>
						</span>
						<span style="font-size:x-small;"><a style="color: red" href="http://webappick.helpscoutdocs.com/article/19-how-to-map-store-category-with-merchant-category" target="_blank">Learn More..</a></span>
					<?php } else { ?>
						<input <?php echo ( 'pattern' == $type[ $merchant ] ) ? '' : 'style=" display: none;"'; ?>autocomplete="off" class="wf_default wf_attributes "  type="text" name="default[]" value="<?php echo esc_attr( $default[ $merchant ] ); ?>"/>
					<?php } ?>
				</td>
				<td>
					<input type="text" name="suffix[]" value="<?php echo esc_attr( stripslashes( $suffix[ $merchant ] ) ); ?>" autocomplete="off" class="wf_ps"/>
				</td>
				<td>
					<select name="output_type[<?php echo esc_attr( $counter ); ?>][]" class="outputType wfnoempty" data-placeholder="<?php esc_attr_e( 'Select Output Type', 'woo-feed' ); ?>" multiple>
						<?php
						foreach ( woo_feed_get_field_output_type_options() as $key => $option ) {
							if ( isset( $outputType[ $counter ] ) ) {
								if ( is_array( $outputType[ $counter ] ) ) {
									$selected = in_array( $key, $outputType[ $counter ] );
								} else {
									$selected = $outputType[ $counter ] == $key;
								}
							} else {
								$selected = '1' == $key;
							}
							printf( '<option value="%s" %s>%s</option>', esc_attr( $key ), selected( $selected, true, false ), esc_html( $option ) );
						}
						?>
					</select>
				</td>
				<td>
					<input type="text" name="limit[]" value="<?php echo esc_attr( $limit[ $merchant ] ); ?>" autocomplete="off" class="wf_ps"/>
				</td>
				<td>
					<i class="delRow dashicons dashicons-trash"></i>
				</td>
			</tr>
			<?php
			$counter++;
		}
	}
	?>
	</tbody>
	<tfoot>
	<tr>
		<td colspan="3">
			<button type="button" class="button-small button-primary" id="wf_newRow"><?php _e( 'Add New Row', 'woo-feed' ); ?></button>
		</td>
		<td colspan="6"></td>
	</tr>
	</tfoot>
</table>
