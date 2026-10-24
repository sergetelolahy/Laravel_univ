<?php

declare(strict_types=1);

namespace App\Domain\Scolarite\Entities;

readonly class Groupe
{
    public function __construct(
        public int $id,
        public string $nom,
        public int $filiereId,
        public int $anneeId,
        public int $effectifMax,
        public int $effectifActuel = 0,
    ) {
    }
}
