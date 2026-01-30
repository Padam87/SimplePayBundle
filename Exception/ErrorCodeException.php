<?php

namespace Padam87\SimplePayBundle\Exception;

class ErrorCodeException extends SimplePayException
{
    public function __construct(array $codes)
    {
        $message = sprintf('[SimplePay] Error codes: %s', implode(', ', $codes));

        parent::__construct($message);
    }
}
