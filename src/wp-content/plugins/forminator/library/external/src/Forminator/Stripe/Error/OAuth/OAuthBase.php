<?php

namespace Forminator\Stripe\Error\OAuth;

class OAuthBase extends \Forminator\Stripe\Error\Base
{
    public function __construct(
        $code,
        $description,
        $httpStatus = null,
        $httpBody = null,
        $jsonBody = null,
        $httpHeaders = null
    ) {
        parent::__construct($description, $httpStatus, $httpBody, $jsonBody, $httpHeaders);
        $this->errorCode = $code;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }
}
