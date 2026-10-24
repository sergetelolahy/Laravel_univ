<?php

declare(strict_types=1);

namespace App\Application\Referentiel\Semestre;

use App\Domain\Referentiel\Exceptions\ConflitException;
use App\Domain\Referentiel\Exceptions\DonneesInvalidesException;
use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Referentiel\Repositories\SemestreRepositoryInterface;

final class ModifierSemestre
{
    public function __construct(private readonly SemestreRepositoryInterface $repository)
    {
    }

    /**
     * @param array{filiere_id?:int, numero?:int} $donnees
     */
    public function execute(int $id, array $donnees): object
    {
        $semestre = $this->repository->trouverParId($id);
        if ($semestre === null) {
            throw new RessourceIntrouvableException('Semestre', $id);
        }

        $filiereId = (int) ($donnees['filiere_id'] ?? $semestre->filiereId);
        $numero = (int) ($donnees['numero'] ?? $semestre->numero);

        if ($filiereId <= 0) {
            throw new DonneesInvalidesException('La filière du semestre est obligatoire.');
        }

        if ($numero < 1 || $numero > 10) {
            throw new DonneesInvalidesException('Le numéro de semestre doit être compris entre 1 et 10.');
        }

        if ($this->repository->existeNumeroPourFiliere($filiereId, $numero, $id)) {
            throw new ConflitException('Ce numéro de semestre existe déjà pour cette filière.');
        }

        return $this->repository->modifier($id, [
            'filiere_id' => $filiereId,
            'numero' => $numero,
        ]);
    }
}
