<?php

declare(strict_types=1);

namespace App\Application\Scolarite\Groupes;

use App\Domain\Referentiel\Exceptions\ConflitException;
use App\Domain\Referentiel\Exceptions\DonneesInvalidesException;
use App\Domain\Scolarite\Repositories\GroupeRepository;

final class CreerGroupe
{
    public function __construct(private readonly GroupeRepository $repository)
    {
    }

    /**
     * @param array{nom:string, filiere_id:int, annee_id:int, effectif_max:int} $donnees
     */
    public function execute(array $donnees): array
    {
        $nom = trim((string) ($donnees['nom'] ?? ''));
        $filiereId = (int) ($donnees['filiere_id'] ?? 0);
        $anneeId = (int) ($donnees['annee_id'] ?? 0);
        $effectifMax = (int) ($donnees['effectif_max'] ?? 0);

        if ($nom === '' || $filiereId <= 0 || $anneeId <= 0 || $effectifMax <= 0) {
            throw new DonneesInvalidesException('Le nom, la filière, l\'année et la capacité sont obligatoires.');
        }

        $groupe = $this->repository->creer([
            'nom' => $nom,
            'filiere_id' => $filiereId,
            'annee_id' => $anneeId,
            'effectif_max' => $effectifMax,
        ]);

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
