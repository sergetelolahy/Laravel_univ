<?php

declare(strict_types=1);

namespace App\Application\Scolarite\Inscriptions;

use App\Domain\Referentiel\Exceptions\DonneesInvalidesException;
use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Scolarite\Repositories\InscriptionRepository;

final class Desinscrire
{
    public function __construct(private readonly InscriptionRepository $repository)
    {
    }

    public function execute(int $id): void
    {
        if ($id <= 0) {
            throw new DonneesInvalidesException('L\'identifiant de l\'inscription est obligatoire.');
        }

        $inscription = $this->repository->trouverParId($id);
        if ($inscription === null) {
            throw new RessourceIntrouvableException('Inscription', $id);
        }

        $this->repository->supprimer($id);
    }
}
