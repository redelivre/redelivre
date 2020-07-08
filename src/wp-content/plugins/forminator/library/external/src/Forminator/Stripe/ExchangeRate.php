<?php

namespace Forminator\Stripe;

/**
 * Class ExchangeRate
 *
 * @property string $id
 * @property string $object
 * @property \Forminator\Stripe\StripeObject $rates
 *
 * @package Stripe
 */
class ExchangeRate extends ApiResource
{
    const OBJECT_NAME = 'exchange_rate';

    use ApiOperations\All;
    use ApiOperations\Retrieve;
}
