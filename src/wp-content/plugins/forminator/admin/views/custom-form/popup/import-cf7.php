<?php
$is_addons = false;
$nonce = wp_create_nonce( 'forminator_save_import_custom_form_cf7' );
$forms = forminator_list_thirdparty_contact_forms('cf7');

// Empty message
$image_empty   = forminator_plugin_url() . 'assets/images/forminator-summary.png';
$image_empty2x = forminator_plugin_url() . 'assets/images/forminator-summary@2x.png';
?>

<div class="forminator-cf7-import">

	<form class="forminator-cf7-import-form" method="post">

		<input type="hidden" name="action" value="forminator_save_import_custom_form_cf7_popup" />
		<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( $nonce ); ?>" />

		<div class="sui-box-body wpmudev-popup-form">

			<div class="sui-notice sui-notice-error wpmudev-ajax-error-placeholder sui-hidden"><p></p></div>

			<?php // ROW: Forms. ?>
			<div class="sui-box-settings-row">

				<div class="sui-box-settings-col-2">

					<h4 class="sui-settings-label sui-dark"><?php esc_html_e( 'Forms', Forminator::DOMAIN ); ?></h4>

					<p class="sui-description" style="margin-bottom: 10px;"><?php printf( esc_html__( "Choose the forms you'd like to import from the Contact Form 7 plugin. Note that we will strip off any %1\$sunsupported form fields and settings%2\$s during the import.", Forminator::DOMAIN ), '<a href="#" class="forminator-toggle-unsupported-settings">', '</a>' ); ?></p>

					<div class="forminator-unsupported-settings fui-dismiss-box fui-flushed" style="display: none;">

						<p class="sui-description" style="margin-bottom: 10px; color: #333; font-weight: bold;"><?php esc_html_e( 'Unsupported form fields and settings', Forminator::DOMAIN ); ?></p>

						<ol class="fui-dismiss-list">
							<li><?php printf( esc_html__( "%1\$s1. Quiz field:%2\$s Forminator doesn't have a built-in quiz field, however, you can enable Google's reCAPTCHA v3 and Honeypot protection on your imported forms.", Forminator::DOMAIN ), '<strong>', '</strong>' ); ?></li>
							<li><?php printf( esc_html__( "%1\$s2. ConstantContact:%2\$s Forminator doesn't integrate directly with ConstantContact. However, you can use %3\$sZapier integration%4\$s to send your leads to ConstantContact.", Forminator::DOMAIN ), '<strong>', '</strong>', '<a href="https://premium.wpmudev.org/blog/zapier-wordpress-form-integrations/" target="_blank">', '</a>' ); ?></li>
							<li><?php printf( esc_html__( "%1\$s3. reCAPTCHA v3 integration:%2\$s At this stage, Forminator can't import your existing reCAPTCHA integration. You can set this up manually on your imported forms once they are transferred.", Forminator::DOMAIN ), '<strong>', '</strong>' ); ?></li>
							<li><?php printf( esc_html__( "%1\$s4. Additional settings:%2\$s Forminator doesn't support CF7’s additional form settings.", Forminator::DOMAIN ), '<strong>', '</strong>' ); ?></li>
							<li><?php printf( esc_html__( "%1\$s5. Custom field IDs:%2\$s Forminator creates a unique ID for each field, and the conditional logic relies on them. However, you can provide a custom CSS class for each field.", Forminator::DOMAIN ), '<strong>', '</strong>' ); ?></li>
						</ol>

						<button role="button" class="fui-dismiss-button forminator-dismiss-unsupported">
							<i class="sui-icon-close sui-sm" aria-hidden="true"></i>
							<?php printf( esc_html__( 'Dismiss%1$s this notice%2$s', Forminator::DOMAIN ), '<span class="sui-screen-reader-text">', '</span>' ); ?>
						</button>

					</div>

					<div class="sui-side-tabs" style="margin-top: 10px;">

						<div class="sui-tabs-menu">

							<label for="cf7_forms_all" class="sui-tab-item active">
								<input
									type="radio"
									name="cf7_forms"
									value="all"
									id="cf7_forms_all"
                                    class="forminator-import-forms"
									checked="checked"
								/>
								<?php esc_html_e( 'All', Forminator::DOMAIN ); ?>
							</label>

							<label for="cf7_forms_specific" class="sui-tab-item">
								<input
									type="radio"
									name="cf7_forms"
									value="specific"
									id="cf7_forms_specific"
                                    class="forminator-import-forms"
									data-tab-menu="cf7_forms"
								/>
								<?php esc_html_e( 'Specific Forms', Forminator::DOMAIN ); ?>
							</label>

						</div>

						<div class="sui-tabs-content">

							<div data-tab-content="cf7_forms" class="sui-tab-content sui-tab-boxed">

								<div class="sui-form-field">

									<label class="sui-label"><?php esc_html_e( 'Choose Forms', Forminator::DOMAIN ); ?></label>

									<select id="forminator-choose-import-form" class="sui-select" multiple="multiple" name="cf7-form-id[]">

										<?php
										if ( ! empty( $forms ) ) :

											foreach ( $forms as $key => $value ) {

												echo sprintf(
													'<option value="%f">%s</option>',
													absint( $value->ID ),
													esc_html( $value->post_title )
												);

											}

										endif;
										?>

									</select>

								</div>

							</div>

						</div>

					</div>

				</div>

			</div>

			<?php // ROW: Add-ons. ?>
			<div class="sui-box-settings-row">

				<div class="sui-box-settings-col-2">

					<h4 class="sui-settings-label sui-dark"><?php esc_html_e( 'Add-ons', Forminator::DOMAIN ); ?></h4>

					<p class="sui-description" style="margin-bottom: 20px;"><?php printf( esc_html__( "Choose the Contact Form 7 add-ons you wish to import form data and settings from. %1\$sNote:%2\$s The importer only supports the most widely used add-ons. For less common add-ons, you'll need to manually configure the equivalent functionality in those imported forms.", Forminator::DOMAIN ), '<strong>', '</strong>' ); ?></p>

					<?php if ( is_plugin_active( 'flamingo/flamingo.php' ) ) : $is_addons = true; ?>

						<div class="fui-addons-option">

							<label for="forminator-cf7-addon-flamingo" class="sui-checkbox sui-checkbox-sm sui-checkbox-stacked" style="margin-bottom: 2px;">
								<input
									type="checkbox"
									name="cf7-addons[]"
									value="flamingo"
									id="forminator-cf7-addon-flamingo"
									aria-labelledby="listings-cf7-addon-label listings-cf7-addon-flamingo"
									aria-describedby="listings-cf7-addon-message"
									checked="checked"
								/>
								<span aria-hidden="true"></span>
								<span id="listings-cf7-addon-flamingo"><?php esc_html_e( 'Flamingo', Forminator::DOMAIN ); ?></span>
							</label>

							<span class="sui-description sui-checkbox-description"><?php esc_html_e( 'Import your form submissions from Flamingo and show them on the submissions page.', Forminator::DOMAIN ); ?></span>

						</div>

					<?php
					endif;

					if ( is_plugin_active( 'contact-form-7-honeypot/honeypot.php' ) ) : $is_addons = true; ?>

						<div class="fui-addons-option">

							<label for="forminator-cf7-addon-honeypot" class="sui-checkbox sui-checkbox-sm sui-checkbox-stacked" style="margin-bottom: 2px;">
								<input
									type="checkbox"
									name="cf7-addons[]"
									value="honeypot"
									id="forminator-cf7-addon-honeypot"
									aria-labelledby="listings-cf7-addon-label listings-cf7-addon-honeypot"
									aria-describedby="listings-cf7-addon-message"
									checked="checked"
								/>
								<span aria-hidden="true"></span>
								<span id="listings-cf7-addon-honeypot"><?php esc_html_e( 'Honeypot for Contact Form 7', Forminator::DOMAIN ); ?></span>
							</label>

							<span class="sui-description sui-checkbox-description"><?php esc_html_e( 'Enable honeypot protection on the imported forms in Forminator.', Forminator::DOMAIN ); ?></span>

						</div>

					<?php endif;

					if ( is_plugin_active( 'contact-form-cfdb7/contact-form-cfdb-7.php' ) ) : $is_addons = true; ?>

						<div class="fui-addons-option">

							<label for="forminator-cf7-addon-cfdb7" class="sui-checkbox sui-checkbox-sm sui-checkbox-stacked" style="margin-bottom: 2px;">
								<input
										type="checkbox"
										name="cf7-addons[]"
										value="cfdb7"
										id="forminator-cf7-addon-cfdb7"
										aria-labelledby="listings-cf7-addon-label listings-cf7-addon-cfdb7"
										aria-describedby="listings-cf7-addon-message"
										checked="checked"
								/>
								<span aria-hidden="true"></span>
								<span id="listings-cf7-addon-cfdb7"><?php esc_html_e( 'Contact Form 7 Database Addon – CFDB7', Forminator::DOMAIN ); ?></span>
							</label>

							<span class="sui-description sui-checkbox-description"><?php esc_html_e( 'Import your form submissions from CFDB7 add-on and show them on the submissions page.', Forminator::DOMAIN ); ?></span>

						</div>

					<?php endif;

					if ( is_plugin_active( 'wpcf7-redirect/wpcf7-redirect.php' ) ) : $is_addons = true; ?>

						<div class="fui-addons-option">

							<label for="forminator-cf7-addon-redirection" class="sui-checkbox sui-checkbox-sm sui-checkbox-stacked" style="margin-bottom: 2px;">
								<input
										type="checkbox"
										name="cf7-addons[]"
										value="redirection"
										id="forminator-cf7-addon-redirection"
										aria-labelledby="listings-cf7-addon-label listings-cf7-addon-redirection"
										aria-describedby="listings-cf7-addon-message"
										checked="checked"
								/>
								<span aria-hidden="true"></span>
								<span id="listings-cf7-addon-redirection"><?php esc_html_e( 'Contact Form 7 Redirection', Forminator::DOMAIN ); ?></span>
							</label>

							<span class="sui-description sui-checkbox-description"><?php esc_html_e( 'Import redirection URL settings and apply them on your imported forms in Forminator. Note that Forminator doesn’t support passing form fields as query parameters into redirect URL, redirection delay, and running a script after form submission.', Forminator::DOMAIN ); ?></span>

						</div>

					<?php endif;

					if ( is_plugin_active( 'cf7-conditional-fields/contact-form-7-conditional-fields.php' ) ) : $is_addons = true; ?>

						<div class="fui-addons-option">

							<label for="forminator-cf7-addon-conditional" class="sui-checkbox sui-checkbox-sm sui-checkbox-stacked" style="margin-bottom: 2px;">
								<input
										type="checkbox"
										name="cf7-addons[]"
										value="conditional"
										id="forminator-cf7-addon-conditional"
										aria-labelledby="listings-cf7-addon-label listings-cf7-addon-conditional"
										aria-describedby="listings-cf7-addon-message"
										checked="checked"
								/>
								<span aria-hidden="true"></span>
								<span id="listings-cf7-addon-conditional"><?php esc_html_e( 'Contact Form 7 Conditional Fields', Forminator::DOMAIN ); ?></span>
							</label>

							<span class="sui-description sui-checkbox-description"><?php esc_html_e( 'Import your form fields conditions and apply them automatically on your imported forms.', Forminator::DOMAIN ); ?></span>

						</div>

					<?php endif;

					if ( is_plugin_active( 'contact-form-submissions/contact-form-submissions.php' ) ) : $is_addons = true; ?>

						<div class="fui-addons-option">

							<label for="forminator-cf7-addon-submissions" class="sui-checkbox sui-checkbox-sm sui-checkbox-stacked" style="margin-bottom: 2px;">
								<input
										type="checkbox"
										name="cf7-addons[]"
										value="submissions"
										id="forminator-cf7-addon-submissions"
										aria-labelledby="listings-cf7-addon-label listings-cf7-addon-submissions"
										aria-describedby="listings-cf7-addon-message"
										checked="checked"
								/>
								<span aria-hidden="true"></span>
								<span id="listings-cf7-addon-submissions"><?php esc_html_e( 'Contact Form Submissions', Forminator::DOMAIN ); ?></span>
							</label>

							<span class="sui-description sui-checkbox-description"><?php esc_html_e( 'Import your form submissions and show them on the submissions page.', Forminator::DOMAIN ); ?></span>

						</div>

					<?php endif;

					if ( is_plugin_active( 'wpcf7-recaptcha/wpcf7-recaptcha.php' ) ) : $is_addons = true; ?>

						<div class="fui-addons-option">

							<label for="forminator-cf7-addon-recaptchav2" class="sui-checkbox sui-checkbox-sm sui-checkbox-stacked" style="margin-bottom: 2px;">
								<input
										type="checkbox"
										name="cf7-addons[]"
										value="recaptchav2"
										id="forminator-cf7-addon-recaptchav2"
										aria-labelledby="listings-cf7-addon-label listings-cf7-addon-recaptchav2"
										aria-describedby="listings-cf7-addon-message"
										checked="checked"
								/>
								<span aria-hidden="true"></span>
								<span id="listings-cf7-addon-recaptchav2"><?php esc_html_e( 'Contact Form 7 - reCAPTCHA v2', Forminator::DOMAIN ); ?></span>
							</label>

							<span class="sui-description sui-checkbox-description"><?php esc_html_e( 'Import your reCaptcha v2 API keys and configure the reCaptcha v2 on your imported forms.', Forminator::DOMAIN ); ?></span>

						</div>

					<?php endif;

					if ( is_plugin_active( 'advanced-cf7-db/advanced-cf7-db.php' ) ) : $is_addons = true; ?>

						<div class="fui-addons-option">

							<label for="forminator-cf7-addon-advanced_cf7" class="sui-checkbox sui-checkbox-sm sui-checkbox-stacked" style="margin-bottom: 2px;">
								<input
										type="checkbox"
										name="cf7-addons[]"
										value="advanced_cf7"
										id="forminator-cf7-addon-advanced_cf7"
										aria-labelledby="listings-cf7-addon-label listings-cf7-addon-advanced_cf7"
										aria-describedby="listings-cf7-addon-message"
										checked="checked"
								/>
								<span aria-hidden="true"></span>
								<span id="listings-cf7-addon-advanced_cf7"><?php esc_html_e( 'Advanced Contact form 7 DB', Forminator::DOMAIN ); ?></span>
							</label>

							<span class="sui-description sui-checkbox-description"><?php esc_html_e( 'Import your form submissions and show them on the submissions page.', Forminator::DOMAIN ); ?></span>

						</div>

					<?php endif; ?>

					<?php  if ( ! $is_addons ) { ?>

						<div class="sui-notice">
							<p><?php esc_html_e( "We couldn't find any supported add-ons.", Forminator::DOMAIN ); ?></p>
						</div>

					<?php } ?>

				</div>

			</div>

		</div>

		<div class="sui-box-footer">

			<div class="sui-actions-right">

				<button class="sui-button sui-button-blue wpmudev-action-ajax-cf7-import">
					<span class="sui-loading-text"><?php esc_html_e( 'Begin Import', Forminator::DOMAIN ); ?></span>
					<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
				</button>

			</div>

		</div>

	</form>
