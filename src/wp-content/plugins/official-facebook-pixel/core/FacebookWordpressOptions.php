<?php
/*
* Copyright (C) 2017-present, Facebook, Inc.
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; version 2 of the License.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*/

/**
 * @package FacebookPixelPlugin
 */

namespace FacebookPixelPlugin\Core;

defined('ABSPATH') or die('Direct access not allowed');

class FacebookWordpressOptions {
  private static $options = array();
  private static $userInfo = array();
  private static $versionInfo = array();

  public static function initialize() {
    self::setOptions();
    self::setUserInfo();
    self::setVersionInfo();
  }

  public static function getOptions() {
    return self::$options;
  }

  public static function getDefaultPixelID() {
    return is_null(FacebookPluginConfig::DEFAULT_PIXEL_ID)
              ? '' : FacebookPluginConfig::DEFAULT_PIXEL_ID;
  }

  public static function getDefaultAccessToken() {
    return is_null(FacebookPluginConfig::DEFAULT_ACCESS_TOKEN)
              ? '' : FacebookPluginConfig::DEFAULT_ACCESS_TOKEN;
  }

  // Default is on for unset config
  public static function getDefaultUsePIIKey() {
    return (!is_null(FacebookPluginConfig::USE_ADVANCED_MATCHING_DEFAULT)
      && !FacebookPluginConfig::USE_ADVANCED_MATCHING_DEFAULT) ? '0' : '1';
  }

  // We default not to send events through S2S, if the config is unset.
  public static function getDefaultUseS2SKey() {
    return (is_null(FacebookPluginConfig::USE_S2S_DEFAULT)
      || !FacebookPluginConfig::USE_S2S_DEFAULT) ? '0' : '1';
  }

  private static function setOptions() {
    self::$options = \get_option(
      FacebookPluginConfig::SETTINGS_KEY,
      array(
        FacebookPluginConfig::PIXEL_ID_KEY => self::getDefaultPixelID(),
        FacebookPluginConfig::USE_PII_KEY => self::getDefaultUsePIIKey(),
        FacebookPluginConfig::USE_S2S_KEY => self::getDefaultUseS2SKey(),
        FacebookPluginConfig::ACCESS_TOKEN_KEY => self::getDefaultAccessToken(),
      ));

    // we need esc_js because the id is set through the form
    if (array_key_exists(FacebookPluginConfig::PIXEL_ID_KEY, self::$options)) {
      self::$options[FacebookPluginConfig::PIXEL_ID_KEY] =
        esc_js(self::$options[FacebookPluginConfig::PIXEL_ID_KEY]);
    }

    if (array_key_exists(
      FacebookPluginConfig::ACCESS_TOKEN_KEY, self::$options)) {
      self::$options[FacebookPluginConfig::ACCESS_TOKEN_KEY] =
        esc_js(self::$options[FacebookPluginConfig::ACCESS_TOKEN_KEY]);
    }
  }

  public static function getPixelId() {
    if (array_key_exists(FacebookPluginConfig::PIXEL_ID_KEY, self::$options)) {
      return self::$options[FacebookPluginConfig::PIXEL_ID_KEY];
    }

    return self::getDefaultPixelID();
  }

  public static function getAccessToken() {
    if (array_key_exists(
      FacebookPluginConfig::ACCESS_TOKEN_KEY, self::$options)) {
      return self::$options[FacebookPluginConfig::ACCESS_TOKEN_KEY];
    }

    return self::getDefaultAccessToken();
  }

  public static function getUsePii() {
    if (array_key_exists(
      FacebookPluginConfig::USE_PII_KEY, self::$options)) {
      return self::$options[FacebookPluginConfig::USE_PII_KEY];
    }

    return self::getDefaultUsePIIKey();
  }

  public static function getUseS2S() {
    if (array_key_exists(FacebookPluginConfig::USE_S2S_KEY, self::$options)) {
      return self::$options[FacebookPluginConfig::USE_S2S_KEY];
    }

    return self::getDefaultUseS2SKey();
  }

  public static function getUserInfo() {
    return self::$userInfo;
  }

  public static function setUserInfo() {
    add_action(
      'init',
      array(
        'FacebookPixelPlugin\\Core\\FacebookWordpressOptions',
        'registerUserInfo'
      ),
      0);
  }

  public static function registerUserInfo() {
    $current_user = wp_get_current_user();
    $use_pii = self::getUsePii();
    if (0 === $current_user->ID || $use_pii !== '1') {
      // User not logged in or admin chose not to send PII.
      self::$userInfo = array();
    } else {
      self::$userInfo = array_filter(
        array(
          // Keys documented in
          // https://developers.facebook.com/docs/facebook-pixel/pixel-with-ads/conversion-tracking#advanced_match
          'em' => $current_user->user_email,
          'fn' => $current_user->user_firstname,
          'ln' => $current_user->user_lastname
        ),
        function ($value) { return $value !== null && $value !== ''; });
    }
  }

  public static function getVersionInfo() {
    return self::$versionInfo;
  }

  public static function setVersionInfo() {
    global $wp_version;

    self::$versionInfo = array(
      'pluginVersion' => FacebookPluginConfig::PLUGIN_VERSION,
      'source' => FacebookPluginConfig::SOURCE,
      'version' => $wp_version
    );
  }

  public static function getAgentString() {
    return sprintf(
      '%s-%s-%s',
      self::$versionInfo['source'],
      self::$versionInfo['version'],
      self::$versionInfo['pluginVersion']);
  }
}
