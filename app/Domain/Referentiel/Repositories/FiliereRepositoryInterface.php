<?php

declare(strict_types=1);

namespace App\Domain\Referentiel\Repositories;

use App\Domain\Referentiel\Entities\Filiere;

interface FiliereRepositoryInterface
{
    /** @return array<int, Filiere> */
    public function lister(array $filtres, int $page, int $parPage): array;

    public function trouverParId(int $id): ?Filiere;

    public function creer(array $donnees): Filiere;

    public function modifier(int $id, array $donnees): Filiere;

    public function supprimer(int $id): void;

    public function existeCode(string $code, ?int $excludeId = null): bool;

    public function existeNom(string $nom, ?int $excludeId = null): bool;
}
