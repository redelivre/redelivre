<?php
// defaults
$vars = array(
	'error_message' => '',
	'type'          => '',
	'type_error'    => '',
	'types'         => array(),
);
/** @var array $template_vars */
foreach ( $template_vars as $key => $val ) {
	$vars[ $key ] = $val;
}

?>
<div class="integration-header">
	<h3 class="sui-box-title" id="dialogTitle2"><?php echo esc_html( __( 'Select Type', Forminator::DOMAIN ) ); ?></h3>
	<p><?php esc_html_e( 'Select what type of channel Slack will send the message to: a public channel, a private group or a DM channel.', Forminator::DOMAIN ); ?></p>
	<?php if ( ! empty( $vars['error_message'] ) ) : ?>
		<span class="sui-notice sui-notice-error"><p><?php echo esc_html( $vars['error_message'] ); ?></p></span>
	<?php endif; ?>
</div>
<form>

	<div class="sui-form-field <?php echo esc_attr( ! empty( $vars['type_error'] ) ? 'sui-form-field-error' : '' ); ?>">
		<label class="sui-label"><?php esc_html_e( 'Type', Forminator::DOMAIN ); ?>
			<select name="type" class="sui-select sui-form-control">
				<option><?php esc_html_e( 'Please select type', Forminator::DOMAIN ); ?></option>
				<?php foreach ( $vars['types'] as $type_id => $type_name ) : ?>
					<option value="<?php echo esc_attr( $type_id ); ?>" <?php selected( $vars['type'], $type_id ); ?>><?php echo esc_html( $type_name ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php if ( ! empty( $vars['type_error'] ) ) : ?>
				<span class="sui-error-message"><?php echo esc_html( $vars['type_error'] ); ?></span>
			<?php endif; ?>
		</label>
	</div>

	<input type="hidden" name="multi_id" value="<?php echo esc_attr( $vars['multi_id'] ); ?>">
</form>
