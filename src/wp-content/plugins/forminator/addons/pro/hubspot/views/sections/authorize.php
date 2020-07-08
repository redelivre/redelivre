<?php
// defaults
$vars = array(
	'error_message' => '',
	'is_close'      => false,
);
/** @var array $template_vars */
foreach ( $template_vars as $key => $val ) {
	$vars[ $key ] = $val;
}
?>

<div id="forminator-integrations" class="wpmudev-settings--box">
	<div class="sui-box">
		<div class="sui-box-header">
			<h2 class="sui-box-title"><?php esc_html_e( 'Authorizing HubSpot', Forminator::DOMAIN ); ?></h2>
		</div>
		<div class="sui-box-body">
			<?php if ( ! empty( $vars['error_message'] ) ) : ?>
				<span class="sui-notice sui-notice-error"><p><?php echo esc_html( $vars['error_message'] ); ?></p></span>
			<?php elseif ( $vars['is_close'] ) : ?>
				<span class="sui-notice sui-notice-success">
					<p>
						<?php esc_html_e( 'Successfully authorized HubSpot, you can go back to integration settings.', Forminator::DOMAIN ); ?>
					</p>
				</span>
			<?php else : ?>
				<span class="sui-notice sui-notice-loading">
					<p><?php esc_html_e( 'Please Wait...', Forminator::DOMAIN ); ?></p>
				</span>
			<?php endif; ?>
		</div>
	</div>
</div>

<script>
	(function ($) {
		$(document).ready(function (e) {
			<?php if ( $vars['is_close'] ) : ?>
			setTimeout(function () {
				window.close();
			}, 3000);
			<?php endif; ?>
		});
	})(jQuery);
</script>
