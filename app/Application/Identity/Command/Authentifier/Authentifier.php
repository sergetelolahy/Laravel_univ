<?php

declare(strict_types=1);

namespace App\Application\Identity\Command\Authentifier;

use App\Domain\Identity\Entities\Utilisateur;
use App\Domain\Identity\Exceptions\CompteInactifException;
use App\Domain\Identity\Exceptions\IdentifiantsInvalidesException;
use App\Domain\Identity\Repositories\UtilisateurRepository;
use App\Domain\Identity\Services\PasswordHasher;
use App\Domain\Identity\Services\TokenService;

final class Authentifier
{
    public function __construct(
        private readonly UtilisateurRepository $utilisateurRepository,
        private readonly PasswordHasher $passwordHasher,
        private readonly TokenService $tokenService,
    ) {
    }

    /**
     * @return array{token: string, utilisateur: Utilisateur}
     */
    public function execute(string $email, string $motDePasse): array
    {
        $utilisateur = $this->utilisateurRepository->trouverParEmail($email);

        if ($utilisateur === null || ! $this->passwordHasher->verifier($motDePasse, $utilisateur->motDePasseHash)) {
            throw new IdentifiantsInvalidesException();
        }

        if (! $utilisateur->actif) {
            throw new CompteInactifException();
        }

        $this->utilisateurRepository->enregistrerConnexion($utilisateur->id);

        return [
            'token' => $this->tokenService->emettre($utilisateur->id),
            'utilisateur' => $utilisateur,
        ];
    }
}
