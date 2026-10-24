<?php

declare(strict_types=1);

namespace App\Application\Referentiel\Cours;

use App\Domain\Referentiel\Exceptions\DonneesInvalidesException;
use App\Domain\Referentiel\Repositories\CoursRepositoryInterface;

final class ListerEnseignantsDuCours
{
    public function __construct(private readonly CoursRepositoryInterface $repository)
    {
    }

    /**
     * @return array<int, int>
     */
    public function execute(int $coursId, ?int $anneeId = null): array
    {
        if ($coursId <= 0) {
            throw new DonneesInvalidesException('L\'identifiant du cours est obligatoire.');
        }

        return $this->repository->listerEnseignants($coursId, $anneeId);
    }
}