</div>

<div class="forminator-cf7-importing sui-hidden">

	<div class="sui-box-body wpmudev-popup-form">

		<div class="sui-box-settings-row">

			<div class="sui-box-settings-col-2">

				<p><?php esc_html_e( 'Please keep this modal open while we import your Contact Form 7 forms and add-ons. It will only take a few seconds.', Forminator::DOMAIN ); ?></p>

				<div class="sui-progress-block">

					<div class="sui-progress">

						<span class="sui-progress-icon" aria-hidden="true">
							<i class="sui-icon-loader sui-loading"></i>
						</span>

						<span class="sui-progress-text">
							<span>50%</span>
						</span>

						<div class="sui-progress-bar" aria-hidden="true">
							<span style="width: 50%"></span>
						</div>

					</div>

				</div>

				<span class="sui-progress-state"><?php printf( esc_html__( 'Importing submissions from %s…', Forminator::DOMAIN ), 'Flamingo' ); ?></span>

			</div>

		</div>

	</div>

	<?php if ( forminator_is_show_branding() ): ?>
		<img
			src="<?php echo esc_url( $image_empty ); ?>"
			srcset="<?php echo esc_url( $image_empty2x ); ?> 1x, <?php echo esc_url( $image_empty2x ); ?> 2x"
			class="sui-image sui-image-center"
			aria-hidden="true"
		/>
	<?php endif; ?>

