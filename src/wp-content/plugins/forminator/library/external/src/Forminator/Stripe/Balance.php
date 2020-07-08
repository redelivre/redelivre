<?php

namespace Forminator\Stripe;

/**
 * Class Balance
 *
 * @property string $object
 * @property \Forminator\Stripe\StripeObject[] $available
 * @property \Forminator\Stripe\StripeObject[] $connect_reserved
 * @property bool $livemode
 * @property \Forminator\Stripe\StripeObject[] $pending
 *
 * @package Stripe
 */
class Balance extends SingletonApiResource
{
    const OBJECT_NAME = 'balance';

    /**
     * @param array|string|null $opts
     *
     * @throws \Forminator\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return Balance
     */
    public static function retrieve($opts = null)
    {
        return self::_singletonRetrieve($opts);
    }
}
