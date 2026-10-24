<?php

declare(strict_types=1);

namespace App\Domain\Referentiel\Repositories;

use App\Domain\Referentiel\Entities\Cours;

interface CoursRepositoryInterface
{
    /** @return array<int, Cours> */
    public function lister(array $filtres, int $page, int $parPage): array;

    public function trouverParId(int $id): ?Cours;

    public function creer(array $donnees): Cours;

    public function modifier(int $id, array $donnees): Cours;

    public function supprimer(int $id): void;

    public function existeCode(string $code, ?int $excludeId = null): bool;

    public function affecterEnseignant(int $coursId, int $enseignantId, int $anneeId): void;

    public function retirerEnseignant(int $coursId, int $enseignantId, int $anneeId): void;

    /** @return array<int, int> */
    public function listerEnseignants(int $coursId, ?int $anneeId = null): array;
}
