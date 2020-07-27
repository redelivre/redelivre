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

namespace FacebookPixelPlugin\Integration;

defined('ABSPATH') or die('Direct access not allowed');

use FacebookPixelPlugin\Core\FacebookPluginUtils;
use FacebookPixelPlugin\Core\FacebookServerSideEvent;
use FacebookPixelPlugin\Core\FacebookWordPressOptions;
use FacebookPixelPlugin\Core\ServerEventFactory;
use FacebookPixelPlugin\Core\PixelRenderer;
use FacebookAds\Object\ServerSide\Event;
use FacebookAds\Object\ServerSide\UserData;

class FacebookWordpressContactForm7 extends FacebookWordpressIntegrationBase {
  const PLUGIN_FILE = 'contact-form-7/wp-contact-form-7.php';
  const TRACKING_NAME = 'contact-form-7';

  public static function injectPixelCode() {
    add_action(
      'wpcf7_submit',
      array(__CLASS__, 'trackServerEvent'),
      10, 2);
  }

  public static function trackServerEvent($form, $result) {
    if (FacebookPluginUtils::isAdmin()) {
      return $result;
    }

    $server_event = ServerEventFactory::safeCreateEvent(
      'Lead',
      array(__CLASS__, 'readFormData'),
      array($form),
      self::TRACKING_NAME,
      true
    );
    FacebookServerSideEvent::getInstance()->track($server_event);

    add_action(
      'wpcf7_ajax_json_echo',
      array(__CLASS__, 'injectLeadEvent'),
      20, 2);

    return $result;
  }

  public static function injectLeadEvent($response, $result) {
    if (FacebookPluginUtils::isAdmin()) {
      return $response;
    }

    $events = FacebookServerSideEvent::getInstance()->getTrackedEvents();
    $code = PixelRenderer::render($events, self::TRACKING_NAME);
    $code = sprintf("
<!-- Facebook Pixel Event Code -->
%s
<!-- End Facebook Pixel Event Code -->
      ",
      $code);

    $response['message'] .= $code;
    return $response;
  }

  public static function readFormData($form) {
    if (empty($form)) {
      return array();
    }

    $form_tags = $form->scan_form_tags();
    $name = self::getName($form_tags);

    return array(
      'email' => self::getEmail($form_tags),
      'first_name' => $name[0],
      'last_name' => $name[1]
    );
  }

  private static function getEmail($form_tags) {
    if (empty($form_tags)) {
      return null;
    }

    foreach ($form_tags as $tag) {
      if ($tag->basetype == "email") {
        return $_POST[$tag->name];
      }
    }

    return null;
  }

  private static function getName($form_tags) {
    if (empty($form_tags)) {
      return null;
    }

    foreach ($form_tags as $tag) {
      if ($tag->basetype === "text"
        && strpos(strtolower($tag->name), 'name') !== false) {
        return ServerEventFactory::splitName($_POST[$tag->name]);
      }
    }

    return null;
  }
}
