<?php
// defaults
$vars = array(
	'error_message' => '',
	'is_close'      => false,
);
/** @var array $template_vars */
foreach ( $template_vars as $key => $val ) {
	$vars[ $key ] = $val;
}
?>

<div id="forminator-integrations" class="wpmudev-settings--box">
	<div class="sui-box">
		<div class="sui-box-header">
			<h2 class="sui-box-title"><?php esc_html_e( 'Authorizing AWeber', Forminator::DOMAIN ); ?></h2>
		</div>
		<div class="sui-box-body">
			<?php if ( ! empty( $vars['error_message'] ) ) : ?>
				<span class="sui-notice sui-notice-error"><p><?php echo esc_html( $vars['error_message'] ); ?></p></span>
			<?php elseif ( $vars['is_close'] ) : ?>
				<span class="sui-notice sui-notice-success">
					<p>
						<?php
						esc_html_e(
							'Successfully authorized AWeber, you can go back to integration settings.',
							Forminator::DOMAIN
						);
						?>
					</p>
				</span>
			<?php else : ?>
				<span class="sui-notice sui-notice sui-notice-loading">
					<p><?php esc_html_e( 'Please Wait...', Forminator::DOMAIN ); ?></p>
				</span>
			<?php endif; ?>
		</div>
	</div>
</div>

<script>
	// SO.
	function getHashParams() {

		var hashParams = {};
		var e,
			a          = /\+/g,  // Regex for replacing addition symbol with a space
			r          = /([^&;=]+)=?([^&;]*)/g,
			d          = function (s) {
				return decodeURIComponent(s.replace(a, " "));
			},
			q          = window.location.hash.substring(1);

		while (e = r.exec(q))
			hashParams[d(e[1])] = d(e[2]);

		return hashParams;
	}

	(function ($) {
		$(document).ready(function (e) {
			<?php if ( $vars['is_close'] ) : ?>

			setTimeout(function () {
				window.close();
			}, 3000);

			<?php endif; ?>
			var hash_params = getHashParams();
			if (typeof hash_params.token !== 'undefined') {
				var current_href = window.location.href;
				current_href     = current_href.replace(window.location.hash, '');

				window.location.href = current_href + '&token=' + hash_params.token;
			}
		});

	})(jQuery);
</script>
