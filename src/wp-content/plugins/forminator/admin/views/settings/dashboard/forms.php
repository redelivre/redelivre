<?php
$dashboard_settings = forminator_get_dashboard_settings( 'forms', array() );
$num_recent         = isset( $dashboard_settings['num_recent'] ) ? $dashboard_settings['num_recent'] : 5;
$published          = isset( $dashboard_settings['published'] ) ? filter_var( $dashboard_settings['published'], FILTER_VALIDATE_BOOLEAN ) : true;
$draft              = isset( $dashboard_settings['draft'] ) ? filter_var( $dashboard_settings['draft'], FILTER_VALIDATE_BOOLEAN ) : true;
?>

<div class="sui-form-field">

	<label for="listings-forms-limit" id="listings-forms-limit-label" class="sui-settings-label"><?php esc_html_e( 'Number of Forms', Forminator::DOMAIN ); ?></label>

	<span id="listings-forms-limit-message" class="sui-description" style="margin-bottom: 10px;"><?php esc_html_e( 'Choose the number of recent forms to be shown on your dashboard.', Forminator::DOMAIN ); ?></span>

	<input
		type="number"
		min="0"
		value="<?php echo esc_attr( $num_recent ); ?>"
		placeholder="0"
		name="num_recent[forms]"
		id="listings-forms-limit"
		class="sui-form-control sui-input-sm"
		style="max-width: 100px;"
		aria-labelledby="listings-forms-limit-label"
		aria-describedby="listings-forms-limit-message"
		aria-required="true"
	/>

	<span class="sui-error-message" style="display: none;"><?php esc_html_e( "This field shouldn't be empty.", Forminator::DOMAIN ); ?></span>

</div>

<div class="sui-form-field">

	<label id="listings-forms-status-label" class="sui-settings-label"><?php esc_html_e( 'Status', Forminator::DOMAIN ); ?></label>

	<span id="listings-forms-status-message" class="sui-description" style="margin-bottom: 10px;"><?php esc_html_e( 'By default, we display forms with any status. However, you can display forms having a specific status only on the dashboard.', Forminator::DOMAIN ); ?></span>

	<label for="forminator-forms-status-published" class="sui-checkbox sui-checkbox-sm sui-checkbox-stacked">
		<input
			type="checkbox"
			name="published[forms]"
			value="true"
			id="forminator-forms-status-published"
			aria-labelledby="listings-forms-status-label listings-forms-status-published"
			aria-describedby="listings-forms-status-message"
			<?php echo checked( $published ); ?>
		/>
		<span aria-hidden="true"></span>
		<span id="listings-forms-status-published"><?php esc_html_e( 'Published', Forminator::DOMAIN ); ?></span>
	</label>

	<label for="forminator-forms-status-drafts" class="sui-checkbox sui-checkbox-sm sui-checkbox-stacked">
		<input
			type="checkbox"
			name="draft[forms]"
			value="true"
			id="forminator-forms-status-drafts"
			aria-labelledby="listings-forms-status-label listings-forms-status-drafts"
			aria-describedby="listings-forms-status-message"
			<?php echo checked( $draft ); ?>
		/>
		<span aria-hidden="true"></span>
		<span id="listings-forms-status-drafts"><?php esc_html_e( 'Drafts', Forminator::DOMAIN ); ?></span>
	</label>

</div>
