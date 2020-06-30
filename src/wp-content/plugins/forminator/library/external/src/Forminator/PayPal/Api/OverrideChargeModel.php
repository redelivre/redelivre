<?php

namespace Forminator\PayPal\Api;

use Forminator\PayPal\Common\PayPalModel;

/**
 * Class OverrideChargeModel
 *
 * A resource representing an override_charge_model to be used during creation of the agreement.
 *
 * @package Forminator\PayPal\Api
 *
 * @property string charge_id
 * @property \Forminator\PayPal\Api\Currency amount
 */
class OverrideChargeModel extends PayPalModel
{
    /**
     * ID of charge model.
     *
     * @param string $charge_id
     * 
     * @return $this
     */
    public function setChargeId($charge_id)
    {
        $this->charge_id = $charge_id;
        return $this;
    }

    /**
     * ID of charge model.
     *
     * @return string
     */
    public function getChargeId()
    {
        return $this->charge_id;
    }

    /**
     * Updated Amount to be associated with this charge model.
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
     * Updated Amount to be associated with this charge model.
     *
     * @return \Forminator\PayPal\Api\Currency
     */
    public function getAmount()
    {
        return $this->amount;
    }

}
