<?php

namespace Forminator\Stripe\Util;

use Forminator\Stripe\StripeObject;

abstract class Util
{
    private static $isMbstringAvailable = null;
    private static $isHashEqualsAvailable = null;

    /**
     * Whether the provided array (or other) is a list rather than a dictionary.
     * A list is defined as an array for which all the keys are consecutive
     * integers starting at 0. Empty arrays are considered to be lists.
     *
     * @param array|mixed $array
     * @return boolean true if the given object is a list.
     */
    public static function isList($array)
    {
        if (!is_array($array)) {
            return false;
        }
        if ($array === []) {
            return true;
        }
        if (array_keys($array) !== range(0, count($array) - 1)) {
            return false;
        }
        return true;
    }

    /**
     * Converts a response from the Stripe API to the corresponding PHP object.
     *
     * @param array $resp The response from the Stripe API.
     * @param array $opts
     * @return StripeObject|array
     */
    public static function convertToStripeObject($resp, $opts)
    {
        $types = [
            // data structures
            \Forminator\Stripe\Collection::OBJECT_NAME => \Forminator\Stripe\Collection::class,

            // business objects
            \Forminator\Stripe\Account::OBJECT_NAME => \Forminator\Stripe\Account::class,
            \Forminator\Stripe\AccountLink::OBJECT_NAME => \Forminator\Stripe\AccountLink::class,
            \Forminator\Stripe\AlipayAccount::OBJECT_NAME => \Forminator\Stripe\AlipayAccount::class,
            \Forminator\Stripe\ApplePayDomain::OBJECT_NAME => \Forminator\Stripe\ApplePayDomain::class,
            \Forminator\Stripe\ApplicationFee::OBJECT_NAME => \Forminator\Stripe\ApplicationFee::class,
            \Forminator\Stripe\ApplicationFeeRefund::OBJECT_NAME => \Forminator\Stripe\ApplicationFeeRefund::class,
            \Forminator\Stripe\Balance::OBJECT_NAME => \Forminator\Stripe\Balance::class,
            \Forminator\Stripe\BalanceTransaction::OBJECT_NAME => \Forminator\Stripe\BalanceTransaction::class,
            \Forminator\Stripe\BankAccount::OBJECT_NAME => \Forminator\Stripe\BankAccount::class,
            \Forminator\Stripe\BitcoinReceiver::OBJECT_NAME => \Forminator\Stripe\BitcoinReceiver::class,
            \Forminator\Stripe\BitcoinTransaction::OBJECT_NAME => \Forminator\Stripe\BitcoinTransaction::class,
            \Forminator\Stripe\Capability::OBJECT_NAME => \Forminator\Stripe\Capability::class,
            \Forminator\Stripe\Card::OBJECT_NAME => \Forminator\Stripe\Card::class,
            \Forminator\Stripe\Charge::OBJECT_NAME => \Forminator\Stripe\Charge::class,
            \Forminator\Stripe\Checkout\Session::OBJECT_NAME => \Forminator\Stripe\Checkout\Session::class,
            \Forminator\Stripe\CountrySpec::OBJECT_NAME => \Forminator\Stripe\CountrySpec::class,
            \Forminator\Stripe\Coupon::OBJECT_NAME => \Forminator\Stripe\Coupon::class,
            \Forminator\Stripe\CreditNote::OBJECT_NAME => \Forminator\Stripe\CreditNote::class,
            \Forminator\Stripe\CreditNoteLineItem::OBJECT_NAME => \Forminator\Stripe\CreditNoteLineItem::class,
            \Forminator\Stripe\Customer::OBJECT_NAME => \Forminator\Stripe\Customer::class,
            \Forminator\Stripe\CustomerBalanceTransaction::OBJECT_NAME => \Forminator\Stripe\CustomerBalanceTransaction::class,
            \Forminator\Stripe\Discount::OBJECT_NAME => \Forminator\Stripe\Discount::class,
            \Forminator\Stripe\Dispute::OBJECT_NAME => \Forminator\Stripe\Dispute::class,
            \Forminator\Stripe\EphemeralKey::OBJECT_NAME => \Forminator\Stripe\EphemeralKey::class,
            \Forminator\Stripe\Event::OBJECT_NAME => \Forminator\Stripe\Event::class,
            \Forminator\Stripe\ExchangeRate::OBJECT_NAME => \Forminator\Stripe\ExchangeRate::class,
            \Forminator\Stripe\File::OBJECT_NAME => \Forminator\Stripe\File::class,
            \Forminator\Stripe\File::OBJECT_NAME_ALT => \Forminator\Stripe\File::class,
            \Forminator\Stripe\FileLink::OBJECT_NAME => \Forminator\Stripe\FileLink::class,
            \Forminator\Stripe\Invoice::OBJECT_NAME => \Forminator\Stripe\Invoice::class,
            \Forminator\Stripe\InvoiceItem::OBJECT_NAME => \Forminator\Stripe\InvoiceItem::class,
            \Forminator\Stripe\InvoiceLineItem::OBJECT_NAME => \Forminator\Stripe\InvoiceLineItem::class,
            \Forminator\Stripe\Issuing\Authorization::OBJECT_NAME => \Forminator\Stripe\Issuing\Authorization::class,
            \Forminator\Stripe\Issuing\Card::OBJECT_NAME => \Forminator\Stripe\Issuing\Card::class,
            \Forminator\Stripe\Issuing\CardDetails::OBJECT_NAME => \Forminator\Stripe\Issuing\CardDetails::class,
            \Forminator\Stripe\Issuing\Cardholder::OBJECT_NAME => \Forminator\Stripe\Issuing\Cardholder::class,
            \Forminator\Stripe\Issuing\Dispute::OBJECT_NAME => \Forminator\Stripe\Issuing\Dispute::class,
            \Forminator\Stripe\Issuing\Transaction::OBJECT_NAME => \Forminator\Stripe\Issuing\Transaction::class,
            \Forminator\Stripe\LoginLink::OBJECT_NAME => \Forminator\Stripe\LoginLink::class,
            \Forminator\Stripe\Mandate::OBJECT_NAME => \Forminator\Stripe\Mandate::class,
            \Forminator\Stripe\Order::OBJECT_NAME => \Forminator\Stripe\Order::class,
            \Forminator\Stripe\OrderItem::OBJECT_NAME => \Forminator\Stripe\OrderItem::class,
            \Forminator\Stripe\OrderReturn::OBJECT_NAME => \Forminator\Stripe\OrderReturn::class,
            \Forminator\Stripe\PaymentIntent::OBJECT_NAME => \Forminator\Stripe\PaymentIntent::class,
            \Forminator\Stripe\PaymentMethod::OBJECT_NAME => \Forminator\Stripe\PaymentMethod::class,
            \Forminator\Stripe\Payout::OBJECT_NAME => \Forminator\Stripe\Payout::class,
            \Forminator\Stripe\Person::OBJECT_NAME => \Forminator\Stripe\Person::class,
            \Forminator\Stripe\Plan::OBJECT_NAME => \Forminator\Stripe\Plan::class,
            \Forminator\Stripe\Product::OBJECT_NAME => \Forminator\Stripe\Product::class,
            \Forminator\Stripe\Radar\EarlyFraudWarning::OBJECT_NAME => \Forminator\Stripe\Radar\EarlyFraudWarning::class,
            \Forminator\Stripe\Radar\ValueList::OBJECT_NAME => \Forminator\Stripe\Radar\ValueList::class,
            \Forminator\Stripe\Radar\ValueListItem::OBJECT_NAME => \Forminator\Stripe\Radar\ValueListItem::class,
            \Forminator\Stripe\Recipient::OBJECT_NAME => \Forminator\Stripe\Recipient::class,
            \Forminator\Stripe\RecipientTransfer::OBJECT_NAME => \Forminator\Stripe\RecipientTransfer::class,
            \Forminator\Stripe\Refund::OBJECT_NAME => \Forminator\Stripe\Refund::class,
            \Forminator\Stripe\Reporting\ReportRun::OBJECT_NAME => \Forminator\Stripe\Reporting\ReportRun::class,
            \Forminator\Stripe\Reporting\ReportType::OBJECT_NAME => \Forminator\Stripe\Reporting\ReportType::class,
            \Forminator\Stripe\Review::OBJECT_NAME => \Forminator\Stripe\Review::class,
            \Forminator\Stripe\SetupIntent::OBJECT_NAME => \Forminator\Stripe\SetupIntent::class,
            \Forminator\Stripe\Sigma\ScheduledQueryRun::OBJECT_NAME => \Forminator\Stripe\Sigma\ScheduledQueryRun::class,
            \Forminator\Stripe\SKU::OBJECT_NAME => \Forminator\Stripe\SKU::class,
            \Forminator\Stripe\Source::OBJECT_NAME => \Forminator\Stripe\Source::class,
            \Forminator\Stripe\SourceTransaction::OBJECT_NAME => \Forminator\Stripe\SourceTransaction::class,
            \Forminator\Stripe\Subscription::OBJECT_NAME => \Forminator\Stripe\Subscription::class,
            \Forminator\Stripe\SubscriptionItem::OBJECT_NAME => \Forminator\Stripe\SubscriptionItem::class,
            \Forminator\Stripe\SubscriptionSchedule::OBJECT_NAME => \Forminator\Stripe\SubscriptionSchedule::class,
            \Forminator\Stripe\TaxId::OBJECT_NAME => \Forminator\Stripe\TaxId::class,
            \Forminator\Stripe\TaxRate::OBJECT_NAME => \Forminator\Stripe\TaxRate::class,
            \Forminator\Stripe\ThreeDSecure::OBJECT_NAME => \Forminator\Stripe\ThreeDSecure::class,
            \Forminator\Stripe\Terminal\ConnectionToken::OBJECT_NAME => \Forminator\Stripe\Terminal\ConnectionToken::class,
            \Forminator\Stripe\Terminal\Location::OBJECT_NAME => \Forminator\Stripe\Terminal\Location::class,
            \Forminator\Stripe\Terminal\Reader::OBJECT_NAME => \Forminator\Stripe\Terminal\Reader::class,
            \Forminator\Stripe\Token::OBJECT_NAME => \Forminator\Stripe\Token::class,
            \Forminator\Stripe\Topup::OBJECT_NAME => \Forminator\Stripe\Topup::class,
            \Forminator\Stripe\Transfer::OBJECT_NAME => \Forminator\Stripe\Transfer::class,
            \Forminator\Stripe\TransferReversal::OBJECT_NAME => \Forminator\Stripe\TransferReversal::class,
            \Forminator\Stripe\UsageRecord::OBJECT_NAME => \Forminator\Stripe\UsageRecord::class,
            \Forminator\Stripe\UsageRecordSummary::OBJECT_NAME => \Forminator\Stripe\UsageRecordSummary::class,
            \Forminator\Stripe\WebhookEndpoint::OBJECT_NAME => \Forminator\Stripe\WebhookEndpoint::class,
        ];
        if (self::isList($resp)) {
            $mapped = [];
            foreach ($resp as $i) {
                array_push($mapped, self::convertToStripeObject($i, $opts));
            }
            return $mapped;
        } elseif (is_array($resp)) {
            if (isset($resp['object']) && is_string($resp['object']) && isset($types[$resp['object']])) {
                $class = $types[$resp['object']];
            } else {
                $class = \Forminator\Stripe\StripeObject::class;
            }
            return $class::constructFrom($resp, $opts);
        } else {
            return $resp;
        }
    }

