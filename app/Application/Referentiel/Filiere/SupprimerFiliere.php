<?php

declare(strict_types=1);

namespace App\Application\Referentiel\Filiere;

use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Referentiel\Repositories\FiliereRepositoryInterface;

final class SupprimerFiliere
{
    public function __construct(private readonly FiliereRepositoryInterface $repository)
    {
    }

    public function execute(int $id): void
    {
        $filiere = $this->repository->trouverParId($id);
        if ($filiere === null) {
            throw new RessourceIntrouvableException('Filiere', $id);
        }

        $this->repository->supprimer($id);
    }
}
