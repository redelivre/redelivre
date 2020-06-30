<?php

namespace Forminator\PayPal\Api;

use Forminator\PayPal\Common\PayPalModel;

/**
 * Class InstallmentOption
 *
 *  A resource describing an installment
 *
 * @package Forminator\PayPal\Api
 *
 * @property int term
 * @property \Forminator\PayPal\Api\Currency monthly_payment
 * @property \Forminator\PayPal\Api\Currency discount_amount
 * @property string discount_percentage
 */
class InstallmentOption extends PayPalModel
{
    /**
     * Number of installments
     *
     * @param int $term
     * 
     * @return $this
     */
    public function setTerm($term)
    {
        $this->term = $term;
        return $this;
    }

    /**
     * Number of installments
     *
     * @return int
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * Monthly payment
     *
     * @param \Forminator\PayPal\Api\Currency $monthly_payment
     * 
     * @return $this
     */
    public function setMonthlyPayment($monthly_payment)
    {
        $this->monthly_payment = $monthly_payment;
        return $this;
    }

    /**
     * Monthly payment
     *
     * @return \Forminator\PayPal\Api\Currency
     */
    public function getMonthlyPayment()
    {
        return $this->monthly_payment;
    }

    /**
     * Discount amount applied to the payment, if any
     *
     * @param \Forminator\PayPal\Api\Currency $discount_amount
     * 
     * @return $this
     */
    public function setDiscountAmount($discount_amount)
    {
        $this->discount_amount = $discount_amount;
        return $this;
    }

    /**
     * Discount amount applied to the payment, if any
     *
     * @return \Forminator\PayPal\Api\Currency
     */
    public function getDiscountAmount()
    {
        return $this->discount_amount;
    }

    /**
     * Discount percentage applied to the payment, if any
     *
     * @param string $discount_percentage
     * 
     * @return $this
     */
    public function setDiscountPercentage($discount_percentage)
    {
        $this->discount_percentage = $discount_percentage;
        return $this;
    }

    /**
     * Discount percentage applied to the payment, if any
     *
     * @return string
     */
    public function getDiscountPercentage()
    {
        return $this->discount_percentage;
    }

}
