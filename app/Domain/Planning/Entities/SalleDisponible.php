<?php

declare(strict_types=1);

namespace App\Domain\Planning\Entities;

final class SalleDisponible
{
    public function __construct(
        public readonly int $id,
        public readonly string $nom,
        public readonly int $capacite,
    ) {
    }
}
