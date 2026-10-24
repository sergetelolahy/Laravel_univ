<?php

declare(strict_types=1);

namespace App\Application\Scolarite\Etudiants;

use App\Domain\Scolarite\Repositories\EtudiantRepository;

final class ListerEtudiants
{
    public function __construct(private readonly EtudiantRepository $repository)
    {
    }

    /**
     * @return array<int, array{id:int, utilisateur_id:int, matricule:string, nom:string, prenom:string, filiere_id:?int, filiere?:array{id:int, code:string, nom:string}|null}>
     */
    public function execute(?int $filiereId = null, ?string $q = null, int $page = 1, int $parPage = 20): array
    {
        $etudiants = $this->repository->lister($filiereId, $q, $page, $parPage);

        $result = [];
        foreach ($etudiants as $etudiant) {
            $result[] = [
                'id' => $etudiant->id,
                'utilisateur_id' => $etudiant->utilisateurId,
                'matricule' => $etudiant->matricule,
                'nom' => $etudiant->nom,
                'prenom' => $etudiant->prenom,
                'filiere_id' => $etudiant->filiereId,
                'filiere' => $etudiant->resumeFiliere(),
            ];
        }

        return $result;
    }
}
