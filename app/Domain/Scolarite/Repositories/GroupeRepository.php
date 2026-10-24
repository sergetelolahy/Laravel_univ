<?php

declare(strict_types=1);

namespace App\Domain\Scolarite\Repositories;

use App\Domain\Scolarite\Entities\Etudiant;
use App\Domain\Scolarite\Entities\Groupe;

interface GroupeRepository
{
    /** @return array<int, Groupe> */
    public function lister(?int $filiereId = null, ?int $anneeId = null, ?string $q = null, int $page = 1, int $parPage = 20): array;

    public function trouverParId(int $id): ?Groupe;

    public function creer(array $donnees): Groupe;

    public function modifier(int $id, array $donnees): Groupe;

    public function supprimer(int $id): void;

    /** @return array<int, Etudiant> */
    public function listerEtudiants(int $groupeId): array;

    public function ajouterEtudiantsAtomique(int $groupeId, array $etudiantIds): void;

    public function retirerEtudiant(int $groupeId, int $etudiantId): void;

    public function groupeDeLEtudiantPourAnnee(int $etudiantId, int $anneeId): ?Groupe;
}
