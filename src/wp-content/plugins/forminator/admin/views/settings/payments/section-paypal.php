<?php
$plugin_url              = forminator_plugin_url();
$paypal_min_php_version  = apply_filters( 'forminator_payments_paypal_min_php_version', '5.3' );
$paypal_loaded           = forminator_payment_lib_paypal_version_loaded( '1.14.0' );
$paypal_is_configured    = false;
$forminator_currencies   = forminator_pp_currency_list();
$paypal_default_currency = 'USD';

if ( $paypal_loaded ) {

	try {
		$paypal = new Forminator_PayPal_Express();

		$paypal_default_currency = $paypal->get_default_currency();
		if ( $paypal->is_test_ready() || $paypal->is_live_ready() ) {
			$paypal_is_configured = true;
		}
	} catch ( Forminator_Gateway_Exception $e ) {
		$paypal_is_configured = false;
	}
}
?>
<div class="sui-box-settings-col-1">

	<span class="sui-settings-label"><?php esc_html_e( 'PayPal', Forminator::DOMAIN ); ?></span>

	<span class="sui-description"><?php esc_html_e( 'Use PayPal Checkout to process payments in your forms.', Forminator::DOMAIN ); ?></span>

</div>

<div class="sui-box-settings-col-2">

	<?php if ( version_compare( PHP_VERSION, $paypal_min_php_version, 'lt' ) ) : ?>

		<div class="sui-notice sui-notice-warning">

			<p><?php /* translators: ... */ printf( esc_html__( 'To be able to use PayPal Payments feature please upgrade your PHP to %1$sversion %2$s%3$s or above.', Forminator::DOMAIN ), '<strong>', esc_html( $paypal_min_php_version ), '</strong>' ); ?></p>

		</div>

	<?php elseif ( ! $paypal_loaded ) : ?>

		<div class="sui-notice sui-notice-warning">

			<p><?php esc_html_e( 'Failed to load PayPal Library, possibly conflict with other plugins. Please contact our support .', Forminator::DOMAIN ); ?></p>

		</div>

	<?php else : ?>

		<span class="sui-settings-label"><?php esc_html_e( 'Authorization', Forminator::DOMAIN ); ?></span>

		<span class="sui-description"><?php esc_html_e( 'Connect your PayPal business account with Forminator to use PayPal field for collecting payments in your forms.', Forminator::DOMAIN ); ?></span>

		<?php if ( ! $paypal_is_configured ) { ?>

			<div class="sui-form-field" style="margin-top: 10px;">

				<button
						class="sui-button paypal-connect-modal"
						type="button"
						data-modal-image="<?php echo esc_url( $plugin_url . 'assets/images/paypal-logo.png' ); ?>"
						data-modal-image-x2="<?php echo esc_url( $plugin_url . 'assets/images/paypal-logo@2x.png' ); ?>"
						data-modal-title="<?php esc_html_e( 'Connect PayPal Account', Forminator::DOMAIN ); ?>"
						data-modal-nonce="<?php echo esc_html( wp_create_nonce( 'forminator_paypal_settings_modal' ) ); ?>"
				>
					<?php esc_html_e( 'Connect To PayPal', Forminator::DOMAIN ); ?>
				</button>

			</div>

		<?php } else { ?>
			<table class="sui-table" style="margin-top: 10px;">

				<thead>

				<tr>
					<th><?php esc_html_e( 'Account Type', Forminator::DOMAIN ); ?></th>
					<th colspan="2"><?php esc_html_e( 'Client Id', Forminator::DOMAIN ); ?></th>
				</tr>

				</thead>

				<tbody>

				<tr>
					<td class="sui-table-title"><?php esc_html_e( 'Sandbox', Forminator::DOMAIN ); ?></td>
					<td colspan="2"><span style="display: block; word-break: break-all;"><?php echo esc_html( $paypal->get_sandbox_id() ); ?></span></td>
				</tr>

				<tr>
					<td class="sui-table-title"><?php esc_html_e( 'Live', Forminator::DOMAIN ); ?></td>
					<td colspan="2"><span style="display: block; word-break: break-all;"><?php echo esc_html( $paypal->get_live_id() ); ?></span></td>
				</tr>

				</tbody>

				<tfoot>

				<tr>

					<td colspan="3">

						<div class="fui-buttons-alignment">

							<form class="forminator-settings-save">

								<button
										class="sui-button sui-button-ghost wpmudev-open-modal"
										data-modal="disconnect-paypal"
										data-modal-title="<?php esc_attr_e( 'Disconnect PayPal Account', Forminator::DOMAIN ); ?>"
										data-modal-content="<?php esc_attr_e( 'Are you sure you want to disconnect your PayPal Account? This will affect the forms using the PayPal field.', Forminator::DOMAIN ); ?>"
										data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminatorSettingsRequest' ) ); ?>"
								>

										<span class="sui-loading-text">
											<?php esc_html_e( 'Disconnect', Forminator::DOMAIN ); ?>
										</span>

									<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>

								</button>

							</form>

							<button
									class="sui-button paypal-connect-modal"
									type="button"
									data-modal-image="<?php echo esc_url( $plugin_url . 'assets/images/paypal-logo.png' ); ?>"
									data-modal-image-x2="<?php echo esc_url( $plugin_url . 'assets/images/paypal-logo@2x.png' ); ?>"
									data-modal-title="<?php esc_html_e( 'Connect PayPal Account', Forminator::DOMAIN ); ?>"
									data-modal-nonce="<?php echo esc_html( wp_create_nonce( 'forminator_paypal_settings_modal' ) ); ?>"
							>
								<?php esc_html_e( 'Configure', Forminator::DOMAIN ); ?>
							</button>

						</div>

					</td>

				</tr>

				</tfoot>

			</table>

			<div class="sui-form-field">

				<label for="forminator-stripe-currency" class="sui-settings-label"><?php esc_html_e( 'Default charge currency', Forminator::DOMAIN ); ?></label>

				<span class="sui-description" aria-describedby="forminator-stripe-currency"><?php esc_html_e( 'Choose the default charge currency for your PayPal payments. You can override this while setting up the PayPal field in your forms.', Forminator::DOMAIN ); ?></span>

				<div style="max-width: 240px; display: block; margin-top: 10px;">

					<select class="sui-select" id="forminator-paypal-currency" name="paypal-default-currency">
						<?php foreach ( $forminator_currencies as $currency => $currency_nice ) : ?>
							<option value="<?php echo esc_attr( $currency ); ?>" <?php echo selected( $currency, $paypal_default_currency ); ?>><?php echo esc_html( $currency ); ?></option>
						<?php endforeach; ?>
					</select>

				</div>

			</div>
		<?php } ?>
	<?php endif; ?>

</div>
