<?php

declare(strict_types=1);

namespace App\Domain\Referentiel\Repositories;

use App\Domain\Referentiel\Entities\Semestre;

interface SemestreRepositoryInterface
{
    /** @return array<int, Semestre> */
    public function lister(array $filtres, int $page, int $parPage): array;

    public function trouverParId(int $id): ?Semestre;

    public function creer(array $donnees): Semestre;

    public function modifier(int $id, array $donnees): Semestre;

    public function supprimer(int $id): void;

    public function existeNumeroPourFiliere(int $filiereId, int $numero, ?int $excludeId = null): bool;
}
