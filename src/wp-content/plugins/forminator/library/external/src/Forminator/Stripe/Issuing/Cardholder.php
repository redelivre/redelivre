<?php

namespace Forminator\Stripe\Issuing;

/**
 * Class Cardholder
 *
 * @property string $id
 * @property string $object
 * @property mixed $billing
 * @property int $created
 * @property string $email
 * @property bool $livemode
 * @property \Forminator\Stripe\StripeObject $metadata
 * @property string $name
 * @property string $phone_number
 * @property string $status
 * @property string $type
 *
 * @package Forminator\Stripe\Issuing
 */
class Cardholder extends \Forminator\Stripe\ApiResource
{
    const OBJECT_NAME = "issuing.cardholder";

    use \Forminator\Stripe\ApiOperations\All;
    use \Forminator\Stripe\ApiOperations\Create;
    use \Forminator\Stripe\ApiOperations\Retrieve;
    use \Forminator\Stripe\ApiOperations\Update;
}
