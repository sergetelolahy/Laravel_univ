<?php

declare(strict_types=1);

namespace App\Domain\Scolarite\Exceptions;

final class AnneeNonCouranteException extends DomainException
{
    public function __construct()
    {
        parent::__construct('L\'inscription doit se faire pour l\'année universitaire courante.');
    }
}
