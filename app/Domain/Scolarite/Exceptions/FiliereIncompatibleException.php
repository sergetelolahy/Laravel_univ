<?php

declare(strict_types=1);

namespace App\Domain\Scolarite\Exceptions;

final class FiliereIncompatibleException extends DomainException
{
    public function __construct()
    {
        parent::__construct('L’étudiant doit être affecté à une filière et le cours doit appartenir à cette filière.');
    }
}
