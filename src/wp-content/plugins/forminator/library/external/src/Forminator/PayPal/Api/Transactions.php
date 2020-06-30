<?php

namespace Forminator\PayPal\Api;

use Forminator\PayPal\Common\PayPalModel;

/**
 * Class Transactions
 *
 * 
 *
 * @package Forminator\PayPal\Api
 *
 * @property \Forminator\PayPal\Api\Amount amount
 */
class Transactions extends PayPalModel
{
    /**
     * Amount being collected.
     * 
     *
     * @param \Forminator\PayPal\Api\Amount $amount
     * 
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Amount being collected.
     *
     * @return \Forminator\PayPal\Api\Amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

}
