<?php
// Defaults
$vars = array(
	'account_id' => 0,
	'auth_url'   => '',
);

/** @var array $template_vars */
foreach ( $template_vars as $key => $val ) {
	$vars[ $key ] = $val;
} ?>

<div class="integration-header">

	<h3 class="sui-box-title" id="dialogTitle2"><?php echo esc_html( sprintf( __( 'Connect %1$s', Forminator::DOMAIN ), 'AWeber' ) ); ?></h3>

</div>

<form>

	<div class="sui-notice sui-notice-loading">

		<p><?php echo esc_html( sprintf( __( 'We are waiting %1$s authorization...', Forminator::DOMAIN ), 'AWeber' ) ); ?></p>

	</div>

	<?php if ( empty( $vars['account_id'] ) ) : ?>
	<div class="sui-block-content-center">
		<a href="<?php echo esc_attr( $vars['auth_url'] ); ?>"
			target="_blank"
			class="sui-button sui-button-ghost disable-loader">
			<?php esc_html_e( 'Retry', Forminator::DOMAIN ); ?>
		</a>
	</div>
	<?php endif; ?>

</form>

