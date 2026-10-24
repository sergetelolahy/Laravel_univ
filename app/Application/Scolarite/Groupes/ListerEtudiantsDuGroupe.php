<?php

declare(strict_types=1);

namespace App\Application\Scolarite\Groupes;

use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Scolarite\Repositories\GroupeRepository;

final class ListerEtudiantsDuGroupe
{
    public function __construct(private readonly GroupeRepository $repository)
    {
    }

    /**
     * @return array<int, array{id:int, utilisateur_id:int, matricule:string, nom:string, prenom:string, filiere_id:?int}>
     */
    public function execute(int $groupeId): array
    {
        $groupe = $this->repository->trouverParId($groupeId);
        if ($groupe === null) {
            throw new RessourceIntrouvableException('Groupe', $groupeId);
        }

        $etudiants = $this->repository->listerEtudiants($groupeId);
        $result = [];
        foreach ($etudiants as $etudiant) {
            $result[] = [
                'id' => $etudiant->id,
                'utilisateur_id' => $etudiant->utilisateurId,
                'matricule' => $etudiant->matricule,
                'nom' => $etudiant->nom,
                'prenom' => $etudiant->prenom,
                'filiere_id' => $etudiant->filiereId,
            ];
        }

        return $result;
    }
}
