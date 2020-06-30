<?php

namespace Forminator\PayPal\Api;

use Forminator\PayPal\Common\PayPalModel;

/**
 * Class InvoiceNumber
 *
 * The next invoice number
 *
 * @package Forminator\PayPal\Api
 *
 * @property string number
 */
class InvoiceNumber extends PayPalModel
{
    /**
     * The next invoice number.
     *
     * @param string $number
     *
     * @return $this
     */
    public function setNumber($number) {
        $this->number = $number;
        return $this;
    }

    /**
     * The next invoice number.
     *
     * @return string
     */
    public function getNumber() {
        return $this->number;
    }
}
