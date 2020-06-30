<?php

namespace Forminator\PayPal\Api;

use Forminator\PayPal\Common\PayPalModel;

/**
 * Class AgreementStateDescriptor
 *
 * Description of the current state of the agreement.
 *
 * @package Forminator\PayPal\Api
 *
 * @property string note
 * @property \Forminator\PayPal\Api\Currency amount
 */
class AgreementStateDescriptor extends PayPalModel
{
    /**
     * Reason for changing the state of the agreement.
     *
     * @param string $note
     * 
     * @return $this
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * Reason for changing the state of the agreement.
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * The amount and currency of the agreement.
     *
     * @param \Forminator\PayPal\Api\Currency $amount
     * 
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * The amount and currency of the agreement.
     *
     * @return \Forminator\PayPal\Api\Currency
     */
    public function getAmount()
    {
        return $this->amount;
    }

}
