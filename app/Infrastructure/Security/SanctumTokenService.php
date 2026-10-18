<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\Identity\Services\TokenService;
use App\Infrastructure\Persistence\Eloquent\Models\Utilisateur;
use Laravel\Sanctum\PersonalAccessToken;

final class SanctumTokenService implements TokenService
{
    public function emettre(int $utilisateurId): string
    {
        $utilisateur = Utilisateur::query()->findOrFail($utilisateurId);

        return $utilisateur->createToken('auth-token')->plainTextToken;
    }

    public function revoquerCourant(): void
    {
        $utilisateur = request()->user('sanctum');

        if (! $utilisateur instanceof Utilisateur) {
            return;
        }

        $token = $utilisateur->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
    }

    public function revoquerTous(int $utilisateurId): void
    {
        Utilisateur::query()->find($utilisateurId)?->tokens()->delete();
    }
}
