<?php

declare(strict_types=1);

namespace App\Application\Referentiel\Semestre;

use App\Domain\Referentiel\Repositories\SemestreRepositoryInterface;

final class ListerSemestres
{
    public function __construct(private readonly SemestreRepositoryInterface $repository)
    {
    }

    /**
     * @return array<int, \App\Domain\Referentiel\Entities\Semestre>
     */
    public function execute(array $filtres = [], int $page = 1, int $parPage = 15): array
    {
        return $this->repository->lister($filtres, $page, $parPage);
    }
}
