<?php
$count = Forminator_Form_Entry_Model::count_all_entries();
?>

<?php if ( $count > 0 ) { ?>

	<?php $markup = $this->render_entries(); ?>

	<form method="get"
		name="bulk-action-form"
		class="sui-box">

		<div class="fui-entries-bar">

			<div class="fui-bar-selectors">

				<input type="hidden" name="page" value="forminator-entries" />

				<select name="form_type"
					onchange="submit()"
					class="sui-select-sm">

					<?php foreach ( $this->get_form_types() as $post_type => $name ) { ?>
						<option value="<?php echo esc_attr( $post_type ); ?>" <?php echo selected( $post_type, $this->get_current_form_type() ); ?>><?php echo esc_html( $name ); ?></option>
					<?php } ?>

				</select>

				<?php echo $this->render_form_switcher(); // phpcs:ignore ?>

			</div>

			<button class="sui-button sui-button-blue" onclick="submit()"><?php esc_html_e( 'Show Submissions', Forminator::DOMAIN ); ?></button>

			<?php if ( $markup ) : ?>
				<a href="/" class="sui-button sui-button-ghost wpmudev-open-modal" data-modal="exports-schedule"><i class="sui-icon-paperclip" aria-hidden="true"></i> <?php esc_html_e( 'Export', Forminator::DOMAIN ); ?></a>
			<?php endif; ?>

		</div>

	</form>

	<?php if( $markup ) : ?>

		<?php echo $markup; // phpcs:ignore ?>

	<?php else: ?>

		<div class="sui-box sui-message">

			<?php if ( forminator_is_show_branding() ): ?>
				<img src="<?php echo esc_url( forminator_plugin_url() . 'assets/img/forminator-disabled.png' ); ?>"
				     srcset="<?php echo esc_url( forminator_plugin_url() . 'assets/img/forminator-disabled.png' ); ?> 1x, <?php echo esc_url( forminator_plugin_url() . 'assets/img/forminator-disabled@2x.png' ); ?> 2x"
				     alt="<?php esc_html_e( 'Forminator', Forminator::DOMAIN ); ?>"
				     class="sui-image"/>
			<?php endif; ?>

			<div class="sui-message-content">

				<h2><?php esc_html_e( 'Almost there!', Forminator::DOMAIN ); ?></h2>

				<p><?php esc_html_e( 'Select the form, poll or quiz module to view the corresponding submissions.', Forminator::DOMAIN ); ?></p>

			</div>

		</div>

	<?php endif; ?>

<?php } else { ?>

	<div class="sui-box sui-message">

		<?php if ( forminator_is_show_branding() ): ?>
			<img src="<?php echo esc_url( forminator_plugin_url() . 'assets/img/forminator-submissions.png' ); ?>"
			     srcset="<?php echo esc_url( forminator_plugin_url() . 'assets/img/forminator-submissions.png' ); ?> 1x, <?php echo esc_url( forminator_plugin_url() . 'assets/img/forminator-submissions@2x.png' ); ?> 2x"
			     alt="<?php esc_html_e( 'Forminator', Forminator::DOMAIN ); ?>"
			     class="sui-image"/>
		<?php endif; ?>

		<div class="sui-message-content">

			<h2><?php esc_html_e( 'Submissions', Forminator::DOMAIN ); ?></h2>

			<p><?php esc_html_e( 'You haven’t received any form, poll or quiz submissions yet. When you do, you’ll be able to view all the data here.', Forminator::DOMAIN ); ?></p>

		</div>

	</div>

<?php } ?>
