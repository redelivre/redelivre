<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}
?>
<table class="table tree widefat fixed sorted_table mtable" style="width: 100%" id="table-1">
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
		$mAttributes   = array_values( $feedRules['mattributes'] );
		$wooAttributes = array_values( $feedRules['attributes'] );
		$attr_type     = array_values( $feedRules['type'] );
		$default       = array_values( $feedRules['default'] );
		$prefix        = array_values( $feedRules['prefix'] );
		$suffix        = array_values( $feedRules['suffix'] );
		$outputType    = array_values( $feedRules['output_type'] );
		$limit         = array_values( $feedRules['limit'] );
		$counter       = 0;
		foreach ( $mAttributes as $k => $mAttribute ) {
			?>
			<tr>
				<td><i class="wf_sortedtable dashicons dashicons-menu"></i></td>
				<td>
					<?php if ( method_exists( $wooFeedDropDown, $feedRules['provider'] . 'AttributesDropdown' ) ) { ?>
						<select name="mattributes[<?php echo esc_attr( $k ); ?>]" class="wf_mattributes">
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo $wooFeedDropDown->{$feedRules['provider'] . 'AttributesDropdown'}( esc_attr( $mAttribute ) );
							?>
						</select>
					<?php } else { ?>
						<input type="text" name="mattributes[<?php echo esc_attr( $k ); ?>]" value="<?php echo esc_attr( $mAttribute ); ?>" required class="wf_mattributes">
					<?php } ?>
				</td>
				<td>
					<input type="text" name="prefix[<?php echo esc_attr( $k ); ?>]" value="<?php echo esc_attr( stripslashes( $prefix[ $k ] ) ); ?>" autocomplete="off" class="wf_ps"/>
				</td>
				<td>
					<select name="type[<?php echo esc_attr( $k ); ?>]"  class="attr_type wfnoempty">
						<option <?php echo ( 'attribute' == $attr_type[ $k ] ) ? 'selected="selected" ' : ''; ?>value="attribute"><?php _e( 'Attribute', 'woo-feed' ); ?></option>
						<option <?php echo ( 'pattern' == $attr_type[ $k ] ) ? 'selected="selected" ' : ''; ?> value="pattern"><?php _e( 'Pattern', 'woo-feed' ); ?></option>
					</select>
				</td>
				<td>
					<select <?php echo ( 'attribute' == $attr_type[ $k ] ) ? '' : 'style=" display: none;" '; ?>name="attributes[<?php echo esc_attr( $k ); ?>]" class="wf_attr wf_attributes">
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $wooFeedDropDown->product_attributes_dropdown( esc_attr( $wooAttributes[ $k ] ) );
						?>
					</select>
					<?php if ( woo_feed_merchant_require_google_category( $feedRules['provider'], $mAttribute ) ) { ?>
						<span <?php echo ( 'pattern' == $attr_type[ $k ] ) ? '' : 'style=" display: none;" '; ?>class="wf_default wf_attributes">
							<select name="default[<?php echo esc_attr( $k ); ?>]" class="selectize" data-placeholder="<?php esc_attr_e( 'Select A Category', 'woo-feed' ); ?>">
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $wooFeedDropDown->googleTaxonomy( esc_attr( $default[ $k ] ) );
								?>
							</select>
						</span>
						<span style="font-size:x-small;"><a style="color: red" href="http://webappick.helpscoutdocs.com/article/19-how-to-map-store-category-with-merchant-category" target="_blank">Learn More..</a></span>
					<?php } else { ?>
						<input <?php echo ( 'pattern' == $attr_type[ $k ] ) ? '' : 'style=" display: none;"'; ?>autocomplete="off" class="wf_default wf_attributes "  type="text" name="default[<?php echo esc_attr( $k ); ?>]" value="<?php echo esc_attr( $default[ $k ] ); ?>"/>
					<?php } ?>
				</td>
				<td>
					<input type="text" name="suffix[<?php echo esc_attr( $k ); ?>]" value="<?php echo esc_attr( stripslashes( $suffix[ $k ] ) ); ?>" autocomplete="off" class="wf_ps"/>
				</td>
				<td>
					<select name="output_type[<?php echo esc_attr( $k ); ?>][]" class="outputType wfnoempty" data-placeholder="<?php esc_attr_e( 'Select Output Type', 'woo-feed' ); ?>" multiple>
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $wooFeedDropDown->outputTypes( $outputType[ $k ] );
						?>
					</select>
				</td>
				<td>
					<input type="text" name="limit[<?php echo esc_attr( $k ); ?>]" value="<?php echo esc_attr( $limit[ $k ] ); ?>" autocomplete="off" class="wf_ps"/>
				</td>
				<td>
					<i class="delRow dashicons dashicons-trash"></i>
				</td>
			</tr>
			<?php
		}
	}
	?>
	</tbody>
	<tfoot>
	<tr>
		<td colspan="3">
			<script type="text/template" id="feed_config_template">
				<tr>
					<td><i class="wf_sortedtable dashicons dashicons-menu"></i></td>
					<td>
						<?php if ( method_exists( $wooFeedDropDown, $feedRules['provider'] . 'AttributesDropdown' ) ) { ?>
							<select name="mattributes[__idx__]" class="wf_mattributes">
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $wooFeedDropDown->{$feedRules['provider'] . 'AttributesDropdown'}();
								?>
							</select>
						<?php } else { ?>
							<input type="text" name="mattributes[__idx__]" autocomplete="off" value="" required class="wf_validate_attr wf_mattributes">
						<?php } ?>
					</td>
					<td>
						<input type="text" name="prefix[__idx__]" autocomplete="off" value="" class="wf_ps">
					</td>
					<td>
						<select name="type[__idx__]" class="attr_type wfnoempty">
							<option value="attribute"><?php _e( 'Attribute', 'woo-feed' ); ?></option>
							<option value="pattern"><?php _e( 'Pattern', 'woo-feed' ); ?></option>
						</select>
					</td>
					<td>
						<select name="attributes[__idx__]" required="required" class="wf_validate_attr wf_attr wf_attributes">
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo $wooFeedDropDown->product_attributes_dropdown();
							?>
						</select>
						<input value="" type="text" name="default[]" autocomplete="off" class="wf_default wf_attributes" style="display:none;">
					</td>
					<td>
						<input type="text" name="suffix[__idx__]" autocomplete="off" value="" class="wf_ps">
					</td>
					<td>
						<select name="output_type[__idx__][]" class="outputType wfnoempty" data-placeholder="<?php esc_attr_e( 'Select Output Type', 'woo-feed' ); ?>" multiple>
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo $wooFeedDropDown->outputTypes();
							?>
						</select>
					</td>
					<td>
						<input type="text" value="" name="limit[__idx__]" class="wf_ps">
					</td>
					<td>
						<i class="delRow dashicons dashicons-trash"></i>
					</td>
				</tr>
			</script>
			<button type="button" class="button-small button-primary" id="wf_newRow"><?php _e( 'Add New Attribute', 'woo-feed' ); ?></button>
		</td>
		<td colspan="6"></td>
	</tr>
	</tfoot>
</table>