</div>

<div class="forminator-cf7-imported sui-hidden">

	<div class="sui-box-body wpmudev-popup-form">

		<div class="sui-notice sui-notice-success">
			<p><?php esc_html_e( 'Your selected forms from Contact Form 7 and the add-ons imported successfully.', Forminator::DOMAIN ); ?></p>
		</div>

		<div class="fui-dismiss-box fui-flushed">

			<p class="sui-description" style="margin-bottom: 10px; color: #333; font-weight: bold;"><?php esc_html_e( 'Recommendations', Forminator::DOMAIN ); ?></p>

			<p class="sui-description" style="margin-bottom: 5px;"><?php esc_html_e( 'Following are the next recommended steps:', Forminator::DOMAIN ); ?></p>

			<ol class="fui-dismiss-list">
				<li><?php esc_html_e( '1. Visit Forminator and preview your forms to make sure everything looks perfect and adjust your forms with the additional settings Forminator offers as per your needs.', Forminator::DOMAIN ); ?></li>
				<li><?php esc_html_e( '2. Use the Gutenberg block or shortcode to embed your forms in the required places.', Forminator::DOMAIN ); ?></li>
				<li><?php esc_html_e( '3. Deactivate Contact Form 7 and the add-ons if you don’t wish to use them anymore.', Forminator::DOMAIN ); ?></li>
			</ol>

		</div>

	</div>

	<div class="sui-box-footer" style="padding-top: 0; border-top: 0;">

		<button class="sui-button sui-button-ghost forminator-popup-close" data-a11y-dialog-hide="forminator-popup">
			<span class="sui-loading-text"><?php esc_html_e( 'Close', Forminator::DOMAIN ); ?></span>
			<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
		</button>

		<div class="sui-actions-right">

			<a href="<?php echo esc_url( forminator_get_disable_url( 'cf7', 'contact-form-7/wp-contact-form-7.php' ) ); ?>" class="sui-button">
				<span class="sui-loading-text">
					<i class="sui-icon-power-on-off" aria-hidden="true"></i>
					<?php esc_html_e( 'Deactivate contact form 7', Forminator::DOMAIN ); ?>
				</span>
				<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
			</a>

		</div>

	</div>

	<?php if ( forminator_is_show_branding() ): ?>
		<img
			src="<?php echo esc_url( $image_empty ); ?>"
			srcset="<?php echo esc_url( $image_empty2x ); ?> 1x, <?php echo esc_url( $image_empty2x ); ?> 2x"
			class="sui-image sui-image-center"
			aria-hidden="true"
		/>
	<?php endif; ?>

