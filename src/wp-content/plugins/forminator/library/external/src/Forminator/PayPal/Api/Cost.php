<?php

namespace Forminator\PayPal\Api;

use Forminator\PayPal\Common\PayPalModel;
use Forminator\PayPal\Converter\FormatConverter;
use Forminator\PayPal\Validation\NumericValidator;

/**
 * Class Cost
 *
 * Cost as a percent or an amount. For example, to specify 10%, enter `10`. Alternatively, to specify an amount of 5, enter `5`.
 *
 * @package Forminator\PayPal\Api
 *
 * @property string percent
 * @property \Forminator\PayPal\Api\Currency amount
 */
class Cost extends PayPalModel
{
    /**
     * Cost in percent. Range of 0 to 100.
     *
     * @param string $percent
     * 
     * @return $this
     */
    public function setPercent($percent)
    {
        NumericValidator::validate($percent, "Percent");
        $percent = FormatConverter::formatToNumber($percent);
        $this->percent = $percent;
        return $this;
    }

    /**
     * Cost in percent. Range of 0 to 100.
     *
     * @return string
     */
    public function getPercent()
    {
        return $this->percent;
    }

    /**
     * The cost, as an amount. Valid range is from 0 to 1,000,000.
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
     * The cost, as an amount. Valid range is from 0 to 1,000,000.
     *
     * @return \Forminator\PayPal\Api\Currency
     */
    public function getAmount()
    {
        return $this->amount;
    }

}
