<?php

declare(strict_types=1);

namespace App\Domain\Planning\Repositories;

/**
 * Lectures nécessaires aux règles du planning (autres modules).
 */
interface PlanningContexte
{
    public function filiereDuCours(int $coursId): ?int;

    /** @return array{id:int,filiere_id:int,annee_id:int,effectif:int}|null */
    public function groupe(int $groupeId): ?array;

    /** @return array{id:int,nom:string,capacite:int}|null */
    public function salle(int $salleId): ?array;

    public function enseignantAffecte(int $enseignantId, int $coursId, int $anneeId): bool;

    /** @return array{date_debut:string,date_fin:string}|null */
    public function anneePlage(int $anneeId): ?array;

    public function enseignantIdPourUtilisateur(int $utilisateurId): ?int;

    public function etudiantIdPourUtilisateur(int $utilisateurId): ?int;
}
