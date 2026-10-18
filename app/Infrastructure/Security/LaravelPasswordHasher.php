<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\Identity\Services\PasswordHasher;
use Illuminate\Support\Facades\Hash;

final class LaravelPasswordHasher implements PasswordHasher
{
    public function hacher(string $motDePasse): string
    {
        return Hash::make($motDePasse);
    }

    public function verifier(string $motDePasse, string $motDePasseHash): bool
    {
        return Hash::check($motDePasse, $motDePasseHash);
    }
}
