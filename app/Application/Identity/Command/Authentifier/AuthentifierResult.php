<?php

declare(strict_types=1);

namespace App\Application\Identity\Command\Authentifier;

use App\Domain\Identity\Entities\Utilisateur;

final class AuthentifierResult
{
    public function __construct(
        public readonly string $token,
        public readonly Utilisateur $utilisateur,
    ) {
    }
}
