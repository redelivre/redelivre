<?php
$sender_email_address = get_global_sender_email_address();
$sender_name          = get_global_sender_name();
?>

<div class="sui-box-settings-row">

	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'From Headers', Forminator::DOMAIN ); ?></span>
		<span class="sui-description"><?php esc_html_e( 'Choose the default sender name and sender email address for all of your outgoing emails from Forminator.', Forminator::DOMAIN ); ?></span>
	</div>

	<div class="sui-box-settings-col-2">

		<div class="sui-form-field">

			<label for="forminator-settings--sender-email"
				class="sui-label"><?php esc_html_e( 'Sender email address', Forminator::DOMAIN ); ?></label>
			<input type="email"
				name="sender_email"
				placeholder="<?php esc_html_e( 'Enter email', Forminator::DOMAIN ); ?>"
				value="<?php echo esc_html( $sender_email_address ); ?>"
				id="forminator-settings--sender-email"
				class="sui-form-control forminator-required"/>
			<span class="sui-error-message"
				style="display: none;"><?php esc_html_e( 'Please, enter a valid email address.', Forminator::DOMAIN ); ?></span>

		</div>

		<div class="sui-form-field">

			<label for="forminator-settings--sender-name"
				class="sui-label"><?php esc_html_e( 'Sender name', Forminator::DOMAIN ); ?></label>
			<input type="text"
				name="sender_name"
				placeholder="<?php esc_html_e( 'Enter name', Forminator::DOMAIN ); ?>"
				value="<?php echo esc_html( $sender_name ); ?>"
				id="forminator-settings--sender-name"
				class="sui-form-control forminator-required"/>
			<span class="sui-error-message"
				style="display: none;"><?php esc_html_e( 'The sender email cannot be empty.', Forminator::DOMAIN ); ?></span>

		</div>

	</div>

</div>
