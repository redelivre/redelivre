<?php
/**
 * Progress bar block.
 *
 * @package WP_Smush
 *
 * @var object $count  Core
 */

use Smush\Core\Core;

if ( ! defined( 'WPINC' ) ) {
	die;
}

?>

<div class="wp-smush-bulk-progress-bar-wrapper sui-hidden">
	<div class="sui-notice sui-notice-warning sui-hidden"></div>

	<div class="sui-notice sui-notice-warning sui-hidden" id="bulk_smush_warning">
		<p>
			<?php
			$upgrade_url = add_query_arg(
				array(
					'utm_source'   => 'smush',
					'utm_medium'   => 'plugin',
					'utm_campaign' => 'smush_bulksmush_limit_reached_notice',
				),
				esc_url( 'https://premium.wpmudev.org/project/wp-smush-pro/' )
			);

			printf(
				/* translators: %s1$d - bulk smush limit, %2$s - upgrade link, %3$s - </a>, %4$s - <strong>, $5$s - </strong> */
				esc_html__( 'The free version of Smush allows you to compress %1$d images at a time. %2$sUpgrade to Pro for FREE%3$s to compress unlimited images at once or click Resume to compress another %1$d images.', 'wp-smushit' ),
				absint( Core::$max_free_bulk ),
				'<a href="' . esc_url( $upgrade_url ) . '" target="_blank">',
				'</a>'
			)
			?>
		</p>
	</div>

	<div class="sui-progress-block sui-progress-can-close">
		<div class="sui-progress">
			<span class="sui-progress-icon" aria-hidden="true">
				<i class="sui-icon-loader sui-loading"></i>
			</span>
			<div class="sui-progress-text">
				<span class="wp-smush-images-percent">0%</span>
			</div>
			<div class="sui-progress-bar">
				<span class="wp-smush-progress-inner" style="width: 0%"></span>
			</div>
		</div>
		<button class="sui-progress-close wp-smush-cancel-bulk" type="button">
			<?php esc_html_e( 'Cancel', 'wp-smushit' ); ?>
		</button>
		<button class="sui-progress-close sui-button-icon sui-tooltip wp-smush-all sui-hidden" type="button" data-tooltip="<?php esc_html_e( 'Resume scan.', 'wp-smushit' ); ?>">
			<i class="sui-icon-play"></i>
		</button>
	</div>

	<div class="sui-progress-state">
		<span class="sui-progress-state-text">
			<span>0</span>/<span><?php echo absint( $count->remaining_count ); ?></span> <?php esc_html_e( 'images smushed', 'wp-smushit' ); ?>
		</span>
	</div>

	<div id="bulk-smush-resume-button" class="sui-hidden">
		<div style="display: flex; flex-flow: row-reverse;">
			<a class="wp-smush-all sui-button wp-smush-started">
				<i class="sui-icon-play" aria-hidden="true"></i>
				<?php esc_html_e( 'Resume', 'wp-smushit' ); ?>
			</a>
		</div>
	</div>

	<div class="sui-notice">
		<p>
			<?php esc_html_e( 'Bulk smush is currently running. You need to keep this page open for the process to complete.', 'wp-smushit' ); ?>
		</p>
	</div>
</div>
