<?php

declare(strict_types=1);

namespace App\Domain\Referentiel\Entities;

readonly class Salle
{
    public function __construct(
        public int $id,
        public string $nom,
        public int $capacite,
    ) {
    }
}
