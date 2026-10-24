<?php

declare(strict_types=1);

namespace App\Application\Referentiel\AnneeUniversitaire;

use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Referentiel\Repositories\AnneeUniversitaireRepositoryInterface;

final class ObtenirAnneeUniversitaire
{
    public function __construct(private readonly AnneeUniversitaireRepositoryInterface $repository)
    {
    }

    public function execute(int $id): array
    {
        $annee = $this->repository->trouverParId($id);
        if ($annee === null) {
            throw new RessourceIntrouvableException('AnneeUniversitaire', $id);
        }

        return [
            'id' => $annee->id,
            'libelle' => $annee->libelle,
            'date_debut' => $annee->dateDebut->format('Y-m-d'),
            'date_fin' => $annee->dateFin->format('Y-m-d'),
            'courante' => $annee->courante,
        ];
    }
}
