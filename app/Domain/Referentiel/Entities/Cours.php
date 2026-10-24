<?php

declare(strict_types=1);

namespace App\Domain\Referentiel\Entities;

readonly class Cours
{
    public function __construct(
        public int $id,
        public string $code,
        public string $intitule,
        public int $semestreId,
        public float $coefficient,
        public int $credits,
        public ?Semestre $semestre = null,
    ) {
    }
}
