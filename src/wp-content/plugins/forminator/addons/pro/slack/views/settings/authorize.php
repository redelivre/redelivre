<?php
// defaults
$vars = array(
	'auth_url' => '',
	'token'    => '',
);
/** @var array $template_vars */
foreach ( $template_vars as $key => $val ) {
	$vars[ $key ] = $val;
}
?>
<div class="integration-header">
	<h3 class="sui-box-title" id="dialogTitle2"><?php echo esc_html( sprintf( __( 'Connect %1$s', Forminator::DOMAIN ), 'Slack' ) ); ?></h3>
	<?php if ( ! empty( $vars['token'] ) ) : ?>
		<p><?php esc_html_e( 'Click button below to re-authorize.', Forminator::DOMAIN ); ?> </p>
	<?php else : ?>
		<p><?php esc_html_e( 'Authorize Forminator to connect with your Slack in order to send data from your forms.', Forminator::DOMAIN ); ?></p>
	<?php endif ?>
</div>
<?php if ( empty( $vars['token'] ) ) : ?>
	<a href="<?php echo esc_attr( $vars['auth_url'] ); ?>" target="_blank" class="sui-button sui-button-primary forminator-addon-connect"><?php esc_html_e( 'AUTHORIZE', Forminator::DOMAIN ); ?></a>
<?php else : ?>
	<a href="<?php echo esc_attr( $vars['auth_url'] ); ?>" target="_blank" class="sui-button sui-button-primary forminator-addon-connect"><?php esc_html_e( 'RE-AUTHORIZE', Forminator::DOMAIN ); ?></a>
<?php endif ?>
