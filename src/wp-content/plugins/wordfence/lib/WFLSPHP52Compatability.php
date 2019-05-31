<?php

/**
 * Class WFLSPHP52Compatability
 * 
 * This class exists solely to contain syntax incompatible with PHP 5.2 into a single file.
 */
class WFLSPHP52Compatability {
	public static function install_plugin() {
		\WordfenceLS\Controller_WordfenceLS::shared()->_install_plugin();
	}
	
	public static function uninstall_plugin() {
		\WordfenceLS\Controller_WordfenceLS::shared()->_uninstall_plugin();
	}
	
	public static function import_2fa($import) {
		$imported = \WordfenceLS\Controller_Users::shared()->import_2fa($import);
		if ($imported && wfConfig::get('loginSec_requireAdminTwoFactor')) {
			\WordfenceLS\Controller_Settings::shared()->set(\WordfenceLS\Controller_Settings::OPTION_REQUIRE_2FA_ADMIN, true);
		}
		return $imported;
	}
	
	public static function secrets_table() {
		return \WordfenceLS\Controller_DB::shared()->secrets;
	}
}