<?php

namespace Forminator\Stripe\Terminal;

/**
 * Class ConnectionToken
 *
 * @property string $object
 * @property string $location
 * @property string $secret
 *
 * @package Forminator\Stripe\Terminal
 */
class ConnectionToken extends \Forminator\Stripe\ApiResource
{
    const OBJECT_NAME = 'terminal.connection_token';

    use \Forminator\Stripe\ApiOperations\Create;
}
