<?php
/**
 * Google Template
 */
if ( ! defined( 'ABSPATH' ) ) {
	die();
}
global $provider;
?>
<table class="table tree widefat fixed sorted_table mtable" style="width: 100%;" id="table-1">
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
	<tr>
		<td>
			<i class="wf_sortedtable dashicons dashicons-menu"></i>
		</td>
		<td>
			<select name="mattributes[]"  required class="wf_mattributes">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->googleAttributesDropdown( 'id' );
				?>
			</select>
		</td>
		<td>
			<input type="text" name="prefix[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="type[]" class="attr_type wfnoempty">
				<option value="attribute"><?php _e( 'Attribute', 'woo-feed' ); ?></option>
				<option value="pattern"><?php _e( 'Pattern', 'woo-feed' ); ?></option>
			</select>
		</td>
		<td>
			<select name="attributes[]"  class="wf_attr wf_attributes">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedProduct->attributeDropdown( 'id' );
				?>
			</select>
			<input type="text" name="default[]" autocomplete="off" class="wf_default wf_attributes" style=" display: none;">
		</td>
		<td>
			<input type="text" name="suffix[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="output_type[][]"   class="outputType wfnoempty" data-placeholder="<?php esc_attr_e( 'Select Output Type', 'woo-feed' ); ?>" multiple>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->outputTypes();
				?>
			</select>
		</td>
		<td>
			<input type="text" name="limit[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<i class="delRow dashicons dashicons-trash"></i>
		</td>
	</tr>
	<tr>
		<td>
			<i class="wf_sortedtable dashicons dashicons-menu"></i>
		</td>
		<td>
			<select name="mattributes[]"  required class="wf_mattributes">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->googleAttributesDropdown( 'title' );
				?>
			</select>
		</td>
		<td>
			<input type="text" name="prefix[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="type[]" class="attr_type wfnoempty">
				<option value="attribute"><?php _e( 'Attribute', 'woo-feed' ); ?></option>
				<option value="pattern"><?php _e( 'Pattern', 'woo-feed' ); ?></option>
			</select>
		</td>
		<td>
			<select name="attributes[]"  class="wf_attr wf_attributes">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedProduct->attributeDropdown( 'title' );
				?>
			</select>
			<input type="text" name="default[]" autocomplete="off" class="wf_default wf_attributes" style=" display: none;">
		</td>
		<td>
			<input type="text" name="suffix[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="output_type[][]"   class="outputType wfnoempty" data-placeholder="<?php esc_attr_e( 'Select Output Type', 'woo-feed' ); ?>" multiple>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->outputTypes();
				?>
			</select>
		</td>
		<td>
			<input type="text" name="limit[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<i class="delRow dashicons dashicons-trash"></i>
		</td>
	</tr>
	<tr>
		<td>
			<i class="wf_sortedtable dashicons dashicons-menu"></i>
		</td>
		<td>
			<select name="mattributes[]"  required class="wf_mattributes">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->googleAttributesDropdown( 'description' );
				?>
			</select>
		</td>
		<td>
			<input type="text" name="prefix[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="type[]" class="attr_type wfnoempty">
				<option value="attribute"><?php _e( 'Attribute', 'woo-feed' ); ?></option>
				<option value="pattern"><?php _e( 'Pattern', 'woo-feed' ); ?></option>
			</select>
		</td>
		<td>
			<select name="attributes[]"  class="wf_attr wf_attributes">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedProduct->attributeDropdown( 'description' );
				?>
			</select>
			<input type="text" name="default[]" autocomplete="off" class="wf_default wf_attributes" style=" display: none;">
		</td>
		<td>
			<input type="text" name="suffix[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="output_type[][]"   class="outputType wfnoempty" data-placeholder="<?php esc_attr_e( 'Select Output Type', 'woo-feed' ); ?>" multiple>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->outputTypes();
				?>
			</select>
		</td>
		<td>
			<input type="text" name="limit[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<i class="delRow dashicons dashicons-trash"></i>
		</td>
	</tr>
	<tr>
		<td>
			<i class="wf_sortedtable dashicons dashicons-menu"></i>
		</td>
		<td>
			<select name="mattributes[]"  required class="wf_mattributes">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->googleAttributesDropdown( 'item_group_id' );
				?>
			</select>
		</td>
		<td>
			<input type="text" name="prefix[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="type[]" class="attr_type wfnoempty">
				<option value="attribute"><?php _e( 'Attribute', 'woo-feed' ); ?></option>
				<option value="pattern"><?php _e( 'Pattern', 'woo-feed' ); ?></option>
			</select>
		</td>
		<td>
			<select name="attributes[]"  class="wf_attr wf_attributes">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedProduct->attributeDropdown( 'item_group_id' );
				?>
			</select>
			<input type="text" name="default[]" autocomplete="off" class="wf_default wf_attributes" style=" display: none;">
		</td>
		<td>
			<input type="text" name="suffix[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="output_type[][]"   class="outputType wfnoempty" data-placeholder="<?php esc_attr_e( 'Select Output Type', 'woo-feed' ); ?>" multiple>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->outputTypes();
				?>
			</select>
		</td>
		<td>
			<input type="text" name="limit[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<i class="delRow dashicons dashicons-trash"></i>
		</td>
	</tr>
	<tr>
		<td>
			<i class="wf_sortedtable dashicons dashicons-menu"></i>
		</td>
		<td>
			<select name="mattributes[]"  required class="wf_mattributes">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->googleAttributesDropdown( 'link' );
				?>
			</select>
		</td>
		<td>
			<input type="text" name="prefix[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="type[]" class="attr_type wfnoempty">
				<option value="attribute"><?php _e( 'Attribute', 'woo-feed' ); ?></option>
				<option value="pattern"><?php _e( 'Pattern', 'woo-feed' ); ?></option>
			</select>
		</td>
		<td>
			<select name="attributes[]"  class="wf_attr wf_attributes">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedProduct->attributeDropdown( 'link' );
				?>
			</select>
			<input type="text" name="default[]" autocomplete="off" class="wf_default wf_attributes" style=" display: none;">
		</td>
		<td>
			<input type="text" name="suffix[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="output_type[][]"   class="outputType wfnoempty" data-placeholder="<?php esc_attr_e( 'Select Output Type', 'woo-feed' ); ?>" multiple>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->outputTypes();
				?>
			</select>
		</td>
		<td>
			<input type="text" name="limit[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<i class="delRow dashicons dashicons-trash"></i>
		</td>
	</tr>
	<tr>
		<td>
			<i class="wf_sortedtable dashicons dashicons-menu"></i>
		</td>
		<td>
			<select name="mattributes[]"  required class="wf_mattributes">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->googleAttributesDropdown( 'product_type' );
				?>
			</select>
		</td>
		<td>
			<input type="text" name="prefix[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="type[]" class="attr_type wfnoempty">
				<option value="attribute"><?php _e( 'Attribute', 'woo-feed' ); ?></option>
				<option value="pattern"><?php _e( 'Pattern', 'woo-feed' ); ?></option>
			</select>
		</td>
		<td>
			<select name="attributes[]"  class="wf_attr wf_attributes">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedProduct->attributeDropdown( 'product_type' );
				?>
			</select>
			<input type="text" name="default[]" autocomplete="off" class="wf_default wf_attributes" style=" display: none;">
		</td>
		<td>
			<input type="text" name="suffix[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="output_type[][]"   class="outputType wfnoempty" data-placeholder="<?php esc_attr_e( 'Select Output Type', 'woo-feed' ); ?>" multiple>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->outputTypes();
				?>
			</select>
		</td>
		<td>
			<input type="text" name="limit[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<i class="delRow dashicons dashicons-trash"></i>
		</td>
	</tr>
	<tr>
		<td>
			<i class="wf_sortedtable dashicons dashicons-menu"></i>
		</td>
		<td>
			<select name="mattributes[]"  required class="wf_mattributes">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->googleAttributesDropdown( 'current_category' );
				?>
			</select>
		</td>
		<td>
			<input type="text" name="prefix[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="type[]" class="attr_type wfnoempty">
				<option value="attribute"><?php _e( 'Attribute', 'woo-feed' ); ?></option>
				<option value="pattern" selected><?php _e( 'Pattern', 'woo-feed' ); ?></option>
			</select>
		</td>
		<!--	<td>-->
		<!--		<select name="attributes[]"  style=" display: none;" class="wf_attr wf_attributes">-->
		<!--			--><?php // echo $wooFeedProduct->attributeDropdown(); ?>
		<!--		</select>-->
		<!--		<input type="text" name="default[]" autocomplete="off" class="wf_default wf_attributes">-->
		<!--		<br><span style="font-size:x-small;"><a style="color: red" href="http://webappick.helpscoutdocs.com/article/19-how-to-map-store-category-with-merchant-category" target="_blank">Learn More..</a></span>-->
		<!--	</td>-->
		<td>
			<span class="wf_default wf_attributes">
				<select name="default[]" class="selectize" data-placeholder="<?php esc_attr_e( 'Select A Category', 'woo-feed' ); ?>">
					<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->googleTaxonomy();
				?>
				</select>
			</span>
			<select name="attributes[]"  class="wf_attr wf_attributes" style="display:none;">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedProduct->attributeDropdown( '' );
				?>
			</select>
			<span style="font-size:x-small;"><a style="color: red" href="http://webappick.helpscoutdocs.com/article/19-how-to-map-store-category-with-merchant-category" target="_blank">Learn More..</a></span>
		</td>
		<td>
			<input type="text" name="suffix[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="output_type[][]"   class="outputType wfnoempty" data-placeholder="<?php esc_attr_e( 'Select Output Type', 'woo-feed' ); ?>" multiple>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->outputTypes();
				?>
			</select>
		</td>
		<td>
			<input type="text" name="limit[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<i class="delRow dashicons dashicons-trash"></i>
		</td>
	</tr>
	<tr>
		<td>
			<i class="wf_sortedtable dashicons dashicons-menu"></i>
		</td>
		<td>
			<select name="mattributes[]"  required class="wf_mattributes">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->googleAttributesDropdown( 'image' );
				?>
			</select>
		</td>
		<td>
			<input type="text" name="prefix[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="type[]" class="attr_type wfnoempty">
				<option value="attribute"><?php _e( 'Attribute', 'woo-feed' ); ?></option>
				<option value="pattern"><?php _e( 'Pattern', 'woo-feed' ); ?></option>
			</select>
		</td>
		<td>
			<select name="attributes[]"  class="wf_attr wf_attributes">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedProduct->attributeDropdown( 'image' );
				?>
			</select>
			<input type="text" name="default[]" autocomplete="off" class="wf_default wf_attributes" style=" display: none;">
		</td>
		<td>
			<input type="text" name="suffix[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="output_type[][]"   class="outputType wfnoempty" data-placeholder="<?php esc_attr_e( 'Select Output Type', 'woo-feed' ); ?>" multiple>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->outputTypes();
				?>
			</select>
		</td>
		<td>
			<input type="text" name="limit[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<i class="delRow dashicons dashicons-trash"></i>
		</td>
	</tr>
	<tr>
		<td>
			<i class="wf_sortedtable dashicons dashicons-menu"></i>
		</td>
		<td>
			<select name="mattributes[]"  required class="wf_mattributes">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->googleAttributesDropdown( 'condition' );
				?>
			</select>
		</td>
		<td>
			<input type="text" name="prefix[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="type[]" class="attr_type wfnoempty">
				<option value="attribute"><?php _e( 'Attribute', 'woo-feed' ); ?></option>
				<option value="pattern"><?php _e( 'Pattern', 'woo-feed' ); ?></option>
			</select>
		</td>
		<td>
			<select name="attributes[]"  class="wf_attr wf_attributes">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedProduct->attributeDropdown( 'condition' );
				?>
			</select>
			<input type="text" style=" display: none;" name="default[]" autocomplete="off" class="wf_default wf_attributes"
			/>
		</td>
		<td>
			<input type="text" name="suffix[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="output_type[][]"   class="outputType wfnoempty" data-placeholder="<?php esc_attr_e( 'Select Output Type', 'woo-feed' ); ?>" multiple>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->outputTypes();
				?>
			</select>
		</td>
		<td>
			<input type="text" name="limit[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<i class="delRow dashicons dashicons-trash"></i>
		</td>
	</tr>
	<tr>
		<td>
			<i class="wf_sortedtable dashicons dashicons-menu"></i>
		</td>
		<td>
			<select name="mattributes[]"  required class="wf_mattributes">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->googleAttributesDropdown( 'availability' );
				?>
			</select>
		</td>
		<td>
			<input type="text" name="prefix[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="type[]" class="attr_type wfnoempty">
				<option value="attribute"><?php _e( 'Attribute', 'woo-feed' ); ?></option>
				<option value="pattern"><?php _e( 'Pattern', 'woo-feed' ); ?></option>
			</select>
		</td>
		<td>
			<select name="attributes[]"  class="wf_attr wf_attributes">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedProduct->attributeDropdown( 'availability' );
				?>
			</select>
			<input type="text" name="default[]" autocomplete="off" class="wf_default wf_attributes" style=" display: none;">
		</td>
		<td>
			<input type="text" name="suffix[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="output_type[][]"   class="outputType wfnoempty" data-placeholder="<?php esc_attr_e( 'Select Output Type', 'woo-feed' ); ?>" multiple>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->outputTypes();
				?>
			</select>
		</td>
		<td>
			<input type="text" name="limit[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<i class="delRow dashicons dashicons-trash"></i>
		</td>
	</tr>
	<tr>
		<td>
			<i class="wf_sortedtable dashicons dashicons-menu"></i>
		</td>
		<td>
			<select name="mattributes[]"  required class="wf_mattributes">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->googleAttributesDropdown( 'price' );
				?>
			</select>
		</td>
		<td>
			<input type="text" name="prefix[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="type[]" class="attr_type wfnoempty">
				<option value="attribute"><?php _e( 'Attribute', 'woo-feed' ); ?></option>
				<option value="pattern"><?php _e( 'Pattern', 'woo-feed' ); ?></option>
			</select>
		</td>
		<td>
			<select name="attributes[]"  class="wf_attr wf_attributes">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedProduct->attributeDropdown( 'price' );
				?>
			</select>
			<input type="text" name="default[]" autocomplete="off" class="wf_default wf_attributes" style=" display: none;">
		</td>
		<td>
			<input type="text" name="suffix[]" value="<?php echo esc_attr( get_woocommerce_currency() ); ?>" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="output_type[][]"   class="outputType wfnoempty" data-placeholder="<?php esc_attr_e( 'Select Output Type', 'woo-feed' ); ?>" multiple>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->outputTypes( 6 );
				?>
			</select>
		</td>
		<td>
			<input type="text" name="limit[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<i class="delRow dashicons dashicons-trash"></i>
		</td>
	</tr>
	<tr>
		<td>
			<i class="wf_sortedtable dashicons dashicons-menu"></i>
		</td>
		<td>
			<select name="mattributes[]"  required class="wf_mattributes">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->googleAttributesDropdown( 'sku' );
				?>
			</select>
		</td>
		<td>
			<input type="text" name="prefix[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="type[]" class="attr_type wfnoempty">
				<option value="attribute"><?php _e( 'Attribute', 'woo-feed' ); ?></option>
				<option value="pattern"><?php _e( 'Pattern', 'woo-feed' ); ?></option>
			</select>
		</td>
		<td>
			<select name="attributes[]"  class="wf_attr wf_attributes">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedProduct->attributeDropdown( 'sku' );
				?>
			</select>
			<input type="text" name="default[]" autocomplete="off" class="wf_default wf_attributes" style=" display: none;">
		</td>
		<td>
			<input type="text" name="suffix[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="output_type[][]"   class="outputType wfnoempty" data-placeholder="<?php esc_attr_e( 'Select Output Type', 'woo-feed' ); ?>" multiple>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->outputTypes();
				?>
			</select>
		</td>
		<td>
			<input type="text" name="limit[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<i class="delRow dashicons dashicons-trash"></i>
		</td>
	</tr>
	<tr>
		<td>
			<i class="wf_sortedtable dashicons dashicons-menu"></i>
		</td>
		<td>
			<select name="mattributes[]"  required class="wf_mattributes">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->googleAttributesDropdown( 'brand' );
				?>
			</select>
		</td>
		<td>
			<input type="text" name="prefix[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="type[]" class="attr_type wfnoempty">
				<option value="attribute"><?php _e( 'Attribute', 'woo-feed' ); ?></option>
				<option value="pattern" selected><?php _e( 'Pattern', 'woo-feed' ); ?></option>
			</select>
		</td>
		<td>
			<select name="attributes[]" style=" display: none;"  class="wf_attr wf_attributes">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedProduct->attributeDropdown();
				?>
			</select>
			<input type="text" name="default[]" value="<?php echo esc_attr( woo_feed_get_default_brand() ); ?>" autocomplete="off" class="wf_default wf_attributes">
		</td>
		<td>
			<input type="text" name="suffix[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<select name="output_type[][]"   class="outputType wfnoempty" data-placeholder="<?php esc_attr_e( 'Select Output Type', 'woo-feed' ); ?>" multiple>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $wooFeedDropDown->outputTypes();
				?>
			</select>
		</td>
		<td>
			<input type="text" name="limit[]" autocomplete="off" class="wf_ps">
		</td>
		<td>
			<i class="delRow dashicons dashicons-trash"></i>
		</td>
	</tr>
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
