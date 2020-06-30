<?php
// Defaults
$vars = array(
	'error_message'                                   => '',
	'multi_id'                                        => '',
	'resubscribe'                                     => 0,
	'resubscribe_error'                               => '',
	'restart_subscription_based_autoresponders'       => 0,
	'restart_subscription_based_autoresponders_error' => '',
	'consent_to_track'                                => 'Unchanged',
	'consent_to_track_error'                          => '',
);

/** @var array $template_vars */
foreach ( $template_vars as $key => $val ) {
	$vars[ $key ] = $val;
} ?>

<div class="integration-header">

	<h3 class="sui-box-title" id="dialogTitle2"><?php echo esc_html( __( 'Additional Options', Forminator::DOMAIN ) ); ?></h3>

	<span class="sui-description" style="margin-top: 20px;"><?php esc_html_e( 'Configure additional options for Campaign Monitor integration.', Forminator::DOMAIN ); ?></span>

	<?php if ( ! empty( $vars['error_message'] ) ) : ?>
		<div class="sui-notice sui-notice-error">
			<p><?php echo esc_html( $vars['error_message'] ); ?></p>
		</div>
	<?php endif; ?>

</div>

<form>

	<div class="sui-form-field<?php echo esc_attr( ! empty( $vars['resubscribe_error'] ) ? ' sui-form-field-error' : '' ); ?>">

		<label class="sui-toggle">

			<input type="checkbox"
				name="resubscribe"
				value="1"
				id="resubscribe"
				<?php checked( true, $vars['resubscribe'] ); ?> />

			<span class="sui-toggle-slider" aria-hidden="true"></span>

		</label>

		<label for="resubscribe" class="sui-toggle-label"><?php esc_html_e( 'Re-subscribe', Forminator::DOMAIN ); ?></label>

		<?php if ( ! empty( $vars['resubscribe_error'] ) ) : ?>
			<span class="sui-error-message"><?php echo esc_html( $vars['resubscribe_error'] ); ?></span>
		<?php endif; ?>

		<span class="sui-description"><?php esc_html_e( 'If the subscriber is in an inactive state or has previously been unsubscribed or added to the suppression list and you enabled Re-subscribe, they will be re-added to the list. Therefore, this options should be used with caution and only where suitable. If Re-subscribe is disabled, the subscriber will not be re-added to the active list.', Forminator::DOMAIN ); ?></span>

	</div>

	<div class="sui-form-field<?php echo esc_attr( ! empty( $vars['restart_subscription_based_autoresponders_error'] ) ? ' sui-form-field-error' : '' ); ?>">

		<label class="sui-toggle">

			<input type="checkbox"
				name="restart_subscription_based_autoresponders"
				value="1"
				id="restart_subscription_based_autoresponders"
				<?php checked( true, $vars['restart_subscription_based_autoresponders'] ); ?> />

			<span class="sui-toggle-slider" aria-hidden="true"></span>

		</label>

		<label class="sui-toggle-label" for="restart_subscription_based_autoresponders"><?php esc_html_e( 'Restart Subscription based Autoresponders', Forminator::DOMAIN ); ?></label>

		<?php if ( ! empty( $vars['restart_subscription_based_autoresponders_error'] ) ) : ?>
			<span class="sui-error-message"><?php echo esc_html( $vars['restart_subscription_based_autoresponders_error'] ); ?></span>
		<?php endif; ?>

		<span class="sui-description"><?php esc_html_e( 'By default, resubscribed subscribers will not restart any automated workflows, but they will receive any remaining emails. However, if you enable the Restart Subscription based Autoresponders, any sequences will be restarted. Restart Subscription based Autoresponders only affects resubscribing subscribers.', Forminator::DOMAIN ); ?></span>

	</div>

	<div class="sui-form-field<?php echo esc_attr( ! empty( $vars['consent_to_track_error'] ) ? ' sui-form-field-error' : '' ); ?>">

		<label class="sui-label" for="consent_to_track"><?php esc_html_e( 'Consent to Track', Forminator::DOMAIN ); ?></label>
		
		<select name="consent_to_track" id="consent_to_track" class="sui-select sui-form-control">
			<option value="Unchanged" <?php selected( 'Unchanged', $vars['consent_to_track'] ); ?>>Unchanged</option>
			<option value="Yes" <?php selected( 'Yes', $vars['consent_to_track'] ); ?>>Yes</option>
			<option value="No" <?php selected( 'No', $vars['consent_to_track'] ); ?>>No</option>
		</select>

		<?php if ( ! empty( $vars['consent_to_track_error'] ) ) : ?>
			<span class="sui-error-message"><?php echo esc_html( $vars['consent_to_track_error'] ); ?></span>
		<?php endif; ?>

		<span class="sui-description"><?php esc_html_e( 'Whether or not the subscriber has consented to having their email opens and clicks tracked. This value applies to all subscribers with the same email address, within the same client. If an email address has no value stored for Consent to Track, it is assumed that the subscriber has given consent. You can find more information ', Forminator::DOMAIN ); ?><a href="https://help.campaignmonitor.com/gdpr-faqs" target="_blank">here</a>.
		</span>
	</div>

	<input type="hidden"
		name="multi_id"
		value="<?php echo esc_attr( $vars['multi_id'] ); ?>" />

</form>
