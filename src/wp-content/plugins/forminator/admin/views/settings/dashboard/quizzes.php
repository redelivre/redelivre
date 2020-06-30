<?php
$dashboard_settings = forminator_get_dashboard_settings( 'quizzes', array() );
$num_recent         = isset( $dashboard_settings['num_recent'] ) ? $dashboard_settings['num_recent'] : 5;
$published          = isset( $dashboard_settings['published'] ) ? filter_var( $dashboard_settings['published'], FILTER_VALIDATE_BOOLEAN ) : true;
$draft              = isset( $dashboard_settings['draft'] ) ? filter_var( $dashboard_settings['draft'], FILTER_VALIDATE_BOOLEAN ) : true;
?>

<div class="sui-form-field">

	<label for="listings-quizzes-limit" id="listings-quizzes-limit-label" class="sui-settings-label"><?php esc_html_e( 'Number of Quizzes', Forminator::DOMAIN ); ?></label>

	<span id="listings-quizzes-limit-message" class="sui-description" style="margin-bottom: 10px;"><?php esc_html_e( 'Choose the number of recent quizzes to be shown on your dashboard.', Forminator::DOMAIN ); ?></span>

	<input
		type="number"
		min="0"
		name="num_recent[quizzes]"
		value="<?php echo esc_attr( $num_recent ); ?>"
		placeholder="0"
		id="listings-quizzes-limit"
		class="sui-form-control sui-input-sm"
		style="max-width: 100px;"
		aria-labelledby="listings-quizzes-limit-label"
		aria-describedby="listings-quizzes-limit-message"
	/>

	<span class="sui-error-message" style="display: none;"><?php esc_html_e( "This field shouldn't be empty.", Forminator::DOMAIN ); ?></span>

</div>

<div class="sui-form-field">

	<label for="forminator-quizzes-status-published" id="listings-quizzes-status-label" class="sui-settings-label"><?php esc_html_e( 'Status', Forminator::DOMAIN ); ?></label>

	<span id="listings-quizzes-status-message" class="sui-description" style="margin-bottom: 10px;"><?php esc_html_e( 'By default, we display quizzes with any status. However, you can display quizzes having a specific status only on the dashboard.', Forminator::DOMAIN ); ?></span>

	<label for="forminator-quizzes-status-published" class="sui-checkbox sui-checkbox-sm sui-checkbox-stacked">
		<input
			type="checkbox"
			name="published[quizzes]"
			value="true"
			id="forminator-quizzes-status-published"
			<?php echo checked( $published ); ?>
			aria-labelledby="listings-quizzes-status-label listings-quizzes-status-published"
			aria-describedby="listings-quizzes-status-message"
		/>
		<span aria-hidden="true"></span>
		<span id="listings-quizzes-status-published"><?php esc_html_e( 'Published', Forminator::DOMAIN ); ?></span>
	</label>

	<label for="forminator-quizzes-status-drafts" class="sui-checkbox sui-checkbox-sm sui-checkbox-stacked">
		<input
			type="checkbox"
			name="draft[quizzes]"
			value="true"
			id="forminator-quizzes-status-drafts"
			<?php echo checked( $draft ); ?>
			aria-labelledby="listings-quizzes-status-label listings-quizzes-status-drafts"
			aria-describedby="listings-quizzes-status-message"
		/>
		<span aria-hidden="true"></span>
		<span id="listings-quizzes-status-drafts"><?php esc_html_e( 'Drafts', Forminator::DOMAIN ); ?></span>
	</label>

</div>
