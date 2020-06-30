<?php
// defaults
$vars = array(
	'error_message'     => '',
	'test_key'          => '',
	'test_key_error'    => '',
	'test_secret'       => '',
	'test_secret_error' => '',
	'live_key'          => '',
	'live_key_error'    => '',
	'live_secret'       => '',
	'live_secret_error' => '',

);
/** @var array $template_vars */
foreach ( $template_vars as $key => $val ) {
	$vars[ $key ] = $val;
}
?>

<span class="sui-description"><?php printf( esc_html__( 'Enter your Stripe API keys below to connect your account. You can grab your API keys from %1$shere%2$s.' ), '<a href="https://dashboard.stripe.com/account/apikeys" target="_blank">', '</a>' ); ?></span>

<?php if ( ! empty( $vars['error_message'] ) ) : ?>

	<div class="sui-notice sui-notice-error">
		<p><?php echo esc_html( $vars['error_message'] ); ?></p>
	</div>

<?php endif; ?>

<form class="sui-form-field">

	<div class="sui-form-field <?php echo esc_attr( ! empty( $vars['test_key_error'] ) ? 'sui-form-field-error' : '' ); ?>">

		<label class="sui-label"><?php esc_html_e( 'Test Publishable Key', Forminator::DOMAIN ); ?></label>

		<input
			class="sui-form-control"
			name="test_key" placeholder="<?php echo esc_attr( __( 'Enter your test publishable key', Forminator::DOMAIN ) ); ?>"
			value="<?php echo esc_attr( $vars['test_key'] ); ?>"
		/>
		<?php if ( ! empty( $vars['test_key_error'] ) ) : ?>
			<span class="sui-error-message"><?php echo esc_html( $vars['test_key_error'] ); ?></span>
		<?php endif; ?>

	</div>

	<div class="sui-form-field <?php echo esc_attr( ! empty( $vars['test_secret_error'] ) ? 'sui-form-field-error' : '' ); ?>">

		<label class="sui-label"><?php esc_html_e( 'Test Secret Key', Forminator::DOMAIN ); ?></label>

		<input
			class="sui-form-control"
			name="test_secret" placeholder="<?php echo esc_attr( __( 'Enter your test secret key', Forminator::DOMAIN ) ); ?>"
			value="<?php echo esc_attr( $vars['test_secret'] ); ?>"
		/>

		<?php if ( ! empty( $vars['test_secret_error'] ) ) : ?>
			<span class="sui-error-message"><?php echo esc_html( $vars['test_secret_error'] ); ?></span>
		<?php endif; ?>

	</div>

	<div class="sui-form-field <?php echo esc_attr( ! empty( $vars['live_key_error'] ) ? 'sui-form-field-error' : '' ); ?>">

		<label class="sui-label"><?php esc_html_e( 'Live Publishable Key', Forminator::DOMAIN ); ?></label>

		<input
			class="sui-form-control"
			name="live_key" placeholder="<?php echo esc_attr( __( 'Enter your live publishable key', Forminator::DOMAIN ) ); ?>"
			value="<?php echo esc_attr( $vars['live_key'] ); ?>"
		/>

		<?php if ( ! empty( $vars['live_key_error'] ) ) : ?>
			<span class="sui-error-message"><?php echo esc_html( $vars['live_key_error'] ); ?></span>
		<?php endif; ?>

	</div>

	<div class="sui-form-field <?php echo esc_attr( ! empty( $vars['live_secret_error'] ) ? 'sui-form-field-error' : '' ); ?>">

		<label class="sui-label"><?php esc_html_e( 'Live Secret Key', Forminator::DOMAIN ); ?></label>

		<input
			class="sui-form-control"
			name="live_secret" placeholder="<?php echo esc_attr( __( 'Enter your live secret key', Forminator::DOMAIN ) ); ?>"
			value="<?php echo esc_attr( $vars['live_secret'] ); ?>"
		/>

		<?php if ( ! empty( $vars['live_secret_error'] ) ) : ?>
			<span class="sui-error-message"><?php echo esc_html( $vars['live_secret_error'] ); ?></span>
		<?php endif; ?>

	</div>

</form>
