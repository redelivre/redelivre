<?php

require_once 'wfRESTBaseController.php';

class wfRESTConfigController extends wfRESTBaseController {

	public static function disconnectConfig($adminEmail = null) {
		global $wpdb;
		delete_transient('wordfenceCentralJWT' . wfConfig::get('wordfenceCentralSiteID'));

		if (is_null($adminEmail)) {
			$adminEmail = wfConfig::get('wordfenceCentralConnectEmail');
		}

		$result = $wpdb->query('DELETE FROM ' . wfDB::networkTable('wfConfig') . " WHERE name LIKE 'wordfenceCentral%'");

		wfConfig::set('wordfenceCentralDisconnected', true);
		wfConfig::set('wordfenceCentralDisconnectTime', time());
		wfConfig::set('wordfenceCentralDisconnectEmail', $adminEmail);

		return !!$result;
	}

	public function registerRoutes() {
		register_rest_route('wordfence/v1', '/config', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array($this, 'getConfig'),
			'permission_callback' => array($this, 'verifyToken'),
			'fields'              => array(
				'description' => __('Specific config options to return.', 'wordfence'),
				'type'        => 'array',
				'required'    => false,
			),
		));
		register_rest_route('wordfence/v1', '/config', array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => array($this, 'setConfig'),
			'permission_callback' => array($this, 'verifyToken'),
			'fields'              => array(
				'description' => __('Specific config options to set.', 'wordfence'),
				'type'        => 'array',
				'required'    => true,
			),
		));
		register_rest_route('wordfence/v1', '/disconnect', array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => array($this, 'disconnect'),
			'permission_callback' => array($this, 'verifyToken'),
		));
		register_rest_route('wordfence/v1', '/premium-connect', array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => array($this, 'premiumConnect'),
			'permission_callback' => array($this, 'verifyTokenPremium'),
		));
	}

	/**
	 * @param WP_REST_Request $request
	 * @return mixed|WP_REST_Response
	 */
	public function getConfig($request) {
		$fields = (array) $request['fields'];

		$config = array();

		$firewall = new wfFirewall();
		$wafFields = array(
			'autoPrepend'                    => $firewall->protectionMode() === wfFirewall::PROTECTION_MODE_EXTENDED,
			'avoid_php_input'                => wfWAF::getInstance()->getStorageEngine()->getConfig('avoid_php_input', false) ? 1 : 0,
			'disabledRules'                  => array_keys((array) wfWAF::getInstance()->getStorageEngine()->getConfig('disabledRules')),
			'ruleCount'                      => count((array) wfWAF::getInstance()->getRules()),
			'disableWAFBlacklistBlocking'    => wfWAF::getInstance()->getStorageEngine()->getConfig('disableWAFBlacklistBlocking'),
			'enabled'                        => $firewall->wafStatus() !== wfFirewall::FIREWALL_MODE_DISABLED,
            'firewallMode'                   => $firewall->firewallMode(),
			'learningModeGracePeriod'        => wfWAF::getInstance()->getStorageEngine()->getConfig('learningModeGracePeriod'),
			'learningModeGracePeriodEnabled' => wfWAF::getInstance()->getStorageEngine()->getConfig('learningModeGracePeriodEnabled'),
			'subdirectoryInstall'            => $firewall->isSubDirectoryInstallation(),
			'wafStatus'                      => $firewall->wafStatus(),
		);

		if (!$fields) {
			foreach (wfConfig::$defaultConfig as $group => $groupOptions) {
				foreach ($groupOptions as $field => $values) {
					$fields[] = $field;
				}
			}
			foreach ($wafFields as $wafField => $value) {
				$fields[] = 'waf.' . $wafField;
			}
		}

		foreach ($fields as $field) {
			if (strpos($field, 'waf.') === 0) {
				$wafField = substr($field, 4);
				if (array_key_exists($wafField, $wafFields)) {
					$config['waf'][$wafField] = $wafFields[$wafField];
				}
				continue;
			}

			if (array_key_exists($field, wfConfig::$defaultConfig['checkboxes'])) {
				$config[$field] = (bool) wfConfig::get($field);

			} else if (array_key_exists($field, wfConfig::$defaultConfig['otherParams']) ||
				array_key_exists($field, wfConfig::$defaultConfig['defaultsOnly'])) {

				$configConfig = !empty(wfConfig::$defaultConfig['otherParams'][$field]) ?
					wfConfig::$defaultConfig['otherParams'][$field] : wfConfig::$defaultConfig['defaultsOnly'][$field];

				if (!empty($configConfig['validation']['type'])) {
					switch ($configConfig['validation']['type']) {
						case wfConfig::TYPE_INT:
							$config[$field] = wfConfig::getInt($field);
							break;

						case wfConfig::TYPE_DOUBLE:
						case wfConfig::TYPE_FLOAT:
							$config[$field] = floatval(wfConfig::get($field));
							break;

						case wfConfig::TYPE_BOOL:
							$config[$field] = (bool) wfConfig::get($field);
							break;

						case wfConfig::TYPE_ARRAY:
							$config[$field] = wfConfig::get_ser($field);
							break;

						case wfConfig::TYPE_STRING:
						default:
							$config[$field] = wfConfig::get($field);
							break;
					}
				} else {
					$config[$field] = wfConfig::get($field);
				}

			} else if (in_array($field, wfConfig::$serializedOptions)) {
				$config[$field] = wfConfig::get_ser($field);
			}
		}

		$api = new wfAPI(wfConfig::get('apiKey'), wfUtils::getWPVersion());
		parse_str($api->makeAPIQueryString(), $qs);
		$systemInfo = json_decode(wfUtils::base64url_decode($qs['s']), true);
		$systemInfo['output_buffering'] = ini_get('output_buffering');
		$systemInfo['ip'] = wfUtils::getIPAndServerVariable();
		$systemInfo['detected_ips'] = wfUtils::getAllServerVariableIPs();
		$systemInfo['admin_url'] = network_admin_url();

		$response = rest_ensure_response(array(
			'config' => $config,
			'info'   => $systemInfo,
		));
		return $response;
	}

	/**
	 * @param WP_REST_Request $request
	 * @return mixed|WP_REST_Response
	 */
	public function setConfig($request) {
		$fields = $request['fields'];
		if (is_array($fields) && $fields) {
			$errors = wfConfig::validate($fields);
			if ($errors !== true) {
				if (count($errors) == 1) {
					return new WP_Error('rest_set_config_error',
						sprintf(__('An error occurred while saving the configuration: %s', 'wordfence'), $errors[0]['error']),
						array('status' => 422));

				} else if (count($errors) > 1) {
					$compoundMessage = array();
					foreach ($errors as $e) {
						$compoundMessage[] = $e['error'];
					}
					return new WP_Error('rest_set_config_error',
						sprintf(__('Errors occurred while saving the configuration: %s', 'wordfence'), implode(', ', $compoundMessage)),
						array('status' => 422));
				}

				return new WP_Error('rest_set_config_error',
					__('Errors occurred while saving the configuration.', 'wordfence'),
					array('status' => 422));
			}

			try {
				wfConfig::save($fields);
				return rest_ensure_response(array(
					'success' => true,
				));

			} catch (Exception $e) {
				return new WP_Error('rest_save_config_error',
					sprintf(__('A server error occurred while saving the configuration: %s', 'wordfence'), $e->getMessage()),
					array('status' => 500));
			}
		}
		return new WP_Error('rest_save_config_error',
			__("Validation error: 'fields' parameter is empty or not an array.", 'wordfence'),
			array('status' => 422));

	}

	/**
	 * @param WP_REST_Request $request
	 * @return mixed|WP_REST_Response
	 */
	public function disconnect($request) {
		self::disconnectConfig();
		return rest_ensure_response(array(
			'success' => true,
		));
	}

	/**
	 * @param WP_REST_Request $request
	 * @return mixed|WP_REST_Response
	 */
	public function premiumConnect($request) {
		require_once WORDFENCE_PATH . '/vendor/paragonie/sodium_compat/autoload-fast.php';

		// Store values sent by Central.
		$wordfenceCentralPK = $request['public-key'];
		$wordfenceCentralSiteData = $request['site-data'];
		$wordfenceCentralSiteID = $request['site-id'];

		$keypair = ParagonIE_Sodium_Compat::crypto_sign_keypair();
		$publicKey = ParagonIE_Sodium_Compat::crypto_sign_publickey($keypair);
		$secretKey = ParagonIE_Sodium_Compat::crypto_sign_secretkey($keypair);
		wfConfig::set('wordfenceCentralSecretKey', $secretKey);

		wfConfig::set('wordfenceCentralConnected', 1);
		wfConfig::set('wordfenceCentralCurrentStep', 6);
		wfConfig::set('wordfenceCentralPK', pack("H*", $wordfenceCentralPK));
		wfConfig::set('wordfenceCentralSiteData', json_encode($wordfenceCentralSiteData));
		wfConfig::set('wordfenceCentralSiteID', $wordfenceCentralSiteID);
		wfConfig::set('wordfenceCentralConnectTime', time());
		wfConfig::set('wordfenceCentralConnectEmail', !empty($this->tokenData['adminEmail']) ? $this->tokenData['adminEmail'] : null);

		// Return values created by Wordfence.
		return rest_ensure_response(array(
			'success'    => true,
			'public-key' => ParagonIE_Sodium_Compat::bin2hex($publicKey),
		));
	}
}