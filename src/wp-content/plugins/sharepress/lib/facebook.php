<?php
/**
 * Copyright 2011 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

require_once "base_facebook.php";

/**
 * Extends the SpBaseFacebook class with the intent of using
 * PHP sessions to store user ids and access tokens.
 */
class SharePressFacebook extends SpBaseFacebook
{
  private $use_session = true;

  /**
   * Our special implementation of the PHP SDK allows us
   * to control when/if the $_SESSION is used to store
   * session data. When $use_session is false, FB session
   * data is instead stored in WP options, allowing the
   * keys to be shared between users, and more relevantly,
   * by scheduled events.
   */
  public function __construct($config, $use_session = true) {
    $this->use_session = $use_session;
    parent::__construct($config);
    /*
    if (!$this->use_session && ($access_token = $this->getUserAccessToken(true))) {
      if (!$this->getPersistentData('token_extended')) {
        $access_token = self::api('/oauth/access_token', 'POST', array(
          'client_id' => $this->getAppId(),
          'client_secret' => $this->getAppSecret(),
          'grant_type' => 'fb_exchange_token',
          'fb_exchange_token' => $access_token
        ));
        SharePress::log($access_token, 'WARN');
      }
    }
    */
  }

  public function useFileUploadSupport() {
    return false;
  }

  protected static $kSupportedKeys =
    array('state', 'code', 'access_token', 'user_id', 'token_extended');

    protected function makeRequest($url, $params, $ch=null) {
    $http = _wp_http_get_object();
 
    $args = array();
    $args['method'] = 'POST';
     
    $args['body'] = http_build_query($params, null, '&');
    
    // disable the 'Expect: 100-continue' behaviour. This causes CURL to wait
    // for 2 seconds if the server does not support this header.
    $opts = self::$CURL_OPTS;
    if (isset($opts[CURLOPT_HTTPHEADER])) {
      $existing_headers = $opts[CURLOPT_HTTPHEADER];
      $existing_headers[] = 'Expect:';
      $opts[CURLOPT_HTTPHEADER] = $existing_headers;
    } else {
      $opts[CURLOPT_HTTPHEADER] = array('Expect:');
    }
    $args['headers'] = $opts[CURLOPT_HTTPHEADER];
 
    $args['sslverify'] = false;
    $args['timeout'] = $opts[CURLOPT_CONNECTTIMEOUT] * 1000;
 
    $result = $http->request($url, $args);
 
    if (is_wp_error($result)) {
      throw new SpFacebookApiException(array(
        'error_code' => (int) $result->get_error_code(),
        'error' => array(
          'message' => $result->get_error_message(),
          'type' => 'WP_Error'
        )
      ));
    } 
 
    return $result['body'];
  }

  public function getUserAccessToken($read_only = false) {
    // first, consider using the stored access token,
    // so long as the session storage is not $_SESSION
    if (!$this->use_session && ( $access_token = $this->getPersistentData('access_token') )) {
      if (class_exists('Sharepress')) {
        SharePress::log("Using stored access token: {$access_token}");
      }
      return $access_token;
    } else {
      if (class_exists('Sharepress')) {
        if ($this->use_session) {
          SharePress::log(sprintf('Facebook SDK is in session mode - not using stored access token. %s %s', json_encode($_SESSION), json_encode($_REQUEST)), 'WARN');
        } else {
          // SharePress::log('No access token on file.', 'WARN');
        }
      }
    }

    if ($read_only) {
      return;
    }

    // first, consider a signed request if it's supplied.
    // if there is a signed request, then it alone determines
    // the access token.
    $signed_request = $this->getSignedRequest();
    if ($signed_request) {
      // apps.facebook.com hands the access_token in the signed_request
      if (array_key_exists('oauth_token', $signed_request)) {
        $access_token = $signed_request['oauth_token'];
        $this->setPersistentData('access_token', $access_token);
        return $access_token;
      }

      // the JS SDK puts a code in with the redirect_uri of ''
      if (array_key_exists('code', $signed_request)) {
        $code = $signed_request['code'];
        $access_token = $this->getAccessTokenFromCode($code, '');
        if ($access_token) {
          $this->setPersistentData('code', $code);
          $this->setPersistentData('access_token', $access_token);
          return $access_token;
        }
      }

      // signed request states there's no access token, so anything
      // stored should be cleared.
      $this->clearAllPersistentData();
      return false; // respect the signed request's data, even
                    // if there's an authorization code or something else
    }

    $code = $this->getCode();
    if ($code && $code != $this->getPersistentData('code')) {
      $access_token = $this->getAccessTokenFromCode($code);
      if ($access_token) {
        $this->setPersistentData('code', $code);
        $this->setPersistentData('access_token', $access_token);
        return $access_token;
      }

      // code was bogus, so everything based on it should be invalidated.
      $this->clearAllPersistentData();
      return false;
    }

    // as a fallback, just return whatever is in the persistent
    // store, knowing nothing explicit (signed request, authorization
    // code, etc.) was present to shadow it (or we saw a code in $_REQUEST,
    // but it's the same as what's in the persistent store)
    return $this->getPersistentData('access_token');
  }

  /**
   * Provides the implementations of the inherited abstract
   * methods.  The implementation uses PHP sessions to maintain
   * a store for authorization codes, user ids, CSRF states, and
   * access tokens.
   */
  protected function setPersistentData($key, $value) {
    if (!in_array($key, self::$kSupportedKeys)) {
      self::errorLog('Unsupported key passed to setPersistentData.');
      return;
    }

    $session_var_name = $this->constructSessionVariableName($key);

    if ($this->use_session) {
      if (!session_id()) {
        session_start();
      }
      $_SESSION[$session_var_name] = $value;
    } else {
      update_option($session_var_name, $value);
    }
  }

  protected function getPersistentData($key, $default = false) {
    if (!in_array($key, self::$kSupportedKeys)) {
      self::errorLog('Unsupported key passed to getPersistentData.');
      return $default;
    }

    $session_var_name = $this->constructSessionVariableName($key);
    
    if ($this->use_session) {
      if (!session_id()) {
        session_start();
      }
      return isset($_SESSION[$session_var_name]) ? $_SESSION[$session_var_name] : $default;
    } else {
      return get_option($session_var_name, $default);
    }
  }

  protected function clearPersistentData($key) {
    if (!in_array($key, self::$kSupportedKeys)) {
      self::errorLog('Unsupported key passed to clearPersistentData.');
      return;
    }

    $session_var_name = $this->constructSessionVariableName($key);

    if ($this->use_session) {
      if (!session_id()) {
        session_start();
      }
      unset($_SESSION[$session_var_name]);
    } else {
      delete_option($session_var_name);
    }
  }

  function clearAllPersistentData() {
    foreach (self::$kSupportedKeys as $key) {
      $this->clearPersistentData($key);
    }
  }

  protected function constructSessionVariableName($key) {
    $arg = implode('_', array('fb', $this->getAppId(), $key));
    if ($this->use_session) {
      return $arg;
    } else {
      return sprintf(SharePress::OPTION_SESSION_ARG, $arg);
    }
  }

  function getSessionVariableName($key) {
    return $this->constructSessionVariableName($key);
  }
}
