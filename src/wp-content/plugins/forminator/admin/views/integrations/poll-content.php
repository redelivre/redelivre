<?php
$integrations_page = admin_url( 'admin.php?page=forminator-integrations' );

if ( empty( $poll_id ) ) {
	$poll_id = 0;
}

if ( empty( $addons['poll_connected'] ) && empty( $addons['not_poll_connected'] ) ) { ?>

	<div class="sui-notice sui-notice-info">
		<p><?php printf( /* translators: ... */ esc_html__( 'You are not connected to any third party apps. You can connect to the available apps via their API on the %1$sIntegrations%2$s page and come back to activate them for collecting data of this poll.', Forminator::DOMAIN ), '<a href="' . esc_url( $integrations_page ) . '">', '</a>' ); ?></p>
	</div>

<?php } else { ?>

	<div class="fui-integrations-block">

		<span class="sui-table-title"><?php esc_html_e( 'Active Apps', Forminator::DOMAIN ); ?></span>

		<?php if ( empty( $addons['poll_connected'] ) ) { ?>

			<div class="sui-notice sui-notice-info">
				<p><?php esc_html_e( "You are not sending this poll's data to any third party apps. You can activate any of the connected apps below and start sending this poll's data to them.", Forminator::DOMAIN ); ?></p>
			</div>

		<?php } else { ?>

			<table class="sui-table fui-table--apps fui-connected">

				<tbody>

				<?php foreach ( $addons['poll_connected'] as $key => $provider ) : ?>

					<?php echo forminator_addon_poll_row_html_markup( $provider, $poll_id, true, true ); // phpcs:ignore ?>

				<?php endforeach; ?>

				</tbody>

			</table>

			<span class="sui-description"><?php esc_html_e( 'These apps are collecting data of your poll.', Forminator::DOMAIN ); ?></span>

		<?php } ?>

	</div>

	<div class="fui-integrations-block">

		<span class="sui-table-title"><?php esc_html_e( 'Connected Apps', Forminator::DOMAIN ); ?></span>

		<?php if ( empty( $addons['not_poll_connected'] ) ) { ?>

			<div class="sui-notice">
				<p><?php printf( /* translators: ... */ esc_html__( 'Connect to more third party apps on the %1$sIntegrations%2$s page and activate them to collect the data of this poll here.', Forminator::DOMAIN ), '<a href="' . esc_url( $integrations_page ) . '">', '</a>' ); ?></p>
			</div>

		<?php } else { ?>

			<table class="sui-table fui-table--apps">

				<tbody>

				<?php foreach ( $addons['not_poll_connected'] as $key => $provider ) : ?>

					<?php
					if ( ! $provider['is_poll_settings_available'] ) {
						continue;
					}
					?>

					<?php echo forminator_addon_poll_row_html_markup( $provider, $poll_id, true, true ); // phpcs:ignore ?>

				<?php endforeach; ?>

				</tbody>

			</table>

			<span class="sui-description"><?php printf( /* translators: ... */ esc_html__( 'You are connected to these apps via their API. Connect to more apps on the %1$sIntegrations%2$s page.', Forminator::DOMAIN ), '<a href="' . esc_url( $integrations_page ) . '">', '</a>' ); ?></span>

		<?php } ?>

	</div>

	<?php
}
