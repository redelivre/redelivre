<?php
$section              = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : 'dashboard';
$nonce                = wp_create_nonce( 'forminator_save_popup_uninstall_settings' );
$forminator_uninstall = get_option( 'forminator_uninstall_clear_data', false );

?>

<div class="sui-box" data-nav="data" style="<?php echo esc_attr( 'data' !== $section ? 'display: none;' : '' ); ?>">

	<div class="sui-box-header">
		<h2 class="sui-box-title"><?php esc_html_e( 'Data', Forminator::DOMAIN ); ?></h2>
	</div>

	<form class="forminator-settings-save" action="">

		<div class="sui-box-body">
			<div class="sui-box-settings-row">

				<div class="sui-box-settings-col-1">
					<span class="sui-settings-label"><?php esc_html_e( 'Uninstallation', Forminator::DOMAIN ); ?></span>
					<span class="sui-description"><?php esc_html_e( 'When you uninstall this plugin, what do you want to do with your plugin\'s and data?', Forminator::DOMAIN ); ?></span>
				</div>

				<div class="sui-box-settings-col-2">
					<div class="sui-side-tabs">

						<div class="sui-tabs-menu">

							<label for="delete_uninstall-false" class="sui-tab-item<?php echo $forminator_uninstall ? '' : ' active'; ?>">
								<input type="radio"
									name="delete_uninstall"
									value="false"
									id="delete_uninstall-false"
									<?php echo esc_attr( checked( $forminator_uninstall, false ) ); ?> />
								<?php esc_html_e( 'Preserve', Forminator::DOMAIN ); ?>
							</label>

							<label for="delete_uninstall-true" class="sui-tab-item<?php echo $forminator_uninstall ? ' active' : ''; ?>">
								<input type="radio"
									name="delete_uninstall"
									value="true"
									id="delete_uninstall-true"
									<?php echo esc_attr( checked( $forminator_uninstall, true ) ); ?> />
								<?php esc_html_e( 'Reset', Forminator::DOMAIN ); ?>
							</label>

						</div>

					</div>
				</div>

			</div>

			<div class="sui-box-settings-row">

				<div class="sui-box-settings-col-1">
					<span class="sui-settings-label"><?php esc_html_e( 'Reset Plugin', Forminator::DOMAIN ); ?></span>
					<span class="sui-description"><?php esc_html_e( 'Needing to start fresh? Use this setting to roll back to the default plugin state.', Forminator::DOMAIN ); ?></span>
				</div>

				<div class="sui-box-settings-col-2">
					<button
							class="sui-button sui-button-ghost wpmudev-open-modal"
							data-modal="reset-plugin-settings"
							data-modal-title="<?php esc_attr_e( 'Reset Plugin', Forminator::DOMAIN ); ?>"
							data-modal-content="<?php esc_attr_e( 'Are you sure you want to reset the plugin to its default state?', Forminator::DOMAIN ); ?>"
							data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminatorSettingsRequest' ) ); ?>"
					>

						<span class="sui-loading-text">
							<i class="sui-icon-refresh"></i> <?php esc_html_e( 'RESET', Forminator::DOMAIN ); ?>
						</span>
						<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>

					</button>
					<span class="sui-description">
						<?php
						esc_html_e(
							'Note: This will delete all the form/polls/quizzes you currently have and revert all settings to their default state.',
							Forminator::DOMAIN
						);
						?>
					</span>
				</div>

			</div>

		</div>

		<div class="sui-box-footer">

			<div class="sui-actions-right">

				<button class="sui-button sui-button-blue wpmudev-action-done" data-title="<?php esc_attr_e( 'Data settings', Forminator::DOMAIN ); ?>" data-action="uninstall_settings"
						data-nonce="<?php echo esc_attr( $nonce ); ?>">
					<span class="sui-loading-text"><?php esc_html_e( 'Save Settings', Forminator::DOMAIN ); ?></span>
					<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
				</button>

			</div>

		</div>

	</form>

</div>
