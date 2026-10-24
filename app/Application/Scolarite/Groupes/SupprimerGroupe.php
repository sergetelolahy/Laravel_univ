<?php

declare(strict_types=1);

namespace App\Application\Scolarite\Groupes;

use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Referentiel\Exceptions\RessourceUtiliseeException;
use App\Domain\Scolarite\Repositories\GroupeRepository;

final class SupprimerGroupe
{
    public function __construct(private readonly GroupeRepository $repository)
    {
    }

    public function execute(int $id): void
    {
        $groupe = $this->repository->trouverParId($id);
        if ($groupe === null) {
            throw new RessourceIntrouvableException('Groupe', $id);
        }

        if ($groupe->effectifActuel > 0) {
            throw new RessourceUtiliseeException('Groupe');
        }

        $this->repository->supprimer($id);
    }
}
