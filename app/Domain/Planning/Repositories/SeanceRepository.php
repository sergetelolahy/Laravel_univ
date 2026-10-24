<?php

declare(strict_types=1);

namespace App\Domain\Planning\Repositories;

use App\Domain\Planning\Entities\PageSeances;
use App\Domain\Planning\Entities\SalleDisponible;
use App\Domain\Planning\Entities\Seance;

interface SeanceRepository
{
    /**
     * @param array{cours_id:int,groupe_id:int,enseignant_id:int,salle_id:int,debut:string,fin:string} $donnees
     * @throws \App\Domain\Planning\Exceptions\PlanningException en cas de conflit
     */
    public function creer(array $donnees): Seance;

    public function modifier(int $id, array $donnees): Seance;

    public function supprimer(int $id): void;

    public function trouverParId(int $id): ?Seance;

    /** @param array<string,mixed> $filtres groupe_id, enseignant_id, salle_id, cours_id, du, au */
    public function lister(array $filtres, int $page, int $parPage): PageSeances;

    /** @return Seance[] */
    public function listerPourEnseignant(int $enseignantId, string $du, string $au): array;

    /** @return Seance[] */
    public function listerPourEtudiant(int $etudiantId, string $du, string $au): array;

    /** @return Seance[] */
    public function occupationSalle(int $salleId, string $du, string $au): array;

    /** @return SalleDisponible[] */
    public function sallesDisponibles(string $debut, string $fin, int $capaciteMin): array;
}
