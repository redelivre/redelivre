<?php
// Defaults
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
} ?>

<div class="integration-header">

	<h3 class="sui-box-title" id="dialogTitle2"><?php echo esc_html( sprintf( /* translators: ... */ __( 'Setup %1$s Client', Forminator::DOMAIN ), 'Google Sheets' ) ); ?></h3>

	<?php if ( ! empty( $vars['token'] ) ) : ?>
		<span class="sui-description" style="margin-top: 20px;"><?php esc_html_e( 'Your Google Sheets account is already authorized. Edit info below to re-authorize.', Forminator::DOMAIN ); ?></span>
	<?php else : ?>
		<span class="sui-description" style="margin-top: 20px;">
			<?php esc_html_e( 'Setup Google Client to be used by Forminator to communicating with Google Sheets server.', Forminator::DOMAIN ); ?>
		</span>
		<?php if ( ! empty( $vars['error_message'] ) ) : ?>
			<div class="sui-notice sui-notice-error">
				<p><?php echo esc_html( $vars['error_message'] ); ?></p>
			</div>
		<?php endif; ?>
	<?php endif ?>

</div>

<form>

	<div class="sui-form-field<?php echo esc_attr( ! empty( $vars['client_id_error'] ) ? ' sui-form-field-error' : '' ); ?>">

		<label class="sui-label"><?php esc_html_e( 'Client ID', Forminator::DOMAIN ); ?></label>

		<div class="sui-control-with-icon">

			<input name="client_id"
				placeholder="<?php echo esc_attr( __( 'Client ID', Forminator::DOMAIN ) ); ?>"
				value="<?php echo esc_attr( $vars['client_id'] ); ?>"
				class="sui-form-control" />

			<i class="sui-icon-profile-male" aria-hidden="true"></i>

		</div>

		<?php if ( ! empty( $vars['client_id_error'] ) ) : ?>
			<span class="sui-error-message"><?php echo esc_html( $vars['client_id_error'] ); ?></span>
		<?php endif; ?>

	</div>

	<div class="sui-form-field<?php echo esc_attr( ! empty( $vars['client_secret_error'] ) ? ' sui-form-field-error' : '' ); ?>">

		<label class="sui-label"><?php esc_html_e( 'Client Secret', Forminator::DOMAIN ); ?></label>

		<div class="sui-control-with-icon">

			<input name="client_secret"
				placeholder="<?php echo esc_attr( __( 'Client Secret', Forminator::DOMAIN ) ); ?>"
				value="<?php echo esc_attr( $vars['client_secret'] ); ?>"
				class="sui-form-control" />

			<i class="sui-icon-key" aria-hidden="true"></i>

		</div>

		<?php if ( ! empty( $vars['client_secret_error'] ) ) : ?>
			<span class="sui-error-message"><?php echo esc_html( $vars['client_secret_error'] ); ?></span>
		<?php endif; ?>

	</div>

	<div class="sui-border-frame sui-description">

		<span style="display: block;"><?php esc_html_e( 'Follow these instructions to retrieve your Client ID and Secret.', Forminator::DOMAIN ); ?></span>

		<ol class="instructions" id="clientid-instructions">

			<li>
				<?php
				echo sprintf(/* translators: ... */
					esc_html__( 'Go to the %1$sGoogle API Console%2$s', Forminator::DOMAIN ),
					'<a href="https://console.developers.google.com/cloud-resource-manager" target="_blank">',
					'</a>'
				); //phpcs:ignore Standard.Category.SniffName.ErrorCode
				?>
			</li>

			<li><?php esc_html_e( 'Select an existing project or create a new one. If creating a new project, you will need to enter a name, but the ID is not important and can be ignored.', Forminator::DOMAIN ); ?></li>

			<li>
				<?php
				echo sprintf(/* translators: ... */
					esc_html__( 'Once the Project creation is completed go to the %1$sAPI Manager%2$s. Here you need to enable the %3$sGoogle Drive API%4$s and %5$sGoogle Sheets API%6$s.', Forminator::DOMAIN ),
					'<strong>',
					'</strong>',
					'<strong>',
					'</strong>',
					'<strong>',
					'</strong>'
				); //phpcs:ignore Standard.Category.SniffName.ErrorCode
				?>
			</li>

			<li>
				<?php
				echo sprintf(/* translators: ... */
					esc_html__( 'Next, go to the %1$sAPI Manager &gt; Credentials%2$s section.', Forminator::DOMAIN ),
					'<strong>',
					'</strong>'
				); //phpcs:ignore Standard.Category.SniffName.ErrorCode
				?>
			</li>

				<ol style="margin-top: 15px; margin-bottom: 15px;">

					<li>
						<?php
						echo sprintf(/* translators: ... */
							esc_html__( 'Click %1$sCreate Credentials &gt; OAuth 2.0 client ID%2$s.', Forminator::DOMAIN ),
							'<strong>',
							'</strong>'
						); //phpcs:ignore Standard.Category.SniffName.ErrorCode
						?>
					</li>

					<li>
						<?php
						echo sprintf(/* translators: ... */
							esc_html__( 'In the popup select the %1$sApplication Type%2$s as %3$sWeb application%4$s.', Forminator::DOMAIN ),
							'<strong>',
							'</strong>',
							'<strong>',
							'</strong>'
						); //phpcs:ignore Standard.Category.SniffName.ErrorCode
						?>
					</li>

					<li>
						<?php
						echo sprintf(/* translators: ... */
							esc_html__( 'In the field %1$sAuthorized redirect URI%2$s, put this value below:', Forminator::DOMAIN ),
							'<strong>',
							'</strong>'
						); //phpcs:ignore Standard.Category.SniffName.ErrorCode
						?>
						<pre class="sui-code-snippet"><?php echo esc_html( ! empty( $vars['redirect_url'] ) ? $vars['redirect_url'] : '' ); ?></pre>
					</li>

					<li>
						<?php
						echo sprintf(/* translators: ... */
							esc_html__( 'Then click the %1$sCreate Client ID%2$s button, then navigate to %1$sAPI Keys%2$s section.', Forminator::DOMAIN ),
							'<strong>',
							'</strong>'
						); //phpcs:ignore Standard.Category.SniffName.ErrorCode
						?>
					</li>

				</ol>

			</li>

			<li><?php esc_html_e( 'After the popup closes copy the Client ID and Client Secret from the Google page and paste into the form fields.', Forminator::DOMAIN ); ?></li>

		</ol>

	</div>

</form>
