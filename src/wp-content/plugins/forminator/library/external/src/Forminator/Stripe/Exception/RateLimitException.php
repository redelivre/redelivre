<?php

namespace Forminator\Stripe\Exception;

/**
 * RateLimitException is thrown in cases where an account is putting too much
 * load on Stripe's API servers (usually by performing too many requests).
 * Please back off on request rate.
 *
 * @package Forminator\Stripe\Exception
 */
class RateLimitException extends InvalidRequestException
{
}
