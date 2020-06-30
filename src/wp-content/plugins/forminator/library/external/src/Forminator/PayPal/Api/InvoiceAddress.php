<?php

namespace Forminator\PayPal\Api;

/**
 * Class InvoiceAddress
 *
 * Base Address object used as billing address in a payment or extended for Shipping Address.
 *
 * @package Forminator\PayPal\Api
 *
 * @property \Forminator\PayPal\Api\Phone phone
 */
class InvoiceAddress extends BaseAddress
{
    /**
     * Phone number in E.123 format.
     *
     * @param \Forminator\PayPal\Api\Phone $phone
     * 
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * Phone number in E.123 format.
     *
     * @return \Forminator\PayPal\Api\Phone
     */
    public function getPhone()
    {
        return $this->phone;
    }

}
