<?php
if (!defined('WORDFENCE_LS_VERSION')) { exit; }

/**
 * @var \WP_User $user The user being edited. Required.
 * @var bool $canEditUsers Whether or not the viewer of the page can edit other users. Optional, defaults to false.
 */

if (!isset($canEditUsers)) {
	$canEditUsers = false;
}

$ownAccount = false;
$ownUser = wp_get_current_user();
if ($ownUser->ID == $user->ID) {
	$ownAccount = true;
}

$enabled = \WordfenceLS\Controller_Users::shared()->has_2fa_active($user);

?>
<p><?php printf(__('Two-Factor Authentication, or 2FA, significantly improves login security for your website. Wordfence 2FA works with a number of TOTP-based apps like Google Authenticator, FreeOTP, and Authy. For a full list of tested TOTP-based apps, <a href="%s" target="_blank" rel="noopener noreferrer">click here</a>.', 'wordfence-2fa'), \WordfenceLS\Controller_Support::esc_supportURL(\WordfenceLS\Controller_Support::ITEM_MODULE_LOGIN_SECURITY_2FA)); ?></p>
<?php if ($canEditUsers): ?>
<div id="wfls-editing-display" class="wfls-flex-row wfls-flex-row-xs-wrappable wfls-flex-row-equal-heights">
	<div class="wfls-block wfls-always-active wfls-flex-item-full-width wfls-add-bottom">
		<div class="wfls-block-header wfls-block-header-border-bottom">
			<div class="wfls-block-header-content">
				<div class="wfls-block-title">
					<strong><?php printf(__('Editing User:&nbsp;&nbsp;%s <span class="wfls-text-plain">%s</span>', 'wordfence-2fa'), get_avatar($user->ID, 16, '', $user->user_login), \WordfenceLS\Text\Model_HTML::esc_html($user->user_login) . ($ownAccount ? ' ' . __('(you)', 'wordfence-2fa') : '')); ?></strong>
				</div>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>
<div id="wfls-deactivation-controls" class="wfls-flex-row wfls-flex-row-wrappable wfls-flex-row-equal-heights"<?php if (!$enabled) { echo ' style="display: none;"'; } ?>>
	<!-- begin status content -->
	<div class="wfls-flex-row wfls-flex-row-equal-heights wfls-flex-item-xs-100">
		<?php
		echo \WordfenceLS\Model_View::create('manage/deactivate', array(
			'user' => $user,
		))->render();
		?>
	</div>
	<!-- end status content -->
	<!-- begin regenerate codes -->
	<div class="wfls-flex-row wfls-flex-row-equal-heights wfls-flex-item-xs-100">
		<?php
		echo \WordfenceLS\Model_View::create('manage/regenerate', array(
			'user' => $user,
			'remaining' => \WordfenceLS\Controller_Users::shared()->recovery_code_count($user),
		))->render();
		?>
	</div>
	<!-- end regenerate codes -->
</div>
<div id="wfls-activation-controls" class="wfls-flex-row wfls-flex-row-xs-wrappable wfls-flex-row-equal-heights"<?php if ($enabled) { echo ' style="display: none;"'; } ?>>
	<?php
		$secret = \WordfenceLS\Model_Crypto::random_bytes(20);
		$base32 = new \WordfenceLS\Crypto\Model_Base2n(5, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567', false, true, true);
		$base32Secret = $base32->encode($secret);
		$totpURL = "otpauth://totp/" . rawurlencode(preg_replace('~^https?://~i', '', home_url()) . ' (' . $user->user_login . ')') . '?secret=' . $base32Secret . '&algorithm=SHA1&digits=6&period=30&issuer=Wordfence';
		$codes = \WordfenceLS\Controller_Users::shared()->regenerate_recovery_codes();
	?>
	<!-- begin qr code -->
	<div class="wfls-flex-row wfls-flex-row-equal-heights wfls-col-sm-half-padding-right wfls-flex-item-xs-100 wfls-flex-item-sm-50">
		<?php
		echo \WordfenceLS\Model_View::create('manage/code', array(
			'secret' => $secret,
			'base32Secret' => $base32Secret,
			'totpURL' => $totpURL,
		))->render();
		?>
	</div>
	<!-- end qr code -->
	<!-- begin activation -->
	<div class="wfls-flex-row wfls-flex-row-equal-heights wfls-col-sm-half-padding-left wfls-flex-item-xs-100 wfls-flex-item-sm-50">
		<?php
		echo \WordfenceLS\Model_View::create('manage/activate', array(
			'secret' => $secret,
			'base32Secret' => $base32Secret,
			'recovery' => $codes,
			'user' => $user,
		))->render();
		?>
	</div>
	<!-- end activation -->
</div>
<?php
/**
 * Fires after the main content of the activation page has been output.
 */
do_action('wfls_activation_page_footer');
?>
<?php if (\WordfenceLS\Controller_Permissions::shared()->can_manage_settings()): ?>
<p><?php _e('Server Time:', 'wordfence-2fa'); ?> <?php echo date('Y-m-d H:i:s', microtime(true)); ?><br>
<?php if (\WordfenceLS\Controller_Settings::shared()->get_bool(\WordfenceLS\Controller_Settings::OPTION_USE_NTP)): _e('Corrected Time:', 'wordfence-2fa'); ?> <?php echo date('Y-m-d H:i:s', \WordfenceLS\Controller_Time::time()); ?><br><?php endif; ?>
<?php _e('Detected IP:', 'wordfence-2fa'); ?> <?php echo \WordfenceLS\Text\Model_HTML::esc_html(\WordfenceLS\Model_Request::current()->ip()); if (\WordfenceLS\Controller_Whitelist::shared()->is_whitelisted(\WordfenceLS\Model_Request::current()->ip())) { echo ' (' . __('whitelisted', 'wordfence-2fa') . ')'; } ?></p>
<?php endif; ?>
