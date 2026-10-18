<?php

declare(strict_types=1);

namespace App\Domain\Identity\Repositories;

use App\Domain\Identity\Enums\Role;
use App\Domain\Identity\Entities\Utilisateur;

interface UtilisateurRepository
{
    public function trouverParEmail(string $email): ?Utilisateur;

    public function trouverParId(int $id): ?Utilisateur;

    public function emailExiste(string $email): bool;

    /**
     * @param array<string, mixed> $compte
     * @param array<string, mixed> $profil
     */
    public function creerAvecProfil(array $compte, array $profil): Utilisateur;

    /**
     * @return array<int, Utilisateur>
     */
    public function lister(?Role $role, int $page, int $parPage): array;

    public function desactiver(int $id): void;

    public function enregistrerConnexion(int $id): void;
}
