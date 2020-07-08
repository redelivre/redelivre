<?php

namespace Forminator\Stripe\Terminal;

/**
 * Class Reader
 *
 * @property string $id
 * @property string $object
 * @property string|null $device_sw_version
 * @property string $device_type
 * @property string|null $ip_address
 * @property string $label
 * @property bool $livemode
 * @property string|null $location
 * @property \Forminator\Stripe\StripeObject $metadata
 * @property string $serial_number
 * @property string|null $status
 *
 * @package Forminator\Stripe\Terminal
 */
class Reader extends \Forminator\Stripe\ApiResource
{
    const OBJECT_NAME = 'terminal.reader';

    use \Forminator\Stripe\ApiOperations\All;
    use \Forminator\Stripe\ApiOperations\Create;
    use \Forminator\Stripe\ApiOperations\Delete;
    use \Forminator\Stripe\ApiOperations\Retrieve;
    use \Forminator\Stripe\ApiOperations\Update;
}
