<?php
$count = Forminator_Form_Entry_Model::count_entries( $this->form_id );

$poll_question    = $this->get_poll_question();
$poll_description = $this->get_poll_description();

$custom_votes = $this->map_custom_votes();
?>

<?php if ( $this->error_message() ) : ?>
	<span class="sui-notice sui-notice-error"><p><?php echo esc_html( $this->error_message() ); ?></p></span>
<?php endif; ?>

<?php if ( $count > 0 ) : ?>

	<div class="sui-box sui-poll-submission">

		<div class="sui-box-body">

			<?php $this->template( 'poll/entries/prompt' ); ?>

			<div class="sui-block-content-center">
				<?php if ( ! empty( $poll_question ) ) { ?>

					<h2><?php echo $poll_question; // WPCS: XSS ok. ?></h2>

				<?php } ?>

				<?php if ( ! empty( $poll_description ) ) { ?>

					<p><?php echo $poll_description; // WPCS: XSS ok. ?></p>

				<?php } ?>
			</div>

		</div>

		<div class="sui-box-body">

			<canvas id="forminator-chart-poll" role="img" style="max-width: 800px; margin: 0 auto;"></canvas>

			<?php if ( ! empty( $custom_votes ) && count( $custom_votes ) > 0 ) { ?>

				<?php
				foreach ( $custom_votes as $element_id => $custom_vote ) {

					echo '<div style="margin-top: 30px;">';

						echo '<label class="sui-label">' . $this->get_field_title( $element_id ) . '</label>'; // phpcs:ignore

						echo '<div style="margin-top: 10px;">';

					foreach ( $custom_vote as $answer => $vote ) {
						echo '<span class="sui-tag">' . /* translators: ... */ esc_html( sprintf( _n( '%1$s (%2$s) vote', '%1$s (%2$s) votes', $vote, Forminator::DOMAIN ), $answer, $vote ) ) . '</span>';
					}

						echo '</div>';

					echo '</div>';

				}
				?>

			<?php } ?>

		</div>

		<div class="sui-box-footer">

			<div class="sui-actions-right">

				<button
						type="button"
						class="sui-button sui-button-ghost wpmudev-open-modal"
						data-modal="delete-poll-submission"
						data-modal-title="<?php esc_attr_e( 'Delete Submissions', Forminator::DOMAIN ); ?>"
						data-modal-content="<?php esc_attr_e( 'Are you sure you wish to delete the submissions on this poll?', Forminator::DOMAIN ); ?>"
						data-form-id="<?php echo esc_attr( $this->form_id ); ?>"
						data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminatorPollEntries' ) ); ?>"
				>
					<i class="sui-icon-trash" aria-hidden="true"></i> <?php esc_html_e( 'Delete Submissions', Forminator::DOMAIN ); ?>
				</button>

			</div>

		</div>

	</div>

<?php else : ?>

	<div class="sui-box sui-message">
		<?php
		$form_id = $this->form_id;
			include_once forminator_plugin_dir() . 'admin/views/poll/entries/content-none.php';
		?>
	</div>

	<?php
endif;