    /**
     * @param string|mixed $value A string to UTF8-encode.
     *
     * @return string|mixed The UTF8-encoded string, or the object passed in if
     *    it wasn't a string.
     */
    public static function utf8($value)
    {
        if (self::$isMbstringAvailable === null) {
            self::$isMbstringAvailable = function_exists('mb_detect_encoding');

            if (!self::$isMbstringAvailable) {
                trigger_error("It looks like the mbstring extension is not enabled. " .
                    "UTF-8 strings will not properly be encoded. Ask your system " .
                    "administrator to enable the mbstring extension, or write to " .
                    "support@stripe.com if you have any questions.", E_USER_WARNING);
            }
        }

        if (is_string($value) && self::$isMbstringAvailable && mb_detect_encoding($value, "UTF-8", true) != "UTF-8") {
            return utf8_encode($value);
        } else {
            return $value;
        }
    }

    /**
     * Compares two strings for equality. The time taken is independent of the
     * number of characters that match.
     *
     * @param string $a one of the strings to compare.
     * @param string $b the other string to compare.
     * @return bool true if the strings are equal, false otherwise.
     */
    public static function secureCompare($a, $b)
    {
        if (self::$isHashEqualsAvailable === null) {
            self::$isHashEqualsAvailable = function_exists('hash_equals');
        }

        if (self::$isHashEqualsAvailable) {
            return hash_equals($a, $b);
        } else {
            if (strlen($a) != strlen($b)) {
                return false;
            }

            $result = 0;
            for ($i = 0; $i < strlen($a); $i++) {
                $result |= ord($a[$i]) ^ ord($b[$i]);
            }
            return ($result == 0);
        }
    }

