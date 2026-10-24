<?php

declare(strict_types=1);

namespace App\Application\Scolarite\Etudiants;

use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Scolarite\Repositories\EtudiantRepository;

final class ObtenirMaFiche
{
    public function __construct(private readonly EtudiantRepository $repository)
    {
    }

    public function execute(int $utilisateurId): array
    {
        $etudiant = $this->repository->trouverParUtilisateurId($utilisateurId);
        if ($etudiant === null) {
            throw new RessourceIntrouvableException('Etudiant', $utilisateurId);
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
