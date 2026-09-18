<?php

namespace App\Domain\Exceptions;

use DomainException;

class QuotationAlreadyContractedException extends DomainException
{
    public function __construct(string $message = 'Este seguro ya ha sido contratado previamente.')
    {
        parent::__construct($message, 422);
    }
}
