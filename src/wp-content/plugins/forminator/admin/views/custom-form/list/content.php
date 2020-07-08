<?php
/** @var Forminator_CForm_Page $this */

// Empty message
$image_empty   = forminator_plugin_url() . 'assets/images/forminator-empty-message.png';
$image_empty2x = forminator_plugin_url() . 'assets/images/forminator-empty-message@2x.png';

// Count total forms
$count        = $this->countModules();
$count_active = $this->countModules( 'publish' );

// available bulk actions
$bulk_actions = $this->bulk_actions();

// Start date for retrieving the information of the last 30 days in sql format
$sql_month_start_date = date( 'Y-m-d H:i:s', strtotime( '-30 days midnight' ) );// phpcs:ignore

// Count total entries from last 30 days
$total_entries_from_last_month = count( Forminator_Form_Entry_Model::get_newer_entry_ids( 'custom-forms', $sql_month_start_date ) );
?>

<?php if ( $count > 0 ) { ?>

	<div class="sui-box sui-summary sui-summary-sm <?php echo esc_attr( $this->get_box_summary_classes() ); ?>">

		<div class="sui-summary-image-space" aria-hidden="true" style="<?php echo esc_attr( $this->get_box_summary_image_style() ); ?>"></div>

		<div class="sui-summary-segment">

			<div class="sui-summary-details">

				<span class="sui-summary-large"><?php echo esc_html( $count_active ); ?></span>

				<span class="sui-summary-sub"><?php printf( esc_html( _n( 'Active Form', 'Active Forms', esc_html( $count_active ), Forminator::DOMAIN ) ), esc_html( $count_active ) ); ?></span>

			</div>

		</div>

		<div class="sui-summary-segment">

			<ul class="sui-list">

				<li>
					<span class="sui-list-label"><?php esc_html_e( 'Last Submission', Forminator::DOMAIN ); ?></span>
					<span class="sui-list-detail"><?php echo esc_html( forminator_get_latest_entry_time( 'custom-forms' ) ); ?></span>
				</li>

				<li>
					<span class="sui-list-label"><?php esc_html_e( 'Submissions in the last 30 days', Forminator::DOMAIN ); ?></span>
					<span class="sui-list-detail"><?php echo esc_html( $total_entries_from_last_month ); ?></span>
				</li>

			</ul>

		</div>

	</div>

	<!-- START: Bulk actions and pagination -->
	<div class="fui-listings-pagination">

		<div class="fui-pagination-mobile sui-pagination-wrap">

			<span class="sui-pagination-results"><?php /* translators: ... */ echo esc_html( sprintf( _n( '%s result', '%s results', $count, Forminator::DOMAIN ), $count ) ); ?></span>

			<?php $this->pagination(); ?>

		</div>

		<div class="fui-pagination-desktop sui-box">

			<div class="sui-box-search">

				<form method="post" name="bulk-action-form" class="sui-search-left"
					style="display: flex; align-items: center;">

					<?php wp_nonce_field( 'forminatorCustomFormRequest', 'forminatorNonce' ); ?>

					<input type="hidden" name="ids" value="" />

					<label for="forminator-check-all-modules" class="sui-checkbox">
						<input type="checkbox" id="forminator-check-all-modules">
						<span aria-hidden="true"></span>
						<span class="sui-screen-reader-text"><?php esc_html_e( 'Select all', Forminator::DOMAIN ); ?></span>
					</label>

					<select class="sui-select-sm sui-select-inline fui-select-listing-actions" name="forminator_action">
						<option value=""><?php esc_html_e( 'Bulk Action', Forminator::DOMAIN ); ?></option>
						<?php foreach ( $bulk_actions as $val => $label ) : ?>
							<option value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>

					<button class="sui-button"><?php esc_html_e( 'Apply', Forminator::DOMAIN ); ?></button>

				</form>

				<div class="sui-search-right">

					<div class="sui-pagination-wrap">
						<span class="sui-pagination-results"><?php /* translators: ... */ echo esc_html( sprintf( _n( '%s result', '%s results', $count, Forminator::DOMAIN ), $count ) ); ?></span>
						<?php $this->pagination(); ?>
					</div>

				</div>

			</div>

		</div>

	</div>
	<!-- END: Bulk actions and pagination -->

	<div class="sui-accordion sui-accordion-block" id="forminator-modules-list">

		<?php
		foreach ( $this->getModules() as $module ) {
			$module_entries_from_last_month = 0 !== $module['entries'] ? count( Forminator_Form_Entry_Model::get_newer_entry_ids_of_form_id( $module['id'], $sql_month_start_date ) ) : 0;
			$opened_class                   = '';
			$opened_chart                   = '';

			if( isset( $_GET['view-stats'] ) && intval( $_GET['view-stats'] ) === intval( $module['id'] ) ) { // phpcs:ignore
				$opened_class = ' sui-accordion-item--open forminator-scroll-to';
				$opened_chart = ' sui-chartjs-loaded';
			}
			?>

			<div class="sui-accordion-item<?php echo esc_attr( $opened_class ); ?>">

				<div class="sui-accordion-item-header">

					<div class="sui-accordion-item-title sui-trim-title">

						<label for="wpf-module-<?php echo esc_attr( $module['id'] ); ?>" class="sui-checkbox sui-accordion-item-action">
							<input type="checkbox" id="wpf-module-<?php echo esc_attr( $module['id'] ); ?>" value="<?php echo esc_html( $module['id'] ); ?>">
							<span aria-hidden="true"></span>
							<span class="sui-screen-reader-text"><?php esc_html_e( 'Select this form', Forminator::DOMAIN ); ?></span>
						</label>

						<span class="sui-trim-text"><?php echo forminator_get_form_name( $module['id'], 'custom_form' );// phpcs:ignore ?></span>

						<?php
						if ( 'publish' === $module['status'] ) {
							echo '<span class="sui-tag sui-tag-blue">' . esc_html__( 'Published', Forminator::DOMAIN ) . '</span>';
						}
						?>

						<?php
						if ( 'draft' === $module['status'] ) {
							echo '<span class="sui-tag">' . esc_html__( 'Draft', Forminator::DOMAIN ) . '</span>';
						}
						?>

					</div>

					<div class="sui-accordion-item-date"><strong><?php esc_html_e( 'Last Submission', Forminator::DOMAIN ); ?></strong> <?php echo esc_html( $module['last_entry_time'] ); ?></div>

					<div class="sui-accordion-col-auto">

						<a href="<?php echo admin_url( 'admin.php?page=forminator-cform-wizard&id=' . $module['id'] ); // phpcs:ignore ?>"
							class="sui-button sui-button-ghost sui-accordion-item-action sui-desktop-visible">
							<i class="sui-icon-pencil" aria-hidden="true"></i> <?php esc_html_e( 'Edit', Forminator::DOMAIN ); ?>
						</a>

						<a href="<?php echo admin_url( 'admin.php?page=forminator-cform-wizard&id=' . $module['id'] ); // phpcs:ignore ?>"
							class="sui-button-icon sui-accordion-item-action sui-mobile-visible">
							<i class="sui-icon-pencil" aria-hidden="true"></i>
							<span class="sui-screen-reader-text"><?php esc_html_e( 'Edit', Forminator::DOMAIN ); ?></span>
						</a>

						<div class="sui-dropdown sui-accordion-item-action">

							<button class="sui-button-icon sui-dropdown-anchor">
								<i class="sui-icon-widget-settings-config" aria-hidden="true"></i>
								<span class="sui-screen-reader-text"><?php esc_html_e( 'Open list settings', Forminator::DOMAIN ); ?></span>
							</button>

							<ul>

								<li><a href="#"
									class="wpmudev-open-modal"
									data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminator_popup_preview_cforms' ) ); ?>"
									data-form-id="<?php echo esc_attr( $module['id'] ); ?>"
									data-modal="preview_cforms"
									data-modal-title="<?php echo sprintf( '%s - %s', esc_html__( 'Preview Custom Form', Forminator::DOMAIN ), forminator_get_form_name( $module['id'], 'custom_form' ) ); // phpcs:ignore ?>">
									<i class="sui-icon-eye" aria-hidden="true"></i> <?php esc_html_e( 'Preview', Forminator::DOMAIN ); ?>
								</a></li>

								<li>
									<button class="copy-clipboard" data-shortcode='[forminator_form id="<?php echo esc_attr( $module['id'] ); ?>"]'><i class="sui-icon-code" aria-hidden="true"></i> <?php esc_html_e( 'Copy Shortcode', Forminator::DOMAIN ); ?></button>
								</li>

								<?php if ( 'publish' === $module['status'] ) { ?>

									<li>
										<form method="post">
											<input type="hidden" name="forminator_action" value="update-status">
											<input type="hidden" name="id" value="<?php echo esc_attr( $module['id'] ); ?>"/>
											<input type="hidden" name="status" value="draft"/>
											<?php wp_nonce_field( 'forminatorCustomFormRequest', 'forminatorNonce' ); ?>
											<button type="submit">
												<i class="sui-icon-unpublish" aria-hidden="true"></i> <?php esc_html_e( 'Unpublish', Forminator::DOMAIN ); ?>
											</button>
										</form>
									</li>

								<?php } ?>

								<?php if ( 'draft' === $module['status'] ) { ?>

									<li>
										<form method="post">
											<input type="hidden" name="forminator_action" value="update-status">
											<input type="hidden" name="id" value="<?php echo esc_attr( $module['id'] ); ?>"/>
											<input type="hidden" name="status" value="publish"/>
											<?php wp_nonce_field( 'forminatorCustomFormRequest', 'forminatorNonce' ); ?>
											<button type="submit">
												<i class="sui-icon-upload-cloud" aria-hidden="true"></i> <?php esc_html_e( 'Publish', Forminator::DOMAIN ); ?>
											</button>
										</form>
									</li>

									<li><button>

									</button></li>

								<?php } ?>

								<li><a href="<?php echo admin_url( 'admin.php?page=forminator-entries&form_type=forminator_forms&form_id=' . $module['id'] ); // phpcs:ignore ?>">
									<i class="sui-icon-community-people" aria-hidden="true"></i> <?php esc_html_e( 'View Submissions', Forminator::DOMAIN ); ?>
								</a></li>

								<li><form method="post">
									<input type="hidden" name="forminator_action" value="clone">
									<input type="hidden" name="id" value="<?php echo esc_attr( $module['id'] ); ?>"/>
									<?php wp_nonce_field( 'forminatorCustomFormRequest', 'forminatorNonce' ); ?>
									<button type="submit">
										<i class="sui-icon-page-multiple" aria-hidden="true"></i> <?php esc_html_e( 'Duplicate', Forminator::DOMAIN ); ?>
									</button>
								</form></li>

								<li><form method="post">
									<input type="hidden" name="forminator_action" value="reset-views">
									<input type="hidden" name="id" value="<?php echo esc_attr( $module['id'] ); ?>"/>
									<?php wp_nonce_field( 'forminatorCustomFormRequest', 'forminatorNonce' ); ?>
									<button type="submit"><i class="sui-icon-update" aria-hidden="true"></i> <?php esc_html_e( 'Reset Tracking data', Forminator::DOMAIN ); ?></button>
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
									<button
										class="sui-option-red wpmudev-open-modal"
										data-modal="delete-module"
										data-modal-title="<?php esc_attr_e( 'Delete Form', Forminator::DOMAIN ); ?>"
										data-modal-content="<?php esc_attr_e( 'Are you sure you wish to permanently delete this form?', Forminator::DOMAIN ); ?>"
										data-form-id="<?php echo esc_attr( $module['id'] ); ?>"
										data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminatorCustomFormRequest' ) ); ?>"
									>
										<i class="sui-icon-trash" aria-hidden="true"></i> <?php esc_html_e( 'Delete', Forminator::DOMAIN ); ?>
									</button>
								</li>

							</ul>

						</div>

						<button class="sui-button-icon sui-accordion-open-indicator" aria-label="<?php esc_html_e( 'Open item', Forminator::DOMAIN ); ?>"><i class="sui-icon-chevron-down" aria-hidden="true"></i></button>

					</div>

				</div>

				<div class="sui-accordion-item-body">

					<ul class="sui-accordion-item-data">

						<li data-col="large">
							<strong><?php esc_html_e( 'Last Submission', Forminator::DOMAIN ); ?></strong>
							<span><?php echo esc_html( $module['last_entry_time'] ); ?></span>
						</li>

						<li data-col="small">
							<strong><?php esc_html_e( 'Views', Forminator::DOMAIN ); ?></strong>
							<span><?php echo esc_html( $module['views'] ); ?></span>
						</li>

						<li>
							<strong><?php esc_html_e( 'Submissions', Forminator::DOMAIN ); ?></strong>
							<a href="<?php echo admin_url( 'admin.php?page=forminator-cform-view&form_id=' . $module['id'] ); // phpcs:ignore ?>"><?php echo esc_html( $module['entries'] ); ?></a>
						</li>

						<li>
							<strong><?php esc_html_e( 'Conversion Rate', Forminator::DOMAIN ); ?></strong>
							<span><?php echo esc_html( $this->getRate( $module ) ); ?>%</span>
						</li>

					</ul>

					<div class="sui-chartjs sui-chartjs-animated<?php echo esc_attr( $opened_chart ); ?>" data-chart-id="<?php echo esc_attr( $module['id'] ); ?>">

						<div class="sui-chartjs-message sui-chartjs-message--loading">
							<p><i class="sui-icon-loader sui-loading" aria-hidden="true"></i> <?php esc_html_e( 'Loading data...', Forminator::DOMAIN ); ?></p>
						</div>

						<?php if ( 0 === $module['entries'] ) { ?>

							<div class="sui-chartjs-message sui-chartjs-message--empty">
								<p><i class="sui-icon-info" aria-hidden="true"></i> <?php esc_html_e( "Your form doesn't have any submissions yet. Try again in a moment.", Forminator::DOMAIN ); ?></p>
							</div>

						<?php } else { ?>

							<?php if ( 0 === $module_entries_from_last_month ) { ?>

								<?php
								if ( 'draft' === $module['status'] ) {
									$message = esc_html__( "This form is in draft state, so we've paused collecting data until you publish it live.", Forminator::DOMAIN );
								} else {
									$message = esc_html__( "Your form didn't collect submissions the past 30 days.", Forminator::DOMAIN );
								}
								?>

								<div class="sui-chartjs-message sui-chartjs-message--empty">
									<p><i class="sui-icon-info" aria-hidden="true"></i> <?php echo esc_html( $message ); ?></p>
								</div>

							<?php } else { ?>

								<?php if ( 'draft' === $module['status'] ) { ?>

									<div class="sui-chartjs-message">
										<p><i class="sui-icon-info" aria-hidden="true"></i> <?php esc_html_e( "This form is in draft state, so we've paused collecting data until you publish it live.", Forminator::DOMAIN ); ?></p>
									</div>

								<?php } ?>

							<?php } ?>

						<?php } ?>

						<div class="sui-chartjs-canvas">

							<?php if ( ( 0 !== $module['entries'] ) || ( 0 !== $module_entries_from_last_month ) ) { ?>
								<canvas id="forminator-form-<?php echo $module['id']; // phpcs:ignore ?>-stats"></canvas>
							<?php } ?>

						</div>

					</div>

				</div>

			</div>

		<?php } ?>

	</div>

<?php } else { ?>

	<div class="sui-box sui-message sui-message-lg">

		<?php if ( forminator_is_show_branding() ) : ?>
			<img src="<?php echo esc_url( $image_empty ); ?>"
				srcset="<?php echo esc_url( $image_empty2x ); ?> 1x, <?php echo esc_url( $image_empty2x ); ?> 2x"
				alt="<?php esc_html_e( 'Empty forms', Forminator::DOMAIN ); ?>"
				class="sui-image sui-image-center"
				aria-hidden="true"/>
		<?php endif; ?>

		<div class="sui-message-content">

			<p><?php esc_html_e( 'Create custom forms for all your needs with as many fields as you like. From contact forms to quote requests and everything in between.', Forminator::DOMAIN ); ?></p>

			<?php if ( Forminator::is_import_export_feature_enabled() ) : ?>

				<p>
					<button class="sui-button sui-button-blue wpmudev-button-open-modal" data-modal="custom_forms"><i class="sui-icon-plus" aria-hidden="true"></i> <?php esc_html_e( 'Create', Forminator::DOMAIN ); ?></button>

					<a href="#"
						class="sui-button wpmudev-open-modal"
						data-modal="import_cform"
						data-modal-title=""
						data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminator_popup_import_cform' ) ); ?>"><i class="sui-icon-upload-cloud" aria-hidden="true"></i> <?php esc_html_e( 'Import', Forminator::DOMAIN ); ?></a>
				</p>

			<?php else : ?>

				<p><button class="sui-button sui-button-blue wpmudev-button-open-modal" data-modal="custom_forms">
					<i class="sui-icon-plus" aria-hidden="true"></i> <?php esc_html_e( 'Create', Forminator::DOMAIN ); ?>
				</button></p>

			<?php endif; ?>

		</div>

	</div>

<?php } ?>

