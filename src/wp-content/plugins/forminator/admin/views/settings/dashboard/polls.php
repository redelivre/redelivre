<?php
$dashboard_settings = forminator_get_dashboard_settings( 'polls', array() );
$num_recent         = isset( $dashboard_settings['num_recent'] ) ? $dashboard_settings['num_recent'] : 5;
$published          = isset( $dashboard_settings['published'] ) ? filter_var( $dashboard_settings['published'], FILTER_VALIDATE_BOOLEAN ) : true;
$draft              = isset( $dashboard_settings['draft'] ) ? filter_var( $dashboard_settings['draft'], FILTER_VALIDATE_BOOLEAN ) : true;
?>

<div class="sui-form-field">

	<label for="listings-polls-limit" id="listings-polls-limit-label" class="sui-settings-label"><?php esc_html_e( 'Number of Polls', Forminator::DOMAIN ); ?></label>

	<span id="listings-polls-limit-message" class="sui-description" style="margin-bottom: 10px;"><?php esc_html_e( 'Choose the number of recent polls to be shown on your dashboard.', Forminator::DOMAIN ); ?></span>

	<input
		type="number"
		name="num_recent[polls]"
		min="0"
		value="<?php echo esc_attr( $num_recent ); ?>"
		placeholder="0"
		class="sui-form-control sui-input-sm"
		style="max-width: 100px;"
		aria-labelledby="listings-polls-limit-label"
		aria-describedby="listings-polls-limit-message"
	/>

	<span class="sui-error-message" style="display: none;"><?php esc_html_e( "This field shouldn't be empty.", Forminator::DOMAIN ); ?></span>

</div>

<div class="sui-form-field">

	<label id="listings-polls-status-label" class="sui-settings-label"><?php esc_html_e( 'Status', Forminator::DOMAIN ); ?></label>

	<span id="listings-polls-status-message" class="sui-description" style="margin-bottom: 10px;"><?php esc_html_e( 'By default, we display polls with any status. However, you can display polls having a specific status only on the dashboard.', Forminator::DOMAIN ); ?></span>

	<label for="forminator-polls-status-published" class="sui-checkbox sui-checkbox-sm sui-checkbox-stacked">
		<input
			type="checkbox"
			name="published[polls]"
			value="true"
			id="forminator-polls-status-published"
			aria-labelledby="listings-polls-status-label listings-polls-status-published"
			aria-describedby="listings-polls-status-message"
			<?php echo checked( $published ); ?>
		/>
		<span aria-hidden="true"></span>
		<span id="listings-polls-status-published"><?php esc_html_e( 'Published', Forminator::DOMAIN ); ?></span>
	</label>

	<label for="forminator-polls-status-drafts" class="sui-checkbox sui-checkbox-sm sui-checkbox-stacked">
		<input
			type="checkbox"
			name="draft[polls]"
			value="true"
			id="forminator-polls-status-drafts"
			aria-labelledby="listings-polls-status-label listings-polls-status-drafts"
			aria-describedby="listings-polls-status-message"
			<?php echo checked( $draft ); ?>
		/>
		<span aria-hidden="true"></span>
		<span id="listings-polls-status-drafts"><?php esc_html_e( 'Drafts', Forminator::DOMAIN ); ?></span>
	</label>

</div>
