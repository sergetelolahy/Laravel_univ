<?php

declare(strict_types=1);

namespace App\Domain\Identity\Exceptions;

final class UtilisateurIntrouvableException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Utilisateur introuvable.');
    }
}
