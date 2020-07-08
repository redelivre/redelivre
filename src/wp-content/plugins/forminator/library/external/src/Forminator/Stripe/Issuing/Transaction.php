<?php

namespace Forminator\Stripe\Issuing;

/**
 * Class Transaction
 *
 * @property string $id
 * @property string $object
 * @property int $amount
 * @property string|null $authorization
 * @property string|null $balance_transaction
 * @property string $card
 * @property string|null $cardholder
 * @property int $created
 * @property string $currency
 * @property string|null $dispute
 * @property bool $livemode
 * @property int $merchant_amount
 * @property string $merchant_currency
 * @property \Forminator\Stripe\StripeObject $merchant_data
 * @property \Forminator\Stripe\StripeObject $metadata
 * @property string $type
 *
 * @package Forminator\Stripe\Issuing
 */
class Transaction extends \Forminator\Stripe\ApiResource
{
    const OBJECT_NAME = 'issuing.transaction';

    use \Forminator\Stripe\ApiOperations\All;
    use \Forminator\Stripe\ApiOperations\Retrieve;
    use \Forminator\Stripe\ApiOperations\Update;
}
