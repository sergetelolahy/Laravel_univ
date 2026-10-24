<?php

declare(strict_types=1);

namespace App\Application\Referentiel\Cours;

use App\Domain\Referentiel\Exceptions\ConflitException;
use App\Domain\Referentiel\Exceptions\DonneesInvalidesException;
use App\Domain\Referentiel\Repositories\CoursRepositoryInterface;

final class CreerCours
{
    public function __construct(private readonly CoursRepositoryInterface $repository)
    {
    }

    /**
     * @param array{code:string, intitule:string, semestre_id:int, coefficient:float|int, credits:int} $donnees
     */
    public function execute(array $donnees): object
    {
        $code = trim((string) ($donnees['code'] ?? ''));
        $intitule = trim((string) ($donnees['intitule'] ?? ''));
        $semestreId = (int) ($donnees['semestre_id'] ?? 0);
        $coefficient = (float) ($donnees['coefficient'] ?? 0);
        $credits = (int) ($donnees['credits'] ?? 0);

        if ($code === '' || $intitule === '' || $semestreId <= 0) {
            throw new DonneesInvalidesException('Le code, l\'intitulé et le semestre sont obligatoires.');
        }

        if ($coefficient <= 0) {
            throw new DonneesInvalidesException('Le coefficient doit être strictement positif.');
        }

        if ($credits < 0) {
            throw new DonneesInvalidesException('Les crédits doivent être positifs ou nuls.');
        }

        if ($this->repository->existeCode($code)) {
            throw new ConflitException('Un cours avec ce code existe déjà.');
        }

        return $this->repository->creer([
            'code' => $code,
            'intitule' => $intitule,
            'semestre_id' => $semestreId,
            'coefficient' => $coefficient,
            'credits' => $credits,
        ]);
    }
}
