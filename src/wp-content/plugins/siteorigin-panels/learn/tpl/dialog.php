<?php
$user = wp_get_current_user();
?>

<div id="siteorigin-learn" style="display: none;">

	<div id="siteorigin-learn-overlay"></div>
	<div id="siteorigin-learn-dialog">

		<div class="poster-wrapper">
			<img src="" width="640px" height="360px" class="main-poster" />
			<img src="<?php echo plugin_dir_url( __FILE__ ) . '../img/play.svg' ?>" width="48px" height="48px" class="play-button" />
		</div>
		<div class="video-iframe">
		</div>

		<p class="learn-description"></p>

		<form class="signup-form" method="post" action="<?php echo esc_url( SiteOrigin_Learn_Dialog::SUBMIT_URL ) ?>" target="_blank" data-email-error="<?php esc_attr_e( 'Please enter a valid email', 'siteorigin-panels' ) ?>" >
			<?php if( ! empty( $user->data->display_name ) && $user->data->display_name !== $user->data->user_login ) : ?>
				<div class="form-field">
					<label for="siteorigin-learn-name-input"><?php esc_attr_e( 'Your Name', 'siteorigin-panels' ) ?></label>
					<input type="text" name="name" value="<?php echo ! empty( $user->data->display_name ) ? esc_attr( $user->data->display_name ) : '' ?>" id="siteorigin-learn-name-input" />
				</div>
			<?php endif ?>
			<div class="form-field">
				<label for="siteorigin-learn-email-input"><?php esc_attr_e( 'Your Email', 'siteorigin-panels' ) ?></label>
				<input type="text" name="email" value="<?php echo ! empty( $user->data->user_email ) ? esc_attr( $user->data->user_email ) : '' ?>" id="siteorigin-learn-email-input" />
			</div>
			<div class="form-submit">
				<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Sign Up', 'siteorigin-panels' ) ?>" />
			</div>
			<input type="hidden" name="lesson_id" value="" />
		</form>
		<div class="form-description"></div>

		<div class="learn-close"><?php _e( 'Close', 'siteorigin-panels' ) ?></div>

	</div>

</div>
