<?php
$section = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : 'dashboard';
$v2_captcha_key              = get_option( 'forminator_captcha_key', '' );
$v2_captcha_secret           = get_option( 'forminator_captcha_secret', '' );
$v2_invisible_captcha_key    = get_option( 'forminator_v2_invisible_captcha_key', '' );
$v2_invisible_captcha_secret = get_option( 'forminator_v2_invisible_captcha_secret', '' );
$v3_captcha_key              = get_option( 'forminator_v3_captcha_key', '' );
$v3_captcha_secret           = get_option( 'forminator_v3_captcha_secret', '' );
$captcha_language            = get_option( 'forminator_captcha_language', '' );
$nonce                       = wp_create_nonce( 'forminator_save_popup_captcha' );

$new = true;
?>

<div class="sui-box" data-nav="recaptcha" style="<?php echo esc_attr( 'recaptcha' !== $section ? 'display: none;' : '' ); ?>">

	<div class="sui-box-header">
		<h2 class="sui-box-title"><?php esc_html_e( 'Google reCAPTCHA', Forminator::DOMAIN ); ?></h2>
	</div>

	<form class="forminator-settings-save" action="">

		<div class="sui-box-body">

			<div class="sui-box-settings-row">

				<div class="sui-box-settings-col-1">
					<span class="sui-settings-label"><?php esc_html_e( 'Configure reCaptcha', Forminator::DOMAIN ); ?></span>
					<span class="sui-description"><?php esc_html_e( 'Enter reCAPTCHA keys and the language preference to use reCAPTCHA field in your forms.', Forminator::DOMAIN ); ?></span>
				</div>

				<div class="sui-box-settings-col-2">

					<div class="sui-form-field">

						<span class="sui-settings-label"><?php esc_html_e( 'API Keys', Forminator::DOMAIN ); ?></span>
						<span class="sui-description" style="margin-bottom: 10px;"><?php /* translators: ... */ printf( esc_html( __( 'Enter the API keys for each reCAPTCHA type you want to use in your forms. Note that each reCAPTCHA type requires a different set of API keys. %1$sGenerate API keys%2$s', Forminator::DOMAIN ) ), '<a href="https://www.google.com/recaptcha/admin#list" target="_blank">', '</a>' ); ?></span>

						<div class="sui-tabs sui-tabs-overflow">

							<div role="tablist" class="sui-tabs-menu">
								<button type="button" role="tab" id="v2-checkbox" class="sui-tab-item active" aria-controls="v2-checkbox-tab" aria-selected="true"><?php esc_html_e( 'v2 Checkbox', Forminator::DOMAIN ); ?></button>
								<button type="button" role="tab" id="v2-invisible" class="sui-tab-item" aria-controls="v2-invisible-tab" aria-selected="false" tabindex="-1"><?php esc_html_e( 'v2 Invisible', Forminator::DOMAIN ); ?></button>
								<button type="button" role="tab" id="recaptcha-v3" class="sui-tab-item" aria-controls="v3-recaptcha-tab" aria-selected="false" tabindex="-1"><?php esc_html_e( 'v3 reCaptcha', Forminator::DOMAIN ); ?></button>
							</div>

							<div class="sui-tabs-content">

								<?php // TAB: v2 Checkbox ?>
								<div tabindex="0" role="tabpanel" id="v2-checkbox-tab" class="sui-tab-content active" aria-labelledby="v2-checkbox">

									<span class="sui-description"><?php esc_html_e( 'Enter the API keys for reCAPTCHA v2 Checkbox type below:', Forminator::DOMAIN ); ?></span>

									<div class="sui-form-field">
										<label for="v2_captcha_key" id="v2checkbox-sitekey-label" class="sui-label"><?php esc_html_e( 'Site Key', Forminator::DOMAIN ); ?></label>
										<input
											type="text"
											name="v2_captcha_key"
											placeholder="<?php esc_html_e( 'Enter your site key here', Forminator::DOMAIN ); ?>"
											value="<?php echo esc_attr( $v2_captcha_key ); ?>"
											id="v2_captcha_key"
											class="sui-form-control"
											aria-labelledby="v2checkbox-sitekey-label"
										/>
									</div>

									<div class="sui-form-field">
										<label for="v2_captcha_secret" id="v2checkbox-secretkey-label" class="sui-label"><?php esc_html_e( 'Secret Key', Forminator::DOMAIN ); ?></label>
										<input
											type="text"
											name="v2_captcha_secret"
											placeholder="<?php esc_html_e( 'Enter your secret key here', Forminator::DOMAIN ); ?>"
											value="<?php echo esc_attr( $v2_captcha_secret ); ?>"
											id="v2_captcha_secret"
											class="sui-form-control"
											aria-labelledby="v2checkbox-secretkey-label"
										/>
									</div>

									<div class="sui-form-field">
										<label class="sui-label"><?php esc_html_e( 'reCAPTCHA Preview', Forminator::DOMAIN ); ?></label>
										<div id="v2-recaptcha-preview">
											<p class="fui-loading-dialog">
												<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
											</p>
										</div>
										<span class="sui-description"><?php esc_html_e( "If you see any errors in the preview, make sure the key you've entered are valid, and you've listed your domain name while generating the keys.", Forminator::DOMAIN ); ?></span>
									</div>

								</div>

								<?php // TAB: v2 Invisible ?>
								<div tabindex="0" role="tabpanel" id="v2-invisible-tab" class="sui-tab-content" aria-labelledby="v2-invisible" hidden>

									<span class="sui-description"><?php esc_html_e( 'Enter the API keys for reCAPTCHA v2 Invisible type below:', Forminator::DOMAIN ); ?></span>

									<div class="sui-form-field">
										<label for="invisible_captcha_key" id="v2invisible-sitekey-label" class="sui-label"><?php esc_html_e( 'Site Key', Forminator::DOMAIN ); ?></label>
										<input
											type="text"
											name="v2_invisible_captcha_key"
											placeholder="<?php esc_html_e( 'Enter your site key here', Forminator::DOMAIN ); ?>"
											value="<?php echo esc_attr( $v2_invisible_captcha_key ); ?>"
											id="invisible_captcha_key"
											class="sui-form-control"
											aria-labelledby="v2invisible-sitekey-label"
										/>
									</div>

									<div class="sui-form-field">
										<label for="invisible_captcha_secret" id="v2invisible-secretkey-label" class="sui-label"><?php esc_html_e( 'Secret Key', Forminator::DOMAIN ); ?></label>
										<input
											type="text"
											name="v2_invisible_captcha_secret"
											placeholder="<?php esc_html_e( 'Enter your secret key here', Forminator::DOMAIN ); ?>"
											value="<?php echo esc_attr( $v2_invisible_captcha_secret ); ?>"
											id="invisible_captcha_secret"
											class="sui-form-control"
											aria-labelledby="v2invisible-secretkey-label"
										/>
									</div>

									<div class="sui-form-field">
										<label class="sui-label"><?php esc_html_e( 'reCAPTCHA Preview', Forminator::DOMAIN ); ?></label>

										<div id="v2-invisible-recaptcha-preview">
											<p class="fui-loading-dialog">
												<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
											</p>
										</div>

										<span class="sui-description"><?php esc_html_e( "If you see any errors in the preview, make sure the key you've entered are valid, and you've listed your domain name while generating the keys.", Forminator::DOMAIN ); ?></span>
									</div>

								</div>

								<?php // TAB: v3 reCaptcha ?>
								<div tabindex="0" role="tabpanel" id="v3-recaptcha-tab" class="sui-tab-content" aria-labelledby="recaptcha-v3" hidden>

									<span class="sui-description"><?php esc_html_e( 'Enter the API keys for reCAPTCHA v3 type below:', Forminator::DOMAIN ); ?></span>

									<div class="sui-form-field">
										<label for="v3_captcha_key" id="v3recaptcha-sitekey-label" class="sui-label"><?php esc_html_e( 'Site Key', Forminator::DOMAIN ); ?></label>
										<input
											type="text"
											name="v3_captcha_key"
											placeholder="<?php esc_html_e( 'Enter your site key here', Forminator::DOMAIN ); ?>"
											value="<?php echo esc_attr( $v3_captcha_key ); ?>"
											id="v3_captcha_key"
											class="sui-form-control"
											aria-labelledby="v3recaptcha-sitekey-label"
										/>
									</div>

									<div class="sui-form-field">
										<label for="v3_captcha_secret" id="v3recaptcha-secretkey-label" class="sui-label"><?php esc_html_e( 'Secret Key', Forminator::DOMAIN ); ?></label>
										<input
											type="text"
											name="v3_captcha_secret"
											placeholder="<?php esc_html_e( 'Enter your secret key here', Forminator::DOMAIN ); ?>"
											value="<?php echo esc_attr( $v3_captcha_secret ); ?>"
											id="v3_captcha_secret"
											class="sui-form-control"
											aria-labelledby="v3recaptcha-secretkey-label"
										/>
									</div>

									<div class="sui-form-field">

										<label class="sui-label"><?php esc_html_e( 'reCAPTCHA Preview', Forminator::DOMAIN ); ?></label>

										<div id="v3-recaptcha-preview">
											<p class="fui-loading-dialog">
												<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
											</p>
										</div>

										<span class="sui-description"><?php esc_html_e( "If you see any errors in the preview, make sure the key you've entered are valid, and you've listed your domain name while generating the keys.", Forminator::DOMAIN ); ?></span>

									</div>

								</div>

							</div>

						</div>

					</div>

					<div class="sui-form-field">

						<span class="sui-settings-label"><?php esc_html_e( 'Language', Forminator::DOMAIN ); ?></span>
						<span class="sui-description" style="margin-bottom: 10px;"><?php esc_html_e( 'By default, we\'ll show the reCAPTCHA in your website\'s language.', Forminator::DOMAIN ); ?></span>

						<div style="width: 100%; max-width: 240px;">

							<select name="captcha_language" id="captcha_language" class="sui-select">
								<?php $languages = forminator_get_captcha_languages(); ?>
								<option value=""><?php esc_html_e( 'Automatic', Forminator::DOMAIN ); ?></option>
								<?php foreach ( $languages as $key => $lang ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $captcha_language, $key ); ?>><?php echo esc_html( $lang ); ?></option>
								<?php endforeach; ?>
							</select>

						</div>

					</div>

				</div>

			</div>
		</div>

		<div class="sui-box-footer">

			<div class="sui-actions-right">

				<button
					class="sui-button sui-button-blue wpmudev-action-done"
					data-title="<?php esc_attr_e( 'reCaptcha settings', Forminator::DOMAIN ); ?>"
					data-action="captcha"
					data-nonce="<?php echo esc_attr( $nonce ); ?>"
				>
					<span class="sui-loading-text"><?php esc_html_e( 'Save Settings', Forminator::DOMAIN ); ?></span>
					<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
				</button>

			</div>

		</div>

	</form>

</div>
