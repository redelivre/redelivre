<?php
// defaults
$vars = array(
	'card_name'              => '',
	'card_name_error'        => '',
	'card_description'       => '',
	'card_description_error' => '',
	'position'               => '',
	'position_error'         => '',
	'positions'              => array(),
	'label_ids'              => array(),
	'label_ids_error'        => '',
	'labels'                 => array(),
	'member_ids'             => array(),
	'member_ids_error'       => '',
	'members'                => array(),
	'name_fields'            => array(),
	'desc_fields'            => array(),
	'error_message'          => '',
	'list_name'              => '',
);
/** @var array $template_vars */
foreach ( $template_vars as $key => $val ) {
	$vars[ $key ] = $val;
}
?>

<div class="integration-header">

	<h3 class="sui-box-title" id="dialogTitle2"><?php echo esc_html( __( 'Customize Fields', Forminator::DOMAIN ) ); ?></h3>

	<p><?php esc_html_e( 'Set up how you want your cards to be created in Trello.', Forminator::DOMAIN ); ?></p>

	<?php if ( ! empty( $vars['error_message'] ) ) : ?>
		<span class="sui-notice sui-notice-error"><p><?php echo esc_html( $vars['error_message'] ); ?></p></span>
	<?php endif; ?>

</div>

<form>

	<div id="trello-card-name-settings" class="sui-row">

		<div class="sui-col-12">

			<div class="sui-form-field <?php echo esc_attr( ! empty( $vars['card_name_error'] ) ? 'sui-form-field-error' : '' ); ?>">

				<label class="sui-label"><?php esc_html_e( 'Card Name', Forminator::DOMAIN ); ?></label>

				<div class="sui-insert-variables">

					<input id="card_name"
					       class="sui-form-control"
					       name="card_name"
					       placeholder="<?php echo esc_attr( __( 'Card Name', Forminator::DOMAIN ) ); ?>"
					       value="<?php echo esc_attr( $vars['card_name'] ); ?>">

					<select data-textarea-id="card_name"><?php foreach ( $vars['name_fields'] as $key => $field ) : ?>
							<option value="{<?php echo esc_attr( $key ); ?>}" data-content="{<?php echo esc_attr( $key ); ?>}"><?php echo esc_html( $field ); ?></option>
						<?php endforeach; ?></select>

				</div>

				<?php if ( ! empty( $vars['card_name_error'] ) ) : ?>
					<span class="sui-error-message"><?php echo esc_html( $vars['card_name_error'] ); ?></span>
				<?php endif; ?>

			</div>

		</div>

	</div>

	<div id="trello-card-description-settings" class="sui-row">

		<div class="sui-col-12">

			<div class="sui-form-field <?php echo esc_attr( ! empty( $vars['card_description_error'] ) ? 'sui-form-field-error' : '' ); ?>">

				<label class="sui-label" for="card_description"><?php esc_html_e( 'Card Description', Forminator::DOMAIN ); ?></label>

				<div class="sui-insert-variables">

					<textarea class="sui-form-control" name="card_description" id="card_description" rows="7"><?php echo esc_html( $vars['card_description'] ); ?></textarea>

					<select data-textarea-id="card_description"><?php foreach ( $vars['desc_fields'] as $key => $field ) : ?>
							<option value="{<?php echo esc_attr( $key ); ?>}" data-content="{<?php echo esc_attr( $key ); ?>}"><?php echo esc_html( $field ); ?></option>
						<?php endforeach; ?></select>

				</div>

				<?php if ( ! empty( $vars['card_description_error'] ) ) : ?>
					<span class="sui-error-message"><?php echo esc_html( $vars['card_description_error'] ); ?></span>
				<?php endif; ?>

				<span class="sui-description">
					<?php esc_html_e( 'Markdown supported for card description. Find complete guide', Forminator::DOMAIN ); ?>
					<a href="https://help.trello.com/article/821-using-markdown-in-trello" target="_blank"><?php esc_html_e( 'here', Forminator::DOMAIN ); ?></a>.
				</span>

			</div>

		</div>

	</div>

	<div class="sui-row">
		<div class="sui-col-md-4">
			<div class="sui-form-field <?php echo esc_attr( ! empty( $vars['position_error'] ) ? 'sui-form-field-error' : '' ); ?>">
				<label class="sui-label" for="position"><?php esc_html_e( 'Position', Forminator::DOMAIN ); ?></label>
				<select name="position" id="position">
					<?php foreach ( $vars['positions'] as $pos_id => $pos_name ) : ?>
						<option value="<?php echo esc_attr( $pos_id ); ?>" <?php selected( $vars['position'], $pos_id ); ?>><?php echo esc_html( $pos_name ); ?></option>
					<?php endforeach; ?>
				</select>
				<?php if ( ! empty( $vars['position_error'] ) ) : ?>
					<span class="sui-error-message"><?php echo esc_html( $vars['position_error'] ); ?></span>
				<?php endif; ?>

			</div>
		</div>

		<div class="sui-col-md-8">
			<div class="sui-form-field <?php echo esc_attr( ! empty( $vars['label_ids_error'] ) ? 'sui-form-field-error' : '' ); ?>">
				<label class="sui-label" for="label_ids"><?php esc_html_e( 'Labels', Forminator::DOMAIN ); ?></label>
				<select class="sui-select fui-multi-select" name="label_ids[]" id="label_ids"
				        multiple="multiple"
				        data-reorder="1"
				        data-tags="false"
				        data-token-separators="[',']"
				        data-placeholder="<?php esc_html_e( 'Enter label name', Forminator::DOMAIN ); ?>"
				        data-allow-clear="false">
					<?php foreach ( $vars['label_ids'] as $label_id ) : ?>
						<?php if ( isset( $vars['labels'][ $label_id ] ) ): ?>
							<option
									value="<?php echo esc_attr( $label_id ); ?>"
									selected="selected"
									data-color="<?php echo esc_attr( $vars['labels'][ $label_id ]['color'] ); ?>"
									style="background-color: <?php echo esc_attr( $vars['labels'][ $label_id ]['color'] ); ?>;"
							><?php echo esc_html( $vars['labels'][ $label_id ]['name'] ); ?></option>
						<?php endif; ?>
					<?php endforeach; ?>

					<?php foreach ( $vars['labels'] as $label_id => $label ) : ?>
						<?php if ( ! in_array( $label_id, $vars['label_ids'], true ) ): ?>
							<option
									value="<?php echo esc_attr( $label_id ); ?>"
									data-color="<?php echo esc_attr( $label['color'] ); ?>"
									style="background-color: <?php echo esc_attr( $vars['labels'][ $label_id ]['color'] ); ?>;"
							><?php echo esc_html( $label['name'] ); ?></option>
						<?php endif; ?>
					<?php endforeach; ?>
				</select>
				<?php if ( ! empty( $vars['label_ids_error'] ) ) : ?>
					<span class="sui-error-message"><?php echo esc_html( $vars['label_ids_error'] ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="sui-row">
		<div class="sui-col-md-12">
			<div class="sui-form-field <?php echo esc_attr( ! empty( $vars['member_ids_error'] ) ? 'sui-form-field-error' : '' ); ?>">
				<label class="sui-label" for="member_ids"><?php esc_html_e( 'Members', Forminator::DOMAIN ); ?></label>
				<select class="sui-select fui-multi-select" name="member_ids[]" id="member_ids"
				        multiple="multiple"
				        data-reorder="1"
				        data-tags="false"
				        data-token-separators="[',']"
				        data-placeholder="<?php esc_html_e( 'Enter member name', Forminator::DOMAIN ); ?>"
				        data-allow-clear="false">
					<?php foreach ( $vars['member_ids'] as $member_id ) : ?>
						<?php if ( isset( $vars['members'][ $member_id ] ) ): ?>
							<option
									value="<?php echo esc_attr( $member_id ); ?>"
									selected="selected"
							><?php echo esc_html( $vars['members'][ $member_id ] ); ?></option>
						<?php endif; ?>
					<?php endforeach; ?>

					<?php foreach ( $vars['members'] as $member_id => $name ) : ?>
						<?php if ( ! in_array( $member_id, $vars['member_ids'], true ) ): ?>
							<option
									value="<?php echo esc_attr( $member_id ); ?>"
							><?php echo esc_html( $name ); ?></option>
						<?php endif; ?>
					<?php endforeach; ?>
				</select>
				<?php if ( ! empty( $vars['member_ids_error'] ) ) : ?>
					<span class="sui-error-message"><?php echo esc_html( $vars['member_ids_error'] ); ?></span>
				<?php endif; ?>
			</div>


		</div>
	</div>

	<input type="hidden" name="multi_id" value="<?php echo esc_attr( $vars['multi_id'] ); ?>">
</form>
