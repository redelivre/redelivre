<?php
/**
 * Copyright (c) 2016-present, Facebook, Inc.
 * All rights reserved.
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 */
namespace Facebook\InstantArticles\Transformer\Rules;

use Facebook\InstantArticles\Elements\Header;
use Facebook\InstantArticles\Elements\H2;

class HeaderSubTitleRule extends ConfigurationSelectorRule
{
    public function getContextClass()
    {
        return Header::getClassName();
    }

    public static function create()
    {
        return new HeaderSubTitleRule();
    }

    public static function createFrom($configuration)
    {
        return self::create()->withSelector($configuration['selector']);
    }

    public function apply($transformer, $header, $h2)
    {
        $header->withSubTitle($transformer->transform(H2::create(), $h2));
        return $header;
    }
}
