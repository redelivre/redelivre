<?php

namespace Forminator\Stripe;

/**
 * Class Mandate
 *
 * @property string $id
 * @property string $object
 * @property \Forminator\Stripe\StripeObject $customer_acceptance
 * @property bool $livemode
 * @property \Forminator\Stripe\StripeObject $multi_use
 * @property string $payment_method
 * @property \Forminator\Stripe\StripeObject $payment_method_details
 * @property \Forminator\Stripe\StripeObject $single_use
 * @property string $status
 * @property string $type
 *
 * @package Stripe
 */
class Mandate extends ApiResource
{
    const OBJECT_NAME = 'mandate';

    use ApiOperations\Retrieve;
}