<?php
$days_array    = array();
$default_array = array();

for ( $h = 30; $h >= 0; $h-- ) {
	$time                   = strtotime( '-' . $h . ' days' );
	$date                   = date( 'Y-m-d', $time );// phpcs:ignore
	$default_array[ $date ] = 0;
	$days_array[]           = date( 'M j, Y', $time );// phpcs:ignore
}

foreach ( $this->getModules() as $module ) {

	if ( 0 === $module['entries'] ) {
		$submissions_data = $default_array;
	} else {
		$submissions       = Forminator_Form_Entry_Model::get_form_latest_entries_count_grouped_by_day( $module['id'], $sql_month_start_date );
		$submissions_array = wp_list_pluck( $submissions, 'entries_amount', 'date_created' );
		$submissions_data  = array_merge( $default_array, array_intersect_key( $submissions_array, $default_array ) );
	}

	// Get highest value
	$highest_submission = max( $submissions_data );

	// Calculate canvas top spacing
	$canvas_top_spacing = $highest_submission + 8;
	?>

<script>

	var ctx = document.getElementById( 'forminator-form-<?php echo $module['id']; // phpcs:ignore ?>-stats' );

	var monthDays = [ '<?php echo implode( "', '", $days_array ); // phpcs:ignore ?>' ],
		submissions = [ <?php echo implode( ', ', $submissions_data );  // phpcs:ignore ?> ]
		;

	var chartData = {
		labels: monthDays,
		datasets: [{
			label: '<?php esc_html_e( 'Submissions', Forminator::DOMAIN ); ?>',
			data: submissions,
			backgroundColor: [
				'#E1F6FF'
			],
			borderColor: [
				'#17A8E3'
			],
			borderWidth: 2,
			pointRadius: 0,
			pointHitRadius: 20,
			pointHoverRadius: 5,
			pointHoverBorderColor: '#17A8E3',
			pointHoverBackgroundColor: '#17A8E3'
		}]
	};

	var chartOptions = {
		maintainAspectRatio: false,
		legend: {
			display: false
		},
		scales: {
			xAxes: [{
				display: false,
				gridLines: {
					color: 'rgba(0, 0, 0, 0)'
				}
			}],
			yAxes: [{
				display: false,
				gridLines: {
					color: 'rgba(0, 0, 0, 0)'
				},
				ticks: {
					beginAtZero: false,
					min: 0,
					max: <?php echo esc_attr( $canvas_top_spacing ); ?>,
					stepSize: 1
				}
			}]
		},
		elements: {
			line: {
				tension: 0
			},
			point: {
				radius: 0
			}
		},
		tooltips: {
			custom: function( tooltip ) {
				if ( ! tooltip ) return;
				// disable displaying the color box;
				tooltip.displayColors = false;
			},
			callbacks: {
				title: function( tooltipItem, data ) {
					return tooltipItem[0].yLabel + " Submissions";
				},
				label: function( tooltipItem, data ) {
					return tooltipItem.xLabel;
				},
				// Set label text color
				labelTextColor:function( tooltipItem, chart ) {
					return '#AAAAAA';
				}
			}
		},
		plugins: {
			datalabels: {
				display: false
			}
		}
	};

	if (ctx) {
		var myChart = new Chart(ctx, {
			type: 'line',
			fill: 'start',
			data: chartData,
			plugins: [
				ChartDataLabels
			],
			options: chartOptions
		});
	}


</script>

<?php } ?>
