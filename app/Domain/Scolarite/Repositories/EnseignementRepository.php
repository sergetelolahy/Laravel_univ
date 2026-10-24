<?php

declare(strict_types=1);

namespace App\Domain\Scolarite\Repositories;

interface EnseignementRepository
{
    public function enseignantAffecteAuCours(int $utilisateurId, int $coursId, int $anneeId): bool;

    public function filierePourCours(int $coursId): int;

    public function anneeCourante(): ?int;
}
