<?php
// Defaults
$vars = array(
	'error_message' => '',
	'is_connected'  => false,
);

/** @var array $template_vars */
foreach ( $template_vars as $key => $val ) {
	$vars[ $key ] = $val;
} ?>

<div class="integration-header">

	<h3 id="dialogTitle2" class="sui-box-title"><?php echo esc_html( sprintf( __( 'Activate %1$s', Forminator::DOMAIN ), 'Zapier' ) ); ?></h3>

	<span class="sui-description" style="margin-top: 20px;"><?php esc_html_e( 'Activate Zapier to start using it on your forms.', Forminator::DOMAIN ); ?></span>

	<?php if ( ! empty( $vars['is_connected'] ) ) : ?>
		<div class="sui-notice sui-notice-success" style="text-align: left;">
			<p><?php esc_html_e( 'Zapier is already active.', Forminator::DOMAIN ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $vars['error_message'] ) ) : ?>
		<div class="sui-notice sui-notice-error" style="text-align: left;">
			<p><?php echo esc_html( $vars['error_message'] ); ?></p>
		</div>
	<?php endif; ?>

</div>

<form>

	<input type="hidden" value="1" name="connect">

</form>
