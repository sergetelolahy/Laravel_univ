<?php

declare(strict_types=1);

namespace App\Application\Referentiel\Filiere;

use App\Domain\Referentiel\Repositories\FiliereRepositoryInterface;

final class ListerFilieres
{
    public function __construct(private readonly FiliereRepositoryInterface $repository)
    {
    }

    /**
     * @return array<int, array{id:int, code:string, nom:string}>
     */
    public function execute(array $filtres = [], int $page = 1, int $parPage = 15): array
    {
        $items = $this->repository->lister($filtres, $page, $parPage);

        return array_map(static fn ($filiere) => [
            'id' => $filiere->id,
            'code' => $filiere->code,
            'nom' => $filiere->nom,
        ], $items);
    }
}
