<?php

namespace Forminator\Stripe\Exception;

/**
 * AuthenticationException is thrown when invalid credentials are used to
 * connect to Stripe's servers.
 *
 * @package Forminator\Stripe\Exception
 */
class AuthenticationException extends ApiErrorException
{
}
