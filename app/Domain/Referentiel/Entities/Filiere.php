<?php

declare(strict_types=1);

namespace App\Domain\Referentiel\Entities;

readonly class Filiere
{
    public function __construct(
        public int $id,
        public string $code,
        public string $nom,
    ) {
    }
}
