<?php
// defaults
$vars = array(
	'message'       => '',
	'message_error' => '',
	'error_message' => '',
	'multi_id'      => '',
	'tags'          => array(),
);
/** @var array $template_vars */
foreach ( $template_vars as $key => $val ) {
	$vars[ $key ] = $val;
}

?>
<div class="integration-header">
	<h3 class="sui-box-title" id="dialogTitle2"><?php echo esc_html( __( 'Setup Message', Forminator::DOMAIN ) ); ?></h3>
	<p><?php esc_html_e( 'Configure message to be sent.', Forminator::DOMAIN ); ?></p>
	<?php if ( ! empty( $vars['error_message'] ) ) : ?>
		<span class="sui-notice sui-notice-error"><p><?php echo esc_html( $vars['error_message'] ); ?></p></span>
	<?php endif; ?>
</div>

<form>
	<div class="sui-form-field <?php echo esc_attr( ! empty( $vars['message_error'] ) ? 'sui-form-field-error' : '' ); ?>">

		<label class="sui-label"><?php esc_html_e( 'Message', Forminator::DOMAIN ); ?></label>

		<div class="sui-insert-variables">

			<textarea id="slack_message"
			          class="sui-form-control"
			          name="message"
			          placeholder="<?php echo esc_attr( __( 'Message', Forminator::DOMAIN ) ); ?>"><?php echo esc_html( $vars['message'] ); ?></textarea>

			<select data-textarea-id="slack_message">
				<?php foreach ( $vars['tags'] as $short_tag => $label ) : ?>
					<option value="{<?php echo esc_attr( $short_tag ); ?>}"
					        data-content="{<?php echo esc_attr( $short_tag ); ?>}"><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?></select>

		</div>

		<?php if ( ! empty( $vars['message_error'] ) ) : ?>
			<span class="sui-error-message"><?php echo esc_html( $vars['message_error'] ); ?></span>
		<?php endif; ?>
		<span class="sui-description">
			<?php esc_html_e( 'You can format your message using Slack Flavored Markdown, find more information ', Forminator::DOMAIN ); ?>
			<a href="https://get.slack.help/hc/en-us/articles/202288908-how-can-i-add-formatting-to-my-messages" target="_blank"><?php esc_html_e( 'here.', Forminator::DOMAIN ); ?></a>.
		</span>
		<span class="sui-description">
			<?php esc_html_e(
				'By default sent message will include Quiz Answer and Quiz Result as attachment using Forminator Format to ease you up, more information about attachment can be found ',
				Forminator::DOMAIN
			); ?>
			<a href="https://api.slack.com/docs/message-attachments" target="_blank"><?php esc_html_e( 'here', Forminator::DOMAIN ); ?></a>
		</span>

	</div>

	<input type="hidden" name="multi_id" value="<?php echo esc_attr( $vars['multi_id'] ); ?>">
</form>
