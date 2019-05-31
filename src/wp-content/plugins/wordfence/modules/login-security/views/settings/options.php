<?php
if (!defined('WORDFENCE_LS_VERSION')) { exit; }
?>
<div class="wfls-block wfls-always-active wfls-flex-item-full-width">
	<div class="wfls-block-header wfls-block-header-border-bottom">
		<div class="wfls-block-header-content">
			<div class="wfls-block-title">
				<strong><?php _e('Settings', 'wordfence-2fa'); ?></strong>
			</div>
		</div>
		<div class="wfls-block-header-action wfls-block-header-action-text wfls-nowrap wfls-padding-add-right-responsive">
			<a href="#" id="wfls-cancel-changes" class="wfls-btn wfls-btn-sm wfls-btn-default wfls-disabled"><?php _e('Cancel Changes', 'wordfence-2fa'); ?></a>&nbsp;&nbsp;<a href="#" id="wfls-save-changes" class="wfls-btn wfls-btn-sm wfls-btn-primary wfls-disabled"><?php _e('Save Changes', 'wordfence-2fa'); ?></a>
		</div>
	</div>
	<div class="wfls-block-content">
		<ul class="wfls-block-list">
			<li>
				<?php
				$roles = new \WP_Roles();
				$options = array();
				if (is_multisite()) {
					$options[] = array(
						'name' => 'enabled-roles.super-admin',
						'enabledValue' => '1',
						'disabledValue' => '0',
						'value' => '1',
						'title' => __('Super Administrator', 'wordfence-2fa'),
						'editable' => false,
					);
				}
				
				foreach ($roles->role_objects as $name => $r) {
					/** @var \WP_Role $r */
					$options[] = array(
						'name' => 'enabled-roles.' . $name,
						'enabledValue' => '1',
						'disabledValue' => '0',
						'value' => $r->has_cap(\WordfenceLS\Controller_Permissions::CAP_ACTIVATE_2FA_SELF) ? '1' : '0',
						'title' => $roles->role_names[$name],
						'editable' => (!is_multisite() && $name == 'administrator' ? false : true),
					);
				}
				
				echo \WordfenceLS\Model_View::create('options/option-toggled-multiple', array(
					'title' => new \WordfenceLS\Text\Model_HTML('<strong>' . __('Enable 2FA for these roles', 'wordfence-2fa') . '</strong>'),
					'options' => $options,
					'wrap' => true,
				))->render();
				?>
			</li>
			<li>
				<?php
				echo \WordfenceLS\Model_View::create('options/option-require-2fa', array(
				))->render();
				?>
			</li>
			<li>
				<?php
				echo \WordfenceLS\Model_View::create('options/option-toggled', array(
					'optionName' => \WordfenceLS\Controller_Settings::OPTION_REMEMBER_DEVICE_ENABLED,
					'enabledValue' => '1',
					'disabledValue' => '0',
					'value' => \WordfenceLS\Controller_Settings::shared()->get_bool(\WordfenceLS\Controller_Settings::OPTION_REMEMBER_DEVICE_ENABLED) ? '1': '0',
					'title' => new \WordfenceLS\Text\Model_HTML('<strong>' . __('Allow remembering device for 30 days', 'wordfence-2fa') . '</strong>'),
					'subtitle' => __('If enabled, users with 2FA enabled may choose to be prompted for a code only once every 30 days per device.', 'wordfence-2fa'),
				))->render();
				?>
			</li>
			<li>
				<?php
				echo \WordfenceLS\Model_View::create('options/option-switch', array(
					'optionName' => \WordfenceLS\Controller_Settings::OPTION_XMLRPC_ENABLED,
					'value' => \WordfenceLS\Controller_Settings::shared()->get_bool(\WordfenceLS\Controller_Settings::OPTION_XMLRPC_ENABLED) ? '1': '0',
					'title' => new \WordfenceLS\Text\Model_HTML('<strong>' . __('Require 2FA for XML-RPC call authentication', 'wordfence-2fa') . '</strong>'),
					'subtitle' => __('If enabled, XML-RPC calls that require authentication will also require a valid 2FA code to be appended to the password. You must choose the "Skipped" option if you use the WordPress app, the Jetpack plugin, or other services that require XML-RPC.', 'wordfence-2fa'),
					'states' => array(
						array('value' => '0', 'label' => __('Skipped', 'wordfence-2fa')),
						array('value' => '1', 'label' => __('Required', 'wordfence-2fa')),
					),
					'noSpacer' => true,
					'alignment' => 'wfls-right',
				))->render();
				?>
			</li>
			<li>
				<?php
				echo \WordfenceLS\Model_View::create('options/option-toggled', array(
					'optionName' => \WordfenceLS\Controller_Settings::OPTION_ALLOW_XML_RPC,
					'enabledValue' => '0',
					'disabledValue' => '1',
					'value' => \WordfenceLS\Controller_Settings::shared()->get_bool(\WordfenceLS\Controller_Settings::OPTION_ALLOW_XML_RPC) ? '1': '0',
					'title' => new \WordfenceLS\Text\Model_HTML('<strong>' . __('Disable XML-RPC authentication', 'wordfence-2fa') . '</strong>'),
					'subtitle' => __('If disabled, XML-RPC requests that attempt authentication will be rejected.', 'wordfence-2fa'),
				))->render();
				?>
			</li>
			<li>
				<?php
				echo \WordfenceLS\Model_View::create('options/option-textarea', array(
					'textOptionName' => \WordfenceLS\Controller_Settings::OPTION_2FA_WHITELISTED,
					'textValue' => implode("\n", \WordfenceLS\Controller_Settings::shared()->whitelisted_ips()),
					'title' => new \WordfenceLS\Text\Model_HTML('<strong>' . __('Whitelisted IP addresses that bypass 2FA', 'wordfence-2fa') . '</strong>'),
					'alignTitle' => 'top',
					'subtitle' => __('Whitelisted IPs must be placed on separate lines. You can specify ranges using the following formats: 127.0.0.1/24, 127.0.0.[1-100], or 127.0.0.1-127.0.1.100.', 'wordfence-2fa'),
					'subtitlePosition' => 'value',
					'noSpacer' => true,
				))->render();
				?>
			</li>
			<li>
				<?php
				echo \WordfenceLS\Model_View::create('options/option-captcha', array(
				))->render();
				?>
			</li>
			<?php if (!WORDFENCE_LS_FROM_CORE): ?>
			<li>
				<?php
				echo \WordfenceLS\Model_View::create('options/option-ip-source', array())->render();
				?>
			</li>
			<?php endif; ?>
		</ul>
	</div>
</div>
