<?php
// defaults
$vars = array(
	'error_message' => '',
	'name'          => '',
	'name_error'    => '',
	'multi_id'      => '',
	'fields'        => array(),
	'form_fields'   => array(),
	'email_fields'  => array(),
	'lists'         => array(),
);
/** @var array $template_vars */

foreach ( $template_vars as $key => $val ) {
	$vars[ $key ] = $val;
}

$list_selector_class = 'sui-select';

if ( empty( $vars['lists'] ) ) {
	$list_selector_class = 'fui-select-disabled';
}

?>
<div class="integration-header">

	<h3 id="dialogTitle2" class="sui-box-title"><?php echo esc_html( __( 'Create Contact', Forminator::DOMAIN ) ); ?></h3>

	<p class="sui-description" style="max-width: 400px; margin: 20px auto 0; line-height: 22px;"><?php esc_html_e( 'Let\'s start with choosing a HubSpot list and matching up your form fields with the default HubSpot contact fields to make sure we’re sending data to the right place.', Forminator::DOMAIN ); ?></p>

	<?php if ( ! empty( $vars['error_message'] ) ) : ?>
		<span class="sui-notice sui-notice-error"><p><?php echo esc_html( $vars['error_message'] ); ?></p></span>
	<?php endif; ?>

</div>

<form style="display: block; margin-top: -10px;">

	<div tabindex="0" role="group" class="sui-form-field<?php echo esc_attr( ! empty( $vars['list_id_error'] ) ? ' sui-form-field-error' : '' ); ?>"<?php echo empty( $vars['lists'] ) ? ' disabled="disabled"' : ''; ?>>

		<label for="hubspot-list-id" id="hubspot-list-id-label" class="sui-label">
			<?php esc_html_e( 'HubSpot List (optional)', Forminator::DOMAIN ); ?>
			<span class="sui-label-note"><?php esc_html_e( 'Static list only', Forminator::DOMAIN ); ?></span>
		</label>

		<select id="hubspot-list-id" class="<?php echo esc_attr( $list_selector_class ); ?>" aria-labelledby="hubspot-list-id-label" aria-describedby="hubspot-list-id-desc hubspot-list-id-error" name="list_id">

			<?php
			// Select a list.
			if ( ! empty( $vars['lists'] ) ) { ?>

				<option value=""><?php esc_html_e( 'Select a list', Forminator::DOMAIN ); ?></option>

				<?php
				foreach ( $vars['lists'] as $list_id => $list_name ) : ?>
					<option value="<?php echo esc_attr( $list_id ); ?>" <?php selected( $vars['list_id'], $list_id ); ?>><?php echo esc_html( $list_name ); ?></option>
				<?php
				endforeach; ?>

			<?php
			// Empty notice.
			} else { ?>
				<option value=""><?php esc_html_e( 'No static lists found on your HubSpot account', Forminator::DOMAIN ); ?></option>
			<?php } ?>

		</select>

		<span id="hubspot-list-id-error" class="sui-error-message">
			<?php
			if ( ! empty( $vars['list_id_error'] ) ) :
				echo esc_html( $vars['list_id_error'] );
			endif;
			?>
		</span>

		<span id="hubspot-list-id-desc" class="sui-description"><?php esc_html_e( 'You can optionally add the contact to a static HubSpot list. Leave it empty to create a contact without adding it to a list.', Forminator::DOMAIN ); ?></span>

	</div>

	<div tabindex="0" role="group" class="sui-form-field" style="margin-bottom: 0;">

		<label for="hubspot-list-id" id="hubspot-list-id-label" class="sui-label"><?php esc_html_e( 'Field Mapping', Forminator::DOMAIN ); ?></label>

		<table class="sui-table" style="margin-top: 5px; margin-bottom: 0;">

			<thead>

				<tr>
					<th><?php esc_html_e( 'HubSpot Fields', Forminator::DOMAIN ); ?></th>
					<th><?php esc_html_e( 'Forminator Fields', Forminator::DOMAIN ); ?></th>
				</tr>

			</thead>

			<tbody>

			<?php
			if ( ! empty( $vars['fields'] ) ) :

				foreach ( $vars['fields'] as $key => $field_title ) :
					?>

					<tr>

						<td>
							<?php echo esc_html( $field_title ); ?>
							<?php if ( 'email' === $key ) : ?>
								<span class="integrations-required-field">*</span>
							<?php endif; ?>
						</td>

						<td>
							<?php
							$forminator_fields = $vars['form_fields'];
							if ( 'email' === $key ) {
								$forminator_fields = $vars['email_fields'];
							}
							$current_error    = '';
							$current_selected = '';
							if ( isset( $vars[ $key . '_error' ] ) && ! empty( $vars[ $key . '_error' ] ) ) {
								$current_error = $vars[ $key . '_error' ];
							}
							if ( isset( $vars['fields_map'][ $key ] ) && ! empty( $vars['fields_map'][ $key ] ) ) {
								$current_selected = $vars['fields_map'][ $key ];
							}
							?>
							<div class="sui-form-field <?php echo esc_attr( ! empty( $current_error ) ? 'sui-form-field-error' : '' ); ?>"<?php echo ( ! empty( $current_error ) ) ? ' style="padding-top: 5px;"' : ''; ?>>
								<select class="sui-select" name="fields_map[<?php echo esc_attr( $key ); ?>]">
									<option value=""><?php esc_html_e( 'None', Forminator::DOMAIN ); ?></option>
									<?php
									if ( ! empty( $forminator_fields ) ) :
										foreach ( $forminator_fields as $forminator_field ) :
											?>
											<option value="<?php echo esc_attr( $forminator_field['element_id'] ); ?>"
												<?php selected( $current_selected, $forminator_field['element_id'] ); ?>>
												<?php echo esc_html( $forminator_field['field_label'] . ' | ' . $forminator_field['element_id'] ); ?>
											</option>
											<?php
										endforeach;
									endif;
									?>
								</select>
								<?php if ( ! empty( $current_error ) ) : ?>
									<span class="sui-error-message"
										style="margin-top: 5px; margin-bottom: 5px;"><?php echo esc_html( $current_error ); ?></span>
								<?php endif; ?>
							</div>
						</td>

					<tr>

				<?php endforeach;

			endif;
			?>

			</tbody>

		</table>
	</div>

	<input type="hidden" name="multi_id" value="<?php echo esc_attr( $vars['multi_id'] ); ?>" />

</form>
