<?php

/**
 * Display the update notice in the Page Builder interface
 */
function siteorigin_panels_update_notice(){
	$dismissed = get_option('siteorigin_panels_notice_dismissed');

	if(empty($dismissed) || $dismissed != SITEORIGIN_PANELS_VERSION) {
		wp_enqueue_script('siteorigin-panels-admin-notice', plugin_dir_url(SITEORIGIN_PANELS_BASE_FILE) . 'js/panels.admin.notice.min.js', array('jquery'), SITEORIGIN_PANELS_VERSION);

		?>
		<div class="updated">
			<p>
				<?php
				if( get_option('siteorigin_panels_initial_version') == SITEORIGIN_PANELS_VERSION ) {
					printf( __("You've successfully installed <strong>Page Builder</strong> version %s. ", 'siteorigin-panels'), SITEORIGIN_PANELS_VERSION );
				}
				else {
					printf( __("You've successfully updated <strong>Page Builder</strong> to version %s. ", 'siteorigin-panels'), SITEORIGIN_PANELS_VERSION );
				}

				printf(
					__('Please post on our <a href="%s" target="_blank">support forums</a> if you have any issues and sign up to <a href="%s" target="_blank">our newsletter</a> to stay up to date.', 'siteorigin-panels'),
					'http://siteorigin.com/threads/plugin-page-builder/',
					'http://siteorigin.com/page-builder/#newsletter'
				)
				?>
			</p>
			<p>
				<a href="http://siteorigin.com/threads/plugin-page-builder/" class="button button-secondary" target="_blank"><?php _e('Support Forums', 'siteorigin-panels') ?></a>
				<a href="http://siteorigin.com/page-builder/#newsletter" class="button button-secondary" target="_blank"><?php _e('Newsletter', 'siteorigin-panels') ?></a>
				<?php if(empty($dismissed)) : ?>
					<a href="<?php echo add_query_arg('action', 'siteorigin_panels_update_notice_dismiss', admin_url( 'admin-ajax.php') ) ?>" class="button button-primary siteorigin-panels-dismiss"><?php _e('Dismiss', 'siteorigin-panels') ?></a>
				<?php endif; ?>
			</p>
		</div>
		<?php
		if( !empty($dismissed) && $dismissed != SITEORIGIN_PANELS_VERSION ) {
			// The user has already dismissed this message, so we'll show it once and update the dismissed version
			update_option('siteorigin_panels_notice_dismissed', SITEORIGIN_PANELS_VERSION);
		}
	}
}
add_action('siteorigin_panels_before_interface', 'siteorigin_panels_update_notice');

/**
 * Returns a list of incompatible plugins
 *
 * @return mixed
 */
function siteorigin_panels_get_incompatible_plugins(){
	static $incompatible = null;
	if(is_null($incompatible)) {
		$incompatible = include( plugin_dir_path(__FILE__).'/incompatible.php' );
	}

	return $incompatible;
}

/**
 * Displays notices for incompatible plugins
 */
function siteorigin_panels_incompatibility_notice(){
	$active = get_option('active_plugins');
	$incompatible = siteorigin_panels_get_incompatible_plugins();
	$active_incompatible = array_intersect($active, array_keys($incompatible));

	if( !empty($active_incompatible) ) {
		// Don't show this for dismissed
		$dismissed_incompatible = get_option('siteorigin_panels_incompatible_dismissed');
		if( !empty($dismissed_incompatible) ) {
			$non_dismissed = array_diff( $active_incompatible, (array) $dismissed_incompatible );
			if( empty($non_dismissed) ) return;
		}

		wp_enqueue_script('siteorigin-panels-admin-notice', plugin_dir_url(SITEORIGIN_PANELS_BASE_FILE) . 'js/panels.admin.notice.min.js', array('jquery'), SITEORIGIN_PANELS_VERSION);

		?>
		<div class="error">
			<p>
				<?php

				_e("One or more of your active plugins are known to be incompatible with Page Builder.", 'siteorigin-panels');
				?><ul><?php
				foreach($active_incompatible as $incompatible_plugin) {
					$data = get_plugin_data(WP_PLUGIN_DIR . '/' . $incompatible_plugin);
					if( empty( $data['Name'] ) ) continue;

					echo '<li>';
					echo $data['Name'];
					if( !empty($incompatible[$incompatible_plugin]['more']) ) {
						echo ' - <a href="' . esc_url($incompatible[$incompatible_plugin]['more']) . '" target="_blank">' . __('More', 'siteorigin-panels') . '</a>';
					}
					echo '</li>';
				}
				?></ul><?php

				?>
			</p>
			<p>
				<a href="http://siteorigin.com/threads/plugin-page-builder/" class="button button-secondary" target="_blank"><?php _e('Support Forums', 'siteorigin-panels') ?></a>
				<a href="<?php echo add_query_arg('action', 'siteorigin_panels_incompatible_notice_dismiss', admin_url( 'admin-ajax.php') ) ?>" class="button button-primary siteorigin-panels-dismiss"><?php _e('Dismiss', 'siteorigin-panels') ?></a>
			</p>
		</div>
		<?php

	}

}
add_action('siteorigin_panels_before_interface', 'siteorigin_panels_incompatibility_notice');

/**
 * This action handles dismissing the updated notice.
 */
function siteorigin_panels_update_notice_dismiss_action(){
	add_option('siteorigin_panels_notice_dismissed', SITEORIGIN_PANELS_VERSION, '', 'no');
	exit();
}
add_action('wp_ajax_siteorigin_panels_update_notice_dismiss', 'siteorigin_panels_update_notice_dismiss_action');

/**
 * This action handles dismissing the incompatibility notice.
 */
function siteorigin_panels_incompatibility_notice_dismiss_action(){
	$active = get_option('active_plugins');
	$incompatible = siteorigin_panels_get_incompatible_plugins();
	$active_incompatible = array_intersect($active, array_keys($incompatible) );

	// Add the option for these dismissed incompatible plugins
	$current = get_option('siteorigin_panels_incompatible_dismissed');
	if($current === false) {
		add_option('siteorigin_panels_incompatible_dismissed', $active_incompatible, '', 'no');
	}
	else {
		update_option('siteorigin_panels_incompatible_dismissed', $active_incompatible);
	}

	exit();
}
add_action('wp_ajax_siteorigin_panels_incompatible_notice_dismiss', 'siteorigin_panels_incompatibility_notice_dismiss_action');