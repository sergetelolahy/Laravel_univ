<?php

declare(strict_types=1);

namespace App\Application\Referentiel\Cours;

use App\Domain\Referentiel\Exceptions\DonneesInvalidesException;
use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Referentiel\Repositories\CoursRepositoryInterface;

final class AffecterEnseignantACours
{
    public function __construct(private readonly CoursRepositoryInterface $repository)
    {
    }

    public function execute(int $coursId, int $enseignantId, int $anneeId): void
    {
        if ($coursId <= 0 || $enseignantId <= 0 || $anneeId <= 0) {
            throw new DonneesInvalidesException('Les identifiants du cours, enseignant et année sont obligatoires.');
        }

        $cours = $this->repository->trouverParId($coursId);
        if ($cours === null) {
            throw new RessourceIntrouvableException('Cours', $coursId);
        }

        $this->repository->affecterEnseignant($coursId, $enseignantId, $anneeId);
    }
}
