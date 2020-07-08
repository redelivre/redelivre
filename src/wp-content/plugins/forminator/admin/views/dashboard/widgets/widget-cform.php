<?php
$dashboard_settings = forminator_get_dashboard_settings( 'forms', array() );
$num_recent         = isset( $dashboard_settings['num_recent'] ) ? $dashboard_settings['num_recent'] : 5;
$published          = isset( $dashboard_settings['published'] ) ? filter_var( $dashboard_settings['published'], FILTER_VALIDATE_BOOLEAN ) : true;
$draft              = isset( $dashboard_settings['draft'] ) ? filter_var( $dashboard_settings['draft'], FILTER_VALIDATE_BOOLEAN ) : true;
$statuses           = array();

if ( $published ) {
	$statuses[] = Forminator_Base_Form_Model::STATUS_PUBLISH;
}

if ( $draft ) {
	$statuses[] = Forminator_Base_Form_Model::STATUS_DRAFT;
}

if ( 0 === $num_recent ) {
	return;
};
?>

<div class="fui-col-6">

	<div class="sui-box">

		<div class="sui-box-header">

			<h3 class="sui-box-title"><i class="sui-icon-clipboard-notes" aria-hidden="true"></i><?php esc_html_e( 'Forms', Forminator::DOMAIN ); ?></h3>

		</div>

		<div class="sui-box-body">

			<p><?php esc_html_e( 'Create any type of form from one of our pre-made templates, or build your own from scratch.', Forminator::DOMAIN ); ?></p>

			<?php if ( 0 === forminator_cforms_total() ) { ?>

				<p><button href="/" class="sui-button sui-button-blue wpmudev-open-modal" data-modal="custom_forms"><i class="sui-icon-plus" aria-hidden="true"></i> <?php esc_html_e( 'Create', Forminator::DOMAIN ); ?></button></p>

			<?php } ?>

		</div>

		<?php if ( 0 < forminator_cforms_total() ) { ?>

			<table class="sui-table sui-table-flushed">

				<thead>

					<tr>

						<th><?php esc_html_e( 'Name', Forminator::DOMAIN ); ?></th>

						<th class="fui-col-status"><?php esc_html_e( 'Status', Forminator::DOMAIN ); ?></th>

					</tr>

				</thead>

				<tbody>

					<?php foreach ( forminator_cform_modules( $num_recent, $statuses ) as $module ) { ?>

						<tr>

							<td class="sui-table-item-title"><?php echo forminator_get_form_name( $module['id'], 'custom_form' ); // phpcs:ignore ?></td>

							<td class="fui-col-status">

								<?php
								if ( 'publish' === $module['status'] ) {
									$status_class = 'published';
									$status_text  = esc_html__( 'Published', Forminator::DOMAIN );
								} else {
									$status_class = 'draft';
									$status_text  = esc_html__( 'Draft', Forminator::DOMAIN );
								}
								?>

								<span
									class="sui-status-dot sui-<?php echo esc_html( $status_class ); ?> sui-tooltip"
									data-tooltip="<?php echo esc_html( $status_text ); ?>"
								>
									<span aria-hidden="true"></span>
								</span>

								<a href="<?php echo admin_url( 'admin.php?page=forminator-cform&view-stats=' . esc_attr( $module['id'] ) ); // phpcs:ignore ?>"
									class="sui-button-icon sui-tooltip sui-tooltip-top-right-mobile"
									data-tooltip="<?php esc_html_e( 'View Stats', Forminator::DOMAIN ); ?>">
									<i class="sui-icon-graph-line" aria-hidden="true"></i>
								</a>

								<div class="sui-dropdown">

									<button class="sui-button-icon sui-dropdown-anchor"
										aria-expanded="false"
										aria-label="<?php esc_html_e( 'More options', Forminator::DOMAIN ); ?>">
										<i class="sui-icon-widget-settings-config" aria-hidden="true"></i>
									</button>

									<ul>
										<li>
											<a href="<?php echo admin_url( 'admin.php?page=forminator-cform-wizard&id=' . $module['id'] ); // phpcs:ignore ?>">
												<i class="sui-icon-pencil" aria-hidden="true"></i> <?php esc_html_e( 'Edit', Forminator::DOMAIN ); ?>
											</a>
										</li>
										<li><button class="wpmudev-open-modal"
											data-modal="preview_cforms"
											data-modal-title="<?php echo sprintf( '%s - %s', esc_html__( 'Preview Custom Form', Forminator::DOMAIN ), forminator_get_form_name( $module['id'], 'custom_form' ) ); // phpcs:ignore ?>"
											data-form-id="<?php echo esc_attr( $module['id'] ); ?>"
											data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminator_popup_preview_cforms' ) ); ?>">
											<i class="sui-icon-eye" aria-hidden="true"></i> <?php esc_html_e( 'Preview', Forminator::DOMAIN ); ?>
										</button></li>

										<li>
											<button class="copy-clipboard" data-shortcode='[forminator_form id="<?php echo esc_attr( $module['id'] ); ?>"]'><i class="sui-icon-code" aria-hidden="true"></i> <?php esc_html_e( 'Copy Shortcode', Forminator::DOMAIN ); ?></button>
										</li>

										<li><a href="<?php echo admin_url( 'admin.php?page=forminator-entries&form_type=forminator_forms&form_id=' . $module['id'] ); // phpcs:ignore ?>"><i class="sui-icon-community-people" aria-hidden="true"></i> <?php esc_html_e( 'View Submissions', Forminator::DOMAIN ); ?></a></li>

										<li><form method="post">
											<input type="hidden" name="forminator_action" value="clone">
											<input type="hidden" name="id" value="<?php echo esc_attr( $module['id'] ); ?>"/>
											<?php wp_nonce_field( 'forminatorCustomFormRequest', 'forminatorNonce' ); ?>
											<button type="submit">
												<i class="sui-icon-page-multiple" aria-hidden="true"></i> <?php esc_html_e( 'Duplicate', Forminator::DOMAIN ); ?>
											</button>
										</form></li>

										<?php if ( Forminator::is_import_export_feature_enabled() ) : ?>

											<li><a href="#"
												class="wpmudev-open-modal"
												data-modal="export_cform"
												data-modal-title=""
												data-form-id="<?php echo esc_attr( $module['id'] ); ?>"
												data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminator_popup_export_cform' ) ); ?>">
												<i class="sui-icon-cloud-migration" aria-hidden="true"></i> <?php esc_html_e( 'Export', Forminator::DOMAIN ); ?>
											</a></li>

										<?php endif; ?>

										<li>
											<button class="wpmudev-open-modal"
													data-modal="delete-module"
													data-modal-title="<?php esc_attr_e( 'Delete Form', Forminator::DOMAIN ); ?>"
													data-modal-content="<?php esc_attr_e( 'Are you sure you wish to permanently delete this form?', Forminator::DOMAIN ); ?>"
													data-form-id="<?php echo esc_attr( $module['id'] ); ?>"
													data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminatorCustomFormRequest' ) ); ?>">
												<i class="sui-icon-trash" aria-hidden="true"></i> <?php esc_html_e( 'Delete', Forminator::DOMAIN ); ?>
											</button>
										</li>

									</ul>

								</div>

							</td>

						</tr>

					<?php } ?>

				</tbody>

			</table>

			<div class="sui-box-footer">

				<button class="sui-button sui-button-blue wpmudev-open-modal"
					data-modal="custom_forms">
					<i class="sui-icon-plus" aria-hidden="true"></i> <?php esc_html_e( 'Create', Forminator::DOMAIN ); ?>
				</button>

				<div class="sui-actions-right">
					<p class="sui-description"><a href="<?php echo admin_url( 'admin.php?page=forminator-cform' ); // phpcs:ignore ?>" class="sui-link-gray"><?php esc_html_e( 'View all forms', Forminator::DOMAIN ); ?></a></p>
				</div>

			</div>

		<?php } ?>

	</div>

</div>
