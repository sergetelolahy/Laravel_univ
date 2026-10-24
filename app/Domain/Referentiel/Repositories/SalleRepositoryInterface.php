<?php

declare(strict_types=1);

namespace App\Domain\Referentiel\Repositories;

use App\Domain\Referentiel\Entities\Salle;

interface SalleRepositoryInterface
{
    /** @return array<int, Salle> */
    public function lister(array $filtres, int $page, int $parPage): array;

    public function trouverParId(int $id): ?Salle;

    public function creer(array $donnees): Salle;

    public function modifier(int $id, array $donnees): Salle;

    public function supprimer(int $id): void;

    public function existeNom(string $nom, ?int $excludeId = null): bool;
}
