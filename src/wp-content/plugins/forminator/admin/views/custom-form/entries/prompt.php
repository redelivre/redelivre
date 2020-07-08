<?php
if ( ! FORMINATOR_PRO ) {
	$submission               = $this->filtered_total_entries();
	$form_id                  = $this->get_form_id();
	$notice_success           = get_option( 'forminator_rating_success', false );
	$notice_dismissed         = get_option( 'forminator_rating_dismissed', false );
	$submission_later         = get_post_meta( $form_id, 'forminator_submission_rating_later' );
	$submission_later_dismiss = get_post_meta( $form_id, 'forminator_submission_rating_later_dismiss' );
	$live_payment_count       = $this->has_live_payments( $form_id );
	if ( ! $notice_dismissed && ! $notice_success ) {
		if ( $this->has_payments() ) {
			if ( ( 0 < $live_payment_count && 100 >= $submission && ! $submission_later ) ||
				 ( 0 < $live_payment_count && 100 < $submission && ! $submission_later_dismiss ) ) { ?>
				<div class="forminator-rating-notice sui-notice sui-notice-purple fui-notice-rate<?php echo forminator_is_show_branding() ? '' : ' fui-unbranded'; ?>"
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminator_dismiss_notification' ) ); ?>">
					<?php if ( $submission <= 100 ) { ?>
						<p><?php printf( esc_html__( "%1\$sCongratulations!%2\$s You have started collecting live payments on this form - that's awesome. We have spent countless hours developing this free plugin for you, and we would really appreciate it if you could drop us a rating on wp.org to help us spread the word and boost our motivation.", Forminator::DOMAIN ), '<strong>', '</strong>' ); ?></p>
					<?php } else { ?>
						<p><?php printf( esc_html__( "Hey, we noticed you just crossed %1\$s 100 submissions%2\$s on this form - that's awesome! We have spent countless hours developing this free plugin for you, and we would really appreciate it if you could drop us a rating on wp.org to help us spread the word and boost our motivation.", Forminator::DOMAIN ), '<strong> ', '</strong>' ); ?></p>
					<?php } ?>
					<p>
						<a type="button" href="#" target="_blank"
							class="sui-button sui-button-purple"
							data-prop="forminator_rating_success"><?php esc_html_e( 'Rate Forminator', Forminator::DOMAIN ); ?></a>

						<button type="button"
								class="sui-button sui-button-ghost"
								data-prop="<?php echo 100 > $live_payment_count ? 'forminator_submission_rating_later' : 'forminator_submission_rating_later_dismiss'; ?>"><?php esc_html_e( 'Maybe later', Forminator::DOMAIN ); ?></button>

						<a href="#" style="color: #888;"
							data-prop="forminator_rating_dismissed"
							data-prop="forminator_rating_dismissed"><?php esc_html_e( 'No Thanks', Forminator::DOMAIN ); ?></a>
					</p>

				</div>
			<?php }
		} else {
			if ( ( ( 10 < $submission && 100 >= $submission ) && ! $submission_later )
				|| ( 100 < $submission && ! $submission_later_dismiss ) ) {
				$milestone = ( 100 >= $submission ) ? 10 : 100;
				?>
				<div class="forminator-rating-notice sui-notice sui-notice-purple fui-notice-rate<?php echo forminator_is_show_branding() ? '' : ' fui-unbranded'; ?>"
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminator_dismiss_notification' ) ); ?>">

					<p><?php printf( esc_html__( "Hey, we noticed you just crossed %1\$s submissions%2\$s on this form - that's awesome! We have spent countless hours developing this free plugin for you, and we would really appreciate it if you could drop us a rating on wp.org to help us spread the word and boost our motivation.", Forminator::DOMAIN ), '<strong> ' . $milestone, '</strong>' ); ?></p>
					<p>
						<a type="button" href="#" target="_blank"
							class="sui-button sui-button-purple"
							data-prop="forminator_rating_success"><?php esc_html_e( 'Rate Forminator', Forminator::DOMAIN ); ?></a>

						<button type="button"
								class="sui-button sui-button-ghost"
								data-prop="<?php echo 100 > $submission ? 'forminator_submission_rating_later' : 'forminator_submission_rating_later_dismiss'; ?>"><?php esc_html_e( 'Maybe later', Forminator::DOMAIN ); ?></button>

						<a href="#" style="color: #888;"
							data-prop="forminator_rating_dismissed"
							data-prop="forminator_rating_dismissed"><?php esc_html_e( 'No Thanks', Forminator::DOMAIN ); ?></a>
					</p>

				</div>
			<?php }
		} ?>
		<script type="text/javascript">
			var ajaxUrl = '<?php echo forminator_ajax_url(); ?>';
			jQuery('.forminator-rating-notice a').on('click', function (e) {
				e.preventDefault();

				var $notice = jQuery(e.currentTarget).closest('.forminator-rating-notice'),
					prop = jQuery(this).data('prop');

				if ('forminator_rating_success' === prop) {
					window.open('https://wordpress.org/support/plugin/forminator/reviews/#new-post', '_blank');
				}

				jQuery.post(
					ajaxUrl,
					{
						action: 'forminator_dismiss_notification',
						prop: prop,
						_ajax_nonce: $notice.data('nonce')
					}
				).always(function () {
					$notice.hide();
				});
			});
			jQuery('.forminator-rating-notice button').on('click', function (e) {
				e.preventDefault();

				var $notice = jQuery(e.currentTarget).closest('.forminator-rating-notice'),
					prop = jQuery(this).data('prop');

				jQuery.post(
					ajaxUrl,
					{
						action: 'forminator_later_notification',
						prop: prop,
						form_id: <?php echo $form_id; ?>,
						_ajax_nonce: $notice.data('nonce')
					}
				).always(function () {
					$notice.hide();
				});
			});
		</script>
		<?php
	} ?>
<?php } ?>
