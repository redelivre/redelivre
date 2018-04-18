<?php
$user = wp_get_current_user();
/* @var $dialog_strings */
?>

<div id="siteorigin-learn" style="display: none;">
	<div id="siteorigin-learn-overlay"></div>
	<div id="siteorigin-learn-dialog">
		
		<h4 class="video-title"></h4>
		
		<div class="poster-wrapper">
			<img src="" width="640px" height="360px" class="main-poster"/>
			
			<div class="video-play-info">
				<div class="video-play-info-text">
					<?php esc_html_e( $dialog_strings['watch_video'] ) ?>
					<small><?php esc_html_e( $dialog_strings['loaded_from_vimeo'] ) ?></small>
				</div>
			</div>
			
			<img src="<?php echo siteorigin_panels_url( 'learn/img/play.svg' ) ?>" width="640px" height="58px"
			     class="play-button"/>
		</div>
		<div class="video-iframe">
		</div>
		
		<p class="learn-description"></p>
		
		<form class="signup-form" method="post" action="<?php echo esc_url( SiteOrigin_Learn_Dialog::SUBMIT_URL ) ?>"
		      target="_blank" data-email-error="<?php esc_attr( $dialog_strings['valid_email'] ) ?>">
			<?php if ( ! empty( $user->data->display_name ) && $user->data->display_name !== $user->data->user_login ) : ?>
				<div class="form-field">
					<label for="siteorigin-learn-name-input"><?php esc_html( $dialog_strings['your_name'] ) ?></label>
					<input type="text" name="name"
					       value="<?php echo ! empty( $user->data->display_name ) ? esc_attr( $user->data->display_name ) : '' ?>"
					       id="siteorigin-learn-name-input"/>
				</div>
			<?php endif ?>
			<div class="form-field">
				<label for="siteorigin-learn-email-input"><?php esc_html( $dialog_strings['your_email'] ) ?></label>
				<input type="text" name="email"
				       value="<?php echo ! empty( $user->data->user_email ) ? esc_attr( $user->data->user_email ) : '' ?>"
				       id="siteorigin-learn-email-input"/>
			</div>
			<div class="form-submit">
				<input type="submit" class="button-primary"
				       value="<?php echo esc_attr( $dialog_strings['sign_up'] ) ?>"/>
			</div>
			<input type="hidden" name="lesson_id" value=""/>
		</form>
		<div class="form-description"></div>
		
		<div class="learn-close"><?php esc_html( $dialog_strings['close'] ) ?></div>
	
	</div>

</div>
