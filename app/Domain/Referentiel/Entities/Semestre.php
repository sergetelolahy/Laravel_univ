<?php

declare(strict_types=1);

namespace App\Domain\Referentiel\Entities;

readonly class Semestre
{
    public function __construct(
        public int $id,
        public int $filiereId,
        public int $numero,
        public ?Filiere $filiere = null,
    ) {
    }
}
