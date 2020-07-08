<?php
// defaults
$vars = array(
	'error_message'        => '',
	'multi_id'             => '',
	'ad_tracking'          => '',
	'ad_tracking_error'    => '',
	'tags_fields'          => array(),
	'tags_selected_fields' => array(),
	'fields'               => array(),
	'tags_error'           => '',
);
/** @var array $template_vars */
foreach ( $template_vars as $key => $val ) {
	$vars[ $key ] = $val;
}

?>
<div class="integration-header">

	<h3 class="sui-box-title" id="dialogTitle2"><?php echo esc_html( __( 'Additional Options', Forminator::DOMAIN ) ); ?></h3>

	<span class="sui-description" style="margin-top: 20px;"><?php esc_html_e( 'Configure additional options for AWeber integration.', Forminator::DOMAIN ); ?></span>

	<?php if ( ! empty( $vars['error_message'] ) ) : ?>
		<div class="sui-notice sui-notice-error">
			<p><?php echo esc_html( $vars['error_message'] ); ?></p>
		</div>
	<?php endif; ?>

</div>

<form>

	<div class="sui-form-field<?php echo esc_attr( ! empty( $vars['ad_tracking_error'] ) ? ' sui-form-field-error' : '' ); ?>">

		<label class="sui-label"><?php esc_html_e( 'Ad Tracking', Forminator::DOMAIN ); ?></label>

		<div class="sui-insert-variables">

			<input name="ad_tracking"
				placeholder="<?php echo esc_attr( __( 'Ad Tracking', Forminator::DOMAIN ) ); ?>"
				value="<?php echo esc_attr( $vars['ad_tracking'] ); ?>"
				id="ad_tracking"
				class="sui-form-control" />

			<select data-textarea-id="ad_tracking">

				<?php foreach ( $vars['fields'] as $field ) : ?>
					<option value="{<?php echo esc_attr( $field['element_id'] ); ?>}"
						data-content="{<?php echo esc_attr( $field['element_id'] ); ?>}">
						<?php echo esc_html( $field['field_label'] ); ?>
					</option>
				<?php endforeach; ?>

			</select>

		</div>

		<?php if ( ! empty( $vars['ad_tracking_error'] ) ) : ?>
			<span class="sui-error-message"><?php echo esc_html( $vars['ad_tracking_error'] ); ?></span>
		<?php endif; ?>

		<span class="sui-description"><?php echo ( sprintf( __( 'Use 20 or fewer characters to label subscribers based on how they signed up. Find complete article %1$shere%2$s.', Forminator::DOMAIN ), '<a href="https://help.aweber.com/hc/en-us/articles/204028836-What-is-Ad-Tracking-" target="_blank">', '</a>' ) ); // phpcs:ignore ?></span>

	</div>

	<div class="sui-form-field <?php echo esc_attr( ! empty( $vars['tags_error'] ) ? 'sui-form-field-error' : '' ); ?>">

		<label class="sui-label" for="tags"><?php esc_html_e( 'Tags', Forminator::DOMAIN ); ?></label>

		<select class="sui-select fui-multi-select" name="tags[]" id="tags"
				multiple="multiple"
				data-reorder="1"
				data-tags="true"
				data-token-separators="[',']"
				data-placeholder=""
				data-allow-clear="false">

			<?php foreach ( $vars['tags_selected_fields'] as $forminator_field ) : ?>
				<option value="<?php echo esc_attr( $forminator_field['element_id'] ); ?>"
					selected="selected">
					<?php echo esc_html( $forminator_field['field_label'] ); ?>
				</option>
			<?php endforeach; ?>

			<?php foreach ( $vars['tags_fields'] as $forminator_field ) : ?>
				<option value="{<?php echo esc_attr( $forminator_field['element_id'] ); ?>}">
					<?php echo esc_html( $forminator_field['field_label'] . ' | ' . $forminator_field['element_id'] ); ?>
				</option>
			<?php endforeach; ?>

		</select>

		<?php if ( ! empty( $vars['tags_error'] ) ) : ?>
			<span class="sui-error-message"><?php echo esc_html( $vars['tags_error'] ); ?></span>
		<?php endif; ?>

		<span class="sui-description"><?php echo ( sprintf( __( 'Available fields value or free text can be used as tags. Have a campaign that’s triggered when a subscriber is tagged? Enter the appropriate tag to add the subscriber to the campaign, and they will immediately begin receiving your messages. More info about AWeber subscriber tags can be found %1$shere%2$s.', Forminator::DOMAIN ), '<a href="https://help.aweber.com/hc/en-us/articles/212677877-How-do-I-use-Tags-with-Campaigns-" target="_blank">', '</a>' ) ); // phpcs:ignore ?></span>

	</div>

	<input type="hidden" name="multi_id" value="<?php echo esc_attr( $vars['multi_id'] ); ?>">

</form>
