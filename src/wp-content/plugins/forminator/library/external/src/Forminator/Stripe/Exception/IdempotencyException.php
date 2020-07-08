<?php

namespace Forminator\Stripe\Exception;

/**
 * IdempotencyException is thrown in cases where an idempotency key was used
 * improperly.
 *
 * @package Forminator\Stripe\Exception
 */
class IdempotencyException extends ApiErrorException
{
}