    /**
     * Recursively goes through an array of parameters. If a parameter is an instance of
     * ApiResource, then it is replaced by the resource's ID.
     * Also clears out null values.
     *
     * @param mixed $h
     * @return mixed
     */
    public static function objectsToIds($h)
    {
        if ($h instanceof \Forminator\Stripe\ApiResource) {
            return $h->id;
        } elseif (static::isList($h)) {
            $results = [];
            foreach ($h as $v) {
                array_push($results, static::objectsToIds($v));
            }
            return $results;
        } elseif (is_array($h)) {
            $results = [];
            foreach ($h as $k => $v) {
                if (is_null($v)) {
                    continue;
                }
                $results[$k] = static::objectsToIds($v);
            }
            return $results;
        } else {
            return $h;
        }
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public static function encodeParameters($params)
    {
        $flattenedParams = self::flattenParams($params);
        $pieces = [];
        foreach ($flattenedParams as $param) {
            list($k, $v) = $param;
            array_push($pieces, self::urlEncode($k) . '=' . self::urlEncode($v));
        }
        return implode('&', $pieces);
    }

    /**
     * @param array $params
     * @param string|null $parentKey
     *
     * @return array
     */
    public static function flattenParams($params, $parentKey = null)
    {
        $result = [];

        foreach ($params as $key => $value) {
            $calculatedKey = $parentKey ? "{$parentKey}[{$key}]" : $key;

            if (self::isList($value)) {
                $result = array_merge($result, self::flattenParamsList($value, $calculatedKey));
            } elseif (is_array($value)) {
                $result = array_merge($result, self::flattenParams($value, $calculatedKey));
            } else {
                array_push($result, [$calculatedKey, $value]);
            }
        }

        return $result;
    }

    /**
     * @param array $value
     * @param string $calculatedKey
     *
     * @return array
     */
    public static function flattenParamsList($value, $calculatedKey)
    {
        $result = [];

        foreach ($value as $i => $elem) {
            if (self::isList($elem)) {
                $result = array_merge($result, self::flattenParamsList($elem, $calculatedKey));
            } elseif (is_array($elem)) {
                $result = array_merge($result, self::flattenParams($elem, "{$calculatedKey}[{$i}]"));
            } else {
                array_push($result, ["{$calculatedKey}[{$i}]", $elem]);
            }
        }

        return $result;
    }

    /**
     * @param string $key A string to URL-encode.
     *
     * @return string The URL-encoded string.
     */
    public static function urlEncode($key)
    {
        $s = urlencode($key);

        // Don't use strict form encoding by changing the square bracket control
        // characters back to their literals. This is fine by the server, and
        // makes these parameter strings easier to read.
        $s = str_replace('%5B', '[', $s);
        $s = str_replace('%5D', ']', $s);

        return $s;
    }

    public static function normalizeId($id)
    {
        if (is_array($id)) {
            $params = $id;
            $id = $params['id'];
            unset($params['id']);
        } else {
            $params = [];
        }
        return [$id, $params];
    }

    /**
     * Returns UNIX timestamp in milliseconds
     *
     * @return integer current time in millis
     */
    public static function currentTimeMillis()
    {
        return (int) round(microtime(true) * 1000);
    }
}
