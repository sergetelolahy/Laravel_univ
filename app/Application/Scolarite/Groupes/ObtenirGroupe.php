<?php

declare(strict_types=1);

namespace App\Application\Scolarite\Groupes;

use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Scolarite\Repositories\GroupeRepository;

final class ObtenirGroupe
{
    public function __construct(private readonly GroupeRepository $repository)
    {
    }

    public function execute(int $id): array
    {
        $groupe = $this->repository->trouverParId($id);
        if ($groupe === null) {
            throw new RessourceIntrouvableException('Groupe', $id);
        }

        return [
            'id' => $groupe->id,
            'nom' => $groupe->nom,
            'filiere_id' => $groupe->filiereId,
            'annee_id' => $groupe->anneeId,
            'effectif_max' => $groupe->effectifMax,
            'effectif_actuel' => $groupe->effectifActuel,
        ];
    }
}
