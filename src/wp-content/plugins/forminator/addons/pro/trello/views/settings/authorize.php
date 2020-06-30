<?php
// defaults
$vars = array(
	'connected_account' => array(),
	'auth_url'          => '',
	'token'             => '',
	'is_connected'             => false,
);
/** @var array $template_vars */
foreach ( $template_vars as $key => $val ) {
	$vars[ $key ] = $val;
}
?>
<div class="integration-header">
	<h3 class="sui-box-title" id="dialogTitle2"><?php echo esc_html( sprintf( __( 'Connect %1$s', Forminator::DOMAIN ), 'Trello' ) ); ?></h3>
	<?php if ( ! empty( $vars['connected_account'] ) ) : ?>
		<p><?php esc_html_e( 'Your Trello account is now authorized', Forminator::DOMAIN ); ?> </p>
		<strong><?php echo esc_html( $vars['connected_account']['email'] ); ?></strong>
	<?php else : ?>
		<p><?php esc_html_e( 'Authorize Forminator to connect with your Trello account in order to send data from your forms.', Forminator::DOMAIN ); ?></p>
	<?php endif ?>
</div>
<?php if ( empty( $vars['token'] ) ) : ?>
	<a href="<?php echo esc_attr( $vars['auth_url'] ); ?>" target="_blank" class="sui-button sui-button-primary forminator-addon-connect"><?php esc_html_e( 'AUTHORIZE', Forminator::DOMAIN ); ?></a>
<?php endif ?>
<?php if ( $vars['is_connected'] ) : ?>
	<button class="sui-button sui-button-ghost forminator-addon-disconnect"><?php esc_html_e( 'DISCONNECT', Forminator::DOMAIN ); ?></button>
<?php endif ?>

