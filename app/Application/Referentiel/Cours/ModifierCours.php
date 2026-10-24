<?php

declare(strict_types=1);

namespace App\Application\Referentiel\Cours;

use App\Domain\Referentiel\Exceptions\ConflitException;
use App\Domain\Referentiel\Exceptions\DonneesInvalidesException;
use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Referentiel\Repositories\CoursRepositoryInterface;

final class ModifierCours
{
    public function __construct(private readonly CoursRepositoryInterface $repository)
    {
    }

    /**
     * @param array{code?:string, intitule?:string, semestre_id?:int, coefficient?:float|int, credits?:int} $donnees
     */
    public function execute(int $id, array $donnees): object
    {
        $cours = $this->repository->trouverParId($id);
        if ($cours === null) {
            throw new RessourceIntrouvableException('Cours', $id);
        }

        $code = trim((string) ($donnees['code'] ?? $cours->code));
        $intitule = trim((string) ($donnees['intitule'] ?? $cours->intitule));
        $semestreId = (int) ($donnees['semestre_id'] ?? $cours->semestreId);
        $coefficient = (float) ($donnees['coefficient'] ?? $cours->coefficient);
        $credits = (int) ($donnees['credits'] ?? $cours->credits);

        if ($code === '' || $intitule === '' || $semestreId <= 0) {
            throw new DonneesInvalidesException('Le code, l\'intitulé et le semestre sont obligatoires.');
        }

        if ($coefficient <= 0) {
            throw new DonneesInvalidesException('Le coefficient doit être strictement positif.');
        }

        if ($credits < 0) {
            throw new DonneesInvalidesException('Les crédits doivent être positifs ou nuls.');
        }

        if ($this->repository->existeCode($code, $id)) {
            throw new ConflitException('Un cours avec ce code existe déjà.');
        }

        return $this->repository->modifier($id, [
            'code' => $code,
            'intitule' => $intitule,
            'semestre_id' => $semestreId,
            'coefficient' => $coefficient,
            'credits' => $credits,
        ]);
    }
}
