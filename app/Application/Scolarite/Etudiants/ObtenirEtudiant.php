<?php

declare(strict_types=1);

namespace App\Application\Scolarite\Etudiants;

use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Scolarite\Repositories\EtudiantRepository;

final class ObtenirEtudiant
{
    public function __construct(private readonly EtudiantRepository $repository)
    {
    }

    public function execute(int $id): array
    {
        $etudiant = $this->repository->trouverParId($id);
        if ($etudiant === null) {
            throw new RessourceIntrouvableException('Etudiant', $id);
        }

        return [
            'id' => $etudiant->id,
            'utilisateur_id' => $etudiant->utilisateurId,
            'matricule' => $etudiant->matricule,
            'nom' => $etudiant->nom,
            'prenom' => $etudiant->prenom,
            'filiere_id' => $etudiant->filiereId,
            'filiere' => $etudiant->resumeFiliere(),
        ];
    }
}
