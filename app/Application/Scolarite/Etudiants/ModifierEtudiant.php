<?php

declare(strict_types=1);

namespace App\Application\Scolarite\Etudiants;

use App\Domain\Referentiel\Exceptions\DonneesInvalidesException;
use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Scolarite\Repositories\EtudiantRepository;

final class ModifierEtudiant
{
    public function __construct(private readonly EtudiantRepository $repository)
    {
    }

    /**
     * @param array{nom?:string, prenom?:string, matricule?:string, filiere_id?:int|null} $donnees
     */
    public function execute(int $id, array $donnees): array
    {
        $etudiant = $this->repository->trouverParId($id);
        if ($etudiant === null) {
            throw new RessourceIntrouvableException('Etudiant', $id);
        }

        $payload = [];
        if (array_key_exists('nom', $donnees)) {
            $nom = trim((string) $donnees['nom']);
            if ($nom === '') {
                throw new DonneesInvalidesException('Le nom de l\'étudiant est obligatoire.');
            }
            $payload['nom'] = $nom;
        }

        if (array_key_exists('prenom', $donnees)) {
            $prenom = trim((string) $donnees['prenom']);
            if ($prenom === '') {
                throw new DonneesInvalidesException('Le prénom de l\'étudiant est obligatoire.');
            }
            $payload['prenom'] = $prenom;
        }

        if (array_key_exists('matricule', $donnees)) {
            $matricule = trim((string) $donnees['matricule']);
            if ($matricule === '') {
                throw new DonneesInvalidesException('La matricule de l\'étudiant est obligatoire.');
            }
            $payload['matricule'] = $matricule;
        }

        if (array_key_exists('filiere_id', $donnees)) {
            $payload['filiere_id'] = $donnees['filiere_id'];
        }

        $updated = $this->repository->modifierFiche($id, $payload);

        return [
            'id' => $updated->id,
            'utilisateur_id' => $updated->utilisateurId,
            'matricule' => $updated->matricule,
            'nom' => $updated->nom,
            'prenom' => $updated->prenom,
            'filiere_id' => $updated->filiereId,
            'filiere' => $updated->resumeFiliere(),
        ];
    }
}
