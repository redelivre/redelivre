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

use ReflectionClass;
use FacebookAds\Object\ServerSide\CustomData;

defined('ABSPATH') or die('Direct access not allowed');

class PixelRenderer {
  const EVENT_ID = 'eventID';
  const TRACK = 'track';
  const TRACK_CUSTOM = 'trackCustom';
  const FB_INTEGRATION_TRACKING = 'fb_integration_tracking';
  const SCRIPT_TAG = "<script type='text/javascript'>%s</script>";
  const FBQ_CODE = "
  fbq('%s', '%s', %s, %s);
";

  public static function render($events, $fb_integration_tracking) {
    if (empty($events)) {
      return "";
    }

    $code = "";
    foreach ($events as $event) {
      $code .= self::getPixelTrackCode($event, $fb_integration_tracking);
    }

    return sprintf(self::SCRIPT_TAG, $code);
  }

  private static function getPixelTrackCode($event, $fb_integration_tracking) {
    $event_data[self::EVENT_ID] = $event->getEventId();

    $custom_data = $event->getCustomData() !== null ?
                    $event->getCustomData() :
                    new CustomData();

    $normalized_custom_data = $custom_data->normalize();
    if (!is_null($fb_integration_tracking)) {
      $normalized_custom_data[
        self::FB_INTEGRATION_TRACKING] = $fb_integration_tracking;
    }

    $class = new ReflectionClass('FacebookPixelPlugin\Core\FacebookPixel');

    return sprintf(
      self::FBQ_CODE,
      $class->getConstant(strtoupper($event->getEventName())) !== false
      ? self::TRACK : self::TRACK_CUSTOM,
      $event->getEventName(),
      json_encode($normalized_custom_data, JSON_PRETTY_PRINT),
      json_encode($event_data, JSON_PRETTY_PRINT)
    );
  }
}
