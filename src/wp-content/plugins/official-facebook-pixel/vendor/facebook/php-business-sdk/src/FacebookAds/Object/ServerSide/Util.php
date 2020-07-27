<?php
/**
 * Copyright (c) 2015-present, Facebook, Inc. All rights reserved.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Facebook.
 *
 * As with any software that integrates with the Facebook platform, your use
 * of this software is subject to the Facebook Developer Principles and
 * Policies [http://developers.facebook.com/policy/]. This copyright notice
 * shall be included in all copies or substantial portions of the software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */

namespace FacebookAds\Object\ServerSide;

/**
 * Util Class
 *
 * @category    Class
 * @package     FacebookAds\Object\ServerSide
 */
class Util {
  /**
   * @param string $data hash input data using SHA256 algorithm.
   * @return string
   */
  public static function hash($data) {
    if ($data == null || Util::isHashed($data)) {
      return $data;
    }
    return hash('sha256', $data, false);
  }

  /**
   * @param string $pii PII data to check if its hashed.
   * @return bool
   */
  public static function isHashed($pii) {
    // it could be sha256 or md5
    return preg_match('/^[A-Fa-f0-9]{64}$/', $pii) ||
      preg_match('/^[a-f0-9]{32}$/', $pii);
  }
}
