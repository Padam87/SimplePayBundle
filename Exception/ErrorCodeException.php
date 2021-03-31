<?php

namespace Padam87\SimplePayBundle\Exception;

class ErrorCodeException extends SimplePayException
{
    private array $codes;

    public function __construct(array $codes)
    {
        $this->codes = $codes;

        $message = sprintf('[SimplePay] Error codes: %s', implode(', ', $codes));

        parent::__construct($message);
    }
}
