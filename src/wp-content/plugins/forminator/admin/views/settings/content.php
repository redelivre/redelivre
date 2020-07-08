<?php
$section = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : 'dashboard';
?>
<div class="sui-row-with-sidenav">

	<div class="sui-sidenav">

		<ul class="sui-vertical-tabs sui-sidenav-hide-md">

			<li class="sui-vertical-tab <?php echo esc_attr( 'dashboard' === $section ? 'current' : '' ); ?>">
				<a href="#" data-nav="dashboard"><?php esc_html_e( 'General', Forminator::DOMAIN ); ?></a>
			</li>

			<li class="sui-vertical-tab <?php echo esc_attr( 'accessibility' === $section ? 'current' : '' ); ?>">
				<a href="#" data-nav="accessibility"><?php esc_html_e( 'Accessibility', Forminator::DOMAIN ); ?></a>
			</li>

			<li class="sui-vertical-tab <?php echo esc_attr( 'data' === $section ? 'current' : '' ); ?>">
				<a href="#" data-nav="data"><?php esc_html_e( 'Data', Forminator::DOMAIN ); ?></a>
			</li>

			<li class="sui-vertical-tab <?php echo esc_attr( 'recaptcha' === $section ? 'current' : '' ); ?>">
				<a href="#" data-nav="recaptcha"><?php esc_html_e( 'Google reCAPTCHA', Forminator::DOMAIN ); ?></a>
			</li>

			<li class="sui-vertical-tab <?php echo esc_attr( 'import' === $section ? 'current' : '' ); ?>">
				<a href="#" data-nav="import"><?php esc_html_e( 'Import', Forminator::DOMAIN ); ?></a>
			</li>

			<li class="sui-vertical-tab <?php echo esc_attr( 'submissions' === $section ? 'current' : '' ); ?>">
				<a href="#" data-nav="submissions"><?php esc_html_e( 'Submissions', Forminator::DOMAIN ); ?></a>
			</li>

			<li class="sui-vertical-tab <?php echo esc_attr( 'payments' === $section ? 'current' : '' ); ?>">
				<a href="#" data-nav="payments"><?php esc_html_e( 'Payments', Forminator::DOMAIN ); ?></a>
			</li>

		</ul>

		<select class="sui-mobile-nav sui-sidenav-hide-lg">
			<option value="dashboard"><?php esc_html_e( 'General', Forminator::DOMAIN ); ?></option>
			<option value="accessibility"><?php esc_html_e( 'Accessibility', Forminator::DOMAIN ); ?></option>
			<option value="data"><?php esc_html_e( 'Data', Forminator::DOMAIN ); ?></option>
			<option value="recaptcha"><?php esc_html_e( 'Google reCAPTCHA', Forminator::DOMAIN ); ?></option>
			<option value="import"><?php esc_html_e( 'Import', Forminator::DOMAIN ); ?></option>
			<option value="submissions"><?php esc_html_e( 'Submissions', Forminator::DOMAIN ); ?></option>
			<option value="payments"><?php esc_html_e( 'Payments', Forminator::DOMAIN ); ?></option>
		</select>

	</div>

	<?php $this->template( 'settings/tab-dashboard' ); ?>
	<?php $this->template( 'settings/tab-recaptcha' ); ?>
	<?php $this->template( 'settings/tab-data' ); ?>
	<?php $this->template( 'settings/tab-submissions' ); ?>
	<?php $this->template( 'settings/tab-payments' ); ?>
	<?php $this->template( 'settings/tab-accessibility' ); ?>
	<?php $this->template( 'settings/tab-import' ); ?>

</div>