</div>

<div class="forminator-cf7-imported-fail sui-hidden">

	<div class="sui-box-body wpmudev-popup-form">
        <?php $support_url = FORMINATOR_PRO ? 'https://premium.wpmudev.org/hub/support/' :'https://wordpress.org/support/plugin/forminator'; ?>
		<p><?php printf( esc_html__( 'We have encountered an error while importing your forms from Contact Form 7 and selected add-ons. Unable to solve this? Contact our %1$ssupport%2$s team for further help.', Forminator::DOMAIN ), '<a href="' . $support_url . '" target="_blank">', '</a>' ); ?></p>

		<div class="sui-notice sui-notice-error">
			<p><?php esc_html_e( "We couldn't find any compatible data to import.", Forminator::DOMAIN ); ?></p>
		</div>

	</div>

	<div class="sui-box-footer" style="padding-top: 0; border-top: 0;">

		<button class="sui-button sui-button-ghost forminator-popup-close" data-a11y-dialog-hide="forminator-popup">
			<span class="sui-loading-text"><?php esc_html_e( 'Cancel', Forminator::DOMAIN ); ?></span>
			<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
		</button>

		<div class="sui-actions-right">

			<button class="sui-button forminator-retry-import">
				<span class="sui-loading-text"><?php esc_html_e( 'Retry Import', Forminator::DOMAIN ); ?></span>
				<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
			</button>

		</div>

	</div>

	<?php if ( forminator_is_show_branding() ): ?>
		<img
			src="<?php echo esc_url( $image_empty ); ?>"
			srcset="<?php echo esc_url( $image_empty2x ); ?> 1x, <?php echo esc_url( $image_empty2x ); ?> 2x"
			class="sui-image sui-image-center"
			aria-hidden="true"
		/>
	<?php endif; ?>

</div>
