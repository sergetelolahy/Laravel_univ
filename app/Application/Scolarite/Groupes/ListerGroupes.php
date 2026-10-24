<?php

declare(strict_types=1);

namespace App\Application\Scolarite\Groupes;

use App\Domain\Scolarite\Repositories\GroupeRepository;

final class ListerGroupes
{
    public function __construct(private readonly GroupeRepository $repository)
    {
    }

    /**
     * @return array<int, array{id:int, nom:string, filiere_id:int, annee_id:int, effectif_max:int, effectif_actuel:int}>
     */
    public function execute(?int $filiereId = null, ?int $anneeId = null, ?string $q = null, int $page = 1, int $parPage = 20): array
    {
        $groupes = $this->repository->lister($filiereId, $anneeId, $q, $page, $parPage);

        $result = [];
        foreach ($groupes as $groupe) {
            $result[] = [
                'id' => $groupe->id,
                'nom' => $groupe->nom,
                'filiere_id' => $groupe->filiereId,
                'annee_id' => $groupe->anneeId,
                'effectif_max' => $groupe->effectifMax,
                'effectif_actuel' => $groupe->effectifActuel,
            ];
        }

        return $result;
    }
}
