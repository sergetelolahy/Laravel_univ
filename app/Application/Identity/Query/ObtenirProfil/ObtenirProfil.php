<?php

declare(strict_types=1);

namespace App\Application\Identity\Query\ObtenirProfil;

use App\Domain\Identity\Entities\Utilisateur;
use App\Domain\Identity\Exceptions\UtilisateurIntrouvableException;
use App\Domain\Identity\Repositories\UtilisateurRepository;

final class ObtenirProfil
{
    public function __construct(private readonly UtilisateurRepository $utilisateurRepository)
    {
    }

    public function execute(int $utilisateurId): Utilisateur
    {
        $utilisateur = $this->utilisateurRepository->trouverParId($utilisateurId);

        if ($utilisateur === null) {
            throw new UtilisateurIntrouvableException();
        }

        return $utilisateur;
    }
}
