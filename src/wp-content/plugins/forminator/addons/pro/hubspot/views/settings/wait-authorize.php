<?php
// defaults
$vars = array(
	'auth_url' => '',
	'token'    => '',
);
/** @var array $template_vars */
foreach ( $template_vars as $key => $val ) {
	$vars[ $key ] = $val;
} ?>

<div class="integration-header">

	<h3 id="dialogTitle2" class="sui-box-title">
		<?php
			/* translators: ... */
			echo esc_html( sprintf( __( 'Connect %1$s', Forminator::DOMAIN ), 'HubSpot' ) );
		?>
	</h3>

	<?php if ( ! empty( $vars['token'] ) ) : ?>
		<div class="sui-notice sui-notice-success" style="margin-bottom: -30px;">
			<p>
				<?php
					/* translators: ... */
					echo esc_html( sprintf( __( 'Your %1$s account is already authorized.', Forminator::DOMAIN ), 'HubSpot' ) );
				?>
			</p>
		</div>
	<?php else : ?>
		<span class="sui-description" style="color: #666666; margin-top: 20px; line-height: 22px;"><?php esc_html_e( "Authenticate your HubSpot account using the button below. Note that you'll be taken to the HubSpot website to grant access to Forminator and then redirected back.", Forminator::DOMAIN ); ?></span>
	<?php endif; ?>

</div>

<?php if ( empty( $vars['token'] ) ) : ?>

	<div class="sui-block-content-center" style="margin-top: -10px; margin-bottom: -20px;">

		<button type="button" class="sui-button">
			<span class="sui-loading-text"><?php esc_html_e( 'Authenticating', Forminator::DOMAIN ); ?></span>
			<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
		</button>

	</div>

<?php endif; ?>
