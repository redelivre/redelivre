<?php

namespace Forminator\PayPal\Api;


/**
 * Class DetailedRefund
 *
 * A refund transaction. This is the resource that is returned on GET /refund
 *
 * @package Forminator\PayPal\Api
 *
 * @property string custom
 * @property \Forminator\PayPal\Api\Currency refund_to_payer
 * @property \Forminator\PayPal\Api\ExternalFunding[] refund_to_external_funding
 * @property \Forminator\PayPal\Api\Currency refund_from_transaction_fee
 * @property \Forminator\PayPal\Api\Currency refund_from_received_amount
 * @property \Forminator\PayPal\Api\Currency total_refunded_amount
 */
class DetailedRefund extends Refund
{
    /**
     * free-form field for the use of clients
     *
     * @param string $custom
     * 
     * @return $this
     */
    public function setCustom($custom)
    {
        $this->custom = $custom;
        return $this;
    }

    /**
     * free-form field for the use of clients
     *
     * @return string
     */
    public function getCustom()
    {
        return $this->custom;
    }

    /**
     * Amount refunded to payer of the original transaction, in the current Refund call
     *
     * @param \Forminator\PayPal\Api\Currency $refund_to_payer
     * 
     * @return $this
     */
    public function setRefundToPayer($refund_to_payer)
    {
        $this->refund_to_payer = $refund_to_payer;
        return $this;
    }

    /**
     * Amount refunded to payer of the original transaction, in the current Refund call
     *
     * @return \Forminator\PayPal\Api\Currency
     */
    public function getRefundToPayer()
    {
        return $this->refund_to_payer;
    }

    /**
     * List of external funding that were refunded by the Refund call. Each external_funding unit should have a unique reference_id
     *
     * @param \Forminator\PayPal\Api\ExternalFunding[] $refund_to_external_funding
     * 
     * @return $this
     */
    public function setRefundToExternalFunding($refund_to_external_funding)
    {
        $this->refund_to_external_funding = $refund_to_external_funding;
        return $this;
    }

    /**
     * List of external funding that were refunded by the Refund call. Each external_funding unit should have a unique reference_id
     *
     * @return \Forminator\PayPal\Api\ExternalFunding[]
     */
    public function getRefundToExternalFunding()
    {
        return $this->refund_to_external_funding;
    }

    /**
     * Transaction fee refunded to original recipient of payment.
     *
     * @param \Forminator\PayPal\Api\Currency $refund_from_transaction_fee
     * 
     * @return $this
     */
    public function setRefundFromTransactionFee($refund_from_transaction_fee)
    {
        $this->refund_from_transaction_fee = $refund_from_transaction_fee;
        return $this;
    }

    /**
     * Transaction fee refunded to original recipient of payment.
     *
     * @return \Forminator\PayPal\Api\Currency
     */
    public function getRefundFromTransactionFee()
    {
        return $this->refund_from_transaction_fee;
    }

    /**
     * Amount subtracted from PayPal balance of the original recipient of payment, to make this refund.
     *
     * @param \Forminator\PayPal\Api\Currency $refund_from_received_amount
     * 
     * @return $this
     */
    public function setRefundFromReceivedAmount($refund_from_received_amount)
    {
        $this->refund_from_received_amount = $refund_from_received_amount;
        return $this;
    }

    /**
     * Amount subtracted from PayPal balance of the original recipient of payment, to make this refund.
     *
     * @return \Forminator\PayPal\Api\Currency
     */
    public function getRefundFromReceivedAmount()
    {
        return $this->refund_from_received_amount;
    }

    /**
     * Total amount refunded so far from the original purchase. Say, for example, a buyer makes $100 purchase, the buyer was refunded $20 a week ago and is refunded $30 in this transaction. The gross refund amount is $30 (in this transaction). The total refunded amount is $50.
     *
     * @param \Forminator\PayPal\Api\Currency $total_refunded_amount
     * 
     * @return $this
     */
    public function setTotalRefundedAmount($total_refunded_amount)
    {
        $this->total_refunded_amount = $total_refunded_amount;
        return $this;
    }

    /**
     * Total amount refunded so far from the original purchase. Say, for example, a buyer makes $100 purchase, the buyer was refunded $20 a week ago and is refunded $30 in this transaction. The gross refund amount is $30 (in this transaction). The total refunded amount is $50.
     *
     * @return \Forminator\PayPal\Api\Currency
     */
    public function getTotalRefundedAmount()
    {
        return $this->total_refunded_amount;
    }

}
