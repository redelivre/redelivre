<?php

namespace Forminator\Stripe\Issuing;

/**
 * Class Card
 *
 * @property string $id
 * @property string $object
 * @property mixed $authorization_controls
 * @property mixed $billing
 * @property string $brand
 * @property Cardholder $cardholder
 * @property int $created
 * @property string $currency
 * @property int $exp_month
 * @property int $exp_year
 * @property string $last4
 * @property bool $livemode
 * @property \Forminator\Stripe\StripeObject $metadata
 * @property string $name
 * @property mixed $shipping
 * @property string $status
 * @property string $type
 *
 * @package Forminator\Stripe\Issuing
 */
class Card extends \Forminator\Stripe\ApiResource
{
    const OBJECT_NAME = "issuing.card";

    use \Forminator\Stripe\ApiOperations\All;
    use \Forminator\Stripe\ApiOperations\Create;
    use \Forminator\Stripe\ApiOperations\Retrieve;
    use \Forminator\Stripe\ApiOperations\Update;

    /**
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return CardDetails The card details associated with that issuing card.
     */
    public function details($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/details';
        list($response, $opts) = $this->_request('get', $url, $params, $options);
        $obj = \Forminator\Stripe\Util\Util::convertToStripeObject($response, $opts);
        $obj->setLastResponse($response);
        return $obj;
    }
}
