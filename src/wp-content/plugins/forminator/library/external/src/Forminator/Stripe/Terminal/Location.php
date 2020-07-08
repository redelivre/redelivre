<?php

namespace Forminator\Stripe\Terminal;

/**
 * Class Location
 *
 * @property string $id
 * @property string $object
 * @property \Forminator\Stripe\StripeObject $address
 * @property string $display_name
 * @property bool $livemode
 * @property \Forminator\Stripe\StripeObject $metadata
 *
 * @package Forminator\Stripe\Terminal
 */
class Location extends \Forminator\Stripe\ApiResource
{
    const OBJECT_NAME = 'terminal.location';

    use \Forminator\Stripe\ApiOperations\All;
    use \Forminator\Stripe\ApiOperations\Create;
    use \Forminator\Stripe\ApiOperations\Delete;
    use \Forminator\Stripe\ApiOperations\Retrieve;
    use \Forminator\Stripe\ApiOperations\Update;
}
