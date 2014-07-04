<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2><?php _e('SiteOrigin Page Builder', 'siteorigin-panels') ?></h2>

	<form action="options.php" method="POST">
		<?php do_settings_sections( 'siteorigin-panels' ); ?>
		<?php settings_fields( 'siteorigin-panels' ); ?>
		<?php submit_button(); ?>
	</form>
</div>