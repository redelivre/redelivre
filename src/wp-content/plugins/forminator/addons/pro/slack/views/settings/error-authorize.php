<?php
// defaults
$vars = array(
	'auth_url'      => '',
	'error_message' => '',
);
/** @var array $template_vars */
foreach ( $template_vars as $key => $val ) {
	$vars[ $key ] = $val;
}
?>
<div class="integration-header">
	<h3 class="sui-box-title" id="dialogTitle2">
		<?php
		/* translators: ... */
		echo esc_html( sprintf( __( 'Failed to add %1$s', Forminator::DOMAIN ), 'Slack' ) );
		?>
	</h3>
	<p>
		<?php if ( ! empty( $vars['error_message'] ) ) : ?>
			<?php echo esc_html( $vars['error_message'] ); ?>
		<?php endif; ?>
	</p>
</div>
<a href="<?php echo esc_attr( $vars['auth_url'] ); ?>" target="_blank" class="sui-button forminator-addon-connect"><?php esc_html_e( 'RETRY', Forminator::DOMAIN ); ?></a>
