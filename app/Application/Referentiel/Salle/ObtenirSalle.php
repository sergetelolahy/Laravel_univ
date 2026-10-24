<?php

declare(strict_types=1);

namespace App\Application\Referentiel\Salle;

use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Referentiel\Repositories\SalleRepositoryInterface;

final class ObtenirSalle
{
    public function __construct(private readonly SalleRepositoryInterface $repository)
    {
    }

    public function execute(int $id): array
    {
        $salle = $this->repository->trouverParId($id);
        if ($salle === null) {
            throw new RessourceIntrouvableException('Salle', $id);
        }

        return [
            'id' => $salle->id,
            'nom' => $salle->nom,
            'capacite' => $salle->capacite,
        ];
    }
}
