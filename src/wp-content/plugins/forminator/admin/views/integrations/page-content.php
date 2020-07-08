<p>
	<?php
		esc_html_e( 'Forminator integrates with your favorite third party apps. You can connect to the available apps via their API here and activate them to collect data in the Integrations tab of your forms, polls or quizzes.', Forminator::DOMAIN );
	?>
</p>

<div class="fui-integrations-block">

	<span class="sui-table-title"><?php esc_html_e( 'Connected Apps', Forminator::DOMAIN ); ?></span>

	<?php
	if ( ! empty( $addons['connected'] ) ) {
		?>

		<table class="sui-table fui-table--apps">

			<tbody>

				<?php foreach ( $addons['connected'] as $key => $provider ) : ?>

					<?php echo forminator_addon_row_html_markup( $provider, 0, true, true );// phpcs:ignore ?>

				<?php endforeach; ?>

			</tbody>

		</table>

		<span class="sui-description"><?php esc_html_e( 'To activate any of these to collect data, go to the Integrations tab of your forms, polls or quizzes.', Forminator::DOMAIN ); ?></span>

	<?php } else { ?>

		<div class="sui-notice sui-notice-info">
			<p><?php esc_html_e( 'You are not connected to any third party apps. You can connect to the available apps listed below and activate them in your modules to collect data.', Forminator::DOMAIN ); ?></p>
		</div>

	<?php } ?>

</div>

<div class="fui-integrations-block">

	<span class="sui-table-title"><?php esc_html_e( 'Available Apps', Forminator::DOMAIN ); ?></span>

	<?php
	if ( ! empty( $addons['not_connected'] ) ) {
		?>

		<table class="sui-table fui-table--apps">

			<tbody>

				<?php foreach ( $addons['not_connected'] as $key => $provider ) : ?>

					<?php echo forminator_addon_row_html_markup( $provider, 0, true );// phpcs:ignore ?>

				<?php endforeach; ?>

			</tbody>

		</table>

		<?php
	}
	?>

</div>
