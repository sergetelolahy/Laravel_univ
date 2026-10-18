<?php

declare(strict_types=1);

namespace App\Domain\Identity\Entities;

use App\Domain\Identity\Enums\Role;

readonly class Utilisateur
{
    public function __construct(
        public int $id,
        public string $email,
        public string $motDePasseHash,
        public Role $role,
        public bool $actif,
        public ?\DateTimeImmutable $derniereConnexion = null,
        public ?array $profil = null,
    ) {
    }
}
