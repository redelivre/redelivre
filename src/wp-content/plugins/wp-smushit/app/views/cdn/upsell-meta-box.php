<?php
/**
 * Upsell CDN meta box.
 *
 * @since 3.0
 * @package WP_Smush
 *
 * @var string $upgrade_url  Upgrade URL.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

?>

<div class="sui-block-content-center">
	<img src="<?php echo esc_url( WP_SMUSH_URL . 'app/assets/images/graphic-smush-cdn-default.png' ); ?>"
		srcset="<?php echo esc_url( WP_SMUSH_URL . 'app/assets/images/graphic-smush-cdn-default@2x.png' ); ?> 2x"
		alt="<?php esc_html_e( 'Smush CDN', 'wp-smushit' ); ?>">

	<p>
		<?php
		esc_html_e(
			'Multiply the speed and savings! Upload huge images and the Smush CDN will perfectly resize the files, safely convert to a Next-Gen format (WebP), and delivers them directly to your visitors from our blazing-fast multi-location globe servers.',
			'wp-smushit'
		);
		?>
	</p>

	<a href="<?php echo esc_url( $upgrade_url ); ?>" class="sui-button sui-button-green" target="_blank">
		<?php esc_html_e( 'UPGRADE', 'wp-smushit' ); ?>
	</a>
</div>
