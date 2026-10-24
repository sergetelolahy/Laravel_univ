<?php

declare(strict_types=1);

namespace App\Application\Referentiel\Semestre;

use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Referentiel\Repositories\SemestreRepositoryInterface;

final class SupprimerSemestre
{
    public function __construct(private readonly SemestreRepositoryInterface $repository)
    {
    }

    public function execute(int $id): void
    {
        $semestre = $this->repository->trouverParId($id);
        if ($semestre === null) {
            throw new RessourceIntrouvableException('Semestre', $id);
        }

        $this->repository->supprimer($id);
    }
}
