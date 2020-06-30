<div id="forminator-content-box-<?php echo esc_attr( $template_class ); ?>" class="wpmudev-box">
	<?php if ( is_callable( $header_callback ) ): ?>
		<div class="wpmudev-box-header">
			<?php call_user_func( $header_callback ); ?>
		</div><!-- end content box header -->
	<?php elseif ( $this->template_exists( $template_id . '/content-header' ) ): ?>
		<div class="wpmudev-box-header">
			<?php $this->template( $template_id . '/content-header', array( 'title' => $title ) ); ?>
		</div><!-- end content box header -->
	<?php elseif ( $this->template_exists( $template_id . '-content-header' ) ): ?>
		<div class="wpmudev-box-header">
			<?php $this->template( $template_id . '-content-header', array( 'title' => $title ) ); ?>
		</div><!-- end content box header -->
	<?php elseif ( $title ) : ?>
		<div class="wpmudev-box-header">
			<h2 class="wpmudev-box-title--alt"><?php echo esc_html( $title ); ?></h2>
		</div><!-- end content box header -->
	<?php endif; ?>

	<div class="wpmudev-box-section">
		<?php if ( is_callable( $main_callback ) ): ?>
			<?php call_user_func( $main_callback ); ?>
		<?php elseif ( $this->template_exists( $template_id . '/content' ) ): ?>
			<?php $this->template( $template_id . '/content' ); ?>
		<?php else: ?>
			<?php $this->template( $template_id . '-content' ); ?>
		<?php endif; ?>
	</div><!-- end content box content -->

	<?php if ( is_callable( $footer_callback ) ): ?>
		<div class="wpmudev-box-footer">
			<?php call_user_func( $footer_callback ); ?>
		</div><!-- end content box footer -->
	<?php elseif ( $this->template_exists( $template_id . '/content-footer' ) ): ?>
		<div class="wpmudev-box-footer">
			<?php $this->template( $template_id . '/content-footer' ); ?>
		</div><!-- end content box footer -->
	<?php elseif ( $this->template_exists( $template_id . '-content-footer' ) ): ?>
		<div class="wpmudev-box-footer">
			<?php $this->template( $template_id . '-content-footer' ); ?>
		</div><!-- end content box footer -->
	<?php endif; ?>
</div><!-- end content box-<?php echo esc_attr( $template_class ); ?> -->
