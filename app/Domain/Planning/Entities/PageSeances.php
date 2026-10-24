<?php

declare(strict_types=1);

namespace App\Domain\Planning\Entities;

final class PageSeances
{
    /** @param Seance[] $items */
    public function __construct(
        public readonly array $items,
        public readonly int $total,
    ) {
    }
}
