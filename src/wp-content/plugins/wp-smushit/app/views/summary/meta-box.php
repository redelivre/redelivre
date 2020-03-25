<?php
/**
 * Summary meta box on dashboard page.
 *
 * @package WP_Smush
 *
 * @var string     $human_format
 * @var string     $human_size
 * @var int        $remaining
 * @var int        $resize_count
 * @var bool       $resize_enabled
 * @var int        $resize_savings
 * @var string|int $stats_percent
 * @var int        $total_optimized
 */

use Smush\Core\Settings;

if ( ! defined( 'WPINC' ) ) {
	die;
}

$tooltip = sprintf(
	/* translators: %d - number of images */
	esc_html__( 'You have %d images that need smushing', 'wp-smushit' ),
	absint( $remaining )
);

?>

<div class="sui-summary-image-space" aria-hidden="true"></div>

<div class="sui-summary-segment">
	<div class="sui-summary-details">
		<span class="sui-summary-large wp-smush-stats-human">
			<?php echo esc_html( $human_size ); ?>
		</span>
		<span class="sui-tooltip" data-tooltip="<?php echo esc_html( $tooltip ); ?>">
			<i class="sui-icon-info sui-warning smush-stats-icon <?php echo $remaining > 0 ? '' : 'sui-hidden'; ?>" aria-hidden="true"></i>
		</span>
		<span class="sui-summary-detail wp-smush-savings">
			<span class="wp-smush-stats-human"><?php echo esc_html( $human_format ); ?></span> /
			<span class="wp-smush-stats-percent"><?php echo esc_html( $stats_percent ); ?></span>%
		</span>
		<span class="sui-summary-sub">
			<?php esc_html_e( 'Total Savings', 'wp-smushit' ); ?>
		</span>
		<span class="smushed-items-count">
			<span class="wp-smush-count-total">
				<span class="sui-summary-detail wp-smush-total-optimised">
					<?php echo esc_html( $total_optimized ); ?>
				</span>
				<span class="sui-summary-sub">
					<?php esc_html_e( 'Images Smushed', 'wp-smushit' ); ?>
				</span>
			</span>
			<?php if ( $resize_count > 0 ) : ?>
				<span class="wp-smush-count-resize-total">
					<span class="sui-summary-detail wp-smush-total-optimised">
						<?php echo esc_html( $resize_count ); ?>
					</span>
					<span class="sui-summary-sub">
						<?php esc_html_e( 'Images Resized', 'wp-smushit' ); ?>
					</span>
				</span>
			<?php endif; ?>
		</span>
	</div>
</div>

<div class="sui-summary-segment">
	<ul class="sui-list smush-stats-list">
		<li class="smush-resize-savings">
			<span class="sui-list-label">
				<?php esc_html_e( 'Image Resize Savings', 'wp-smushit' ); ?>
				<?php if ( ! $resize_enabled && $resize_savings <= 0 ) : ?>
					<p class="wp-smush-stats-label-message">
						<?php
						$link_class = 'wp-smush-resize-enable-link';
						if ( ( is_multisite() && Settings::can_access( 'bulk' ) ) || 'bulk' !== $this->get_current_tab() ) {
							$settings_link = $this->get_page_url() . '#enable-resize';
						} else {
							$settings_link = '#';
							$link_class    = 'wp-smush-resize-enable';
						}
						printf(
							/* translators: %1$1s - opening <a> tag, %2$2s - closing <a> tag */
							esc_html__( 'Save a ton of space by not storing over-sized images on your server. %1$1sEnable image resizing%2$2s', 'wp-smushit' ),
							'<a role="button" class="' . esc_attr( $link_class ) . '" href="' . esc_url( $settings_link ) . '">',
							'<span class="sui-screen-reader-text">' . esc_html__( 'Clicking this link will toggle the Enable image resizing checkbox.', 'wp-smushit' ) . '</span></a>'
						);
						?>
					</p>
				<?php endif; ?>
			</span>
			<span class="sui-list-detail wp-smush-stats">
				<?php if ( $resize_enabled || $resize_savings > 0 ) : ?>
					<?php echo $resize_savings > 0 ? esc_html( $resize_savings ) : esc_html__( 'No resize savings available', 'wp-smushit' ); ?>
				<?php endif; ?>
			</span>
		</li>
		<?php
		/**
		 * Allows to output Directory Smush stats
		 */
		do_action( 'stats_ui_after_resize_savings' );
		?>
	</ul>
</div>
