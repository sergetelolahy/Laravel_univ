<?php

declare(strict_types=1);

namespace App\Domain\Scolarite\Repositories;

use App\Domain\Scolarite\Entities\Etudiant;

interface EtudiantRepository
{
    /** @return array<int, Etudiant> */
    public function lister(?int $filiereId = null, ?string $q = null, int $page = 1, int $parPage = 20): array;

    public function trouverParId(int $id): ?Etudiant;

    public function trouverParUtilisateurId(int $utilisateurId): ?Etudiant;

    public function modifierFiche(int $id, array $donnees): Etudiant;
}
