<?php $icon_close = forminator_plugin_dir() . 'assets/icons/admin-icons/close.php'; ?>

<div id="forminator-modal-<?php echo esc_attr( $template_class ); ?>" class="wpmudev-modal <?php echo esc_attr( $template_class ); ?>">

	<div class="wpmudev-modal-mask" aria-hidden="true"></div>

	<div class="wpmudev-box-modal">

		<div class="wpmudev-box-header">

			<div class="wpmudev-header--text">

				<h2 class="wpmudev-subtitle"><?php echo esc_html( $title ); ?></h2>

			</div>

			<div class="wpmudev-header--action">

				<button class="wpmudev-box--action"><?php require $icon_close; ?></button>

				<button class="wpmudev-sr-only"><?php esc_html_e( 'Close modal', Forminator::DOMAIN ); ?></button>

			</div>

		</div>

		<div class="wpmudev-box-body">
			<?php if ( is_callable( $main_callback ) ) : ?>
				<?php call_user_func( $main_callback ); ?>
			<?php elseif ( $this->template_exists( $template_id . '/content' ) ) : ?>
				<?php $this->template( $template_id . '/content' ); ?>
			<?php else : ?>
				<?php $this->template( $template_id . '-content' ); ?>
			<?php endif; ?>
		</div>

	</div>

</div>
