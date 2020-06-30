<?php

namespace Forminator\PayPal\Api;

/**
 * Class TransactionBase
 *
 * A transaction defines the contract of a payment - what is the payment for and who is fulfilling it.
 *
 * @package Forminator\PayPal\Api
 *
 * @property \Forminator\PayPal\Api\RelatedResources related_resources
 */
class TransactionBase extends CartBase 
{
    /**
     * List of financial transactions (Sale, Authorization, Capture, Refund) related to the payment.
     * 
     *
     * @param \Forminator\PayPal\Api\RelatedResources[] $related_resources
     * 
     * @return $this
     */
    public function setRelatedResources($related_resources)
    {
        $this->related_resources = $related_resources;
        return $this;
    }

    /**
     * List of financial transactions (Sale, Authorization, Capture, Refund) related to the payment.
     *
     * @return \Forminator\PayPal\Api\RelatedResources[]
     */
    public function getRelatedResources()
    {
        return $this->related_resources;
    }

}
