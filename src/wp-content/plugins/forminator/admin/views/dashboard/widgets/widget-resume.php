<?php
$total_modules           = forminator_total_forms();
$count_active            = forminator_total_forms( 'publish' );
$forms_total_submissions = Forminator_Form_Entry_Model::count_all_entries_by_type( 'custom-forms' ); // phpcs:ignore -- forminator_cforms_total();
$poll_total_submissions  = Forminator_Form_Entry_Model::count_all_entries_by_type( 'poll' );// phpcs:ignore -- forminator_polls_total();
$quiz_total_submissions  = Forminator_Form_Entry_Model::count_all_entries_by_type( 'quizzes' );// phpcs:ignore -- forminator_quizzes_total();
$last_submission         = forminator_get_latest_entry_time( 'custom-forms' );
?>

<div class="sui-box sui-summary <?php echo esc_attr( $this->get_box_summary_classes() ); ?>">

	<div class="sui-summary-image-space" aria-hidden="true" style="<?php echo esc_attr( $this->get_box_summary_image_style() ); ?>"></div>

	<div class="sui-summary-segment">

		<div class="sui-summary-details">

			<?php if ( 0 < $total_modules ) { ?>
				<span class="sui-summary-large"><?php echo esc_html( $count_active ); ?></span>
			<?php } else { ?>
				<span class="sui-summary-large">0</span>
			<?php } ?>

			<?php if ( 1 === $total_modules ) { ?>
				<span class="sui-summary-sub"><?php esc_html_e( 'Active Module', Forminator::DOMAIN ); ?></span>
			<?php } else { ?>
				<span class="sui-summary-sub"><?php esc_html_e( 'Active Modules', Forminator::DOMAIN ); ?></span>
			<?php } ?>

			<?php if ( $total_modules > 0 ) { ?>
				<span class="sui-summary-detail"><strong><?php echo esc_html( $last_submission ); ?></strong></span>
			<?php } else { ?>
				<span class="sui-summary-detail"><strong><?php esc_html_e( 'Never', Forminator::DOMAIN ); ?></strong></span>
			<?php } ?>

			<span class="sui-summary-sub"><?php esc_html_e( 'Last Submission', Forminator::DOMAIN ); ?></span>

		</div>

	</div>

	<div class="sui-summary-segment">

		<ul class="sui-list">

			<li>
				<span class="sui-list-label"><?php esc_html_e( 'Form Submissions', Forminator::DOMAIN ); ?></span>
				<?php if ( $forms_total_submissions > 0 ) { ?>
					<span class="sui-list-detail"><?php echo esc_html( $forms_total_submissions ); ?></span>
				<?php } else { ?>
					<span class="sui-list-detail">0</span>
				<?php } ?>
			</li>

			<li>
				<span class="sui-list-label"><?php esc_html_e( 'Poll Submissions', Forminator::DOMAIN ); ?></span>
				<?php if ( $poll_total_submissions > 0 ) { ?>
					<span class="sui-list-detail"><?php echo esc_html( $poll_total_submissions ); ?></span>
				<?php } else { ?>
					<span class="sui-list-detail">0</span>
				<?php } ?>
			</li>

			<li>
				<span class="sui-list-label"><?php esc_html_e( 'Quiz Submissions', Forminator::DOMAIN ); ?></span>
				<?php if ( $quiz_total_submissions > 0 ) { ?>
					<span class="sui-list-detail"><?php echo esc_html( $quiz_total_submissions ); ?></span>
				<?php } else { ?>
					<span class="sui-list-detail">0</span>
				<?php } ?>
			</li>

		</ul>

	</div>

</div>
