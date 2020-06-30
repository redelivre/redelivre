<?php

namespace Forminator\PayPal\Api;

use Forminator\PayPal\Common\PayPalModel;

/**
 * Class ChargeModel
 *
 * A resource representing a charge model for a payment definition.
 *
 * @package Forminator\PayPal\Api
 *
 * @property string id
 * @property string type
 * @property \Forminator\PayPal\Api\Currency amount
 */
class ChargeModel extends PayPalModel
{
    /**
     * Identifier of the charge model. 128 characters max.
     *
     * @param string $id
     * 
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Identifier of the charge model. 128 characters max.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Type of charge model. Allowed values: `SHIPPING`, `TAX`.
     *
     * @param string $type
     * 
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Type of charge model. Allowed values: `SHIPPING`, `TAX`.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Specific amount for this charge model.
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
     * Specific amount for this charge model.
     *
     * @return \Forminator\PayPal\Api\Currency
     */
    public function getAmount()
    {
        return $this->amount;
    }

}
