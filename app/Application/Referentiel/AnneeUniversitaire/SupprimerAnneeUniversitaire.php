<?php

declare(strict_types=1);

namespace App\Application\Referentiel\AnneeUniversitaire;

use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Referentiel\Repositories\AnneeUniversitaireRepositoryInterface;

final class SupprimerAnneeUniversitaire
{
    public function __construct(private readonly AnneeUniversitaireRepositoryInterface $repository)
    {
    }

    public function execute(int $id): void
    {
        $annee = $this->repository->trouverParId($id);
        if ($annee === null) {
            throw new RessourceIntrouvableException('AnneeUniversitaire', $id);
        }

        $this->repository->supprimer($id);
    }
}
