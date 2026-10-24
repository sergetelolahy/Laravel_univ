<?php

declare(strict_types=1);

namespace App\Domain\Referentiel\Entities;

readonly class AnneeUniversitaire
{
    public function __construct(
        public int $id,
        public string $libelle,
        public \DateTimeImmutable $dateDebut,
        public \DateTimeImmutable $dateFin,
        public bool $courante = false,
    ) {
    }
}
