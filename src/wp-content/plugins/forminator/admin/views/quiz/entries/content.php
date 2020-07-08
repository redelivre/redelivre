<?php
/** @var Forminator_Quizz_Renderer_Entries $this */
$plugin_path      = forminator_plugin_url();
$entries          = $this->get_table();
$form_type        = $this->get_form_type();
$count            = $this->get_total_entries();
$entries_per_page = $this->get_per_page();
$total_page       = ceil( $count / $entries_per_page );
?>
<?php if ( $this->error_message() ) : ?>
	<span class="sui-notice sui-notice-error"><p><?php echo esc_html( $this->error_message() ); ?></p></span>
<?php endif; ?>

<?php if ( $count > 0 ) : ?>

	<form method="post" class="sui-box">

		<?php wp_nonce_field( 'forminator_quiz_bulk_action', 'forminatorEntryNonce' ); ?>

		<div class="sui-box-body">

			<?php $this->template( 'quiz/entries/prompt' ); ?>

			<input type="hidden" name="form_id" value="<?php echo esc_attr( $this->form_id ); ?>"/>

			<div class="sui-box-search">

				<div class="sui-search-left">

					<?php $this->bulk_actions(); ?>

				</div>

				<div class="sui-search-right">

					<div class="sui-pagination-wrap">

						<span class="sui-pagination-results">
							<?php
							if ( 1 === $count ) {
								/* translators: ... */
								printf( esc_html__( '%s result', Forminator::DOMAIN ), esc_html( $count ) );
							} else {
								/* translators: ... */
								printf( esc_html__( '%s results', Forminator::DOMAIN ), esc_html( $count ) );
							}
							?>
						</span>

						<?php $this->paginate(); ?>

					</div>

				</div>

			</div>

		</div>

		<table class="sui-table sui-table-flushed sui-accordion">

			<thead>
			<tr>
				<th>
					<label class="sui-checkbox">
						<input id="wpf-cform-check_all" type="checkbox">
						<span></span>
						<div class="sui-description"><?php esc_html_e( 'ID', Forminator::DOMAIN ); ?></div>
					</label>
				</th>
				<th colspan="5"><?php esc_html_e( 'Date Submitted', Forminator::DOMAIN ); ?></th>
			</tr>

			</thead>

			<tbody>

			<?php
			$first_item  = $count;
			$page_number = $this->get_paged();

			if ( $page_number > 1 ) {
				$first_item = $count - ( ( $page_number - 1 ) * $entries_per_page );
			}
			?>

			<?php foreach ( $entries as $entry ) : ?>
				<tr class="sui-accordion-item">

					<td>
						<label class="sui-checkbox">
							<input name="ids[]" value="<?php echo esc_attr( $entry->entry_id ); ?>" type="checkbox" id="quiz-answer-<?php echo esc_attr( $entry->entry_id ); ?>">
							<span></span>
							<div class="sui-description"><?php echo esc_attr( $first_item ); ?></div>
						</label>
					</td>

					<td colspan="5">
						<?php echo date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $entry->date_created_sql ) ); // phpcs:ignore ?>
						<span class="sui-accordion-open-indicator">
							<i class="sui-icon-chevron-down"></i>
						</span>
					</td>

				</tr>

				<tr class="sui-accordion-item-content">

					<td colspan="6">

						<div class="sui-box" style="margin-bottom: 30px;">

							<div class="sui-box-body">

								<h2>
									<?php echo forminator_get_form_name( $this->form_id, 'quiz' ); // phpcs:ignore ?>
								</h2>

								<?php if ( 'knowledge' === $form_type ) { ?>

									<?php
									$meta  = $entry->meta_data['entry']['value'];
									$total = 0;
									$right = 0;
									?>

									<table class="sui-table">

										<thead>

										<tr>

											<th><?php esc_html_e( 'Question', Forminator::DOMAIN ); ?></th>

											<th><?php esc_html_e( 'Answer', Forminator::DOMAIN ); ?></th>

										</tr>

										</thead>

										<tbody>

										<?php foreach ( $meta as $answer ) : ?>
											<?php
											$total ++;

											if ( $answer['isCorrect'] ) {
												$right ++;
											}

											$user_answer = $answer['answer'];
											?>

											<tr>

												<td><?php echo esc_html( $answer['question'] ); ?></td>

												<td>
													<?php
													if ( $answer['isCorrect'] ) {

														echo '<span class="sui-tag sui-tag-success">' . esc_html( $user_answer ) . '</span>';

													} else {

														echo '<span class="sui-tag sui-tag-error">' . esc_html( $user_answer ) . '</span>';

													}
													?>
												</td>

											</tr>

										<?php endforeach; ?>

										<?php $integrations_data = $this->get_integrations_data_from_entry( $entry ); ?>
										<?php if ( ! empty( $integrations_data ) ) : ?>
											<?php foreach ( $integrations_data as $integrations_datum ) : ?>
												<tr>
													<td><?php echo $integrations_datum['label']; // phpcs:ignore -- html output intended ?></td>
													<td>
														<?php
														$sub_entries = isset( $integrations_datum['sub_entries'] ) ? $integrations_datum['sub_entries'] : array();
														?>
														<?php if ( ! empty( $sub_entries ) && is_array( $sub_entries ) ) : ?>
															<?php foreach ( $sub_entries as $sub_entry ) : ?>
																<div class="">
																	<span class="sui-settings-label"><?php echo esc_html( $sub_entry['label'] ); ?></span>
																	<span class="sui-description"><?php echo( $sub_entry['value'] ); // phpcs:ignore -- html output intended ?></span>
																</div>
															<?php endforeach; ?>
														<?php else : ?>
															<?php echo( $integrations_datum['value'] ); // phpcs:ignore -- html output intended ?>
														<?php endif; ?>
													</td>
												</tr>
											<?php endforeach; ?>
										<?php endif; ?>

										</tbody>

									</table>

									<div class="sui-box-footer">

										<p><?php echo sprintf( __( "You got <strong>%s / %s</strong> correct answers.", Forminator::DOMAIN ), $right, $total ); // phpcs:ignore ?></p>
									</div>

									<?php
								} else {

									$meta = $entry->meta_data['entry']['value'][0]['value'];
									?>

									<?php if ( isset( $meta['answers'] ) && is_array( $meta['answers'] ) ) : ?>

										<table class="sui-table">

											<thead>

											<tr>

												<th><?php esc_html_e( 'Question', Forminator::DOMAIN ); ?></th>

												<th><?php esc_html_e( 'Answer', Forminator::DOMAIN ); ?></th>

											</tr>

											</thead>

											<tbody>

											<?php foreach ( $meta['answers'] as $answer ) : ?>

												<tr>

													<td><?php echo esc_html( $answer['question'] ); ?></td>

													<td><?php echo esc_html( $answer['answer'] ); ?></td>

												</tr>

											<?php endforeach; ?>

											<?php $integrations_data = $this->get_integrations_data_from_entry( $entry ); ?>
											<?php if ( ! empty( $integrations_data ) ) : ?>
												<?php foreach ( $integrations_data as $integrations_datum ) : ?>
													<tr>
														<td><?php echo $integrations_datum['label']; // phpcs:ignore -- html output intended ?></td>
														<td>
															<?php
															$sub_entries = isset( $integrations_datum['sub_entries'] ) ? $integrations_datum['sub_entries'] : array();
															?>
															<?php if ( ! empty( $sub_entries ) && is_array( $sub_entries ) ) : ?>
																<?php foreach ( $sub_entries as $sub_entry ) : ?>
																	<div class="">
																		<span class="sui-settings-label"><?php echo esc_html( $sub_entry['label'] ); ?></span>
																		<span class="sui-description"><?php echo( $sub_entry['value'] ); // phpcs:ignore -- html output intended ?></span>
																	</div>
																<?php endforeach; ?>
															<?php else : ?>
																<?php echo( $integrations_datum['value'] ); // phpcs:ignore -- html output intended ?>
															<?php endif; ?>
														</td>
													</tr>
												<?php endforeach; ?>
											<?php endif; ?>


											</tbody>

										</table>

									<?php endif; ?>

									<div class="sui-box-footer">

										<p><?php printf( __( '<strong>Quiz Result:</strong> %s', Forminator::DOMAIN ), $meta['result']['title'] ); // phpcs:ignore ?></p>

									</div>

								<?php } ?>

							</div>

						</div>

					</td>

				</tr>

				<?php
				$first_item --;

			endforeach;
			?>

			</tbody>

		</table>

		<div class="sui-box-body">

			<div class="sui-box-search">

				<div class="sui-search-left">

					<?php $this->bulk_actions( 'bottom' ); ?>

				</div>

				<div class="sui-search-right">

					<div class="sui-pagination-wrap">

						<span class="sui-pagination-results">
							<?php
							if ( 1 === $count ) {
								/* translators: ... */
								printf( esc_html__( '%s result', Forminator::DOMAIN ), esc_html( $count ) );
							} else {
								/* translators: ... */
								printf( esc_html__( '%s results', Forminator::DOMAIN ), esc_html( $count ) );
							}
							?>
						</span>

						<?php $this->paginate(); ?>

					</div>

				</div>

			</div>

		</div>

	</form>

<?php else : ?>

	<div class="sui-box sui-message">

		<?php if ( forminator_is_show_branding() ) : ?>
			<img src="<?php echo esc_url( $plugin_path . 'assets/img/forminator-submissions.png' ); ?>"
				srcset="<?php echo esc_url( $plugin_path . 'assets/img/forminator-submissions.png' ); ?> 1x,
				<?php echo esc_url( $plugin_path . 'assets/img/forminator-submissions@2x.png' ); ?> 2x"
				alt="<?php esc_html_e( 'Forminator', Forminator::DOMAIN ); ?>"
				class="sui-image"
				aria-hidden="true"/>
		<?php endif; ?>

		<div class="sui-message-content">

			<h2><?php echo forminator_get_form_name( $this->form_id, 'quiz' ); // phpcs:ignore ?></h2>

			<p><?php esc_html_e( 'You haven’t received any submissions for this quiz yet. When you do, you’ll be able to view all the data here.', Forminator::DOMAIN ); ?></p>

		</div>

	</div>

<?php endif; ?>
