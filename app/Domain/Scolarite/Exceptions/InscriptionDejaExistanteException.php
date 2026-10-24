<?php

declare(strict_types=1);

namespace App\Domain\Scolarite\Exceptions;

final class InscriptionDejaExistanteException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Cette inscription existe déjà pour l\'étudiant, le cours et l\'année.');
    }
}
