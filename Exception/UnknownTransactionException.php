<?php

namespace Padam87\SimplePayBundle\Exception;

class UnknownTransactionException extends SimplePayException
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;

        $message = sprintf('[SimplePay] Unknown transaction: %s', $id);

        parent::__construct($message);
    }
}
