<?php

declare(strict_types=1);

namespace App\Application\Referentiel\AnneeUniversitaire;

use App\Domain\Referentiel\Repositories\AnneeUniversitaireRepositoryInterface;

final class ListerAnneesUniversitaires
{
    public function __construct(private readonly AnneeUniversitaireRepositoryInterface $repository)
    {
    }

    /**
     * @return array<int, array{id:int, libelle:string, date_debut:string, date_fin:string, courante:bool}>
     */
    public function execute(array $filtres = [], int $page = 1, int $parPage = 15): array
    {
        $items = $this->repository->lister($filtres, $page, $parPage);

        return array_map(static fn ($annee) => [
            'id' => $annee->id,
            'libelle' => $annee->libelle,
            'date_debut' => $annee->dateDebut->format('Y-m-d'),
            'date_fin' => $annee->dateFin->format('Y-m-d'),
            'courante' => $annee->courante,
        ], $items);
    }
}
