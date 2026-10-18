<?php

declare(strict_types=1);

namespace App\Domain\Identity\Services;

interface PasswordHasher
{
    public function hacher(string $motDePasse): string;

    public function verifier(string $motDePasse, string $motDePasseHash): bool;
}
