<?php

declare(strict_types=1);

namespace App\Application\Referentiel\Cours;

use App\Domain\Referentiel\Repositories\CoursRepositoryInterface;

final class ListerCours
{
    public function __construct(private readonly CoursRepositoryInterface $repository)
    {
    }

    /**
     * @return array<int, \App\Domain\Referentiel\Entities\Cours>
     */
    public function execute(array $filtres = [], int $page = 1, int $parPage = 15): array
    {
        return $this->repository->lister($filtres, $page, $parPage);
    }
}
