<?php

declare(strict_types=1);

namespace App\Domain\Identity\Exceptions;

final class IdentifiantsInvalidesException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Identifiants invalides.');
    }
}
