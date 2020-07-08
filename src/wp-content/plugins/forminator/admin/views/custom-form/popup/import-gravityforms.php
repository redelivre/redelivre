<?php
$nonce = wp_create_nonce( 'forminator_save_import_custom_form_gravity' );
$forms = forminator_list_thirdparty_contact_forms( 'gravityforms' );
?>

<div class="sui-box-body wpmudev-popup-form">

	<div class="sui-notice sui-notice-error wpmudev-ajax-error-placeholder sui-hidden"><p></p></div>

	<div class="sui-form-field">
		<select class="sui-form-dropdown" name="gravityforms">
			<option value="all"><?php esc_html_e('All Forms', Forminator::DOMAIN); ?></option>
			<?php
			if( ! empty( $forms ) ):
				foreach ($forms as $key => $value) {
					echo sprintf('<option value="%f">%s</option>', 
						absint( $value['id'] ), 
						esc_html( $value['title'] ) 
					);
				}
			endif;

			?>
		</select>

		<span class="sui-description"><?php esc_html_e( 'Select the form.', Forminator::DOMAIN ); ?></span>

	</div>

</div>

<div class="sui-box-footer">

	<button class="sui-button forminator-popup-cancel" data-a11y-dialog-hide="forminator-popup">
		<span class="sui-loading-text"><?php esc_html_e( 'Cancel', Forminator::DOMAIN ); ?></span>
		<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
	</button>

	<div class="sui-actions-right">

		<button class="sui-button sui-button-primary wpmudev-action-ajax-done" data-nonce="<?php echo esc_attr( $nonce ); ?>">
			<span class="sui-loading-text"><?php esc_html_e( 'Import', Forminator::DOMAIN ); ?></span>
			<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
		</button>

	</div>

</div>
