<section class="wpmudev-dashboard-section">

	<?php $this->template( 'dashboard/widgets/widget-resume' ); ?>

	<div class="fui-row fui-row-dynamic">

		<?php $this->template( 'dashboard/widgets/widget-cform' ); ?>

		<?php $this->template( 'dashboard/widgets/widget-poll' ); ?>

		<?php $this->template( 'dashboard/widgets/widget-quiz' ); ?>

		<?php if ( isset( $_GET['show_stripe_dialog'] ) && ! empty( $_GET['show_stripe_dialog'] ) ) { // phpcs:ignore ?>

			<?php $this->template( 'dashboard/stripe-notice' ); ?>

		<?php } ?>

	</div>

</section>
