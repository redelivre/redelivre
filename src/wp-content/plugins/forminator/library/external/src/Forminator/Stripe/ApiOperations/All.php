<?php

namespace Forminator\Stripe\ApiOperations;

/**
 * Trait for listable resources. Adds a `all()` static method to the class.
 *
 * This trait should only be applied to classes that derive from StripeObject.
 */
trait All
{
    /**
     * @param array|null $params
     * @param array|string|null $opts
     *
     * @return \Forminator\Stripe\Collection of ApiResources
     */
    public static function all($params = null, $opts = null)
    {
        self::_validateParams($params);
        $url = static::classUrl();

        list($response, $opts) = static::_staticRequest('get', $url, $params, $opts);
        $obj = \Forminator\Stripe\Util\Util::convertToStripeObject($response->json, $opts);
        if (!is_a($obj, 'Forminator\Stripe\\Collection')) {
            $class = get_class($obj);
            $message = "Expected type \"Forminator\Stripe\\Collection\", got \"$class\" instead";
            throw new \Forminator\Stripe\Error\Api($message);
        }
        $obj->setLastResponse($response);
        $obj->setRequestParams($params);
        return $obj;
    }
}
