<?php

declare(strict_types=1);

namespace App\Domain\Referentiel\Repositories;

use App\Domain\Referentiel\Entities\AnneeUniversitaire;

interface AnneeUniversitaireRepositoryInterface
{
    /** @return array<int, AnneeUniversitaire> */
    public function lister(array $filtres, int $page, int $parPage): array;

    public function trouverParId(int $id): ?AnneeUniversitaire;

    public function creer(array $donnees): AnneeUniversitaire;

    public function modifier(int $id, array $donnees): AnneeUniversitaire;

    public function supprimer(int $id): void;

    public function existeLibelle(string $libelle, ?int $excludeId = null): bool;

    public function definirCourante(int $id): void;
}
