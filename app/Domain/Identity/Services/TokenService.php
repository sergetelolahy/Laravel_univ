<?php

declare(strict_types=1);

namespace App\Domain\Identity\Services;

interface TokenService
{
    public function emettre(int $utilisateurId): string;

    public function revoquerCourant(): void;

    public function revoquerTous(int $utilisateurId): void;
}
