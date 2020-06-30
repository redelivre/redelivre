<?php

namespace Forminator\Stripe\Terminal;

/**
 * Class Reader
 *
 * @property string $id
 * @property string $object
 * @property bool $deleted
 * @property string $device_sw_version
 * @property string $device_type
 * @property string $ip_address
 * @property string $label
 * @property string $location
 * @property string $serial_number
 * @property string $status
 *
 * @package Forminator\Stripe\Terminal
 */
class Reader extends \Forminator\Stripe\ApiResource
{
    const OBJECT_NAME = "terminal.reader";

    use \Forminator\Stripe\ApiOperations\All;
    use \Forminator\Stripe\ApiOperations\Create;
    use \Forminator\Stripe\ApiOperations\Delete;
    use \Forminator\Stripe\ApiOperations\Retrieve;
    use \Forminator\Stripe\ApiOperations\Update;
}
