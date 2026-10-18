<?php

declare(strict_types=1);

namespace App\Domain\Identity\Exceptions;

final class EmailDejaUtiliseException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Cet email est déjà utilisé.');
    }
}
