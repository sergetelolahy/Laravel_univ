<?php

declare(strict_types=1);

namespace App\Application\Referentiel\Salle;

use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Referentiel\Repositories\SalleRepositoryInterface;

final class SupprimerSalle
{
    public function __construct(private readonly SalleRepositoryInterface $repository)
    {
    }

    public function execute(int $id): void
    {
        $salle = $this->repository->trouverParId($id);
        if ($salle === null) {
            throw new RessourceIntrouvableException('Salle', $id);
        }

        $this->repository->supprimer($id);
    }
}
