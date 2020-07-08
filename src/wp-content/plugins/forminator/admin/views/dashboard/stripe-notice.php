<div
	id="forminator-stripe-sca"
	class="sui-dialog sui-dialog-onboard"
	aria-hidden="true"
>

	<div class="sui-dialog-overlay sui-fade-out" data-a11y-dialog-hide="forminator-stripe-sca"></div>

	<div
			class="sui-dialog-content sui-bounce-out"
			aria-labelledby="dialogTitle"
			aria-describedby="dialogDescription"
			role="dialog"
	>

		<div class="sui-slider">

			<ul class="sui-slider-content" role="document">

				<li class="sui-current sui-loaded" data-slide="1">

					<div class="sui-box">

						<div class="sui-box-banner" role="banner" aria-hidden="true">
							<img
								src="<?php echo forminator_plugin_url(); // phpcs:ignore ?>assets/images/sca-cover.png"
								srcset="<?php echo forminator_plugin_url(); // phpcs:ignore ?>assets/images/sca-cover@2x.png 2x"
							/>
						</div>

						<div class="sui-box-header sui-block-content-center">

							<h2 id="dialogTitle" class="sui-box-title"><?php esc_html_e( 'Stripe is SCA compliant', Forminator::DOMAIN ); ?></h2>

							<p id="dialogDescription" class="sui-description" style="margin-bottom: 0;"><?php printf( esc_html__( "We have replaced the Stripe Checkout modal with Stripe Elements to make our Stripe integration %1\$sSCA compliant%2\$s. The Stripe field now adds an inline field to collect your customer's credit or debit card details.", Forminator::DOMAIN ), '<a href="https://stripe.com/gb/guides/strong-customer-authentication" target="_blank">', '</a>' ); ?></p>

							<button data-a11y-dialog-hide="forminator-stripe-sca" class="sui-dialog-close" aria-label="<?php esc_html_e( 'Close this dialog window', Forminator::DOMAIN ); ?>"></button>

						</div>

						<div class="sui-box-body">

							<div class="sui-form-field">

								<p class="sui-description" style="margin-bottom: 5px; color: #333;"><strong style="font-weight: bold;"><?php esc_html_e( 'Recommendations', Forminator::DOMAIN ); ?></strong></p>

								<p class="sui-description" style="margin-bottom: 0;"><?php esc_html_e( 'Your existing forms with Stripe field are automatically updated. However, we recommend checking the following to ensure they work fine:', Forminator::DOMAIN ); ?></p>

								<ol style="margin-left: 0;">

									<li class="sui-description">1. <?php printf( esc_html__( "There isn't any label added to the Stripe field for existing forms. We recommend adding a label such as \"%1\$sCredit/Debit Card%2\$s\" to your Stripe field.", Forminator::DOMAIN ), '<strong>', '</strong>' ); ?></li>

									<li class="sui-description">2. <?php esc_html_e( "Preview your form to ensure that the Stripe field is matching with other form fields' styles.", Forminator::DOMAIN ); ?></li>

									<li class="sui-description">3. <?php esc_html_e( 'Make sure that Stripe field settings are mapped correctly after the update. There are some new settings worth checking as well.', Forminator::DOMAIN ); ?></li>

								</ol>

							</div>

							<div class="sui-form-field">

								<p class="sui-description" style="margin-bottom: 5px; color: #333;"><strong style="font-weight: bold;"><?php esc_html_e( 'Affected Forms', Forminator::DOMAIN ); ?></strong></p>

								<p class="sui-description" style="margin-bottom: 5px;"><?php esc_html_e( 'Following are the forms which are affected by this updated. We recommend going through each of them and follow the recommendations above:', Forminator::DOMAIN ); ?></p>

								<?php foreach ( $this->stripeModules() as $module ) : ?>

									<ul class="fui-list-fields">

										<li>
											<span class="fui-list-label"><?php echo esc_html( $module['title'] ); ?></span>
											<a href="<?php echo esc_url( menu_page_url( 'forminator-cform-wizard', false ) . '&id=' . $module['id'] ); ?>" class="sui-button-icon" target="_blank">
												<i class="sui-icon-pencil" aria-hidden="true"></i>
												<span class="sui-screen-reader-text"><?php esc_html_e( 'Edit Field', Forminator::DOMAIN ); ?></span>
											</a>
										</li>

									</ul>

								<?php endforeach; ?>

							</div>

						</div>

					</div>

					<p class="sui-onboard-skip"><a href="#" data-a11y-dialog-hide="forminator-stripe-sca"><?php esc_html_e( "I'll check this later", Forminator::DOMAIN ); ?></a></p>

				</li>

			</ul>

		</div>

	</div>

</div>
