<?php

namespace Padam87\SimplePayBundle\Exception;

class UnknownTransactionException extends SimplePayException
{
    public function __construct($id)
    {
        $message = sprintf('[SimplePay] Unknown transaction: %s', $id);

        parent::__construct($message);
    }
}
