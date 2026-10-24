<?php

declare(strict_types=1);

namespace App\Application\Scolarite\Groupes;

use App\Domain\Referentiel\Exceptions\DonneesInvalidesException;
use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Scolarite\Repositories\GroupeRepository;

final class ModifierGroupe
{
    public function __construct(private readonly GroupeRepository $repository)
    {
    }

    /**
     * @param array{nom?:string, filiere_id?:int, annee_id?:int, effectif_max?:int} $donnees
     */
    public function execute(int $id, array $donnees): array
    {
        $groupe = $this->repository->trouverParId($id);
        if ($groupe === null) {
            throw new RessourceIntrouvableException('Groupe', $id);
        }

        $payload = [];
        if (array_key_exists('nom', $donnees)) {
            $nom = trim((string) $donnees['nom']);
            if ($nom === '') {
                throw new DonneesInvalidesException('Le nom du groupe est obligatoire.');
            }
            $payload['nom'] = $nom;
        }

        if (array_key_exists('filiere_id', $donnees)) {
            $filiereId = (int) $donnees['filiere_id'];
            if ($filiereId <= 0) {
                throw new DonneesInvalidesException('La filière du groupe est invalide.');
            }
            $payload['filiere_id'] = $filiereId;
        }

        if (array_key_exists('annee_id', $donnees)) {
            $anneeId = (int) $donnees['annee_id'];
            if ($anneeId <= 0) {
                throw new DonneesInvalidesException('L\'année du groupe est invalide.');
            }
            $payload['annee_id'] = $anneeId;
        }

        if (array_key_exists('effectif_max', $donnees)) {
            $effectifMax = (int) $donnees['effectif_max'];
            if ($effectifMax <= 0) {
                throw new DonneesInvalidesException('L\'effectif maximal doit être strictement positif.');
            }
            $payload['effectif_max'] = $effectifMax;
        }

        $updated = $this->repository->modifier($id, $payload);

        return [
            'id' => $updated->id,
            'nom' => $updated->nom,
            'filiere_id' => $updated->filiereId,
            'annee_id' => $updated->anneeId,
            'effectif_max' => $updated->effectifMax,
            'effectif_actuel' => $updated->effectifActuel,
        ];
    }
}
