<?php

namespace Forminator\PayPal\Api;

use Forminator\PayPal\Common\PayPalModel;

/**
 * Class PaymentSummary
 *
 * Payment/Refund break up
 *
 * @package Forminator\PayPal\Api
 *
 * @property \Forminator\PayPal\Api\Currency paypal
 * @property \Forminator\PayPal\Api\Currency other
 */
class PaymentSummary extends PayPalModel
{
    /**
     * Total Amount paid/refunded via PayPal.
     *
     * @param \Forminator\PayPal\Api\Currency $paypal
     * 
     * @return $this
     */
    public function setPaypal($paypal)
    {
        $this->paypal = $paypal;
        return $this;
    }

    /**
     * Total Amount paid/refunded via PayPal.
     *
     * @return \Forminator\PayPal\Api\Currency
     */
    public function getPaypal()
    {
        return $this->paypal;
    }

    /**
     * Total Amount paid/refunded via other sources.
     *
     * @param \Forminator\PayPal\Api\Currency $other
     * 
     * @return $this
     */
    public function setOther($other)
    {
        $this->other = $other;
        return $this;
    }

    /**
     * Total Amount paid/refunded via other sources.
     *
     * @return \Forminator\PayPal\Api\Currency
     */
    public function getOther()
    {
        return $this->other;
    }

}
