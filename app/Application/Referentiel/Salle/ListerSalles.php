<?php

declare(strict_types=1);

namespace App\Application\Referentiel\Salle;

use App\Domain\Referentiel\Repositories\SalleRepositoryInterface;

final class ListerSalles
{
    public function __construct(private readonly SalleRepositoryInterface $repository)
    {
    }

    /**
     * @return array<int, array{id:int, nom:string, capacite:int}>
     */
    public function execute(array $filtres = [], int $page = 1, int $parPage = 15): array
    {
        $items = $this->repository->lister($filtres, $page, $parPage);

        return array_map(static fn ($salle) => [
            'id' => $salle->id,
            'nom' => $salle->nom,
            'capacite' => $salle->capacite,
        ], $items);
    }
}
