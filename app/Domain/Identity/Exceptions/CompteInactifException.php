<?php

declare(strict_types=1);

namespace App\Domain\Identity\Exceptions;

final class CompteInactifException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Le compte est inactif.');
    }
}
