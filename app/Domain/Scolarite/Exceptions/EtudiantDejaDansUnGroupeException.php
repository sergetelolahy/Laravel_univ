<?php

declare(strict_types=1);

namespace App\Domain\Scolarite\Exceptions;

final class EtudiantDejaDansUnGroupeException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Cet étudiant est déjà inscrit dans un groupe pour cette année.');
    }
}
