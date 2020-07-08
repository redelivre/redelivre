<?php
$section = isset( $_GET['section'] ) ? $_GET['section'] : 'dashboard'; // wpcs csrf ok.

?>

<div class="sui-box" data-nav="import" style="<?php echo esc_attr( 'import' !== $section ? 'display: none;' : '' ); ?>">

	<div class="sui-box-header">
		<h2 class="sui-box-title"><?php esc_html_e( 'Import', Forminator::DOMAIN ); ?></h2>
	</div>

	<form class="forminator-settings-save" action="">

		<div class="sui-box-body">

			<div class="sui-box-settings-row">

				<div class="sui-box-settings-col-1">

					<h2 class="sui-settings-label"><?php esc_html_e( 'Third-Party Plugins', Forminator::DOMAIN ); ?></h2>

					<p class="sui-description"><?php esc_html_e( 'Use this tool to import your existing forms from other third-party form builder plugins automatically to Forminator.', Forminator::DOMAIN ); ?></p>

				</div>

				<div class="sui-box-settings-col-2">

					<div class="sui-form-field">

						<h3 class="sui-settings-label"><?php esc_html_e( 'Contact Form 7', Forminator::DOMAIN ); ?></h3>

						<p class="sui-description" style="margin-bottom: 10px;"><?php esc_html_e( 'Import your existing forms and the relevant plugin settings from the Contact Form 7. The importer supports a few widely used add-ons as well.', Forminator::DOMAIN ); ?></p>

						<?php if ( forminator_is_import_plugin_enabled( 'cf7' ) ) : ?>

							<button
								role="button"
								class="sui-button wpmudev-open-modal"
								data-modal="import_cform_cf7"
								data-modal-title=""
								data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminator_popup_import_cform_cf7' ) ); ?>"
							>
								<i class="sui-icon-upload-cloud" aria-hidden="true"></i> <?php esc_html_e( 'Import', Forminator::DOMAIN ); ?>
							</button>

						<?php else : ?>

							<div class="sui-notice" style="margin-top: 10px;">
								<p><?php echo esc_html__( 'Contact Form 7 plugin is not active on your website.', Forminator::DOMAIN ); ?></p>
							</div>

						<?php endif; ?>

					</div>

					<?php if ( forminator_is_import_plugin_enabled( 'ninjaforms' ) ) : ?>

						<div class="sui-form-field">

							<h3 class="sui-settings-label"><?php esc_html_e( 'Ninja Forms', Forminator::DOMAIN ); ?></h3>

							<p class="sui-description" style="margin-bottom: 10px;"><?php esc_html_e( 'Import your forms from Ninja Forms', Forminator::DOMAIN ); ?></p>

							<button
								role="button"
								class="sui-button wpmudev-open-modal"
								data-modal="import_cform_ninja"
								data-modal-title=""
								data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminator_popup_import_cform_ninjaforms' ) ); ?>"
							>
								<i class="sui-icon-upload-cloud" aria-hidden="true"></i> <?php esc_html_e( 'Import Ninja Forms', Forminator::DOMAIN ); ?>
							</button>

						</div>

					<?php endif; ?>

					<?php if ( forminator_is_import_plugin_enabled( 'gravityforms' ) ) : ?>

						<div class="sui-form-field">

							<h3 class="sui-settings-label"><?php esc_html_e( 'Gravity Forms', Forminator::DOMAIN ); ?></h3>

							<p class="sui-description" style="margin-bottom: 10px;"><?php esc_html_e( 'Import your forms from Gravity Forms', Forminator::DOMAIN ); ?></p>

							<button
								role="button"
								class="sui-button wpmudev-open-modal"
								data-modal="import_cform_gravity"
								data-modal-title=""
								data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminator_popup_import_cform_gravityforms' ) ); ?>"
							>
								<i class="sui-icon-upload-cloud" aria-hidden="true"></i> <?php esc_html_e( 'Import Gravity Forms', Forminator::DOMAIN ); ?>
							</button>

						</div>

					<?php endif; ?>

				</div>

			</div>

		</div>

	</form>

</div>
