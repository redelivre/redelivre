<?php
// defaults
$vars = array(
	'token'               => '',
	'error_message'       => '',
	'client_id'           => '',
	'client_secret'       => '',
	'client_secret_error' => '',
	'client_id_error'     => '',
	'redirect_url'        => '',
);
/** @var array $template_vars */
foreach ( $template_vars as $key => $val ) {
	$vars[ $key ] = $val;
}
?>
<div class="integration-header">
	<h3 class="sui-box-title" id="dialogTitle2"><?php echo esc_html( sprintf( __( 'Setup %1$s Client', Forminator::DOMAIN ), 'Slack' ) ); ?></h3>
	<?php if ( ! empty( $vars['token'] ) ) : ?>
		<p><?php esc_html_e( 'Your Slack account is already authorized. Edit info below to re-authorize.', Forminator::DOMAIN ); ?> </p>
	<?php else : ?>
		<p><?php esc_html_e( 'Setup Slack to be used by Forminator to communicating with Slack server.', Forminator::DOMAIN ); ?></p>
		<?php if ( ! empty( $vars['error_message'] ) ) : ?>
			<span class="sui-notice sui-notice-error"><p><?php echo esc_html( $vars['error_message'] ); ?></p></span>
		<?php endif; ?>
	<?php endif ?>
</div>
<form>
	<div class="sui-form-field <?php echo esc_attr( ! empty( $vars['client_id_error'] ) ? 'sui-form-field-error' : '' ); ?>">
		<label class="sui-label"><?php esc_html_e( 'Client ID', Forminator::DOMAIN ); ?></label>
		<input
				class="sui-form-control"
				name="client_id" placeholder="<?php echo esc_attr( __( 'Client ID', Forminator::DOMAIN ) ); ?>"
				value="<?php echo esc_attr( $vars['client_id'] ); ?>">
		<?php if ( ! empty( $vars['client_id_error'] ) ) : ?>
			<span class="sui-error-message"><?php echo esc_html( $vars['client_id_error'] ); ?></span>
		<?php endif; ?>
	</div>
	<div class="sui-form-field <?php echo esc_attr( ! empty( $vars['client_secret_error'] ) ? 'sui-form-field-error' : '' ); ?>">
		<label class="sui-label"><?php esc_html_e( 'Client Secret', Forminator::DOMAIN ); ?></label>
		<input
				class="sui-form-control"
				name="client_secret" placeholder="<?php echo esc_attr( __( 'Client Secret', Forminator::DOMAIN ) ); ?>"
				value="<?php echo esc_attr( $vars['client_secret'] ); ?>">
		<?php if ( ! empty( $vars['client_secret_error'] ) ) : ?>
			<span class="sui-error-message"><?php echo esc_html( $vars['client_secret_error'] ); ?></span>
		<?php endif; ?>
		<span class="sui-description">
				<?php esc_html_e( 'Follow these instructions to retrieve your Client ID and Secret.', Forminator::DOMAIN ); ?>
			<ol class="instructions" id="clientid-instructions">
					<li>
						<?php echo sprintf(
							__( 'Go %1$s to create new Slack App.', Forminator::DOMAIN ),
							'<a href="https://api.slack.com/apps?new_app=1" target="_blank">' . __( 'here', Forminator::DOMAIN ) . '</a>'
						); //wpcs: xss ok?>
					</li>
					<li>
						<?php esc_html_e(
							'You will need to enter App Name and Development Slack Workspace.',
							Forminator::DOMAIN
						); ?>
					</li>
					<li>
						<?php echo sprintf(
							__( 'Once the Project creation is completed go to the %1$s. Then scroll through %2$s, to take a note of %3$s and %4$s.', Forminator::DOMAIN ),
							'<strong>' . __( 'Basic Information', Forminator::DOMAIN ) . '</strong>',
							'<strong>' . __( 'App Credentials', Forminator::DOMAIN ) . '</strong>',
							'<strong>' . __( 'Client ID', Forminator::DOMAIN ) . '</strong>',
							'<strong>' . __( 'Client Secret', Forminator::DOMAIN ) . '</strong>'
						); //wpcs: xss ok?>
					</li>
					<li>
						<?php echo sprintf(
							__( 'Next, go to the %1$s &gt; %2$s &gt; %3$s section.', Forminator::DOMAIN ),
							'<strong>' . __( 'Features', Forminator::DOMAIN ) . '</strong>',
							'<strong>' . __( 'OAuth & Permissions', Forminator::DOMAIN ) . '</strong>',
							'<strong>' . __( 'Redirect URLs', Forminator::DOMAIN ) . '</strong>'
						); //wpcs: xss ok?>
						<ol>
							<li>
								<?php echo sprintf(
									__( 'Click %1$s.', Forminator::DOMAIN ),
									'<strong>' . __( 'Add a new Redirect URL', Forminator::DOMAIN ) . '</strong>'
								); //wpcs: xss ok?>
							</li>
							<li>
								<?php esc_html_e( 'In the shown input field, put this value below', Forminator::DOMAIN ); ?>
								<pre class="sui-code-snippet"><?php echo esc_html( ! empty( $vars['redirect_url'] ) ? $vars['redirect_url'] : '' ); ?></pre>.</li>
							<li>
								<?php echo sprintf(
									__( 'Then click the %1$s button.', Forminator::DOMAIN ),
									'<strong>' . __( 'Add', Forminator::DOMAIN ) . '</strong>'
								); //wpcs: xss ok?>
							</li>
							<li>
								<?php echo sprintf(
									__( 'Then click the %1$s button.', Forminator::DOMAIN ),
									'<strong>' . __( 'Save URLs', Forminator::DOMAIN ) . '</strong>'
								); //wpcs: xss ok?>
							</li>
						</ol>
				</ol>
			</span>
	</div>
</form>
