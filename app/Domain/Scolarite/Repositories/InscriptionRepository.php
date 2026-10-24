<?php

declare(strict_types=1);

namespace App\Domain\Scolarite\Repositories;

use App\Domain\Scolarite\Entities\Inscription;

interface InscriptionRepository
{
    public function creer(array $donnees): Inscription;

    public function creerEnLotAtomique(array $inscriptions): void;

    /** @return array<int, Inscription> */
    public function lister(?int $etudiantId = null, ?int $coursId = null, ?int $anneeId = null, ?int $filiereId = null, int $page = 1, int $parPage = 20): array;

    /** @return array<int, Inscription> */
    public function listerParEtudiant(int $etudiantId): array;

    /** @return array<int, Inscription> */
    public function listerInscritsDuCours(int $coursId, ?int $anneeId = null): array;

    public function supprimer(int $id): void;

    public function trouverParId(int $id): ?Inscription;

    public function existe(int $etudiantId, int $coursId, int $anneeId): bool;
}
