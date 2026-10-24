<?php

declare(strict_types=1);

namespace App\Domain\Scolarite\Exceptions;

final class FiliereIncompatibleException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Le cours ne correspond pas à la filière de l\'étudiant.');
    }
}
