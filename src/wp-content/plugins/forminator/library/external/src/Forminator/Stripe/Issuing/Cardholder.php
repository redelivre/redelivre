<?php

namespace Forminator\Stripe\Issuing;

/**
 * Class Cardholder
 *
 * @property string $id
 * @property string $object
 * @property \Forminator\Stripe\StripeObject|null $authorization_controls
 * @property \Forminator\Stripe\StripeObject $billing
 * @property \Forminator\Stripe\StripeObject|null $company
 * @property int $created
 * @property string|null $email
 * @property \Forminator\Stripe\StripeObject|null $individual
 * @property bool $is_default
 * @property bool $livemode
 * @property \Forminator\Stripe\StripeObject $metadata
 * @property string $name
 * @property string|null $phone_number
 * @property \Forminator\Stripe\StripeObject $requirements
 * @property string $status
 * @property string $type
 *
 * @package Forminator\Stripe\Issuing
 */
class Cardholder extends \Forminator\Stripe\ApiResource
{
    const OBJECT_NAME = 'issuing.cardholder';

    use \Forminator\Stripe\ApiOperations\All;
    use \Forminator\Stripe\ApiOperations\Create;
    use \Forminator\Stripe\ApiOperations\Retrieve;
    use \Forminator\Stripe\ApiOperations\Update;
}
