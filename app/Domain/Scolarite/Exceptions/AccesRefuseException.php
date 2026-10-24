<?php

declare(strict_types=1);

namespace App\Domain\Scolarite\Exceptions;

final class AccesRefuseException extends DomainException
{
    public function __construct(string $message = 'Accès refusé.')
    {
        parent::__construct($message);
    }
}
