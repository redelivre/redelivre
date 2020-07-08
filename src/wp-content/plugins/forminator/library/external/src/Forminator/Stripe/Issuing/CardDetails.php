<?php

namespace Forminator\Stripe\Issuing;

/**
 * Class CardDetails
 *
 * @property string $id
 * @property string $object
 * @property Card $card
 * @property string $cvc
 * @property int $exp_month
 * @property int $exp_year
 * @property string $number
 *
 * @package Forminator\Stripe\Issuing
 */
class CardDetails extends \Forminator\Stripe\ApiResource
{
    const OBJECT_NAME = 'issuing.card_details';
}
