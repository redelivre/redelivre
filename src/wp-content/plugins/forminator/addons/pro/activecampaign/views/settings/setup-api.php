<?php
// Defaults
$vars = array(
	'error_message' => '',
	'api_url'       => '',
	'api_url_error' => '',
	'api_key'       => '',
	'api_key_error' => '',
);

/** @var array $template_vars */
foreach ( $template_vars as $key => $val ) {
	$vars[ $key ] = $val;
} ?>

<div class="integration-header">

	<h3 class="sui-box-title" id="dialogTitle2"><?php echo esc_html( sprintf( __( 'Configure %1$s API', Forminator::DOMAIN ), 'ActiveCampaign' ) ); ?></h3>

	<span class="sui-description" style="margin-top: 20px;"><?php esc_html_e( 'Setup ActiveCampaign API Access.', Forminator::DOMAIN ); ?></span>

	<?php if ( ! empty( $vars['error_message'] ) ) : ?>
		<span class="sui-notice sui-notice-error"><p><?php echo esc_html( $vars['error_message'] ); ?></p></span>
	<?php endif; ?>

</div>

<form>
	
	<div class="sui-form-field<?php echo esc_attr( ! empty( $vars['api_url_error'] ) ? ' sui-form-field-error' : '' ); ?>">

		<label class="sui-label"><?php esc_html_e( 'API URL', Forminator::DOMAIN ); ?></label>

		<div class="sui-control-with-icon">
			
			<input name="api_url"
				placeholder="<?php echo esc_attr( sprintf( __( 'Enter %1$s API URL', Forminator::DOMAIN ), 'ActiveCampaign' ) ); ?>"
				value="<?php echo esc_attr( $vars['api_url'] ); ?>"
				class="sui-form-control" />

			<i class="sui-icon-link" aria-hidden="true"></i>

		</div>

		<?php if ( ! empty( $vars['api_url_error'] ) ) : ?>
			<span class="sui-error-message"><?php echo esc_html( $vars['api_url_error'] ); ?></span>
		<?php endif; ?>

	</div>

	<div class="sui-form-field<?php echo esc_attr( ! empty( $vars['api_key_error'] ) ? ' sui-form-field-error' : '' ); ?>">

		<label class="sui-label"><?php esc_html_e( 'API Key', Forminator::DOMAIN ); ?></label>

		<div class="sui-control-with-icon">
			
			<input name="api_key"
				placeholder="<?php echo esc_attr( sprintf( __( 'Enter %1$s API Key', Forminator::DOMAIN ), 'ActiveCampaign' ) ); ?>"
				value="<?php echo esc_attr( $vars['api_key'] ); ?>"
				class="sui-form-control" />

			<i class="sui-icon-key" aria-hidden="true"></i>

		</div>

		<?php if ( ! empty( $vars['api_key_error'] ) ) : ?>
			<span class="sui-error-message"><?php echo esc_html( $vars['api_key_error'] ); ?></span>
		<?php endif; ?>

	</div>

</form>
