<?php

declare(strict_types=1);

namespace App\Application\Scolarite\Groupes;

use App\Domain\Referentiel\Exceptions\DonneesInvalidesException;
use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Scolarite\Repositories\GroupeRepository;

final class RetirerEtudiantDuGroupe
{
    public function __construct(private readonly GroupeRepository $repository)
    {
    }

    public function execute(int $groupeId, int $etudiantId): void
    {
        if ($groupeId <= 0 || $etudiantId <= 0) {
            throw new DonneesInvalidesException('Les identifiants du groupe et de l\'étudiant sont obligatoires.');
        }

        $groupe = $this->repository->trouverParId($groupeId);
        if ($groupe === null) {
            throw new RessourceIntrouvableException('Groupe', $groupeId);
        }

        $this->repository->retirerEtudiant($groupeId, $etudiantId);
    }
}
