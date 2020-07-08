<?php
// defaults
$vars = array(
	'error_message'     => '',
	'name'              => '',
	'name_error'        => '',
	'multi_id'          => '',
	'new_zap_url'       => '',
	'webhook_url'       => '',
	'webhook_url_error' => '',
);
/** @var array $template_vars */
foreach ( $template_vars as $key => $val ) {
	$vars[ $key ] = $val;
}

?>
<div class="integration-header">
	<h3 class="sui-box-title" id="dialogTitle2"><?php esc_html_e( 'Setup Webhook', Forminator::DOMAIN ); ?></h3>
	<p><?php esc_html_e( 'Put your ZAP Webhook URL below. ', Forminator::DOMAIN ); ?></p>
	<?php if ( ! empty( $vars['error_message'] ) ) : ?>
		<span class="sui-notice sui-notice-error"><p><?php echo esc_html( $vars['error_message'] ); ?></p></span>
	<?php endif; ?>
</div>
<form enctype="multipart/form-data">
	<div class="sui-form-field <?php echo esc_attr( ! empty( $vars['name_error'] ) ? 'sui-form-field-error' : '' ); ?>">
		<label class="sui-label"><?php esc_html_e( 'Zapier Integration Name', Forminator::DOMAIN ); ?></label>
		<div class="sui-control-with-icon">
			<input type="text"
				name="name"
				placeholder="<?php esc_attr_e( 'Friendly Name', Forminator::DOMAIN ); ?>"
				value="<?php echo esc_attr( $vars['name'] ); ?>"
				class="sui-form-control"
			/>
			<i class="sui-icon-web-globe-world" aria-hidden="true"></i>
		</div>
		<?php if ( ! empty( $vars['name_error'] ) ) : ?>
			<span class="sui-error-message"><?php echo esc_html( $vars['name_error'] ); ?></span>
		<?php endif; ?>
	</div>
	<div class="sui-form-field <?php echo esc_attr( ! empty( $vars['webhook_url_error'] ) ? 'sui-form-field-error' : '' ); ?>">
		<label class="sui-label"><?php esc_html_e( 'Webhook URL', Forminator::DOMAIN ); ?></label>
		<div class="sui-control-with-icon">
			<input
					type="text"
					name="webhook_url"
					placeholder="<?php esc_attr_e( 'Webhook URL', Forminator::DOMAIN ); ?>"
					value="<?php echo esc_attr( $vars['webhook_url'] ); ?>"
					class="sui-form-control"/>
			<i class="sui-icon-link" aria-hidden="true"></i>
		</div>
		<?php if ( ! empty( $vars['webhook_url_error'] ) ) : ?>
			<span class="sui-error-message"><?php echo esc_html( $vars['webhook_url_error'] ); ?></span>
		<?php endif; ?>
	</div>
	<input type="hidden" name="multi_id" value="<?php echo esc_attr( $vars['multi_id'] ); ?>">
</form>
<div class="sui-notice sui-notice-warning">
	<p>
		<?php
		echo sprintf(/* translators: ... */
			esc_html__( 'Please go %1$shere%2$s if you do not have any ZAP created. Remember to choose %3$sWebhooks by Zapier%4$s as Trigger App.', Forminator::DOMAIN ),
			'<a href="' . esc_url( $vars['new_zap_url'] ) . '" target="_blank">',
			'</a>',
			'<strong>',
			'</strong>'
		);
		?>
	</p>
</div>
