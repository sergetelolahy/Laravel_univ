<?php

declare(strict_types=1);

namespace App\Application\Identity\Command\DesactiverUtilisateur;

use App\Domain\Identity\Exceptions\UtilisateurIntrouvableException;
use App\Domain\Identity\Repositories\UtilisateurRepository;
use App\Domain\Identity\Services\TokenService;

final class DesactiverUtilisateur
{
    public function __construct(
        private readonly UtilisateurRepository $utilisateurRepository,
        private readonly TokenService $tokenService,
    ) {
    }

    public function execute(int $id): void
    {
        $utilisateur = $this->utilisateurRepository->trouverParId($id);

        if ($utilisateur === null) {
            throw new UtilisateurIntrouvableException();
        }

        $this->utilisateurRepository->desactiver($id);
        $this->tokenService->revoquerTous($id);
    }
}
