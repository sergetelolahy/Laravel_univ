<?php

declare(strict_types=1);

namespace App\Application\Planning\ListerSeances;

use App\Domain\Planning\Entities\PageSeances;
use App\Domain\Planning\Repositories\SeanceRepository;

final class ListerSeances
{
    public function __construct(private readonly SeanceRepository $seances)
    {
    }

    /** @param array<string,mixed> $filtres */
    public function execute(array $filtres, int $page, int $parPage): PageSeances
    {
        return $this->seances->lister($filtres, $page, $parPage);
    }
}
