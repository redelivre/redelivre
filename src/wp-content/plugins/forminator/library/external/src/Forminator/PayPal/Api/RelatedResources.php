<?php

namespace Forminator\PayPal\Api;

use Forminator\PayPal\Common\PayPalModel;

/**
 * Class RelatedResources
 *
 * Each one representing a financial transaction (Sale, Authorization, Capture, Refund) related to the payment.
 *
 * @package Forminator\PayPal\Api
 *
 * @property \Forminator\PayPal\Api\Sale sale
 * @property \Forminator\PayPal\Api\Authorization authorization
 * @property \Forminator\PayPal\Api\Order order
 * @property \Forminator\PayPal\Api\Capture capture
 * @property \Forminator\PayPal\Api\Refund refund
 */
class RelatedResources extends PayPalModel
{
    /**
     * Sale transaction
     *
     * @param \Forminator\PayPal\Api\Sale $sale
     * 
     * @return $this
     */
    public function setSale($sale)
    {
        $this->sale = $sale;
        return $this;
    }

    /**
     * Sale transaction
     *
     * @return \Forminator\PayPal\Api\Sale
     */
    public function getSale()
    {
        return $this->sale;
    }

    /**
     * Authorization transaction
     *
     * @param \Forminator\PayPal\Api\Authorization $authorization
     * 
     * @return $this
     */
    public function setAuthorization($authorization)
    {
        $this->authorization = $authorization;
        return $this;
    }

    /**
     * Authorization transaction
     *
     * @return \Forminator\PayPal\Api\Authorization
     */
    public function getAuthorization()
    {
        return $this->authorization;
    }

    /**
     * Order transaction
     *
     * @param \Forminator\PayPal\Api\Order $order
     * 
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Order transaction
     *
     * @return \Forminator\PayPal\Api\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Capture transaction
     *
     * @param \Forminator\PayPal\Api\Capture $capture
     * 
     * @return $this
     */
    public function setCapture($capture)
    {
        $this->capture = $capture;
        return $this;
    }

    /**
     * Capture transaction
     *
     * @return \Forminator\PayPal\Api\Capture
     */
    public function getCapture()
    {
        return $this->capture;
    }

    /**
     * Refund transaction
     *
     * @param \Forminator\PayPal\Api\Refund $refund
     * 
     * @return $this
     */
    public function setRefund($refund)
    {
        $this->refund = $refund;
        return $this;
    }

    /**
     * Refund transaction
     *
     * @return \Forminator\PayPal\Api\Refund
     */
    public function getRefund()
    {
        return $this->refund;
    }

}
