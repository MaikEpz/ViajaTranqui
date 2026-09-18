<?php

namespace App\Domain\Exceptions;

use DomainException;

class InvalidTravelDatesException extends DomainException
{
    public function __construct(string $message = 'La fecha de regreso no puede ser anterior a la fecha de salida.')
    {
        parent::__construct($message, 422);
    }
}
