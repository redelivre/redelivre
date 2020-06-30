<?php
// defaults
$vars = array(
	'connected_account' => array(),
	'auth_url'          => '',
	'token'             => '',
);
/** @var array $template_vars */
foreach ( $template_vars as $key => $val ) {
	$vars[ $key ] = $val;
}
?>
<div class="integration-header">
	<h3 class="sui-box-title" id="dialogTitle2"></h3>
	<p class="" aria-label="Loading content">
		<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
	</p>
	<p><?php esc_html_e( 'We are waiting for authorization from Trello...', Forminator::DOMAIN ); ?></p>
</div>
<?php if ( empty( $vars['token'] ) ) : ?>
	<a href="<?php echo esc_attr( $vars['auth_url'] ); ?>" target="_blank" class="sui-button disable-loader"><?php esc_html_e( 'RETRY', Forminator::DOMAIN ); ?></a>
<?php endif ?>

